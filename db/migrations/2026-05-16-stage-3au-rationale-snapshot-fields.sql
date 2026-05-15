-- Stage 3AU: Rationale snapshot/comparison storage extension
-- Storage-only migration for future decision-type-first rationale records.
--
-- This migration prepares hero_override_rationale to store:
--   * selected-image snapshot details,
--   * system-ranked #1 snapshot details,
--   * optional displaced-current-hero snapshot details,
--   * criteria/shortlist snapshot context,
--   * decision-type and comparison-target role,
--   * product-specific reasons/cross-cutting signals,
--   * eligibility/data-quality flags,
--   * optional structured snapshot metadata.
--
-- Important:
--   * No runtime behavior is changed by this migration.
--   * No existing columns are removed or renamed.
--   * No existing records are backfilled or rewritten.
--   * Legacy rationale records remain valid for historical/exploratory review,
--     but should not be automatically interpreted as clean criteria-refinement
--     training data.

ALTER TABLE hero_override_rationale
    -- Product snapshot
    ADD COLUMN product_name_snapshot VARCHAR(255) DEFAULT NULL AFTER itemId,
    ADD COLUMN brand_snapshot VARCHAR(100) DEFAULT NULL AFTER product_name_snapshot,

    -- Selected image snapshot
    ADD COLUMN selected_image_path VARCHAR(1024) DEFAULT NULL AFTER brand_snapshot,
    ADD COLUMN selected_image_rank_snapshot SMALLINT UNSIGNED DEFAULT NULL AFTER selected_image_path,
    ADD COLUMN selected_image_score_snapshot DECIMAL(10,4) DEFAULT NULL AFTER selected_image_rank_snapshot,
    ADD COLUMN selected_image_role VARCHAR(80) DEFAULT NULL AFTER selected_image_score_snapshot,

    -- System-ranked #1 snapshot
    ADD COLUMN ranked_1_image_path_snapshot VARCHAR(1024) DEFAULT NULL AFTER selected_image_role,
    ADD COLUMN ranked_1_image_score_snapshot DECIMAL(10,4) DEFAULT NULL AFTER ranked_1_image_path_snapshot,
    ADD COLUMN ranked_1_image_role VARCHAR(80) DEFAULT NULL AFTER ranked_1_image_score_snapshot,
    ADD COLUMN ranked_1_reason_snapshot TEXT DEFAULT NULL AFTER ranked_1_image_role,

    -- Displaced current hero snapshot
    ADD COLUMN displaced_current_hero_path_snapshot VARCHAR(1024) DEFAULT NULL AFTER ranked_1_reason_snapshot,
    ADD COLUMN displaced_current_hero_rank_snapshot SMALLINT UNSIGNED DEFAULT NULL AFTER displaced_current_hero_path_snapshot,
    ADD COLUMN displaced_current_hero_role VARCHAR(80) DEFAULT NULL AFTER displaced_current_hero_rank_snapshot,

    -- Criteria/shortlist snapshot
    ADD COLUMN criteria_profile_snapshot VARCHAR(128) DEFAULT NULL AFTER displaced_current_hero_role,
    ADD COLUMN shortlist_basis_snapshot VARCHAR(128) DEFAULT NULL AFTER criteria_profile_snapshot,

    -- Decision model snapshot
    ADD COLUMN decision_type VARCHAR(80) DEFAULT NULL AFTER shortlist_basis_snapshot,
    ADD COLUMN comparison_target_role VARCHAR(80) DEFAULT NULL AFTER decision_type,

    -- Future reason/signal storage (LONGTEXT by convention; no JSON type dependency)
    ADD COLUMN product_specific_reason_codes LONGTEXT DEFAULT NULL AFTER comparison_target_role,
    ADD COLUMN cross_cutting_signal_codes LONGTEXT DEFAULT NULL AFTER product_specific_reason_codes,
    ADD COLUMN reviewer_note TEXT DEFAULT NULL AFTER cross_cutting_signal_codes,

    -- Eligibility flags
    ADD COLUMN counts_toward_criteria_refinement TINYINT(1) NOT NULL DEFAULT 0 AFTER reviewer_note,
    ADD COLUMN data_quality_only TINYINT(1) NOT NULL DEFAULT 0 AFTER counts_toward_criteria_refinement,

    -- Optional structured snapshots (stored as LONGTEXT for compatibility)
    ADD COLUMN candidate_snapshot_json LONGTEXT DEFAULT NULL AFTER data_quality_only,
    ADD COLUMN reviewer_metadata_json LONGTEXT DEFAULT NULL AFTER candidate_snapshot_json,

    -- Reporting-oriented indexes for future rationale analysis
    ADD KEY idx_hor_decision_type (decision_type),
    ADD KEY idx_hor_comparison_target_role (comparison_target_role),
    ADD KEY idx_hor_criteria_profile_snapshot (criteria_profile_snapshot),
    ADD KEY idx_hor_counts_toward_criteria_refinement (counts_toward_criteria_refinement),
    ADD KEY idx_hor_data_quality_only (data_quality_only),
    ADD KEY idx_hor_selected_image_rank_snapshot (selected_image_rank_snapshot),
    ADD KEY idx_hor_displaced_current_hero_rank_snapshot (displaced_current_hero_rank_snapshot),
    ADD KEY idx_hor_item_decision_type (itemId, decision_type),
    ADD KEY idx_hor_decision_type_counts_refinement (decision_type, counts_toward_criteria_refinement),
    ADD KEY idx_hor_data_quality_decision_type (data_quality_only, decision_type);
