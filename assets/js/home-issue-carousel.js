document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	const carousel = document.querySelector('.js-issue-carousel');

	if (!carousel) {
		return;
	}

	const slides = Array.from(carousel.querySelectorAll('.issue-carousel__slide'));
	const dots = Array.from(carousel.querySelectorAll('.issue-carousel__dot'));
	const prev = carousel.querySelector('.issue-carousel__nav--prev');
	const next = carousel.querySelector('.issue-carousel__nav--next');
	const moreButtons = Array.from(carousel.querySelectorAll('.issue-carousel__more'));

	let currentIndex = 0;

	function reset(slide) {
		const excerpt = slide.querySelector('.issue-carousel__excerpt');
		const more = slide.querySelector('.issue-carousel__more');

		if (excerpt) {
			excerpt.classList.add('is-collapsed');
		}

		if (more) {
			more.textContent = 'More';
			more.setAttribute('aria-expanded', 'false');
		}
	}

	function render(index) {
		slides.forEach(function (slide, i) {
			const active = i === index;

			slide.classList.toggle('is-active', active);
			slide.setAttribute('aria-hidden', active ? 'false' : 'true');

			if (!active) {
				reset(slide);
			}
		});

		dots.forEach(function (dot, i) {
			dot.classList.toggle('is-active', i === index);
		});

		currentIndex = index;
	}

	if (prev) {
		prev.addEventListener('click', function () {
			render((currentIndex - 1 + slides.length) % slides.length);
		});
	}

	if (next) {
		next.addEventListener('click', function () {
			render((currentIndex + 1) % slides.length);
		});
	}

	moreButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			const slide = button.closest('.issue-carousel__slide');
			const excerpt = slide ? slide.querySelector('.issue-carousel__excerpt') : null;

			if (!excerpt) {
				return;
			}

			const collapsed = excerpt.classList.contains('is-collapsed');

			excerpt.classList.toggle('is-collapsed', !collapsed);
			button.textContent = collapsed ? 'Less' : 'More';
			button.setAttribute('aria-expanded', collapsed ? 'true' : 'false');
		});
	});

	render(0);
});
