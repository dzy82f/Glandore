<?php
/**
 * Template Name: About
 */

get_header();

/**
 * Artefacts surfaced on the About page.
 */
$artefacts = [
    [
        'title' => 'CV',
        'url'   => '/wp-content/uploads/2025/12/CV_v0_6_20251201.pdf',
    ],
    [
        'title' => 'LinkedIn',
        'url'   => 'https://www.linkedin.com/in/julian-macnamara-98026112a/',
		'target' => '_blank',
    ],
    [
        'title' => 'Email',
        'url'   => 'mailto:julian@glandore.com',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'about'); ?>
        </div>

        <!-- RIGHT -->
        <div class="page-split-right">
            <h4>Artefacts</h4>

            <?php
            if (!empty($artefacts)) {
                get_template_part(
                    'template_parts/artefact-list',
                    null,
                    ['artefacts' => $artefacts]
                );
            } else {
                echo '<p class="artefact-empty">There are no additional artefacts currently.</p>';
            }
            ?>
        </div>

    </div>
</main>
