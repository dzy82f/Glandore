/* global window, document */
(function () {
	'use strict';

	function log(stage, extra) {
		if (!window.console || !console.log) { return; }
		var payload = extra || {};
		payload.stage = stage;
		console.log('[GACC_JS]', payload);
	}

	function getModal() {
		return document.getElementById('glandore-account-modal');
	}

	function isOpen() {
		var modal = getModal();
		return !!(modal && modal.hidden === false);
	}

	function openModal() {
		var modal = getModal();
		if (!modal) {
			log('open_no_modal');
			return;
		}

		modal.hidden = false;
		modal.removeAttribute('aria-hidden');
		document.documentElement.classList.add('account-modal-open');
		log('open_modal');
	}

	function closeModal() {
		var modal = getModal();
		if (!modal) {
			log('close_no_modal');
			return;
		}

		modal.hidden = true;
		modal.setAttribute('aria-hidden', 'true');
		document.documentElement.classList.remove('account-modal-open');
		log('close_modal');
	}

	function toggleModal() {
		if (isOpen()) {
			log('toggle_to_close');
			closeModal();
		} else {
			log('toggle_to_open');
			openModal();
		}
	}

	function bindOpeners() {
		var openers = document.querySelectorAll('[data-account-open-modal="true"]');

		if (!openers.length) {
			log('bind_openers_none');
			return;
		}

		log('bind_openers', { count: openers.length });

		for (var i = 0; i < openers.length; i++) {
			openers[i].addEventListener('click', function (e) {
				e.preventDefault();
				log('account_link_click', {
					tag: this.tagName,
					href: this.getAttribute('href')
				});
				toggleModal();
			});
		}
	}

	function bindClosers() {
		document.addEventListener('click', function (e) {
			var target = e.target;
			if (!target) { return; }

			// Close on backdrop or close button
			if (target.closest('[data-account-modal-close="1"]')) {
				e.preventDefault();
				log('close_click');
				closeModal();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') { return; }
			if (!isOpen()) { return; }
			log('close_escape');
			e.preventDefault();
			closeModal();
		});

		log('bind_closers_done');
	}

	function autoOpenFromQuery() {
		var params = new URLSearchParams(window.location.search);
		if (params.get('account_modal') === '1' || params.get('cop_member_login') === '1') {
			log('auto_open_query');
			openModal();
		} else {
			log('auto_open_skip');
		}
	}

	function init() {
		var modal = getModal();
		if (!modal) {
			log('init_no_modal');
			return;
		}

		log('init_start', { readyState: document.readyState });

		bindOpeners();
		bindClosers();
		autoOpenFromQuery();

		log('init_complete');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
