document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll(
		'.glandore-chain-inline-help, .glandore-chain-result-help'
	).forEach(function (el) {
		el.remove();
	});

	const chainButton = Array.from(document.querySelectorAll('a, button')).find(function (el) {
		return (el.textContent || el.value || '').trim().toLowerCase() === 'chain';
	});

	if (!chainButton || document.querySelector('.glandore-chain-help-button')) {
		return;
	}

	const helpButton = document.createElement('button');
	helpButton.type = 'button';
	helpButton.className = 'glandore-chain-help-button';
	helpButton.textContent = '?';
	helpButton.setAttribute('aria-label', 'Explain Chain');

	const modal = document.createElement('div');
	modal.className = 'glandore-chain-modal';
	modal.setAttribute('hidden', '');

	modal.innerHTML = `
		<div class="glandore-chain-modal-panel" role="dialog" aria-modal="true" aria-labelledby="glandore-chain-modal-title">
			<h3 id="glandore-chain-modal-title">What Chain does</h3>
			<p>Chain creates a new assessment using the selected Domain, Issue and Perspectives, while preserving the previous assessment as historical context.</p>
			<button type="button" class="glandore-chain-modal-close">Close</button>
		</div>
	`;

	chainButton.insertAdjacentElement('afterend', helpButton);
	document.body.appendChild(modal);

	function openModal() {
		modal.removeAttribute('hidden');
		document.body.style.overflow = 'hidden';

		const closeButton = modal.querySelector('.glandore-chain-modal-close');
		if (closeButton) {
			closeButton.focus();
		}
	}

	function closeModal() {
		modal.setAttribute('hidden', '');
		document.body.style.overflow = '';
		helpButton.focus();
	}

	helpButton.addEventListener('click', openModal);

	modal.addEventListener('click', function (event) {
		if (
			event.target === modal ||
			event.target.classList.contains('glandore-chain-modal-close')
		) {
			closeModal();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && !modal.hasAttribute('hidden')) {
			closeModal();
		}
	});
});