document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	const carousel = document.querySelector('.js-featured-carousel');

	if (!carousel) {
		return;
	}

	const slides = Array.from(carousel.querySelectorAll('.featured-carousel__slide'));
	const dots = Array.from(carousel.querySelectorAll('.featured-carousel__dot'));
	const prevButton = carousel.querySelector('.featured-carousel__nav--prev');
	const nextButton = carousel.querySelector('.featured-carousel__nav--next');
	const moreButtons = Array.from(carousel.querySelectorAll('.featured-carousel__more-btn'));

	if (!slides.length) {
		return;
	}

	let currentIndex = 0;
	let timerId = null;
	const delay = 7000;

	function resetSlideState(slide) {
		const excerpt = slide.querySelector('.featured-carousel__excerpt');
		const button = slide.querySelector('.featured-carousel__more-btn');

		if (excerpt) {
			excerpt.classList.add('is-collapsed');
		}

		if (button) {
			button.textContent = 'More';
			button.setAttribute('aria-expanded', 'false');
		}
	}

	function render(index) {
		slides.forEach(function (slide, i) {
			const active = i === index;

			slide.classList.toggle('is-active', active);
			slide.setAttribute('aria-hidden', active ? 'false' : 'true');

			if (!active) {
				resetSlideState(slide);
			}
		});

		dots.forEach(function (dot, i) {
			dot.classList.toggle('is-active', i === index);
		});

		currentIndex = index;
	}

	function next() {
		render((currentIndex + 1) % slides.length);
	}

	function prev() {
		render((currentIndex - 1 + slides.length) % slides.length);
	}

	function stop() {
		if (timerId) {
			window.clearInterval(timerId);
			timerId = null;
		}
	}

	function start() {
		if (slides.length <= 1) {
			return;
		}

		stop();
		timerId = window.setInterval(next, delay);
	}

	moreButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			const wrap = button.closest('.featured-carousel__excerpt-wrap');

			if (!wrap) {
				return;
			}

			const excerpt = wrap.querySelector('.featured-carousel__excerpt');

			if (!excerpt) {
				return;
			}

			const isCollapsed = excerpt.classList.contains('is-collapsed');

			if (isCollapsed) {
				excerpt.classList.remove('is-collapsed');
				button.textContent = 'Less';
				button.setAttribute('aria-expanded', 'true');
				stop();
			} else {
				excerpt.classList.add('is-collapsed');
				button.textContent = 'More';
				button.setAttribute('aria-expanded', 'false');
				start();
			}
		});
	});

	if (prevButton) {
		prevButton.addEventListener('click', function () {
			stop();
			prev();
			start();
		});
	}

	if (nextButton) {
		nextButton.addEventListener('click', function () {
			stop();
			next();
			start();
		});
	}

	carousel.addEventListener('mouseenter', stop);

	carousel.addEventListener('mouseleave', function () {
		const expandedExcerpt = carousel.querySelector('.featured-carousel__slide.is-active .featured-carousel__excerpt:not(.is-collapsed)');

		if (!expandedExcerpt) {
			start();
		}
	});

	render(0);
	start();
});
