/**
 * Glandore – Registration Update Modal
 *
 * Responsibilities:
 * - Open the "Update your registration data" modal when the user clicks
 *   the link in the Account dashboard.
 * - When the user clicks the X in that modal, send them to the home page.
 *
 * Does NOT interfere with backdrop close logic or any other modals.
 */
(function () {
  'use strict';

  var MODAL_ID         = 'glandore-modal-registration-update';
  var MODAL_SELECTOR   = '#' + MODAL_ID;
  var TRIGGER_SELECTOR = '[data-auth-modal-open="registration-update"]';
  var ACCOUNT_MODAL_ID = 'glandore-account-modal';

  function getModal() {
    return document.getElementById(MODAL_ID);
  }

  function detachModalFromHiddenParent() {
    var modal = getModal();
    if (!modal) return;

    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
  }

  function closeAccountModal() {
    var acc = document.getElementById(ACCOUNT_MODAL_ID);
    if (!acc) return;

    if (acc.contains(document.activeElement)) {
      try {
        document.activeElement.blur();
      } catch (e) {}
    }

    acc.hidden = true;
    acc.setAttribute('aria-hidden', 'true');
    acc.classList.remove('is-open');
    document.documentElement.classList.remove('account-modal-open');
  }

  function openUpdateModal() {
    detachModalFromHiddenParent();

    var modal = getModal();
    if (!modal) return;

    // Close the Account dashboard behind it.
    closeAccountModal();

    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('is-open');

    if (document.body) {
      document.body.classList.add('has-auth-modal-open');
    }
  }

  function handleTriggerClick(e) {
    var trigger = e.target.closest(TRIGGER_SELECTOR);
    if (!trigger) return;

    e.preventDefault();
    openUpdateModal();
  }

  // Global handler: X inside registration-update modal → go home.
  function handleXRedirect(e) {
    var x = e.target.closest(
      MODAL_SELECTOR + ' .auth-modal__close'
    );
    if (!x) return;

    // Only act if we're really inside the registration-update modal.
    if (!e.target.closest(MODAL_SELECTOR)) return;

    e.preventDefault();
    if (e.stopImmediatePropagation) {
      e.stopImmediatePropagation();
    } else if (e.stopPropagation) {
      e.stopPropagation();
    }

    // Send user to the home page.
    window.location.href = '/';
    // Or hard-code staging if you ever want:
    // window.location.href = 'https://staging.glandore.com/';
  }

  document.addEventListener('DOMContentLoaded', function () {
    detachModalFromHiddenParent();

    // Open from Account dashboard.
    document.addEventListener('click', handleTriggerClick, false);

    // X → redirect home (capture so we win before anything else).
    document.addEventListener('click', handleXRedirect, true);
  });
})();
