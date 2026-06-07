<?php
/**
 * Community of Practice – profile and gating helpers.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if current (or given) user has completed the CoP induction.
 *
 * A profile is only complete when the user has been marked complete.
 *
 * @param int $user_id Optional user ID.
 * @return bool
 */
function glandore_cop_is_profile_complete( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	return (bool) get_user_meta( $user_id, 'glandore_cop_about_complete', true );
}

/**
 * Convenience helper: is the current user allowed to contribute?
 *
 * @param int $user_id Optional user ID.
 * @return bool
 */
function glandore_cop_can_contribute( $user_id = 0 ) {
	return glandore_cop_is_profile_complete( $user_id );
}

/**
 * Calculate simple word-set similarity between two strings.
 *
 * @param string $a First string.
 * @param string $b Second string.
 * @return float
 */
function glandore_cop_wordset_similarity( $a, $b ) {
	$a = strtolower( wp_strip_all_tags( (string) $a ) );
	$b = strtolower( wp_strip_all_tags( (string) $b ) );

	$a_words = preg_split( '/\s+/', $a );
	$b_words = preg_split( '/\s+/', $b );

	$a_words = array_filter( $a_words );
	$b_words = array_filter( $b_words );

	if ( empty( $a_words ) && empty( $b_words ) ) {
		return 1.0;
	}

	$a_set = array_unique( $a_words );
	$b_set = array_unique( $b_words );

	$intersection = array_intersect( $a_set, $b_set );
	$union        = array_unique( array_merge( $a_set, $b_set ) );

	if ( empty( $union ) ) {
		return 1.0;
	}

	return (float) ( count( $intersection ) / count( $union ) );
}

/**
 * Finalise CoP induction atomically.
 *
 * A user is only treated as fully inducted when:
 * - a non-empty bio exists
 * - the original bio anchor exists
 * - the profile is marked visible
 * - the induction-complete flag is set
 *
 * @param int    $user_id  WordPress user ID.
 * @param string $bio_html Final bio HTML.
 * @return bool
 */
function glandore_cop_finalize_induction( int $user_id, string $bio_html ) : bool {
	$user_id  = absint( $user_id );
	$bio_html = trim( $bio_html );

	if ( ! $user_id ) {
		return false;
	}

	if ( '' === trim( wp_strip_all_tags( $bio_html ) ) ) {
		return false;
	}

	$bio_html = wp_kses_post( $bio_html );

	update_user_meta( $user_id, 'cop_profile_bio', $bio_html );

	$existing_original = (string) get_user_meta( $user_id, 'cop_profile_bio_original', true );
	if ( '' === trim( wp_strip_all_tags( $existing_original ) ) ) {
		update_user_meta( $user_id, 'cop_profile_bio_original', $bio_html );
	}

	update_user_meta( $user_id, 'cop_profile_visible', 1 );
	update_user_meta( $user_id, 'glandore_cop_about_complete', 1 );

	return true;
}

/**
 * AJAX handler – evaluate About You induction submission.
 *
 * Expects:
 * - POST['who']
 * - POST['work']
 * - POST['ethos']
 * - POST['bio']
 * - POST['nonce']
 *
 * This handler assumes upstream code has already produced:
 * - $decision
 * - $status
 * - $explanation
 * - $questions
 *
 * and that $bio contains the generated/suggested public bio.
 */
