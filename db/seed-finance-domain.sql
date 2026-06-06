START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('finance-economic-viability', 'Finance / Economic Viability', 'active', 240)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'finance-economic-viability'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'funding-gap', 'Funding Gap', 10, 'active'),
(@class_id, 'cost-escalation', 'Cost Escalation', 20, 'active'),
(@class_id, 'unsustainable-business-model', 'Unsustainable Business Model', 30, 'active'),
(@class_id, 'poor-value-for-money', 'Poor Value for Money', 40, 'active'),
(@class_id, 'budgetary-short-termism', 'Budgetary Short-Termism', 50, 'active'),
(@class_id, 'investment-underperformance', 'Investment Underperformance', 60, 'active'),
(@class_id, 'benefits-realisation-gap', 'Benefits Realisation Gap', 70, 'active'),
(@class_id, 'hidden-costs', 'Hidden Costs', 80, 'active'),
(@class_id, 'financial-risk-exposure', 'Financial Risk Exposure', 90, 'active'),
(@class_id, 'misaligned-financial-incentives', 'Misaligned Financial Incentives', 100, 'active'),
(@class_id, 'resource-allocation-failure', 'Resource Allocation Failure', 110, 'active'),
(@class_id, 'economic-dependency', 'Economic Dependency', 120, 'active')
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
  SELECT '5-whys' AS lens_slug,
         'Why does the financial pressure keep recurring even after budgets, forecasts, or savings plans are revised?' AS question_text,
         'Push beyond the visible deficit or overspend to the repeated causes: assumptions, behaviours, incentives, governance, or demand patterns.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'complexity',
         'How are costs, demand, income, capacity, and external conditions interacting in ways that make viability hard to control?',
         'Look for feedback loops, delayed consequences, unintended effects, and dependencies that make simple financial fixes unstable.',
         20
  UNION ALL SELECT 'governance',
         'Where are financial decisions being approved without enough challenge, ownership, or visibility of long-term consequences?',
         'Consider whether boards, committees, sponsors, or budget holders are seeing the real trade-offs clearly enough.',
         30
  UNION ALL SELECT 'incentives',
         'What financial incentives are encouraging short-term savings, cost-shifting, underinvestment, or behaviour that weakens overall viability?',
         'Identify whether local optimisation is damaging system-wide value or future resilience.',
         40
  UNION ALL SELECT 'institutional-memory',
         'What previous financial lessons, warnings, failed savings plans, or investment cases are being forgotten or repeated?',
         'Look for recurring patterns that have appeared before but are not being used to shape current decisions.',
         50
  UNION ALL SELECT 'risk',
         'Which financial risks are being underestimated, normalised, deferred, or hidden until they become unavoidable?',
         'Consider downside exposure, optimism bias, fragile assumptions, and risks transferred to later periods.',
         60
  UNION ALL SELECT 'delivery',
         'Where is weak execution turning financially viable plans into financially weak outcomes?',
         'Focus on whether benefits, savings, productivity gains, or revenue assumptions are actually being delivered.',
         70
  UNION ALL SELECT 'power',
         'Who has the power to protect budgets, shift costs, secure investment, or define what counts as affordable?',
         'Surface the political and organisational dynamics behind allocation, prioritisation, and financial accountability.',
         80
  UNION ALL SELECT 'learning-loop',
         'How does the organisation learn from financial variance, investment outcomes, and failed assumptions?',
         'Assess whether financial feedback changes future decisions or is merely reported and absorbed.',
         90
  UNION ALL SELECT 'scenario',
         'What future financial conditions would make the current model unviable, and how prepared is the organisation for them?',
         'Test the issue against inflation, demand growth, funding constraint, revenue loss, workforce pressure, or market change.',
         100
) q
  ON l.lens_slug = q.lens_slug;

COMMIT;