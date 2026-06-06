<?php
/**
 * Template Name: Thinking
 */

get_header();

/**
 * Artefacts surfaced on the JnJ page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [
    [
        'title'  => 'Thinking',
        'parent' => null,
        'url'    => '/thinking/',
    ],
    [
        'title'  => 'Turning AI into a Multi-Perspective Thinking System',
        'parent' => 'Thinking',
        'url'    => '/wp-content/uploads/2026/03/Example_AI_thinking_v0_1.pdf',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'thinking'); ?>
        </div>

        <!-- RIGHT: artefacts -->
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
                echo '<p class="artefact-empty">There are no artefacts currently.</p>';
            }
            ?>
        </div>

    </div>
</main>	