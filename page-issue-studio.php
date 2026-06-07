<?php
/**
 * Template Name: Issue Studio
 * Template Post Type: page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$glandore_chain_from_artefact_id = isset( $_GET['chain_from_artefact_id'] ) ? absint( $_GET['chain_from_artefact_id'] ) : 0;
$glandore_chain_from_snapshot_id = isset( $_GET['chain_from_snapshot_id'] ) ? absint( $_GET['chain_from_snapshot_id'] ) : 0;

$glandore_is_chain_mode = (
	$glandore_chain_from_artefact_id > 0
	|| $glandore_chain_from_snapshot_id > 0
	|| isset( $_GET['chain'] )
	|| isset( $_GET['chain_id'] )
	|| isset( $_GET['chain_artefact_id'] )
	|| isset( $_GET['source_artefact_id'] )
	|| isset( $_GET['previous_artefact_id'] )
	|| isset( $_GET['previous_snapshot_id'] )
	|| isset( $_GET['based_on_snapshot_id'] )
);

add_filter(
	'body_class',
	static function ( array $classes ) use ( $glandore_is_chain_mode ) : array {
		if ( $glandore_is_chain_mode ) {
			$classes[] = 'glandore-chain-mode';
		}

		return $classes;
	}
);

$classes_table   = $wpdb->prefix . 'sa_classes';
$issues_table    = $wpdb->prefix . 'sa_issues';
$lenses_table    = $wpdb->prefix . 'sa_lenses';
$questions_table = $wpdb->prefix . 'sa_perspective_questions';
$artefacts_table = $wpdb->prefix . 'sa_artefacts';
$snapshots_table = $wpdb->prefix . 'sa_artefact_snapshots';

$issue_studio_js = get_stylesheet_directory() . '/assets/js/issue-studio.js';

wp_enqueue_script(
	'glandore-issue-studio',
	get_stylesheet_directory_uri() . '/assets/js/issue-studio.js',
	array(),
	file_exists( $issue_studio_js ) ? filemtime( $issue_studio_js ) : null,
	true
);

$classes = $wpdb->get_results(
	"SELECT id, class_slug, class_title
	 FROM {$classes_table}
	 WHERE status = 'active'
	 ORDER BY sort_order ASC, class_title ASC"
) ?: array();

$issues = $wpdb->get_results(
	"SELECT id, class_id, issue_slug, issue_title
	 FROM {$issues_table}
	 WHERE status = 'active'
	 ORDER BY sort_order ASC, issue_title ASC"
) ?: array();

$questions = $wpdb->get_results(
	"SELECT
		q.id,
		q.class_id,
		q.question_text,
		q.help_text,
		q.sort_order,
		l.lens_slug,
		l.lens_title
	 FROM {$questions_table} q
	 INNER JOIN {$lenses_table} l ON q.lens_id = l.id
	 WHERE q.status = 'active'
	   AND l.status = 'active'
	 ORDER BY q.sort_order ASC, l.lens_title ASC"
) ?: array();

$existing_artefacts = array();

if ( ! $glandore_is_chain_mode ) {
	$existing_artefacts = $wpdb->get_results(
		"
		SELECT
			a.id,
			a.artefact_title,
			a.issue_description,
			a.created_at,
			c.class_title,
			i.issue_title,
			s.id AS latest_snapshot_id,
			s.created_at AS snapshot_created_at
		FROM {$artefacts_table} a
		INNER JOIN (
			SELECT artefact_id, MAX(id) AS latest_snapshot_id
			FROM {$snapshots_table}
			GROUP BY artefact_id
		) latest ON latest.artefact_id = a.id
		INNER JOIN {$snapshots_table} s ON s.id = latest.latest_snapshot_id
		LEFT JOIN {$classes_table} c ON c.id = a.class_id
		LEFT JOIN {$issues_table} i ON i.id = a.issue_id
		WHERE a.status IN ('active', 'complete')
		ORDER BY s.created_at DESC, s.id DESC
		LIMIT 50
		"
	) ?: array();
}

get_header();
?>

<style id="glandore-chain-ui-v4">
.glandore-chain-help {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 1.35rem;
	height: 1.35rem;
	margin-left: 0.45rem;
	border: 1px solid #9ca3af;
	border-radius: 999px;
	color: #374151;
	font-size: 0.85rem;
	font-weight: 700;
	cursor: help;
}

.glandore-working-note {
	max-width: 46rem;
	margin: 0.75rem 0 0;
	color: #374151;
	font-size: 0.95rem;
	line-height: 1.45;
}

.glandore-chain-perspective-note {
	margin: 0.5rem 0 0.85rem;
	color: #374151;
	font-size: 0.95rem;
	line-height: 1.45;
}

.glandore-chain-perspective-error {
	display: none;
	margin-top: 0.75rem;
	color: #b91c1c;
	font-size: 0.95rem;
}

.glandore-chain-perspective-error.is-active {
	display: block;
}
</style>

<main id="main" class="split-layout">
	<div class="page-split">

		<div class="page-split-left">
			<section class="page-content">
				<?php get_template_part( 'template_parts/content', 'issue-studio' ); ?>
			</section>
		</div>

		<div class="page-split-right">

			<?php if ( ! $glandore_is_chain_mode ) : ?>
				<h4>Existing issues</h4>

				<div class="cop-contribution-field">
					
					<select
						class="cop-contribution-select"
						id="sa-existing"
						name="existing_artefact"
						data-issue-studio-existing
					>
						<option value="">Select an existing issue</option>

						<?php foreach ( $existing_artefacts as $artefact ) : ?>
							<?php
							if ( ! empty( $artefact->artefact_title ) ) {
								$title = trim( wp_strip_all_tags( (string) $artefact->artefact_title ) );
							} elseif ( ! empty( $artefact->issue_description ) ) {
								$title = trim(
									wp_trim_words(
										wp_strip_all_tags( (string) $artefact->issue_description ),
										16,
										'…'
									)
								);
							} else {
								$title = 'Untitled issue';
							}

							$option_label = sprintf(
								'#%d — %s',
								absint( $artefact->id ),
								$title
							);

							$url = add_query_arg(
								'artefact_id',
								absint( $artefact->id ),
								home_url( '/issue-studio-result/' )
							);
							?>

							<option value="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( $option_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<h4><?php echo esc_html( $glandore_is_chain_mode ? 'Chaining' : 'Start a new Issue' ); ?></h4>

			<form
				id="sa-form"
				data-issue-studio-form
				data-chain-mode="<?php echo esc_attr( $glandore_is_chain_mode ? '1' : '0' ); ?>"
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			>
				<input type="hidden" name="action" value="glandore_sa_start">
				<?php wp_nonce_field( 'glandore_sa_start', 'glandore_sa_start_nonce' ); ?>

				<?php if ( $glandore_is_chain_mode ) : ?>
					<input type="hidden" name="chain_from_artefact_id" value="<?php echo esc_attr( (string) $glandore_chain_from_artefact_id ); ?>">
					<input type="hidden" name="chain_from_snapshot_id" value="<?php echo esc_attr( (string) $glandore_chain_from_snapshot_id ); ?>">
				<?php endif; ?>

				<div class="cop-contribution-field">
					<label for="sa-class" class="cop-contribution-label">Domain</label>

					<select class="cop-contribution-select" id="sa-class" name="class_id" required>
						<option value="">Select a domain</option>

						<?php foreach ( $classes as $class ) : ?>
							<option value="<?php echo esc_attr( (string) $class->id ); ?>">
								<?php echo esc_html( $class->class_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="cop-contribution-field">
					<label for="sa-issue" class="cop-contribution-label">Issue</label>

					<select class="cop-contribution-select" id="sa-issue" name="issue_id" required>
						<option value="">Select an issue</option>

						<?php foreach ( $issues as $issue ) : ?>
							<option
								value="<?php echo esc_attr( (string) $issue->id ); ?>"
								data-class-id="<?php echo esc_attr( (string) $issue->class_id ); ?>"
							>
								<?php echo esc_html( $issue->issue_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="cop-contribution-field" id="sa-lenses-field" hidden>
					<span class="cop-contribution-label">Choose how to explore this issue</span>

					<?php if ( $glandore_is_chain_mode ) : ?>
						<p class="glandore-chain-perspective-note">
							Select one or more perspectives for the chained assessment.
						</p>
					<?php endif; ?>

					<?php foreach ( $questions as $question ) : ?>
						<label
							data-issue-studio-perspective
							data-class-id="<?php echo esc_attr( (string) $question->class_id ); ?>"
							hidden
						>
							<input
								type="checkbox"
								name="perspectives[]"
								value="<?php echo esc_attr( (string) $question->id ); ?>"
							>
							<?php echo esc_html( $question->question_text ); ?>
						</label>
					<?php endforeach; ?>

					<?php if ( $glandore_is_chain_mode ) : ?>
						<p class="glandore-chain-perspective-error" id="sa-chain-perspective-error">
							Please choose at least one perspective before generating the chained assessment.
						</p>
					<?php endif; ?>
				</div>

				<div class="cop-induction-actions">
					<button
						type="submit"
						name="start_mode"
						value="normal"
						data-sa-next-step
						<?php if ( $glandore_is_chain_mode ) : ?>
							title="Creates a new chained assessment using the selected Domain, Issue and Perspectives, while preserving the previous assessment as historical context."
							aria-label="Next Step. Creates a new chained assessment using the selected Domain, Issue and Perspectives, while preserving the previous assessment as historical context."
						<?php endif; ?>
					>
						<?php echo esc_html( $glandore_is_chain_mode ? 'Generate chained assessment' : 'Next step' ); ?>
					</button>

					<?php if ( false && ! $glandore_is_chain_mode ) : ?>
						<button
							type="submit"
							name="start_mode"
							value="boilerplate"
							data-sa-boilerplate
						>
							Create Use Case
						</button>
					<?php endif; ?>
				</div>
			</form>

		</div>
	</div>
</main>

<?php if ( $glandore_is_chain_mode ) : ?>
<script id="glandore-chain-perspectives-v1">
document.addEventListener('DOMContentLoaded', function () {
	const form = document.getElementById('sa-form');
	const classSelect = document.getElementById('sa-class');
	const issueSelect = document.getElementById('sa-issue');
	const lensesField = document.getElementById('sa-lenses-field');
	const error = document.getElementById('sa-chain-perspective-error');

	if (!form || !classSelect || !issueSelect || !lensesField) {
		return;
	}

	function visiblePerspectiveLabels() {
		return Array.from(lensesField.querySelectorAll('[data-issue-studio-perspective]'))
			.filter(function (label) {
				return !label.hidden;
			});
	}

	function refreshPerspectives() {
		const classId = classSelect.value || (issueSelect.selectedOptions[0] ? issueSelect.selectedOptions[0].dataset.classId : '');
		const labels = Array.from(lensesField.querySelectorAll('[data-issue-studio-perspective]'));
		let hasVisible = false;

		labels.forEach(function (label) {
			const checkbox = label.querySelector('input[type="checkbox"]');
			const matches = classId && label.dataset.classId === classId;

			label.hidden = !matches;

			if (!matches && checkbox) {
				checkbox.checked = false;
			}

			if (matches) {
				hasVisible = true;
			}
		});

		lensesField.hidden = !hasVisible;

		if (error) {
			error.classList.remove('is-active');
		}
	}

	classSelect.addEventListener('change', refreshPerspectives);
	issueSelect.addEventListener('change', function () {
		const selected = issueSelect.selectedOptions[0];

		if (selected && selected.dataset.classId && classSelect.value !== selected.dataset.classId) {
			classSelect.value = selected.dataset.classId;
		}

		refreshPerspectives();
	});

	form.addEventListener('submit', function (event) {
		refreshPerspectives();

		const checked = visiblePerspectiveLabels().some(function (label) {
			const checkbox = label.querySelector('input[type="checkbox"]');
			return checkbox && checkbox.checked;
		});

		if (!checked) {
			event.preventDefault();
			event.stopPropagation();

			if (error) {
				error.classList.add('is-active');
			}

			lensesField.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}, true);

	refreshPerspectives();
});
</script>
<?php endif; ?>

<?php get_footer(); ?>
