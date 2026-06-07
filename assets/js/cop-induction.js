/* global jQuery, tinymce */

jQuery(function ($) {
    var $form = $('#cop-induction-form');
    if (!$form.length) {
        return;
    }

    var ajaxUrl     = $form.data('ajax-url');
    var $result     = $('#cop-induction-result');
    var $submit     = $form.find('button[type="submit"], input[type="submit"]');
    var $bioWrapper = $('#cop-bio-review-wrapper');
    var $bioSaveBtn = $('#cop-bio-save');
    var $bioStatus  = $('#cop-bio-save-status');

    function resetResult() {
        $result
            .removeClass(
                'cop-induction-result--welcome ' +
                'cop-induction-result--need-more ' +
                'cop-induction-result--not-suitable ' +
                'cop-induction-result--error'
            )
            .empty();
    }

    function showError(msg) {
        resetResult();
        $result
            .addClass('cop-induction-result--error')
            .html('<p>' + msg + '</p>');
    }

    function populateBioEditor(bio) {
        if (!$bioWrapper.length) {
            return;
        }
        bio = bio || '';

        if (typeof tinymce !== 'undefined' && tinymce.get('cop_bio_review')) {
            tinymce.get('cop_bio_review').setContent(bio);
        } else {
            $('#cop_bio_review').val(bio);
        }
    }

    function toggleBioEditor(show, bio) {
        if (!$bioWrapper.length) {
            return;
        }

        if (show) {
            $bioWrapper.show();
            populateBioEditor(bio);
        } else {
            $bioWrapper.hide();
        }
    }

    // ------------------------------------------------
    // Save bio button
    // ------------------------------------------------
    if ($bioSaveBtn.length) {
        $bioSaveBtn.on('click', function (e) {
            e.preventDefault();

            if (!ajaxUrl) {
                return;
            }

            var bioContent = '';
            if (typeof tinymce !== 'undefined' && tinymce.get('cop_bio_review')) {
                bioContent = tinymce.get('cop_bio_review').getContent();
            } else {
                bioContent = $('#cop_bio_review').val();
            }

            bioContent = bioContent.trim();

            if (!bioContent) {
                $bioStatus.text('Please add a short bio before saving.');
                return;
            }

            $bioSaveBtn.prop('disabled', true);
            $bioStatus.text('Saving…');

            $.post(ajaxUrl, {
                action: 'glandore_cop_save_bio',
                bio: bioContent,
                cop_induction_nonce: $('#cop_induction_nonce').val()
            })
            .done(function (resp) {
                if (resp && resp.success) {
                    $bioStatus.text('Saved.');
                } else if (resp && resp.data && resp.data.message) {
                    $bioStatus.text(resp.data.message);
                } else {
                    $bioStatus.text('There was a problem saving your bio.');
                }
            })
            .fail(function () {
                $bioStatus.text('There was a technical problem saving your bio.');
            })
            .always(function () {
                $bioSaveBtn.prop('disabled', false);
            });
        });
    }

    // ------------------------------------------------
    // Induction form submit
    // ------------------------------------------------
    $form.on('submit', function (e) {
        e.preventDefault();

        if (!ajaxUrl) {
            showError('Missing AJAX endpoint.');
            return;
        }

        $submit.prop('disabled', true);
        resetResult();
        toggleBioEditor(false);

        var data = $form.serializeArray();
        data.push({ name: 'action', value: 'glandore_cop_submit_induction' });

        $.post(ajaxUrl, data)
        .done(function (resp) {
            if (!resp || !resp.success || !resp.data) {
                showError('Something went wrong while saving your introduction. Please try again.');
                return;
            }

            var status  = resp.data.status || 'need_more_detail';
            var bio     = resp.data.bio || '';
            var message = resp.data.message || '';
            var html    = '';

            resetResult();

            if (status === 'welcome') {
                // WELCOME: just the message; bio is handled by editor below
                $result.addClass('cop-induction-result--welcome');

                html += '<p><strong>Welcome.</strong> ' +
                    (message || 'You can start posting and engaging with the community straight away. You can always refine or expand your profile later.') +
                    '</p>';

                // Show TinyMCE editor prefilled with the generated bio
                toggleBioEditor(true, bio);

            } else if (status === 'need_more_detail') {
                $result.addClass('cop-induction-result--need-more');
                html += '<p>' + (
                    message ||
                    'At the moment, what you have written does not yet give others enough sense of who you are and the work you do. Please add a few more sentences in ordinary language and send again.'
                ) + '</p>';
                toggleBioEditor(false);

            } else if (status === 'not_suitable') {
                $result.addClass('cop-induction-result--not-suitable');
                html += '<p><strong>Thank you for your interest.</strong> Julian feels that, based on what you have written, you may not currently be well suited to participating in this Community of Practice.</p>';
                html += '<p>This space is for thoughtful, good-faith conversation rather than disruption or quick takes. You are very welcome to update your introduction if you would like to participate seriously, then send it again.</p>';
                toggleBioEditor(false);

            } else {
                $result.addClass('cop-induction-result--error');
                html += '<p>Something about your introduction is unclear at the moment. Please review what you have written, add a little more detail about who you are and the work you do, and try again.</p>';
                toggleBioEditor(false);
            }

            $result.html(html);
        })
        .fail(function () {
            showError('There was a technical problem submitting your introduction. Please try again in a moment.');
        })
        .always(function () {
            $submit.prop('disabled', false);
        });
    });
});
