<?php
/**
 * CoP – Print discussion
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enqueue print JS on CoP Discussions template only.
 */
function glandore_cop_enqueue_print_script() : void {
	if ( ! is_page_template( 'page-cop-discussions.php' ) ) {
		return;
	}

	wp_enqueue_script(
		'glandore-cop-print',
		get_template_directory_uri() . '/assets/js/cop-print.js',
		[],
		'1.0.1',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_cop_enqueue_print_script' );

/**
 * Serve a minimal print view when ?cop_print=1&cop_thread=ID is present.
 */
/**
 * Serve a minimal print view when ?cop_print=1&cop_thread=ID is present.
 * Note: cop_thread refers to wp_cop_threads.id (custom table), not a WP post.
 */
function glandore_cop_print_view() : void {
	if ( empty( $_GET['cop_print'] ) ) {
		return;
	}

	$thread_id = isset( $_GET['cop_thread'] ) ? absint( $_GET['cop_thread'] ) : 0;
	if ( ! $thread_id ) {
		wp_die( 'Missing cop_thread.' );
	}

	global $wpdb;

	$threads_table   = $wpdb->prefix . 'cop_threads';
	$snapshots_table = $wpdb->prefix . 'cop_snapshots';

	$thread = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id, title, created_by, created_at
			 FROM {$threads_table}
			 WHERE id = %d
			 LIMIT 1",
			$thread_id
		)
	);

	if ( ! $thread ) {
		wp_die( 'Invalid thread.' );
	}

	// Latest snapshot.
	$snapshot = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id, thread_id, version, content, created_by, created_at
			 FROM {$snapshots_table}
			 WHERE thread_id = %d
			 ORDER BY version DESC, id DESC
			 LIMIT 1",
			$thread_id
		)
	);

	$title   = (string) $thread->title;
	$content = $snapshot ? wp_kses_post( $snapshot->content )
		: '<p>' . esc_html__( 'There is not yet a synthesised view for this discussion.', 'glandore' ) . '</p>';

	?><!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo esc_html( $title ); ?></title>
		<?php wp_head(); ?>
	</head>
	<body class="cop-print-view">
		<main class="cop-print">
			<h1 class="cop-print-title"><?php echo esc_html( $title ); ?></h1>
			<div class="cop-print-body">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</main>

		<script>
		  window.addEventListener('load', function () {
			window.print();
		  });

		  // In most browsers this fires after the dialog closes (print OR cancel).
		  window.addEventListener('afterprint', function () {
			window.close();
		  });

		  // Fallback: if afterprint doesn't fire, allow manual close.
		</script>

		<?php wp_footer(); ?>
	</body>
	</html><?php
	exit;
}
add_action( 'template_redirect', 'glandore_cop_print_view' );