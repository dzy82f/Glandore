START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('water-fountain', 'Water Fountain', 'active', 255)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'water-fountain'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'whisper-networks', 'Whisper Networks', 10, 'active'),
(@class_id, 'collectively-managed-silence', 'Collectively Managed Silence', 20, 'active'),
(@class_id, 'rumour-escalation', 'Rumour Escalation', 30, 'active'),
(@class_id, 'psychological-unsafety', 'Psychological Unsafety', 40, 'active'),
(@class_id, 'trust-erosion', 'Trust Erosion', 50, 'active'),
(@class_id, 'hidden-workarounds', 'Hidden Workarounds', 60, 'active'),
(@class_id, 'shadow-decision-making', 'Shadow Decision-Making', 70, 'active'),
(@class_id, 'narrative-drift', 'Narrative Drift', 80, 'active'),
(@class_id, 'cynicism-contagion', 'Cynicism Contagion', 90, 'active'),
(@class_id, 'silent-knowledge', 'Silent Knowledge', 100, 'active'),
(@class_id, 'institutional-forgetting', 'Institutional Forgetting', 110, 'active'),
(@class_id, 'informal-gatekeeping', 'Informal Gatekeeping', 120, 'active'),
(@class_id, 'emotional-exhaustion-signals', 'Emotional Exhaustion Signals', 130, 'active'),
(@class_id, 'innovation-suppression', 'Innovation Suppression', 140, 'active'),
(@class_id, 'legitimacy-collapse', 'Legitimacy Collapse', 150, 'active')
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
FROM wp_sa_lenses l
JOIN (
  SELECT
    'systems-thinking' AS lens_slug,
    'What is being said privately but not publicly?' AS question_text,
    'Explore divergence between official communication and informal organisational reality.' AS help_text,
    10 AS sort_order

  UNION ALL SELECT
    'power-dynamics',
    'Which truths feel unsafe to voice?',
    'Identify fear, hierarchy, reputation risk, or political sensitivity that suppresses openness.',
    20

  UNION ALL SELECT
    'stakeholder-analysis',
    'Where does trust actually reside?',
    'Examine who people genuinely believe, follow, or rely upon informally.',
    30

  UNION ALL SELECT
    'culture-web',
    'What do newcomers learn unofficially?',
    'Identify hidden norms, survival behaviours, and informal onboarding signals.',
    40

  UNION ALL SELECT
    'critical-thinking',
    'Which topics trigger silence or discomfort?',
    'Look for defended territory, taboo subjects, or emotionally charged avoidance.',
    50

  UNION ALL SELECT
    'narrative-analysis',
    'What stories do people tell each other about this situation?',
    'Explore informal narratives, myths, assumptions, and shared interpretations.',
    60

  UNION ALL SELECT
    'communication-analysis',
    'Which rumours persist despite official communication?',
    'Identify credibility gaps between leadership messaging and lived experience.',
    70

  UNION ALL SELECT
    'systems-thinking',
    'How differently do groups interpret the same events?',
    'Examine fragmentation of organisational reality across teams or hierarchies.',
    80

  UNION ALL SELECT
    'critical-thinking',
    'What assumptions are treated as unquestionable?',
    'Surface invisible cultural rules or socially protected beliefs.',
    90

  UNION ALL SELECT
    'emotional-dynamics',
    'What emotional tone dominates informal conversations?',
    'Assess whether anxiety, cynicism, resignation, optimism, fear, or exhaustion dominate.',
    100

  UNION ALL SELECT
    'power-dynamics',
    'Who influences outcomes informally?',
    'Identify hidden influence networks beyond formal organisational charts.',
    110

  UNION ALL SELECT
    'decision-analysis',
    'Where are decisions actually formed?',
    'Distinguish formal approval processes from informal pre-alignment and negotiation.',
    120

  UNION ALL SELECT
    'stakeholder-analysis',
    'Which individuals or groups act as information brokers?',
    'Map the people through whom informal organisational knowledge flows.',
    130

  UNION ALL SELECT
    'behavioural-analysis',
    'What behaviours are quietly rewarded or punished?',
    'Explore hidden incentives, social approval, and informal sanctions.',
    140

  UNION ALL SELECT
    'power-dynamics',
    'Where is access controlled socially rather than structurally?',
    'Examine favour systems, gatekeeping, and informal exclusion mechanisms.',
    150

  UNION ALL SELECT
    'knowledge-management',
    'What knowledge exists only in people’s heads?',
    'Identify undocumented dependency on tacit or informal expertise.',
    160

  UNION ALL SELECT
    'learning-analysis',
    'Which failures are repeatedly rediscovered?',
    'Explore institutional forgetting and recurring avoidable mistakes.',
    170

  UNION ALL SELECT
    'systems-thinking',
    'What workarounds have become normalised?',
    'Identify adaptive behaviours compensating for structural weakness.',
    180

  UNION ALL SELECT
    'learning-analysis',
    'Which lessons are remembered informally but absent formally?',
    'Examine divergence between lived memory and official learning systems.',
    190

  UNION ALL SELECT
    'signal-detection',
    'What weak signals are appearing repeatedly?',
    'Look for early warnings before problems become formally acknowledged.',
    200
) q
ON l.lens_slug = q.lens_slug
WHERE l.status = 'active';

COMMIT;