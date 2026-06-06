<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php
$glandore_login_modal_url = add_query_arg(
	'login_modal',
	'1',
	home_url( '/' )
);

// -----------------------------------------------------------------------------
// Auth links (desktop)
// -----------------------------------------------------------------------------
ob_start();

if ( is_user_logged_in() ) {
	?>
	<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Logout</a>
	<?php
} else {
	?>
	<a
		id="header-login-link"
		href="<?php echo esc_url( $glandore_login_modal_url ); ?>"
		data-login-modal-open="1"
	>
		Login / Register
	</a>
	<?php
}

$glandore_auth_links_desktop = trim( ob_get_clean() );

// -----------------------------------------------------------------------------
// Auth links (mobile)
// -----------------------------------------------------------------------------
ob_start();

if ( is_user_logged_in() ) {
	?>
	<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Logout</a>
	<?php
} else {
	?>
	<a
		href="<?php echo esc_url( $glandore_login_modal_url ); ?>"
		data-login-modal-open="1"
	>
		Login / Register
	</a>
	<?php
}

$glandore_auth_links_mobile = trim( ob_get_clean() );

// -----------------------------------------------------------------------------
// Context detection
// -----------------------------------------------------------------------------
$is_mobile      = wp_is_mobile();
$is_mobile_home = isset( $is_mobile_home ) ? $is_mobile_home : false;
?>

<?php if ( $is_mobile && $is_mobile_home ) : ?>

	<header class="g-mobile-header" role="banner">
		<div class="g-mobile-header__inner">

			<div class="g-mobile-header__logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img
						src="/wp-content/uploads/2026/03/AI_large_1.png"
						alt="Glandore Associates">
				</a>
			</div>

			<div class="g-mobile-header__auth">
				<?php echo $glandore_auth_links_mobile; ?>
			</div>

		</div>
	</header>

<?php else : ?>

	<header class="glandore-header">

		<div class="header-desktop">

			<div class="header-inner">

				<div class="glandore-logo">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img
							src="/wp-content/uploads/2026/03/AI_large_1.png"
							alt="Glandore Associates">
					</a>
				</div>

				<nav class="glandore-nav" id="primary-menu" aria-label="Primary">
					<ul class="glandore-menu">
						<li><a href="/">Home</a></li>
						<li><a href="/issue-studio/">The Issue Studio</a></li>
						<li><a href="/community-of-practice/">Community</a></li>
						<li><a href="/tychevia/">Tychevia</a></li>
						<li><a href="/systems/">Systems</a></li>
						<li><a href="/general-ai/">General AI</a></li>						
						<li><a href="/papers/">Papers</a></li>
						<li><a href="/about/">About</a></li>
					</ul>
				</nav>

			</div>

			<div class="header-auth">
				<?php echo $glandore_auth_links_desktop; ?>
			</div>

		</div>

		<?php if ( $is_mobile ) : ?>
			<div class="header-mobile">
				<div class="header-mobile-inner">

					<div class="header-mobile-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<img
								src="/wp-content/uploads/2026/03/Logo_inf_imgn_v0_4.png"
								alt="Glandore Associates">
						</a>
					</div>

					<div class="header-mobile-auth">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-mobile-home-link">
							Home
						</a>
					</div>

				</div>
			</div>
		<?php endif; ?>

	</header>

<?php endif; ?>