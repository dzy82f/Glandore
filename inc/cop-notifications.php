<?php
/**
 * File: /wp-content/themes/glandore/inc/cop-notifications.php
 *
 * CoP email notifications.
 *
 * Rules:
 * - registered  => notify the whole validated/approved community, including sender
 * - direct      => notify only the named individual
 * - moderators  => notify only moderators
 *
 * Email body:
 * - New discussion: initiator line + title + full body + link
 * - Contribution : post-by line + title (if any) + link
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a readable display name for a user ID.
 *
 * @param int $user_id WordPress user ID.
 * @return string
 */
function glandore_cop_notification_user_name( int $user_id ) : string {
	$first_name = trim( (string) get_user_meta( $user_id, 'first_name', true ) );
	$last_name  = trim( (string) get_user_meta( $user_id, 'last_name', true ) );
	$name       = trim( $first_name . ' ' . $last_name );

	if ( '' !== $name ) {
		return $name;
	}

	$user = get_userdata( $user_id );
	if ( $user && ! empty( $user->display_name ) ) {
		return (string) $user->display_name;
	}

	if ( $user && ! empty( $user->user_login ) ) {
		return (string) $user->user_login;
	}

	return __( 'Unknown contributor', 'glandore' );
}

/**
 * Return validated/approved community recipient user IDs.
 *
 * Current rule:
 * - ur_confirm_email = 1
 * - ur_user_status   = 1
 *
 * @return int[]
 */
function glandore_cop_get_validated_community_user_ids() : array {
	$user_ids = get_users(
		array(
			'fields'     => 'ids',
			'number'     => 5000,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'ur_confirm_email',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'ur_user_status',
					'value'   => '1',
					'compare' => '=',
				),
			),
		)
	);

	if ( empty( $user_ids ) ) {
		return array();
	}

	return array_values( array_unique( array_map( 'intval', $user_ids ) ) );
}

/**
 * Return moderator user IDs.
 *
 * Uses the same rule already implicit in the discussions template:
 * capability = moderate_comments.
 *
 * @return int[]
 */
function glandore_cop_get_moderator_user_ids() : array {
	$user_ids = get_users(
		array(
			'fields'     => 'ids',
			'number'     => 500,
			'capability' => 'moderate_comments',
		)
	);

	if ( empty( $user_ids ) ) {
		return array();
	}

	return array_values( array_unique( array_map( 'intval', $user_ids ) ) );
}

/**
 * Turn user IDs into unique, valid email addresses.
 *
 * @param int[] $user_ids WordPress user IDs.
 * @return string[]
 */
function glandore_cop_user_ids_to_emails( array $user_ids ) : array {
	$emails = array();

	foreach ( $user_ids as $user_id ) {
		$user = get_userdata( (int) $user_id );
		if ( ! $user || empty( $user->user_email ) ) {
			continue;
		}

		$email = sanitize_email( $user->user_email );
		if ( ! is_email( $email ) ) {
			continue;
		}

		$emails[] = strtolower( $email );
	}

	return array_values( array_unique( $emails ) );
}

/**
 * Build the discussion URL appropriate for the visibility mode.
 *
 * @param int    $thread_id          Thread ID.
 * @param string $visibility         registered|direct|moderators
 * @param int    $sender_user_id     Sender WP user ID.
 * @param int    $recipient_user_id  Recipient WP user ID for direct posts.
 * @return string
 */
function glandore_cop_build_notification_discussion_url(
	int $thread_id,
	string $visibility,
	int $sender_user_id,
	int $recipient_user_id = 0
) : string {
	$args = array(
		'topic_id' => $thread_id,
	);

	if ( 'direct' === $visibility && $recipient_user_id > 0 ) {
		$args['cop_view_as'] = $recipient_user_id;
	} elseif ( 'moderators' === $visibility && $sender_user_id > 0 ) {
		$args['cop_view_as'] = $sender_user_id;
	}

	return add_query_arg( $args, home_url( '/community-discussions/' ) );
}

/**
 * Build the subject line.
 *
 * @param bool $is_reply_mode True if contribution to existing discussion.
 * @return string
 */
function glandore_cop_build_notification_subject( bool $is_reply_mode ) : string {
	if ( $is_reply_mode ) {
		return 'New comment added to Glandore Community discussions';
	}

	return 'New discussion added to Glandore Community discussions';
}

/**
 * Build the HTML body for a new discussion email.
 *
 * @param string $sender_name    Sender display name.
 * @param string $date_string    Human-readable date.
 * @param string $thread_title   Discussion title.
 * @param string $content_html   Full discussion HTML content.
 * @param string $discussion_url URL to open the discussion.
 * @return string
 */
function glandore_cop_build_new_discussion_email_body(
	string $sender_name,
	string $date_string,
	string $thread_title,
	string $content_html,
	string $discussion_url
) : string {
	$body  = '<p><strong>Discussion initiated by ' . esc_html( $sender_name ) . ' on ' . esc_html( $date_string ) . '.</strong></p>';
	$body .= '<p style="margin:0 0 12px 0; font-size:16px; font-weight:700; line-height:1.35;">' . esc_html( $thread_title ) . '</p>';
	$body .= wp_kses_post( $content_html );
	$body .= '<p style="margin:16px 0 0 0;"><strong>View discussion:</strong><br>';
	$body .= '<a href="' . esc_url( $discussion_url ) . '">' . esc_html( $discussion_url ) . '</a></p>';

	return $body;
}

