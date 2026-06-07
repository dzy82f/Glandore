<?php
/**
 * The default template for all pages
 *
 * @package glandore
 */

get_header();
?>

<main id="main" class="site-main page-single">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
</main>

<?php
get_footer();
