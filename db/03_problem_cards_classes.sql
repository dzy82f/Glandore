-- =========================================================
-- Glandore – Problem Card Classification Schema
-- Version: v1.0
-- Notes:
-- Provides multi-dimensional classification for problem cards:
-- domain, problem_type, lens, sector.
-- =========================================================


-- ---------------------------------------------------------
-- Classes
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_problem_card_classes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  class_type VARCHAR(60) NOT NULL,   -- domain | problem_type | lens | sector

  description TEXT DEFAULT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  UNIQUE KEY uk_class_slug_type (slug, class_type),

  KEY idx_class_type (class_type),
  KEY idx_class_sort (sort_order)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


-- ---------------------------------------------------------
-- Card ↔ Class Mapping (many-to-many)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS wp_problem_card_class_map (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  card_id BIGINT UNSIGNED NOT NULL,
  class_id BIGINT UNSIGNED NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  UNIQUE KEY uk_card_class (card_id, class_id),

  KEY idx_map_card (card_id),
  KEY idx_map_class (class_id),

  CONSTRAINT fk_map_card
    FOREIGN KEY (card_id)
    REFERENCES wp_problem_cards(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_map_class
    FOREIGN KEY (class_id)
    REFERENCES wp_problem_card_classes(id)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_520_ci;


-- ---------------------------------------------------------
-- Seed Data – Initial Classification Set
-- ---------------------------------------------------------
INSERT INTO wp_problem_card_classes
(name, slug, class_type, description, sort_order)
VALUES

-- Domains
('Leadership', 'leadership', 'domain',
 'Authority, alignment, trust, decision-making and influence.',
 10),

('Organisation Design', 'organisation-design', 'domain',
 'Structure, roles, coordination and operating model design.',
 20),

('Change and Transformation', 'change-and-transformation', 'domain',
 'Movement from current state to intended future state.',
 30),


-- Problem Types
('Coordination Failure', 'coordination-failure', 'problem_type',
 'Agreement exists but does not translate into aligned action.',
 10),

('Hidden Power Structure', 'hidden-power-structure', 'problem_type',
 'Real influence sits outside the formal hierarchy.',
 20),

('Cultural Resistance', 'cultural-resistance', 'problem_type',
 'Intent undermined by norms, habits or informal incentives.',
 30),


-- Lenses
('Informal Organisation', 'informal-organisation', 'lens',
 'Shadow networks, trust relationships and informal authority.',
 10),

('Systems Thinking', 'systems-thinking', 'lens',
 'Interdependence, feedback loops, delays and unintended consequences.',
 20),


-- Sector
('Cross-sector', 'cross-sector', 'sector',
 'Applicable across public, private and voluntary sectors.',
 10)

ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  description = VALUES(description),
  sort_order = VALUES(sort_order);