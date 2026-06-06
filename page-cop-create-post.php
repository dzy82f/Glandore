<?php
/**
 * Template Name: CoP – Create a new post
 *
 * Community of Practice – start a NEW discussion or
 * add a contribution to an existing one:
 *   - NEW discussion: creates a new thread, opening contribution, v1 snapshot
 *   - EXISTING discussion (via ?cop_thread=ID): adds a contribution, snapshot
 *
 * Privacy:
 *   - registered (default): visible to all logged-in users; included in snapshots
 *   - direct: private to sender + recipient (+ moderators for viewing); NOT included in snapshots
 *   - moderators: visible only to moderators; NOT included in snapshots
 *
 * Notifications:
 *   - registered: email whole validated/approved community, including sender
 *   - direct: email only the named recipient
 *   - moderators: email only moderators
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require login.
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/account/' ) );
	exit;
}

$current_user_id = get_current_user_id();

// Ensure the CoP DB helpers are available.
if ( ! function_exists( 'glandore_cop_table' ) ) {
	require_once get_template_directory() . '/inc/cop-db.php';
}

// Ensure notifications helper is available.
if ( ! function_exists( 'glandore_cop_send_submission_notifications' ) ) {
	require_once get_template_directory() . '/inc/cop-notifications.php';
}

global $wpdb;

/**
 * Snapshot helpers (unchanged)
 */
if ( ! function_exists( 'glandore_cop_build_snapshot_block_for_contribution' ) ) {
	function glandore_cop_build_snapshot_block_for_contribution( int $contribution_id, int $order_number ) : string {
		global $wpdb;

		$contrib_table = $wpdb->prefix . 'cop_contributions';

		$contrib = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT user_id, title, content, created_at
				 FROM {$contrib_table}
				 WHERE id = %d AND status = 'published'",
				$contribution_id
			)
		);

		if ( ! $contrib ) {
			return '';
		}

		$author_id = (int) $contrib->user_id;

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

		$raw_title = (string) $contrib->title;
		$title     = '' !== trim( $raw_title ) ? $raw_title : __( 'Contribution', 'glandore' );

		$date_string = '';
		if ( ! empty( $contrib->created_at ) ) {
			$date_string = mysql2date( get_option( 'date_format' ), $contrib->created_at );
		}

		$meta_text = '';
		if ( $order_number > 1 ) {
			if ( $date_string ) {
				$meta_text = sprintf(
					__( 'Post by %1$s on %2$s.', 'glandore' ),
					$author_name,
					$date_string
				);
			} else {
				$meta_text = sprintf(
					__( 'Post by %s.', 'glandore' ),
					$author_name
				);
			}
		}

		$block  = '<article class="cop-snapshot-contribution">' . "\n";
		$block .= '<h3 class="cop-snapshot-contribution-title">' . esc_html( $title ) . '</h3>' . "\n";

		if ( '' !== $meta_text ) {
			$block .= '<p class="cop-snapshot-contribution-meta">' . esc_html( $meta_text ) . '</p>' . "\n";
		}

		$block .= wp_kses_post( $contrib->content ) . "\n";
		$block .= "</article>\n\n";

		return $block;
	}
}

if ( ! function_exists( 'glandore_cop_build_snapshot_body_append' ) ) {
	function glandore_cop_build_snapshot_body_append( int $thread_id, int $contribution_id ) : string {
		global $wpdb;

		$snapshots_table = $wpdb->prefix . 'cop_snapshots';
		$contrib_table   = $wpdb->prefix . 'cop_contributions';

		$prev_snapshot = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, content, version
				 FROM {$snapshots_table}
				 WHERE thread_id = %d
				 ORDER BY version DESC, id DESC
				 LIMIT 1",
				$thread_id
			)
		);

		$prev_content = $prev_snapshot ? (string) $prev_snapshot->content : '';

		$order_number = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$contrib_table}
				 WHERE thread_id = %d
				   AND status = 'published'
				   AND id <= %d",
				$thread_id,
				$contribution_id
			)
		);

		if ( $order_number < 1 ) {
			$order_number = 1;
		}

		$new_block = glandore_cop_build_snapshot_block_for_contribution( $contribution_id, $order_number );

		if ( '' === $new_block ) {
			return $prev_content;
		}

		if ( '' === trim( $prev_content ) ) {
			return $new_block;
		}

		return $prev_content . "\n\n" . $new_block;
	}
}

// Resolve CoP contributor row for this user (induction gate).
$cop_contributor = glandore_cop_get_or_create_contributor_for_user( (int) $current_user_id );

