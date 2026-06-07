/**
 * Glandore – Registration Completion Modal
 *
 * Opens the completion modal when:
 *   - URL contains ?registration_continue=1
 *     OR
 *   - PHP has set window.GlandoreShowCompletionModal = true
 *     (for logged-in users with the one-time meta flag).
 */

(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  ready(function () {
    var modal = document.getElementById('glandore-modal-registration-completion');
    if (!modal) return;

    var dialog       = modal.querySelector('.auth-modal__dialog');
    var closeButtons = modal.querySelectorAll('[data-auth-modal-close]');
    var body         = document.body;

    // Decide whether we should open the completion modal on this page.
    var params        = new URLSearchParams(window.location.search);
    var fromQuery     = (params.get('registration_continue') === '1');
    var fromJsFlag    = !!window.GlandoreShowCompletionModal;

    if (!fromQuery && !fromJsFlag) {
      // Neither condition met – do nothing on this page.
      return;
    }

    function openModal() {
      // Force it visible even if base CSS has display:none
      modal.style.display = 'flex';
      modal.removeAttribute('hidden');
      modal.setAttribute('aria-hidden', 'false');
      body.classList.add('has-auth-modal-open');
    }

    function closeModal() {
      if (modal.hasAttribute('hidden')) return;
      modal.setAttribute('hidden', 'hidden');
      modal.setAttribute('aria-hidden', 'true');
      modal.style.display = '';
      body.classList.remove('has-auth-modal-open');
    }

    // Open immediately based on the conditions above.
    openModal();

    // Close via close buttons
    closeButtons.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        closeModal();
      });
    });

    // Close on overlay click (outside dialog)
    modal.addEventListener('click', function (event) {
      if (!dialog) return;
      if (!dialog.contains(event.target)) {
        closeModal();
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' || event.key === 'Esc') {
        closeModal();
      }
    });
  });
})();
