<?php
/**
 * File: /wp-content/themes/glandore/inc/cop-editors.php
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_cop_enqueue_frontend_editors() : void {

	if (
		is_page_template( 'page-cop-about-dialogue.php' ) ||
		is_page_template( 'page-cop-post.php' )
	) {
		if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		} else {
			wp_enqueue_script( 'editor' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_cop_enqueue_frontend_editors' );