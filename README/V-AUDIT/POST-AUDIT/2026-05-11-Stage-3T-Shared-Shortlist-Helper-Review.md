# Stage 3T - Shared Shortlist Helper Review

## 1. Stage 3T status

Stage 3T is a review/audit stage only.

No batch endpoint was implemented. No UI rendering was implemented. No JavaScript or CSS was changed. No scoring or ranking formulas were changed. No database writes were introduced.

Manual selection remains final.

The current shortlist basis remains:

```text
legacy_rank_placeholder
```

This is still not criteria-aware AI ranking.

## 2. Files reviewed

Reviewed:

- `inc/hero/shortlist.php`
- `admin/hero-candidates.php`

No other files required code review for Stage 3T.

## 3. Read-only safety verdict

Accepted.

`inc/hero/shortlist.php` is read-only helper code. It does not:

- write to MySQL;
- update `hero_image`;
- update `hero_score`;
- update `hero_override`;
- update `hero_rejections`;
- call Python;
- regenerate JSON;
- mutate diagnostics JSON;
- alter candidate scoring formulas;
- alter ranking formulas;
- render HTML;
- depend on JavaScript or CSS;
- change manual selection authority.

The helper receives candidate arrays and returns enriched read-only shortlist contract arrays.

## 4. Safe include behaviour

Accepted.

`inc/hero/shortlist.php` defines functions only. It produces no output at include time, contains no smoke-test code, does not assume a browser request context, and can be required by endpoint files safely.

## 5. Helper function boundary review

Accepted.

The helper cleanly owns:

- placeholder criteria profile inference;
- criteria profile metadata;
- shortlist contract building;
- top-three selection using existing candidate rank/order;
- candidate-level shortlist metadata enrichment;
- recommendation reason text;
- recommendation confidence;
- current hero summary;
- shortlist status;
- shortlist warning text.

`admin/hero-candidates.php` no longer contains the duplicated shortlist-building logic. It now delegates the opt-in shortlist contract to:

```php
sw_build_hero_shortlist_contract($itemId, $result['candidates'] ?? [])
```

## 6. Default endpoint preservation

Accepted.

Tested:

```text
admin/hero-candidates.php?item_id=98
```

Observed top-level response keys:

```text
item_id,candidates
```

Confirmed absent from the default response:

- `recommended_candidates`
- `all_candidates`
- `active_criteria_profile`
- `shortlist_basis`
- `shortlist_status`

Candidate diagnostics remain present as additive candidate metadata from the earlier diagnostics enrichment stage.

## 7. Opt-in endpoint stability

Accepted.

Tested:

```text
admin/hero-candidates.php?item_id=98&include_shortlist=1
```

Observed:

- `active_criteria_profile`: `object_only`
- `shortlist_basis`: `legacy_rank_placeholder`
- `shortlist_status`: `partial`
- `recommended_candidates`: 1
- `all_candidates`: 1
- diagnostics available: yes
- ROI specificity: `object_bbox`
- `roi.is_garment_specific`: false
- current hero present and inside recommended candidates

Tested:

```text
admin/hero-candidates.php?item_id=79&include_shortlist=1
```

Observed:

- `active_criteria_profile`: null
- `shortlist_basis`: `legacy_rank_placeholder`
- `shortlist_status`: `ready`
- `recommended_candidates`: 3
- `all_candidates`: 5
- recommended ranks: `1,2,3`
- current hero rank: 4
- current hero outside top three: true
- diagnostics unavailable safely with `no_record_for_image`

No hard failure was observed.

## 8. Current hero summary review

Accepted.

The current hero summary reports:

- `path`;
- `rank`;
- `is_in_recommended_candidates`;
- `current_hero_outside_top_three`;
- `is_manual_override`.

The current hero is not forced into `recommended_candidates` when it falls outside the top three. The Stage 3S addition of `current_hero.is_manual_override` is contract-aligned and useful for future batch/list display.

## 9. Rejected candidate handling

Accepted.

The helper excludes rejected candidates from `recommended_candidates` where possible while preserving all candidates in `all_candidates`.

The review found no code that mutates rejection state. Existing candidate `status`, `actions`, and rejection-related fields remain part of the preserved candidate record.

## 10. Manual override handling

Accepted.

Manual override state is preserved on candidate status where present. The current hero summary can surface `is_manual_override`, but the helper does not overwrite manual status, does not update authority state, and does not write to the database.

## 11. Shortlist derivation safety

Accepted.

`recommended_candidates` are selected from the existing candidate order/rank only.

The helper does not:

- recalculate scores;
- use `diagnostics.score.final_advisory_score` for ranking;
- combine existing score with diagnostic score;
- implement criteria-aware scoring prematurely;
- change candidate order;
- change candidate rank.

The shortlist remains explicitly labelled as `legacy_rank_placeholder`.

## 12. Candidate preservation

Accepted.

Original candidate fields remain intact:

- `basename`
- `path`
- `source`
- `score`
- `analysis`
- `status`
- `rank`
- `actions`
- `diagnostics`

Shortlist metadata is additive only:

- `is_recommended_top_three`
- `recommendation_rank`
- `recommendation_reason`
- `recommendation_confidence`
- `shortlist_basis`
- `shortlist_warning`
- `active_criteria_profile`

