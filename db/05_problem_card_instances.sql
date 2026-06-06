-- =========================================================
-- Glandore – Problem Card Instances Extension
-- Version: v1.1
-- Notes:
-- Adds named, classified real-world problem instances.
-- =========================================================

CREATE TABLE IF NOT EXISTS wp_problem_card_instances (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  card_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED DEFAULT NULL,

  title VARCHAR(255) NOT NULL,
  description LONGTEXT DEFAULT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'active',

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_instance_card (card_id),
  KEY idx_instance_user (user_id),
  KEY idx_instance_status (status),

  CONSTRAINT fk_instance_card
    FOREIGN KEY (card_id)
    REFERENCES wp_problem_cards(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS wp_problem_card_instance_class_map (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  instance_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  UNIQUE KEY uk_instance_class (instance_id, class_id),
  KEY idx_map_instance (instance_id),
  KEY idx_map_class (class_id),

  CONSTRAINT fk_instance_class_map_instance
    FOREIGN KEY (instance_id)
    REFERENCES wp_problem_card_instances(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_instance_class_map_class
    FOREIGN KEY (class_id)
    REFERENCES wp_problem_card_classes(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


ALTER TABLE wp_problem_card_sessions
ADD COLUMN instance_id BIGINT UNSIGNED DEFAULT NULL AFTER card_id,
ADD KEY idx_session_instance (instance_id),
ADD CONSTRAINT fk_session_instance
  FOREIGN KEY (instance_id)
  REFERENCES wp_problem_card_instances(id)
  ON DELETE SET NULL;