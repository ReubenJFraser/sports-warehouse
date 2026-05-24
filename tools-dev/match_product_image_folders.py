#!/usr/bin/env python3
"""Stage 2 contract-aware, structure-aware product/image folder matching report."""
from __future__ import annotations

import argparse
import csv
import re
from collections import Counter, defaultdict
from dataclasses import dataclass
from pathlib import Path

IMAGE_EXTS = {".png", ".jpg", ".jpeg", ".webp", ".gif", ".avif"}
STATUS = {
    "high": "matched_high_confidence",
    "possible": "matched_possible",
    "low_candidate": "matched_possible_low_confidence",
    "unmatched_product_candidates": "unmatched_product_with_candidates",
    "unmatched_product": "unmatched_product",
    "unmatched_folder": "unmatched_folder",
}

TOP_CANDIDATES_PER_PRODUCT = 5
MIN_MEANINGFUL_SCORE = 8


@dataclass
class FolderRec:
    rel: str
    abs: str
    segments: list[str]
    model_root_segments: list[str]
    images: list[str]
    likely_variant_segment: str
    likely_model_root: str


def norm(s: str) -> str:
    s = (s or "").strip().lower().replace("&", " and ")
    s = re.sub(r"[^a-z0-9]+", "_", s)
    s = re.sub(r"_+", "_", s).strip("_")
    return s


def token_variants(value: str) -> set[str]:
    nv = norm(value)
    if not nv or nv in {"null", "none", "na", "n_a"}:
        return set()
    parts = [p for p in nv.split("_") if p]
    variants = {nv}
    variants.update(parts)
    return variants


def parse_inventory(path: Path, repo_root: Path) -> list[FolderRec]:
    rows: list[tuple[str, str]] = []
    with path.open(newline="", encoding="utf-8-sig") as f:
        reader = csv.DictReader(f)
        headers = {h.lower(): h for h in (reader.fieldnames or [])}
        rel_key = headers.get("relativepath") or headers.get("relative_path") or headers.get("path") or headers.get("file_path") or headers.get("filepath")
        abs_key = headers.get("fullname") or headers.get("full_name")
        type_key = headers.get("type")
        ext_key = headers.get("extension")
        if not rel_key and not abs_key:
            raise ValueError("Inventory CSV needs RelativePath/FullName or path/file_path columns.")

        for r in reader:
            if type_key:
                t = (r.get(type_key) or "").strip().upper()
                if t and t != "FILE":
                    continue

            rel_val = (r.get(rel_key) or "").strip().replace('\\', '/') if rel_key else ""
            abs_val = (r.get(abs_key) or "").strip().replace('\\', '/') if abs_key else ""
            chosen = rel_val or abs_val
            if not chosen:
                continue

            ext = (r.get(ext_key) or "").strip().lower() if ext_key else ""
            if ext and not ext.startswith("."):
                ext = f".{ext}"
            if not ext:
                ext = Path(chosen).suffix.lower()
            if ext not in IMAGE_EXTS:
                continue

            rows.append((rel_val or chosen, abs_val))

    by_folder: dict[str, list[str]] = defaultdict(list)
    abs_by_folder: dict[str, str] = {}
    for rel_path, abs_path in rows:
        rel_norm = rel_path.lstrip("/")
        folder = str(Path(rel_norm).parent).rstrip("/")
        by_folder[folder].append(Path(rel_norm).name)
        if abs_path:
            abs_by_folder.setdefault(folder, str(Path(abs_path).parent))

    recs = []
    for folder, imgs in sorted(by_folder.items()):
        seg = [s for s in folder.split('/') if s]
        variant = seg[-1] if seg else ""
        model_root_segments = seg[:-1] if len(seg) > 1 else seg
        model_root = '/'.join(model_root_segments)
        recs.append(
            FolderRec(
                rel=folder,
                abs=abs_by_folder.get(folder, str((repo_root / folder).resolve())),
                segments=seg,
                model_root_segments=model_root_segments,
                images=sorted(imgs),
                likely_variant_segment=variant,
                likely_model_root=model_root,
            )
        )
    return recs


