<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
  id="glandore-modal-delete-account"
  class="auth-modal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="glandore-modal-delete-account-title"
  aria-hidden="true"
>
  <button
    type="button"
    class="auth-modal__backdrop"
    data-auth-modal-close="1"
  ></button>

  <div class="auth-modal__dialog">
    <button
      type="button"
      class="auth-modal__close"
      data-auth-modal-close="1"
      aria-label="<?php esc_attr_e( 'Close dialog', 'glandore' ); ?>"
    >
      &times;
    </button>

    <header class="auth-modal__header">
      <h2
        id="glandore-modal-delete-account-title"
        class="auth-modal__title"
      >
        <?php esc_html_e( 'Delete your account', 'glandore' ); ?>
      </h2>
      <p class="auth-modal__subtitle">
        <?php esc_html_e(
          "I'm sorry you're thinking of deleting your account but thank you for your interest.",
          'glandore'
        ); ?>
      </p>
    </header>

    <div class="auth-modal__body">
      <p class="auth-modal__copy">
        <?php esc_html_e(
          'Once you delete your account, you will be logged out immediately and your profile will be removed from the site.',
          'glandore'
        ); ?>
      </p>

      <form
        id="glandore-delete-account-form"
        class="auth-modal__form"
        method="post"
        action=""
      >
        <?php
        // Nonce used by the hard-delete handler in inc/glandore-delete-account.php
        wp_nonce_field( 'glandore_delete_account_page', 'glandore_delete_account_nonce' );
        ?>

        <div
          id="glandore-delete-account-error"
          class="auth-modal__message auth-modal__message--error"
          style="display:none"
          aria-live="polite"
        ></div>

        <div
          id="glandore-delete-account-success"
          class="auth-modal__message auth-modal__message--success"
          style="display:none"
          aria-live="polite"
        ></div>

        <fieldset class="auth-modal__field">
          <legend class="auth-modal__label">
            <?php esc_html_e(
              "It would be very helpful if you could help me understand why you're considering leaving",
              'glandore'
            ); ?>
          </legend>

          <label class="auth-modal__label" for="glandore-delete-reason">
            <?php esc_html_e( 'Reason for deleting your account', 'glandore' ); ?>
          </label>
          <select
            id="glandore-delete-reason"
            name="glandore_delete_reason"
            class="auth-modal__input"
          >
            <option value="">
              <?php esc_html_e( 'Select a reason (optional)', 'glandore' ); ?>
            </option>
            <option value="just_exploring">
              <?php esc_html_e( 'I was just exploring / testing the site', 'glandore' ); ?>
            </option>
            <option value="no_time">
              <?php esc_html_e( 'I do not have time to use it right now', 'glandore' ); ?>
            </option>
            <option value="too_complex">
              <?php esc_html_e( 'It feels too complex or confusing', 'glandore' ); ?>
            </option>
            <option value="not_relevant">
              <?php esc_html_e( 'It is not relevant to my work or interests', 'glandore' ); ?>
            </option>
            <option value="privacy_concerns">
              <?php esc_html_e( 'I am concerned about data or privacy', 'glandore' ); ?>
            </option>
            <option value="no_accounts_general">
              <?php esc_html_e( 'I prefer not to maintain online accounts generally', 'glandore' ); ?>
            </option>
            <option value="duplicate_account">
              <?php esc_html_e( 'I created a duplicate account by mistake', 'glandore' ); ?>
            </option>
            <option value="other">
              <?php esc_html_e( 'Other reason', 'glandore' ); ?>
            </option>
            <option value="prefer_not_to_say">
              <?php esc_html_e( 'Prefer not to say', 'glandore' ); ?>
            </option>
          </select>

          <label
            class="auth-modal__label"
            for="glandore-delete-reason-comment"
          >
            <?php esc_html_e( 'Any other comments? (optional)', 'glandore' ); ?>
          </label>
          <textarea
            id="glandore-delete-reason-comment"
            name="glandore_delete_reason_comment"
            class="auth-modal__input"
            rows="3"
          ></textarea>
        </fieldset>

        <div class="auth-modal__actions">
          <a
            href="<?php echo esc_url( home_url( '/' ) ); ?>"
            class="auth-modal__button auth-modal__button--secondary"
          >
            <?php esc_html_e( 'Cancel', 'glandore' ); ?>
          </a>

          <button
            type="submit"
            class="auth-modal__button auth-modal__button--danger"
          >
            <?php esc_html_e( 'Delete my account', 'glandore' ); ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
