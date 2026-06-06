START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('capability-skills', 'Capability & Skills', 'active', 210)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'capability-skills'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'skills-gap', 'Skills Gap', 10, 'active'),
(@class_id, 'capability-fragility', 'Capability Fragility', 20, 'active'),
(@class_id, 'single-point-of-failure', 'Single Point of Failure', 30, 'active'),
(@class_id, 'training-without-transfer', 'Training Without Transfer', 40, 'active'),
(@class_id, 'knowledge-silos', 'Knowledge Silos', 50, 'active'),
(@class_id, 'role-capability-mismatch', 'Role–Capability Mismatch', 60, 'active'),
(@class_id, 'leadership-capability-gap', 'Leadership Capability Gap', 70, 'active'),
(@class_id, 'dependency-on-heroes', 'Dependency on Heroes', 80, 'active'),
(@class_id, 'loss-of-institutional-knowledge', 'Loss of Institutional Knowledge', 90, 'active'),
(@class_id, 'capability-underutilisation', 'Capability Underutilisation', 100, 'active'),
(@class_id, 'poor-succession-readiness', 'Poor Succession Readiness', 110, 'active'),
(@class_id, 'low-learning-capacity', 'Low Learning Capacity', 120, 'active'),
(@class_id, 'digital-capability-gap', 'Digital Capability Gap', 130, 'active'),
(@class_id, 'inconsistent-competence', 'Inconsistent Competence', 140, 'active'),
(@class_id, 'change-fatigue-skills-erosion', 'Change Fatigue & Skills Erosion', 150, 'active')
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
    'Why does this capability or skills problem continue despite previous interventions?' AS question_text,
    'Explore the deeper structural, behavioural, or leadership causes beneath the visible capability issue.' AS help_text,
    10 AS sort_order

  UNION ALL SELECT
    'institutional-memory',
    'What critical knowledge, experience, or practice is not being retained or shared?',
    'Consider retirements, turnover, undocumented processes, tacit knowledge, and dependency on individuals.',
    20

  UNION ALL SELECT
    'learning-loop',
    'How effectively does the organisation learn from mistakes, delivery problems, or past initiatives?',
    'Examine whether reflection, feedback, mentoring, and improvement loops actually change behaviour.',
    30

  UNION ALL SELECT
    'delivery',
    'How is the capability issue affecting operational delivery, quality, resilience, or responsiveness?',
    'Focus on service outcomes, execution risk, delays, inconsistency, or operational fragility.',
    40

  UNION ALL SELECT
    'power',
    'Who controls access to development opportunities, progression, knowledge, or influence?',
    'Consider informal gatekeeping, favouritism, hierarchy, and unequal access to capability growth.',
    50

  UNION ALL SELECT
    'culture',
    'What cultural norms or behaviours are reinforcing weak capability development or knowledge sharing?',
    'Explore attitudes to learning, experimentation, mentoring, failure, and professional growth.',
    60

  UNION ALL SELECT
    'risk',
    'What risks emerge if the current capability profile remains unchanged?',
    'Examine resilience, continuity, succession, dependency, compliance, cyber, operational, or strategic risks.',
    70

  UNION ALL SELECT
    'informal-organisation',
    'How does the informal organisation shape who learns, who contributes, and whose expertise is valued?',
    'Consider trust networks, reputation, unofficial mentors, hidden experts, and exclusion dynamics.',
    80

  UNION ALL SELECT
    'epistemic',
    'How confident is the organisation that it truly understands its capability strengths and weaknesses?',
    'Explore blind spots, assumptions, outdated competency models, and false confidence.',
    90

  UNION ALL SELECT
    'complexity',
    'How does organisational complexity make capability development, coordination, or knowledge transfer harder?',
    'Consider fragmentation, multiple systems, matrix structures, competing priorities, and scaling problems.',
    100

  UNION ALL SELECT
    'scenario',
    'What future scenarios could expose current capability weaknesses or make them more severe?',
    'Consider technology shifts, retirements, crises, restructuring, AI adoption, regulation, or growth.',
    110

  UNION ALL SELECT
    'abstraction-gap',
    'Where is there a disconnect between formal capability frameworks and the reality of day-to-day work?',
    'Examine whether competency models, training plans, or organisational charts reflect lived practice.',
    120
) q
  ON l.lens_slug = q.lens_slug
WHERE l.status = 'active';

COMMIT;

SELECT
  c.class_title,
  COUNT(DISTINCT i.id) AS issues,
  COUNT(DISTINCT pq.id) AS perspective_questions
FROM wp_sa_classes c
LEFT JOIN wp_sa_issues i
  ON i.class_id = c.id
LEFT JOIN wp_sa_perspective_questions pq
  ON pq.class_id = c.id
WHERE c.class_slug = 'capability-skills'
GROUP BY c.id;