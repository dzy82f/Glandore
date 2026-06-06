<?php
/**
 * Glandore – Account Dashboard Modal
 *
 * Visual pattern:
 * - Same overlay layout as registration completion (auth-modal)
 * - Big centred card, scrollable if needed
 *
 * Behaviour:
 * - Hidden by [hidden] attribute
 * - Opened/closed by assets/js/account-modal.js
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();
$display_name = '';
$profile_url   = esc_url( admin_url( 'profile.php' ) );
$password_url  = esc_url( wp_lostpassword_url( home_url( '/account/' ) ) );
$delete_url = esc_url( '#glandore-modal-delete-account' );
$delete_url = esc_url( home_url( '/delete-account-debug/' ) );

if ( $current_user && $current_user->exists() ) {
	$display_name = $current_user->display_name
		? $current_user->display_name
		: $current_user->user_login;
}
?>
<div
	id="glandore-account-modal"
	class="auth-modal account-modal"
	role="dialog"
	aria-modal="true"
	aria-hidden="true"
	hidden
>
	<div class="auth-modal__backdrop" data-account-modal-close="1" tabindex="-1" aria-hidden="true"></div>

	<div class="auth-modal__dialog" role="document" aria-labelledby="account-panel-title">
		<button
			type="button"
			class="auth-modal__close"
			aria-label="Close account dashboard"
			data-account-modal-close="1"
		>
			<span aria-hidden="true">&times;</span>
		</button>

		<section class="account-panel" role="document" aria-labelledby="account-panel-title">
			<header class="account-panel-header">
				<h2 id="account-panel-title" class="account-panel-title">
					Account dashboard
				</h2>
			</header>

			<div class="account-panel-body">
				<?php if ( $display_name ) : ?>
					<p class="account-panel-welcome">
						Welcome, <strong><?php echo esc_html( $display_name ); ?></strong>
					</p>
				<?php endif; ?>

				<div class="account-panel-avatar" aria-hidden="true"></div>

				<p class="account-panel-intro">
					This is your account dashboard. From here you can:
				</p>

				<ul class="account-panel-actions">
				
					<li>
					  <a
						href="#glandore-modal-account-profile"
						data-auth-modal-open="account-profile"
						role="button"
					  >
						<?php esc_html_e( 'Edit your profile', 'glandore' ); ?>
					  </a>
					</li>

					<li>
						<a
							href="#glandore-modal-registration-update"
							data-auth-modal-open="registration-update"
							role="button"
						>
							Edit your registration data
						</a>
					</li>
					
					<li>
					  <a
						href="#glandore-modal-lost-password"
						data-auth-modal-open="lost-password"
						role="button"
					  >
						<?php esc_html_e( 'Lost your password?', 'glandore' ); ?>
					  </a>
					</li>

					<li>
					  <a
						href="<?php echo $delete_url; ?>"
						class="account-modal__danger-link"
					  >
					  Delete your account
					  </a>
					</li>

				</ul>
			</div>
		</section>
	</div>
</div>
