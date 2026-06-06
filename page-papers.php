<?php
/**
 * Template Name: Papers
 */

get_header();

$artefacts = [

    [
        'title' => 'Complexity and organisations',
        'parent' => null,
		'url'   => '#',
    ],
    [
        'title'  => 'Complexity, Strategic thinking and Organisational change',
        'parent' => 'Complexity and organisations',
        'url'    => '/wp-content/uploads/2020/05/Complexity-Strategic-thinking-and-Organisational-change-McMillan-Carlisle.pdf',
    ],
    [
        'title'  => 'Considering Organisation Structure and Design from a Complexity Paradigm Perspective',
        'parent' => 'Complexity and organisations',
        'url'    => '/wp-content/uploads/2020/05/organisational_learning_foundational_roots_for_design_for_complexity.pdf',
    ],

    [
        'title' => 'Software development',
		'parent' => null,
        'url'   => '#',
    ],
    [
        'title'  => 'Spiral Development: Experience, Principles and Refinements',
        'parent' => 'Software development',
        'url'    => '/wp-content/uploads/2022/05/Spiral-Boehm-SEI.pdf',
    ],
];

?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'papers'); ?>
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