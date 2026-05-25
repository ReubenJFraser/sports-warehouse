#!/usr/bin/env python3
"""Copy reviewed product media from image-import plan.

Local-only utility. Safe by default (dry-run unless --execute).
"""
from __future__ import annotations

import argparse
import csv
import json
import shutil
import tempfile
from dataclasses import dataclass
from pathlib import Path
from typing import Any

ALLOWED_EXTENSIONS = {".png", ".jpg", ".jpeg", ".webp", ".mp4"}
DEFAULT_PLAN = Path("docs/operations/generated/image-import-plan.csv")
DEFAULT_OUT_CSV = Path("docs/operations/generated/image-import-copy-report.csv")
DEFAULT_OUT_SUMMARY = Path("docs/operations/generated/image-import-copy-summary.md")


@dataclass
class CopyResult:
    product_db_itemId: str
    product_brand: str
    product_itemName: str
    source_file: str
    source_abs_path: str
    target_rel_path: str
    target_abs_path: str
    source_exists: str
    target_exists: str
    action_taken: str
    reason: str


def normalize_rel(value: str) -> str:
    return (value or "").strip().replace("\\", "/").strip("/")


def parse_json_list(raw: str, field_name: str) -> list[str]:
    text = (raw or "").strip()
    if not text:
        return []
    parsed = json.loads(text)
    if not isinstance(parsed, list):
        raise ValueError(f"{field_name} must be a JSON list")
    out: list[str] = []
    for item in parsed:
        if not isinstance(item, str):
            raise ValueError(f"{field_name} entries must be strings")
        out.append(item)
    return out


def read_plan_rows(path: Path) -> list[dict[str, str]]:
    with path.open("r", newline="", encoding="utf-8-sig") as handle:
        reader = csv.DictReader(handle)
        if not reader.fieldnames:
            raise ValueError(f"CSV has no header row: {path}")
        return [dict(row) for row in reader]


def is_allowed_media_file(filename: str) -> bool:
    return Path(filename).suffix.lower() in ALLOWED_EXTENSIONS


def process_plan(rows: list[dict[str, str]], source_root: Path, repo_root: Path, execute: bool) -> tuple[list[CopyResult], int]:
    results: list[CopyResult] = []
    copy_rows_processed = 0

    for row in rows:
        if (row.get("action") or "").strip() != "copy":
            continue
        copy_rows_processed += 1

        product_id = (row.get("product_db_itemId") or "").strip()
        product_brand = (row.get("product_brand") or "").strip()
        product_name = (row.get("product_itemName") or "").strip()

        src_folder_rel = normalize_rel(row.get("source_folder_relative_path") or "")
        source_folder = source_root / src_folder_rel

        source_files = parse_json_list(row.get("source_files") or "", "source_files")
        target_files = parse_json_list(row.get("proposed_target_files") or "", "proposed_target_files")

        if len(source_files) != len(target_files):
            for index, source_file in enumerate(source_files):
                results.append(
                    CopyResult(
                        product_id,
                        product_brand,
                        product_name,
                        source_file,
                        str((source_folder / source_file).resolve()),
                        "",
                        "",
                        "false",
                        "false",
                        "error",
                        "source_files/proposed_target_files length mismatch",
                    )
                )
            continue

        for source_file, target_rel_raw in zip(source_files, target_files):
            source_rel_name = Path(source_file).name
            target_rel_path = normalize_rel(target_rel_raw)
            source_abs = source_folder / source_rel_name
            target_abs = repo_root / Path(target_rel_path)

            if not is_allowed_media_file(source_rel_name):
                results.append(
                    CopyResult(
                        product_id,
                        product_brand,
                        product_name,
                        source_rel_name,
                        str(source_abs.resolve()),
                        target_rel_path,
                        str(target_abs.resolve()),
                        str(source_abs.exists()).lower(),
                        str(target_abs.exists()).lower(),
                        "error",
                        f"unsupported media extension: {Path(source_rel_name).suffix}",
                    )
                )
                continue

            if not source_abs.exists():
                results.append(
                    CopyResult(
                        product_id,
                        product_brand,
                        product_name,
                        source_rel_name,
                        str(source_abs.resolve()),
                        target_rel_path,
                        str(target_abs.resolve()),
                        "false",
                        str(target_abs.exists()).lower(),
                        "missing_source",
                        "source file not found",
                    )
                )
                continue

            if target_abs.exists():
                results.append(
                    CopyResult(
                        product_id,
                        product_brand,
                        product_name,
                        source_rel_name,
                        str(source_abs.resolve()),
                        target_rel_path,
                        str(target_abs.resolve()),
                        "true",
                        "true",
                        "skipped_existing",
                        "target already exists; overwrite disabled",
                    )
                )
                continue

            if execute:
                try:
                    target_abs.parent.mkdir(parents=True, exist_ok=True)
                    shutil.copy2(source_abs, target_abs)
                    action_taken = "copied"
                    reason = "copied in execute mode"
                except Exception as exc:  # noqa: BLE001
                    action_taken = "error"
                    reason = f"copy failed: {exc}"
            else:
                action_taken = "dry_run_copy"
                reason = "dry-run mode; no files copied"

            results.append(
                CopyResult(
                    product_id,
                    product_brand,
                    product_name,
                    source_rel_name,
                    str(source_abs.resolve()),
                    target_rel_path,
                    str(target_abs.resolve()),
                    "true",
                    "false",
                    action_taken,
                    reason,
                )
            )

    return results, copy_rows_processed


