<?php
/**
 * File: /wp-content/themes/glandore/page-cop-discussions.php
 *
 * Template Name: CoP – Discussions
 * Description: Community of Practice discussion browser (Figure 1 wireframe).
 *
 * Private messages:
 * - Stored in wp_cop_contributions with visibility = 'direct' or 'moderators'
 * - Never included in snapshots
 * - Displayed ONLY when "View as" is a specific (numeric) contributor, and
 *   only to authorised viewers (sender/recipient, or moderators)
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wpdb;

/**
 * Current contributor filter (server-side, persisted in URL).
 */
$current_contributor = 'all';
if ( isset( $_GET['cop_view_as'] ) ) {
	$current_contributor = sanitize_text_field( wp_unslash( $_GET['cop_view_as'] ) );
	if ( '' === $current_contributor ) {
		$current_contributor = 'all';
	}
}

/**
 * Build list of contributors.
 */
$cop_contributors = array();

$users = get_users(
	array(
		'meta_key'     => 'cop_profile_bio',
		'meta_compare' => 'EXISTS',
		'number'       => 50,
	)
);

if ( ! empty( $users ) ) {
	foreach ( $users as $user ) {
		$bio = get_user_meta( $user->ID, 'cop_profile_bio', true );
		if ( '' === $bio ) {
			$bio = get_user_meta( $user->ID, 'cop_profile_bio_original', true );
		}
		if ( '' === $bio ) {
			continue;
		}

		$first_name = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
		$last_name  = trim( (string) get_user_meta( $user->ID, 'last_name', true ) );
		$name       = trim( $first_name . ' ' . $last_name );

		if ( '' === $name ) {
			$name = $user->display_name;
		}

		$cop_contributors[] = array(
			'id'   => (string) $user->ID,
			'name' => $name,
			'bio'  => wp_kses_post( $bio ),
		);
	}
}

$ariadne_bio = 'Ariadne is an AI Assistant who helps host and curate this Community of Practice. She supports induction, moderates contributions, and works with Julian to develop the methods and artefacts used here.';

$cop_contributors[] = array(
	'id'   => 'ariadne',
	'name' => 'Ariadne (digital Assistant)',
	'bio'  => wp_kses_post( $ariadne_bio ),
);

$cop_bio_map = array();
foreach ( $cop_contributors as $c ) {
	$cop_bio_map[ $c['id'] ] = $c['bio'];
}

/**
 * Threads/snapshots tables.
 */
$threads_table   = $wpdb->prefix . 'cop_threads';
$snapshots_table = $wpdb->prefix . 'cop_snapshots';

/**
 * SERVER-SIDE filtering of discussions list by initiator.
 * Only numeric IDs can filter threads. 'ariadne' and 'all' show everything for now.
 */
$where_sql  = "WHERE status = 'open'";
$where_args = array();

if ( 'all' !== $current_contributor && ctype_digit( (string) $current_contributor ) ) {
	$where_sql   .= " AND created_by = %d";
	$where_args[] = (int) $current_contributor;
}

$sql = "
	SELECT id, slug, title, created_by, created_at
	FROM {$threads_table}
	{$where_sql}
	ORDER BY created_at ASC
";

$cop_threads = $where_args
	? $wpdb->get_results( $wpdb->prepare( $sql, $where_args ) )
	: $wpdb->get_results( $sql );

$cop_thread_id          = 0;
$cop_discussion_title   = '';
$cop_discussion_meta    = '';
$cop_discussion_content = '';
$selected_thread        = null;

