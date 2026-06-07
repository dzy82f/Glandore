<?php
/**
 * File: /wp-content/themes/glandore/inc/mailer.php
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'glandore_mailer',
	function () {
		return new Postmark_Mailer(
			POSTMARK_TOKEN,
			'noreply@k2pmr.com'
		);
	}
);