document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('[data-issue-studio-form]');
	const existingSelect = document.querySelector('[data-issue-studio-existing]');
	const classSelect = document.getElementById('sa-class');
	const issueSelect = document.getElementById('sa-issue');
	const lensesField = document.getElementById('sa-lenses-field');
	const perspectiveLabels = Array.from(
		document.querySelectorAll('[data-issue-studio-perspective]')
	);

	if (existingSelect) {
		existingSelect.addEventListener('change', function () {
			if (existingSelect.value) {
				window.location.href = existingSelect.value;
			}
		});
	}

	if (!form || !classSelect || !issueSelect) {
		return;
	}

	const params = new URLSearchParams(window.location.search);
	const chainArtefactId = params.get('chain_from_artefact_id') || '';
	const chainSnapshotId = params.get('chain_from_snapshot_id') || '';
	const isChainMode = form.getAttribute('data-chain-mode') === '1';

	function numericValue(value) {
		const parsed = parseInt(value || '', 10);
		return Number.isFinite(parsed) ? parsed : 0;
	}

	function resetPerspectives() {
		perspectiveLabels.forEach(function (label) {
			label.hidden = true;
			label.style.display = 'none';

			const checkbox = label.querySelector('input[type="checkbox"]');

			if (checkbox) {
				checkbox.checked = false;
			}
		});

		if (lensesField) {
			lensesField.hidden = true;
			lensesField.style.display = 'none';
		}
	}

	function filterIssues() {
		const selectedClassId = numericValue(classSelect.value);

		Array.from(issueSelect.options).forEach(function (option) {
			if (!option.value) {
				option.hidden = false;
				option.disabled = false;
				return;
			}

			const issueClassId = numericValue(option.getAttribute('data-class-id'));
			const matches = selectedClassId > 0 && issueClassId === selectedClassId;

			option.hidden = !matches;
			option.disabled = !matches;
		});

		issueSelect.value = '';
		resetPerspectives();
	}

	function showPerspectives() {
		const selectedClassId = numericValue(classSelect.value);
		const selectedIssueId = numericValue(issueSelect.value);

		resetPerspectives();

		if (!selectedClassId || !selectedIssueId) {
			return;
		}

		let visibleCount = 0;

		perspectiveLabels.forEach(function (label) {
			const labelClassId = numericValue(label.getAttribute('data-class-id'));

			if (labelClassId !== selectedClassId) {
				return;
			}

			label.hidden = false;
			label.style.display = '';
			visibleCount += 1;
		});

		if (lensesField && visibleCount > 0) {
			lensesField.hidden = false;
			lensesField.style.display = '';
		}
	}

	function selectedPerspectives() {
		return Array.from(
			form.querySelectorAll('input[name="perspectives[]"]:checked')
		)
			.map(function (checkbox) {
				return numericValue(checkbox.value);
			})
			.filter(function (value, index, values) {
				return value > 0 && values.indexOf(value) === index;
			});
	}

	function setWorkingState(message) {
		const button = form.querySelector('[data-sa-clicked-submit]') || form.querySelector('[data-sa-next-step]');

		if (button) {
			button.textContent = 'Working…';
			button.setAttribute('aria-disabled', 'true');
			button.style.cursor = 'progress';
			button.style.opacity = '0.75';
		}

		if (!form.querySelector('.glandore-working-note')) {
			const note = document.createElement('p');
			note.className = 'glandore-working-note';
			note.textContent = message;
			form.appendChild(note);
		}
	}

	classSelect.addEventListener('change', filterIssues);
	issueSelect.addEventListener('change', showPerspectives);

	form.addEventListener('click', function (event) {
		const button = event.target.closest('button[type="submit"]');

		if (!button || !form.contains(button)) {
			return;
		}

		form.querySelectorAll('[data-sa-clicked-submit]').forEach(function (existingButton) {
			delete existingButton.dataset.saClickedSubmit;
		});

		button.dataset.saClickedSubmit = '1';
	});

	resetPerspectives();
	filterIssues();

	form.addEventListener('submit', function (event) {
		if (!isChainMode) {
			setWorkingState('Issue Studio is creating the assessment. This may take a few moments.');
			return;
		}

		const classId = numericValue(classSelect.value);
		const issueId = numericValue(issueSelect.value);

		if (!classId || !issueId || !chainArtefactId || !chainSnapshotId) {
			return;
		}

		event.preventDefault();

		setWorkingState('Issue Studio is creating the chained assessment. This may take a few moments.');

		const url = new URL('/issue-input/', window.location.origin);

		url.searchParams.set('class_id', String(classId));
		url.searchParams.set('issue_id', String(issueId));
		url.searchParams.set('chain_from_artefact_id', chainArtefactId);
		url.searchParams.set('chain_from_snapshot_id', chainSnapshotId);

		selectedPerspectives().forEach(function (perspectiveId) {
			url.searchParams.append('perspectives[]', String(perspectiveId));
		});

		window.location.href = url.toString();
	});
});document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('[data-issue-studio-form]');
	const existingSelect = document.querySelector('[data-issue-studio-existing]');
	const classSelect = document.getElementById('sa-class');
	const issueSelect = document.getElementById('sa-issue');
	const lensesField = document.getElementById('sa-lenses-field');
	const perspectiveLabels = Array.from(
		document.querySelectorAll('[data-issue-studio-perspective]')
	);

	if (existingSelect) {
		existingSelect.addEventListener('change', function () {
			if (existingSelect.value) {
				window.location.href = existingSelect.value;
			}
		});
	}

	if (!form || !classSelect || !issueSelect) {
		return;
	}

	const params = new URLSearchParams(window.location.search);
	const chainArtefactId = params.get('chain_from_artefact_id') || '';
	const chainSnapshotId = params.get('chain_from_snapshot_id') || '';
	const isChainMode = form.getAttribute('data-chain-mode') === '1';

	function numericValue(value) {
		const parsed = parseInt(value || '', 10);
		return Number.isFinite(parsed) ? parsed : 0;
	}

	function resetPerspectives() {
		perspectiveLabels.forEach(function (label) {
			label.hidden = true;
			label.style.display = 'none';

			const checkbox = label.querySelector('input[type="checkbox"]');

			if (checkbox) {
				checkbox.checked = false;
			}
		});

		if (lensesField) {
			lensesField.hidden = true;
			lensesField.style.display = 'none';
		}
	}

	function filterIssues() {
		const selectedClassId = numericValue(classSelect.value);

		Array.from(issueSelect.options).forEach(function (option) {
			if (!option.value) {
				option.hidden = false;
				option.disabled = false;
				return;
			}

			const issueClassId = numericValue(option.getAttribute('data-class-id'));
			const matches = selectedClassId > 0 && issueClassId === selectedClassId;

			option.hidden = !matches;
			option.disabled = !matches;
		});

		issueSelect.value = '';
		resetPerspectives();
	}

	function showPerspectives() {
		const selectedClassId = numericValue(classSelect.value);
		const selectedIssueId = numericValue(issueSelect.value);

		resetPerspectives();

		if (!selectedClassId || !selectedIssueId) {
			return;
		}

		let visibleCount = 0;

		perspectiveLabels.forEach(function (label) {
			const labelClassId = numericValue(label.getAttribute('data-class-id'));

			if (labelClassId !== selectedClassId) {
				return;
			}

			label.hidden = false;
			label.style.display = '';
			visibleCount += 1;
		});

		if (lensesField && visibleCount > 0) {
			lensesField.hidden = false;
			lensesField.style.display = '';
		}
	}

	function selectedPerspectives() {
		return Array.from(
			form.querySelectorAll('input[name="perspectives[]"]:checked')
		)
			.map(function (checkbox) {
				return numericValue(checkbox.value);
			})
			.filter(function (value, index, values) {
				return value > 0 && values.indexOf(value) === index;
			});
	}

	function setWorkingState(message) {
		const button = form.querySelector('[data-sa-clicked-submit]') || form.querySelector('[data-sa-next-step]');

		if (button) {
			button.textContent = 'Working…';
			button.setAttribute('aria-disabled', 'true');
			button.style.cursor = 'progress';
			button.style.opacity = '0.75';
		}

		if (!form.querySelector('.glandore-working-note')) {
			const note = document.createElement('p');
			note.className = 'glandore-working-note';
			note.textContent = message;
			form.appendChild(note);
		}
	}

	classSelect.addEventListener('change', filterIssues);
	issueSelect.addEventListener('change', showPerspectives);

	form.addEventListener('click', function (event) {
		const button = event.target.closest('button[type="submit"]');

		if (!button || !form.contains(button)) {
			return;
		}

		form.querySelectorAll('[data-sa-clicked-submit]').forEach(function (existingButton) {
			delete existingButton.dataset.saClickedSubmit;
		});

		button.dataset.saClickedSubmit = '1';
	});

	resetPerspectives();
	filterIssues();

	form.addEventListener('submit', function (event) {
		if (!isChainMode) {
			setWorkingState('Issue Studio is creating the assessment. This may take a few moments.');
			return;
		}

		const classId = numericValue(classSelect.value);
		const issueId = numericValue(issueSelect.value);

		if (!classId || !issueId || !chainArtefactId || !chainSnapshotId) {
			return;
		}

		event.preventDefault();

		setWorkingState('Issue Studio is creating the chained assessment. This may take a few moments.');

		const url = new URL('/issue-input/', window.location.origin);

		url.searchParams.set('class_id', String(classId));
		url.searchParams.set('issue_id', String(issueId));
		url.searchParams.set('chain_from_artefact_id', chainArtefactId);
		url.searchParams.set('chain_from_snapshot_id', chainSnapshotId);

		selectedPerspectives().forEach(function (perspectiveId) {
			url.searchParams.append('perspectives[]', String(perspectiveId));
		});

		window.location.href = url.toString();
	});
});