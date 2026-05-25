#!/usr/bin/env python3
"""Generate a local review-only image import plan for Sports Warehouse.

This script is a planning/reporting tool only. It does NOT copy, move, rename,
delete, or modify any image files or database records.
"""
from __future__ import annotations

import argparse
import csv
import json
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

DEFAULT_MATCH_REVIEW = Path("docs/operations/generated/image-product-folder-match-review.csv")
DEFAULT_PRODUCT_DB = Path("docs/data/SportWarehouse_ProductDB.csv")
DEFAULT_INVENTORY = Path("clothes_folder_inventory.csv")
DEFAULT_PLAN_CSV = Path("docs/operations/generated/image-import-plan.csv")
DEFAULT_SUMMARY_MD = Path("docs/operations/generated/image-import-plan-summary.md")


EXPECTED_UNMATCHED_OUT_OF_SCOPE = {
    "pro_tec_classic_skate_helmet",
    "sting_armaplus_boxing_gloves",
}


def normalize_path(value: str) -> str:
    return (value or "").strip().replace("\\", "/")


def normalize_key(value: str) -> str:
    return "".join(ch.lower() if ch.isalnum() else "_" for ch in (value or "")).strip("_")


def fail_if_missing(paths: list[Path]) -> None:
    missing = [str(p) for p in paths if not p.exists()]
    if missing:
        formatted = "\n - ".join([""] + missing)
        raise FileNotFoundError(
            "Missing required input file(s). These are local review artifacts and must exist before planning can run:" + formatted
        )


def read_csv_rows(path: Path) -> tuple[list[str], list[dict[str, str]]]:
    with path.open("r", newline="", encoding="utf-8-sig") as handle:
        reader = csv.DictReader(handle)
        if not reader.fieldnames:
            raise ValueError(f"CSV has no header row: {path}")
        rows = [dict(r) for r in reader]
        return list(reader.fieldnames), rows


def inventory_index(rows: list[dict[str, str]]) -> dict[str, list[str]]:
    key_lookup = {normalize_key(k): k for k in (rows[0].keys() if rows else [])}
    rel_key = (
        key_lookup.get("relativepath")
        or key_lookup.get("relative_path")
        or key_lookup.get("path")
        or key_lookup.get("file_path")
        or key_lookup.get("filepath")
    )
    if not rel_key:
        raise ValueError("clothes_folder_inventory.csv must include RelativePath/relative_path/path/file_path/filepath")

    by_folder: dict[str, list[str]] = defaultdict(list)
    for row in rows:
        rel_file = normalize_path((row.get(rel_key) or "").strip())
        if not rel_file:
            continue
        file_path = Path(rel_file)
        folder = normalize_path(str(file_path.parent)).strip("/")
        by_folder[folder].append(file_path.name)

    for folder in by_folder:
        by_folder[folder].sort()
    return by_folder


def build_product_index(product_rows: list[dict[str, str]]) -> dict[str, dict[str, str]]:
    idx: dict[str, dict[str, str]] = {}
    for row in product_rows:
        item_id = (row.get("db_itemId") or "").strip()
        if item_id:
            idx[item_id] = row
    return idx


def infer_out_of_scope(product: dict[str, str], match_status: str, brand: str, item_name: str, model_id: str) -> bool:
    if match_status not in {"unmatched_product", "unmatched_product_with_candidates", "unmatched_folder"}:
        return False

    marker = normalize_key(model_id or item_name)
    if marker in EXPECTED_UNMATCHED_OUT_OF_SCOPE:
        return True

    text_blob = " ".join(
        [
            brand,
            item_name,
            model_id,
            product.get("subCategory", ""),
            product.get("categoryName", ""),
        ]
    ).lower()
    out_of_scope_signals = (
        "helmet",
        "boxing",
        "glove",
        "gloves",
        "mma",
        "martial",
        "combat",
        "protective",
        "protection",
        "pad",
        "pads",
        "shin guard",
        "headgear",
    )
    return any(signal in text_blob for signal in out_of_scope_signals)


