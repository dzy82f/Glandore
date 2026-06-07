<?php
/**
 * Template Name: Glandore – Account
 * Template Post Type: page
 *
 * Canonical /account/ page with optional modal fragment mode (?account_modal=1).
 * - Full page: includes header/footer.
 * - Fragment: returns ONLY the account card body for modal injection.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_modal_fragment = isset( $_GET['account_modal'] ) && $_GET['account_modal'] === '1';

if ( ! $is_modal_fragment ) {
	get_header();
}

$current_user = wp_get_current_user();
$display_name = ( $current_user && $current_user->exists() )
	? ( $current_user->display_name ? $current_user->display_name : $current_user->user_login )
	: '';

$account_url = home_url( '/account/' );
$login_url   = add_query_arg(
	array(
		'login_modal' => '1',
		'redirect_to' => rawurlencode( $account_url ),
	),
	home_url( '/' )
);

?>
<div class="glandore-account-shell<?php echo $is_modal_fragment ? ' is-modal-fragment' : ''; ?>">

	<?php if ( ! is_user_logged_in() ) : ?>

		<section class="account-panel" role="document" aria-labelledby="account-panel-title">
			<header class="account-panel-header">
				<h2 id="account-panel-title" class="account-panel-title">Account Dashboard</h2>
			</header>

			<div class="account-panel-body">
				<p class="account-panel-intro">
					Please sign in to access your account.
				</p>

				<p class="account-panel-actions-cta">
					<a class="account-panel-primary" href="<?php echo esc_url( $login_url ); ?>">
						Sign in
					</a>
				</p>
			</div>
		</section>

	<?php else : ?>

		<section class="account-panel" role="document" aria-labelledby="account-panel-title">
			<header class="account-panel-header">
				<h2 id="account-panel-title" class="account-panel-title">Account Dashboard</h2>
			</header>

			<div class="account-panel-body">
				<p class="account-panel-welcome">
					Welcome, <strong><?php echo esc_html( $display_name ); ?></strong>
				</p>

				<div class="account-panel-avatar" aria-hidden="true"></div>

				<p class="account-panel-intro">
					This is your account dashboard. From here you can:
				</p>

				<ul class="account-panel-actions">
					<li><a href="<?php echo esc_url( $account_url ); ?>">Edit your profile</a></li>
					<li><a href="<?php echo esc_url( $account_url ); ?>">Edit your registration data</a></li>
					<li><a href="<?php echo esc_url( $account_url ); ?>">Edit your password</a></li>
					<li><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Logout</a></li>
					<li><a href="<?php echo esc_url( $account_url ); ?>">Delete your account</a></li>
				</ul>

				<?php if ( ! $is_modal_fragment ) : ?>
					<div class="account-panel-divider"></div>

					<div class="account-panel-urm">
						<?php
						/**
						 * If your /account/ page content already contains the URM shortcode via WP editor,
						 * it will render through the_content() below.
						 *
						 * If you prefer hard-wiring URM here, uncomment the do_shortcode line and remove the_content().
						 */
						// echo do_shortcode( '[user_registration_my_account]' );
						while ( have_posts() ) :
							the_post();
							the_content();
						endwhile;
						?>
					</div>
				<?php endif; ?>

			</div>
		</section>

	<?php endif; ?>

</div>
<?php

if ( ! $is_modal_fragment ) {
	get_footer();
}
