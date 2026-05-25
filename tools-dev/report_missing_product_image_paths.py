#!/usr/bin/env python3
"""Create a local-only review report for ProductDB rows missing runtime image paths.

This script is reporting-only and does not modify ProductDB, images, or database records.
"""
from __future__ import annotations

import argparse
import csv
import json
import re
import tempfile
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

DEFAULT_PLAN = Path("docs/operations/generated/image-import-plan.csv")
DEFAULT_PRODUCT_DB = Path("docs/data/SportWarehouse_ProductDB.csv")
DEFAULT_OUT_CSV = Path("docs/operations/generated/missing-product-image-paths-review.csv")
DEFAULT_OUT_MD = Path("docs/operations/generated/missing-product-image-paths-summary.md")


def read_csv_rows(path: Path) -> tuple[list[str], list[dict[str, str]]]:
    with path.open("r", newline="", encoding="utf-8-sig") as handle:
        reader = csv.DictReader(handle)
        if not reader.fieldnames:
            raise ValueError(f"CSV has no header row: {path}")
        return list(reader.fieldnames), [dict(r) for r in reader]


def norm(v: str) -> str:
    return (v or "").strip()


def norm_path(v: str) -> str:
    return norm(v).replace("\\", "/")


def normalize_slug(value: str) -> str:
    value = norm(value).lower()
    value = re.sub(r"[^a-z0-9]+", "-", value).strip("-")
    return re.sub(r"-+", "-", value)


def parse_json_list(raw: str) -> list[str]:
    text = norm(raw)
    if not text:
        return []
    try:
        parsed = json.loads(text)
    except json.JSONDecodeError:
        return []
    if not isinstance(parsed, list):
        return []
    return [str(x) for x in parsed]


def has_blank_images_path(row: dict[str, str]) -> bool:
    return norm_path(row.get("images", "")).strip("/") == ""


def choose_best_plan_row(rows: list[dict[str, str]]) -> dict[str, str] | None:
    if not rows:
        return None

    def key_fn(r: dict[str, str]) -> tuple[int, int, int, int]:
        status = norm(r.get("diagnostic_match_status"))
        reason = norm(r.get("reason"))
        score_raw = norm(r.get("diagnostic_match_score"))
        try:
            score = float(score_raw)
        except ValueError:
            score = -1.0
        source_count = int(norm(r.get("source_file_count")) or "0")
        return (
            1 if status == "matched_high_confidence" else 0,
            1 if reason == "missing ProductDB images path" else 0,
            int(score * 1000),
            source_count,
        )

    return sorted(rows, key=key_fn, reverse=True)[0]



def derive_ryderwear_taxonomy(source_parts: list[str]) -> list[str]:
    """Extract stable taxonomy segments from Ryderwear source path."""
    if not source_parts:
        return []

    parts = source_parts[:]
    if parts and parts[0] == "ryderwear":
        parts = parts[1:]

    top_level = {"women", "men", "accessories"}
    category_tokens = {"tops", "bottoms", "sports-bra", "leggings", "shorts", "pilates-gym-bag"}

    taxonomy: list[str] = []
    for seg in parts:
        if seg in top_level:
            taxonomy = [seg]
            continue
        if seg in {"nkd", "non-nkd"} and taxonomy:
            taxonomy.append(seg)
            continue
        if seg in category_tokens and taxonomy:
            taxonomy.append(seg)
            continue

    # Keep a short, meaningful branch under accessories if available.
    if taxonomy and taxonomy[0] == "accessories" and len(taxonomy) < 3:
        for seg in parts:
            if seg not in {"accessories"} and seg not in taxonomy:
                taxonomy.append(seg)
            if len(taxonomy) >= 3:
                break

    # Guarantee minimum useful depth for known apparel patterns.
    if taxonomy and taxonomy[0] in {"women", "men"} and len(taxonomy) >= 2:
        if taxonomy[1] in {"nkd", "non-nkd"} and len(taxonomy) == 2:
            for seg in parts:
                if seg in {"tops", "bottoms"} and seg not in taxonomy:
                    taxonomy.append(seg)
                    break

    return taxonomy