def to_json_list(items: list[str]) -> str:
    return json.dumps(items, ensure_ascii=False)


def plan_rows(match_rows: list[dict[str, str]], product_idx: dict[str, dict[str, str]], inv: dict[str, list[str]]) -> list[dict[str, Any]]:
    out: list[dict[str, Any]] = []

    for m in match_rows:
        item_id = (m.get("product_db_itemId") or m.get("db_itemId") or "").strip()
        product = product_idx.get(item_id, {})

        brand = (product.get("brand") or m.get("product_brand") or m.get("brand") or "").strip()
        item_name = (product.get("itemName") or m.get("product_itemName") or m.get("itemName") or "").strip()
        model_id = (product.get("model_id") or m.get("product_model_id") or m.get("model_id") or "").strip()
        images_path = normalize_path((product.get("images") or "").strip())
        thumbnails_json = (product.get("thumbnails_json") or "").strip()

        match_status = (m.get("match_status") or "").strip()
        match_score = (m.get("match_score") or "").strip()
        confidence_reason = (m.get("confidence_reason") or "").strip()
        candidate_folder = normalize_path((m.get("candidate_folder_relative_path") or "").strip()).strip("/")

        source_files = inv.get(candidate_folder, []) if candidate_folder else []
        source_file_count = len(source_files)

        warnings: list[str] = []
        matcher_warning = (m.get("warning") or "").strip()
        if matcher_warning:
            warnings.append(matcher_warning)
        if candidate_folder and source_file_count == 0:
            warnings.append("matched_folder_has_no_source_files")
        if not images_path:
            warnings.append("missing_product_images_path")

        if match_status == "matched_high_confidence" and images_path and candidate_folder and source_file_count > 0:
            action = "copy"
            reason = "high-confidence match with usable ProductDB images path and source files"
        elif infer_out_of_scope(product, match_status, brand, item_name, model_id):
            action = "skip"
            reason = "outside_current_inventory_scope"
        elif match_status == "matched_high_confidence" and not images_path:
            action = "skip"
            reason = "missing ProductDB images path"
        elif match_status in {"matched_possible", "matched_possible_low_confidence", "structure_rule_unclear"}:
            action = "manual_review"
            reason = "diagnostic match status is not safe for automatic copy planning"
        else:
            action = "skip"
            reason = "no usable source folder for current import stage"

        proposed_runtime_folder = images_path
        proposed_target_files = [f"{images_path.rstrip('/')}/{name}" if images_path else name for name in source_files]

        out.append(
            {
                "product_db_itemId": item_id,
                "product_brand": brand,
                "product_itemName": item_name,
                "product_model_id": model_id,
                "product_images_path": images_path,
                "product_thumbnails_json": thumbnails_json,
                "source_folder_relative_path": candidate_folder,
                "source_file_count": source_file_count,
                "source_files": to_json_list(source_files),
                "proposed_runtime_folder": proposed_runtime_folder,
                "proposed_target_files": to_json_list(proposed_target_files),
                "action": action,
                "reason": reason,
                "diagnostic_match_status": match_status,
                "diagnostic_match_score": match_score,
                "diagnostic_confidence_reason": confidence_reason,
                "diagnostic_warning": "; ".join(warnings),
            }
        )

    return out


def write_plan(path: Path, rows: list[dict[str, Any]]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    columns = [
        "product_db_itemId",
        "product_brand",
        "product_itemName",
        "product_model_id",
        "product_images_path",
        "product_thumbnails_json",
        "source_folder_relative_path",
        "source_file_count",
        "source_files",
        "proposed_runtime_folder",
        "proposed_target_files",
        "action",
        "reason",
        "diagnostic_match_status",
        "diagnostic_match_score",
        "diagnostic_confidence_reason",
        "diagnostic_warning",
    ]
    with path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=columns)
        writer.writeheader()
        for row in rows:
            writer.writerow(row)


