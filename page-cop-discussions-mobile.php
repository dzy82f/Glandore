<?php
/**
 * File: /wp-content/themes/glandore/page-cop-discussions-mobile.php
 *
 * Template Name: CoP – Discussions (Mobile)
 * Description: Mobile-only discussion browser (Venetian), using latest snapshot per thread.
 *
 * Rule:
 * - Snapshot content: first <h3 ... </h3> is header; remainder is body.
 * - This template is NOT used on desktop and does not share rendering concerns with page-cop-discussions.php.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'cop-minimal' );

global $wpdb;

$threads_table   = $wpdb->prefix . 'cop_threads';
$snapshots_table = $wpdb->prefix . 'cop_snapshots';

/**
 * Latest snapshot per thread (max version, tie-break max id),
 * split into header/body via string ops.
 */
$sql = "
  SELECT
    s.thread_id,
    s.id AS snapshot_id,
    s.version,
    s.created_at AS snapshot_created_at,
    t.created_by AS thread_created_by,
    t.created_at AS thread_created_at,

    TRIM(
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(s.content, '</h3>', 1),
        '>',
        -1
      )
    ) AS header,

    TRIM(
      SUBSTRING(s.content, LOCATE('</h3>', s.content) + LENGTH('</h3>'))
    ) AS body

  FROM {$snapshots_table} s
  JOIN {$threads_table} t
    ON t.id = s.thread_id

  JOIN (
    SELECT thread_id, MAX(version) AS max_version
    FROM {$snapshots_table}
    GROUP BY thread_id
  ) mv
    ON mv.thread_id = s.thread_id
   AND mv.max_version = s.version

  JOIN (
    SELECT thread_id, version, MAX(id) AS max_id
    FROM {$snapshots_table}
    GROUP BY thread_id, version
  ) mi
    ON mi.thread_id = s.thread_id
   AND mi.version   = s.version
   AND mi.max_id    = s.id

  ORDER BY t.created_at ASC, s.thread_id ASC
";

$rows = $wpdb->get_results( $sql );
?>

<main id="primary" class="site-main cop-discussions-mobile">
	<div class="cop-dialogue-container">
		<div class="entry-content">

			<h3 class="cop-induction-heading">Community discussions</h3>

			<?php if ( ! empty( $rows ) ) : ?>
				<div class="cop-venetian">

					<?php foreach ( $rows as $r ) : ?>

					  <?php
					  $initiator_id = (int) $r->thread_created_by;

					  $first = trim( (string) get_user_meta( $initiator_id, 'first_name', true ) );
					  $last  = trim( (string) get_user_meta( $initiator_id, 'last_name', true ) );
					  $name  = trim( $first . ' ' . $last );

					  if ( '' === $name ) {
						$name = (string) get_the_author_meta( 'display_name', $initiator_id );
					  }
					  if ( '' === $name ) {
						$u = get_userdata( $initiator_id );
						$name = ( $u && ! empty( $u->user_login ) ) ? $u->user_login : __( 'Unknown contributor', 'glandore' );
					  }

					  $date = '';
					  if ( ! empty( $r->thread_created_at ) ) {
						$date = mysql2date( 'd/m/y', (string) $r->thread_created_at );
					  }
					  ?>

					  <details class="cop-venetian-item">
						<summary class="cop-venetian-header">
						  <?php echo esc_html( (string) $r->header ); ?>
						</summary>

						<div class="cop-venetian-body">
						  <p class="cop-venetian-meta">
							<?php
							echo esc_html(
							  sprintf(
								'Discussion initiated by %1$s on %2$s',
								$name,
								$date
							  )
							);
							?>
						  </p>

						  <div class="cop-venetian-spacer"></div>

						  <?php echo wp_kses_post( (string) $r->body ); ?>
						</div>
					  </details>

					<?php endforeach; ?>

				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No discussions available.', 'glandore' ); ?></p>
			<?php endif; ?>

			<div class="cop-discussions-actions">
				<a href="<?php echo esc_url( home_url( '/home-mobile/' ) ); ?>" class="button cop-discussions-button">
					<?php esc_html_e( 'Return', 'glandore' ); ?>
				</a>
			</div>

		</div>
	</div>
</main>

<?php
get_footer( 'cop-minimal' );