/* global GL_COP_POST, jQuery */
(function (window, document, $, undefined) {
  'use strict';

  function initCopPost() {
    var topicSelect  = document.getElementById('cop_topic');
    var threadField  = document.querySelector('.cop-field-thread');
    var threadSelect = document.getElementById('cop_thread');
    var newTopicWrap = document.querySelector('.cop-topic-new-wrap');

    if (!topicSelect) {
      return;
    }

    /**
     * Show/hide "New topic name" and Thread based on Topic selection.
     */
    function updateTopicThreadVisibility() {
      var isNewTopic = (topicSelect.value === 'new');

      // New topic name: only when Topic = new.
      if (newTopicWrap) {
        newTopicWrap.style.display = isNewTopic ? '' : 'none';
      }

      // Thread field: only when Topic is NOT new.
      if (threadField) {
        threadField.style.display = isNewTopic ? 'none' : '';
      }

      // Clear thread selection when switching to a new topic.
      if (isNewTopic && threadSelect) {
        threadSelect.value = '';
      }

      // If switching to an existing topic, refresh its threads list.
      if (!isNewTopic) {
        fetchThreadsForTopic(topicSelect.value);
      }
    }

    /**
     * Populate the Thread <select> with:
     *   - "— Select —"
     *   - "New thread in this topic…" (value = "new")
     *   - Threads returned from Ajax for current topic.
     */
    function populateThreadSelect(threads) {
      if (!threadSelect) {
        return;
      }

      // Clear all existing options.
      while (threadSelect.firstChild) {
        threadSelect.removeChild(threadSelect.firstChild);
      }

      // Placeholder.
      var optBlank = document.createElement('option');
      optBlank.value = '';
      optBlank.textContent = '\u2014 Select \u2014';
      threadSelect.appendChild(optBlank);

      // "New thread in this topic…" option.
      var optNew = document.createElement('option');
      optNew.value = 'new';
      optNew.textContent = 'New thread in this topic\u2026';
      threadSelect.appendChild(optNew);

      // Existing threads for this topic (if any).
      if (Array.isArray(threads)) {
        threads.forEach(function (t) {
          if (!t || !t.id) {
            return;
          }
          var opt = document.createElement('option');
          opt.value = String(t.id);
          opt.textContent = t.title || ('Thread #' + t.id);
          threadSelect.appendChild(opt);
        });
      }
    }

    /**
     * Fetch existing threads for a given topic via Ajax.
     * This is defensive: if GL_COP_POST or Ajax is not set up,
     * we simply leave the Thread select with only the "new" option.
     */
    function fetchThreadsForTopic(topicValue) {
      if (!threadSelect || !topicValue || topicValue === 'new') {
        return;
      }

      if (typeof GL_COP_POST === 'undefined' || !GL_COP_POST.ajaxUrl || !GL_COP_POST.getThreadsAction) {
        // Ajax infrastructure not wired yet – keep default options.
        return;
      }

      $.ajax({
        url: GL_COP_POST.ajaxUrl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: GL_COP_POST.getThreadsAction,
          _ajax_nonce: GL_COP_POST.nonce || '',
          topic: topicValue
        }
      }).done(function (response) {
        if (!response || response.success === false) {
          // Do not break the UI on failure – user can still create a new thread.
          return;
        }

        // Expect response.threads = [{id: 123, title: "Some title"}, ...]
        populateThreadSelect(response.threads || []);
      }).fail(function () {
        // On error, leave Thread select as is (only "new" option).
      });
    }

    // Initialise.
    updateTopicThreadVisibility();

    // React to changes.
    topicSelect.addEventListener('change', updateTopicThreadVisibility);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCopPost);
  } else {
    initCopPost();
  }

})(window, document, window.jQuery);