if ( ! $cop_contributor || empty( $cop_contributor->id ) ) {
	wp_die(
		esc_html__(
			'You must complete Community of Practice induction before creating discussions.',
			'glandore'
		)
	);
}

// -------------------------------------------------------------------------
// Reply mode vs new discussion mode.
// -------------------------------------------------------------------------

$threads_table = $wpdb->prefix . 'cop_threads';

$cop_thread_id = isset( $_GET['cop_thread'] ) ? (int) $_GET['cop_thread'] : 0;
$is_reply_mode = false;
$thread_title  = '';

if ( $cop_thread_id > 0 ) {
	$thread_row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id, title FROM {$threads_table} WHERE id = %d",
			$cop_thread_id
		)
	);

	if ( $thread_row && ! empty( $thread_row->id ) ) {
		$is_reply_mode = true;
		$cop_thread_id = (int) $thread_row->id;
		$thread_title  = (string) $thread_row->title;
	}
}

$initial_title_value = $is_reply_mode ? $thread_title : '';

// -------------------------------------------------------------------------
// Recipient options (WP user IDs; excludes self; only users with bios).
// -------------------------------------------------------------------------

$recipient_users = get_users(
	array(
		'meta_key'     => 'cop_profile_bio',
		'meta_compare' => 'EXISTS',
		'number'       => 200,
	)
);

$recipient_options = array();
if ( $recipient_users ) {
	foreach ( $recipient_users as $u ) {
		if ( (int) $u->ID === (int) $current_user_id ) {
			continue;
		}

		$name = trim(
			trim( (string) get_user_meta( $u->ID, 'first_name', true ) ) . ' ' .
			trim( (string) get_user_meta( $u->ID, 'last_name', true ) )
		);

		if ( '' === $name ) {
			$name = $u->display_name;
		}

		$recipient_options[ (int) $u->ID ] = $name;
	}
}

// -------------------------------------------------------------------------
// Handle form submission.
// -------------------------------------------------------------------------

$errors        = array();
$title_value   = $initial_title_value;
$content_value = '';

$visibility        = 'registered';
$recipient_user_id = 0;

