<?php
/**
 * File: /wp-content/themes/glandore/inc/theme-bootstrap.php
 *
 * Single orchestration point for theme bootstrapping.
 * Keep functions.php thin: require this file and nothing else.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * --------------------------------------------------------
 * CORE INFRASTRUCTURE
 * --------------------------------------------------------
 */
require_once get_template_directory() . '/inc/register-handler.php';
require_once get_stylesheet_directory() . '/inc/analytics-consent.php';
require_once get_stylesheet_directory() . '/inc/analytics-consent-ajax.php';
require_once get_stylesheet_directory() . '/inc/access-gate.php';
require_once get_stylesheet_directory() . '/inc/login-redirect.php';
require_once get_stylesheet_directory() . '/inc/registration-modal.php';
require_once get_template_directory() . '/inc/access-gate-continuation.php';
require_once get_template_directory() . '/inc/registration-update-modal.php';

require_once get_template_directory() . '/inc/account-modal.php';
require_once get_template_directory() . '/inc/account-profile-modal.php';
require_once get_template_directory() . '/inc/lost-password-modal.php';
require_once get_template_directory() . '/inc/glandore-delete-account-modal.php';

/**
 * --------------------------------------------------------
 * COMMUNITY OF PRACTICE
 * --------------------------------------------------------
 */
require_once get_stylesheet_directory() . '/inc/cop-openai-gatekeeper.php';
require_once get_stylesheet_directory() . '/inc/cop-profile.php';
require_once get_stylesheet_directory() . '/inc/cop-tinymce.php';
require_once get_stylesheet_directory() . '/inc/cop-bio-editor.php';

require_once get_template_directory() . '/inc/cop-db.php';
require_once get_template_directory() . '/inc/cop-privacy.php';
require_once get_template_directory() . '/inc/cop-print.php';

/**
 * --------------------------------------------------------
 * PROBLEM CARDS / ISSUE STUDIO
 * --------------------------------------------------------
 */
require_once get_stylesheet_directory() . '/inc/problem-cards.php';
require_once get_stylesheet_directory() . '/inc/issue-studio-artefacts.php';
require_once get_stylesheet_directory() . '/inc/issue-studio-chain.php';

/**
 * --------------------------------------------------------
 * THEME SETUP
 * --------------------------------------------------------
 */
require_once get_template_directory() . '/inc/enqueue-assets.php';

function glandore_theme_setup() : void {

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'menus' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'glandore' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support( 'editor-styles' );
	add_editor_style( 'editor-style.css' );
}
add_action( 'after_setup_theme', 'glandore_theme_setup' );

/**
 * --------------------------------------------------------
 * BODY CLASSES
 * --------------------------------------------------------
 */
function glandore_add_split_layout_class( array $classes ) : array {
	if ( is_page_template( 'page-split.php' ) ) {
		$classes[] = 'split-layout';
	}

	return $classes;
}
add_filter( 'body_class', 'glandore_add_split_layout_class' );

function glandore_add_mobile_pane_class( array $classes ) : array {
	if ( is_page_template( 'page-systems.php' ) ) {
		$classes[] = 'glandore-mobile-pane';
	}

	return $classes;
}
add_filter( 'body_class', 'glandore_add_mobile_pane_class' );

/**
 * --------------------------------------------------------
 * MAILER CONFIGURATION
 * --------------------------------------------------------
 */
add_filter(
	'glandore_mailer',
	function () {
		return new Postmark_Mailer(
			POSTMARK_TOKEN,
			'noreply@k2pmr.com'
		);
	}
);

/**
 * --------------------------------------------------------
 * LOGIN REDIRECT → FRONT PAGE
 * --------------------------------------------------------
 */
function glandore_login_redirect_to_front( string $redirect_to, string $request, $user ) : string {

	if ( is_wp_error( $user ) || ! $user ) {
		return $redirect_to;
	}

	return home_url( '/' );
}
add_filter( 'login_redirect', 'glandore_login_redirect_to_front', 10, 3 );

