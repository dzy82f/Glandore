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
        'title'  => 'See what members of the community are discussing.',
        'parent' => null,
        'url'    => home_url( '/community-discussions/' ),
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