$notice_type = '';
$notice_text = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

	if (
		! isset( $_POST['glandore_cop_create_post_nonce'] ) ||
		! wp_verify_nonce(
			wp_unslash( $_POST['glandore_cop_create_post_nonce'] ),
			'glandore_cop_create_post'
		)
	) {
		$errors[] = __( 'Security check failed. Please try again.', 'glandore' );
	} else {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$title_value   = isset( $_POST['cop_post_title'] )
			? sanitize_text_field( wp_unslash( $_POST['cop_post_title'] ) )
			: '';

		$content_value = isset( $_POST['cop_post_content'] )
			? wp_kses_post( wp_unslash( $_POST['cop_post_content'] ) )
			: '';

		$visibility = isset( $_POST['cop_post_visibility'] )
			? sanitize_text_field( wp_unslash( $_POST['cop_post_visibility'] ) )
			: 'registered';

		$recipient_user_id = isset( $_POST['cop_post_recipient_user_id'] )
			? (int) $_POST['cop_post_recipient_user_id']
			: 0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$allowed_visibility = array( 'registered', 'direct', 'moderators' );
		if ( ! in_array( $visibility, $allowed_visibility, true ) ) {
			$visibility = 'registered';
		}

		// New discussions are always community-visible.
		if ( ! $is_reply_mode && 'registered' !== $visibility ) {
			$visibility = 'registered';
			$recipient_user_id = 0;
		}

		if ( ! $is_reply_mode && '' === trim( $title_value ) ) {
			$errors[] = __( 'Please add a title for your new discussion.', 'glandore' );
		}

		if ( '' === trim( $content_value ) ) {
			$errors[] = __( 'Please add some content to your post.', 'glandore' );
		}

		if ( 'direct' === $visibility ) {
			if ( $recipient_user_id <= 0 || ! array_key_exists( $recipient_user_id, $recipient_options ) ) {
				$errors[] = __( 'A valid recipient is required for a private (direct) post.', 'glandore' );
			}
		} else {
			$recipient_user_id = 0;
		}

		if ( empty( $errors ) ) {

			if (
				! function_exists( 'glandore_cop_create_thread' ) ||
				! function_exists( 'glandore_cop_add_contribution' ) ||
				! function_exists( 'glandore_cop_create_snapshot' )
			) {
				$errors[] = __( 'The discussion engine is currently unavailable. Please contact the site administrator.', 'glandore' );
			} else {

				// 1) Determine which thread we are writing into.
				if ( $is_reply_mode && $cop_thread_id > 0 ) {
					$thread_id = $cop_thread_id;
				} else {
					$thread_id = glandore_cop_create_thread(
						array(
							'slug'       => sanitize_title( $title_value ),
							'title'      => $title_value,
							'topic'      => sanitize_title( $title_value ),
							'created_by' => (int) $current_user_id,
						)
					);
				}

				if ( ! $thread_id ) {
					$errors[] = __( 'Unable to create the discussion topic. Please try again.', 'glandore' );
				} else {

					// 2) Store the user's post as a contribution.
					$contribution_id = glandore_cop_add_contribution(
						(int) $thread_id,
						(int) $current_user_id,
						$content_value,
						$title_value
					);

					if ( ! $contribution_id ) {
						$errors[] = __( 'Something went wrong while saving your post. Please try again.', 'glandore' );
					} else {

						// 2b) Persist privacy fields.
						$contrib_table = $wpdb->prefix . 'cop_contributions';

						$wpdb->update(
							$contrib_table,
							array( 'visibility' => $visibility ),
							array( 'id' => (int) $contribution_id ),
							array( '%s' ),
							array( '%d' )
						);

						if ( 'direct' === $visibility ) {
							$wpdb->update(
								$contrib_table,
								array( 'recipient_user_id' => (int) $recipient_user_id ),
								array( 'id' => (int) $contribution_id ),
								array( '%d' ),
								array( '%d' )
							);
						} else {
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$contrib_table} SET recipient_user_id = NULL WHERE id = %d",
									(int) $contribution_id
								)
							);
						}

						// 3) Snapshots only for community-visible contributions.
						if ( 'registered' === $visibility ) {

							$snapshot_body = glandore_cop_build_snapshot_body_append(
								(int) $thread_id,
								(int) $contribution_id
							);

							glandore_cop_create_snapshot(
								(int) $thread_id,
								$snapshot_body,
								(int) $current_user_id,
								array( (int) $contribution_id )
							);
						}

						// 4) Notifications.
						glandore_cop_send_submission_notifications(
							array(
								'thread_id'         => (int) $thread_id,
								'contribution_id'   => (int) $contribution_id,
								'sender_user_id'    => (int) $current_user_id,
								'thread_title'      => (string) $thread_title ? (string) $thread_title : (string) $title_value,
								'post_title'        => (string) $title_value,
								'content_html'      => (string) $content_value,
								'visibility'        => (string) $visibility,
								'recipient_user_id' => (int) $recipient_user_id,
								'is_reply_mode'     => (bool) $is_reply_mode,
							)
						);

						// 5) Redirect to Community discussions, passing thread id as topic_id.
						$redirect_url = add_query_arg(
							array(
								'cop_posted' => 1,
								'topic_id'   => (int) $thread_id,
							),
							home_url( '/community-discussions/' )
						);

						wp_safe_redirect( $redirect_url );
						exit;
					}
				}
			}
		}
	}
}

if ( ! empty( $errors ) ) {
	$notice_type = 'error';
	$notice_text = __( 'There were problems with your submission. Please review the messages below.', 'glandore' );
}

get_header();
?>

