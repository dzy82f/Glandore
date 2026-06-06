START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('risk', 'Risk', 'active', 190)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'risk'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'risk-blindness', 'Risk Blindness', 10, 'active'),
(@class_id, 'normalisation-of-deviance', 'Normalisation of Deviance', 20, 'active'),
(@class_id, 'false-assurance', 'False Assurance', 30, 'active'),
(@class_id, 'compliance-theatre', 'Compliance Theatre', 40, 'active'),
(@class_id, 'single-point-of-failure', 'Single Point of Failure', 50, 'active'),
(@class_id, 'fragile-dependencies', 'Fragile Dependencies', 60, 'active'),
(@class_id, 'escalation-suppression', 'Escalation Suppression', 70, 'active'),
(@class_id, 'silent-operational-drift', 'Silent Operational Drift', 80, 'active'),
(@class_id, 'risk-ownership-ambiguity', 'Risk Ownership Ambiguity', 90, 'active'),
(@class_id, 'optimism-bias', 'Optimism Bias', 100, 'active'),
(@class_id, 'institutional-overconfidence', 'Institutional Overconfidence', 110, 'active'),
(@class_id, 'failure-to-imagine', 'Failure to Imagine', 120, 'active'),
(@class_id, 'known-but-ignored-risks', 'Known but Ignored Risks', 130, 'active'),
(@class_id, 'warning-fatigue', 'Warning Fatigue', 140, 'active'),
(@class_id, 'risk-displacement', 'Risk Displacement', 150, 'active'),
(@class_id, 'crisis-dependency', 'Crisis Dependency', 160, 'active'),
(@class_id, 'shadow-risk-transfer', 'Shadow Risk Transfer', 170, 'active'),
(@class_id, 'reputational-fragility', 'Reputational Fragility', 180, 'active'),
(@class_id, 'institutional-fragility', 'Institutional Fragility', 190, 'active'),
(@class_id, 'scenario-denial', 'Scenario Denial', 200, 'active'),
(@class_id, 'delivery-fragility', 'Delivery Fragility', 210, 'active'),
(@class_id, 'hidden-operational-risk', 'Hidden Operational Risk', 220, 'active'),
(@class_id, 'unacknowledged-tradeoffs', 'Unacknowledged Trade-offs', 230, 'active'),
(@class_id, 'fear-based-reporting', 'Fear-Based Reporting', 240, 'active'),
(@class_id, 'risk-culture-failure', 'Risk Culture Failure', 250, 'active')
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
  SELECT '5-whys' AS lens_slug,
         'Why does this risk continue to exist despite awareness, controls, or repeated incidents?' AS question_text,
         'Move beneath symptoms and controls to expose the deeper structural causes sustaining the risk.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'complexity',
         'Which parts of this risk environment are inherently unpredictable or tightly interconnected?',
         'Distinguish manageable uncertainty from true systemic complexity and emergence.',
         20
  UNION ALL SELECT 'stakeholder',
         'Which stakeholders benefit from minimising, delaying, or obscuring this risk?',
         'Identify groups whose incentives or reputation improve when the risk remains unaddressed.',
         30
  UNION ALL SELECT 'governance',
         'Where is accountability for this risk formally assigned versus actually exercised?',
         'Compare official oversight structures with where decisions and trade-offs really occur.',
         40
  UNION ALL SELECT 'institutional-memory',
         'What previous warnings, failures, or near misses resemble this current risk?',
         'Look for forgotten lessons, repeated incidents, or institutional amnesia.',
         50
  UNION ALL SELECT 'incentives',
         'Which incentives encourage short-term reassurance over honest risk visibility?',
         'Assess how targets, budgets, promotions, or political pressures distort risk behaviour.',
         60
  UNION ALL SELECT 'power',
         'Who has the authority to suppress, reinterpret, or quietly absorb this risk?',
         'Surface formal and informal actors capable of reshaping how the risk is perceived.',
         70
  UNION ALL SELECT 'learning-loop',
         'How effectively does the organisation learn from incidents, weak signals, and near misses?',
         'Assess whether feedback genuinely changes behaviour or is absorbed without adaptation.',
         80
  UNION ALL SELECT 'causal-loop',
         'Which reinforcing loops are increasing vulnerability or fragility over time?',
         'Identify cycles where delay, silence, fear, or pressure amplify future risk exposure.',
         90
  UNION ALL SELECT 'epistemic',
         'Which assumptions about safety, stability, or control are no longer being questioned?',
         'Examine beliefs and narratives that may have become institutionally protected.',
         100
  UNION ALL SELECT 'scenario',
         'Which plausible future conditions would cause this risk to escalate rapidly?',
         'Use scenario thinking to expose fragility under stress, disruption, or cascading failure.',
         110
  UNION ALL SELECT 'delivery',
         'Which operational dependencies or capability gaps make this risk difficult to manage?',
         'Identify where resilience relies on fragile processes, individuals, or assumptions.',
         120
  UNION ALL SELECT 'risk',
         'Which risks are visible informally but disappear from formal reporting or decision-making?',
         'Look for known concerns that are minimised, diluted, or institutionally ignored.',
         130
  UNION ALL SELECT 'culture',
         'Which cultural norms discourage honest escalation, challenge, or risk visibility?',
         'Consider habits around blame, optimism, silence, hierarchy, and psychological safety.',
         140
  UNION ALL SELECT 'informal-organisation',
         'How are informal networks shaping risk visibility, escalation, or mitigation?',
         'Look beyond formal processes to hidden influence, trust networks, and back-channel decisions.',
         150
  UNION ALL SELECT 'abstraction-gap',
         'Where does formal risk language become disconnected from operational reality?',
         'Test whether policies, dashboards, and assurances reflect lived conditions.',
         160
  UNION ALL SELECT 'non-customer-value',
         'Which risk activities consume effort while adding little meaningful resilience or protection?',
         'Identify reporting, governance, compliance, or assurance processes that create activity without reducing vulnerability.',
         170
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug;

COMMIT;