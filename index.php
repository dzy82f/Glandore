<?php
echo "<!-- USING INDEX.PHP instead of FRONT-PAGE.PHP -->";
get_header();
?>

<div class="landing-background">

    <div class="landing-overlay"></div>

    <main class="landing-content">
        <div class="landing-intro-content">
            <?php get_template_part('sections/intro'); ?>
        </div>
    </main>

</div>

<?php get_footer(); ?>
