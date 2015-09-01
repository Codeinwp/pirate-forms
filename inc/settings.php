<?php

// Theme settings/options page	

/*
0 = name
1 = id
2 = desc
3 = type
4 = default
5 = options
*/

function pirate_forms_plugin_options() {
	return array(
		'head1'                          => array(
			__( 'Fields','pirate-forms' ),
			'',
			'title',
			'',
		),
		'propercfp_name_field'           => array(
			__( 'Name','pirate-forms' ),
			__( 'Do you want the name field to be displayed?','pirate-forms' ),
			'select',
			'yes',
			array(
				''    => __( 'None','pirate-forms' ),
				'yes' => __( 'Yes but not required','pirate-forms' ),
				'req' => __( 'Required','pirate-forms' ),
			),
		),
		'propercfp_email_field'          => array(
			__( 'Email address','pirate-forms' ),
			__( 'Do you want the email address field be displayed?','pirate-forms' ),
			'select',
			'yes',
			array(
				''    => 'None',
				'yes' => 'Yes but not required',
				'req' => 'Required'
			),
		),
		'propercfp_phone_field'          => array(
			__( 'Subject','pirate-forms' ),
			__( 'Do you want the subject field be displayed?','pirate-forms' ),
			'select',
			'yes',
			array(
				''    => 'None',
				'yes' => 'Yes but not required',
				'req' => 'Required'
			),
		),
		'propercfp_reason'               => array(
			'Message',
			'',
			'textarea',
			'',
		),
		'propercfp_captcha_field'        => array(
			__( 'Add a reCAPTCHA','pirate-forms' ),
			__( 'Checking this box will add a reCAPTCHA to the form to discourage spam','pirate-forms' ),
			'checkbox',
			'',
		),
		'head2'                          => array(
			'Form processing options',
			'',
			'title',
			'',
		),
		'propercfp_email'                => array(
			'Contact notification sender email',
			'Email to use for the sender of the contact form emails both to the recipients below and the contact form submitter (if this is activated below). The domain for this email address should match your site\'s domain.',
			'email',
			get_bloginfo( 'admin_email' )
		),
		'propercfp_reply_to_admin'       => array(
			'Use the email address above as notification sender',
			'When this is on, the notification emails sent from your site will come from the email address above. When this is off, the emails will come from the form submitter, making it easy to reply. If you are not receiving notifications from the site, then turn this option off as your email server might be marking them as spam.',
			'checkbox',
			'',
		),
		'propercfp_email_recipients' => array(
			'Contact submission recipients',
			'Email address(es) to receive contact submission notifications. You can separate multiple emails with a comma.',
			'text',
			proper_contact_get_key( 'propercfp_email' ) ?
				proper_contact_get_key( 'propercfp_email' ) :
				get_bloginfo( 'admin_email' )
		),
		'propercfp_result_url'           => array(
			'"Thank You" URL',
			'Select the post-submit page for all forms submitted',
			'select',
			'',
			proper_get_content_array()
		),
		'propercfp_css'                  => array(
			'Add styles to the site',
			'Checking this box will add styles to the form. By default, this is off so you can add your own styles.',
			'checkbox',
			'',
		),
		'propercfp_store'                => array(
			'Store submissions in the database',
			'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved in Contacts on the left (appears after this option is activated).',
			'checkbox',
			'',
		),
		'propercfp_blacklist'            => array(
			'Use the comments blacklist to restrict submissions',
			'Should form submission IP and email addresses be compared against the Comment Blacklist, found in <strong>wp-admin > Settings > Discussion > Comment Blacklist?</strong>',
			'checkbox',
			'yes',
		),
		'propercfp_confirm_email'        => array(
			'Send email confirmation to form submitter',
			'Adding text here will send an email to the form submitter. The email uses the "Text to show when form is submitted..." field below as the subject line. Plain text only here, no HTML.',
			'textarea',
			'',
		),
		'head3'                          => array(
			'Label Fields',
			'',
			'title',
			'',
		),
		'propercfp_label_name'           => array(
			'Name field label',
			'',
			'text',
			'Your full name'
		),
		'propercfp_label_email'          => array(
			'Email field label',
			'',
			'text',
			'Your email address'
		),
		'propercfp_label_phone'          => array(
			'Phone field label',
			'',
			'text',
			'Your phone number'
		),
		'propercfp_label_reason'         => array(
			'Reason for contacting label',
			'',
			'text',
			'Reason for contacting'
		),
		'propercfp_label_comment'        => array(
			'Comment field label',
			'',
			'text',
			'Question or comment'
		),
		'propercfp_label_math'           => array(
			'Math CAPTCHA label',
			'',
			'text',
			'Solve this equation: '
		),
		'propercfp_label_submit_btn'     => array(
			'Submit button text',
			'',
			'text',
			'Submit'
		),
		'propercfp_label_submit'         => array(
			'Successful form submission text',
			'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.',
			'text',
			'Thank you for your contact!'
		),
		'head4'                          => array(
			'HTML5 validation',
			'',
			'',
			'title',
			'',
		),
		'propercfp_html5_no_validate'    => array(
			'Use HTML5 validation',
			'',
			'checkbox',
			'yes'
		),
		'head5'                          => array(
			'Error Messages',
			'',
			'title',
			'',
		),
		'propercfp_label_err_name'       => array(
			'Name required and missing',
			'',
			'text',
			'Enter your name'
		),
		'propercfp_label_err_email'      => array(
			'E-mail required and missing',
			'',
			'text',
			'Enter a valid email'
		),
		'propercfp_label_err_phone'      => array(
			'EPhone required and missing',
			'',
			'text',
			'Please enter a phone number'
		),
		'propercfp_label_err_no_content' => array(
			'Question/comment is missing',
			'',
			'text',
			'Enter your question or comment'
		),
		'propercfp_label_err_captcha'    => array(
			'Incorrect math CAPTCHA',
			'',
			'text',
			'Check your math ...'
		),
	);
}