No existing candidate fields were renamed or removed.

## 13. Diagnostics preservation

Accepted.

The helper uses only sanitized diagnostics already attached to candidate records.

The endpoint output does not expose:

- raw pose landmarks;
- raw bounding boxes;
- alpha geometry;
- `normalized_tokens`;
- raw diagnostic JSON records;
- implementation-only debug fields.

`final_advisory_score` is not used for ranking.

## 14. Garment-segmentation boundary

Accepted.

No reviewed output or helper wording implies:

- garment segmentation;
- garment masks;
- garment-specific bounding boxes;
- automated final hero selection;
- AI-approved final image;
- criteria-aware winner.

The object-product test preserves:

```text
roi.is_garment_specific: false
```

## 15. Recommendation wording safety

Accepted.

Observed safe wording:

```text
Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics available: object_bbox with high ROI confidence.
```

Observed safe fallback wording:

```text
Included in temporary top-three shortlist using existing Hero Manager rank. Diagnostics unavailable for this image.
```

Observed safe shortlist warning:

```text
Temporary shortlist uses existing Hero Manager rank, not criteria-aware AI ranking.
```

The review found no wording such as:

- AI selected final hero;
- best image guaranteed;
- criteria-aware winner;
- garment detected;
- AI-approved final image.

## 16. Criteria profile inference review

Accepted as placeholder metadata.

The helper maps diagnostic product types to placeholder profiles:

- `object` -> `object_only`
- `lower_body` -> `body_region_first`
- `sports_bra` -> `body_region_first`
- `upper_body` -> `product_first`
- `full_body` -> `full_outfit`
- diagnostics unavailable -> null

This inference does not re-rank candidates and does not act as a criteria engine.

## 17. Shortlist status review

Accepted.

Current behavior:

- `ready` when three recommended candidates exist;
- `partial` when one or two recommended candidates exist;
- `unavailable` when no recommended candidates exist.

Observed examples:

- item 98: one candidate, `partial`;
- item 79: three recommended candidates, `ready`.

This behavior is correct for the temporary shortlist contract.

## 18. Syntax and smoke tests

Syntax checks:

```text
php -l inc\hero\shortlist.php
```

Result:

```text
No syntax errors detected in inc\hero\shortlist.php
```

```text
php -l admin\hero-candidates.php
```

Result:

```text
No syntax errors detected in admin\hero-candidates.php
```

Endpoint tests:

```text
admin/hero-candidates.php?item_id=98
```

Result:

- valid JSON;
- top-level keys: `item_id,candidates`;
- no top-level shortlist contract keys;
- candidate diagnostics present.

```text
admin/hero-candidates.php?item_id=98&include_shortlist=1
```

Result:

- valid JSON;
- opt-in shortlist contract shape present;
- `active_criteria_profile`: `object_only`;
- `shortlist_basis`: `legacy_rank_placeholder`;
- `recommended_candidates`: 1;
- `all_candidates`: 1;
- diagnostics available.

```text
admin/hero-candidates.php?item_id=79&include_shortlist=1
```

Result:

- valid JSON;
- `recommended_candidates`: 3;
- `all_candidates`: 5;
- recommended ranks: `1,2,3`;
- current hero rank 4 is outside the top three;
- diagnostics unavailable safely.

```text
admin/hero-candidates.php?item_id=0&include_shortlist=1
```

Result:

```json
{"error":"Invalid item"}
```

No PHP warnings or notices were observed in the tested responses.

## 19. Batch endpoint readiness

Accepted for future batch endpoint planning.

`inc/hero/shortlist.php` can build a shortlist contract from a candidate array without depending on `admin/hero-candidates.php`. The helper is endpoint-agnostic enough to be reused by a future batch endpoint such as:

```text
admin/hero-shortlists.php
```

The helper already accepts optional item metadata, which gives the future batch endpoint room to pass product context without changing the current single-item endpoint contract.

No helper changes are required before planning or implementing the first endpoint-only batch shortlist contract.

## 20. Defects and corrections

No defects were found during Stage 3T.

No PHP code was changed during Stage 3T.

## 21. Non-goals

Stage 3T does not:

- implement `admin/hero-shortlists.php`;
- implement UI;
- modify JavaScript or CSS;
- implement criteria-aware ranking;
- change scoring formulas;
- change database state;
- alter manual selection authority;
- claim garment segmentation.

## 22. Final verdict

Accepted.

The Stage 3S shared shortlist helper refactor is safe for future batch endpoint implementation planning. The default endpoint remains backward-compatible, the opt-in shortlist output remains stable, the helper is read-only, and the shortlist contract continues to honestly identify its basis as `legacy_rank_placeholder`.

## 23. Recommended Stage 3U

Recommended next stage:

```text
Stage 3U - Implement endpoint-only batch shortlist contract in admin/hero-shortlists.php using inc/hero/shortlist.php, without UI rendering.
```

Stage 3U should remain endpoint-only and should not modify JavaScript, CSS, scoring formulas, ranking formulas, database writes, authority logic, overrides, rejections, diagnostics JSON, Python preprocessing, or generated diagnostic files.