def product_signal_specs(prod: dict[str, str]) -> list[tuple[str, int, set[str]]]:
    def tv(key: str) -> set[str]:
        return token_variants(prod.get(key, ""))

    subcat_norm = norm(prod.get("subCategory", ""))
    specs: list[tuple[str, int, set[str]]] = [
        ("model_id", 26, token_variants(prod.get("model_id", ""))),
        ("brand", 16, tv("brand")),
        ("gender", 10, tv("gender")),
        ("collection", 12, tv("collection")),
        ("subCategory", 16, tv("subCategory")),
        ("model_family", 12, tv("model_family")),
        ("fabric", 9, tv("fabric")),
        ("construction", 9, tv("construction")),
        ("rise", 8, tv("rise")),
        ("length", 8, tv("length")),
        ("neckline", 7, tv("neckline")),
        ("strap_configuration", 7, tv("strap_configuration")),
        ("variant", 8, tv("variant")),
        ("categoryName", 8, tv("categoryName")),
        ("subCategoryParent", 7, tv("subCategoryParent")),
        ("ageGroup", 5, tv("ageGroup")),
        ("sizeType", 5, tv("sizeType")),
        ("fitStyle", 5, tv("fitStyle")),
        ("activityTags", 5, tv("activityTags")),
    ]
    if subcat_norm == "sports_bra":
        specs.append(("support_level", 9, tv("support_level")))
    if norm(prod.get("scrunchFlag", "")) in {"1", "true", "yes", "y", "scrunch"}:
        specs.append(("scrunchFlag", 6, {"scrunch"}))
    if norm(prod.get("invisibleFlag", "")) in {"1", "true", "yes", "y", "invisible"}:
        specs.append(("invisibleFlag", 6, {"invisible"}))
    return [(n, w, t) for n, w, t in specs if t]


def score_product_folder(prod: dict, folder: FolderRec) -> tuple[int, list[str], list[str], str]:
    score = 0
    hit: list[str] = []
    miss: list[str] = []
    root_tokens = set()
    for s in folder.model_root_segments:
        root_tokens |= token_variants(s)
    variant_tokens = token_variants(folder.likely_variant_segment)
    all_tokens = root_tokens | variant_tokens

    for field, weight, tokens in product_signal_specs(prod):
        inter = tokens & all_tokens
        if inter:
            root_inter = tokens & root_tokens
            gain = weight if root_inter else max(2, int(weight * 0.6))
            score += gain
            where = "model_root" if root_inter else "variant_segment"
            hit.append(f"{field}({where}):{','.join(sorted(inter))}")
        else:
            miss.append(field)

    model_id_norm = norm(prod.get("model_id", ""))
    model_root_joined = norm("_".join(folder.model_root_segments))
    if model_id_norm and model_id_norm in model_root_joined:
        score += 18
        hit.append("model_id_exact_in_model_root")
    elif model_id_norm and model_id_norm in norm("_".join(folder.segments)):
        score += 10
        hit.append("model_id_exact_in_full_path")
    elif model_id_norm:
        miss.append("model_id_exact_path_absent")

    notes = "Contract 23 aligned: terminal folder treated as likely colour/variant; identity compared primarily against parent model-root path."
    return score, hit, sorted(set(miss)), notes


