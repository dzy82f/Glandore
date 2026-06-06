document.addEventListener('DOMContentLoaded', function () {
    console.log('footer-controller.js loaded');

    const footer = document.querySelector('.glandore-footer');
    if (!footer) {
        console.warn('footer-controller: .glandore-footer not found');
        return;
    }

    const container = document.querySelector('.landing-intro-content');
    const hasAjax = typeof glandoreAjax !== 'undefined' && glandoreAjax.ajaxUrl;
    const originalMarkup = container ? container.innerHTML : '';

    function loadFooterPanel(slug) {
        if (!container) {
            console.warn('footer-controller: .landing-intro-content not found');
            return;
        }

        if (!hasAjax) {
            console.warn('footer-controller: glandoreAjax.ajaxUrl missing');
            return;
        }

        const action = 'glandore_' + slug;
        const formData = new FormData();
        formData.append('action', action);

        fetch(glandoreAjax.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then(function (response) {
                return response.text();
            })
            .then(function (html) {
                container.innerHTML = html;
            })
            .catch(function (err) {
                console.error('footer-controller AJAX error:', err);
            });
    }

    function resetHome(e) {
        if (e) {
            e.preventDefault();
        }

        if (!container) {
            return;
        }

        container.innerHTML = originalMarkup;
    }

    function openCookieModal() {
        const cookieModal =
            document.getElementById('modal-cookies') ||
            document.getElementById('cookie-modal') ||
            document.querySelector('[data-modal="cookies"]');

        if (!cookieModal) {
            console.warn('footer-controller: cookie modal not found');
            return;
        }

        if (typeof cookieModal.showModal === 'function') {
            cookieModal.showModal();
            return;
        }

        cookieModal.hidden = false;
        cookieModal.setAttribute('aria-hidden', 'false');
        cookieModal.classList.add('is-open');
        document.body.classList.add('modal-open');
    }

    footer.addEventListener('click', function (e) {
        const panelLink = e.target.closest('[data-footer-panel]');
        if (panelLink) {
            e.preventDefault();
            const slug = panelLink.getAttribute('data-footer-panel');
            if (slug) {
                loadFooterPanel(slug);
            }
            return;
        }

        const cookieLink = e.target.closest('[data-cookie-open-modal]');
        if (cookieLink) {
            e.preventDefault();
            openCookieModal();
        }
    });

    if (container) {
        container.addEventListener('click', function (e) {
            if (e.target.closest('.home-reset')) {
                resetHome(e);
            }
        });
    }

    document.addEventListener('click', function (e) {
        const resetTarget = e.target.closest('[data-home-reset]');
        if (resetTarget) {
            resetHome(e);
            return;
        }

        const bg = e.target.closest('.landing-background');
        if (bg) {
            if (e.target.closest('a, button, input, textarea, select, label')) {
                return;
            }

            const homeUrl = bg.dataset.homeUrl || '/';
            window.location.href = homeUrl;
        }
    });
});