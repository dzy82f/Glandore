(function () {
	'use strict';

	const form = document.getElementById('issue-lite-form');
	if (!form) return;

	const steps = Array.from(form.querySelectorAll('.issue-lite-step'));
	const dots = Array.from(form.querySelectorAll('.issue-lite-progress span'));
	const nextButton = form.querySelector('.issue-lite-next');
	const backButton = form.querySelector('.issue-lite-back');
	const submitButton = form.querySelector('.issue-lite-submit');
	const classSelect = document.getElementById('lite-class-id');
	const issueSelect = document.getElementById('lite-issue-id');
	const lensInputs = Array.from(form.querySelectorAll('input[name="perspectives[]"]'));

	let currentStep = 0;

	function showStep(index) {
		steps.forEach((step, i) => step.classList.toggle('is-active', i === index));
		dots.forEach((dot, i) => dot.classList.toggle('is-active', i <= index));

		backButton.hidden = index === 0;
		nextButton.hidden = index === steps.length - 1;
		submitButton.hidden = index !== steps.length - 1;
		currentStep = index;
	}

	function validateCurrentStep() {
		const fields = Array.from(steps[currentStep].querySelectorAll('select, textarea, input'));
		for (const field of fields) {
			if (field.required && !String(field.value || '').trim()) {
				field.focus();
				field.classList.add('issue-lite-error');
				return false;
			}
			field.classList.remove('issue-lite-error');
		}
		return true;
	}

	function filterIssues() {
		const classId = classSelect.value;
		const options = Array.from(issueSelect.options);

		issueSelect.value = '';

		options.forEach((option) => {
			if (!option.value) {
				option.hidden = false;
				return;
			}

			option.hidden = option.dataset.classId !== classId;
		});
	}

	function limitLenses(event) {
		const selected = lensInputs.filter((input) => input.checked);
		if (selected.length > 3) {
			event.target.checked = false;
		}
	}

	function syncHiddenFields() {
		const happening = form.querySelector('[data-lite-field="happening"]').value.trim();
		const matter = form.querySelector('[data-lite-field="matter"]').value.trim();
		const tried = form.querySelector('[data-lite-field="tried"]').value.trim();
		const difficult = form.querySelector('[data-lite-field="difficult"]').value.trim();

		document.getElementById('lite-issue-description').value =
			'Issue Studio Lite submission\n\nWhat is happening?\n' + happening + '\n\nWhy does it matter?\n' + matter;

		document.getElementById('lite-tried-hidden').value = tried || 'Not specified in the Lite reflection.';
		document.getElementById('lite-success-failure').value = difficult || 'Not specified in the Lite reflection.';
		document.getElementById('lite-success-looks-like').value =
			'A clearer understanding of what is happening, why it matters, and what may deserve deeper exploration in the full Issue Studio.';
		document.getElementById('lite-other-relevant').value =
			'Submitted through Issue Studio Lite, the lightweight mobile entry point into the full Issue Studio experience.';
	}

	classSelect.addEventListener('change', filterIssues);
	lensInputs.forEach((input) => input.addEventListener('change', limitLenses));

	nextButton.addEventListener('click', function () {
		if (!validateCurrentStep()) return;
		showStep(Math.min(currentStep + 1, steps.length - 1));
	});

	backButton.addEventListener('click', function () {
		showStep(Math.max(currentStep - 1, 0));
	});

	form.addEventListener('submit', function (event) {
		if (!validateCurrentStep()) {
			event.preventDefault();
			return;
		}

		syncHiddenFields();
		submitButton.disabled = true;
		submitButton.textContent = 'Analysing…';
	});

	filterIssues();
	showStep(0);
})();
