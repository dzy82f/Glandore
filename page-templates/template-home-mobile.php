<?php
/**
 * Template Name: Home (Mobile Service)
 * Description: Mobile-oriented home page with introduction and core WP login link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logged_in = is_user_logged_in();

get_header();
?>

<main class="g-mobile-home" role="main">
	<div class="g-mobile-home__inner">

		<section class="g-mobile-home__intro">

			<p>
				Mobile access is provided as a “convenience” for registered users. 
				Registration is only possible via the desktop or a laptop.
			</p>

			<p>
				The site itself is a living laboratory for exploring systems — especially Complex Adaptive
				Systems. It is shaped by the patterns, tensions and hidden structures that drive real-world
				outcomes and offers resources on:
			</p>

			<nav class="g-mobile-home__nav" aria-label="Site sections">
				<ul>
					<li>
						<?php if ( $logged_in ) : ?>
							<a href="/tychevia/">Tychevia</a>
						<?php else : ?>
							<span class="g-mobile-home__nav-label">Tychevia</span>
						<?php endif; ?>
					</li>
					<li>
						<?php if ( $logged_in ) : ?>
							<a href="/systems/">Systems</a>
						<?php else : ?>
							<span class="g-mobile-home__nav-label">Systems</span>
						<?php endif; ?>
					</li>
					<li>
						<?php if ( $logged_in ) : ?>
							<a href="/general-ai/">General AI</a>
						<?php else : ?>
							<span class="g-mobile-home__nav-label">General AI</span>
						<?php endif; ?>
					</li>
					<li>
						<?php if ( $logged_in ) : ?>
							<a href="/community-discussions-mobile/">Community</a>
						<?php else : ?>
							<span class="g-mobile-home__nav-label">Community</span>
						<?php endif; ?>
					</li>
					<li>
						<?php if ( $logged_in ) : ?>
							<a href="/papers/">Papers</a>
						<?php else : ?>
							<span class="g-mobile-home__nav-label">Papers</span>
						<?php endif; ?>
					</li>					
				</ul>
			</nav>
			
			<p>
				Tychevia&reg; is our in-house exploratory AI framework: a collaborative dialogue between humans
				and digital proxies, designed to explore deeply, learn continuously and make sense of systems
				as they behave.
			</p>

			<p>
				This is a living space to think, to build, and to understand. Anybody who is curious and wants
				to explore systems is welcome to join us.
			</p>

		</section>

	</div>
</main>