function cfp_add_admin() {

	global $current_user;
	get_currentuserinfo();

	$propercfp_options = get_option( 'propercfp_settings_array' );
	$plugin_options    = pirate_forms_plugin_options();

	if (
		// On the right page
		array_key_exists( 'page', $_GET ) &&
		$_GET['page'] === 'pirate-forms-admin' &&
		// We're saving options
		array_key_exists( 'action', $_REQUEST ) &&
		$_REQUEST['action'] == 'save' &&
		// This action is authorized
		current_user_can( 'manage_options' ) &&
		wp_verify_nonce( $_POST['proper_nonce'], $current_user->user_email )
	) {

		foreach ( $plugin_options as $key => $opt ) :
			if ( isset( $_REQUEST[$key] ) ) {
				$opt_data                = filter_var( $_REQUEST[$key], FILTER_SANITIZE_STRING );
				$propercfp_options[$key] = $opt_data;
			}
			else {
				$propercfp_options[$key] = '';
			}
		endforeach;

		update_option( 'propercfp_settings_array', $propercfp_options );
		header( "Location: admin.php?page=pirate-forms-admin&saved=true" );
		die;
	}

	add_submenu_page(
		'options-general.php',
		__( 'Pirate Forms settings', 'proper-contact' ),
		__( 'Pirate Forms', 'proper-contact' ),
		'manage_options',
		'pirate-forms-admin',
		'pirate_forms_admin' );

}

add_action( 'admin_menu', 'cfp_add_admin' );

/*
 *  Admin area setting page for the plugin
 * @since 1.0.0
 *
 */
