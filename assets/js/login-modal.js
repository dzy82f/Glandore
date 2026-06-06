/**
 * Login modal controller.
 *
 * Opens the front-page login modal when present.
 * If the modal is not present, follows the link to /?login_modal=1.
 */

(function () {
	'use strict';

	function getModalParts() {
		return {
			panel: document.getElementById('login-modal-panel'),
			overlay: document.getElementById('login-modal-overlay'),
			closeBtn: document.getElementById('login-modal-close')
		};
	}

	function hasLoginModal() {
		var parts = getModalParts();

		return !!(parts.panel && parts.overlay);
	}

	function openLoginModal() {
		var parts = getModalParts();

		if (!parts.panel || !parts.overlay) {
			return false;
		}

		parts.panel.hidden = false;
		parts.overlay.hidden = false;
		document.body.classList.add('login-modal-open');

		return true;
	}

	function closeLoginModal() {
		var parts = getModalParts();

		if (parts.panel) {
			parts.panel.hidden = true;
		}

		if (parts.overlay) {
			parts.overlay.hidden = true;
		}

		document.body.classList.remove('login-modal-open');
	}

	function shouldAutoOpenFromUrl() {
		var params;

		try {
			params = new URLSearchParams(window.location.search);
			return params.get('login_modal') === '1';
		} catch (error) {
			return window.location.search.indexOf('login_modal=1') !== -1;
		}
	}

	function cleanLoginModalUrl() {
		var url;

		try {
			url = new URL(window.location.href);
			url.searchParams.delete('login_modal');
			window.history.replaceState({}, document.title, url.toString());
		} catch (error) {
			// Leave URL unchanged if history/url handling is unavailable.
		}
	}

	function bindOpenTriggers() {
		document.addEventListener('click', function (event) {
			var trigger = event.target.closest('[data-login-modal-open="1"], #header-login-link');

			if (!trigger) {
				return;
			}

			if (!hasLoginModal()) {
				return;
			}

			event.preventDefault();
			openLoginModal();
		});
	}

	function bindCloseTriggers() {
		var parts = getModalParts();

		if (parts.closeBtn) {
			parts.closeBtn.addEventListener('click', closeLoginModal);
		}

		if (parts.overlay) {
			parts.overlay.addEventListener('click', closeLoginModal);
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeLoginModal();
			}
		});
	}

	function initialiseLoginModal() {
		bindOpenTriggers();
		bindCloseTriggers();

		if (shouldAutoOpenFromUrl() && hasLoginModal()) {
			openLoginModal();
			cleanLoginModalUrl();
		}
	}

	document.addEventListener('DOMContentLoaded', initialiseLoginModal);
})();