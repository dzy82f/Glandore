<?php
/**
 * Cookie Preferences Modal
 * Public, works whether logged in or not.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div
	id="modal-cookies"
	class="glandore-modal"
	role="dialog"
	aria-modal="true"
	aria-labelledby="modal-cookies-title"
	aria-hidden="true"
	hidden
>
	<div class="glandore-modal__overlay" data-modal-close></div>

	<div class="glandore-modal__panel" role="document">
		<header class="glandore-modal__header">
			<h3 id="modal-cookies-title">Cookie preferences</h3>

			<button
				type="button"
				class="glandore-modal__close"
				aria-label="Close cookie preferences"
				data-modal-close
			>
				×
			</button>
		</header>

		<div class="glandore-modal__body">
			<p>
				Essential cookies are always on because they are needed for login,
				security and basic site operation.
			</p>

			<div class="glandore-cookie-toggle">
				<label>
					<input type="checkbox" checked disabled>
					<span>
						<strong>Essential cookies</strong>
						<small>Always on.</small>
					</span>
				</label>
			</div>

			<div class="glandore-cookie-toggle">
				<label>
					<input type="checkbox" id="glandore-functional-checkbox">
					<span>
						<strong>Functional cookies</strong>
						<small>Remember choices and improve the site experience.</small>
					</span>
				</label>
			</div>

			<div class="glandore-cookie-toggle">
				<label>
					<input type="checkbox" id="glandore-analytics-checkbox">
					<span>
						<strong>Analytical cookies</strong>
						<small>Help us understand how the site is used.</small>
					</span>
				</label>
			</div>
		</div>

		<footer class="glandore-modal__footer">
			<button type="button" id="glandore-cookie-save-preferences">
				Save preferences
			</button>

			<button type="button" data-modal-close>
				Close
			</button>
		</footer>
	</div>
</div>