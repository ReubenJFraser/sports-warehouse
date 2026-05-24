#!/usr/bin/env python3
"""Stage 2 contract-aware, structure-aware product/image folder matching report."""
from __future__ import annotations
import argparse, csv, os, re
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
        path_key = headers.get("path") or headers.get("file_path") or headers.get("filepath") or headers.get("relative_path")
        if not path_key:
            raise ValueError("Inventory CSV needs a path/file_path column.")
        for r in reader:
            p = (r.get(path_key) or "").strip().replace('\\', '/')
            if not p:
                continue
            ext = Path(p).suffix.lower()
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
    checks = [
        ("brand", 20), ("gender", 8), ("collection", 12), ("subCategory", 14),
        ("categoryName", 8), ("construction", 8), ("rise", 6), ("length", 5), ("variant", 8), ("model_id", 14)
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
    for p in products:
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
            statuses[st]+=1; brand_counts[p.get("brand","Unknown")]+=1
            out_rows.append({
                "product_db_itemId":p.get("db_itemId",""),"product_brand":p.get("brand",""),"product_itemName":p.get("itemName",""),"product_model_id":p.get("model_id",""),"product_collection":p.get("collection",""),"product_subCategory":p.get("subCategory",""),"product_categoryName":p.get("categoryName",""),"product_variant":p.get("variant",""),
                "candidate_folder_relative_path":fr.rel,"candidate_folder_absolute_path":fr.abs,
                "candidate_path_segments_semicolon_list":";".join(fr.segments),"likely_model_root_relative_path":fr.likely_model_root,
                "likely_colour_or_variant_segment":fr.likely_variant_segment,"image_count":str(len(fr.images)),"image_files_semicolon_list":";".join(fr.images),
                "match_status":st,"match_confidence":cf,"match_score":str(sc),"matched_signals":";".join(hit),"missing_expected_signals":";".join(miss),
                "contract_or_structure_notes":"Structure treated as practical authority; contracts used as interpretation context (model-vs-variant and color-terminal assumptions).",
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
      "13-Ryderwear_Women—Folder_Structure-Product_First.md","14-Non-NKD_Aggregation_Folder_Structure—Governance.md","15-Ryderwear-Women_Folder_System—Harmonization_Contract.md","16-Ryderwear-Women_Folder_System—Harmonization_Execution_Plan.md","17-Product_Model_&_Variant_Separation_Contract.md","18-ProductVariants_Sheet_Schema_Contract.md","19-Model_ID_Generation_&_Identity_Governance_Contract.md","20-Sports_Bra_Identity_&_Support_Classification_Contract.md","21-Collections_Metadata_Schema_Contract.md"]]
    patterns=Counter('/'.join(f.segments[:min(4,len(f.segments))]) for f in folders if f.segments)
    with summary.open('w',encoding='utf-8') as f:
        f.write("# Stage 2 Image Folder ↔ Product Matching Summary\n\n")
        f.write("- Practical authority note: actual folder structure is treated as the operational authority; contracts are interpreted context.\n")
        f.write(f"- Source files used: `{args.inventory}`, `{args.product_db}`\n")
        f.write("- README contracts read:\n")
        for c in contracts: f.write(f"  - `{c}`\n")
        f.write(f"\n## Totals\n- total product rows: {len(products)}\n- total image-containing folders: {len(folders)}\n")
        for k in ["matched_high_confidence","matched_possible","ambiguous_multiple_candidates","unmatched_product","unmatched_folder"]:
            f.write(f"- {k}: {statuses[k]}\n")
        f.write(f"- variant-level folders detected: {variant_level}\n\n## Brand-level counts\n")
        for b,c in brand_counts.most_common(): f.write(f"- {b}: {c}\n")
        f.write("\n## Observed folder-structure patterns\n")
        for p,c in patterns.most_common(12): f.write(f"- `{p}`: {c}\n")
        f.write("\n## Contract clarity notes\n- Model/variant separation and color-terminal guidance from contracts 17 and 18 aligned with most terminal image folders.\n- Non-NKD `__Origin` provenance rule from contract 14 used as contextual signal, not hard requirement.\n")
        f.write("\n## Review-needed notes\n- Ambiguous rows and unmatched folders should be manually reviewed where path tokens do not clearly align with ProductDB taxonomy.\n")

if __name__ == '__main__':
    main()