def write_summary(path: Path, rows: list[dict[str, Any]]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    action_counts = Counter(row["action"] for row in rows)
    status_counts = Counter(row["diagnostic_match_status"] for row in rows)
    missing_images = [r for r in rows if "missing_product_images_path" in (r.get("diagnostic_warning") or "")]
    no_source_files = [r for r in rows if "matched_folder_has_no_source_files" in (r.get("diagnostic_warning") or "")]
    out_scope = [r for r in rows if r.get("reason") == "outside_current_inventory_scope"]
    product_ids = {(r.get("product_db_itemId") or "").strip() for r in rows if (r.get("product_db_itemId") or "").strip()}
    products_with_copy = {
        (r.get("product_db_itemId") or "").strip()
        for r in rows
        if r.get("action") == "copy" and (r.get("product_db_itemId") or "").strip()
    }
    products_with_manual_review = {
        (r.get("product_db_itemId") or "").strip()
        for r in rows
        if r.get("action") == "manual_review" and (r.get("product_db_itemId") or "").strip()
    }
    products_with_skip = {
        (r.get("product_db_itemId") or "").strip()
        for r in rows
        if r.get("action") == "skip" and (r.get("product_db_itemId") or "").strip()
    }
    products_with_only_skip = products_with_skip - products_with_copy - products_with_manual_review
    products_high_confidence_missing_images = {
        (r.get("product_db_itemId") or "").strip()
        for r in rows
        if r.get("diagnostic_match_status") == "matched_high_confidence"
        and "missing_product_images_path" in (r.get("diagnostic_warning") or "")
        and (r.get("product_db_itemId") or "").strip()
    }
    products_missing_images = {
        (r.get("product_db_itemId") or "").strip()
        for r in missing_images
        if (r.get("product_db_itemId") or "").strip()
    }
    products_no_source_files = {
        (r.get("product_db_itemId") or "").strip()
        for r in no_source_files
        if (r.get("product_db_itemId") or "").strip()
    }

    lines = [
        "# Image Import Plan Summary",
        "",
        "This report is generated by `tools-dev/plan_product_image_import.py`.",
        "It is a planning artifact only; no files were copied.",
        "",
        f"- Total plan rows / matcher candidate rows: {len(rows)}",
        f"- Distinct ProductDB products represented: {len(product_ids)}",
        "",
        "## Product-level coverage",
        f"- Distinct products with at least one `copy` row: {len(products_with_copy)}",
        f"- Distinct products with at least one `manual_review` row: {len(products_with_manual_review)}",
        f"- Distinct products with only `skip` rows: {len(products_with_only_skip)}",
        f"- Distinct products with high-confidence rows skipped due to missing ProductDB images path: {len(products_high_confidence_missing_images)}",
        "",
        "## Rows by action",
    ]
    for action, count in sorted(action_counts.items()):
        lines.append(f"- {action}: {count}")

    lines.extend(["", "## Rows by diagnostic match_status"])
    for status, count in sorted(status_counts.items()):
        lines.append(f"- {status or '[blank]'}: {count}")

    lines.extend(["", "## Warnings"])
    lines.append(
        f"- Missing ProductDB images paths: {len(missing_images)} rows across {len(products_missing_images)} distinct products"
    )
    lines.append(
        f"- Matched folders with no source files: {len(no_source_files)} rows across {len(products_no_source_files)} distinct products"
    )

    if out_scope:
        lines.extend(["", "## Products outside current inventory scope (identified)"])
        for row in out_scope:
            lines.append(f"- {row.get('product_itemName') or '[unnamed]'} (itemId: {row.get('product_db_itemId')})")

    with path.open("w", encoding="utf-8") as handle:
        handle.write("\n".join(lines) + "\n")



def run_smoke_check() -> None:
    product_rows = [
        {
            "db_itemId": "1001",
            "brand": "AS Colour",
            "itemName": "Stencil Hood",
            "model_id": "stencil_hood",
            "images": "catalog/products/as_colour/stencil_hood",
            "thumbnails_json": "[]",
            "categoryName": "Tops",
            "subCategory": "Hoodies",
        }
    ]
    match_rows = [
        {
            "product_db_itemId": "1001",
            "product_brand": "AS Colour",
            "product_itemName": "Stencil Hood",
            "product_model_id": "stencil_hood",
            "candidate_folder_relative_path": "tops/hoodies/stencil-hood",
            "match_status": "matched_high_confidence",
            "match_score": "0.98",
            "confidence_reason": "clean_slug_match",
            "warning": "matcher_warn_sample",
        },
        {
            "product_db_itemId": "1002",
            "product_brand": "AS Colour",
            "product_itemName": "Fallback Tee",
            "product_model_id": "fallback_tee",
            "candidate_folder_relative_path": "tops/tees/fallback-tee",
            "match_status": "structure_rule_unclear",
            "match_score": "0.41",
            "confidence_reason": "ambiguous_folder_structure",
            "warning": "needs_human_validation",
        },
    ]
    inv = {
        "tops/hoodies/stencil-hood": ["front.jpg", "back.jpg"],
        "tops/tees/fallback-tee": ["main.jpg"],
    }

    planned = plan_rows(match_rows, build_product_index(product_rows), inv)

    first = planned[0]
    assert first["product_db_itemId"] == "1001"
    assert first["action"] == "copy"
    assert first["proposed_runtime_folder"] == "catalog/products/as_colour/stencil_hood"
    assert "matcher_warn_sample" in first["diagnostic_warning"]

    second = planned[1]
    assert second["action"] == "manual_review"
    assert "needs_human_validation" in second["diagnostic_warning"]

    summary_path = Path("/tmp/image-import-plan-summary-smoke.md")
    write_summary(summary_path, planned)
    summary_text = summary_path.read_text(encoding="utf-8")
    assert "Total plan rows / matcher candidate rows: 2" in summary_text
    assert "Distinct ProductDB products represented: 2" in summary_text
    assert "Distinct products with at least one `copy` row: 1" in summary_text
    assert "Distinct products with at least one `manual_review` row: 1" in summary_text


def build_parser() -> argparse.ArgumentParser:
    p = argparse.ArgumentParser(
        description="Generate a local image import planning report (no file operations)."
    )
    p.add_argument("--match-review", type=Path, default=DEFAULT_MATCH_REVIEW)
    p.add_argument("--product-db", type=Path, default=DEFAULT_PRODUCT_DB)
    p.add_argument("--inventory", type=Path, default=DEFAULT_INVENTORY)
    p.add_argument("--out-csv", type=Path, default=DEFAULT_PLAN_CSV)
    p.add_argument("--out-summary", type=Path, default=DEFAULT_SUMMARY_MD)
    p.add_argument("--smoke-check", action="store_true", help="Run lightweight in-memory smoke assertions and exit.")
    return p


def main() -> int:
    args = build_parser().parse_args()
    if args.smoke_check:
        run_smoke_check()
        print("Smoke check passed.")
        return 0

    try:
        fail_if_missing([args.match_review, args.product_db, args.inventory])
    except FileNotFoundError as exc:
        print(f"ERROR: {exc}")
        return 2

    _, match_rows = read_csv_rows(args.match_review)
    _, product_rows = read_csv_rows(args.product_db)
    _, inventory_rows = read_csv_rows(args.inventory)

    inv = inventory_index(inventory_rows)
    pidx = build_product_index(product_rows)
    planned = plan_rows(match_rows, pidx, inv)

    write_plan(args.out_csv, planned)
    write_summary(args.out_summary, planned)
    print(f"Wrote plan CSV: {args.out_csv}")
    print(f"Wrote summary : {args.out_summary}")
    print("No files were copied.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
