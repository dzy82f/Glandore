(function () {
    'use strict';

    function getEditorContent() {
        // Prefer TinyMCE if active.
        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get('cop_bio_review');
            if (editor && !editor.isHidden()) {
                return editor.getContent();
            }
        }

        // Fallback to the textarea.
        var textarea = document.getElementById('cop_bio_review');
        return textarea ? textarea.value : '';
    }

    function setEditorContent(html) {
        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get('cop_bio_review');
            if (editor) {
                editor.setContent(html || '');
                return;
            }
        }

        var textarea = document.getElementById('cop_bio_review');
        if (textarea) {
            textarea.value = html || '';
        }
    }

    function setMessage(text, type) {
        var el = document.getElementById('cop-bio-message');
        if (!el) {
            return;
        }

        el.textContent = text || '';

        el.classList.remove('cop-bio-message--error', 'cop-bio-message--success');

        if (type === 'error') {
            el.classList.add('cop-bio-message--error');
        } else if (type === 'success') {
            el.classList.add('cop-bio-message--success');
        }
    }

    function init() {
        var saveButton = document.getElementById('cop-bio-save');

        if (!saveButton || typeof glandoreCopBioEditor === 'undefined') {
            return;
        }

        saveButton.addEventListener('click', function (event) {
            event.preventDefault();

            var editedBio = getEditorContent();

            if (!editedBio || !editedBio.trim()) {
                setMessage('The bio cannot be empty.', 'error');
                return;
            }

            setMessage(glandoreCopBioEditor.messages.saving || 'Saving…', null);
            saveButton.disabled = true;

            var payload = new URLSearchParams();
            payload.append('action', 'glandore_cop_save_bio');
            payload.append('edited_bio', editedBio);
            payload.append('nonce', glandoreCopBioEditor.nonce);

            fetch(glandoreCopBioEditor.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: payload.toString()
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    // WordPress AJAX convention: { success: bool, data: {...} }
                    if (json.success && json.data && json.data.status === 'ok') {
                        setMessage(json.data.message || glandoreCopBioEditor.messages.saved, 'success');
                    } else if (!json.success && json.data && json.data.status === 'too_different') {
                        // Reset to original bio and show message.
                        if (json.data.original_bio) {
                            setEditorContent(json.data.original_bio);
                        }
                        setMessage(json.data.message || 'The edited bio is too different from the original suggestion.', 'error');
                    } else {
                        setMessage(glandoreCopBioEditor.messages.error || 'Something went wrong. Please try again.', 'error');
                    }
                })
                .catch(function () {
                    setMessage(glandoreCopBioEditor.messages.error || 'Something went wrong. Please try again.', 'error');
                })
                .finally(function () {
                    saveButton.disabled = false;
                });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
