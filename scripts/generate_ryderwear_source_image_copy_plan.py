from __future__ import annotations
import csv
from pathlib import Path
from collections import defaultdict

ROOT = Path(__file__).resolve().parents[1]
GEN = ROOT / 'docs/operations/generated'
WORKSHEET = GEN / 'ryderwear-source-folder-to-product-mapping-worksheet.csv'
PLAN = GEN / 'ryderwear-source-image-copy-plan.csv'
EXCEPTIONS = GEN / 'ryderwear-source-image-copy-exceptions.csv'
SUMMARY = GEN / 'ryderwear-source-image-copy-plan-summary.md'
DUPLICATES = GEN / 'ryderwear-source-image-copy-duplicate-destinations.csv'

BASE_FIELDS = [
    'model_id','itemName','collection','subCategory','queue_status','mapping_status','mapping_confidence',
    'source_terminal_folder','source_image_files','source_file_count','proposed_project_image_path',
    'destination_file_count','copy_plan_status','copy_notes'
]


def split_files(v:str)->list[str]:
    if not v:
        return []
    return [x.strip() for x in v.split('|') if x.strip()]

rows = list(csv.DictReader(WORKSHEET.open(encoding='utf-8-sig', newline='')))
plan_rows=[]

def row_status(r):
    ms=(r.get('mapping_status') or '').strip()
    stf=(r.get('source_terminal_folder') or '').strip()
    imgs=split_files(r.get('source_image_files') or '')
    if ms=='already_mapped_or_grandfathered':
        return 'excluded_already_grandfathered', 'Excluded from execution plan because worksheet marks this row as already mapped/grandfathered.'
    if not stf or not imgs:
        return 'excluded_no_source_family', 'Excluded because no usable source folder family is available in current worksheet context.'
    return 'ready_to_copy', 'Provisionally accepted mapping ready for controlled local copy.'

for r in rows:
    imgs = split_files(r.get('source_image_files') or '')
    status, note = row_status(r)
    pr = {
        'model_id': r.get('model_id',''),
        'itemName': r.get('itemName',''),
        'collection': r.get('collection',''),
        'subCategory': r.get('subCategory',''),
        'queue_status': r.get('queue_status',''),
        'mapping_status': r.get('mapping_status',''),
        'mapping_confidence': r.get('mapping_confidence',''),
        'source_terminal_folder': r.get('source_terminal_folder',''),
        'source_image_files': r.get('source_image_files',''),
        'source_file_count': str(len(imgs)),
        'proposed_project_image_path': r.get('proposed_project_image_path',''),
        'destination_file_count': str(len(imgs)),
        'copy_plan_status': status,
        'copy_notes': note,
    }
    plan_rows.append(pr)

# duplicate destination collision detection among ready rows
by_dest = defaultdict(list)
for pr in plan_rows:
    if pr['copy_plan_status']!='ready_to_copy':
        continue
    base=(pr.get('proposed_project_image_path') or '').strip().strip('/\\')
    src_folder=(pr.get('source_terminal_folder') or '').strip().strip('/\\')
    for fn in split_files(pr.get('source_image_files') or ''):
        destination_path = f"{base}/{fn}" if base else fn
        source_path = f"{src_folder}/{fn}" if src_folder else fn
        by_dest[destination_path].append((pr, source_path))

collisions={k:v for k,v in by_dest.items() if len({x[0]['model_id'] for x in v})>1}
collision_models = set()
for v in collisions.values():
    for pr,_ in v:
        collision_models.add(pr['model_id'])

for pr in plan_rows:
    if pr['copy_plan_status']=='ready_to_copy' and pr['model_id'] in collision_models:
        pr['copy_plan_status']='excluded_duplicate_destination_collision'
        pr['copy_notes']='Excluded because one or more destination file paths collide with a different model_id.'

# write plan
with PLAN.open('w',encoding='utf-8',newline='') as f:
    w=csv.DictWriter(f,fieldnames=BASE_FIELDS)
    w.writeheader(); w.writerows(plan_rows)

# exceptions
exc_fields = BASE_FIELDS + ['exception_reason']
exc=[]
for pr in plan_rows:
    if pr['copy_plan_status']=='ready_to_copy':
        continue
    reason = {
        'excluded_already_grandfathered':'already-grandfathered',
        'excluded_no_source_family':'no-source-family',
        'excluded_duplicate_destination_collision':'duplicate-destination-collision',
    }.get(pr['copy_plan_status'],'excluded')
    r=dict(pr); r['exception_reason']=reason; exc.append(r)
with EXCEPTIONS.open('w',encoding='utf-8',newline='') as f:
    w=csv.DictWriter(f,fieldnames=exc_fields)
    w.writeheader(); w.writerows(exc)

# duplicate diagnostic
dup_fields=['destination_path','duplicate_count','model_ids','itemNames','source_paths','recommended_action']
dup_rows=[]
for dest,entries in sorted(collisions.items()):
    models=sorted({e[0]['model_id'] for e in entries})
    names=[]; sources=[]
    seen_n=set();seen_s=set()
    for pr,sp in entries:
        if pr['itemName'] not in seen_n:
            names.append(pr['itemName']); seen_n.add(pr['itemName'])
        if sp not in seen_s:
            sources.append(sp); seen_s.add(sp)
    dup_rows.append({
        'destination_path':dest,
        'duplicate_count':str(len(models)),
        'model_ids':' | '.join(models),
        'itemNames':' | '.join(names),
        'source_paths':' | '.join(sources),
        'recommended_action':'Manually resolve mapping ambiguity and keep exactly one model_id for this destination path before executing copy.'
    })
with DUPLICATES.open('w',encoding='utf-8',newline='') as f:
    w=csv.DictWriter(f,fieldnames=dup_fields)
    w.writeheader();w.writerows(dup_rows)

from collections import Counter
c=Counter(r['copy_plan_status'] for r in plan_rows)
planned_files=0
for r in plan_rows:
    if r['copy_plan_status']=='ready_to_copy':
        planned_files += int(r['source_file_count'] or 0)

summary=f"""# Ryderwear Source Image Copy Plan Summary

- Total worksheet rows read: {len(rows)}
- Ready to copy rows: {c.get('ready_to_copy',0)}
- Exception rows: {len(plan_rows)-c.get('ready_to_copy',0)}
- Excluded already-grandfathered rows: {c.get('excluded_already_grandfathered',0)}
- Excluded no-source rows: {c.get('excluded_no_source_family',0)}
- Excluded duplicate destination collision rows: {c.get('excluded_duplicate_destination_collision',0)}
- Duplicate destination file paths detected: {len(collisions)}
- Total source files planned for copying after exclusions: {planned_files}
- Confirmation: No copy was executed by Codex. This task generated planning artifacts and a local-only script.

## Recommended local commands

Dry-run:

```powershell
pwsh -File scripts/copy_ryderwear_source_images_from_plan.ps1
```

Execution:

```powershell
pwsh -File scripts/copy_ryderwear_source_images_from_plan.ps1 -Execute
```
"""
SUMMARY.write_text(summary,encoding='utf-8')
print('Generated artifacts.')
