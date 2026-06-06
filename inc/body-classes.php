<?php
/**
 * File: /wp-content/themes/glandore/inc/body-classes.php
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_body_classes( array $classes ) : array {

	if ( is_page_template( 'page-systems.php' ) ) {
		$classes[] = 'glandore-mobile-pane';
	}

	if ( is_page_template( 'page-split.php' ) ) {
		$classes[] = 'split-layout';
	}

	return $classes;
}
add_filter( 'body_class', 'glandore_body_classes' );