<div class="cop-post-page">
	<div class="glandore-auth-card cop-post-card">

		<h1 class="account-panel-title">
			<?php esc_html_e( 'CoP — Create a post', 'glandore' ); ?>
		</h1>

		<p class="cop-post-mode">
			<strong><?php esc_html_e( 'Mode:', 'glandore' ); ?></strong>
			<?php esc_html_e( 'POST', 'glandore' ); ?>
		</p>

		<?php if ( $is_reply_mode && $thread_title ) : ?>
			<p class="cop-post-context">
				<?php
				printf(
					esc_html__( 'Discussion: %s', 'glandore' ),
					esc_html( $thread_title )
				);
				?>
			</p>
		<?php endif; ?>

		<div class="cop-post-notice-wrap">
			<?php if ( isset( $notice_type, $notice_text ) && $notice_type && $notice_text ) : ?>
				<div class="cop-post-notice cop-post-notice--<?php echo esc_attr( $notice_type ); ?>">
					<p><?php echo esc_html( $notice_text ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="cop-post-message cop-post-message--error">
				<ul>
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<section class="cop-post-form-section">
			<form method="post" class="cop-post-form">
				<?php wp_nonce_field( 'glandore_cop_create_post', 'glandore_cop_create_post_nonce' ); ?>

				<div class="cop-post-grid">
					<div class="cop-post-field cop-field-from">
						<label class="cop-post-label">
							<?php esc_html_e( 'From', 'glandore' ); ?>
						</label>
						<p class="cop-post-from-static">
							<?php echo esc_html( $cop_contributor->display_name ); ?>
						</p>
					</div>

					<div class="cop-post-field cop-field-visibility">
						<label for="cop_post_visibility" class="cop-post-label">
							<?php esc_html_e( 'Post to', 'glandore' ); ?>
						</label>

						<select id="cop_post_visibility" name="cop_post_visibility" class="cop-post-input">
							<option value="registered" <?php selected( $visibility, 'registered' ); ?>>
								<?php esc_html_e( 'All', 'glandore' ); ?>
							</option>

							<option value="direct" <?php selected( $visibility, 'direct' ); ?> <?php echo $is_reply_mode ? '' : 'disabled'; ?>>
								<?php esc_html_e( 'Individual', 'glandore' ); ?>
							</option>

							<option value="moderators" <?php selected( $visibility, 'moderators' ); ?> <?php echo $is_reply_mode ? '' : 'disabled'; ?>>
								<?php esc_html_e( 'Moderators', 'glandore' ); ?>
							</option>
						</select>

						<?php if ( ! $is_reply_mode ) : ?>
							<p class="cop-post-help">
								<?php esc_html_e( 'New discussions are posted to registered users.', 'glandore' ); ?>
							</p>
						<?php endif; ?>
					</div>

					<div class="cop-post-field cop-field-recipient" id="cop_recipient_wrap" style="<?php echo ( 'direct' === $visibility ) ? '' : 'display:none;'; ?>">
						<label for="cop_post_recipient_user_id" class="cop-post-label">
							<?php esc_html_e( 'Recipient', 'glandore' ); ?>
						</label>

						<select id="cop_post_recipient_user_id" name="cop_post_recipient_user_id" class="cop-post-input">
							<option value="0"><?php esc_html_e( 'Select…', 'glandore' ); ?></option>
							<?php foreach ( $recipient_options as $uid => $name ) : ?>
								<option value="<?php echo (int) $uid; ?>" <?php selected( $recipient_user_id, $uid ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
							<?php endforeach; ?>
						</select>

						<p class="cop-post-help cop-contribution-help">
							<?php esc_html_e( 'Direct posts are private and do not appear in snapshots.', 'glandore' ); ?>
						</p>
					</div>
				</div>

				<?php
				$title_label = $is_reply_mode
					? __( 'Title (optional – for your contribution)', 'glandore' )
					: __( 'Title (required – becomes the discussion topic)', 'glandore' );
				?>

				<div class="cop-post-field">
					<label for="cop_post_title" class="cop-post-label">
						<?php echo esc_html( $title_label ); ?>
					</label>
					<input
						type="text"
						id="cop_post_title"
						name="cop_post_title"
						class="cop-post-input"
						value="<?php echo esc_attr( $title_value ); ?>"
						<?php echo $is_reply_mode ? '' : 'required'; ?>
					/>
				</div>

				<div class="cop-post-field">
					<label for="cop_post_content" class="cop-post-label">
						<?php esc_html_e( 'Content', 'glandore' ); ?>
					</label>

					<?php
					wp_editor(
						$content_value,
						'cop_post_content',
						array(
							'textarea_name' => 'cop_post_content',
							'media_buttons' => false,
							'textarea_rows' => 12,
							'teeny'         => true,
							'quicktags'     => false,
							'tinymce'       => array(
								'toolbar1' => 'bold,italic,blockquote,bullist,numlist,link,undo,redo,removeformat',
								'toolbar2' => '',
							),
						)
					);
					?>
				</div>

				<div class="cop-post-actions">
					<button type="submit" class="cop-post-submit">
						<?php esc_html_e( 'Submit', 'glandore' ); ?>
					</button>

					<a href="<?php echo esc_url( home_url( '/community-discussions/' ) ); ?>" class="cop-post-cancel">
						<?php esc_html_e( 'Return', 'glandore' ); ?>
					</a>
				</div>

			</form>
		</section>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var vis  = document.getElementById('cop_post_visibility');
	var wrap = document.getElementById('cop_recipient_wrap');

	if (!vis || !wrap) {
		return;
	}

	function syncRecipient() {
		wrap.style.display = (vis.value === 'direct') ? '' : 'none';
	}

	vis.addEventListener('change', syncRecipient);
	syncRecipient();
});
</script>

<?php
get_footer( 'cop-minimal' );