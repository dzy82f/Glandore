(function () {
    const modal = document.getElementById('modal-cookies');
    if (!modal) return;

    const openers = document.querySelectorAll('[data-cookie-open-modal="true"]');
    const closers = modal.querySelectorAll('[data-modal-close]');
    let lastFocusedElement = null;

    function openModal(e) {
        if (e) e.preventDefault();

        lastFocusedElement = document.activeElement;
        modal.removeAttribute('hidden');

        const focusable = modal.querySelector(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (focusable) focusable.focus();

        document.addEventListener('keydown', onKeyDown);
    }

    function closeModal() {
        modal.setAttribute('hidden', '');
        document.removeEventListener('keydown', onKeyDown);

        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    }

    function onKeyDown(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    }

    openers.forEach(link => {
        link.addEventListener('click', openModal);
    });

    closers.forEach(el => {
        el.addEventListener('click', closeModal);
    });

    /* -----------------------------
       Analytics preference handling
       ----------------------------- */

    const analyticsCheckbox = modal.querySelector('#glandore-analytics-checkbox');

    if (analyticsCheckbox && typeof glandoreAnalytics !== 'undefined') {

        // If PHP has NOT told us there is an existing preference,
        // default Matomo to "allowed" (checkbox ticked) and persist it.
        if (!glandoreAnalytics.hasPreference || glandoreAnalytics.hasPreference !== '1') {
            analyticsCheckbox.checked = true;

            const defaultFormData = new FormData();
            defaultFormData.append('action', 'glandore_set_analytics_preference');
            defaultFormData.append('allowed', '1'); // Matomo = ON by default
            defaultFormData.append('nonce', glandoreAnalytics.nonce);

            fetch(glandoreAnalytics.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: defaultFormData
            });
        }

        // Handle user changes from the modal
        analyticsCheckbox.addEventListener('change', function () {
            const formData = new FormData();
            formData.append('action', 'glandore_set_analytics_preference');
            formData.append('allowed', this.checked ? '1' : '0');
            formData.append('nonce', glandoreAnalytics.nonce);

            fetch(glandoreAnalytics.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
        });
    }

})();

