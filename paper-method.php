<?php
/**
 * Template Name: Systems
 */

get_header();

/**
 * Artefacts surfaced on the Systems page.
 */
$artefacts = [

    // Public sector
    [
        'title' => 'Public sector',
        'parent'=> 'Method',
        'url'   => 'http://167.235.246.112/public-sector/',
    ],
    [
        'title' => 'NHS',
        'parent'=> 'Public sector',
        'url'   => 'http://167.235.246.112/nhs/',
    ],

    // Private sector
    [
        'title' => 'Private sector',
        'parent'=> 'Method',
        'url'   => 'http://167.235.246.112/private-sector/',
    ],
    [
        'title' => 'Shell',
        'parent'=> 'Private sector',
        'url'   => 'http://167.235.246.112/shell/',
    ],
    [
        'title' => 'Stellantis',
        'parent'=> 'Private sector',
        'url'   => 'http://167.235.246.112/stellantis/',
    ],
    [
        'title' => 'Johnson & Johnson',
        'parent'=> 'Private sector',
        'url'   => 'http://167.235.246.112/johnson-johnson/',
    ],
    [
        'title' => 'Premier Miton',
        'parent'=> 'Private sector',
        'url'   => 'http://167.235.246.112/premier-miton/',
    ],

];

?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'systems'); ?>
        </div>

        
        <!-- RIGHT: additional artefacts -->
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