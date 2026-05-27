# Ryderwear Duplicate-Collision Human Adjudication Report

## 1) Executive summary
All **12 Ryderwear duplicate-destination collision rows** remain unresolved and require **manual ownership adjudication** before any image remediation can proceed. No row is currently safe for automatic copy, mapping, ProductDB change, MySQL change, or any downstream publication workflow. The existing worksheet consistently marks these rows as `needs_manual_review`, and ownership is either paired/ambiguous or fully unassigned.

## 2) Collision group table (grouped by duplicate destination path/folder)

| Collision destination path/folder | Affected itemIds | Affected itemNames | Affected external_item_ids | Competing model IDs/items | Prior exception reason | Candidate project folder/path | Recommended owner (current) | Why ownership cannot yet be determined | Recommended human decision |
|---|---|---|---|---|---|---|---|---|---|
| `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/twist/--collection/activate/black` | 138 | Activate Sports Bra | `ryderwear_female_activate_sports_bra_twist` | `ryderwear_female_momentum_sports_bra_twist` | `duplicate-destination-collision` | Same as destination | `paired_manual_adjudication` | Folder semantics suggest "activate", while competing model indicates "momentum" naming conflict; no canonical owner artifact provided. | Human to confirm canonical model-family owner; non-owner gets split path or deferred. |
| `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/halter/seamless/fabric/rib/--collection/lift/black` | 153, 154 | Lift 2.0 Seamless Sports Bra; Lift Rib Seamless Halter Sports Bra | `ryderwear_female_lift_2_0_sports_bra_rib_waistband_light_support`; `ryderwear_female_rib_seamless_sports_bra_scoop_neck_halter_light_support` | Each row references the other as competitor | `duplicate-destination-collision` | Same as destination | `paired_manual_adjudication` | Both products map into a "lift" rib seamless halter destination with near-overlapping semantics; cannot infer if variants should co-locate or split. | Decide single owner vs split variant folders with explicit naming convention. |
| `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/low-support/staples/black` | 157, 163 | NKD Bandeau Sports Bra; NKD One Shoulder Sports Bra | `ryderwear_female_nkd_sports_bra_elastic_underbust_band_bandeau_strapless_low_support`; `ryderwear_female_nkd_sports_bra_elastic_underbust_band_one_shoulder_asymmetrical_back_low_support` | Each row references the other as competitor | `duplicate-destination-collision` | Same as destination | `paired_manual_adjudication` | "Bandeau/strapless" and "one shoulder/asymmetrical" are distinct cuts but currently collide into same staples folder. | Prefer split folders by cut unless merch confirms shared canonical family folder. |
| `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/light-support/embody/blue` | 158, 160 | NKD Core Bra; NKD Embody Sports Crop | `ryderwear_female_nkd_sports_bra_elastic_underbust_band_square_neck_straight_back_light_support`; `ryderwear_female_nkd_sports_bra_v_neck_cross_over_back_light_support` | Each row references the other as competitor | `duplicate-destination-collision` | Same as destination | `paired_manual_adjudication` | "Core" vs "Embody" naming and neckline/back descriptors diverge; folder may be overgeneralized. | Human to assign destination owner and re-path non-owner. |
| `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/halter/construction/tank/cross-over/white` | 162, 174 | NKD Knot Sports Bra; NKD Twist Sports Bra | `ryderwear_female_nkd_sports_bra_elastic_underbust_band_knot_cross_over_back_light_support`; `ryderwear_female_nkd_sports_bra_v_neck_cross_over_back_low_support` | Each row references the other as competitor | `duplicate-destination-collision` | Same as destination | `paired_manual_adjudication` | Shared "cross-over" trait but different style anchors (knot vs twist) and support levels; no deterministic ownership evidence. | Validate product-family taxonomy, then either single canonical owner or split by style/support. |
| `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/halter/construction/scrunch/bra/espresso` | 166, 176 | NKD Scrunch V Halter Bra; NKD Underwire Keyhole Sports Bra | `ryderwear_female_nkd_sports_bra_longline_v_neck_halter_light_support_scrunch`; `ryderwear_female_nkd_sports_bra_underwire_keyhole_racerback_medium_support_scrunch` | Each row references the other as competitor | `duplicate-destination-collision` | Same as destination | `paired_manual_adjudication` | Both include "scrunch" but differ materially (v-halter vs underwire keyhole racerback). | Split by structural style unless asset set is intentionally shared and approved. |
| `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/halter/seamless/cut/low-support/--collection/sculpt/halter-bra/azure` | 184 | Sculpt Seamless Halter Sports Bra | `ryderwear_female_sculpt_sports_bra_rib_waistband_low_support` | `ryderwear_female_rib_seamless_sports_bra_v_neck_halter_low_support \| ryderwear_female_stonewash_sports_bra_rib_waistband_v_neck_racerback_light_support \| ryderwear_female_icon_sports_bra_v_neck_halter_centre_spine_low_support` | `duplicate-destination-collision` | Same as destination | `unassigned` | Multi-competitor collision with no deterministic owner in current artifacts; could represent shared/legacy folder reuse across collections. | Require manual source verification and possibly source-folder recovery before ownership assignment. |

## 3) Per-product decision notes (all 12 rows)

1. **Item 138 — Activate Sports Bra**  
   - Collision destination: `.../--collection/activate/black`.  
   - Competing model: `ryderwear_female_momentum_sports_bra_twist`.  
   - Evidence: worksheet status `confirmed_duplicate_destination_collision`; exception `duplicate-destination-collision`; action `needs_manual_review`.  
   - Needed decision: confirm whether `activate` folder is canonical for item 138 or should belong to momentum competitor.

