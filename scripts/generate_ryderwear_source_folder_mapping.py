#!/usr/bin/env python3
import csv
import re
from pathlib import Path
from collections import Counter, defaultdict

ROOT = Path(__file__).resolve().parents[1]
PRODUCTDB = ROOT / 'docs/data/SportWarehouse_ProductDB.csv'
SOURCE_INV = ROOT / 'docs/operations/generated/source-ryderwear-semantic-image-folders.csv'
OUT = ROOT / 'docs/operations/generated/ryderwear-source-folder-to-product-mapping-worksheet.csv'
SUMMARY_OUT = ROOT / 'docs/operations/generated/ryderwear-source-folder-to-product-mapping-summary.md'
QUEUE_FILES = {
    'grandfather': ROOT / 'docs/operations/generated/ryderwear-contract-24-grandfather-queue.csv',
    'recoverable_review': ROOT / 'docs/operations/generated/ryderwear-contract-24-recoverable-review-queue.csv',
    'source_needed': ROOT / 'docs/operations/generated/ryderwear-contract-24-source-needed-queue.csv',
    'later_migration': ROOT / 'docs/operations/generated/ryderwear-contract-24-later-migration-queue.csv',
}
MANUAL_FILES = [
    ROOT / 'docs/operations/generated/ryderwear-contract-24-manual-decision-worksheet.csv',
    ROOT / 'docs/operations/generated/ryderwear-contract-24-first-pass-desk-review.csv',
]

STOP = {'ryderwear', 'men', 'male', 'women', 'female', 'plus', 'size', 'plus-size', 'non', 'the', 'and', 'with', 'in', 'for', 'to', 'of', 'a'}
CATEGORY_SYNONYMS = {
    'Leggings': {'leggings', 'tight', 'tights'},
    'Sports_Bra': {'sports', 'bra', 'crop', 'bralette'},
    'Bodysuit': {'bodysuit', 'bodysuit', 'onesie'},
    'Tank_Top': {'tank', 'top', 'singlet'},
    'T_Shirt': {'tee', 'shirt', 'tshirt'},
    'Shorts': {'short', 'shorts', 'bike', 'biker'},
    'Track_Pants': {'track', 'pants', 'pant', 'jogger', 'joggers'},
}


def norm_tokens(text):
    text = (text or '').lower().replace('_', ' ').replace('-', ' ')
    text = re.sub(r'[^a-z0-9 ]+', ' ', text)
    return [t for t in text.split() if len(t) > 1 and t not in STOP]


def read_csv(path):
    with path.open(encoding='utf-8-sig', newline='') as f:
        return list(csv.DictReader(f))


def infer_collection_kind(collection):
    c = (collection or '').lower()
    return 'nkd' if 'nkd' in c else 'non_nkd'


def family_collection_kind(family_key):
    f = family_key.lower()
    if '/nkd/' in f:
        return 'nkd'
    if '/non-nkd/' in f:
        return 'non_nkd'
    return 'unknown'


def subcat_match(subcat, family_terms):
    syn = CATEGORY_SYNONYMS.get(subcat, set())
    if not syn:
        return True
    matched = bool(set(syn).intersection(family_terms))
    if not matched:
        return False
    # Hard guards for known cross-garment leakage
    if subcat == 'Sports_Bra' and 'bodysuit' in family_terms:
        return False
    if subcat == 'Bodysuit' and 'sports' in family_terms and 'bra' in family_terms and 'bodysuit' not in family_terms:
        return False
    return True


def analyze_candidate(p, fam, meta):
    collection_kind = infer_collection_kind(p.get('collection', ''))
    fam_kind = family_collection_kind(fam)
    subcat = p.get('subCategory', '')
    terms = meta['terms']

    collection_ok = fam_kind in ('unknown', collection_kind)
    subcat_ok = subcat_match(subcat, terms)

    mismatch_reasons = []
    if not collection_ok:
        mismatch_reasons.append(f'collection mismatch product={collection_kind} family={fam_kind}')
    if not subcat_ok:
        mismatch_reasons.append(f'subCategory mismatch product={subcat}')

    return collection_ok and subcat_ok, mismatch_reasons


