CREATE TABLE IF NOT EXISTS wp_sa_perspective_questions (
	id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	class_id bigint(20) unsigned NOT NULL,
	lens_id bigint(20) unsigned NOT NULL,
	question_text text NOT NULL,
	help_text text NULL,
	sort_order int(11) NOT NULL DEFAULT 0,
	status varchar(20) NOT NULL DEFAULT 'active',
	created_at datetime NOT NULL DEFAULT current_timestamp(),
	updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	PRIMARY KEY (id),
	UNIQUE KEY class_lens_unique (class_id, lens_id),
	KEY class_id (class_id),
	KEY lens_id (lens_id),
	KEY status (status)
);

INSERT INTO wp_sa_perspective_questions
	(class_id, lens_id, question_text, help_text, sort_order, status)
SELECT
	c.id,
	l.id,
	CASE l.lens_slug
		WHEN '5-whys' THEN 'What is really driving this outcome inside the organisation?'
		WHEN 'complexity' THEN 'Are we dealing with a system that can be controlled, or one that must be navigated?'
		WHEN 'stakeholder' THEN 'Whose interests must be aligned for this to work?'
		WHEN 'governance' THEN 'Who has the authority to decide, and who is accountable for the outcome?'
		WHEN 'institutional-memory' THEN 'What has this organisation already learned, and forgotten?'
		WHEN 'incentives' THEN 'What behaviours is the organisation actually rewarding?'
		WHEN 'power' THEN 'Who really has the ability to make or block this happening?'
		WHEN 'learning-loops' THEN 'How does the organisation learn from what it does?'
		WHEN 'causal-loops' THEN 'What feedback loops are reinforcing or stabilising this situation?'
		WHEN 'epistemic' THEN 'How does the organisation know what it believes to be true?'
		WHEN 'scenario' THEN 'What could happen next, and how prepared are we?'
		WHEN 'delivery' THEN 'Why is this not being delivered as intended?'
		WHEN 'risk' THEN 'What could go wrong, and what are we not seeing?'
		WHEN 'culture' THEN 'What behaviours does this organisation normalise?'
		WHEN 'informal-organisation' THEN 'How does work actually get done here?'
		WHEN 'abstraction-gap' THEN 'How far is strategy removed from operational reality?'
		WHEN 'non-customer-value' THEN 'Who else is affected by this, beyond the primary customer?'
		ELSE l.lens_title
	END,
	l.lens_title,
	l.sort_order,
	'active'
FROM wp_sa_classes c
CROSS JOIN wp_sa_lenses l
WHERE c.class_slug = 'organisational-dynamics'
ON DUPLICATE KEY UPDATE
	question_text = VALUES(question_text),
	help_text = VALUES(help_text),
	sort_order = VALUES(sort_order),
	status = VALUES(status);