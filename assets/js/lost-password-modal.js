/**
 * Glandore – Lost Password Modal
 *
 * Responsibilities:
 * - Open the "Lost your password?" modal when the user clicks
 *   the link in the Account dashboard / login UI.
 * - When the user clicks the X in that modal, send them to the home page.
 *
 * Mirrors the Registration Update modal pattern.
 */
(function () {
  'use strict';

  var MODAL_ID         = 'glandore-modal-lost-password';
  var MODAL_SELECTOR   = '#' + MODAL_ID;
  var TRIGGER_SELECTOR = '[data-auth-modal-open="lost-password"]';
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

  function openLostPasswordModal() {
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
    openLostPasswordModal();
  }

  // Global handler: X inside lost-password modal → go home.
  function handleXRedirect(e) {
    var x = e.target.closest(MODAL_SELECTOR + ' .auth-modal__close');
    if (!x) return;

    if (!e.target.closest(MODAL_SELECTOR)) return;

    e.preventDefault();
    if (e.stopImmediatePropagation) {
      e.stopImmediatePropagation();
    } else if (e.stopPropagation) {
      e.stopPropagation();
    }

    window.location.href = '/';
    // Or staging:
    // window.location.href = 'https://staging.glandore.com/';
  }

  document.addEventListener('DOMContentLoaded', function () {
    detachModalFromHiddenParent();

    // Open from Account dashboard / login.
    document.addEventListener('click', handleTriggerClick, false);

    // X → redirect home.
    document.addEventListener('click', handleXRedirect, true);
  });
})();
