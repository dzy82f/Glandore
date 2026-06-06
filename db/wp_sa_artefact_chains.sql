CREATE TABLE wp_sa_artefact_chains (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_artefact_id BIGINT UNSIGNED NOT NULL,
  parent_snapshot_id BIGINT UNSIGNED NOT NULL,
  child_artefact_id BIGINT UNSIGNED NOT NULL,
  chain_type VARCHAR(50) NOT NULL DEFAULT 'domain_reinterpretation',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY parent_artefact_id (parent_artefact_id),
  KEY parent_snapshot_id (parent_snapshot_id),
  KEY child_artefact_id (child_artefact_id)
);