function glandore_cop_handle_about_submission() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'status'  => 'not_logged_in',
				'message' => 'A logged-in account is required.',
			),
			403
		);
	}

	check_ajax_referer( 'glandore_cop_about', 'nonce' );

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		wp_send_json_error(
			array(
				'status'  => 'no_user',
				'message' => 'Unable to identify the current user.',
			),
			400
		);
	}

	$who   = isset( $_POST['who'] ) ? wp_kses_post( wp_unslash( $_POST['who'] ) ) : '';
	$work  = isset( $_POST['work'] ) ? wp_kses_post( wp_unslash( $_POST['work'] ) ) : '';
	$ethos = ! empty( $_POST['ethos'] );

	$who_plain  = trim( wp_strip_all_tags( $who ) );
	$work_plain = trim( wp_strip_all_tags( $work ) );
	$ethos_ok   = (bool) $ethos;

	/*
	 * ---------------------------------------------------------
	 * Existing decision / gatekeeper logic should remain here.
	 * This drop-in keeps the likely current pattern, but the
	 * important change is in the finalisation block below.
	 * ---------------------------------------------------------
	 */

	$decision    = 'fail';
	$status      = 'need_more_detail';
	$explanation = '';
	$questions   = array();
	$bio         = isset( $_POST['bio'] ) ? wp_kses_post( wp_unslash( $_POST['bio'] ) ) : '';
	$bio         = trim( $bio );

	/*
	 * If an OpenAI/gatekeeper function already exists in your
	 * current file, leave it in place and remove this fallback.
	 */
	if ( function_exists( 'glandore_cop_openai_gatekeeper_assess' ) ) {
		$result = glandore_cop_openai_gatekeeper_assess(
			array(
				'user_id' => $user_id,
				'who'     => $who,
				'work'    => $work,
				'ethos'   => $ethos_ok,
				'bio'     => $bio,
			)
		);

		if ( is_array( $result ) ) {
			$decision    = isset( $result['decision'] ) ? (string) $result['decision'] : $decision;
			$status      = isset( $result['status'] ) ? (string) $result['status'] : $status;
			$explanation = isset( $result['explanation'] ) ? (string) $result['explanation'] : $explanation;
			$questions   = ! empty( $result['questions'] ) && is_array( $result['questions'] ) ? $result['questions'] : array();
			$bio         = isset( $result['bio'] ) ? (string) $result['bio'] : $bio;
		}
	}

	$bio = trim( $bio );

	$display_name = trim( get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true ) );
	if ( '' === $display_name ) {
		$user = get_userdata( $user_id );
		if ( $user ) {
			$display_name = $user->display_name ? $user->display_name : $user->user_login;
		}
	}

	if ( '' !== $bio ) {
		if ( false !== strpos( $bio, '%%NAME%%' ) ) {
			if ( '' !== $display_name ) {
				$bio = str_replace( '%%NAME%%', $display_name, $bio );
			} else {
				$bio = str_replace( '%%NAME%%', 'This member', $bio );
			}
		} elseif ( '' !== $display_name ) {
			$bio = preg_replace( '/\b[Tt]his member\b/', $display_name, $bio, 1 );
		}
	}

	update_user_meta( $user_id, 'cop_about_decision', $decision );
	update_user_meta( $user_id, 'cop_about_status', $status );
	update_user_meta( $user_id, 'cop_about_explanation', $explanation );

	if ( ! empty( $questions ) ) {
		update_user_meta( $user_id, 'cop_learning_questions', wp_json_encode( $questions ) );
	}

	/*
	 * Critical fix:
	 * do not mark induction complete unless a real bio exists.
	 */
	if ( 'pass' === $decision && 'welcome' === $status ) {
		if ( ! glandore_cop_finalize_induction( $user_id, $bio ) ) {
			wp_send_json_error(
				array(
					'status'  => 'missing_bio',
					'message' => 'Induction could not be completed because no bio was available to save.',
				),
				500
			);
		}
	}

	$display_message = '';

	if ( 'welcome' === $status ) {
		$display_message = 'This is enough for now to start contributing. Once the bio has been reviewed, the profile can always be refined or expanded later.';
	} elseif ( 'need_more_detail' === $status ) {
		if ( '' === $who_plain || '' === $work_plain ) {
			$display_message = 'Please add at least a short sentence under both “How would you briefly describe who you are?” and “How would you briefly describe the work you do?” before sending your introduction.';
		} elseif ( ! $ethos_ok ) {
			$display_message = 'To take part in this Community, please confirm that the ethos is accepted by ticking the box above.';
		} else {
			$display_message = 'A little more detail would help create a stronger contributor profile.';
		}
	} else {
		$display_message = 'Something went wrong while reviewing the introduction. Please try again.';
	}

	wp_send_json_success(
		array(
			'status'          => $status,
			'decision'        => $decision,
			'message'         => $display_message,
			'explanation'     => $explanation,
			'questions'       => $questions,
			'bio'             => $bio,
			'profile_complete'=> glandore_cop_is_profile_complete( $user_id ),
		)
	);
}
add_action( 'wp_ajax_glandore_cop_handle_about_submission', 'glandore_cop_handle_about_submission' );

/**
 * AJAX handler – save a manually edited bio.
 *
 * Expects:
 * - POST['bio']
 * - POST['nonce']
 */
function glandore_cop_save_profile_bio() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'status'  => 'not_logged_in',
				'message' => 'A logged-in account is required.',
			),
			403
		);
	}

	check_ajax_referer( 'glandore_cop_save_profile_bio', 'nonce' );

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		wp_send_json_error(
			array(
				'status'  => 'no_user',
				'message' => 'Unable to identify the current user.',
			),
			400
		);
	}

	$bio = isset( $_POST['bio'] ) ? wp_kses_post( wp_unslash( $_POST['bio'] ) ) : '';
	$bio = trim( $bio );

	if ( '' === $bio ) {
		wp_send_json_error(
			array(
				'status'  => 'empty',
				'message' => 'Please add a short bio before saving.',
			),
			400
		);
	}

	$plain = trim( wp_strip_all_tags( $bio ) );
	if ( strlen( $plain ) < 40 ) {
		wp_send_json_error(
			array(
				'status'  => 'too_short',
				'message' => 'For the Contributors page, the bio needs to be at least a couple of sentences.',
			),
			400
		);
	}

	$original = (string) get_user_meta( $user_id, 'cop_profile_bio_original', true );

	if ( '' === $original ) {
		if ( ! glandore_cop_finalize_induction( $user_id, $bio ) ) {
			wp_send_json_error(
				array(
					'status'  => 'save_failed',
					'message' => 'The bio could not be saved.',
				),
				500
			);
		}

		wp_send_json_success(
			array(
				'status'     => 'ok',
				'message'    => 'Bio saved.',
				'similarity' => 1.0,
			)
		);
	}

	$similarity = glandore_cop_wordset_similarity( $original, $bio );
	$threshold  = 0.6;

	if ( $similarity < $threshold ) {
		wp_send_json_error(
			array(
				'status'       => 'too_different',
				'message'      => 'Thanks for refining this. For now, the bio needs to stay broadly consistent with the original suggestion.',
				'similarity'   => $similarity,
				'original_bio' => wp_kses_post( $original ),
			),
			400
		);
	}

	if ( ! glandore_cop_finalize_induction( $user_id, $bio ) ) {
		wp_send_json_error(
			array(
				'status'  => 'save_failed',
				'message' => 'The bio could not be saved.',
			),
			500
		);
	}

	wp_send_json_success(
		array(
			'status'     => 'ok',
			'message'    => 'Bio saved.',
			'similarity' => $similarity,
		)
	);
}
add_action( 'wp_ajax_glandore_cop_save_profile_bio', 'glandore_cop_save_profile_bio' );