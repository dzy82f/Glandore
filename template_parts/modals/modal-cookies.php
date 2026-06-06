<?php
/**
 * Cookie Preferences Modal (UI-only, v0)
 * Responsibility:
 * - Accessible modal shell
 * - Opened via data-cookie-open-modal="true"
 * - No consent logic, no persistence
 */
?>

<div
    id="modal-cookies"
    class="glandore-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-cookies-title"
    aria-describedby="modal-cookies-desc"
    hidden
>
    <div class="glandore-modal__overlay" data-modal-close></div>

    <div class="glandore-modal__panel" role="document">
        <header class="glandore-modal__header">
            <h3 id="modal-cookies-title">Cookie preferences</h3>
            <button
                type="button"
                class="glandore-modal__close"
                aria-label="Close cookie preferences"
                data-modal-close
            >
                ×
            </button>
        </header>

        <div class="glandore-modal__body" id="modal-cookies-desc">

    <h5>Analytics</h5>

    <p>
        We use Matomo, a privacy-conscious analytics tool, to understand how the
        site is used and to improve its design and content.
    </p>

    <p>
        Analytics data is processed under our control and is not shared with
        advertisers or third parties.
    </p>

    <?php if ( is_user_logged_in() ) : ?>
        <div class="glandore-analytics-toggle">
            <label>
                <input
                    type="checkbox"
                    id="glandore-analytics-checkbox"
                    <?php checked( glandore_user_allows_analytics() ); ?>
                >
                Allow privacy-conscious analytics
            </label>
        </div>
    <?php else : ?>
        <p>
            Analytics preferences can be managed once you are logged in.
        </p>
    <?php endif; ?>

</div>

        <footer class="glandore-modal__footer">
            <button
                type="button"
                class="glandore-button"
                data-modal-close
            >
                Close
            </button>
        </footer>
    </div>
</div>
