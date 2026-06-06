START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('knowledge-information', 'Knowledge & Information', 'active', 210)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'knowledge-information'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'knowledge-hoarding', 'Knowledge Hoarding', 10, 'active'),
(@class_id, 'information-overload', 'Information Overload', 20, 'active'),
(@class_id, 'institutional-memory-loss', 'Institutional Memory Loss', 30, 'active'),
(@class_id, 'fragmented-information-landscape', 'Fragmented Information Landscape', 40, 'active'),
(@class_id, 'poor-knowledge-transfer', 'Poor Knowledge Transfer', 50, 'active'),
(@class_id, 'decision-making-with-poor-information', 'Decision-Making with Poor Information', 60, 'active'),
(@class_id, 'untrusted-information', 'Untrusted Information', 70, 'active'),
(@class_id, 'data-rich-insight-poor', 'Data Rich, Insight Poor', 80, 'active'),
(@class_id, 'hidden-expertise', 'Hidden Expertise', 90, 'active'),
(@class_id, 'documentation-decay', 'Documentation Decay', 100, 'active'),
(@class_id, 'misaligned-reporting', 'Misaligned Reporting', 110, 'active'),
(@class_id, 'loss-of-context', 'Loss of Context', 120, 'active'),
(@class_id, 'knowledge-dependency-risk', 'Knowledge Dependency Risk', 130, 'active'),
(@class_id, 'signal-vs-noise', 'Signal vs Noise', 140, 'active'),
(@class_id, 'information-gaming', 'Information Gaming', 150, 'active'),
(@class_id, 'failure-to-learn', 'Failure to Learn', 160, 'active'),
(@class_id, 'knowledge-silos', 'Knowledge Silos', 170, 'active'),
(@class_id, 'lack-of-shared-understanding', 'Lack of Shared Understanding', 180, 'active'),
(@class_id, 'missing-feedback-loops', 'Missing Feedback Loops', 190, 'active'),
(@class_id, 'epistemic-fragility', 'Epistemic Fragility', 200, 'active')
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
FROM
  wp_sa_lenses l
JOIN (
  SELECT
    '5-whys' AS lens_slug,
    'Why does this knowledge or information problem keep recurring despite repeated interventions?' AS question_text,
    'Look beneath surface symptoms to recurring structural causes.' AS help_text,
    10 AS sort_order

  UNION ALL SELECT
    'institutional-memory',
    'What important organisational memory has been lost, ignored, or failed to transfer?',
    'Consider retirements, restructures, turnover, outsourcing, or repeated reinvention.',
    20

  UNION ALL SELECT
    'epistemic',
    'How does the organisation decide what is treated as credible, true, or important?',
    'Examine authority, evidence, assumptions, blind spots, and competing narratives.',
    30

  UNION ALL SELECT
    'learning-loop',
    'How effectively does the organisation learn from experience, failure, and success?',
    'Assess whether lessons are captured, shared, retained, and acted upon.',
    40

  UNION ALL SELECT
    'power',
    'Who controls access to information, interpretation, or expertise?',
    'Look for gatekeeping, selective disclosure, informal influence, or dependency.',
    50

  UNION ALL SELECT
    'culture',
    'What cultural behaviours shape how knowledge is shared, hidden, or ignored?',
    'Consider trust, blame, politics, fear, incentives, and psychological safety.',
    60

  UNION ALL SELECT
    'complexity',
    'How has complexity made information harder to interpret, connect, or act upon?',
    'Explore fragmentation, overload, ambiguity, and unintended interactions.',
    70

  UNION ALL SELECT
    'stakeholder',
    'Which stakeholders experience this information landscape differently, and why?',
    'Compare perspectives across teams, levels, professions, and external actors.',
    80

  UNION ALL SELECT
    'governance',
    'What governance structures shape the quality, ownership, and flow of information?',
    'Assess accountability, stewardship, escalation, assurance, and oversight.',
    90

  UNION ALL SELECT
    'risk',
    'What operational, strategic, or reputational risks arise from poor knowledge and information practices?',
    'Identify hidden vulnerabilities, single points of failure, and delayed consequences.',
    100

  UNION ALL SELECT
    'delivery',
    'How does this issue affect execution, coordination, and organisational effectiveness?',
    'Focus on friction, duplication, delay, confusion, or failed implementation.',
    110

  UNION ALL SELECT
    'informal-organisation',
    'What unofficial networks or workarounds are compensating for weaknesses in formal information systems?',
    'Look for shadow systems, trusted individuals, and unofficial communication channels.',
    120

  UNION ALL SELECT
    'causal-loop',
    'What reinforcing loops keep the organisation trapped in poor information behaviours?',
    'Identify cycles such as overload → disengagement → poorer decisions → more reporting.',
    130

  UNION ALL SELECT
    'abstraction-gap',
    'Where is important operational reality being lost through abstraction, reporting, or simplification?',
    'Examine disconnects between frontline experience and executive interpretation.',
    140

  UNION ALL SELECT
    'scenario',
    'What future scenarios could emerge if this knowledge and information problem continues unresolved?',
    'Consider degradation, brittleness, loss of capability, or institutional failure.',
    150
) q
  ON l.lens_slug = q.lens_slug
WHERE l.status = 'active';

COMMIT;