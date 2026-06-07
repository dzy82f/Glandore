<?php
/**
 * Template Name: General AI
 */

get_header();

/**
 * Artefacts surfaced on the Use cases page.
 */
$artefacts = [
[
        'title' => 'General AI',
		'parent' => null,
        'url'   => '/thinking/',
    ],
   [
        'title' => 'Turning AI into a Multi-Perspective Thinking System',
		'parent' => 'General AI',
        'url'   => '/thinking/',
    ],
	
	[
        'title' => 'How to use Iterative Dialogue to co-create task-driven .md files',
		'parent' => 'General AI',
        'url'   => '/dialogue/',
    ],
	[
        'title' => 'Can Personas Think? (PDF)',
		'parent' => 'General AI',
        'url'   => '/wp-content/uploads/2025/08/Orchard_v1.0.pdf',
    ],
    [
        'title' => 'Using ChatGPT Agents to address Issues with Microsoft software (PDF)' ,
		'parent' => 'General AI',
        'url'   => '/wp-content/uploads/2025/07/Pluto-v0.5.pdf',
    ],
	[
		'title' => 'ChatGPT Limitations (PDF)',
		'parent' => 'General AI',
		'url' 	=> '/wp-content/uploads/2025/05/OpenAI_20250529.pdf',
	],	
];

?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'general-ai'); ?>
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