<?php
/**
 * Template Name: Split Page (Left + Sticky Right)
 */

get_header();
?>

<main id="main" class="split-layout">

    <div class="page-split">

        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'tychevia'); ?>
        </div>

        <aside class="page-split-right">
            <?php get_template_part('template_parts/artefacts', 'tychevia'); ?>
        </aside>

    </div>

</main>
