START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('capital-allocation', 'Capital Allocation', 'active', 250)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'capital-allocation'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'short-term-investment-bias', 'Short-Term Investment Bias', 10, 'active'),
(@class_id, 'strategic-underinvestment', 'Strategic Underinvestment', 20, 'active'),
(@class_id, 'capital-misalignment', 'Capital Misalignment', 30, 'active'),
(@class_id, 'innovation-starvation', 'Innovation Starvation', 40, 'active'),
(@class_id, 'politically-driven-spending', 'Politically Driven Spending', 50, 'active'),
(@class_id, 'portfolio-fragmentation', 'Portfolio Fragmentation', 60, 'active'),
(@class_id, 'duplicate-investment', 'Duplicate Investment', 70, 'active'),
(@class_id, 'deferred-maintenance', 'Deferred Maintenance', 80, 'active'),
(@class_id, 'pet-project-funding', 'Pet Project Funding', 90, 'active'),
(@class_id, 'capability-erosion', 'Capability Erosion', 100, 'active'),
(@class_id, 'cost-cutting-cycle', 'Cost-Cutting Cycle', 110, 'active'),
(@class_id, 'transformation-without-capacity', 'Transformation Without Capacity', 120, 'active'),
(@class_id, 'resource-hoarding', 'Resource Hoarding', 130, 'active'),
(@class_id, 'financial-opacity', 'Financial Opacity', 140, 'active'),
(@class_id, 'misleading-business-cases', 'Misleading Business Cases', 150, 'active'),
(@class_id, 'funding-without-accountability', 'Funding Without Accountability', 160, 'active'),
(@class_id, 'sunk-cost-escalation', 'Sunk Cost Escalation', 170, 'active'),
(@class_id, 'initiative-overload', 'Initiative Overload', 180, 'active'),
(@class_id, 'capital-allocation-gridlock', 'Capital Allocation Gridlock', 190, 'active'),
(@class_id, 'failure-to-stop-projects', 'Failure to Stop Projects', 200, 'active')
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
         'Why does capital repeatedly flow towards short-term, visible, or politically safer choices rather than long-term value?' AS question_text,
         'Use this to move beneath individual investment decisions and expose the recurring allocation logic.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'complexity',
         'Which dependencies, delays, or second-order effects make the real value of investment hard to judge?',
         'Look for hidden interactions between finance, capability, delivery, risk and future demand.',
         20
  UNION ALL SELECT 'stakeholder',
         'Which stakeholders gain or lose from the current pattern of capital allocation?',
         'Identify who benefits from spending, who carries the costs, and whose needs are underweighted.',
         30
  UNION ALL SELECT 'governance',
         'Where is investment accountability formally held versus actually exercised?',
         'Compare official approval routes with the informal places where funding priorities are shaped.',
         40
  UNION ALL SELECT 'institutional-memory',
         'What previous investment mistakes, deferred costs, or failed business cases are being repeated?',
         'Surface lost lessons, forgotten post-investment reviews and recurring allocation failures.',
         50
  UNION ALL SELECT 'incentives',
         'Which incentives reward leaders for securing funding rather than delivering enduring value?',
         'Test whether budgets, targets, status, politics, or reporting cycles distort allocation choices.',
         60
  UNION ALL SELECT 'power',
         'Who can protect, redirect, delay, or capture investment decisions?',
         'Identify the formal and informal actors with disproportionate influence over where money flows.',
         70
  UNION ALL SELECT 'learning-loop',
         'How does the organisation learn whether funded initiatives actually delivered value?',
         'Assess whether post-investment learning changes future allocation or simply disappears.',
         80
  UNION ALL SELECT 'causal-loop',
         'Which reinforcing loops cause misallocation, underinvestment, or cost-cutting cycles to repeat?',
         'Look for patterns where poor allocation creates future pressure that drives further poor allocation.',
         90
  UNION ALL SELECT 'epistemic',
         'Which financial assumptions, forecasts, or business-case narratives are treated as reliable without enough challenge?',
         'Examine optimism bias, false precision, selective evidence and untested value claims.',
         100
  UNION ALL SELECT 'scenario',
         'Which future conditions would reveal the current capital allocation model to be fragile?',
         'Use this to test resilience under plausible shocks, demand shifts, constraints, or strategic change.',
         110
  UNION ALL SELECT 'delivery',
         'Can the organisation convert approved funding into real operational capability and measurable value?',
         'Identify gaps between financial approval, delivery capacity, sequencing and benefits realisation.',
         120
  UNION ALL SELECT 'risk',
         'Which risks are being hidden, deferred, transferred, or accepted through current allocation choices?',
         'Look beyond financial risk to operational, reputational, systemic and opportunity risk.',
         130
  UNION ALL SELECT 'culture',
         'Which cultural norms make it difficult to challenge investment proposals or stop weak projects?',
         'Consider optimism, hierarchy, sunk-cost loyalty, empire-building and fear of cancellation.',
         140
  UNION ALL SELECT 'informal-organisation',
         'How do informal relationships, sponsorship, and pre-meeting agreements shape where capital actually goes?',
         'Look beyond formal investment committees to the networks that prepare or constrain decisions.',
         150
  UNION ALL SELECT 'abstraction-gap',
         'Where does the investment case differ from the operational reality it is meant to fund?',
         'Compare strategic language and financial models with lived constraints, capacity and delivery conditions.',
         160
  UNION ALL SELECT 'non-customer-value',
         'Which funded activities consume capital while creating little meaningful value for customers, users, or citizens?',
         'Identify spending that sustains internal machinery, prestige, reporting, or control without proportional value.',
         170
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug;

COMMIT;