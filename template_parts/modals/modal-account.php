<?php
/**
 * Account / member login modal.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div
	id="glandore-account-modal"
	class="auth-modal account-modal"
	hidden
	aria-hidden="true"
	role="dialog"
	aria-modal="true"
>
	<div class="auth-modal__backdrop" data-account-modal-close="1" tabindex="-1" aria-hidden="true"></div>

	<div class="auth-modal__panel account-modal__panel" role="document">
		<button
			type="button"
			class="auth-modal__close"
			data-account-modal-close="1"
			aria-label="<?php esc_attr_e( 'Close', 'glandore' ); ?>"
		>×</button>

		<?php if ( is_user_logged_in() ) : ?>

			<h2>Account dashboard</h2>

			<p>This is your account dashboard. From here you can:</p>

			<ul>
				<li><a href="#glandore-modal-account-profile">Edit your profile</a></li>
				<li><a href="#glandore-modal-registration-update">Edit your registration data</a></li>
				<li><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Lost your password?</a></li>
				<li><a href="#glandore-delete-account-modal">Delete your account</a></li>
			</ul>

		<?php else : ?>

			<h2>Member login</h2>

			<div class="account-modal__login">
				<?php echo do_shortcode( '[user_registration_my_account]' ); ?>
			</div>

		<?php endif; ?>

	</div>
</div>