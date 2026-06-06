-- =========================================================
-- Glandore – Informal Organisation Problem Card (Seed)
-- Version: v1.0
-- Notes:
-- Idempotent seed for Informal Organisation card.
-- Safe to re-run.
-- =========================================================


-- ---------------------------------------------------------
-- 1. Problem Card
-- ---------------------------------------------------------
INSERT INTO wp_problem_cards
(slug, title, strapline, problem_statement, intended_user, status, version, display_order)
VALUES
(
  'informal-organisation',
  'Influencing the Informal Organisation',
  'When agreement exists, but nothing actually changes.',
  'Organisations often agree on what needs to happen, yet behaviour does not change. The formal structure suggests alignment, but outcomes indicate otherwise. This reflects the presence of an informal organisation operating alongside the formal one.',
  'Leaders, programme managers, transformation leads and anyone trying to move from agreement to action.',
  'published',
  '1.0',
  10
)
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  strapline = VALUES(strapline),
  problem_statement = VALUES(problem_statement),
  intended_user = VALUES(intended_user),
  status = VALUES(status),
  version = VALUES(version),
  display_order = VALUES(display_order);


-- ---------------------------------------------------------
-- 2. Steps
-- ---------------------------------------------------------

-- Step 1: Observe signals
INSERT INTO wp_problem_card_steps
(card_id, step_key, step_title, step_type, intro, display_order)
SELECT id, 'observe', 'Observe the Signals', 'observe',
'Identify where formal agreement is not translating into action.',
1
FROM wp_problem_cards WHERE slug = 'informal-organisation'
ON DUPLICATE KEY UPDATE
  step_title = VALUES(step_title),
  intro = VALUES(intro),
  display_order = VALUES(display_order);


-- Step 2: Map informal actors
INSERT INTO wp_problem_card_steps
(card_id, step_key, step_title, step_type, intro, display_order)
SELECT id, 'map', 'Map the Informal Network', 'diagnose',
'Surface the individuals and relationships that actually influence outcomes.',
2
FROM wp_problem_cards WHERE slug = 'informal-organisation'
ON DUPLICATE KEY UPDATE
  step_title = VALUES(step_title),
  intro = VALUES(intro),
  display_order = VALUES(display_order);


-- Step 3: Test alignment
INSERT INTO wp_problem_card_steps
(card_id, step_key, step_title, step_type, intro, display_order)
SELECT id, 'test', 'Test for Alignment', 'diagnose',
'Compare formal intent with informal incentives and behaviour.',
3
FROM wp_problem_cards WHERE slug = 'informal-organisation'
ON DUPLICATE KEY UPDATE
  step_title = VALUES(step_title),
  intro = VALUES(intro),
  display_order = VALUES(display_order);


-- Step 4: Intervene
INSERT INTO wp_problem_card_steps
(card_id, step_key, step_title, step_type, intro, display_order)
SELECT id, 'intervene', 'Intervene Through Influence', 'act',
'Take targeted action within the informal system to shift outcomes.',
4
FROM wp_problem_cards WHERE slug = 'informal-organisation'
ON DUPLICATE KEY UPDATE
  step_title = VALUES(step_title),
  intro = VALUES(intro),
  display_order = VALUES(display_order);


-- Step 5: Re-entry
INSERT INTO wp_problem_card_steps
(card_id, step_key, step_title, step_type, intro, display_order)
SELECT id, 'reentry', 'Re-entry into the System', 'reflect',
'Define what you will do next and what you will watch for.',
5
FROM wp_problem_cards WHERE slug = 'informal-organisation'
ON DUPLICATE KEY UPDATE
  step_title = VALUES(step_title),
  intro = VALUES(intro),
  display_order = VALUES(display_order);


-- ---------------------------------------------------------
-- 3. Step Items (inputs)
-- ---------------------------------------------------------

-- Observe
INSERT INTO wp_problem_card_step_items
(step_id, item_key, item_type, item_label, display_order)
SELECT s.id, 'signals', 'textarea',
'Where are decisions or actions not aligning with agreed intent?',
1
FROM wp_problem_card_steps s
JOIN wp_problem_cards c ON s.card_id = c.id
WHERE c.slug = 'informal-organisation' AND s.step_key = 'observe'
ON DUPLICATE KEY UPDATE
  item_label = VALUES(item_label),
  display_order = VALUES(display_order);


-- Map
INSERT INTO wp_problem_card_step_items
(step_id, item_key, item_type, item_label, display_order)
SELECT s.id, 'actors', 'textarea',
'Who actually influences what happens (regardless of formal role)?',
1
FROM wp_problem_card_steps s
JOIN wp_problem_cards c ON s.card_id = c.id
WHERE c.slug = 'informal-organisation' AND s.step_key = 'map'
ON DUPLICATE KEY UPDATE
  item_label = VALUES(item_label),
  display_order = VALUES(display_order);


-- Test
INSERT INTO wp_problem_card_step_items
(step_id, item_key, item_type, item_label, display_order)
SELECT s.id, 'misalignment', 'textarea',
'Where do informal incentives conflict with formal objectives?',
1
FROM wp_problem_card_steps s
JOIN wp_problem_cards c ON s.card_id = c.id
WHERE c.slug = 'informal-organisation' AND s.step_key = 'test'
ON DUPLICATE KEY UPDATE
  item_label = VALUES(item_label),
  display_order = VALUES(display_order);


-- Intervene
INSERT INTO wp_problem_card_step_items
(step_id, item_key, item_type, item_label, display_order)
SELECT s.id, 'actions', 'textarea',
'What specific actions can you take to influence the informal system?',
1
FROM wp_problem_card_steps s
JOIN wp_problem_cards c ON s.card_id = c.id
WHERE c.slug = 'informal-organisation' AND s.step_key = 'intervene'
ON DUPLICATE KEY UPDATE
  item_label = VALUES(item_label),
  display_order = VALUES(display_order);


-- Re-entry
INSERT INTO wp_problem_card_step_items
(step_id, item_key, item_type, item_label, display_order)
SELECT s.id, 'next_steps', 'textarea',
'What will you do next, and what signals will you monitor?',
1
FROM wp_problem_card_steps s
JOIN wp_problem_cards c ON s.card_id = c.id
WHERE c.slug = 'informal-organisation' AND s.step_key = 'reentry'
ON DUPLICATE KEY UPDATE
  item_label = VALUES(item_label),
  display_order = VALUES(display_order);