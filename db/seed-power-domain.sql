START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('power', 'Power', 'active', 160)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'power'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'power-without-accountability', 'Power Without Accountability', 10, 'active'),
(@class_id, 'informal-power-networks', 'Informal Power Networks', 20, 'active'),
(@class_id, 'decision-rights-ambiguity', 'Decision Rights Ambiguity', 30, 'active'),
(@class_id, 'status-protection', 'Status Protection', 40, 'active'),
(@class_id, 'gatekeeping', 'Gatekeeping', 50, 'active'),
(@class_id, 'political-behaviour', 'Political Behaviour', 60, 'active'),
(@class_id, 'fear-based-compliance', 'Fear-Based Compliance', 70, 'active'),
(@class_id, 'silencing-and-suppression', 'Silencing and Suppression', 80, 'active'),
(@class_id, 'patronage-and-favouritism', 'Patronage and Favouritism', 90, 'active'),
(@class_id, 'power-hoarding', 'Power Hoarding', 100, 'active'),
(@class_id, 'misuse-of-authority', 'Misuse of Authority', 110, 'active'),
(@class_id, 'dependency-creation', 'Dependency Creation', 120, 'active'),
(@class_id, 'narrative-control', 'Narrative Control', 130, 'active'),
(@class_id, 'resource-control', 'Resource Control', 140, 'active'),
(@class_id, 'expertise-marginalisation', 'Expertise Marginalisation', 150, 'active'),
(@class_id, 'escalation-avoidance', 'Escalation Avoidance', 160, 'active'),
(@class_id, 'consent-manufacturing', 'Consent Manufacturing', 170, 'active'),
(@class_id, 'coalition-building', 'Coalition Building', 180, 'active'),
(@class_id, 'boundary-overreach', 'Boundary Overreach', 190, 'active'),
(@class_id, 'institutional-capture', 'Institutional Capture', 200, 'active'),
(@class_id, 'invisible-hierarchies', 'Invisible Hierarchies', 210, 'active'),
(@class_id, 'power-asymmetry', 'Power Asymmetry', 220, 'active'),
(@class_id, 'token-participation', 'Token Participation', 230, 'active'),
(@class_id, 'reputation-management', 'Reputation Management', 240, 'active'),
(@class_id, 'resistance-to-challenge', 'Resistance to Challenge', 250, 'active')
ON DUPLICATE KEY UPDATE
  issue_title = VALUES(issue_title),
  sort_order = VALUES(sort_order),
  status = VALUES(status);

DELETE FROM wp_sa_perspective_questions
WHERE class_id = @class_id;

INSERT INTO wp_sa_perspective_questions
(class_id, lens_id, question_text, help_text, sort_order, status)
SELECT
  @class_id,
  l.id,
  q.question_text,
  q.help_text,
  q.sort_order,
  'active'
FROM (
  SELECT 'power' AS lens_slug, 'Who has formal authority, and who actually shapes outcomes?' AS question_text, 'Distinguish visible hierarchy from real influence.' AS help_text, 10 AS sort_order
  UNION ALL SELECT 'informal-organisation', 'Where are decisions being influenced before they become visible?', 'Identify informal networks, conversations, and hidden influence pathways.', 20
  UNION ALL SELECT 'incentives', 'Who benefits from the current arrangement of power?', 'Examine who gains advantage from maintaining the status quo.', 30
  UNION ALL SELECT 'governance', 'Who carries risk without holding authority?', 'Explore mismatches between accountability and decision-making power.', 40
  UNION ALL SELECT 'culture', 'Where is challenge discouraged, punished, or quietly avoided?', 'Assess whether dissent and scrutiny are culturally suppressed.', 50
  UNION ALL SELECT 'epistemic', 'What forms of compliance are being mistaken for agreement?', 'Look for silence, performative alignment, and false consensus.', 60
  UNION ALL SELECT 'stakeholder', 'Which relationships, loyalties, or dependencies distort judgment?', 'Identify personal, political, or structural dependencies affecting decisions.', 70
  UNION ALL SELECT 'institutional-memory', 'What information is controlled, withheld, reframed, or selectively shared?', 'Assess whether information flow is being shaped to preserve power.', 80
  UNION ALL SELECT 'abstraction-gap', 'Where does expertise lose to status, hierarchy, or politics?', 'Examine where knowledge is overridden by authority or influence.', 90
  UNION ALL SELECT 'scenario', 'What would become possible if power were made visible and accountable?', 'Imagine how behaviour and outcomes might change under greater transparency.', 100
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug
WHERE l.status = 'active';

COMMIT;