def main():
    product_rows = read_csv(PRODUCTDB)
    source_rows = read_csv(SOURCE_INV)

    queue_map = {}
    for queue_source, fp in QUEUE_FILES.items():
        if fp.exists():
            for r in read_csv(fp):
                mid = (r.get('model_id') or '').strip()
                if mid:
                    queue_map[mid] = queue_source

    for fp in MANUAL_FILES:
        if fp.exists():
            for r in read_csv(fp):
                mid = (r.get('model_id') or '').strip()
                q = (r.get('queue_source') or '').strip()
                if mid and q:
                    queue_map[mid] = q

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
        fam_terms = set(norm_tokens('/'.join(family_parts) + ' ' + s.get('semantic_display_path', '') + ' ' + s.get('product_signal_terms', '')))
        family_meta.setdefault(family_key, {'terms': set(), 'colors': set(), 'paths': []})
        family_meta[family_key]['terms'].update(fam_terms)
        family_meta[family_key]['colors'].add(color)
        family_meta[family_key]['paths'].append(path)

    out_fields = [
        'model_id', 'itemName', 'collection', 'subCategory', 'queue_status',
        'source_folder_family_candidate', 'source_colour_folders_available', 'recommended_display_colour',
        'source_terminal_folder', 'source_image_files', 'proposed_project_image_path', 'mapping_status',
        'mapping_confidence', 'reviewer_decision', 'reviewer_notes'
    ]

    rows_out = []
    status_counts = Counter()
    nkd_non_nkd_candidates = 0
    subcat_mismatch_candidates = 0
    batch2_out_of_scope = 0

    corrected_examples = {}

    for p in product_rows:
        if (p.get('brand') or '').strip().lower() != 'ryderwear':
            continue

        model_id = p.get('model_id', '').strip()
        item = p.get('itemName', '').strip()
        collection = p.get('collection', '').strip()
        subcat = p.get('subCategory', '').strip()

        query_terms = set(norm_tokens(' '.join([
            model_id, item, collection, subcat,
            p.get('itemName_fully_derived', ''), p.get('model_family', '')
        ])))

        scored = []
        for fam, meta in family_meta.items():
            overlap = len(query_terms.intersection(meta['terms']))
            if overlap:
                ok, reasons = analyze_candidate(p, fam, meta)
                scored.append({'score': overlap, 'family': fam, 'ok': ok, 'reasons': reasons})

        scored.sort(key=lambda x: x['score'], reverse=True)
        valid = [s for s in scored if s['ok']]
        queue_status = queue_map.get(model_id, '')

        if infer_collection_kind(collection) == 'nkd' and any(family_collection_kind(s['family']) == 'non_nkd' for s in scored):
            nkd_non_nkd_candidates += 1
        if any('subCategory mismatch' in ' '.join(s['reasons']) for s in scored):
            subcat_mismatch_candidates += 1

        notes = []
        if queue_status == 'grandfather' and (p.get('images') or '').strip():
            status = 'already_mapped_or_grandfathered'
            notes.append('Grandfather queue row with existing ProductDB images path.')
        else:
            if queue_status == 'later_migration':
                status = 'out_of_scope_for_batch_2'
                notes.append('Explicit later_migration queue assignment from Contract 24 planning.')
            elif not valid:
                status = 'no_source_family_found'
                notes.append('No same-collection and subCategory-compatible source family found.')
            else:
                top_score = valid[0]['score']
                top_valid = [v for v in valid if v['score'] == top_score]
                if len(top_valid) > 1:
                    status = 'multiple_possible_source_families'
                    notes.append('Multiple same-collection and subCategory-compatible source families scored equally.')
                elif top_score >= 4:
                    status = 'likely_source_family_match'
                    notes.append('Strong collection + subCategory + semantic token overlap with source family.')
                else:
                    status = 'needs_manual_mapping'
                    notes.append('Some semantic overlap exists, but identity evidence is incomplete.')

            if queue_status in ('recoverable_review', 'source_needed') and status == 'out_of_scope_for_batch_2':
                batch2_out_of_scope += 1
            if queue_status in ('recoverable_review', 'source_needed'):
                notes.append(f'Queue context={queue_status}; row requires mapping review for batch-2 handling.')

        candidate = colors = rec_color = terminal = imgs = proposed = ''
        conf = 'low'
        chosen = valid[0] if valid else None
        if chosen:
            fam = chosen['family']
            candidate = fam
            cset = sorted(family_meta[fam]['colors'])
            colors = ' | '.join(cset)
            item_terms = set(norm_tokens(item))
            term_colors = [c for c in cset if c.lower() in query_terms or c.lower() in item_terms]
            rec_color = term_colors[0] if term_colors else (cset[0] if cset else '')
            matches = [s for s in families[fam] if (s.get('likely_colour_folder') or '').lower() == rec_color.lower()] if rec_color else []
            terminal_row = matches[0] if matches else families[fam][0]
            terminal = terminal_row.get('relative_folder', '')
            imgs = terminal_row.get('image_files', '')
            proposed = f"images/brands/ryderwear/{terminal.lower().replace(' ', '-').replace('_', '-')}" if terminal else ''

            if status == 'likely_source_family_match' and chosen['score'] >= 6:
                conf = 'medium'
            elif status == 'likely_source_family_match':
                conf = 'low-medium'
            elif status == 'multiple_possible_source_families':
                conf = 'low'
            elif status == 'needs_manual_mapping':
                conf = 'low'

        if scored and not valid:
            notes.append('Top semantic candidates were rejected due to collection/subCategory mismatch guards.')

        if model_id in {
            'ryderwear_female_nkd_leggings_v_high_waisted_scrunch',
            'ryderwear_female_nkd_bodysuit_v_scrunch',
            'ryderwear_female_nkd_tank_top_scrunch',
        }:
            corrected_examples[model_id] = status

        status_counts[status] += 1

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
            'reviewer_notes': ' '.join(notes),
        })

    OUT.parent.mkdir(parents=True, exist_ok=True)
    with OUT.open('w', encoding='utf-8', newline='') as f:
        w = csv.DictWriter(f, fieldnames=out_fields)
        w.writeheader()
        w.writerows(rows_out)

    with SUMMARY_OUT.open('w', encoding='utf-8') as f:
        f.write('# Ryderwear Source Folder Mapping Summary\n\n')
        f.write('## Totals\n')
        f.write(f'- Total Ryderwear rows considered: {len(rows_out)}\n')
        f.write(f'- Source terminal folders read: {len(source_rows)}\n')
        f.write(f'- Source-folder families inferred: {len(families)}\n\n')
        f.write('## Mapping status counts\n')
        for k in sorted(status_counts):
            f.write(f'- {k}: {status_counts[k]}\n')
        f.write('\n## Guardrail diagnostics\n')
        f.write(f'- NKD rows with Non-NKD candidates in raw semantic pool: {nkd_non_nkd_candidates}\n')
        f.write(f'- Rows with subCategory mismatch candidates in raw semantic pool: {subcat_mismatch_candidates}\n')
        f.write(f'- Batch-2 manual/recoverable/source-needed rows still out_of_scope_for_batch_2: {batch2_out_of_scope}\n\n')
        f.write('## Corrected example rows\n')
        for mid, st in corrected_examples.items():
            f.write(f'- {mid}: {st}\n')

    print(f'Wrote {len(rows_out)} rows to {OUT}')
    print(f'Wrote summary to {SUMMARY_OUT}')


if __name__ == '__main__':
    main()
