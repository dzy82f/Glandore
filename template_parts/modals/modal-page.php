<?php
/**
 * Template Name: Modal Page
 *
 * Renders normal page content inside the Glandore modal shell.
 */

get_header();

// If you already have a canonical modal wrapper class, use it.
// This is deliberately minimal and relies on your existing modal CSS.
?>

<div class="glandore-modal-overlay" role="presentation"></div>

<section class="glandore-modal" role="dialog" aria-modal="true" aria-label="<?php the_title_attribute(); ?>">

    <button class="glandore-modal-close" type="button" aria-label="Close">
        ×
    </button>

    <div class="glandore-modal-inner">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>

</section>

<script>
(function () {
  const closeBtn = document.querySelector('.glandore-modal-close');
  const overlay  = document.querySelector('.glandore-modal-overlay');

  function closeModal() {
    // Return to home (or wherever your login modal returns)
    window.location.href = document.body.getAttribute('data-home-url') || '/';
  }

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (overlay)  overlay.addEventListener('click', closeModal);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });
})();
</script>

<?php
get_footer();
