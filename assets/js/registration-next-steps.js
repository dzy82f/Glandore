(function () {
	'use strict';

	function isSuccess(el) {
		if (!el) return false;

		var text = (el.textContent || '').toLowerCase();

		return (
			text.includes('user registered') ||
			text.includes('verify your email') ||
			text.includes('registration successful')
		);
	}

	function scan() {
		var modal = document.getElementById('glandore-modal-registration');
		if (!modal || modal.dataset.glandoreRegistrationComplete === '1') return;

		var success = Array.from(
			modal.querySelectorAll('.ur-message, .user-registration-message, [class*="message"]')
		).find(isSuccess);

		if (!success) return;

		modal.dataset.glandoreRegistrationComplete = '1';

		var form = success.closest('form');

		var panel = document.createElement('section');
		panel.className = 'glandore-registration-next-steps';
		panel.innerHTML =
			'<h2>Thank you for registering</h2>' +
			'<p>Please check your email and confirm your registration.</p>' +
			'<p>Once your account has been approved, return to the Community of Practice and choose “Post in this discussion” or “New post” again. You’ll then be able to log in and complete the short induction before contributing.</p>';

		if (form && form.parentNode) {
			form.parentNode.insertBefore(panel, form.nextSibling);
			form.style.display = 'none';
		} else {
			modal.appendChild(panel);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		scan();

		new MutationObserver(scan).observe(document.body, {
			childList: true,
			subtree: true
		});
	});
})();