/**
 * Build the HTML body for a contribution/comment email.
 *
 * @param string $sender_name    Sender display name.
 * @param string $date_string    Human-readable date.
 * @param string $post_title     Contribution title.
 * @param string $discussion_url URL to open the discussion.
 * @return string
 */
function glandore_cop_build_contribution_email_body(
	string $sender_name,
	string $date_string,
	string $post_title,
	string $discussion_url
) : string {
	$post_title = trim( $post_title );
	if ( '' === $post_title ) {
		$post_title = __( 'Contribution', 'glandore' );
	}

	$body  = '<p style="margin:0 0 12px 0; font-size:16px; font-weight:700; line-height:1.35;">' . esc_html( $post_title ) . '</p>';
	$body .= '<p><strong>Comment added by ' . esc_html( $sender_name ) . ' on ' . esc_html( $date_string ) . '.</strong></p>';
	$body .= '<p style="margin:16px 0 0 0;"><strong>View comment:</strong><br>';
	$body .= '<a href="' . esc_url( $discussion_url ) . '">' . esc_html( $discussion_url ) . '</a></p>';

	return $body;
}

/**
 * Resolve recipient email addresses based on visibility.
 *
 * @param string $visibility         registered|direct|moderators
 * @param int    $sender_user_id     Sender WP user ID.
 * @param int    $recipient_user_id  Recipient WP user ID for direct posts.
 * @return string[]
 */
function glandore_cop_resolve_notification_emails(
	string $visibility,
	int $sender_user_id,
	int $recipient_user_id = 0
) : array {
	$user_ids = array();

	switch ( $visibility ) {
		case 'registered':
			$user_ids = glandore_cop_get_validated_community_user_ids();
			break;

		case 'direct':
			if ( $recipient_user_id > 0 ) {
				$user_ids = array( (int) $recipient_user_id );
			}
			break;

		case 'moderators':
			$user_ids = glandore_cop_get_moderator_user_ids();
			break;
	}

	if ( empty( $user_ids ) ) {
		return array();
	}

	return glandore_cop_user_ids_to_emails( $user_ids );
}

/**
 * Send CoP notifications for a newly saved contribution.
 *
 * @param array $args {
 *   @type int    $thread_id
 *   @type int    $contribution_id
 *   @type int    $sender_user_id
 *   @type string $thread_title
 *   @type string $post_title
 *   @type string $content_html
 *   @type string $visibility
 *   @type int    $recipient_user_id
 *   @type bool   $is_reply_mode
 * }
 * @return int Number of emails successfully sent.
 */
function glandore_cop_send_submission_notifications( array $args ) : int {
	global $wpdb;

	$defaults = array(
		'thread_id'         => 0,
		'contribution_id'   => 0,
		'sender_user_id'    => 0,
		'thread_title'      => '',
		'post_title'        => '',
		'content_html'      => '',
		'visibility'        => 'registered',
		'recipient_user_id' => 0,
		'is_reply_mode'     => false,
	);

	$args = wp_parse_args( $args, $defaults );

	$thread_id         = (int) $args['thread_id'];
	$contribution_id   = (int) $args['contribution_id'];
	$sender_user_id    = (int) $args['sender_user_id'];
	$thread_title      = (string) $args['thread_title'];
	$post_title        = (string) $args['post_title'];
	$content_html      = (string) $args['content_html'];
	$visibility        = (string) $args['visibility'];
	$recipient_user_id = (int) $args['recipient_user_id'];
	$is_reply_mode     = (bool) $args['is_reply_mode'];

	if ( $thread_id <= 0 || $contribution_id <= 0 || $sender_user_id <= 0 ) {
		return 0;
	}

	$emails = glandore_cop_resolve_notification_emails(
		$visibility,
		$sender_user_id,
		$recipient_user_id
	);

	if ( empty( $emails ) ) {
		return 0;
	}

	$sender_name    = glandore_cop_notification_user_name( $sender_user_id );
	$date_string    = wp_date( get_option( 'date_format' ) );
	$discussion_url = glandore_cop_build_notification_discussion_url(
		$thread_id,
		$visibility,
		$sender_user_id,
		$recipient_user_id
	);

	$subject = glandore_cop_build_notification_subject( $is_reply_mode );

	if ( $is_reply_mode ) {
		$body = glandore_cop_build_contribution_email_body(
			$sender_name,
			$date_string,
			$post_title,
			$discussion_url
		);
	} else {
		$body = glandore_cop_build_new_discussion_email_body(
			$sender_name,
			$date_string,
			$thread_title,
			$content_html,
			$discussion_url
		);
	}

	$body = '<html><body style="font-family: Arial, Helvetica, sans-serif; font-size:14px; line-height:1.5; color:#222; margin:0; padding:24px;">' . $body . '</body></html>';

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
	);

	$sent_count = 0;

	foreach ( $emails as $email ) {
		$sent = wp_mail( $email, $subject, $body, $headers );
		if ( $sent ) {
			$sent_count++;
		}
	}

	if ( $sent_count > 0 ) {
		$contrib_table = $wpdb->prefix . 'cop_contributions';

		$wpdb->update(
			$contrib_table,
			array( 'email_sent_at' => current_time( 'mysql' ) ),
			array( 'id' => $contribution_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	return $sent_count;
}