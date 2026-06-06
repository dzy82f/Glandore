(function () {
  function openModalById(id) {
    var modal = document.getElementById(id);
    if (!modal) {
      console.warn('Auth modal not found:', id);
      return;
    }

    // Show modal
    modal.removeAttribute('hidden');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');

    if (!document.body.classList.contains('has-auth-modal-open')) {
      document.body.classList.add('has-auth-modal-open');
    }
  }

  function initFromQuery() {
    try {
      var params = new URLSearchParams(window.location.search);
      if (params.get('registration_modal') === '1') {
        openModalById('glandore-modal-registration');
      }
    } catch (err) {
      console.error('Glandore registration modal – query init failed', err);
    }
  }

  function initClickTriggers() {
    var triggers = document.querySelectorAll('[data-open-registration-modal]');
    if (!triggers.length) {
      return;
    }

    triggers.forEach(function (el) {
      el.addEventListener('click', function (ev) {
        ev.preventDefault();
        openModalById('glandore-modal-registration');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFromQuery();
    initClickTriggers();
  });
})();
