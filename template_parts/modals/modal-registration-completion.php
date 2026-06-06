<?php
/**
 * Glandore – Registration Completion Modal (Stage 2 – static HTML form)
 *
 * Uses a plain HTML form generated from the Fluent Forms JSON export.
 * We now own the markup and CSS completely.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div
	id="glandore-modal-registration-completion"
	class="auth-modal"
	role="dialog"
	aria-modal="true"
	aria-hidden="true"
	hidden
>
	<div class="auth-modal__backdrop" data-auth-modal-close="1" tabindex="-1" aria-hidden="true"></div>

	<div class="auth-modal__dialog" role="document">
		<button type="button" class="auth-modal__close" aria-label="Close" data-auth-modal-close="1">
			<span aria-hidden="true">&times;</span>
		</button>

		<div class="glandore-auth-card">
			<header class="auth-card__header">
				<p class="auth-card__subtitle">
					One of the aspirations as the site evolves is to develop it as a
					<a href="/wp-content/uploads/2020/05/Communities-of-Practice_-The-Organizational-Frontier.pdf" target="_blank" rel="noopener">Community of Practice</a>.
				</p>

				<p class="auth-card__subtitle">
					This is a group of people who share a concern, craft or passion and who deepen their knowledge and skill by interacting regularly. It isn’t just a team or a network; it’s held together by three things: a shared domain, community and practice. Over time, a community of practice becomes the place where that practice “lives” and evolves.</p>
				<p class="auth-card__subtitle">
					With this in mind, it would be very helpful if you could complete as much of this as you feel able. (You'll be able to change it at any time).</p>
			</header>

			<div class="auth-card__body">
				<?php
				// You can change the action later to a dedicated handler URL if you wish.
				?>

				<!-- BEGIN: static profile form -->
				<!-- Generated from Fluent Form: Registration Completion -->
				<form method="post" class="glandore-profile-form">
					<?php wp_nonce_field( 'glandore_profile_completion', 'glandore_profile_nonce' ); ?>

					<div class="field field--ai-heading">
						<div class="ff-section-break">
							<p>Profile</p>
						</div>
					</div>

					<div class="field">
						<label for="dropdown">Geography</label>
						<select name="dropdown" id="dropdown">
						  <option value="">- Please select -</option>
						  <option value="UK">UK</option>
						  <option value="USA and Canada">USA and Canada</option>
						  <option value="Europe">Europe</option>
						  <option value="Australia and New Zealand">Australia and New Zealand</option>
						  <option value="China">China</option>
						  <option value="India">India</option>
						  <option value="Asia (Other)">Asia (Other)</option>
						  <option value="Africa and the Middle East">Africa and the Middle East</option>
						  <option value="Latin America and the Caribbean">Latin America and the Caribbean</option>
						  <option value="Other / prefer not to say">Other / prefer not to say</option>
						</select>
					</div>

					<div class="field">
						<label for="input_text">If other, please specify</label>
						<input type="text" name="input_text" id="input_text">
					</div>

					<div class="field">
						<label for="dropdown_1">Job level</label>
						<select name="dropdown_1" id="dropdown_1">
						  <option value="">- Please select -</option>
						  <option value="Executive / Senior Leadership">Executive / Senior Leadership</option>
						  <option value="Middle Management">Middle Management</option>
						  <option value="Professional / Specialist">Professional / Specialist</option>
						  <option value="Early Career / Entry Level">Early Career / Entry Level</option>
						  <option value="Retired / Emeritus">Retired / Emeritus</option>
						  <option value="Other / prefer not to say">Other / prefer not to say</option>
						</select>
					</div>

					<div class="field">
						<label for="input_text_1">If other, please specify</label>
						<input type="text" name="input_text_1" id="input_text_1">
					</div>

					<div class="field">
						<label for="dropdown_2">Industry</label>
						<select name="dropdown_2" id="dropdown_2">
						  <option value="">- Please select -</option>
						  <option value="Technology and Digital">Technology and Digital</option>
						  <option value="Financial Services">Financial Services</option>
						  <option value="Professional Services &amp; Consulting">Professional Services &amp; Consulting</option>
						  <option value="Public Sector &amp; Government">Public Sector &amp; Government</option>
						  <option value="Healthcare &amp; Life Services">Healthcare &amp; Life Services</option>						  
						  <option value="Education &amp; Research">Education &amp; Research</option>
						  <option value="Oil &amp Gas">Oil &amp Gas</option>
						  <option value="Energy &amp; Utilities">Energy &amp; Utilities</option>
						  <option value="Manufacturing &amp; Industrial">Manufacturing &amp; Industrial</option>
						  <option value="Retail &amp; Consumer">Retail &amp; Consumer</option>
						  <option value="Media, Comms &amp; Creative">Media, Comms &amp; Creative</option>
						  <option value="Transport, Logistics &amp; Travel">Transport, Logistics &amp; Travel</option>						  
						  <option value="Built Environment &amp; Real Estate ">Built Environment &amp; Real Estate </option>
						  <option value="Sustainability">Sustainability</option>
						  <option value="Agriculture &amp; Natural Resources">Agriculture &amp; Natural Resources</option>
						  <option value="Non-profit &amp; Philanthropy">Non-profit &amp; Philanthropy</option>
						  <option value="Other, prefer not to say">Other, prefer not to say</option>
						</select>
					</div>

					<div class="field">
						<label for="input_text_2">If other, please specify</label>
						<input type="text" name="input_text_2" id="input_text_2">
					</div>

					<div class="field">
						<label for="description">How would you describe what you do?</label>
						<textarea name="description" id="description"></textarea>
					</div>

					<div class="field">
						<label for="description_1">What would you most value in the evolution of this site?</label>
						<textarea name="description_1" id="description_1"></textarea>
					</div>

					<div class="field field--ai-heading">
					
		<div class="field field--ai-heading">
			<div class="ff-section-break">
				<p>Interest in AI-frameworks</p>
			</div>
		</div>

		<div class="field field--ai-select">
			<span class="screen-reader-text" id="ai_frameworks_label">AI-frameworks</span>

			<div class="ai-frameworks-list" aria-labelledby="ai_frameworks_label">

				<!-- Intro row – decorative square only, no real checkbox -->
				<div class="ai-frameworks-row ai-frameworks-row--intro">
					<span class="ai-frameworks-text">- Please select all that apply -</span>
					 <!-- <span class="ai-frameworks-box ai-frameworks-box--dummy" aria-hidden="true"></span> -->
				</div>

				<!-- Options -->
				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">ChatGPT (OpenAI)</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="ChatGPT (OpenAI)">
					</span>
				</label>

				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">Claude (Anthropic)</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="Claude (Anthropic)">
					</span>
				</label>

				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">Gemini (Google)</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="Gemini (Google)">
					</span>
				</label>

				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">Deepseek (V3 / Coder)</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="Deepseek (V3 / Coder)">
					</span>
				</label>

				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">Llama (Meta)</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="Llama (Meta)">
					</span>
				</label>

				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">Grok (xAI)</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="Grok (xAI)">
					</span>
				</label>

				<label class="ai-frameworks-row">
					<span class="ai-frameworks-text">Other</span>
					<span class="ai-frameworks-box">
						<input type="checkbox" name="multi_select[]" value="Other">
					</span>
				</label>
			</div>
		</div>


					<button type="submit">Submit</button>
				</form>
				<!-- END: static profile form -->

			</div><!-- /.auth-card__body -->
		</div><!-- /.glandore-auth-card -->
	</div><!-- /.auth-modal__dialog -->
</div><!-- /#glandore-modal-registration-completion -->
