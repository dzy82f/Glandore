document.addEventListener('DOMContentLoaded', function () {
	const config = window.glandoreProblemCards || {
		ajax_url: '/wp-admin/admin-ajax.php'
	};

	const cards = document.querySelectorAll('.g-problem-card');

	cards.forEach(function (card) {
		const form = card.querySelector('.g-problem-card__form');
		const frontDoor = card.querySelector('[data-problem-card-front-door]');
		const reflection = card.querySelector('[data-problem-card-reflection]');
		const startButton = card.querySelector('[data-problem-card-start]');
		const existingInstanceSelect = card.querySelector('select[name="existing_instance_id"]');
		const titleInput = card.querySelector('input[name="instance_title"]');
		const descriptionInput = card.querySelector('textarea[name="instance_description"]');

		const buttons = card.querySelectorAll('[data-problem-card-step-button]');
		const steps = card.querySelectorAll('[data-problem-card-step]');

		function activateStep(stepKey) {
			buttons.forEach(function (button) {
				button.classList.toggle(
					'is-active',
					button.getAttribute('data-problem-card-step-button') === stepKey
				);
			});

			steps.forEach(function (step) {
				step.classList.toggle(
					'is-active',
					step.getAttribute('data-problem-card-step') === stepKey
				);
			});
		}

		function getFrontDoorStatus() {
			return frontDoor ? frontDoor.querySelector('.g-problem-card__status') : null;
		}

		if (existingInstanceSelect) {
			existingInstanceSelect.addEventListener('change', function () {
				const usingExisting = existingInstanceSelect.value !== '';

				if (titleInput) {
					titleInput.disabled = usingExisting;
				}

				if (descriptionInput) {
					descriptionInput.disabled = usingExisting;
				}
			});
		}

		if (startButton) {
			startButton.addEventListener('click', function () {
				const status = getFrontDoorStatus();
				const usingExisting = existingInstanceSelect && existingInstanceSelect.value !== '';
				const title = titleInput ? titleInput.value.trim() : '';

				if (!usingExisting && title === '') {
					if (status) {
						status.textContent = 'Please add a title, or choose an existing problem.';
					}

					if (titleInput) {
						titleInput.focus();
					}

					return;
				}

				if (frontDoor) {
					frontDoor.hidden = true;
				}

				if (reflection) {
					reflection.hidden = false;
				}

				if (buttons.length > 0) {
					activateStep(buttons[0].getAttribute('data-problem-card-step-button'));
				}

				card.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		}

		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				activateStep(button.getAttribute('data-problem-card-step-button'));
			});
		});

		card.querySelectorAll('[data-problem-card-next]').forEach(function (nextButton) {
			nextButton.addEventListener('click', function () {
				const activeButton = card.querySelector('.g-problem-card__progress-button.is-active');

				if (!activeButton) {
					return;
				}

				const currentIndex = Array.prototype.indexOf.call(buttons, activeButton);
				const next = buttons[currentIndex + 1];

				if (next) {
					activateStep(next.getAttribute('data-problem-card-step-button'));
					card.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		});

		card.addEventListener('click', function (event) {
			const saveButton = event.target.closest('.g-problem-card__save');

			if (!saveButton) {
				return;
			}

			event.preventDefault();

			const activeStep = saveButton.closest('.g-problem-card__step');
			const status = activeStep ? activeStep.querySelector('.g-problem-card__status') : null;
			const savedMessage = activeStep ? activeStep.querySelector('.g-problem-card__saved-message') : null;
			const snapshot = activeStep ? activeStep.querySelector('.g-problem-card__snapshot') : null;
			const snapshotBody = snapshot ? snapshot.querySelector('.g-problem-card__snapshot-body') : null;

			saveButton.disabled = true;

			if (status) {
				status.textContent = 'Saving and generating reflection…';
			}

			const data = new FormData(form);
			data.append('action', 'glandore_problem_card_save_completed');
			data.append('card_slug', card.dataset.problemCard);

			fetch(config.ajax_url, {
				method: 'POST',
				body: data
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (res) {
					saveButton.disabled = false;

					if (res.success) {
						if (status) {
							status.textContent = 'Completed reflection saved.';
						}

						if (savedMessage) {
							savedMessage.hidden = false;
						}

						if (snapshot && snapshotBody && res.data.snapshot_html) {
							snapshotBody.innerHTML = res.data.snapshot_html;
							snapshot.hidden = false;
						}
					} else {
						if (status) {
							status.textContent = res.data && res.data.message ? res.data.message : 'Save failed.';
						}
					}
				})
				.catch(function () {
					saveButton.disabled = false;

					if (status) {
						status.textContent = 'Error saving.';
					}
				});
		});
	});
});