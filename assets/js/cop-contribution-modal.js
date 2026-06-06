(function () {
  'use strict';

  var modal = document.getElementById('glandore-modal-contribution');
  if (!modal) return;

  var body = document.body;
  var canopySelect = modal.querySelector('#cop_canopy_id');
  var threadSelect = modal.querySelector('#cop_thread_id');
  var threadTitleWrapper = modal.querySelector('#cop_thread_title_wrapper');

  function openModal(context) {
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('is-open');
    body.classList.add('glandore-modal-open');

    // Pre-select canopy/thread if provided.
    if (context && context.canopyId && canopySelect) {
      canopySelect.value = String(context.canopyId);
      filterThreadsByCanopy();
    }

    if (context && context.threadId && threadSelect) {
      threadSelect.value = String(context.threadId);
      handleThreadSelectChange();
    }
  }

  function closeModal() {
    modal.setAttribute('aria-hidden', 'true');
    modal.classList.remove('is-open');
    body.classList.remove('glandore-modal-open');
  }

  function filterThreadsByCanopy() {
    if (!canopySelect || !threadSelect) return;

    var canopyId = canopySelect.value;
    var options = threadSelect.querySelectorAll('option');

    options.forEach(function (opt) {
      if (opt.value === 'new') {
        opt.hidden = false;
        return;
      }

      var optCanopyId = opt.getAttribute('data-cop-canopy-id');

      if (!canopyId || !optCanopyId) {
        opt.hidden = false;
      } else {
        opt.hidden = optCanopyId !== canopyId;
      }
    });

    // Default to "new" when canopy changes.
    threadSelect.value = 'new';
    handleThreadSelectChange();
  }

  function handleThreadSelectChange() {
    if (!threadSelect || !threadTitleWrapper) return;

    if (threadSelect.value === 'new') {
      threadTitleWrapper.style.display = '';
    } else {
      threadTitleWrapper.style.display = 'none';
    }
  }

  // Global click handler for open/close triggers.
  document.addEventListener('click', function (e) {
    var openTrigger = e.target.closest('[data-glandore-open-contribution]');
    if (openTrigger) {
      e.preventDefault();

      var canopyId = openTrigger.getAttribute('data-cop-canopy-id') || '';
      var threadId = openTrigger.getAttribute('data-cop-thread-id') || '';

      openModal({
        canopyId: canopyId,
        threadId: threadId
      });
      return;
    }

    var closeTrigger = e.target.closest('[data-glandore-modal-close]');
    if (closeTrigger && modal.contains(closeTrigger)) {
      e.preventDefault();
      closeModal();
    }
  });

  // ESC closes modal.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });

  // Field change handlers.
  if (canopySelect) {
    canopySelect.addEventListener('change', filterThreadsByCanopy);
  }
  if (threadSelect) {
    threadSelect.addEventListener('change', handleThreadSelectChange);
    handleThreadSelectChange();
  }
})();