def propose_images_path(product: dict[str, str], best_plan: dict[str, str] | None) -> tuple[str, str, str]:
    """Return proposed_path, review_action, review_note."""
    brand = normalize_slug(product.get("brand", ""))
    if not brand:
        return "", "manual_review", "brand missing; cannot create safe runtime path"

    source_folder = norm_path((best_plan or {}).get("source_folder_relative_path", "")).strip("/")
    source_parts = [normalize_slug(p) for p in source_folder.split("/") if normalize_slug(p)]

    gender = normalize_slug(product.get("gender", ""))
    age = normalize_slug(product.get("ageGroup", ""))
    audience = gender or age
    collection = normalize_slug(product.get("collection", "")) or normalize_slug(product.get("model_family", ""))
    subtype = normalize_slug(product.get("subCategory", "")) or normalize_slug(product.get("categoryName", ""))
    model = normalize_slug(product.get("model_id", "")) or normalize_slug(product.get("itemName", ""))

    parts: list[str] = ["images", "brands", brand]

    if brand == "ryderwear" and source_parts:
        taxonomy = derive_ryderwear_taxonomy(source_parts)
        parts.extend(taxonomy)
        if model:
            parts.append(model)
    else:
        for seg in [audience, collection, subtype or model]:
            if seg:
                parts.append(seg)

    if len(parts) < 5 and model and model not in parts:
        parts.append(model)

    if len(parts) < 5:
        return "", "manual_review", "insufficient structured fields to safely propose runtime path"

    return "/".join(parts).rstrip("/") + "/", "propose_path", "proposal only; requires reviewer approval"


def build_report_rows(plan_rows: list[dict[str, str]], product_rows: list[dict[str, str]]) -> list[dict[str, Any]]:
    plan_by_item: dict[str, list[dict[str, str]]] = {}
    for row in plan_rows:
        item_id = norm(row.get("product_db_itemId", ""))
        if item_id:
            plan_by_item.setdefault(item_id, []).append(row)

    report: list[dict[str, Any]] = []
    seen_item_ids: set[str] = set()
    for p in product_rows:
        item_id = norm(p.get("db_itemId", ""))
        if not item_id or item_id in seen_item_ids or not has_blank_images_path(p):
            continue
        seen_item_ids.add(item_id)

        candidates = plan_by_item.get(item_id, [])
        best = choose_best_plan_row(candidates)

        if best is None:
            priority = "no_candidate_missing_images"
            status = ""
            score = ""
            conf_reason = ""
            warning = "no import-plan candidate"
            source_folder = ""
            source_files = []
            source_count = 0
        else:
            status = norm(best.get("diagnostic_match_status", ""))
            score = norm(best.get("diagnostic_match_score", ""))
            conf_reason = norm(best.get("diagnostic_confidence_reason", ""))
            warning = norm(best.get("diagnostic_warning", ""))
            source_folder = norm_path(best.get("source_folder_relative_path", "")).strip("/")
            source_files = parse_json_list(best.get("source_files", ""))
            source_count = int(norm(best.get("source_file_count", "0")) or "0")
            if status == "matched_high_confidence":
                priority = "high_confidence_missing_images"
            else:
                priority = "lower_confidence_missing_images"

        proposed, action, note = propose_images_path(p, best)
        if priority == "no_candidate_missing_images":
            action = "manual_review"
            if not note:
                note = "no import-plan evidence; verify source media manually"

        report.append(
            {
                "product_db_itemId": item_id,
                "product_brand": norm(p.get("brand", "")),
                "product_itemName": norm(p.get("itemName", "")),
                "product_model_id": norm(p.get("model_id", "")),
                "product_gender": norm(p.get("gender", "")),
                "product_ageGroup": norm(p.get("ageGroup", "")),
                "product_categoryName": norm(p.get("categoryName", "")),
                "product_subCategory": norm(p.get("subCategory", "")),
                "product_collection": norm(p.get("collection", "")),
                "product_model_family": norm(p.get("model_family", "")),
                "current_product_images_path": norm_path(p.get("images", "")),
                "best_matched_source_folder_relative_path": source_folder,
                "source_file_count": source_count,
                "source_files": json.dumps(source_files, ensure_ascii=False),
                "proposed_images_path": proposed,
                "diagnostic_match_status": status,
                "diagnostic_match_score": score,
                "diagnostic_confidence_reason": conf_reason,
                "diagnostic_warning": warning,
                "priority": priority,
                "review_action": action,
                "review_note": note,
            }
        )

    proposed_index: dict[str, list[dict[str, Any]]] = defaultdict(list)
    for row in report:
        proposed = norm_path(str(row.get("proposed_images_path", ""))).strip()
        if proposed:
            proposed_index[proposed].append(row)

    for proposed, rows in proposed_index.items():
        if len(rows) < 2:
            continue
        item_ids = ", ".join(sorted(r["product_db_itemId"] for r in rows))
        for row in rows:
            row["review_action"] = "collision_review" if row.get("review_action") != "manual_review" else "manual_review"
            row["review_note"] = (
                f"duplicate proposed_images_path collision ({len(rows)} rows share '{proposed}'; itemIds: {item_ids}); "
                f"{norm(str(row.get('review_note', '')))}"
            ).strip("; ")

    rank = {
        "high_confidence_missing_images": 0,
        "lower_confidence_missing_images": 1,
        "no_candidate_missing_images": 2,
    }
    report.sort(key=lambda r: (rank.get(r["priority"], 9), r["product_brand"].lower(), r["product_db_itemId"]))
    return report


