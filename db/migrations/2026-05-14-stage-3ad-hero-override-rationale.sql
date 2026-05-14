-- Stage 3AD: Human Override Rationale storage migration
-- Purpose: add a history-capable table to store administrator rationale
-- when selecting/confirming hero images that differ from recommendations.

CREATE TABLE IF NOT EXISTS hero_override_rationale (
    rationale_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    itemId INT NOT NULL,

    -- Snapshot of hero decision context at save time
    selected_hero_image VARCHAR(1024) NOT NULL,
    current_hero_image VARCHAR(1024) DEFAULT NULL,
    active_criteria_profile VARCHAR(128) DEFAULT NULL,
    shortlist_basis VARCHAR(128) DEFAULT NULL,
    current_hero_rank TINYINT UNSIGNED DEFAULT NULL,
    current_hero_outside_top_three TINYINT(1) NOT NULL DEFAULT 0,

    -- Structured rationale payload
    selected_reason_codes JSON NOT NULL,
    optional_note TEXT DEFAULT NULL,

    -- Signals for future criteria/image quality improvements
    criteria_refinement_signal TINYINT(1) NOT NULL DEFAULT 0,
    image_set_limitation_signal TINYINT(1) NOT NULL DEFAULT 0,
    metadata_issue_signal TINYINT(1) NOT NULL DEFAULT 0,
    diagnostics_issue_signal TINYINT(1) NOT NULL DEFAULT 0,

    -- Active/supersession tracking for history-capable storage
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    superseded_at DATETIME DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (rationale_id),
    KEY idx_hor_item (itemId),
    KEY idx_hor_is_active (is_active),
    KEY idx_hor_item_active (itemId, is_active),
    KEY idx_hor_active_criteria_profile (active_criteria_profile),
    KEY idx_hor_created_at (created_at),
    KEY idx_hor_criteria_refinement_signal (criteria_refinement_signal),
    KEY idx_hor_image_set_limitation_signal (image_set_limitation_signal),
    KEY idx_hor_metadata_issue_signal (metadata_issue_signal),
    KEY idx_hor_diagnostics_issue_signal (diagnostics_issue_signal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