if ( $cop_threads ) {

	$requested_id = 0;

	if ( isset( $_GET['topic_id'] ) ) {
		$requested_id = (int) $_GET['topic_id'];
	} elseif ( isset( $_GET['cop_thread'] ) ) {
		$requested_id = (int) $_GET['cop_thread'];
	}

	$cop_thread_id = $requested_id ?: (int) $cop_threads[0]->id;

	foreach ( $cop_threads as $t ) {
		if ( (int) $t->id === (int) $cop_thread_id ) {
			$selected_thread = $t;
			break;
		}
	}

	if ( ! $selected_thread ) {
		$selected_thread = $cop_threads[0];
		$cop_thread_id   = (int) $selected_thread->id;
	}

	$cop_discussion_title = (string) $selected_thread->title;

	// Meta line.
	$initiator_id = (int) $selected_thread->created_by;

	$initiator_first = trim( get_the_author_meta( 'first_name', $initiator_id ) );
	$initiator_last  = trim( get_the_author_meta( 'last_name', $initiator_id ) );
	$initiator_name  = trim( $initiator_first . ' ' . $initiator_last );

	if ( '' === $initiator_name ) {
		$initiator_name = get_the_author_meta( 'display_name', $initiator_id );
	}

	if ( '' === $initiator_name ) {
		$initiator_obj = get_userdata( $initiator_id );
		if ( $initiator_obj && ! empty( $initiator_obj->user_login ) ) {
			$initiator_name = $initiator_obj->user_login;
		}
	}

	if ( '' === $initiator_name ) {
		$initiator_name = __( 'Unknown contributor', 'glandore' );
	}

	$initiated_date = '';
	if ( ! empty( $selected_thread->created_at ) ) {
		$initiated_date = mysql2date( get_option( 'date_format' ), $selected_thread->created_at );
	}

	if ( $initiated_date ) {
		$cop_discussion_meta = sprintf(
			esc_html__( 'Discussion initiated by %1$s on %2$s.', 'glandore' ),
			esc_html( $initiator_name ),
			esc_html( $initiated_date )
		);
	} else {
		$cop_discussion_meta = sprintf(
			esc_html__( 'Discussion initiated by %s.', 'glandore' ),
			esc_html( $initiator_name )
		);
	}

	// Latest snapshot for selected thread.
	$snapshot = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id, thread_id, version, content, created_by, created_at
			 FROM {$snapshots_table}
			 WHERE thread_id = %d
			 ORDER BY version DESC, id DESC
			 LIMIT 1",
			$cop_thread_id
		)
	);

	if ( $snapshot ) {
		$cop_discussion_content = wp_kses_post( $snapshot->content );
	} else {
		$cop_discussion_content = '<p>' . esc_html__( 'There is not yet a synthesised view for this discussion.', 'glandore' ) . '</p>';
	}

	// ---------------------------------------------------------------------
	// Private messages (direct / moderators) are NOT in snapshots.
	// Display rules (MVP):
	// - NEVER show in "All contributors"
	// - Only show when viewing as a specific (numeric) contributor
	// - Only show private rows involving that viewed contributor
	// - Only show to authorised viewer: sender/recipient, or moderators
	// ---------------------------------------------------------------------

	$show_private = ( 'all' !== $current_contributor && ctype_digit( (string) $current_contributor ) );
	
	$private_html = '';

	if ( $show_private ) {

		$current_user_id = get_current_user_id();
		$view_user_id    = (int) $current_contributor;

		$contrib_table = $wpdb->prefix . 'cop_contributions';

		// MVP moderator check: reuse a built-in WP capability.
		$is_moderator = current_user_can( 'moderate_comments' );

		$private_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, title, content, created_at, visibility, recipient_user_id
				 FROM {$contrib_table}
				 WHERE thread_id = %d
				   AND status = 'published'
				   AND (user_id = %d OR recipient_user_id = %d)
				   AND (
						(visibility = 'direct' AND (user_id = %d OR recipient_user_id = %d OR %d = 1))
					 OR (visibility = 'moderators' AND %d = 1)
				   )
				 ORDER BY created_at ASC, id ASC",
				(int) $cop_thread_id,
				(int) $view_user_id,
				(int) $view_user_id,
				(int) $current_user_id,
				(int) $current_user_id,
				$is_moderator ? 1 : 0,
				$is_moderator ? 1 : 0
			)
		);

		if ( $private_rows ) {
			$private_html .= '<section class="cop-private-messages">';
			$private_html .= '<h4 class="cop-private-messages-title">' . esc_html__( 'Private messages', 'glandore' ) . '</h4>';

			foreach ( $private_rows as $row ) {

				$author_id = (int) $row->user_id;

				$author_first = trim( get_the_author_meta( 'first_name', $author_id ) );
				$author_last  = trim( get_the_author_meta( 'last_name', $author_id ) );
				$author_name  = trim( $author_first . ' ' . $author_last );

				if ( '' === $author_name ) {
					$author_name = get_the_author_meta( 'display_name', $author_id );
				}
				if ( '' === $author_name ) {
					$author_obj = get_userdata( $author_id );
					if ( $author_obj && ! empty( $author_obj->user_login ) ) {
						$author_name = $author_obj->user_login;
					}
				}
				if ( '' === $author_name ) {
					$author_name = __( 'Unknown contributor', 'glandore' );
				}

				$date_string = '';
				if ( ! empty( $row->created_at ) ) {
					$date_string = mysql2date( get_option( 'date_format' ), $row->created_at );
				}

				$from_name = $author_name;

				// Resolve recipient name (only meaningful for direct).
				$to_name = __( '—', 'glandore' );

				if ( 'direct' === $row->visibility && ! empty( $row->recipient_user_id ) ) {
					$to_id = (int) $row->recipient_user_id;

					$to_first = trim( get_the_author_meta( 'first_name', $to_id ) );
					$to_last  = trim( get_the_author_meta( 'last_name', $to_id ) );
					$to_name  = trim( $to_first . ' ' . $to_last );

					if ( '' === $to_name ) {
						$to_name = get_the_author_meta( 'display_name', $to_id );
					}

					if ( '' === $to_name ) {
						$to_obj = get_userdata( $to_id );
						if ( $to_obj && ! empty( $to_obj->user_login ) ) {
							$to_name = $to_obj->user_login;
						}
					}

					if ( '' === $to_name ) {
						$to_name = __( 'Unknown contributor', 'glandore' );
					}
				}

				$private_html .= '<article class="cop-private-message">';
				$private_html .= '<p class="cop-private-message-meta">';

				if ( 'moderators' === $row->visibility ) {
					// Moderators-only note + From + date.
					$private_html .= esc_html__( 'Moderators only', 'glandore' ) . ' — ';
					$private_html .= esc_html__( 'From:', 'glandore' ) . ' ' . esc_html( $from_name );
				} else {
					// Direct: From + To + date.
					$private_html .= esc_html__( 'From:', 'glandore' ) . ' ' . esc_html( $from_name );
					$private_html .= ' — ' . esc_html__( 'To:', 'glandore' ) . ' ' . esc_html( $to_name );
				}

				if ( $date_string ) {
					$private_html .= ' — ' . esc_html( $date_string );
				}

				$private_html .= '</p>';

				$private_html .= '<h5 class="cop-private-message-title">' . esc_html( (string) $row->title ) . '</h5>';
				$private_html .= wp_kses_post( $row->content );
				$private_html .= '</article>';
			}

			$private_html .= '</section>';
		}

		// Append private messages below the snapshot content (chronological flow).
		if ( '' !== $private_html ) {
			$cop_discussion_content = $cop_discussion_content . $private_html;
		}
			}

} else {
	$cop_discussion_title   = esc_html__( 'Discussions', 'glandore' );
	$cop_discussion_meta    = esc_html__( 'No discussions match the current filter.', 'glandore' );
	$cop_discussion_content = '<p>' . esc_html__( 'Select a different contributor to view discussions.', 'glandore' ) . '</p>';
}

