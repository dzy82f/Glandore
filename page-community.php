<?php
/**
 * Template Name: Community
 */

get_header();

/**
 * Artefacts surfaced on the CoP page.
 * Canonical hierarchy enforced via explicit parent.
 */
$artefacts = [

    [
        'title'  => 'Discussions. This provides registered users with read access to Contributions.',
        'parent' => null,
        'url'    => home_url( '/community-discussions/' ),
    ],
    
    [
        'title'  => 'Induction. This is required if you want to Contribute.',
        'parent' => null,
        'url'    => home_url( '/induction/' ),
    ],
    
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'community'); ?>
        </div>

        <!-- RIGHT -->
        <div class="page-split-right">
            <h4>Options</h4>

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
