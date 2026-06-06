START TRANSACTION;

INSERT INTO wp_sa_classes
(class_slug, class_title, status, sort_order)
VALUES
('product-management', 'Product Management', 'active', 260)
ON DUPLICATE KEY UPDATE
  class_title = VALUES(class_title),
  status = VALUES(status),
  sort_order = VALUES(sort_order);

SET @class_id := (
  SELECT id
  FROM wp_sa_classes
  WHERE class_slug = 'product-management'
  LIMIT 1
);

INSERT INTO wp_sa_issues
(class_id, issue_slug, issue_title, sort_order, status)
VALUES
(@class_id, 'product-market-misalignment', 'Product–Market Misalignment', 10, 'active'),
(@class_id, 'feature-factory-behaviour', 'Feature Factory Behaviour', 20, 'active'),
(@class_id, 'roadmap-chaos', 'Roadmap Chaos', 30, 'active'),
(@class_id, 'customer-insight-failure', 'Customer Insight Failure', 40, 'active'),
(@class_id, 'strategy-execution-disconnect', 'Strategy–Execution Disconnect', 50, 'active'),
(@class_id, 'stakeholder-capture', 'Stakeholder Capture', 60, 'active'),
(@class_id, 'vision-drift', 'Vision Drift', 70, 'active'),
(@class_id, 'prioritisation-paralysis', 'Prioritisation Paralysis', 80, 'active'),
(@class_id, 'innovation-theatre', 'Innovation Theatre', 90, 'active'),
(@class_id, 'technical-debt-accumulation', 'Technical Debt Accumulation', 100, 'active'),
(@class_id, 'metrics-myopia', 'Metrics Myopia', 110, 'active'),
(@class_id, 'delivery-over-learning', 'Delivery Over Learning', 120, 'active'),
(@class_id, 'fragmented-ownership', 'Fragmented Ownership', 130, 'active'),
(@class_id, 'user-experience-erosion', 'User Experience Erosion', 140, 'active'),
(@class_id, 'platform-complexity', 'Platform Complexity', 150, 'active'),
(@class_id, 'product-bloat', 'Product Bloat', 160, 'active'),
(@class_id, 'organisational-misalignment', 'Organisational Misalignment', 170, 'active'),
(@class_id, 'scaling-failure', 'Scaling Failure', 180, 'active'),
(@class_id, 'weak-product-discovery', 'Weak Product Discovery', 190, 'active'),
(@class_id, 'ai-strategy-confusion', 'AI Strategy Confusion', 200, 'active'),
(@class_id, 'competitive-drift', 'Competitive Drift', 210, 'active'),
(@class_id, 'internal-product-syndrome', 'Internal Product Syndrome', 220, 'active'),
(@class_id, 'governance-friction', 'Governance Friction', 230, 'active'),
(@class_id, 'adoption-resistance', 'Adoption Resistance', 240, 'active'),
(@class_id, 'value-realisation-failure', 'Value Realisation Failure', 250, 'active')
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
  SELECT 'customer' AS lens_slug,
         'What evidence exists that users genuinely value this product capability?' AS question_text,
         'Look for observed behaviour, adoption, retention, willingness to pay, user workarounds and unmet needs rather than internal opinion.' AS help_text,
         10 AS sort_order
  UNION ALL SELECT 'product-discovery',
         'What assumptions were treated as facts before development began?',
         'Identify the untested beliefs about users, needs, demand, feasibility, desirability and viability.',
         20
  UNION ALL SELECT 'prioritisation',
         'What is really driving prioritisation: customer value, strategy, politics, urgency or noise?',
         'Explore whether roadmap choices reflect disciplined trade-offs or reactive pressure.',
         30
  UNION ALL SELECT 'stakeholder',
         'Which stakeholders most strongly shape the roadmap, formally or informally?',
         'Consider whose voice dominates, whose needs are underrepresented and where influence bypasses formal product governance.',
         40
  UNION ALL SELECT 'technical-debt',
         'What past product or engineering compromises are now constraining future evolution?',
         'Look for shortcuts, brittle architecture, dependency risks, legacy decisions and hidden maintenance burdens.',
         50
  UNION ALL SELECT 'strategy',
         'Is there a coherent product vision connecting current roadmap decisions?',
         'Assess whether teams can explain why this product exists, who it serves and how current work advances that purpose.',
         60
  UNION ALL SELECT 'learning-loop',
         'How effectively does the product team learn from outcomes rather than delivery activity?',
         'Check whether releases generate usable learning, whether feedback changes decisions and whether mistakes are retained as knowledge.',
         70
  UNION ALL SELECT 'metrics',
         'Which metrics may be distorting decision-making or masking reality?',
         'Look for vanity metrics, proxy measures, target gaming and indicators that hide weak value creation.',
         80
  UNION ALL SELECT 'adoption',
         'What barriers prevent meaningful user adoption or sustained engagement?',
         'Consider onboarding, workflow fit, trust, usability, incentives, training, timing and competing alternatives.',
         90
  UNION ALL SELECT 'incentives',
         'Are teams rewarded for shipping, learning, revenue, stability, customer value or appearances?',
         'Examine whether incentives encourage product success or merely visible activity.',
         100
  UNION ALL SELECT 'complexity',
         'Has the product become harder to understand, maintain or use over time?',
         'Look for feature sprawl, confused journeys, duplicated capabilities, unclear ownership and rising coordination cost.',
         110
  UNION ALL SELECT 'governance',
         'Where does governance improve product quality, and where does it suppress adaptation?',
         'Distinguish necessary guardrails from approval friction, risk avoidance and decision bottlenecks.',
         120
  UNION ALL SELECT 'innovation',
         'Is experimentation genuinely encouraged, or merely performed symbolically?',
         'Assess whether experiments can challenge strategy, stop work or redirect investment.',
         130
  UNION ALL SELECT 'delivery',
         'Does delivery activity create customer value or simply visible progress?',
         'Compare shipped output with actual user benefit, adoption and organisational impact.',
         140
  UNION ALL SELECT 'institutional-memory',
         'What product lessons have been forgotten or repeatedly relearned?',
         'Identify recurring mistakes, lost context, ignored evidence and knowledge that disappeared with people or reorganisations.',
         150
  UNION ALL SELECT 'power',
         'Who can quietly veto roadmap decisions or redirect investment?',
         'Look for informal power, executive preference, customer concentration, political sensitivity and hidden constraints.',
         160
  UNION ALL SELECT 'scenario',
         'What future market, user or technology shifts could destabilise this product?',
         'Consider plausible shifts in competition, regulation, AI capability, platform dependency, user behaviour and cost structure.',
         170
  UNION ALL SELECT 'ai',
         'Is AI being introduced to solve a real user problem or to follow market pressure?',
         'Test whether AI use is grounded in value, trust, workflow fit and operational feasibility.',
         180
  UNION ALL SELECT 'ecosystem',
         'How dependent is product success on external systems, platforms, partners or organisational processes?',
         'Map dependencies beyond the product team that may determine whether the product succeeds.',
         190
  UNION ALL SELECT 'meaning-purpose',
         'Does the product still solve a meaningful problem for real people?',
         'Reconnect the product to the human, operational or commercial problem it was meant to address.',
         200
) q
JOIN wp_sa_lenses l
  ON l.lens_slug = q.lens_slug;

COMMIT;