def write_report(path: Path, results: list[CopyResult]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    columns = [
        "product_db_itemId",
        "product_brand",
        "product_itemName",
        "source_file",
        "source_abs_path",
        "target_rel_path",
        "target_abs_path",
        "source_exists",
        "target_exists",
        "action_taken",
        "reason",
    ]
    with path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=columns)
        writer.writeheader()
        for result in results:
            writer.writerow(result.__dict__)


def write_summary(path: Path, mode: str, copy_rows_processed: int, results: list[CopyResult]) -> None:
    total_files = len(results)
    copied = sum(1 for r in results if r.action_taken == "copied")
    dry_run = sum(1 for r in results if r.action_taken == "dry_run_copy")
    missing = sum(1 for r in results if r.action_taken == "missing_source")
    skipped = sum(1 for r in results if r.action_taken == "skipped_existing")
    errors = sum(1 for r in results if r.action_taken == "error")
    copied_or_would = copied if mode == "execute" else dry_run

    explicit = "Files were copied." if mode == "execute" and copied > 0 else "No files were copied."

    path.parent.mkdir(parents=True, exist_ok=True)
    lines = [
        "# Image Import Copy Summary",
        "",
        f"- mode: {mode}",
        f"- total copy-plan rows processed: {copy_rows_processed}",
        f"- total files proposed: {total_files}",
        f"- total files that {'copied' if mode == 'execute' else 'would be copied'}: {copied_or_would}",
        f"- total missing source files: {missing}",
        f"- total existing target files skipped: {skipped}",
        f"- total errors: {errors}",
        f"- explicit copy status: {explicit}",
        "",
    ]
    path.write_text("\n".join(lines), encoding="utf-8")


def run_smoke_check() -> int:
    with tempfile.TemporaryDirectory(prefix="copy-plan-smoke-") as tmp:
        root = Path(tmp)
        source_root = root / "source"
        repo_root = root / "repo"
        plan_path = root / "plan.csv"
        out_csv = root / "report.csv"
        out_md = root / "summary.md"

        (source_root / "brand/item").mkdir(parents=True)
        (repo_root / "runtime/cache/catalog/abc").mkdir(parents=True)

        (source_root / "brand/item/img1.jpg").write_bytes(b"jpg")
        (source_root / "brand/item/video.mp4").write_bytes(b"mp4")
        existing_target = repo_root / "runtime/cache/catalog/abc/video.mp4"
        existing_target.write_bytes(b"existing")

        rows = [
            {
                "product_db_itemId": "1",
                "product_brand": "Brand",
                "product_itemName": "Item",
                "source_folder_relative_path": "brand/item",
                "source_files": json.dumps(["img1.jpg", "missing.png", "video.mp4"]),
                "proposed_target_files": json.dumps(
                    [
                        "runtime/cache/catalog/abc/img1.jpg",
                        "runtime/cache/catalog/abc/missing.png",
                        "runtime/cache/catalog/abc/video.mp4",
                    ]
                ),
                "action": "copy",
            }
        ]
        with plan_path.open("w", newline="", encoding="utf-8") as handle:
            writer = csv.DictWriter(handle, fieldnames=list(rows[0].keys()))
            writer.writeheader()
            writer.writerows(rows)

        # Dry run: ensure no file created
        parsed = read_plan_rows(plan_path)
        dry_results, dry_count = process_plan(parsed, source_root, repo_root, execute=False)
        assert dry_count == 1
        assert any(r.action_taken == "dry_run_copy" for r in dry_results)
        assert any(r.action_taken == "missing_source" for r in dry_results)
        assert any(r.action_taken == "skipped_existing" for r in dry_results)
        assert not (repo_root / "runtime/cache/catalog/abc/img1.jpg").exists()

        # Execute: one file copied, existing not overwritten
        exe_results, _ = process_plan(parsed, source_root, repo_root, execute=True)
        write_report(out_csv, exe_results)
        write_summary(out_md, "execute", 1, exe_results)
        copied_file = repo_root / "runtime/cache/catalog/abc/img1.jpg"
        assert copied_file.exists()
        assert existing_target.read_bytes() == b"existing"
        assert any(r.action_taken == "copied" for r in exe_results)

    print("Smoke check passed")
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="Copy reviewed product media from image import plan (local-only).")
    parser.add_argument("--plan", type=Path, default=DEFAULT_PLAN)
    parser.add_argument("--source-root", type=Path, required=False)
    parser.add_argument("--repo-root", type=Path, default=Path("."))
    parser.add_argument("--out-csv", type=Path, default=DEFAULT_OUT_CSV)
    parser.add_argument("--out-summary", type=Path, default=DEFAULT_OUT_SUMMARY)
    parser.add_argument("--execute", action="store_true", help="Actually copy files. Default is dry-run.")
    parser.add_argument("--smoke-check", action="store_true", help="Run internal smoke checks and exit.")
    args = parser.parse_args()

    if args.smoke_check:
        return run_smoke_check()

    if not args.source_root:
        parser.error("--source-root is required unless --smoke-check is used")

    if not args.plan.exists():
        raise FileNotFoundError(f"Plan CSV not found: {args.plan}")

    rows = read_plan_rows(args.plan)
    mode = "execute" if args.execute else "dry-run"
    results, copy_rows_processed = process_plan(rows, args.source_root, args.repo_root, execute=args.execute)

    write_report(args.out_csv, results)
    write_summary(args.out_summary, mode, copy_rows_processed, results)
    print(f"Completed {mode}. Wrote report: {args.out_csv} and summary: {args.out_summary}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