def write_csv(path: Path, rows: list[dict[str, Any]]) -> None:
    columns = [
        "product_db_itemId","product_brand","product_itemName","product_model_id","product_gender","product_ageGroup",
        "product_categoryName","product_subCategory","product_collection","product_model_family","current_product_images_path",
        "best_matched_source_folder_relative_path","source_file_count","source_files","proposed_images_path","diagnostic_match_status",
        "diagnostic_match_score","diagnostic_confidence_reason","diagnostic_warning","priority","review_action","review_note",
    ]
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=columns)
        w.writeheader()
        for row in rows:
            w.writerow(row)


def write_summary(path: Path, report_rows: list[dict[str, Any]], product_rows: list[dict[str, str]]) -> None:
    total = len(product_rows)
    blank = sum(1 for r in product_rows if has_blank_images_path(r))
    high = sum(1 for r in report_rows if r["priority"] == "high_confidence_missing_images")
    with_proposal = sum(1 for r in report_rows if norm(r["proposed_images_path"]))
    manual = sum(1 for r in report_rows if r["review_action"] == "manual_review")
    collisions = sum(1 for r in report_rows if r["review_action"] == "collision_review")
    dup_path_counts = Counter(
        norm_path(str(r.get("proposed_images_path", ""))).strip()
        for r in report_rows
        if norm_path(str(r.get("proposed_images_path", ""))).strip()
    )
    duplicate_paths = {p: c for p, c in dup_path_counts.items() if c > 1}
    by_brand = Counter((r["product_brand"] or "(blank)") for r in report_rows)
    by_priority = Counter(r["priority"] for r in report_rows)

    lines = [
        "# Missing Product Image Paths Review Summary",
        "",
        "Generated by `tools-dev/report_missing_product_image_paths.py`.",
        "",
        f"- Total ProductDB rows: {total}",
        f"- Total rows with blank/missing images path: {blank}",
        f"- High-confidence missing-images products: {high}",
        f"- Rows with proposed_images_path: {with_proposal}",
        f"- Rows needing manual_review: {manual}",
        f"- Rows flagged collision_review: {collisions}",
        f"- Duplicate non-blank proposed_images_path values: {len(duplicate_paths)}",
        "",
        "## Count by brand",
        "",
    ]
    for brand, count in sorted(by_brand.items(), key=lambda x: (-x[1], x[0].lower())):
        lines.append(f"- {brand}: {count}")
    lines += ["", "## Count by priority", ""]
    for p, c in sorted(by_priority.items()):
        lines.append(f"- {p}: {c}")

    lines += ["", "## Duplicate proposed path collisions", ""]
    if not duplicate_paths:
        lines.append("- none")
    else:
        for path_key, count in sorted(duplicate_paths.items(), key=lambda x: (-x[1], x[0])):
            lines.append(f"- {path_key}: {count}")

    lines += ["", "No ProductDB rows were modified by this script.", ""]

    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text("\n".join(lines), encoding="utf-8")


