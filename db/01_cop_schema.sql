-- =========================================================
-- Glandore – Community of Practice (CoP) Schema
-- Version: v1.0
-- Notes:
-- Core discussion, contribution, snapshot and contributor model.
-- Designed to sit alongside Problem Card system within same DB.
-- =========================================================

-- ---------------------------------------------------------
-- Threads
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_cop_threads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(191) DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  topic VARCHAR(191) DEFAULT NULL,
  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(20) NOT NULL DEFAULT 'open',

  PRIMARY KEY (id),
  UNIQUE KEY uk_thread_slug (slug),
  KEY idx_thread_status (status),
  KEY idx_thread_created_at (created_at)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;

-- ---------------------------------------------------------
-- Contributions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_cop_contributions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  thread_id BIGINT UNSIGNED NOT NULL,
  snapshot_id BIGINT UNSIGNED DEFAULT NULL,
  wp_post_id BIGINT UNSIGNED DEFAULT NULL,
  user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,

  title VARCHAR(255) DEFAULT NULL,
  content LONGTEXT NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(20) NOT NULL DEFAULT 'published',
  visibility VARCHAR(20) NOT NULL DEFAULT 'registered',

  recipient_user_id BIGINT UNSIGNED DEFAULT NULL,
  email_sent_at DATETIME DEFAULT NULL,

  PRIMARY KEY (id),

  KEY idx_contrib_thread_id (thread_id),
  KEY idx_contrib_snapshot_id (snapshot_id),
  KEY idx_contrib_user_id (user_id),
  KEY idx_contrib_created_at (created_at),
  KEY idx_contrib_status (status),
  KEY idx_contrib_visibility (visibility),
  KEY idx_contrib_recipient (recipient_user_id),

  CONSTRAINT fk_contrib_thread
    FOREIGN KEY (thread_id) REFERENCES wp_cop_threads(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_contrib_snapshot
    FOREIGN KEY (snapshot_id) REFERENCES wp_cop_snapshots(id)
    ON DELETE SET NULL

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;

-- ---------------------------------------------------------
-- Snapshots
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_cop_snapshots (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  thread_id BIGINT UNSIGNED NOT NULL,
  version INT UNSIGNED NOT NULL,
  prev_snapshot_id BIGINT UNSIGNED DEFAULT NULL,

  content LONGTEXT NOT NULL,

  created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  UNIQUE KEY uk_snapshot_thread_version (thread_id, version),

  KEY idx_snapshot_thread_id (thread_id),
  KEY idx_snapshot_prev_id (prev_snapshot_id),
  KEY idx_snapshot_created_at (created_at),

  CONSTRAINT fk_snapshot_thread
    FOREIGN KEY (thread_id) REFERENCES wp_cop_threads(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_snapshot_prev
    FOREIGN KEY (prev_snapshot_id) REFERENCES wp_cop_snapshots(id)
    ON DELETE SET NULL

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;

-- ---------------------------------------------------------
-- Contributors
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_cop_contributor (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  display_name VARCHAR(190) NOT NULL DEFAULT '',
  is_virtual TINYINT(1) NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uk_contributor_user (user_id),
  KEY idx_contributor_name (display_name),
  KEY idx_contributor_virtual (is_virtual)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;

-- ---------------------------------------------------------
-- Contributor Profiles
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_cop_contributor_profile (
  contributor_id INT UNSIGNED NOT NULL,

  short_bio TEXT DEFAULT NULL,
  long_bio LONGTEXT DEFAULT NULL,
  headline VARCHAR(255) DEFAULT NULL,
  organisation VARCHAR(255) DEFAULT NULL,
  location VARCHAR(190) DEFAULT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (contributor_id),

  KEY idx_profile_headline (headline),
  KEY idx_profile_organisation (organisation),
  KEY idx_profile_location (location),

  CONSTRAINT fk_profile_contributor
    FOREIGN KEY (contributor_id) REFERENCES wp_cop_contributor(id)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;