#!/usr/bin/env python3
import csv
import re
from pathlib import Path
from collections import defaultdict

ROOT = Path(__file__).resolve().parents[1]
PRODUCTDB = ROOT / 'docs/data/SportWarehouse_ProductDB.csv'
SOURCE_INV = ROOT / 'docs/operations/generated/source-ryderwear-semantic-image-folders.csv'
OUT = ROOT / 'docs/operations/generated/ryderwear-source-folder-to-product-mapping-worksheet.csv'
QUEUE_FILES = {
    'already_mapped_or_grandfathered': ROOT / 'docs/operations/generated/ryderwear-contract-24-grandfather-queue.csv',
    'needs_manual_mapping': ROOT / 'docs/operations/generated/ryderwear-contract-24-recoverable-review-queue.csv',
    'needs_manual_mapping_source_needed': ROOT / 'docs/operations/generated/ryderwear-contract-24-source-needed-queue.csv',
    'out_of_scope_for_batch_2': ROOT / 'docs/operations/generated/ryderwear-contract-24-later-migration-queue.csv',
}

STOP = {'ryderwear','men','male','women','female','plus','size','plus-size','non','the','and','with','in','for','to','of','a'}
COLORS = {'black','white','blue','navy','red','pink','green','grey','gray','beige','brown','purple','yellow','orange','coral','khaki','tan','stone','olive','teal','maroon'}

def norm_tokens(text):
    text = (text or '').lower().replace('_', ' ').replace('-', ' ')
    text = re.sub(r'[^a-z0-9 ]+', ' ', text)
    toks = [t for t in text.split() if len(t) > 1 and t not in STOP]
    return toks

def read_csv(path):
    with path.open(encoding='utf-8-sig', newline='') as f:
        return list(csv.DictReader(f))

def main():
    product_rows = read_csv(PRODUCTDB)
    source_rows = read_csv(SOURCE_INV)

    queue_map = {}
    for status, fp in QUEUE_FILES.items():
        if not fp.exists():
            continue
        for r in read_csv(fp):
            mid = (r.get('model_id') or '').strip()
            if mid:
                queue_map[mid] = status

    families = defaultdict(list)
    family_meta = {}
    for s in source_rows:
        path = s['relative_folder']
        parts = [p for p in path.split('/') if p]
        if len(parts) < 2:
            continue
        color = s.get('likely_colour_folder') or parts[-1]
        family_parts = parts[:-1]
        family_key = '/'.join(family_parts)
        families[family_key].append(s)
        fam_terms = set(norm_tokens('/'.join(family_parts) + ' ' + s.get('semantic_display_path','') + ' ' + s.get('product_signal_terms','')))
        family_meta.setdefault(family_key, {'terms': set(), 'colors': set(), 'paths': []})
        family_meta[family_key]['terms'].update(fam_terms)
        family_meta[family_key]['colors'].add(color)
        family_meta[family_key]['paths'].append(path)

    out_fields = [
        'model_id','itemName','collection','subCategory','queue_status',
        'source_folder_family_candidate','source_colour_folders_available','recommended_display_colour',
        'source_terminal_folder','source_image_files','proposed_project_image_path','mapping_status',
        'mapping_confidence','reviewer_decision','reviewer_notes'
    ]

    rows_out = []
    for p in product_rows:
        brand = (p.get('brand') or '').strip().lower()
        if brand != 'ryderwear':
            continue
        model_id = p.get('model_id','').strip()
        item = p.get('itemName','').strip()
        collection = p.get('collection','').strip()
        subcat = p.get('subCategory','').strip()

        query_terms = set(norm_tokens(' '.join([
            model_id, item, collection, subcat,
            p.get('itemName_fully_derived',''), p.get('model_family','')
        ])))

        scored = []
        for fam, meta in family_meta.items():
            overlap = len(query_terms.intersection(meta['terms']))
            if overlap:
                scored.append((overlap, fam))
        scored.sort(reverse=True)

        queue_status = queue_map.get(model_id, '')
        if queue_status == 'already_mapped_or_grandfathered':
            status = 'already_mapped_or_grandfathered'
        elif queue_status == 'out_of_scope_for_batch_2':
            status = 'out_of_scope_for_batch_2'
        elif not scored:
            status = 'no_source_family_found'
        else:
            top_score = scored[0][0]
            top = [fam for sc, fam in scored if sc == top_score][:5]
            if top_score >= 4 and len(top) == 1:
                status = 'likely_source_family_match'
            elif len(top) > 1:
                status = 'multiple_possible_source_families'
            else:
                status = 'needs_manual_mapping'

        candidate = ''
        colors = ''
        rec_color = ''
        terminal = ''
        imgs = ''
        proposed = ''
        conf = 'low'

        if scored:
            best_fam = scored[0][1]
            candidate = best_fam
            cset = sorted(family_meta[best_fam]['colors'])
            colors = ' | '.join(cset)
            # recommend by matching color term if present, else first
            term_colors = [c for c in cset if c.lower() in query_terms or c.lower() in norm_tokens(item)]
            rec_color = term_colors[0] if term_colors else (cset[0] if cset else '')
            matches = [s for s in families[best_fam] if (s.get('likely_colour_folder') or '').lower() == rec_color.lower()] if rec_color else []
            chosen = matches[0] if matches else families[best_fam][0]
            terminal = chosen.get('relative_folder','')
            imgs = chosen.get('image_files','')
            proposed = f"images/brands/ryderwear/{terminal.lower().replace(' ','-').replace('_','-')}" if terminal else ''

            top_score = scored[0][0]
            if status == 'likely_source_family_match' and top_score >= 6:
                conf = 'medium'
            elif status == 'likely_source_family_match':
                conf = 'low-medium'
            elif status in ('multiple_possible_source_families','needs_manual_mapping'):
                conf = 'low'
        
        rows_out.append({
            'model_id': model_id,
            'itemName': item,
            'collection': collection,
            'subCategory': subcat,
            'queue_status': queue_status,
            'source_folder_family_candidate': candidate,
            'source_colour_folders_available': colors,
            'recommended_display_colour': rec_color,
            'source_terminal_folder': terminal,
            'source_image_files': imgs,
            'proposed_project_image_path': proposed,
            'mapping_status': status,
            'mapping_confidence': conf,
            'reviewer_decision': '',
            'reviewer_notes': '',
        })

    OUT.parent.mkdir(parents=True, exist_ok=True)
    with OUT.open('w', encoding='utf-8', newline='') as f:
        w = csv.DictWriter(f, fieldnames=out_fields)
        w.writeheader()
        w.writerows(rows_out)

    print(f'Wrote {len(rows_out)} rows to {OUT}')
    print(f'Source terminal folders: {len(source_rows)}')
    print(f'Source families inferred: {len(families)}')

if __name__ == '__main__':
    main()
