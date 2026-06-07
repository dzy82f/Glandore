(function () {
    'use strict';

    function attachToggle(input) {
        if (!input || input.dataset.glShowInit === '1') {
            return;
        }
        input.dataset.glShowInit = '1';

        var parent = input.parentElement;
        if (!parent) {
            return;
        }

        // Mark the parent so CSS can position things cleanly
        parent.classList.add('gl-pass-parent');

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'gl-pass-toggle';
        btn.textContent = 'Show';
        btn.setAttribute('aria-label', 'Show password');

        parent.appendChild(btn);

        btn.addEventListener('click', function () {
            var isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            btn.textContent = isPassword ? 'Hide' : 'Show';
            btn.setAttribute(
                'aria-label',
                isPassword ? 'Hide password' : 'Show password'
            );
        });
    }

    function init(root) {
        var scope = root || document;

        // Target exactly the URM fields in question
        var inputs = scope.querySelectorAll(
            'input[type="password"][name="user_pass"],' +
            'input[type="password"][name="user_confirm_password"]'
        );

        inputs.forEach(attachToggle);
    }

    document.addEventListener('DOMContentLoaded', function () {
        init(document);
    });

    // Safety for any future AJAX-rendered forms
    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (node) {
                    if (!(node instanceof HTMLElement)) {
                        return;
                    }
                    init(node);
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
