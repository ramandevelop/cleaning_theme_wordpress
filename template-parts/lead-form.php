<?php
/**
 * Reusable, nonce-protected contact and quote form.
 *
 * @package BrightNest_Cleaning
 */
$lead_status = isset( $_GET['lead_status'] ) && is_string( $_GET['lead_status'] ) ? sanitize_key( wp_unslash( $_GET['lead_status'] ) ) : '';
$notices     = array(
	'sent'         => array( __( 'Thanks for reaching out. Your message has been sent.', 'brightnest-cleaning' ), 'success' ),
	'invalid'      => array( __( 'Please check the required fields and try again.', 'brightnest-cleaning' ), 'error' ),
	'error'        => array( __( 'We could not send your message just now. Please try again or contact us directly.', 'brightnest-cleaning' ), 'error' ),
	'rate_limited' => array( __( 'Too many requests were received. Please wait a little while before trying again.', 'brightnest-cleaning' ), 'error' ),
);
$is_quote_form = ! empty( $args['is_quote_form'] );
$form_id       = wp_unique_id( 'bn-lead-form-' );
$quote_settings = $is_quote_form && function_exists( 'brightnest_core_quote_settings' ) ? brightnest_core_quote_settings() : array();
?>
<?php if ( isset( $notices[ $lead_status ] ) ) : ?>
	<p class="bn-notice<?php echo 'error' === $notices[ $lead_status ][1] ? ' bn-notice--error' : ''; ?>" role="<?php echo 'error' === $notices[ $lead_status ][1] ? 'alert' : 'status'; ?>"><?php echo esc_html( $notices[ $lead_status ][0] ); ?></p>
<?php endif; ?>
<?php if ( ! function_exists( 'brightnest_core_handle_lead_form' ) ) : ?>
	<p class="bn-notice bn-notice--error" role="status"><?php esc_html_e( 'Install and activate the BrightNest Cleaning Core plugin to enable secure form submissions.', 'brightnest-cleaning' ); ?></p>
<?php elseif ( $is_quote_form && ! post_type_exists( 'bn_cleaning_package' ) ) : ?>
	<p class="bn-notice bn-notice--error" role="status"><?php esc_html_e( 'Activate BrightNest Cleaning Core to manage and select cleaning packages.', 'brightnest-cleaning' ); ?></p>
