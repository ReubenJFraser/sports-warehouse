#!/usr/bin/env python3
"""Stage 2 contract-aware, structure-aware product/image folder matching report."""
from __future__ import annotations
import argparse, csv, re
from collections import Counter, defaultdict
from dataclasses import dataclass
from pathlib import Path

IMAGE_EXTS = {".png", ".jpg", ".jpeg", ".webp", ".gif", ".avif"}
STATUS = {
    "high": "matched_high_confidence",
    "possible": "matched_possible",
    "ambiguous": "ambiguous_multiple_candidates",
    "unmatched_product": "unmatched_product",
    "unmatched_folder": "unmatched_folder",
    "variant_review": "variant_level_folder_needs_model_parent_review",
    "identity_review": "identity_review_required",
    "structure_unclear": "structure_rule_unclear",
}

@dataclass
class FolderRec:
    rel: str
    abs: str
    segments: list[str]
    images: list[str]
    likely_variant_segment: str
    likely_model_root: str


def norm(s: str) -> str:
    s = (s or "").strip().lower().replace("&", " and ")
    s = re.sub(r"[^a-z0-9]+", " ", s)
    return re.sub(r"\s+", " ", s).strip()

def tokset(*vals: str) -> set[str]:
    out = set()
    for v in vals:
        nv = norm(v)
        if not nv or nv in {"null", "none", "na"}:
            continue
        out.update(nv.split())
    return out

def parse_inventory(path: Path, repo_root: Path) -> list[FolderRec]:
    rows = []
    with path.open(newline="", encoding="utf-8-sig") as f:
        reader = csv.DictReader(f)
        headers = {h.lower(): h for h in (reader.fieldnames or [])}
        path_key = (
            headers.get("relativepath")
            or headers.get("relative_path")
            or headers.get("path")
            or headers.get("file_path")
            or headers.get("filepath")
            or headers.get("fullname")
            or headers.get("full_name")
        )
        type_key = headers.get("type")
        ext_key = headers.get("extension")
        if not path_key:
            raise ValueError("Inventory CSV needs path-like column (e.g. RelativePath, FullName, path, or file_path).")
        for r in reader:
            row_type = (r.get(type_key) or "").strip().upper() if type_key else ""
            if row_type and row_type != "FILE":
                continue
            p = (r.get(path_key) or "").strip().replace('\\', '/')
            if not p:
                continue
            if path_key.lower() in {"fullname", "full_name"}:
                try:
                    p = str(Path(p).resolve().relative_to(repo_root.resolve())).replace("\\", "/")
                except Exception:
                    p = str(Path(p).resolve())
            ext = (r.get(ext_key) or "").strip().lower() if ext_key else ""
            ext = f".{ext}" if ext and not ext.startswith(".") else ext
            ext = ext or Path(p).suffix.lower()
            if ext not in IMAGE_EXTS:
                continue
            rows.append(p)
    by_folder = defaultdict(list)
    for rp in rows:
        folder = str(Path(rp).parent).rstrip("/")
        by_folder[folder].append(Path(rp).name)
    recs = []
    for folder, imgs in sorted(by_folder.items()):
        seg = [s for s in folder.split('/') if s]
        variant = seg[-1] if seg else ""
        model_root = '/'.join(seg[:-1]) if len(seg) > 1 else folder
        recs.append(FolderRec(folder, str((repo_root / folder).resolve()), seg, sorted(imgs), variant, model_root))
    return recs

def score_product_folder(prod: dict, folder: FolderRec) -> tuple[int, list[str], list[str]]:
    score = 0; hit=[]; miss=[]
    folder_tokens = tokset(*folder.segments)
    folder_norm_path = norm(folder.rel).replace(" ", "_")
    model_id = (prod.get("model_id", "") or "").strip().lower()
    if model_id:
        if model_id in folder.rel.lower():
            score += 24
            hit.append("model_id:full_identity_path_match")
        else:
            miss.append("model_id_full_identity")
    checks = [
        ("brand", 20), ("gender", 8), ("collection", 12), ("subCategory", 14),
        ("categoryName", 8), ("construction", 8), ("rise", 6), ("length", 5), ("variant", 8)
    ]
    for col, pts in checks:
        pt = tokset(prod.get(col, ""))
        if not pt:
            continue
        inter = pt & folder_tokens
        if inter:
            gain = min(pts, max(3, len(inter)*3))
            score += gain
            hit.append(f"{col}:{','.join(sorted(inter))}")
        else:
            miss.append(col)
    if "__origin" in folder_tokens:
        hit.append("origin_provenance_layer")
        score += 4
    if re.fullmatch(r"\d{1,3}", folder.likely_variant_segment):
        miss.append("terminal_numeric_folder")
    if model_id and model_id.replace("_", " ") in folder_norm_path:
        score += 6
        hit.append("model_id:compound_value_respected")
    return score, hit, miss

