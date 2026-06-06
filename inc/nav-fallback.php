<?php
/**
 * File: /wp-content/themes/glandore/inc/nav-fallback.php
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_nav_fallback() : void {
	echo '<ul class="glandore-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
	echo '</ul>';
}