/**
 * --------------------------------------------------------
 * URM Registration Stage 1 – page-specific styles
 * --------------------------------------------------------
 */
function glandore_enqueue_urm_stage1_styles() : void {

	if ( ! is_page( 'registration-stage-1' ) ) {
		return;
	}

	$rel  = '/assets/css/urm-stage1.css';
	$path = get_stylesheet_directory() . $rel;

	if ( file_exists( $path ) ) {
		wp_enqueue_style(
			'glandore-urm-stage1',
			get_stylesheet_directory_uri() . $rel,
			array( 'glandore-style' ),
			filemtime( $path )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_urm_stage1_styles', 60 );

/**
 * --------------------------------------------------------
 * URM: Show/Hide password toggle
 * --------------------------------------------------------
 */
function glandore_urm_password_toggle_assets() : void {

	if ( ! function_exists( 'user_registration' ) ) {
		return;
	}

	$js_rel  = '/js/urm-show-password.js';
	$js_path = get_stylesheet_directory() . $js_rel;

	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'glandore-urm-show-password',
			get_stylesheet_directory_uri() . $js_rel,
			array(),
			filemtime( $js_path ),
			true
		);
	}

	$css_rel  = '/css/urm-show-password.css';
	$css_path = get_stylesheet_directory() . $css_rel;

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'glandore-urm-show-password',
			get_stylesheet_directory_uri() . $css_rel,
			array( 'glandore-style' ),
			filemtime( $css_path )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_urm_password_toggle_assets', 70 );

/**
 * --------------------------------------------------------
 * URM Account restyle (My Account page only)
 * --------------------------------------------------------
 */
function glandore_enqueue_urm_account_css() : void {

	if ( ! is_page( 'account' ) ) {
		return;
	}

	$rel  = '/assets/css/urm-account.css';
	$path = get_stylesheet_directory() . $rel;

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'glandore-urm-account',
		get_stylesheet_directory_uri() . $rel,
		array( 'glandore-style' ),
		filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_urm_account_css', 80 );

/**
 * --------------------------------------------------------
 * Issue Studio Result assets
 * --------------------------------------------------------
 */
function glandore_enqueue_issue_studio_result_assets() : void {

	if ( ! is_page_template( 'page-issue-studio-result.php' ) ) {
		return;
	}

	$css_rel  = '/assets/css/issue-studio-result.css';
	$css_path = get_stylesheet_directory() . $css_rel;

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'glandore-issue-studio-result',
			get_stylesheet_directory_uri() . $css_rel,
			array( 'glandore-style' ),
			filemtime( $css_path )
		);
	}

	$js_rel  = '/assets/js/issue-studio-result.js';
	$js_path = get_stylesheet_directory() . $js_rel;

	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'glandore-issue-studio-result',
			get_stylesheet_directory_uri() . $js_rel,
			array(),
			filemtime( $js_path ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_issue_studio_result_assets', 85 );

/**
 * --------------------------------------------------------
 * Ensure wp_editor assets load on front-end where needed
 * --------------------------------------------------------
 */
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
add_action( 'wp_enqueue_scripts', 'glandore_cop_enqueue_frontend_editors', 90 );

/**
 * --------------------------------------------------------
 * Footer controller
 * --------------------------------------------------------
 */
function glandore_enqueue_footer_controller() : void {

	wp_enqueue_script(
		'glandore-footer-controller',
		get_template_directory_uri() . '/assets/js/footer-controller.js',
		array(),
		'1.0.0',
		true
	);

	wp_add_inline_script(
		'glandore-footer-controller',
		'window.glandoreAjax = ' . wp_json_encode(
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_footer_controller', 20 );

/**
 * --------------------------------------------------------
 * SAFE MENU FALLBACK
 * --------------------------------------------------------
 */
function glandore_nav_fallback() : void {
	echo '<ul class="glandore-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
	echo '</ul>';
}