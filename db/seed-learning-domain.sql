START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('learning', 'Learning', 'active', 120)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'learning'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'learning-blindness', 'Learning Blindness', 10, 'active'),
(@class_id, 'failure-to-learn-from-experience', 'Failure to Learn from Experience', 20, 'active'),
(@class_id, 'institutional-forgetting', 'Institutional Forgetting', 30, 'active'),
(@class_id, 'knowledge-hoarding', 'Knowledge Hoarding', 40, 'active'),
(@class_id, 'reflection-deficit', 'Reflection Deficit', 50, 'active'),
(@class_id, 'training-without-transfer', 'Training Without Transfer', 60, 'active'),
(@class_id, 'defensive-learning', 'Defensive Learning', 70, 'active'),
(@class_id, 'surface-learning', 'Surface Learning', 80, 'active'),
(@class_id, 'learned-helplessness', 'Learned Helplessness', 90, 'active'),
(@class_id, 'incentivised-ignorance', 'Incentivised Ignorance', 100, 'active'),
(@class_id, 'expertise-fragility', 'Expertise Fragility', 110, 'active'),
(@class_id, 'ritualised-learning', 'Ritualised Learning', 120, 'active'),
(@class_id, 'knowledge-fragmentation', 'Knowledge Fragmentation', 130, 'active'),
(@class_id, 'epistemic-stagnation', 'Epistemic Stagnation', 140, 'active'),
(@class_id, 'false-competence', 'False Competence', 150, 'active'),
(@class_id, 'failure-to-generalise', 'Failure to Generalise', 160, 'active'),
(@class_id, 'reflection-avoidance', 'Reflection Avoidance', 170, 'active'),
(@class_id, 'learning-overload', 'Learning Overload', 180, 'active'),
(@class_id, 'metrics-without-understanding', 'Metrics Without Understanding', 190, 'active'),
(@class_id, 'punished-curiosity', 'Punished Curiosity', 200, 'active'),
(@class_id, 'no-safe-failure-space', 'No Safe Failure Space', 210, 'active'),
(@class_id, 'learning-debt', 'Learning Debt', 220, 'active'),
(@class_id, 'cargo-cult-practices', 'Cargo Cult Practices', 230, 'active'),
(@class_id, 'narrative-capture', 'Narrative Capture', 240, 'active'),
(@class_id, 'adaptive-failure', 'Adaptive Failure', 250, 'active')
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
  SELECT 'learning-loop' AS lens_slug,
         'Where is feedback failing to become changed behaviour?' AS question_text,
         'Look for weak, missing, delayed or symbolic feedback loops that prevent learning becoming adaptation.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'institutional-memory',
         'What knowledge has the system already had, but lost or ignored?',
         'Consider churn, restructuring, poor handover, lost records, forgotten decisions and repeated rediscovery.',
         20
  UNION ALL SELECT 'epistemic',
         'How does the system decide what counts as valid knowledge?',
         'Examine whose evidence is trusted, whose experience is discounted and what forms of knowing are excluded.',
         30
  UNION ALL SELECT 'culture',
         'What cultural habits encourage or suppress honest learning?',
         'Look for blame, defensiveness, pride, conformity, embarrassment, curiosity or openness.',
         40
  UNION ALL SELECT 'incentives',
         'What is the system rewarding people for not learning?',
         'Identify incentives that favour speed, certainty, performance theatre, silence or short-term delivery over understanding.',
         50
  UNION ALL SELECT 'power',
         'Who benefits when learning is delayed, distorted or avoided?',
         'Consider whether knowledge is being controlled, withheld, filtered or used to preserve authority.',
         60
  UNION ALL SELECT 'informal-organisation',
         'Where does real learning happen outside formal channels?',
         'Look for corridor knowledge, peer coaching, shadow practices, workarounds and unofficial memory.',
         70
  UNION ALL SELECT 'abstraction-gap',
         'Where is there a gap between formal learning and lived practice?',
         'Test whether training, policy or doctrine matches what people actually experience and do.',
         80
  UNION ALL SELECT 'delivery',
         'Why is learning not transferring into execution?',
         'Examine whether lessons, training or reviews are changing decisions, workflows, ownership and follow-through.',
         90
  UNION ALL SELECT 'risk',
         'What future risk is accumulating because learning is not happening now?',
         'Identify learning debt, capability gaps, repeated mistakes and fragile dependence on undocumented expertise.',
         100
  UNION ALL SELECT 'complexity',
         'What makes learning difficult because the system is complex rather than merely complicated?',
         'Look for unclear causality, delayed effects, interdependencies, emergence and unintended consequences.',
         110
  UNION ALL SELECT 'scenario',
         'What would happen if the system continued not to learn?',
         'Explore plausible future consequences if current learning failures compound over time.',
         120
  UNION ALL SELECT '5-whys',
         'Why does the learning failure keep recurring?',
         'Use repeated why-questioning to move from visible learning failure to deeper systemic cause.',
         130
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug
WHERE l.lens_slug IN (
  'learning-loop',
  'institutional-memory',
  'epistemic',
  'culture',
  'incentives',
  'power',
  'informal-organisation',
  'abstraction-gap',
  'delivery',
  'risk',
  'complexity',
  'scenario',
  '5-whys'
);

COMMIT;