def run_smoke_check() -> None:
    with tempfile.TemporaryDirectory() as td:
        root = Path(td)
        plan = root / "plan.csv"
        pdb = root / "product.csv"
        out_csv = root / "out.csv"
        out_md = root / "out.md"

        plan.write_text(
            "\n".join([
                "product_db_itemId,reason,diagnostic_match_status,diagnostic_match_score,diagnostic_confidence_reason,diagnostic_warning,source_folder_relative_path,source_file_count,source_files",
                '1001,missing ProductDB images path,matched_high_confidence,0.98,strong,missing_product_images_path,ryderwear/Women/Non-NKD/Bottoms/Leggings/Rise/High_Waisted,2,"[""a.jpg"",""b.jpg""]"',
                '1002,missing ProductDB images path,matched_high_confidence,0.96,strong,missing_product_images_path,ryderwear/Women/Non-NKD/Bottoms/Leggings/Core/Fit,3,"[""c.jpg""]"',
                '1005,missing ProductDB images path,matched_high_confidence,0.97,strong,missing_product_images_path,ryderwear/Women/Non-NKD/Bottoms/Leggings/New/Drop,3,"[""d.jpg""]"',
                '1006,missing ProductDB images path,matched_possible,0.66,weak,missing_product_images_path,nike/men/tees,1,"[""e.jpg""]"',
                '1007,missing ProductDB images path,matched_high_confidence,0.95,strong,missing_product_images_path,ryderwear/Women/Non-NKD/Bottoms/Leggings/Alt,2,"[""f.jpg""]"',
            ]),
            encoding="utf-8",
        )
        pdb.write_text(
            "\n".join([
                "db_itemId,brand,itemName,model_id,gender,ageGroup,categoryName,subCategory,collection,model_family,images",
                "1001,Ryderwear,Empower Legging,RYDERWEAR_FEMALE_EMPOWER_HIGH_WAISTED_LEGGINGS,women,adult,Apparel,Bottoms,Non-NKD,Empower,",
                "1001,Ryderwear,Empower Legging,RYDERWEAR_FEMALE_EMPOWER_HIGH_WAISTED_LEGGINGS,women,adult,Apparel,Bottoms,Non-NKD,Empower,",
                "1002,Ryderwear,Focus Legging,RYDERWEAR_FEMALE_FOCUS_HIGH_WAISTED_LEGGINGS,women,adult,Apparel,Bottoms,Non-NKD,Focus,",
                "1003,Adidas,HasPath,HAS_PATH,men,adult,Apparel,Tops,,Core,images/brands/adidas/men/tops/",
                "1004,Puma,NoCandidate,NO_CAND,men,adult,Apparel,Tops,,Core,",
                "1005,Ryderwear,Collision A,duplicate-model,women,adult,Apparel,Bottoms,Non-NKD,Collision,",
                "1007,Ryderwear,Collision B,duplicate-model,women,adult,Apparel,Bottoms,Non-NKD,Collision,",
                "1006,Nike,Club Tee,CLUB_TEE,men,adult,Apparel,Tops,,Club,",
            ]),
            encoding="utf-8",
        )

        _, plan_rows = read_csv_rows(plan)
        _, product_rows = read_csv_rows(pdb)
        report = build_report_rows(plan_rows, product_rows)
        by_id = {r["product_db_itemId"]: r for r in report}

        assert len(report) == 6, "rows should be deduplicated by product_db_itemId"
        assert by_id["1001"]["priority"] == "high_confidence_missing_images"
        assert "1003" not in by_id, "products with existing images path must be excluded"
        assert by_id["1006"]["priority"] == "lower_confidence_missing_images"
        assert by_id["1004"]["priority"] == "no_candidate_missing_images"

        assert by_id["1001"]["proposed_images_path"] != by_id["1002"]["proposed_images_path"]
        assert by_id["1001"]["proposed_images_path"].endswith("/ryderwear-female-empower-high-waisted-leggings/")
        assert by_id["1005"]["review_action"] in {"collision_review", "manual_review"}
        assert "duplicate proposed_images_path collision" in by_id["1005"]["review_note"]
        assert "duplicate proposed_images_path collision" in by_id["1007"]["review_note"]

        write_csv(out_csv, report)
        write_summary(out_md, report, product_rows)
        summary = out_md.read_text(encoding="utf-8")
        assert "Duplicate non-blank proposed_images_path values:" in summary
        assert "Rows flagged collision_review:" in summary
        assert "No ProductDB rows were modified" in summary


def main() -> None:
    parser = argparse.ArgumentParser(description="Report ProductDB rows missing runtime image paths using local import-plan evidence.")
    parser.add_argument("--plan-csv", type=Path, default=DEFAULT_PLAN)
    parser.add_argument("--product-db-csv", type=Path, default=DEFAULT_PRODUCT_DB)
    parser.add_argument("--out-csv", type=Path, default=DEFAULT_OUT_CSV)
    parser.add_argument("--out-summary", type=Path, default=DEFAULT_OUT_MD)
    parser.add_argument("--smoke-check", action="store_true")
    args = parser.parse_args()

    if args.smoke_check:
        run_smoke_check()
        print("Smoke check passed.")
        return

    _, plan_rows = read_csv_rows(args.plan_csv)
    _, product_rows = read_csv_rows(args.product_db_csv)
    report = build_report_rows(plan_rows, product_rows)
    write_csv(args.out_csv, report)
    write_summary(args.out_summary, report, product_rows)
    print(f"Wrote {len(report)} rows to {args.out_csv}")
    print(f"Wrote summary to {args.out_summary}")


if __name__ == "__main__":
    main()
