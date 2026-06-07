<?php
/**
 * Glandore – Registration Update Modal (Stage 2 – edit profile)
 *
 * Static HTML form, populated from glandore_profile_data so that
 * users can review and update their earlier responses.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current user's stored profile data (if any).
$current_user_id  = get_current_user_id();
$profile_data     = $current_user_id
	? get_user_meta( $current_user_id, 'glandore_profile_data', true )
	: array();

$geography        = isset( $profile_data['geography'] ) ? $profile_data['geography'] : '';
$geography_other  = isset( $profile_data['geography_other'] ) ? $profile_data['geography_other'] : '';
$job_level        = isset( $profile_data['job_level'] ) ? $profile_data['job_level'] : '';
$job_level_other  = isset( $profile_data['job_level_other'] ) ? $profile_data['job_level_other'] : '';
$industry         = isset( $profile_data['industry'] ) ? $profile_data['industry'] : '';
$industry_other   = isset( $profile_data['industry_other'] ) ? $profile_data['industry_other'] : '';
$role_description = isset( $profile_data['role_description'] ) ? $profile_data['role_description'] : '';
$site_value       = isset( $profile_data['site_value'] ) ? $profile_data['site_value'] : '';
$ai_framework     = isset( $profile_data['ai_framework'] ) && is_array( $profile_data['ai_framework'] )
	? $profile_data['ai_framework']
	: array();
?>
<div
	id="glandore-modal-registration-update"
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
			<header class="auth-modal__header">
				<h2 class="auth-modal__title">
					Update your profile
				</h2>
			</header>

			<div class="auth-card__body">
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
							<option value="UK" <?php selected( $geography, 'UK' ); ?>>UK</option>
							<option value="USA and Canada" <?php selected( $geography, 'USA and Canada' ); ?>>USA and Canada</option>
							<option value="Europe" <?php selected( $geography, 'Europe' ); ?>>Europe</option>
							<option value="Australia and New Zealand" <?php selected( $geography, 'Australia and New Zealand' ); ?>>Australia and New Zealand</option>
							<option value="China" <?php selected( $geography, 'China' ); ?>>China</option>
							<option value="India" <?php selected( $geography, 'India' ); ?>>India</option>
							<option value="Asia (Other)" <?php selected( $geography, 'Asia (Other)' ); ?>>Asia (Other)</option>
							<option value="Africa and the Middle East" <?php selected( $geography, 'Africa and the Middle East' ); ?>>Africa and the Middle East</option>
							<option value="Latin America and the Caribbean" <?php selected( $geography, 'Latin America and the Caribbean' ); ?>>Latin America and the Caribbean</option>
							<option value="Other / prefer not to say" <?php selected( $geography, 'Other / prefer not to say' ); ?>>Other / prefer not to say</option>
						</select>
					</div>

					<div class="field">
						<label for="input_text">If other, please specify</label>
						<input
							type="text"
							name="input_text"
							id="input_text"
							value="<?php echo esc_attr( $geography_other ); ?>"
						>
					</div>

					<div class="field">
						<label for="dropdown_1">Job level</label>
						<select name="dropdown_1" id="dropdown_1">
							<option value="">- Please select -</option>
							<option value="Executive / Senior Leadership" <?php selected( $job_level, 'Executive / Senior Leadership' ); ?>>Executive / Senior Leadership</option>
							<option value="Middle Management" <?php selected( $job_level, 'Middle Management' ); ?>>Middle Management</option>
							<option value="Professional / Specialist" <?php selected( $job_level, 'Professional / Specialist' ); ?>>Professional / Specialist</option>
							<option value="Early Career / Entry Level" <?php selected( $job_level, 'Early Career / Entry Level' ); ?>>Early Career / Entry Level</option>
							<option value="Retired / Emeritus" <?php selected( $job_level, 'Retired / Emeritus' ); ?>>Retired / Emeritus</option>
							<option value="Other / prefer not to say" <?php selected( $job_level, 'Other / prefer not to say' ); ?>>Other / prefer not to say</option>
						</select>
					</div>

					<div class="field">
						<label for="input_text_1">If other, please specify</label>
						<input
							type="text"
							name="input_text_1"
							id="input_text_1"
							value="<?php echo esc_attr( $job_level_other ); ?>"
						>
					</div>

					<div class="field">
						<label for="dropdown_2">Industry</label>
						<select name="dropdown_2" id="dropdown_2">
							<option value="">- Please select -</option>
							<option value="Technology and Digital" <?php selected( $industry, 'Technology and Digital' ); ?>>Technology and Digital</option>
							<option value="Financial Services" <?php selected( $industry, 'Financial Services' ); ?>>Financial Services</option>
							<option value="Professional Services &amp; Consulting" <?php selected( $industry, 'Professional Services &amp; Consulting' ); ?>>Professional Services &amp; Consulting</option>
							<option value="Public Sector &amp; Government" <?php selected( $industry, 'Public Sector &amp; Government' ); ?>>Public Sector &amp; Government</option>
							<option value="Healthcare &amp; Life Services" <?php selected( $industry, 'Healthcare &amp; Life Services' ); ?>>Healthcare &amp; Life Services</option>
							<option value="Education &amp; Research" <?php selected( $industry, 'Education &amp; Research' ); ?>>Education &amp; Research</option>
							<option value="Oil &amp Gas" <?php selected( $industry, 'Oil &amp Gas' ); ?>>Oil &amp Gas</option>
							<option value="Energy &amp; Utilities" <?php selected( $industry, 'Energy &amp; Utilities' ); ?>>Energy &amp; Utilities</option>
							<option value="Manufacturing &amp; Industrial" <?php selected( $industry, 'Manufacturing &amp; Industrial' ); ?>>Manufacturing &amp; Industrial</option>
							<option value="Retail &amp; Consumer" <?php selected( $industry, 'Retail &amp; Consumer' ); ?>>Retail &amp; Consumer</option>
							<option value="Media, Comms &amp; Creative" <?php selected( $industry, 'Media, Comms &amp; Creative' ); ?>>Media, Comms &amp; Creative</option>
							<option value="Transport, Logistics &amp; Travel" <?php selected( $industry, 'Transport, Logistics &amp; Travel' ); ?>>Transport, Logistics &amp; Travel</option>
							<option value="Built Environment &amp; Real Estate " <?php selected( $industry, 'Built Environment &amp; Real Estate ' ); ?>>Built Environment &amp; Real Estate </option>
							<option value="Sustainability">Sustainability <?php selected( $industry, 'Sustainability' ); ?>>Sustainability</option>
							<option value="Agriculture &amp; Natural Resources" <?php selected( $industry, 'Agriculture &amp; Natural Resources' ); ?>>Agriculture &amp; Natural Resources</option>
							<option value="Non-profit &amp; Philanthropy" <?php selected( $industry, 'Non-profit &amp; Philanthropy' ); ?>>Non-profit &amp; Philanthropy</option>
							<option value="Other, prefer not to say" <?php selected( $industry, 'Other, prefer not to say' ); ?>>Other, prefer not to say</option>
						</select>
					</div>

					<div class="field">
						<label for="input_text_2">If other, please specify</label>
						<input
							type="text"
							name="input_text_2"
							id="input_text_2"
							value="<?php echo esc_attr( $industry_other ); ?>"
						>
					</div>

					<div class="field">
						<label for="description">How would you describe what you do?</label>
						<textarea name="description" id="description"><?php echo esc_textarea( $role_description ); ?></textarea>
					</div>

					<div class="field">
						<label for="description_1">What would you most value in the evolution of this site?</label>
						<textarea name="description_1" id="description_1"><?php echo esc_textarea( $site_value ); ?></textarea>
					</div>

					<div class="field field--ai-heading">
						<div class="ff-section-break">
							<p>Interest in AI-frameworks</p>
						</div>
					</div>

					<div class="field field--ai-select">
						<span class="screen-reader-text" id="ai_frameworks_label">AI-frameworks</span>

						<div class="ai-frameworks-list" aria-labelledby="ai_frameworks_label">

							<div class="ai-frameworks-row ai-frameworks-row--intro">
								<span class="ai-frameworks-text">- Please select all that apply -</span>
								<span class="ai-frameworks-box ai-frameworks-box--dummy" aria-hidden="true"></span>
							</div>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">ChatGPT (OpenAI)</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="ChatGPT (OpenAI)"
										<?php checked( in_array( 'ChatGPT (OpenAI)', $ai_framework, true ) ); ?>
									>
								</span>
							</label>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">Claude (Anthropic)</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="Claude (Anthropic)"
										<?php checked( in_array( 'Claude (Anthropic)', $ai_framework, true ) ); ?>
									>
								</span>
							</label>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">Gemini (Google)</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="Gemini (Google)"
										<?php checked( in_array( 'Gemini (Google)', $ai_framework, true ) ); ?>
									>
								</span>
							</label>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">Deepseek (V3 / Coder)</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="Deepseek (V3 / Coder)"
										<?php checked( in_array( 'Deepseek (V3 / Coder)', $ai_framework, true ) ); ?>
									>
								</span>
							</label>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">Llama (Meta)</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="Llama (Meta)"
										<?php checked( in_array( 'Llama (Meta)', $ai_framework, true ) ); ?>
									>
								</span>
							</label>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">Grok (xAI)</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="Grok (xAI)"
										<?php checked( in_array( 'Grok (xAI)', $ai_framework, true ) ); ?>
									>
								</span>
							</label>

							<label class="ai-frameworks-row">
								<span class="ai-frameworks-text">Other</span>
								<span class="ai-frameworks-box">
									<input
										type="checkbox"
										name="multi_select[]"
										value="Other"
										<?php checked( in_array( 'Other', $ai_framework, true ) ); ?>
									>
								</span>
							</label>
						</div>
					</div>

					<button type="submit">Submit</button>
				</form>
			</div><!-- /.auth-card__body -->
		</div><!-- /.glandore-auth-card -->
	</div><!-- /.auth-modal__dialog -->
</div><!-- /#glandore-modal-registration-update -->
