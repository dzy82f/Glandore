<?php
/**
 * Template Name: Delete Account Debug
 * Description: Renders the delete account modal contents as a normal page
 *              so we can inspect layout and styling.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
  <div class="delete-account-debug">
    <h1 class="page-title">
      <?php esc_html_e( 'Delete account – debug view', 'glandore' ); ?>
    </h1>

    <?php
    // Render the modal template part as-is.
    get_template_part( 'template_parts/modals/modal-delete-account' );
    ?>
  </div>
</main>

<style>
/* Only affect this debug template */
body.page-template-page-delete-account-debug
  #glandore-modal-delete-account {
  position: static;
  inset: auto;
  display: block;
  opacity: 1;
  visibility: visible;
  z-index: auto;
}

/* Hide fullscreen backdrop in debug view */
body.page-template-page-delete-account-debug
  #glandore-modal-delete-account .auth-modal__backdrop {
  display: none;
}

/* Make the dialog look like a normal card */
body.page-template-page-delete-account-debug
  #glandore-modal-delete-account .auth-modal__dialog {
  position: static;
  max-width: 640px;
  margin: 2rem auto;
}
</style>

<?php
get_footer();
