START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('strategy', 'Strategy', 'active', 180)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'strategy'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'strategic-drift', 'Strategic Drift', 10, 'active'),
(@class_id, 'strategy-execution-gap', 'Strategy–Execution Gap', 20, 'active'),
(@class_id, 'reactive-strategy', 'Reactive Strategy', 30, 'active'),
(@class_id, 'strategic-ambiguity', 'Strategic Ambiguity', 40, 'active'),
(@class_id, 'short-termism', 'Short-Termism', 50, 'active'),
(@class_id, 'initiative-overload', 'Initiative Overload', 60, 'active'),
(@class_id, 'misaligned-incentives', 'Misaligned Incentives', 70, 'active'),
(@class_id, 'failure-to-adapt', 'Failure to Adapt', 80, 'active'),
(@class_id, 'scenario-blindness', 'Scenario Blindness', 90, 'active'),
(@class_id, 'leadership-narrative-collapse', 'Leadership Narrative Collapse', 100, 'active'),
(@class_id, 'resource-misallocation', 'Resource Misallocation', 110, 'active'),
(@class_id, 'innovation-theatre', 'Innovation Theatre', 120, 'active'),
(@class_id, 'strategic-dependency', 'Strategic Dependency', 130, 'active'),
(@class_id, 'stakeholder-misalignment', 'Stakeholder Misalignment', 140, 'active'),
(@class_id, 'institutional-forgetting', 'Institutional Forgetting', 150, 'active'),
(@class_id, 'false-consensus', 'False Consensus', 160, 'active'),
(@class_id, 'strategic-overreach', 'Strategic Overreach', 170, 'active'),
(@class_id, 'capability-erosion', 'Capability Erosion', 180, 'active'),
(@class_id, 'fragmented-decision-making', 'Fragmented Decision-Making', 190, 'active'),
(@class_id, 'metrics-myopia', 'Metrics Myopia', 200, 'active'),
(@class_id, 'political-strategy-capture', 'Political Strategy Capture', 210, 'active'),
(@class_id, 'delivery-optimism-bias', 'Delivery Optimism Bias', 220, 'active'),
(@class_id, 'strategic-paralysis', 'Strategic Paralysis', 230, 'active'),
(@class_id, 'competing-operating-models', 'Competing Operating Models', 240, 'active'),
(@class_id, 'vision-without-translation', 'Vision Without Translation', 250, 'active')
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
         'Why does this strategic problem continue despite repeated initiatives and restructures?' AS question_text,
         'Use this to move beneath visible strategy failures and expose recurring systemic causes.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'complexity',
         'Which parts of the strategic environment are genuinely unpredictable rather than merely uncertain?',
         'Distinguish between risks that can be planned for and complexity that requires adaptation.',
         20
  UNION ALL SELECT 'stakeholder',
         'Which stakeholders benefit from the current strategic ambiguity or failure?',
         'Look for groups whose influence, protection, funding, or freedom increases when strategy remains unclear.',
         30
  UNION ALL SELECT 'governance',
         'Where is strategic accountability formally held versus actually exercised?',
         'Compare official governance structures with the places where real strategic choices are made.',
         40
  UNION ALL SELECT 'institutional-memory',
         'What similar strategic ambitions have previously failed, and why were those lessons lost?',
         'Identify forgotten reforms, repeated promises, abandoned initiatives and missing learning loops.',
         50
  UNION ALL SELECT 'incentives',
         'Which incentives quietly reward behaviour that contradicts the stated strategy?',
         'Test whether targets, budgets, promotions, reputational pressures, or reporting cycles pull against strategic intent.',
         60
  UNION ALL SELECT 'power',
         'Who can delay, dilute, reinterpret, or quietly veto the strategy?',
         'Surface formal and informal actors with enough influence to reshape the strategy in practice.',
         70
  UNION ALL SELECT 'learning-loop',
         'How quickly does the organisation detect and adapt to strategic failure signals?',
         'Assess whether feedback changes strategy or is absorbed without consequence.',
         80
  UNION ALL SELECT 'causal-loop',
         'Which reinforcing loops are strengthening the strategic problem over time?',
         'Look for patterns where pressure, fear, delay, silence, or misallocation feed back into further failure.',
         90
  UNION ALL SELECT 'epistemic',
         'Which assumptions are treated as unquestionably true within the strategy?',
         'Examine the beliefs, forecasts, models, or narratives that are no longer being tested.',
         100
  UNION ALL SELECT 'scenario',
         'Which future conditions would cause the current strategy to fail rapidly?',
         'Use this to expose fragility under plausible futures, shocks, constraints, or competitor moves.',
         110
  UNION ALL SELECT 'delivery',
         'What capabilities, structures, or dependencies make execution fragile?',
         'Identify where strategic ambition relies on delivery capacity that may not exist.',
         120
  UNION ALL SELECT 'risk',
         'Which strategic risks are known but institutionally minimised or ignored?',
         'Look for risks that appear in conversations but disappear from formal decisions.',
         130
  UNION ALL SELECT 'culture',
         'Which cultural norms make strategic change difficult or unsafe?',
         'Consider habits around challenge, blame, optimism, escalation, hierarchy and truth-telling.',
         140
  UNION ALL SELECT 'informal-organisation',
         'How are informal relationships influencing strategic priorities or investment decisions?',
         'Look beyond formal plans to the networks, alliances and pre-meetings shaping strategy.',
         150
  UNION ALL SELECT 'abstraction-gap',
         'Where does strategic language become disconnected from operational reality?',
         'Test whether abstract ambition has been translated into practical choices, resources and behaviours.',
         160
  UNION ALL SELECT 'non-customer-value',
         'Which strategic activities consume effort while creating little meaningful value?',
         'Identify reporting, governance, branding, meetings, programmes or metrics that look strategic but add little value.',
         170
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug;

COMMIT;