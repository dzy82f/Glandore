(function () {
	'use strict';

	var STORAGE_KEY = 'glandore_privacy_choice_v1';
	var COOKIE_NAME = 'glandore_privacy_choice';

	function getBanner() {
		return document.getElementById('glandore-privacy-banner');
	}

	function hasChoice() {
		return localStorage.getItem(STORAGE_KEY) !== null;
	}

	function writeChoice(choice) {
		var data = {
			essential: true,
			functional: choice === 'all',
			analytics: choice === 'all',
			choice: choice,
			savedAt: new Date().toISOString()
		};

		var json = JSON.stringify(data);

		localStorage.setItem(STORAGE_KEY, json);

		document.cookie =
			COOKIE_NAME + '=' + encodeURIComponent(json) +
			'; path=/; max-age=' + (180 * 24 * 60 * 60) +
			'; SameSite=Lax';

		hideBanner();
	}

	function hideBanner() {
		var banner = getBanner();
		if (banner) {
			banner.style.display = 'none';
		}
	}

	function showBanner() {
		var banner = getBanner();
		if (banner) {
			banner.style.display = 'block';
		}
	}

	function openModal(event) {
		event.preventDefault();

		var modal = document.getElementById('modal-cookies');
		if (modal) {
			modal.removeAttribute('hidden');
		}
	}

	function handleClick(event) {
		var button = event.target.closest('[data-privacy-choice]');
		if (!button) {
			return;
		}

		var choice = button.getAttribute('data-privacy-choice');

		if (choice === 'essential') {
			writeChoice('essential');
			return;
		}

		if (choice === 'all') {
			writeChoice('all');
			return;
		}

		if (choice === 'manage') {
			openModal(event);
		}
	}

	function init() {
		document.addEventListener('click', handleClick);

		if (hasChoice()) {
			hideBanner();
		} else {
			showBanner();
		}
	}

	document.addEventListener('DOMContentLoaded', init);
})();