def confidence(score: int) -> str:
    return "high" if score >= 62 else "possible" if score >= 40 else "low"


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--inventory", default="clothes_folder_inventory.csv")
    ap.add_argument("--product-db", default="docs/data/SportWarehouse_ProductDB.csv")
    ap.add_argument("--output-csv", default="docs/operations/generated/image-product-folder-match-review.csv")
    ap.add_argument("--output-summary", default="docs/operations/generated/image-product-folder-match-summary.md")
    args = ap.parse_args()

    repo_root = Path.cwd()
    inv_path = Path(args.inventory)
    if not inv_path.exists():
        raise SystemExit(f"Missing inventory file: {inv_path}")

    folders = parse_inventory(inv_path, repo_root)
    with Path(args.product_db).open(newline="", encoding="utf-8-sig") as f:
        products = list(csv.DictReader(f))

    out_rows = []
    matched_folder_paths = set()
    statuses = Counter()
    products_with_candidates = 0

    for p in products:
        scored = []
        for fr in folders:
            sc, hit, miss, notes = score_product_folder(p, fr)
            if sc >= MIN_MEANINGFUL_SCORE:
                scored.append((sc, fr, hit, miss, notes))

        scored.sort(key=lambda x: x[0], reverse=True)
        top_candidates = scored[:TOP_CANDIDATES_PER_PRODUCT]

        if not top_candidates:
            statuses[STATUS["unmatched_product"]] += 1
            out_rows.append({
                "product_db_itemId": p.get("db_itemId", ""), "product_brand": p.get("brand", ""), "product_itemName": p.get("itemName", ""),
                "product_model_id": p.get("model_id", ""), "product_collection": p.get("collection", ""), "product_subCategory": p.get("subCategory", ""),
                "product_categoryName": p.get("categoryName", ""), "product_variant": p.get("variant", ""),
                "candidate_rank": "", "match_status": STATUS["unmatched_product"], "warning": "No meaningful candidates found.",
            })
            continue

        products_with_candidates += 1
        top_score = top_candidates[0][0]
        top_conf = confidence(top_score)
        status = STATUS["high"] if top_conf == "high" else STATUS["possible"] if top_conf == "possible" else STATUS["unmatched_product_candidates"]
        if top_conf == "low":
            status = STATUS["low_candidate"]
        statuses[status] += 1

        for idx, (sc, fr, hit, miss, notes) in enumerate(top_candidates, start=1):
            matched_folder_paths.add(fr.rel)
            c_conf = confidence(sc)
            out_rows.append({
                "product_db_itemId": p.get("db_itemId", ""), "product_brand": p.get("brand", ""), "product_itemName": p.get("itemName", ""),
                "product_model_id": p.get("model_id", ""), "product_collection": p.get("collection", ""), "product_subCategory": p.get("subCategory", ""),
                "product_categoryName": p.get("categoryName", ""), "product_variant": p.get("variant", ""),
                "candidate_rank": str(idx), "candidate_folder_relative_path": fr.rel, "candidate_folder_absolute_path": fr.abs,
                "candidate_path_segments_semicolon_list": ";".join(fr.segments), "likely_model_root_relative_path": fr.likely_model_root,
                "likely_colour_or_variant_segment": fr.likely_variant_segment, "image_count": str(len(fr.images)),
                "image_files_semicolon_list": ";".join(fr.images), "match_status": status, "match_confidence": c_conf,
                "match_score": str(sc), "matched_signals": ";".join(hit), "missing_expected_signals": ";".join(miss),
                "contract_or_structure_notes": notes, "warning": "Low-confidence candidate set; intentionally reported for review." if top_conf == "low" else "",
            })

    for fr in folders:
        if fr.rel in matched_folder_paths:
            continue
        statuses[STATUS["unmatched_folder"]] += 1
        out_rows.append({"product_db_itemId": "", "product_brand": "", "product_itemName": "", "product_model_id": "", "product_collection": "", "product_subCategory": "", "product_categoryName": "", "product_variant": "", "candidate_rank": "", "candidate_folder_relative_path": fr.rel, "candidate_folder_absolute_path": fr.abs, "candidate_path_segments_semicolon_list": ";".join(fr.segments), "likely_model_root_relative_path": fr.likely_model_root, "likely_colour_or_variant_segment": fr.likely_variant_segment, "image_count": str(len(fr.images)), "image_files_semicolon_list": ";".join(fr.images), "match_status": STATUS["unmatched_folder"], "match_confidence": "low", "match_score": "0", "matched_signals": "", "missing_expected_signals": "", "contract_or_structure_notes": "Unreferenced by product candidate set.", "warning": ""})

    out_csv = Path(args.output_csv)
    out_csv.parent.mkdir(parents=True, exist_ok=True)
    fields = ["product_db_itemId", "product_brand", "product_itemName", "product_model_id", "product_collection", "product_subCategory", "product_categoryName", "product_variant", "candidate_rank", "candidate_folder_relative_path", "candidate_folder_absolute_path", "candidate_path_segments_semicolon_list", "likely_model_root_relative_path", "likely_colour_or_variant_segment", "image_count", "image_files_semicolon_list", "match_status", "match_confidence", "match_score", "matched_signals", "missing_expected_signals", "contract_or_structure_notes", "warning"]
    with out_csv.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fields)
        w.writeheader()
        w.writerows(out_rows)

    non_blank_model_ids = [norm(p.get("model_id", "")) for p in products if norm(p.get("model_id", ""))]
    dup_count = sum(1 for _, c in Counter(non_blank_model_ids).items() if c > 1)
    patterns = Counter('/'.join(f.segments[:min(4, len(f.segments))]) for f in folders if f.segments)

    summary = Path(args.output_summary)
    with summary.open("w", encoding="utf-8") as f:
        f.write("# Stage 2 Image Folder ↔ Product Matching Summary\n\n")
        f.write("- Matching is Contract 22/23 aligned and structure-aware.\n")
        f.write("- Low-confidence candidates are deliberately emitted for review diagnostics.\n")
        f.write(f"- Source files used: `{args.inventory}`, `{args.product_db}`\n\n")
        f.write("## Totals\n")
        f.write(f"- total product rows: {len(products)}\n")
        f.write(f"- distinct non-blank model_id values: {len(set(non_blank_model_ids))}\n")
        f.write(f"- duplicate model_id count: {dup_count}\n")
        f.write(f"- total image-containing folders: {len(folders)}\n")
        f.write(f"- count of products with at least one candidate: {products_with_candidates}\n")
        f.write(f"- count of products with no candidates: {len(products) - products_with_candidates}\n\n")
        f.write("## Counts by match_status\n")
        for k, v in sorted(statuses.items()):
            f.write(f"- {k}: {v}\n")
        f.write("\n## Top observed folder patterns\n")
        for ptn, count in patterns.most_common(12):
            f.write(f"- `{ptn}`: {count}\n")


if __name__ == "__main__":
    main()