function pirate_forms_admin() {

	global $current_user;
	get_currentuserinfo();

	$propercfp_options = get_option( 'propercfp_settings_array' );
	$plugin_options    = pirate_forms_plugin_options();
	?>

	<div class="wrap" id="proper-contact-options">

		<h2><?php esc_html_e( 'Pirate Forms Settings','pirate-forms' ); ?></h2>


		<div class="postbox" style="margin-top: 20px; padding: 0 20px">

			<p>Simply configure the form below, save your changes, then add
				<code>[pirate_forms]</code> to any page or post. You can also add a
				<a href="<?php echo admin_url( 'widgets.php' ); ?>">widget</a>.<br>
				If you're adding this to a theme file, add
				<code>&lt;?php echo do_shortcode( '[pirate_forms]' ) ?&gt;</code>
			</p>

		</div>

	<?php if ( ! empty( $_REQUEST['saved'] ) ) : ?>
		<div id="setting-error-settings_updated" class="updated settings-error">
			<p><strong>
					<?php esc_html_e( 'Pirate Forms', 'pirate-forms' ); ?>
					<?php esc_html_e( 'settings saved.', 'pirate-forms' ); ?></strong></p>
		</div>
	<?php endif ?>

	<div class="proper_contact_promo_sidebar">

		<p>
			<a href="http://themeisle.com/" target="_blank">
				<img src="<?php echo PIRATE_FORMS_URL . 'images/logo.png' ?>" alt="Themeisle logo" class="aligncenter">
			</a>
		</p>

	</div>

	<form role="form" method="POST" action="" onSubmit="this.scrollPosition.value=(document.body.scrollTop || document.documentElement.scrollTop)" class="contact-form">

			<input type="hidden" name="scrollPosition">

			<input type="hidden" name="submitted" id="submitted" value="true" />

	<table class="form-table">
	<tr>
		<td>
			<p><input name="save" type="submit" value="Save changes" class="button-primary"></p>
		</td>
	</tr>

	<?php
	foreach ( $plugin_options as $key => $value ) :

		// More clear option names

		// Human-readable name
		$opt_name = $value[0];

		// Machine name as ID
		$opt_id = $key;

		// Description for this field, aka help text
		$opt_desc = $value[1];

		// Input type, set to callback to use a function to build the input
		$opt_type = $value[2];

		// Default value
		$opt_default = $value[3];

		// Value currently saved
		$opt_val = isset( $propercfp_options[$opt_id] ) ? $propercfp_options[$opt_id] : $opt_default;

		// Options if checkbox, select, or radio
		$opt_options = empty( $value[4] ) ? array() : $value[4];

		// Allow for blocks of HTML to be displayed within the settings form
		if ( $opt_type == 'html' ) :
			?>
			<tr>
				<td colspan="2">
					<h4><?php _e( $opt_name, 'proper-contact' ) ?></h4>

					<p class="option_desc"><?php _e( $opt_desc, 'proper-contact' ) ?></p>
				</td>
			</tr>
		<?php

		// Allow titles to be added to deliniate sections
		elseif ( $opt_type == 'title' ) :
			?>

			<tr>
				<th colspan="2" scope="row">
					<hr>
					<h3 class="title"><?php _e( $opt_name, 'proper-contact' ) ?></h3>
				</th>
			</tr>

		<?php

		// Displays correct inputs for "text" type
		elseif ( $opt_type == 'text' || $opt_type == 'number' || $opt_type == 'email' || $opt_type == 'url' ) :
			?>

			<tr>
				<th scope="row">
					<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'proper-contact' ) ?>:</label>
				</th>
				<td>
					<input name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type ?>" value="<?php echo stripslashes( $opt_val ) ?>" class="widefat">

					<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>

				</td>
			</tr>

		<?php

		// Displays correct inputs for "select" type
		elseif ( $opt_type == 'select' ) :
			?>

			<tr>
				<th scope="row">
					<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'proper-contact' ) ?>:</label>
				</th>
				<td>
					<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>">
						<?php
						foreach ( $opt_options as $key => $val ) :

							$selected = '';
							if ( $opt_val == $key )
								$selected = 'selected';
							?>
							<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $val ?></option>
						<?php endforeach; ?>
					</select>

					<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>
				</td>
			</tr>

		<?php

		// Displays correct inputs for "radio" type
		elseif ( $opt_type == 'radio' ) :
			?>

			<tr>
				<th scope="row">
					<span><?php _e( $opt_name, 'proper-contact' ) ?>:</span>
				</th>
				<td>

					<?php
					foreach ( $opt_options as $val ) :

						$checked = '';
						if ( $propercfp_options[$opt_id] == $val )
							$checked = 'checked';
						?>

						<input type="radio" value="<?php echo $val ?>" name="<?php echo $opt_id ?>" id="<?php echo $opt_id . '_' . $val; ?>" <?php echo $checked ?>>
						<label for="<?php echo $opt_id . $val; ?>"><?php echo $val ?></label><br>

						<p class="description"><?php _e( $opt_desc, 'proper-contact' ) ?></p>

					<?php endforeach; ?>
				</td>
			</tr>

		<?php

		// Checkbox input, allows for multiple or single
		elseif ( $opt_type == 'checkbox' ) :
			?>

			<tr>
				<th scope="row">
					<span><?php _e( $opt_name, 'pirate-forms' ) ?>:</span>
				</th>
				<td>
					<?php
					// If we have multiple checkboxes to show
					if ( ! empty( $opt_options ) ) :
						for ( $i = 0; $i < count( $opt_options ); $i ++ ) :

							// Need to mark current options as checked
							$checked = '';
							if ( in_array( $opt_options[$i], $propercfp_options[$opt_id] ) )
								$checked = 'checked';
							?>
							<p>
								<input type="checkbox" value="<?php echo $opt_options[$i] ?>" name="<?php echo $opt_id ?>[]" id="<?php echo $opt_id . '_' . $i ?>" <?php echo $checked ?>>
								<label for="<?php echo $opt_id . '_' . $i ?>"><?php echo $opt_options[$i] ?></label>
							</p>
						<?php
						endfor;

					// Single "on-off" checkbox
					else :
						$checked = '';
						if ( $opt_val == 'yes' )
							$checked = 'checked';
						?>

						<input type="checkbox" value="yes" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" <?php echo $checked ?>>
						<label for="<?php echo $opt_id ?>">Yes</label>

					<?php endif; ?>
					<p class="description"><?php _e( $opt_desc, 'pirate-forms' ) ?></p>
				</td>
			</tr>

		<?php

		// Displays input for "textarea" type
		elseif ( $opt_type == 'textarea' ) :
			?>
			<tr>
				<th scope="row">
					<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'pirate-forms' ) ?>:</label>
				</th>
				<td>
					<textarea rows="6" cols="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" class="large-text"><?php echo stripslashes( $opt_val ) ?></textarea>

					<p class="description"><?php _e( $opt_desc, 'pirate-forms' ) ?></p>
				</td>
			</tr>

		<?php
		endif;

	endforeach;
	?>
	<tr>
		<td colspan="2">
			<p>
				<input name="save" type="submit" value="<?php _e( 'Save changes', 'pirate-forms' ) ?>" class="button-primary">
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="proper_nonce" value="<?php echo wp_create_nonce( $current_user->user_email ) ?>">
			</p>

		</td>
	</tr>
	</table>
	</form>

	</div>

