<?php
/**
 * CoP – Bio Editor
 *
 * Handles the "review & lock in" step for CoP bios:
 * - TinyMCE bio editor
 * - AJAX save with similarity check against original
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS for the CoP bio editor on the induction page.
 */
function glandore_cop_bio_editor_enqueue_scripts() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! is_page_template( 'page-cop-about-dialogue.php' ) ) {
		return;
	}

	$theme_uri = get_stylesheet_directory_uri();

	wp_enqueue_script(
		'glandore-cop-bio-editor',
		$theme_uri . '/assets/js/cop-bio-editor.js',
		array( 'jquery' ),
		'20260328-1',
		true
	);

	wp_localize_script(
		'glandore-cop-bio-editor',
		'glandoreCopBioEditor',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'glandore_cop_save_bio' ),
			'messages' => array(
				'saving' => 'Saving bio…',
				'saved'  => 'Bio saved successfully.',
				'error'  => 'Something went wrong. Please try again.',
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_cop_bio_editor_enqueue_scripts' );

/**
 * Calculate simple word-set similarity between two strings.
 *
 * Kept here for backwards compatibility if this file is loaded independently.
 *
 * @param string $a First string.
 * @param string $b Second string.
 * @return float
 */
if ( ! function_exists( 'glandore_cop_wordset_similarity' ) ) {
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
}

/**
 * AJAX handler – save edited CoP bio with similarity check.
 *
 * Expects:
 * - POST['edited_bio']
 * - POST['nonce']
 */
function glandore_cop_save_bio() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'status'  => 'not_logged_in',
				'message' => 'A logged-in account is required to save a bio.',
			)
		);
	}

	check_ajax_referer( 'glandore_cop_save_bio', 'nonce' );

	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		wp_send_json_error(
			array(
				'status'  => 'no_user',
				'message' => 'Unable to identify the current user.',
			)
		);
	}

	$edited_raw = isset( $_POST['edited_bio'] ) ? wp_unslash( $_POST['edited_bio'] ) : '';

	if ( '' === trim( $edited_raw ) ) {
		wp_send_json_error(
			array(
				'status'  => 'empty_bio',
				'message' => 'The bio cannot be empty.',
			)
		);
	}

	$original = (string) get_user_meta( $user_id, 'cop_profile_bio_original', true );

	if ( '' === trim( $original ) ) {
		if ( ! function_exists( 'glandore_cop_finalize_induction' ) || ! glandore_cop_finalize_induction( $user_id, $edited_raw ) ) {
			wp_send_json_error(
				array(
					'status'  => 'save_failed',
					'message' => 'The bio could not be saved.',
				)
			);
		}

		wp_send_json_success(
			array(
				'status'  => 'ok',
				'message' => 'Your bio has been saved.',
			)
		);
	}

	$similarity = glandore_cop_wordset_similarity( $original, $edited_raw );
	$threshold  = 0.6;

	if ( $similarity >= $threshold ) {
		if ( ! function_exists( 'glandore_cop_finalize_induction' ) || ! glandore_cop_finalize_induction( $user_id, $edited_raw ) ) {
			wp_send_json_error(
				array(
					'status'  => 'save_failed',
					'message' => 'The bio could not be saved.',
				)
			);
		}

		wp_send_json_success(
			array(
				'status'     => 'ok',
				'message'    => 'Your bio has been saved.',
				'similarity' => $similarity,
			)
		);
	}

	$original_html = (string) get_user_meta( $user_id, 'cop_profile_bio_original', true );

	wp_send_json_error(
		array(
			'status'       => 'too_different',
			'message'      => 'Thanks for refining this. For now, the bio needs to stay broadly consistent with the original suggestion.',
			'similarity'   => $similarity,
			'original_bio' => wp_kses_post( $original_html ),
		)
	);
}
add_action( 'wp_ajax_glandore_cop_save_bio', 'glandore_cop_save_bio' );