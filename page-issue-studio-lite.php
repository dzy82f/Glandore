<?php
/**
 * Template Name: Issue Studio Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$theme_dir = get_stylesheet_directory();
$theme_uri = get_stylesheet_directory_uri();

wp_enqueue_style(
	'glandore-cop',
	$theme_uri . '/assets/css/cop.css',
	array(),
	file_exists( $theme_dir . '/assets/css/cop.css' ) ? filemtime( $theme_dir . '/assets/css/cop.css' ) : null
);

wp_enqueue_script(
	'glandore-issue-studio-lite',
	$theme_uri . '/assets/js/issue-studio-lite.js',
	array(),
	file_exists( $theme_dir . '/assets/js/issue-studio-lite.js' ) ? filemtime( $theme_dir . '/assets/js/issue-studio-lite.js' ) : null,
	true
);

$classes = $wpdb->get_results(
	"SELECT id, class_title
	 FROM {$wpdb->prefix}sa_classes
	 WHERE status = 'active'
	 ORDER BY sort_order ASC, class_title ASC"
);

$issues = $wpdb->get_results(
	"SELECT id, class_id, issue_title
	 FROM {$wpdb->prefix}sa_issues
	 WHERE status = 'active'
	 ORDER BY class_id ASC, sort_order ASC, issue_title ASC"
);

$lenses = $wpdb->get_results(
	"SELECT id, lens_slug, lens_title
	 FROM {$wpdb->prefix}sa_lenses
	 WHERE status = 'active'
	 ORDER BY FIELD(lens_slug, '5-whys', 'complexity', 'stakeholder', 'learning-loop', 'power', 'culture') DESC, lens_title ASC
	 LIMIT 6"
);

get_header();
?>

<main class="issue-lite-wrap">
	<section class="issue-lite-card">
		<p class="issue-lite-kicker">Issue Studio Lite</p>

		<h1>Start a quick reflection</h1>

		<p class="issue-lite-intro">
			A lightweight mobile companion for rapid reflection and issue exploration.
			Designed as a simple entry point into the full Issue Studio experience.
		</p>

		<form id="issue-lite-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="glandore_sa_submit">
			<?php wp_nonce_field( 'glandore_sa_submit', 'glandore_sa_nonce' ); ?>

			<div class="issue-lite-progress" aria-hidden="true">
				<span class="is-active"></span>
				<span></span>
				<span></span>
				<span></span>
				<span></span>
			</div>

			<section class="issue-lite-step is-active" data-step="1">
				<label for="lite-class-id">Choose a domain</label>
				<select id="lite-class-id" name="class_id" required>
					<option value="">Select a domain</option>
					<?php foreach ( $classes as $class ) : ?>
						<option value="<?php echo esc_attr( $class->id ); ?>">
							<?php echo esc_html( $class->class_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label for="lite-issue-id">Choose an issue</label>
				<select id="lite-issue-id" name="issue_id" required>
					<option value="">Select an issue</option>
					<?php foreach ( $issues as $issue ) : ?>
						<option value="<?php echo esc_attr( $issue->id ); ?>" data-class-id="<?php echo esc_attr( $issue->class_id ); ?>">
							<?php echo esc_html( $issue->issue_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</section>

			<section class="issue-lite-step" data-step="2">
				<label for="lite-happening">What’s happening?</label>
				<textarea id="lite-happening" data-lite-field="happening" rows="8" required></textarea>
				<p class="issue-lite-help">Describe the situation in ordinary language.</p>
			</section>

			<section class="issue-lite-step" data-step="3">
				<label for="lite-matter">Why does it matter?</label>
				<textarea id="lite-matter" data-lite-field="matter" rows="8" required></textarea>
				<p class="issue-lite-help">What makes this worth thinking about?</p>
			</section>

			<section class="issue-lite-step" data-step="4">
				<label for="lite-tried">What has already been tried?</label>
				<textarea id="lite-tried" data-lite-field="tried" rows="8"></textarea>

				<label for="lite-difficult">What keeps getting in the way?</label>
				<textarea id="lite-difficult" data-lite-field="difficult" rows="8"></textarea>
			</section>

			<section class="issue-lite-step" data-step="5">
				<h2>Choose up to three light-touch perspectives</h2>
				<p class="issue-lite-help">Lite is deliberately lighter than the full desktop workspace.</p>

				<div class="issue-lite-lenses">
					<?php foreach ( $lenses as $lens ) : ?>
						<label>
							<input type="checkbox" name="perspectives[]" value="<?php echo esc_attr( $lens->id ); ?>">
							<span><?php echo esc_html( $lens->lens_title ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</section>

			<textarea name="issue_description" id="lite-issue-description" hidden></textarea>
			<textarea name="tried" id="lite-tried-hidden" hidden></textarea>
			<textarea name="success_failure" id="lite-success-failure" hidden></textarea>
			<textarea name="success_looks_like" id="lite-success-looks-like" hidden></textarea>
			<textarea name="other_relevant" id="lite-other-relevant" hidden></textarea>

			<div class="issue-lite-actions">
				<button type="button" class="issue-lite-back" hidden>Back</button>
				<button type="button" class="issue-lite-next">Continue</button>
				<button type="submit" class="issue-lite-submit" hidden>Generate Lite Reflection</button>
			</div>
		</form>
	</section>
</main>

<?php
get_footer();
