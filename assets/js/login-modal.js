/**
 * Login modal controller
 *
 * Opens the front-page login modal (#login-modal-panel)
 * when the header link (#header-login-link) is clicked.
 *
 * NOTE:
 * - Modal markup lives in front-page.php
 * - Only loaded on front page
 */

document.addEventListener('DOMContentLoaded', function () {
	var loginLink = document.getElementById('header-login-link');
	var panel = document.getElementById('login-modal-panel');
	var overlay = document.getElementById('login-modal-overlay');
	var closeBtn = document.getElementById('login-modal-close');

	if (!loginLink || !panel || !overlay) {
		return;
	}

	function openModal() {
		overlay.hidden = false;
		panel.hidden = false;
		document.body.classList.add('login-modal-open');
	}

	function closeModal() {
		overlay.hidden = true;
		panel.hidden = true;
		document.body.classList.remove('login-modal-open');
	}

	loginLink.addEventListener('click', function (e) {
		e.preventDefault();
		openModal();
	});

	if (closeBtn) {
		closeBtn.addEventListener('click', function () {
			closeModal();
		});
	}

	overlay.addEventListener('click', function () {
		closeModal();
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeModal();
		}
	});
});