<?php
}

/**
 * Save default options if none exist
 */
function proper_contact_form_settings_init() {

	if ( ! get_option( 'propercfp_settings_array' ) ) {

		$new_opt = array();

		foreach ( pirate_forms_plugin_options() as $key => $opt ) {
			$new_opt[$key] = $opt[3];
		}

		update_option( 'propercfp_settings_array', $new_opt );

	}

}

add_action( 'admin_head', 'proper_contact_form_settings_init' );

/**
 * Add a settings link to the plugin listing
 */
function proper_contact_form_plugin_links( $links ) {

	$settings_link = '<a href="' . admin_url( 'options-general.php?page=pirate-forms-admin' ) . '">' .
		__( 'Settings', 'pirate-forms' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_proper-contact-form/proper-contact-form.php',
	'proper_contact_form_plugin_links', 10, 2 );

/**
 * Enqueue CSS and JS needed in the admin
 */
function proper_contact_admin_css_js() {
	global $pagenow;

	if (
		( $pagenow == 'options-general.php' || $pagenow == 'admin.php' )
		&& isset( $_GET['page'] ) && $_GET['page'] == 'pirate-forms-admin'
	) {
		wp_enqueue_style( 'proper-contact', PIRATE_FORMS_URL . 'css/wp-admin.css' );
	}
}

add_action( 'admin_enqueue_scripts', 'proper_contact_admin_css_js' );