<?php else : ?>
	<?php
	$quote_packages = array();
	$addons          = array();
	$selected_package = 0;
	$selected_addon   = 0;
	if ( $is_quote_form ) {
		$available_packages = array();
		$space_options      = brightnest_core_quote_options( $quote_settings['space_options'] );
		$frequency_options  = brightnest_core_quote_options( $quote_settings['frequency_options'] );
		$quote_packages = get_posts(
			array(
				'post_type'      => 'bn_cleaning_package',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			)
		);
		$addons = get_posts(
			array(
				'post_type'      => 'bn_addon_service',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			)
		);
		foreach ( $quote_packages as $package ) {
			if ( ! brightnest_core_cleaning_item_is_enabled( $package->ID, '_bn_package_' ) ) {
				continue;
			}
			$available_packages[] = $package;
		}
		$quote_packages = $available_packages;
		foreach ( $addons as $index => $addon ) {
			if ( ! brightnest_core_cleaning_item_is_enabled( $addon->ID, '_bn_addon_' ) ) {
				unset( $addons[ $index ] );
			}
		}
		$addon_slug = isset( $_GET['addon'] ) && is_string( $_GET['addon'] ) ? sanitize_title( wp_unslash( $_GET['addon'] ) ) : '';
		if ( $addon_slug ) {
			$requested_addon = get_page_by_path( $addon_slug, OBJECT, 'bn_addon_service' );
			if ( $requested_addon && 'publish' === $requested_addon->post_status && brightnest_core_cleaning_item_is_enabled( $requested_addon->ID, '_bn_addon_' ) ) {
				$selected_addon = (int) $requested_addon->ID;
			}
		}
		$package_slug = isset( $_GET['package'] ) && is_string( $_GET['package'] ) ? sanitize_title( wp_unslash( $_GET['package'] ) ) : '';
		if ( $package_slug ) {
			$requested_package = get_page_by_path( $package_slug, OBJECT, 'bn_cleaning_package' );
			if ( $requested_package && 'publish' === $requested_package->post_status && brightnest_core_cleaning_item_is_enabled( $requested_package->ID, '_bn_package_' ) ) {
				$selected_package = (int) $requested_package->ID;
			}
		}
	}
	?>
	<?php if ( $is_quote_form && ! $quote_packages ) : ?>
		<p class="bn-notice bn-notice--error" role="status"><?php esc_html_e( 'No active cleaning packages are available yet. Please contact us directly or publish a package in the WordPress dashboard.', 'brightnest-cleaning' ); ?></p>
	<?php else : ?>
		<form id="<?php echo esc_attr( $is_quote_form ? 'quote-form' : $form_id ); ?>" class="bn-lead-form<?php echo $is_quote_form ? ' bn-quote-form' : ''; ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="brightnest_lead">
			<input type="hidden" name="lead_form_type" value="<?php echo $is_quote_form ? 'quote' : 'contact'; ?>">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
			<?php wp_nonce_field( 'brightnest_lead', 'brightnest_nonce' ); ?>
			<p class="bn-honeypot" aria-hidden="true"><label for="<?php echo esc_attr( $form_id . '-website' ); ?>"><?php esc_html_e( 'Leave this field empty', 'brightnest-cleaning' ); ?></label><input id="<?php echo esc_attr( $form_id . '-website' ); ?>" type="text" name="website" value="" tabindex="-1" autocomplete="off"></p>

			<?php if ( $is_quote_form ) : ?>
				<nav class="bn-quote-progress" aria-label="<?php esc_attr_e( 'Quote request progress', 'brightnest-cleaning' ); ?>">
					<ol>
						<li data-quote-progress><span>01</span><span><?php echo esc_html( $quote_settings['package_step'] ); ?></span></li>
						<li data-quote-progress><span>02</span><span><?php echo esc_html( $quote_settings['space_step'] ); ?></span></li>
						<li data-quote-progress><span>03</span><span><?php echo esc_html( $quote_settings['extras_step'] ); ?></span></li>
						<li data-quote-progress><span>04</span><span><?php echo esc_html( $quote_settings['contact_step'] ); ?></span></li>
					</ol>
				</nav>
				<p class="bn-quote-step-status" data-quote-status aria-live="polite" aria-atomic="true"></p>

				<fieldset class="bn-quote-step" data-quote-step>
					<legend><?php echo esc_html( $quote_settings['package_question'] ); ?></legend>
					<p class="bn-quote-step-intro"><?php echo esc_html( $quote_settings['package_help'] ); ?></p>
					<div class="bn-package-choices">
						<?php foreach ( $quote_packages as $package ) : ?>
							<?php
							$package_id  = (int) $package->ID;
							$price_type  = get_post_meta( $package_id, '_bn_package_price_type', true );
							$price_amount = get_post_meta( $package_id, '_bn_package_price_amount', true );
							$package_price = 'fixed' === $price_type && is_numeric( $price_amount ) ? $quote_settings['currency_symbol'] . number_format_i18n( (float) $price_amount, 2 ) : $quote_settings['custom_price_label'];
							$popular_label = get_post_meta( $package_id, '_bn_package_popular_label', true );
							?>
							<label class="bn-package-choice" for="<?php echo esc_attr( $form_id . '-package-' . $package_id ); ?>">
								<input id="<?php echo esc_attr( $form_id . '-package-' . $package_id ); ?>" type="radio" name="lead_package" value="<?php echo esc_attr( (string) $package_id ); ?>" <?php checked( $selected_package, $package_id ); ?> required>
								<span class="bn-package-choice-card">
									<span class="bn-package-choice-heading"><strong><?php echo esc_html( get_the_title( $package_id ) ); ?></strong><span><?php echo esc_html( $package_price ); ?></span></span>
									<?php if ( $popular_label ) : ?><span class="bn-package-choice-badge"><?php echo esc_html( $popular_label ); ?></span><?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<fieldset class="bn-quote-step" data-quote-step>
					<legend><?php echo esc_html( $quote_settings['space_question'] ); ?></legend>
					<p class="bn-quote-step-intro"><?php echo esc_html( $quote_settings['space_help'] ); ?></p>
					<div class="bn-field">
						<label for="<?php echo esc_attr( $form_id . '-space-size' ); ?>"><?php echo esc_html( $quote_settings['space_label'] ); ?> *</label>
						<select id="<?php echo esc_attr( $form_id . '-space-size' ); ?>" name="lead_space_size" required>
							<option value=""><?php echo esc_html( $quote_settings['space_placeholder'] ); ?></option>
							<?php foreach ( $space_options as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
						</select>
					</div>
					<div class="bn-field">
						<label for="<?php echo esc_attr( $form_id . '-frequency' ); ?>"><?php echo esc_html( $quote_settings['frequency_label'] ); ?> *</label>
						<select id="<?php echo esc_attr( $form_id . '-frequency' ); ?>" name="lead_frequency" required>
							<option value=""><?php echo esc_html( $quote_settings['frequency_placeholder'] ); ?></option>
							<?php foreach ( $frequency_options as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
						</select>
					</div>
				</fieldset>

				<fieldset class="bn-quote-step" data-quote-step>
					<legend><?php echo esc_html( $quote_settings['extras_question'] ); ?></legend>
					<p class="bn-quote-step-intro"><?php echo esc_html( $quote_settings['extras_help'] ); ?></p>
					<?php if ( $addons ) : ?>
						<div class="bn-addon-choices">
							<?php foreach ( $addons as $addon ) : ?>
								<?php
								$addon_id   = (int) $addon->ID;
								$price_type = get_post_meta( $addon_id, '_bn_addon_price_type', true );
								$amount     = get_post_meta( $addon_id, '_bn_addon_price_amount', true );
								$price      = 'fixed' === $price_type && is_numeric( $amount ) ? $quote_settings['currency_symbol'] . number_format_i18n( (float) $amount, 2 ) : $quote_settings['custom_price_label'];
								?>
								<label class="bn-addon-choice" for="<?php echo esc_attr( $form_id . '-addon-' . $addon_id ); ?>">
									<input id="<?php echo esc_attr( $form_id . '-addon-' . $addon_id ); ?>" type="checkbox" name="lead_addons[]" value="<?php echo esc_attr( (string) $addon_id ); ?>" <?php checked( $selected_addon, $addon_id ); ?>>
									<span><?php echo esc_html( get_the_title( $addon_id ) ); ?></span>
									<span class="bn-addon-price"><?php echo esc_html( $price ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="bn-notice" role="status"><?php esc_html_e( 'There are no optional services to add right now. You can continue without extras.', 'brightnest-cleaning' ); ?></p>
					<?php endif; ?>
				</fieldset>

				<fieldset class="bn-quote-step" data-quote-step>
					<legend><?php echo esc_html( $quote_settings['contact_question'] ); ?></legend>
					<p class="bn-quote-step-intro"><?php echo esc_html( $quote_settings['contact_help'] ); ?></p>
					<div class="bn-form-grid">
						<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-name' ); ?>"><?php esc_html_e( 'Your name', 'brightnest-cleaning' ); ?> *</label><input id="<?php echo esc_attr( $form_id . '-name' ); ?>" name="lead_name" type="text" autocomplete="name" maxlength="200" required></div>
						<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-email' ); ?>"><?php esc_html_e( 'Email address', 'brightnest-cleaning' ); ?> *</label><input id="<?php echo esc_attr( $form_id . '-email' ); ?>" name="lead_email" type="email" autocomplete="email" maxlength="254" required></div>
						<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-phone' ); ?>"><?php esc_html_e( 'Phone number', 'brightnest-cleaning' ); ?></label><input id="<?php echo esc_attr( $form_id . '-phone' ); ?>" name="lead_phone" type="tel" autocomplete="tel" maxlength="60"></div>
						<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-date' ); ?>"><?php esc_html_e( 'Preferred cleaning date (optional)', 'brightnest-cleaning' ); ?></label><input id="<?php echo esc_attr( $form_id . '-date' ); ?>" name="lead_date" type="date" min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"></div>
						<div class="bn-field bn-field--wide"><label for="<?php echo esc_attr( $form_id . '-message' ); ?>"><?php esc_html_e( 'Anything else we should know? (optional)', 'brightnest-cleaning' ); ?></label><textarea id="<?php echo esc_attr( $form_id . '-message' ); ?>" name="lead_message" maxlength="10000"></textarea></div>
					</div>
					<p class="bn-form-note"><?php esc_html_e( 'We’ll use these details only to respond to your inquiry. Please don’t include sensitive personal information.', 'brightnest-cleaning' ); ?></p>
				</fieldset>

				<div class="bn-quote-actions">
					<button class="bn-button bn-button--outline" type="button" data-quote-back hidden><?php echo esc_html( $quote_settings['back_button'] ); ?></button>
					<button class="bn-button" type="button" data-quote-next hidden><?php echo esc_html( $quote_settings['continue_button'] ); ?><span class="bn-arrow" aria-hidden="true">→</span></button>
					<button class="bn-button" type="submit" data-quote-submit><?php echo esc_html( $quote_settings['submit_button'] ); ?><span class="bn-arrow" aria-hidden="true">→</span></button>
				</div>
			<?php else : ?>
				<div class="bn-form-grid">
					<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-name' ); ?>"><?php esc_html_e( 'Your name', 'brightnest-cleaning' ); ?> *</label><input id="<?php echo esc_attr( $form_id . '-name' ); ?>" name="lead_name" type="text" autocomplete="name" maxlength="200" required></div>
					<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-email' ); ?>"><?php esc_html_e( 'Email address', 'brightnest-cleaning' ); ?> *</label><input id="<?php echo esc_attr( $form_id . '-email' ); ?>" name="lead_email" type="email" autocomplete="email" maxlength="254" required></div>
					<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-phone' ); ?>"><?php esc_html_e( 'Phone number', 'brightnest-cleaning' ); ?></label><input id="<?php echo esc_attr( $form_id . '-phone' ); ?>" name="lead_phone" type="tel" autocomplete="tel" maxlength="60"></div>
					<div class="bn-field"><label for="<?php echo esc_attr( $form_id . '-service' ); ?>"><?php esc_html_e( 'What can we help clean?', 'brightnest-cleaning' ); ?> *</label><select id="<?php echo esc_attr( $form_id . '-service' ); ?>" name="lead_service" required><option value=""><?php esc_html_e( 'Choose a service', 'brightnest-cleaning' ); ?></option><option value="residential"><?php esc_html_e( 'Residential cleaning', 'brightnest-cleaning' ); ?></option><option value="commercial"><?php esc_html_e( 'Commercial cleaning', 'brightnest-cleaning' ); ?></option><option value="move-in-out"><?php esc_html_e( 'Move-in / move-out cleaning', 'brightnest-cleaning' ); ?></option><option value="other"><?php esc_html_e( 'Something else', 'brightnest-cleaning' ); ?></option></select></div>
					<div class="bn-field bn-field--wide"><label for="<?php echo esc_attr( $form_id . '-message' ); ?>"><?php esc_html_e( 'Tell us a little more', 'brightnest-cleaning' ); ?> *</label><textarea id="<?php echo esc_attr( $form_id . '-message' ); ?>" name="lead_message" maxlength="10000" required></textarea></div>
				</div>
				<p class="bn-form-note"><?php esc_html_e( 'We’ll use these details only to respond to your inquiry. Please don’t include sensitive personal information.', 'brightnest-cleaning' ); ?></p>
				<button class="bn-button" type="submit"><?php esc_html_e( 'Send your request', 'brightnest-cleaning' ); ?><span class="bn-arrow" aria-hidden="true">→</span></button>
			<?php endif; ?>
		</form>
	<?php endif; ?>
<?php endif; ?>
