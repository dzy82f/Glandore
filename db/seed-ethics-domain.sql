START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('ethics-trust', 'Ethics & Trust', 'active', 260)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'ethics-trust'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'loss-of-trust', 'Loss of Trust', 10, 'active'),
(@class_id, 'ethical-drift', 'Ethical Drift', 20, 'active'),
(@class_id, 'unfair-treatment', 'Unfair Treatment', 30, 'active'),
(@class_id, 'lack-of-transparency', 'Lack of Transparency', 40, 'active'),
(@class_id, 'conflicted-incentives', 'Conflicted Incentives', 50, 'active'),
(@class_id, 'accountability-gap', 'Accountability Gap', 60, 'active'),
(@class_id, 'misuse-of-power', 'Misuse of Power', 70, 'active'),
(@class_id, 'psychological-unsafety', 'Psychological Unsafety', 80, 'active'),
(@class_id, 'values-behaviour-gap', 'Values–Behaviour Gap', 90, 'active'),
(@class_id, 'reputational-risk', 'Reputational Risk', 100, 'active')
ON DUPLICATE KEY UPDATE
  issue_title = VALUES(issue_title),
  sort_order = VALUES(sort_order),
  status = VALUES(status);

DELETE pq
FROM wp_sa_perspective_questions pq
WHERE pq.class_id = @class_id;

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
  SELECT '5-whys' AS lens_slug,
         'Why has this trust or ethics issue been allowed to persist?' AS question_text,
         'Look beneath the immediate breach or behaviour to the repeated conditions, incentives or silences that keep reproducing it.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'stakeholder',
         'Who is being asked to bear the ethical cost of this situation?',
         'Identify who benefits, who is exposed, who is unheard, and whose trust is being taken for granted.',
         20
  UNION ALL SELECT 'power',
         'How is power shaping what can be said, challenged or corrected?',
         'Consider formal authority, informal influence, dependency, fear of retaliation and unequal access to decision-making.',
         30
  UNION ALL SELECT 'governance',
         'Where is accountability unclear, weak or avoided?',
         'Examine whether decision rights, escalation routes, oversight and consequences are strong enough to protect trust.',
         40
  UNION ALL SELECT 'incentives',
         'What incentives may be rewarding behaviour that conflicts with stated values?',
         'Look for targets, bonuses, status, political pressure, convenience or avoidance of embarrassment.',
         50
  UNION ALL SELECT 'culture',
         'What does the culture quietly permit that the formal values condemn?',
         'Focus on norms, stories, tolerated exceptions, everyday behaviours and what people learn is safe or unsafe to challenge.',
         60
  UNION ALL SELECT 'institutional-memory',
         'Has the organisation forgotten previous warnings, harms or lessons?',
         'Consider whether earlier incidents, complaints, near misses or ethical concerns have been preserved and acted upon.',
         70
  UNION ALL SELECT 'risk',
         'What trust, legal, reputational or human risks are being underestimated?',
         'Assess both visible risks and slow-burn consequences such as cynicism, disengagement, silence and loss of legitimacy.',
         80
  UNION ALL SELECT 'learning-loop',
         'How does the organisation learn from ethical concerns without blame or concealment?',
         'Look at whether concerns lead to reflection, correction and redesign rather than defensiveness or symbolic responses.',
         90
  UNION ALL SELECT 'scenario',
         'What happens if this trust issue remains unresolved for another year?',
         'Imagine plausible consequences for relationships, reputation, performance, morale and institutional legitimacy.',
         100
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug;

DELETE FROM wp_sa_perspective_questions
WHERE class_id = 0;

COMMIT;