2. **Item 153 — Lift 2.0 Seamless Sports Bra**  
   - Collision destination: `.../--collection/lift/black`.  
   - Competing model: row 154 external ID.  
   - Evidence: pairwise reciprocal competitor relation in worksheet with same destination path.  
   - Needed decision: determine if item 153 owns this destination or must be split to a distinct variant folder.

3. **Item 154 — Lift Rib Seamless Halter Sports Bra**  
   - Collision destination: `.../--collection/lift/black`.  
   - Competing model: row 153 external ID.  
   - Evidence: same destination + reciprocal competitor link.  
   - Needed decision: same adjudication as item 153; choose single owner or split folders.

4. **Item 157 — NKD Bandeau Sports Bra**  
   - Collision destination: `.../low-support/staples/black`.  
   - Competing model: row 163 external ID.  
   - Evidence: confirmed duplicate destination and mirrored competitor between bandeau and one-shoulder variant.  
   - Needed decision: decide if staples folder is truly shared or must separate by cut type.

5. **Item 158 — NKD Core Bra**  
   - Collision destination: `.../light-support/embody/blue`.  
   - Competing model: row 160 external ID.  
   - Evidence: same destination mapped to two different style descriptors.  
   - Needed decision: determine owner of embody folder vs introducing separate core path.

6. **Item 160 — NKD Embody Sports Crop**  
   - Collision destination: `.../light-support/embody/blue`.  
   - Competing model: row 158 external ID.  
   - Evidence: reciprocal pair collision with item 158.  
   - Needed decision: same as above; approve canonical owner or split destinations.

7. **Item 162 — NKD Knot Sports Bra**  
   - Collision destination: `.../tank/cross-over/white`.  
   - Competing model: row 174 external ID.  
   - Evidence: paired collision in worksheet with cross-over destination.  
   - Needed decision: assign ownership to knot or twist lineage, then re-path the other.

8. **Item 163 — NKD One Shoulder Sports Bra**  
   - Collision destination: `.../low-support/staples/black`.  
   - Competing model: row 157 external ID.  
   - Evidence: pairwise collision confirmation and duplicate exception.  
   - Needed decision: decide shared vs split ownership with bandeau variant.

9. **Item 166 — NKD Scrunch V Halter Bra**  
   - Collision destination: `.../scrunch/bra/espresso`.  
   - Competing model: row 176 external ID.  
   - Evidence: confirmed duplicate destination against underwire keyhole competitor.  
   - Needed decision: determine if scrunch folder is canonical for one style or needs structural split.

10. **Item 174 — NKD Twist Sports Bra**  
    - Collision destination: `.../tank/cross-over/white`.  
    - Competing model: row 162 external ID.  
    - Evidence: reciprocal collision with knot variant in same destination folder.  
    - Needed decision: confirm twist vs knot owner and support-level foldering policy.

11. **Item 176 — NKD Underwire Keyhole Sports Bra**  
    - Collision destination: `.../scrunch/bra/espresso`.  
    - Competing model: row 166 external ID.  
    - Evidence: same destination collision despite distinct construction keywords (underwire/keyhole/racerback).  
    - Needed decision: owner assignment or split structural destination.

12. **Item 184 — Sculpt Seamless Halter Sports Bra**  
    - Collision destination: `.../--collection/sculpt/halter-bra/azure`.  
    - Competing models/items: 3 model IDs listed in worksheet (rib seamless, stonewash, icon).  
    - Evidence: exception-derived collision with `recommended_owner=unassigned`; notes explicitly say ownership is not deterministically inferable.  
    - Needed decision: require manual source verification and potential source-folder recovery before any mapping.

## 4) Human decision options (standardized)
For every collision row/group, reviewer may choose one:

1. **Assign destination ownership to this product** (current row becomes canonical owner).
2. **Assign destination ownership to competing product** (current row must be remapped or deferred).
3. **Split into separate destination folders** (create disambiguated folder paths per product/model/cut).
4. **Require source-folder recovery** (insufficient evidence in current artifact set to map safely).
5. **Defer until image source is verified** (block mapping pending provenance checks).
6. **Mark as not currently remediable** (no safe action until taxonomy/source standards are clarified).

## 5) Recommended safest sequence
1. Resolve any clearer same-family pair collisions first (e.g., reciprocal two-row pairs with explicit paired competitors).  
2. Defer more ambiguous style-overlap sports-bra variants where one folder may represent multiple close products (especially broad "staples", "embody", "scrunch", "cross-over").  
3. Do **not** map/copy any row until destination owner/path is explicitly approved by human adjudication.  
4. After approvals, generate a **new non-destructive copy/mapping plan artifact** first (documentation/worksheet), then stage separate downstream approval steps for any future DB/ProductDB actions.

## 6) Encoding check
The referenced next-step summary heading currently renders as `Inactive Products Image Remediation — Next Step Summary` (em dash appears valid in the current file view). No `â€”` artifact was observed during this task.

## 7) Non-goals and safety boundaries
This report intentionally performs **documentation-only** work. Specifically:
- No DB/MySQL changes.
- No ProductDB changes.
- No image copy/move/rename/delete operations.
- No SQL generation.
- No code/admin/runtime/frontend changes.
- No activation, publication, or featured-flag changes.

## Coverage validation checklist
- Total Ryderwear duplicate-collision rows covered: **12/12**.
- Every worksheet row is represented either in the collision-group table and in the per-product notes.
- Grouping performed by duplicate destination path/folder.
