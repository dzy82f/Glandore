START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('culture', 'Culture', 'active', 170)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
SELECT c.id, v.issue_slug, v.issue_title, v.sort_order, 'active'
FROM wp_sa_classes c
JOIN (
  SELECT 'fear-of-speaking-up' AS issue_slug, 'Fear of Speaking Up' AS issue_title, 10 AS sort_order
  UNION ALL SELECT 'blame-culture', 'Blame Culture', 20
  UNION ALL SELECT 'meeting-culture', 'Meeting Culture', 30
  UNION ALL SELECT 'learned-helplessness', 'Learned Helplessness', 40
  UNION ALL SELECT 'hero-dependency', 'Hero Dependency', 50
  UNION ALL SELECT 'cynicism-drift', 'Cynicism Drift', 60
  UNION ALL SELECT 'compliance-theatre', 'Compliance Theatre', 70
  UNION ALL SELECT 'silo-identity', 'Silo Identity', 80
  UNION ALL SELECT 'innovation-aversion', 'Innovation Aversion', 90
  UNION ALL SELECT 'busyness-as-status', 'Busyness as Status', 100
  UNION ALL SELECT 'psychological-unsafety', 'Psychological Unsafety', 110
  UNION ALL SELECT 'change-fatigue', 'Change Fatigue', 120
  UNION ALL SELECT 'ritual-without-meaning', 'Ritual Without Meaning', 130
  UNION ALL SELECT 'perfectionism-pressure', 'Perfectionism Pressure', 140
  UNION ALL SELECT 'informal-exclusion', 'Informal Exclusion', 150
) v
WHERE c.class_slug = 'culture'
ON DUPLICATE KEY UPDATE
  issue_title = VALUES(issue_title),
  sort_order  = VALUES(sort_order),
  status      = VALUES(status);

DELETE pq
FROM wp_sa_perspective_questions pq
JOIN wp_sa_classes c
  ON c.id = pq.class_id
WHERE c.class_slug = 'culture';

INSERT INTO wp_sa_perspective_questions
(class_id, lens_id, question_text, help_text, sort_order, status)
SELECT
  c.id,
  l.id,
  q.question_text,
  q.help_text,
  q.sort_order,
  'active'
FROM wp_sa_classes c
JOIN (
  SELECT '5-whys' AS lens_slug, 'Why has this behaviour become normal here?' AS question_text, 'Explore the deeper conditions and repeated reinforcements that have normalised this behaviour over time.' AS help_text, 10 AS sort_order
  UNION ALL SELECT 'complexity', 'What unintended cultural patterns are emerging from the wider system?', 'Consider how structure, incentives, workload, history, and informal behaviours combine to create unexpected cultural effects.', 20
  UNION ALL SELECT 'stakeholder', 'Who benefits from the current culture and who is diminished by it?', 'Identify which groups gain influence, safety, visibility, or advantage — and which groups become marginalised or silenced.', 30
  UNION ALL SELECT 'governance', 'What behaviours are formally discouraged but informally tolerated?', 'Look for gaps between official standards and the behaviours that are accepted in practice.', 40
  UNION ALL SELECT 'institutional-memory', 'What past events, stories, or scars still shape behaviour today?', 'Consider whether previous restructures, failures, crises, or leadership behaviours still influence current culture.', 50
  UNION ALL SELECT 'incentives', 'What behaviours are rewarded in practice, regardless of stated values?', 'Examine what people learn they must do to succeed, survive, or gain recognition.', 60
  UNION ALL SELECT 'power', 'Who can safely challenge the culture and who cannot?', 'Assess how hierarchy, influence, status, and informal alliances affect people’s ability to speak openly.', 70
  UNION ALL SELECT 'learning-loop', 'How does the organisation respond when mistakes or dissent appear?', 'Explore whether reflection and challenge lead to learning, defensiveness, blame, or silence.', 80
  UNION ALL SELECT 'causal-loop', 'What behaviours reinforce and perpetuate the current culture?', 'Identify repeating cycles where behaviour, incentives, reactions, and assumptions strengthen one another.', 90
  UNION ALL SELECT 'epistemic', 'What truths are difficult or unsafe to express here?', 'Look for topics, concerns, or realities that people avoid discussing openly.', 100
  UNION ALL SELECT 'scenario', 'If nothing changes, what culture is likely to emerge over the next few years?', 'Project the likely future emotional climate, behaviours, and norms if current patterns continue.', 110
  UNION ALL SELECT 'delivery', 'How is the culture affecting execution, collaboration, or reliability?', 'Examine the impact of culture on coordination, ownership, responsiveness, and outcomes.', 120
  UNION ALL SELECT 'risk', 'What risks are being normalised or silently absorbed?', 'Identify unsafe assumptions, tolerated behaviours, or hidden fragilities that people have stopped noticing.', 130
  UNION ALL SELECT 'informal-organisation', 'Where does the real culture differ from the official narrative?', 'Explore how influence, trust, communication, and behaviour operate beneath the formal structure.', 140
  UNION ALL SELECT 'abstraction-gap', 'How different is leadership’s description of the culture from lived experience?', 'Compare executive narratives and stated values with frontline reality.', 150
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug
WHERE c.class_slug = 'culture';

COMMIT;