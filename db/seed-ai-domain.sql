START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('ai-organisational-intelligence', 'AI & Organisational Intelligence', 'active', 190)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'ai-organisational-intelligence'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'ai-strategy-execution-gap', 'AI Strategy–Execution Gap', 10, 'active'),
(@class_id, 'shadow-ai-fragmentation', 'Shadow AI & Fragmentation', 20, 'active'),
(@class_id, 'copilot-without-transformation', 'Copilot Adoption Without Transformation', 30, 'active'),
(@class_id, 'poor-ai-roi', 'Poor Return on AI Investment', 40, 'active'),
(@class_id, 'decision-making-bottlenecks', 'Decision-Making Bottlenecks', 50, 'active'),
(@class_id, 'ai-governance-fragility', 'AI Governance Fragility', 60, 'active'),
(@class_id, 'leadership-ai-capability-gap', 'Leadership AI Capability Gap', 70, 'active'),
(@class_id, 'data-rich-insight-poor', 'Data Rich but Insight Poor', 80, 'active'),
(@class_id, 'siloed-ai-experimentation', 'Siloed AI Experimentation', 90, 'active'),
(@class_id, 'automation-without-learning', 'Automation Without Organisational Learning', 100, 'active'),
(@class_id, 'institutional-memory-loss', 'Institutional Memory Loss in AI Transformation', 110, 'active'),
(@class_id, 'ai-driven-operating-model-misalignment', 'AI-Driven Operating Model Misalignment', 120, 'active')
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
    '5-whys' AS lens_slug,
    'What recurring organisational behaviours or assumptions are preventing AI investment from delivering value?' AS question_text,
    'Look beneath the technology to identify repeated structural or behavioural causes.' AS help_text,
    10 AS sort_order

  UNION ALL SELECT
    'complexity',
    'Where is AI interacting unpredictably with existing organisational systems, workflows, or incentives?',
    'Explore unintended consequences, feedback loops, and system interactions.',
    20

  UNION ALL SELECT
    'stakeholder',
    'Which groups benefit from current AI initiatives, and which groups experience friction, exclusion, or loss of influence?',
    'Consider executives, managers, frontline staff, customers, regulators, and suppliers.',
    30

  UNION ALL SELECT
    'governance',
    'Where are accountability, ownership, or decision rights around AI unclear or fragmented?',
    'Examine governance structures, escalation paths, and executive oversight.',
    40

  UNION ALL SELECT
    'institutional-memory',
    'What lessons from previous transformation, technology, or change initiatives are not being reused?',
    'Identify where organisational learning is being lost or ignored.',
    50

  UNION ALL SELECT
    'incentives',
    'What incentives are encouraging AI activity without necessarily improving organisational performance?',
    'Look for KPI distortion, experimentation theatre, or pressure to appear innovative.',
    60

  UNION ALL SELECT
    'power',
    'How is AI changing influence, control, or informal power structures inside the organisation?',
    'Consider who gains authority, visibility, or dependency through AI adoption.',
    70

  UNION ALL SELECT
    'learning-loop',
    'How effectively is the organisation capturing, sharing, and reusing learning from AI initiatives?',
    'Assess whether experimentation produces compounding organisational intelligence.',
    80

  UNION ALL SELECT
    'causal-loop',
    'What reinforcing or balancing loops are shaping AI adoption and organisational behaviour?',
    'Explore how success, fear, pressure, trust, or resistance interact over time.',
    90

  UNION ALL SELECT
    'epistemic',
    'Where is AI improving organisational understanding versus merely accelerating activity?',
    'Focus on decision quality, clarity, judgement, and sense-making.',
    100

  UNION ALL SELECT
    'scenario',
    'What future organisational risks or opportunities emerge if current AI patterns continue unchanged?',
    'Explore optimistic, pessimistic, and disruptive trajectories.',
    110

  UNION ALL SELECT
    'delivery',
    'What operational barriers are preventing AI initiatives from scaling into day-to-day organisational practice?',
    'Look at workflows, adoption, coordination, and execution capability.',
    120

  UNION ALL SELECT
    'risk',
    'Where could current AI approaches create reputational, regulatory, operational, or strategic risk?',
    'Consider both immediate and systemic exposures.',
    130

  UNION ALL SELECT
    'culture',
    'What cultural norms or leadership behaviours are shaping how AI is understood and adopted?',
    'Assess openness, fear, experimentation, trust, and organisational identity.',
    140

  UNION ALL SELECT
    'informal-organisation',
    'How are unofficial networks, relationships, or workarounds influencing AI adoption in practice?',
    'Look beyond formal structures to how work actually gets done.',
    150

  UNION ALL SELECT
    'abstraction-gap',
    'Where does executive AI strategy diverge from operational reality?',
    'Identify disconnects between leadership narratives and frontline experience.',
    160
) q
ON l.lens_slug = q.lens_slug
WHERE l.status = 'active';

COMMIT;