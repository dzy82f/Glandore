-- =========================================================
-- Glandore – Problem Cards Schema
-- Version: v1.0
-- Notes:
-- Core step-driven Problem Card model.
-- Assumes WordPress table prefix is wp_.
-- =========================================================

CREATE TABLE IF NOT EXISTS wp_problem_cards (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(191) NOT NULL,
  title VARCHAR(255) NOT NULL,
  strapline TEXT DEFAULT NULL,
  problem_statement LONGTEXT DEFAULT NULL,
  intended_user TEXT DEFAULT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'draft',
  version VARCHAR(50) NOT NULL DEFAULT '0.1',
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_by BIGINT UNSIGNED DEFAULT NULL,
  updated_by BIGINT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_card_slug (slug),
  KEY idx_card_status (status),
  KEY idx_card_display_order (display_order)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_steps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  card_id BIGINT UNSIGNED NOT NULL,
  step_key VARCHAR(191) NOT NULL,
  step_title VARCHAR(255) NOT NULL,
  step_type VARCHAR(50) NOT NULL DEFAULT 'reflect',
  intro LONGTEXT DEFAULT NULL,
  completion_prompt LONGTEXT DEFAULT NULL,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_required TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_step_card_key (card_id, step_key),
  KEY idx_step_card_order (card_id, display_order),

  CONSTRAINT fk_step_card
    FOREIGN KEY (card_id)
    REFERENCES wp_problem_cards(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_step_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  step_id BIGINT UNSIGNED NOT NULL,
  item_key VARCHAR(191) NOT NULL,
  item_type VARCHAR(50) NOT NULL DEFAULT 'textarea',
  item_label VARCHAR(255) NOT NULL,
  item_help LONGTEXT DEFAULT NULL,
  placeholder_text LONGTEXT DEFAULT NULL,
  options_json LONGTEXT DEFAULT NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_item_step_key (step_id, item_key),
  KEY idx_item_step_order (step_id, display_order),

  CONSTRAINT fk_item_step
    FOREIGN KEY (step_id)
    REFERENCES wp_problem_card_steps(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  card_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  session_key CHAR(64) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'in_progress',
  current_step_id BIGINT UNSIGNED DEFAULT NULL,
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME DEFAULT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_session_card_key (card_id, session_key),
  KEY idx_session_user_card (user_id, card_id),
  KEY idx_session_status (status),
  KEY idx_session_current_step (current_step_id),

  CONSTRAINT fk_session_card
    FOREIGN KEY (card_id)
    REFERENCES wp_problem_cards(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_session_current_step
    FOREIGN KEY (current_step_id)
    REFERENCES wp_problem_card_steps(id)
    ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_responses (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id BIGINT UNSIGNED NOT NULL,
  step_id BIGINT UNSIGNED NOT NULL,
  item_id BIGINT UNSIGNED NOT NULL,
  response_text LONGTEXT DEFAULT NULL,
  response_json LONGTEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_response_session_item (session_id, item_id),
  KEY idx_response_session_step (session_id, step_id),
  KEY idx_response_item (item_id),

  CONSTRAINT fk_response_session
    FOREIGN KEY (session_id)
    REFERENCES wp_problem_card_sessions(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_response_step
    FOREIGN KEY (step_id)
    REFERENCES wp_problem_card_steps(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_response_item
    FOREIGN KEY (item_id)
    REFERENCES wp_problem_card_step_items(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_snapshots (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id BIGINT UNSIGNED NOT NULL,
  snapshot_title VARCHAR(255) NOT NULL,
  snapshot_markdown LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_snapshot_session_created (session_id, created_at),

  CONSTRAINT fk_snapshot_session
    FOREIGN KEY (session_id)
    REFERENCES wp_problem_card_sessions(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_relationships (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  card_id BIGINT UNSIGNED NOT NULL,
  related_card_id BIGINT UNSIGNED NOT NULL,
  relationship_type VARCHAR(50) NOT NULL DEFAULT 'related',
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_relationship_card_related_type (card_id, related_card_id, relationship_type),
  KEY idx_relationship_card_order (card_id, display_order),
  KEY idx_relationship_related_card (related_card_id),

  CONSTRAINT fk_relationship_card
    FOREIGN KEY (card_id)
    REFERENCES wp_problem_cards(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_relationship_related_card
    FOREIGN KEY (related_card_id)
    REFERENCES wp_problem_cards(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;