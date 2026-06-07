<?php
/**
 * Glandore – Registration modal
 *
 * Displays the URM registration form as a right-hand modal card
 * over the landing background (same pattern as login / cookies).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="glandore-modal-registration"
     class="auth-modal"
     hidden
     aria-hidden="true"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div class="auth-modal__backdrop"
         data-registration-modal-close></div>

    <!-- Dialog -->
    <div class="auth-modal__dialog">

        <!-- Close button -->
        <button type="button"
                class="auth-modal__close"
                aria-label="Close registration"
                data-registration-modal-close>
            &times;
        </button>

        <div class="glandore-auth-card">
            <h3 class="account-panel-title">User Registration</h3>
			<p></p>
		
<!--
		
		<div class="gl-registration-warning">			
            <p>
                User registration is a two-stage process. You will receive two emails:
				</p>
					<ul>
					  <li>The first will ask you to verify your email address. (You may login once you've done this). </li>
					  <li>The second is a welcome email and invites you to log in using the credentials you registered with.</li>
					</ul>
				Once you have registered successfully, you will always be able to log on using these credentials.	
            </p>
		</div>	
		
-->		

            <div class="glandore-auth-card__form" style="margin-top: 1.5rem;">
                <?php
                // URM registration form – same ID as on the existing page
                echo do_shortcode( '[user_registration_form id="8273"]' );
                ?>
            </div>
        </div>
    </div>
</div>
