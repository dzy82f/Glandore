<?php
/**
 * Glandore – Cookie consent bar
 *
 * Self-contained consent bar.
 * Stores preferences in localStorage and cookie.
 * Reloads saved preferences into the cookie preferences modal.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_cookie_consent_markup() {
	?>
	<div id="glandore-consent-bar-v4">
		<div class="glandore-consent-bar-v4__inner">
			<p>
				Glandore uses essential cookies to run the site. Optional cookies help improve the experience and understand how the site is used.
			</p>

			<div class="glandore-consent-bar-v4__actions">
				<button type="button" id="glandore-consent-manage-v4">
					Cookie preferences
				</button>

				<button type="button" id="glandore-consent-all-v4">
					Accept all
				</button>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template_parts/modals/modal-cookies' ); ?>

	<style>
		#glandore-consent-bar-v4 {
			position: fixed;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 999999;
			display: none;
			background: #fff;
			border-top: 1px solid rgba(0, 51, 120, 0.2);
			box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.14);
		}

		.glandore-consent-bar-v4__inner {
			max-width: var(--layout-max-width, 1180px);
			margin: 0 auto;
			padding: 0.7rem 1rem;
			display: flex;
			align-items: center;
			gap: 1rem;
		}

		.glandore-consent-bar-v4__inner p {
			margin: 0;
			flex: 1;
			font-size: 0.9rem;
			line-height: 1.35;
		}

		.glandore-consent-bar-v4__actions {
			display: flex;
			gap: 0.5rem;
			flex-shrink: 0;
		}

		.glandore-consent-bar-v4__actions button {
			border: 1px solid var(--tychevia-blue, #0057cc);
			background: #fff;
			color: var(--tychevia-blue, #0057cc);
			padding: 0.45rem 0.7rem;
			font: inherit;
			font-size: 0.85rem;
			font-weight: 700;
			cursor: pointer;
			white-space: nowrap;
		}

		.glandore-consent-bar-v4__actions button:last-child {
			background: var(--tychevia-blue, #0057cc);
			color: #fff;
		}

		@media (max-width: 760px) {
			.glandore-consent-bar-v4__inner {
				display: block;
			}

			.glandore-consent-bar-v4__inner p {
				margin-bottom: 0.6rem;
			}

			.glandore-consent-bar-v4__actions {
				display: grid;
				grid-template-columns: 1fr;
			}
		}
	</style>

	<script>
	(function () {
		'use strict';

		var STORAGE_KEY = 'glandore_consent_v4';
		var COOKIE_NAME = 'glandore_consent_v4';

		var bar = document.getElementById('glandore-consent-bar-v4');
		var modal = document.getElementById('modal-cookies');

		var manageButton = document.getElementById('glandore-consent-manage-v4');
		var acceptAllButton = document.getElementById('glandore-consent-all-v4');

		var functionalInput = document.getElementById('glandore-functional-checkbox');
		var analyticsInput = document.getElementById('glandore-analytics-checkbox');
		var savePreferencesButton = document.getElementById('glandore-cookie-save-preferences');

		if (!bar) {
			return;
		}

		function showBar() {
			bar.style.display = 'block';
		}

		function hideBar() {
			bar.style.display = 'none';
		}

		function writeConsent(data) {
			var json = JSON.stringify(data);

			try {
				localStorage.setItem(STORAGE_KEY, json);
			} catch (e) {}

			document.cookie =
				COOKIE_NAME + '=' + encodeURIComponent(json) +
				'; path=/; max-age=' + (180 * 24 * 60 * 60) +
				'; SameSite=Lax';

			hideBar();
		}

		function saveChoice(choice) {
			var data = {
				choice: choice,
				essential: true,
				functional: choice === 'all',
				analytics: choice === 'all',
				savedAt: new Date().toISOString()
			};

			writeConsent(data);
			loadSavedPreferences();
		}

		function saveCustomPreferences() {
			var data = {
				choice: 'custom',
				essential: true,
				functional: functionalInput ? !!functionalInput.checked : false,
				analytics: analyticsInput ? !!analyticsInput.checked : false,
				savedAt: new Date().toISOString()
			};

			writeConsent(data);
			closePreferences();
		}

		function getSavedPreferences() {
			var saved = null;

			try {
				saved = localStorage.getItem(STORAGE_KEY);
			} catch (e) {}

			if (!saved) {
				return null;
			}

			try {
				return JSON.parse(saved);
			} catch (e) {
				return null;
			}
		}

		function loadSavedPreferences() {
			var data = getSavedPreferences();

			if (!data) {
				if (functionalInput) {
					functionalInput.checked = false;
				}

				if (analyticsInput) {
					analyticsInput.checked = false;
				}

				return;
			}

			if (functionalInput) {
				functionalInput.checked = !!data.functional;
			}

			if (analyticsInput) {
				analyticsInput.checked = !!data.analytics;
			}
		}

		function openPreferences() {
			loadSavedPreferences();

			if (modal) {
				modal.removeAttribute('hidden');
				modal.setAttribute('aria-hidden', 'false');
			}
		}

		function closePreferences() {
			if (modal) {
				modal.setAttribute('hidden', 'hidden');
				modal.setAttribute('aria-hidden', 'true');
			}
		}

		function bindModalClosers() {
			if (!modal) {
				return;
			}

			modal.addEventListener('click', function (event) {
				var target = event.target;

				if (!target) {
					return;
				}

				if (target.closest('[data-modal-close]')) {
					event.preventDefault();
					closePreferences();
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key !== 'Escape') {
					return;
				}

				if (!modal.hasAttribute('hidden')) {
					closePreferences();
				}
			});
		}

		loadSavedPreferences();

		if (!getSavedPreferences()) {
			showBar();
		}

		if (acceptAllButton) {
			acceptAllButton.addEventListener('click', function () {
				saveChoice('all');
			});
		}

		if (manageButton) {
			manageButton.addEventListener('click', function () {
				openPreferences();
			});
		}

		if (savePreferencesButton) {
			savePreferencesButton.addEventListener('click', function () {
				saveCustomPreferences();
			});
		}

		bindModalClosers();
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'glandore_cookie_consent_markup' );