def confidence(score: int) -> str:
    return "high" if score >= 45 else "possible" if score >= 28 else "low"

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

    out_rows=[]; matched_folder_paths=set(); brand_counts=Counter(); statuses=Counter(); variant_level=0
    total_rows = len(products)
    model_ids = [((p.get("model_id") or "").strip().lower()) for p in products]
    non_blank_model_ids = [m for m in model_ids if m]
    model_id_counts = Counter(non_blank_model_ids)
    duplicate_model_ids = {k: v for k, v in model_id_counts.items() if v > 1}
    duplicate_set = set(duplicate_model_ids.keys())
    for p in products:
        product_model_id = (p.get("model_id") or "").strip().lower()
        if product_model_id and product_model_id in duplicate_set:
            statuses[STATUS["identity_review"]] += 1
            out_rows.append({
                "product_db_itemId":p.get("db_itemId",""),"product_brand":p.get("brand",""),"product_itemName":p.get("itemName",""),"product_model_id":p.get("model_id",""),"product_collection":p.get("collection",""),"product_subCategory":p.get("subCategory",""),"product_categoryName":p.get("categoryName",""),"product_variant":p.get("variant",""),
                "candidate_folder_relative_path":"","candidate_folder_absolute_path":"","candidate_path_segments_semicolon_list":"","likely_model_root_relative_path":"","likely_colour_or_variant_segment":"","image_count":"","image_files_semicolon_list":"",
                "match_status":STATUS["identity_review"],"match_confidence":"low","match_score":"0","matched_signals":"","missing_expected_signals":"model_id_uniqueness",
                "contract_or_structure_notes":"Product model_id is duplicated in ProductDB; identity review required before deterministic mapping.",
                "warning":f"Duplicate model_id appears {duplicate_model_ids[product_model_id]} times."
            })
            continue
        candidates=[]
        for fr in folders:
            sc, hit, miss = score_product_folder(p, fr)
            if sc >= 22:
                candidates.append((sc, fr, hit, miss))
        candidates.sort(key=lambda x: x[0], reverse=True)
        if not candidates:
            statuses[STATUS["unmatched_product"]]+=1
            out_rows.append({"product_db_itemId":p.get("db_itemId",""),"product_brand":p.get("brand",""),"product_itemName":p.get("itemName",""),"product_model_id":p.get("model_id",""),"product_collection":p.get("collection",""),"product_subCategory":p.get("subCategory",""),"product_categoryName":p.get("categoryName",""),"product_variant":p.get("variant",""),"match_status":STATUS["unmatched_product"],"warning":"No candidate folder scored above threshold."})
            continue
        top = candidates[0]
        ties = [c for c in candidates if c[0] >= top[0]-2]
        is_amb = len(ties) > 1
        for sc,fr,hit,miss in (ties if is_amb else [top]):
            matched_folder_paths.add(fr.rel)
            cf = confidence(sc)
            if is_amb: st=STATUS["ambiguous"]
            else: st=STATUS["high"] if cf=="high" else STATUS["possible"]
            if fr.likely_variant_segment and len(fr.segments)>=2:
                variant_level +=1
            notes = "Contract 22/23 aligned: full model_id used as canonical identity signal; structured fields mapped to path segments without blindly splitting model_id on underscores."
            if fr.likely_variant_segment:
                notes += " Final image folder treated as likely colour/variant terminal folder."
            statuses[st]+=1; brand_counts[p.get("brand","Unknown")]+=1
            out_rows.append({
                "product_db_itemId":p.get("db_itemId",""),"product_brand":p.get("brand",""),"product_itemName":p.get("itemName",""),"product_model_id":p.get("model_id",""),"product_collection":p.get("collection",""),"product_subCategory":p.get("subCategory",""),"product_categoryName":p.get("categoryName",""),"product_variant":p.get("variant",""),
                "candidate_folder_relative_path":fr.rel,"candidate_folder_absolute_path":fr.abs,
                "candidate_path_segments_semicolon_list":";".join(fr.segments),"likely_model_root_relative_path":fr.likely_model_root,
                "likely_colour_or_variant_segment":fr.likely_variant_segment,"image_count":str(len(fr.images)),"image_files_semicolon_list":";".join(fr.images),
                "match_status":st,"match_confidence":cf,"match_score":str(sc),"matched_signals":";".join(hit),"missing_expected_signals":";".join(miss),
                "contract_or_structure_notes":notes,
                "warning":"Multiple near-equal candidates" if is_amb else ""
            })

    for fr in folders:
        if fr.rel in matched_folder_paths:
            continue
        st = "ignored_non_product_asset" if any(s.startswith("_") for s in fr.segments) else STATUS["unmatched_folder"]
        statuses[st]+=1
        out_rows.append({"product_db_itemId":"","product_brand":"","product_itemName":"","product_model_id":"","product_collection":"","product_subCategory":"","product_categoryName":"","product_variant":"","candidate_folder_relative_path":fr.rel,"candidate_folder_absolute_path":fr.abs,"candidate_path_segments_semicolon_list":";".join(fr.segments),"likely_model_root_relative_path":fr.likely_model_root,"likely_colour_or_variant_segment":fr.likely_variant_segment,"image_count":str(len(fr.images)),"image_files_semicolon_list":";".join(fr.images),"match_status":st,"match_confidence":"low","match_score":"0","matched_signals":"","missing_expected_signals":"","contract_or_structure_notes":"Unreferenced by ProductDB candidate matching.","warning":"Review taxonomy alignment."})

    out_csv = Path(args.output_csv); out_csv.parent.mkdir(parents=True, exist_ok=True)
    fields = ["product_db_itemId","product_brand","product_itemName","product_model_id","product_collection","product_subCategory","product_categoryName","product_variant","candidate_folder_relative_path","candidate_folder_absolute_path","candidate_path_segments_semicolon_list","likely_model_root_relative_path","likely_colour_or_variant_segment","image_count","image_files_semicolon_list","match_status","match_confidence","match_score","matched_signals","missing_expected_signals","contract_or_structure_notes","warning"]
    with out_csv.open("w", newline="", encoding="utf-8") as f:
        w=csv.DictWriter(f,fieldnames=fields); w.writeheader(); w.writerows(out_rows)

    summary = Path(args.output_summary)
    contracts=[f"README/II-CONTRACTS/{i}" for i in [
      "22-Model_ID_Image_Filesystem_Identity_Contract.md","23-Model_ID_To_Image_Folder_Translation_Contract.md"]]
    patterns=Counter('/'.join(f.segments[:min(4,len(f.segments))]) for f in folders if f.segments)
    with summary.open('w',encoding='utf-8') as f:
        f.write("# Stage 2 Image Folder ↔ Product Matching Summary\n\n")
        f.write("- Practical authority note: actual folder structure is treated as the operational authority; contracts are interpreted context.\n")
        f.write(f"- Source files used: `{args.inventory}`, `{args.product_db}`\n")
        f.write("- Matching logic alignment: Contract 22/23 model_id identity + structured folder translation semantics.\n")
        f.write("- README contracts read:\n")
        for c in contracts: f.write(f"  - `{c}`\n")
        f.write(f"\n## Totals\n- total product rows: {total_rows}\n- distinct non-blank model_id values: {len(set(non_blank_model_ids))}\n- duplicated model_id values: {len(duplicate_model_ids)}\n- total image-containing folders: {len(folders)}\n")
        for k in ["matched_high_confidence","matched_possible","ambiguous_multiple_candidates","unmatched_product","unmatched_folder"]:
            f.write(f"- {k}: {statuses[k]}\n")
        f.write(f"- identity_review_required: {statuses[STATUS['identity_review']]}\n")
        f.write(f"- variant-level folders detected: {variant_level}\n\n## Brand-level counts\n")
        if duplicate_model_ids:
            f.write("\n## Duplicate model_id values\n")
            for mid, cnt in sorted(duplicate_model_ids.items(), key=lambda x: (-x[1], x[0])):
                f.write(f"- `{mid}`: {cnt}\n")
        for b,c in brand_counts.most_common(): f.write(f"- {b}: {c}\n")
        f.write("\n## Observed folder-structure patterns\n")
        for p,c in patterns.most_common(12): f.write(f"- `{p}`: {c}\n")
        f.write("\n## Contract clarity notes\n- Contract 22: model_id treated as strongest canonical product identity signal.\n- Contract 23: folder translation uses structured ProductDB signals and does not infer boundaries by blindly splitting model_id underscores.\n- Colour/variant is interpreted as the terminal folder when parent hierarchy appears to represent product model identity.\n")
        f.write("\n## Review-needed notes\n- Ambiguous rows and unmatched folders should be manually reviewed where path tokens do not clearly align with ProductDB taxonomy.\n")

if __name__ == '__main__':
    main()