/**
 * Post-in-context URL, preserving filter.
 */
$cop_discussion_post_url = add_query_arg(
	array(
		'cop_thread'  => $cop_thread_id,
		'cop_view_as' => $current_contributor,
	),
	home_url( '/community-new-post/' )
);
?>

<main id="primary" class="site-main cop-dialogue-main cop-discussions-page">
	<div class="cop-dialogue-container">
		<div class="entry-content">

			<h3 class="cop-induction-heading">Community discussions</h3>
					
			<div class="cop-discussions-grid">

				<!-- Top-left: Contributor -->
				<div class="cop-discussions-box cop-discussions-box--contributor">
					<div class="cop-discussions-box-header">Contributor</div>
					<div class="cop-discussions-box-body">
						<div class="cop-contributor-select-block">
							<label for="cop_view_as" class="cop-contributor-label">View from</label>
							<select id="cop_view_as" name="cop_view_as" class="cop-contributor-select">
								<option value="all" <?php selected( $current_contributor, 'all' ); ?>>All contributors</option>
								<?php foreach ( $cop_contributors as $contrib ) : ?>
									<option value="<?php echo esc_attr( $contrib['id'] ); ?>" <?php selected( $current_contributor, (string) $contrib['id'] ); ?>>
										<?php echo esc_html( $contrib['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<!-- Top-right: Bio -->
				<div class="cop-discussions-box cop-discussions-box--bio">
					<div class="cop-discussions-box-header">Bio</div>
					<div class="cop-discussions-box-body">
						<div class="cop-bio-scroll">
							<p class="cop-bio-placeholder">
								Select a contributor on the left to see their short public bio here.
							</p>
						</div>
					</div>
				</div>

				<!-- Bottom-left: Discussions list -->
				<div class="cop-discussions-box cop-discussions-box--list">
					<div class="cop-discussions-box-header">Discussions</div>
					<div class="cop-discussions-box-body">
						<div class="cop-discussion-list-scroll">
							<ul class="cop-discussion-list-items">
								<?php if ( $cop_threads ) : ?>
									<?php foreach ( $cop_threads as $thread ) : ?>
										<?php
										$is_active = ( (int) $thread->id === (int) $cop_thread_id );

										$item_url = add_query_arg(
											array(
												'topic_id'    => (int) $thread->id,
												'cop_view_as' => $current_contributor,
											),
											home_url( '/community-discussions/' )
										);
										?>
										<li class="cop-discussion-list-item<?php echo $is_active ? ' is-active' : ''; ?>">
											<a href="<?php echo esc_url( $item_url ); ?>" class="cop-discussion-list-button">
												<?php echo esc_html( $thread->title ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								<?php else : ?>
									<li class="cop-discussion-list-item">
										<span class="cop-discussion-list-button" style="cursor:default;">
											<?php esc_html_e( 'No discussions match this contributor.', 'glandore' ); ?>
										</span>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>

				<!-- Bottom-right: Discussion content -->
				<div class="cop-discussions-box cop-discussions-box--content">
					<div class="cop-discussions-box-body">
						<div class="cop-discussion-content-scroll">
							<header class="cop-discussion-header">
								<h4 class="cop-discussion-title"><?php echo esc_html( $cop_discussion_title ); ?></h4>
								<p class="cop-discussion-meta"><?php echo esc_html( $cop_discussion_meta ); ?></p>
							</header>

							<section class="cop-discussion-body">
								<?php
								echo $cop_discussion_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</section>
						</div>
					</div>
				</div>

			</div><!-- grid -->

			<div class="cop-discussions-actions">
				<a href="<?php echo esc_url( home_url( '/community-of-practice/' ) ); ?>" class="button cop-discussions-button">
					<?php esc_html_e( 'Return', 'glandore' ); ?>
				</a>
				
				<a href="<?php echo esc_url( $cop_discussion_post_url ); ?>" class="button cop-discussions-button">
					<?php esc_html_e( 'Post in this discussion', 'glandore' ); ?>
				</a>
				
				<?php
				$print_url = add_query_arg(
					[
						'cop_print'  => 1,
						'cop_thread' => (int) $cop_thread_id,
						'cop_view_as' => $current_contributor, // optional, but nice to preserve context
					],
					get_permalink()
				);
				?>

				<a class="cop-discussions-button"
				   id="cop-print-discussion"
				   href="<?php echo esc_url( $print_url ); ?>"
				   target="_blank"
				   rel="noopener">
				  Print discussion
				</a>
				
				<a href="<?php echo esc_url( home_url( '/community-new-post/' ) ); ?>" class="button button-primary cop-discussions-button">
					<?php esc_html_e( 'New post', 'glandore' ); ?>
				</a>
			</div>

		</div>
	</div>
</main>

<?php if ( ! empty( $cop_bio_map ) ) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var select = document.getElementById('cop_view_as');
	var bioBox = document.querySelector('.cop-bio-scroll');
	if (!select || !bioBox) return;

	var bios = <?php echo wp_json_encode( $cop_bio_map ); ?>;

	function updateBio(val) {
		if (val === 'all' || !bios[val]) {
			bioBox.innerHTML = '<p class="cop-bio-placeholder">Select a contributor on the left to see their short public bio here.</p>';
		} else {
			bioBox.innerHTML = bios[val];
		}
	}

	updateBio(select.value || 'all');

	select.addEventListener('change', function () {
		// Reload page with persisted filter param (robust).
		var url = new URL(window.location.href);
		url.searchParams.set('cop_view_as', this.value);

		// Keep other params (e.g. topic_id) if present.
		window.location.href = url.toString();
	});
});
</script>
<?php endif; ?>

<?php
get_footer( 'cop-minimal' );