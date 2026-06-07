<?php
/**
 * Site-wide modal shell (Glandore)
 * Used to load pages (e.g. /account/) into a modal via JS.
 */
?>
<div id="glandore-site-modal-overlay" class="glandore-site-modal-overlay" hidden></div>

<section
    id="glandore-site-modal"
    class="glandore-site-modal"
    role="dialog"
    aria-modal="true"
    aria-label="Modal"
    hidden
>
    <button id="glandore-site-modal-close" class="glandore-site-modal-close" type="button" aria-label="Close">
        ×
    </button>

    <div id="glandore-site-modal-panel" class="glandore-site-modal-panel"></div>
</section>
