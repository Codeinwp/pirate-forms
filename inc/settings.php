<?php
/*
 *
 * OPTIONS
 * @since 1.0.0
 * name; id; desc; type; default; options
 *
 */
function pirate_forms_plugin_options() {
	return array(
		'first_tab' => array(
			'header_fields' => array(
				__( 'Fields','pirate-forms' ),
				'',
				'title',
				'',
			),
			/* Name */
			'pirateformsopt_name_field' => array(
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
			/* Email */
			'pirateformsopt_email_field' => array(
				__( 'Email address','pirate-forms' ),
				__( 'Do you want the email address field be displayed?','pirate-forms' ),
				'select',
				'yes',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Subject */
			'pirateformsopt_subject_field' => array(
				__( 'Subject','pirate-forms' ),
				__( 'Do you want the subject field be displayed?','pirate-forms' ),
				'select',
				'yes',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Message */
			'pirateformsopt_reason' => array(
				__( 'Message','pirate-forms' ),
				'',
				'textarea',
				'',
			),
			/* Recaptcha */
			'pirateformsopt_recaptcha_field' => array(
				__( 'Add a reCAPTCHA','pirate-forms' ),
				__( 'Checking this box will add a reCAPTCHA to the form to discourage spam','pirate-forms' ),
				'checkbox',
				'',
			),
		),
		'second_tab' => array(
			'header_options' => array(
				__( 'Form processing options','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_email' => array(
				__( 'Contact notification sender email','pirate-forms' ),
				__( 'Email to use for the sender of the contact form emails both to the recipients below and the contact form submitter (if this is activated below). The domain for this email address should match your site\'s domain.',
					'email','pirate-forms' ),
				get_bloginfo( 'admin_email' )
			),
			'pirateformsopt_reply_to_admin' => array(
				__( 'Use the email address above as notification sender','pirate-forms' ),
				__( 'When this is on, the notification emails sent from your site will come from the email address above. When this is off, the emails will come from the form submitter, making it easy to reply. If you are not receiving notifications from the site, then turn this option off as your email server might be marking them as spam.','pirate-forms' ),
				'checkbox',
				'',
			),
			'pirateformsopt_email_recipients' => array(
				__( 'Contact submission recipients','pirate-forms' ),
				__( 'Email address(es) to receive contact submission notifications. You can separate multiple emails with a comma.','pirate-forms' ),
				'text',
				proper_contact_get_key( 'pirateformsopt_email' ) ? proper_contact_get_key( 'pirateformsopt_email' ) : get_bloginfo( 'admin_email' )
			),
			'pirateformsopt_result_url' => array(
				__( '"Thank You" URL','pirate-forms' ),
				__( 'Select the post-submit page for all forms submitted','pirate-forms' ),
				'select',
				'',
				proper_get_content_array()
			),
			'pirateformsopt_css' => array(
				__( 'Add styles to the site','pirate-forms' ),
				__( 'Checking this box will add styles to the form. By default, this is off so you can add your own styles.','pirate-forms' ),
				'checkbox',
				'',
			),
			'pirateformsopt_store' => array(
				__( 'Store submissions in the database','pirate-forms' ),
				__( 'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved in Contacts on the left (appears after this option is activated).','pirate-forms' ),
				'checkbox',
				'',
			),
			'pirateformsopt_blacklist' => array(
				__( 'Use the comments blacklist to restrict submissions','pirate-forms' ),
				__( 'Should form submission IP and email addresses be compared against the Comment Blacklist, found in','pirate-forms').'<strong>'.__('wp-admin > Settings > Discussion > Comment Blacklist?','pirate-forms').'</strong>',
				'checkbox',
				'yes',
			),
			'pirateformsopt_confirm_email' => array(
				__( 'Send email confirmation to form submitter','pirate-forms' ),
				__( 'Adding text here will send an email to the form submitter. The email uses the "Text to show when form is submitted..." field below as the subject line. Plain text only here, no HTML.','pirate-forms' ),
				'textarea',
				'',
			),
		),
		'third_tab' => array(
			'header_labels' => array(
				__( 'Label Fields','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_label_name' => array(
				__( 'Name field label','pirate-forms' ),
				'',
				'text',
				__( 'Your full name','pirate-forms' ),
			),
			'pirateformsopt_label_email' => array(
				__( 'Email field label','pirate-forms' ),
				'',
				'text',
				__( 'Your email address','pirate-forms' )
			),
			'pirateformsopt_label_subject' => array(
				__( 'Subject field label','pirate-forms' ),
				'',
				'text',
				__( 'Subject','pirate-forms' )
			),
			'pirateformsopt_label_reason' => array(
				__( 'Reason for contacting label','pirate-forms' ),
				'',
				'text',
				__( 'Reason for contacting','pirate-forms' )
			),
			'pirateformsopt_label_comment' => array(
				__( 'Comment field label','pirate-forms' ),
				'',
				'text',
				__( 'Question or comment','pirate-forms' )
			),
			'pirateformsopt_label_submit_btn' => array(
				__( 'Submit button text','pirate-forms' ),
				'',
				'text',
				__( 'Submit','pirate-forms' )
			),
			'pirateformsopt_label_submit' => array(
				__( 'Successful form submission text','pirate-forms' ),
				__( 'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.','pirate-forms' ),
				'text',
				__( 'Thank you for your contact!','pirate-forms' )
			),
		),
		'fourth_tab' => array(
			'header_html5_validation' => array(
				__( 'HTML5 validation','pirate-forms' ),
				'',
				'',
				'title',
				'',
			),
			'pirateformsopt_html5_no_validate' => array(
				__( 'Use HTML5 validation','pirate-forms' ),
				'',
				'checkbox',
				__( 'yes','pirate-forms' )
			),
		),
		'fifth_tab' => array(
			'header_error_messages' => array(
				__( 'Error Messages','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_label_err_name' => array(
				__( 'Name required and missing','pirate-forms' ),
				'',
				'text',
				__( 'Enter your name','pirate-forms' )
			),
			'pirateformsopt_label_err_email' => array(
				__( 'E-mail required and missing','pirate-forms' ),
				'',
				'text',
				__( 'Enter a valid email','pirate-forms' )
			),
			'pirateformsopt_label_err_subject' => array(
				__( 'Subject required and missing','pirate-forms' ),
				'',
				'text',
				__( 'Please enter a subject','pirate-forms' )
			),
			'pirateformsopt_label_err_no_content' => array(
				__( 'Question/comment is missing','pirate-forms' ),
				'',
				'text',
				__( 'Enter your question or comment','pirate-forms' )
			),
			'pirateformsopt_label_err_captcha' => array(
				__( 'Incorrect math CAPTCHA','pirate-forms' ),
				'',
				'text',
				__( 'Check your math ...','pirate-forms' )
			)
		)
	);
}

/*
 *
 *  Add page to the dashbord menu
 *  @since 1.0.0
 */
function pirate_forms_add_to_admin() {

	add_submenu_page(
		'options-general.php',
		__( 'Pirate Forms settings', 'pirate-forms' ),
		__( 'Pirate Forms', 'pirate-forms' ),
		'manage_options',
		'pirate-forms-admin',
		'pirate_forms_admin' );

}
add_action( 'admin_menu', 'pirate_forms_add_to_admin' );

/*
 *
 *  Save forms via Ajax
 *  @since 1.0.0
 *
 */
add_action('wp_ajax_pirate_forms_save', 'pirate_forms_save_callback');
add_action('wp_ajax_nopriv_pirate_forms_save', 'pirate_forms_save_callback');

function pirate_forms_save_callback() {

	if( isset($_POST['dataSent']) ):
		$dataSent = $_POST['dataSent'];
		update_option( 'propercfp_settings_array', $dataSent );
	endif;
	die();
}

/*
 *  Admin area setting page for the plugin
 * @since 1.0.0
 *
 */
function pirate_forms_admin() {

	$propercfp_options = get_option( 'propercfp_settings_array' );

	$pirate_forms_params = array();
	parse_str($propercfp_options, $pirate_forms_params);

	$plugin_options = pirate_forms_plugin_options();
	?>

	<div class="wrap">

		<h2><?php esc_html_e( 'Pirate Forms Settings','pirate-forms' ); ?></h2>

		<div class="pirate-forms-postbox">

			<p><?php esc_html_e( 'Simply configure the form below, save your changes, then add','pirate-forms' ); ?>
				<code>[pirate_forms] </code><?php esc_html_e( 'to any page or post.','pirate-forms' ); ?><br><?php esc_html_e('You can also add a','pirate-forms' ); ?>
				<a href="<?php echo admin_url( 'widgets.php' ); ?>"><?php esc_html_e( 'widget','pirate-forms' ); ?></a>.<br>
				<?php esc_html_e( 'If you\'re adding this to a theme file, add','pirate-forms' ); ?>
				<code>&lt;?php echo do_shortcode( '[pirate_forms]' ) ?&gt;</code>
			</p>

		</div>

		<ul class="pirate-forms-nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#1" aria-controls="fields" role="tab" data-toggle="tab"><?php esc_html_e( 'Fields','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#2" aria-controls="options" role="tab" data-toggle="tab"><?php esc_html_e( 'Options','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#3" aria-controls="labels" role="tab" data-toggle="tab"><?php esc_html_e( 'Labels','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#4" aria-controls="html5_validation" role="tab" data-toggle="tab"><?php esc_html_e( 'HTML5 validation','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#5" aria-controls="error_messages" role="tab" data-toggle="tab"><?php esc_html_e( 'Erorr messages','pirate-forms'); ?></a></li>

		</ul>

		<div class="pirate-forms-tab-content">

		<?php

		$pirate_forms_nr_tabs = 1;

		foreach ( $plugin_options as $plugin_options_tab ) :

			if($pirate_forms_nr_tabs == 1):
				echo '<div id="'.$pirate_forms_nr_tabs.'" class="pirate-forms-tab-pane active">';
			else:
				echo '<div id="'.$pirate_forms_nr_tabs.'" class="pirate-forms-tab-pane">';
			endif;

			?>
			<form method="post" class="pirate_forms_contact_settings">
				<input name="save" type="submit" value="Save changes" class="button-primary pirate-forms-save-button">
				<?php
				$pirate_forms_nr_tabs++;
				foreach ( $plugin_options_tab as $key => $value ) :

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

					$opt_val = isset( $pirate_forms_params[$opt_id] ) ? $pirate_forms_params[$opt_id] : $opt_default;

					// Options if checkbox, select, or radio
					$opt_options = empty( $value[4] ) ? array() : $value[4];

					// Allow for blocks of HTML to be displayed within the settings form
					if ( $opt_type == 'html' ) :
						?>

								<h4><?php echo $opt_name; ?></h4>

								<p class="option_desc"><?php _e( $opt_desc, 'pirate-forms' ) ?></p>

						<?php
					elseif ( $opt_type == 'title' ) :
						?>
						<h3 class="title"><?php echo $opt_name; ?></h3>
						<hr>
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
							<div class="pirate-forms-grouped">

								<label for="<?php echo $opt_id ?>"><?php echo $opt_name.'<br>'.'<span>'.$opt_desc.'</span>'; ?></label>

								<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id; ?>">
									<?php
									foreach ( $opt_options as $key => $val ) :

										$selected = '';
										if ( $opt_val == $key )
											$selected = 'selected';
										?>
										<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $val ?></option>
									<?php endforeach; ?>
								</select>


							</div>

						<?php


					elseif ( $opt_type == 'checkbox' ) :
						?>

								<span><?php echo $opt_name; ?>:</span>

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


						<?php

					// Displays input for "textarea" type
					elseif ( $opt_type == 'textarea' ) :
						?>

								<label for="<?php echo $opt_id ?>"><?php _e( $opt_name, 'pirate-forms' ) ?>:</label>

								<textarea rows="6" cols="60" name="<?php echo $opt_id ?>" id="<?php echo $opt_id ?>" class="large-text"><?php echo stripslashes( $opt_val ) ?></textarea>

								<p class="description"><?php _e( $opt_desc, 'pirate-forms' ) ?></p>

						<?php
					endif;

				endforeach;
				?>
				<input name="save" type="submit" value="<?php _e( 'Save changes', 'pirate-forms' ) ?>" class="button-primary pirate-forms-save-button">
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="proper_nonce" value="<?php echo wp_create_nonce( $current_user->user_email ) ?>">
				<?php
				echo '</form>';
				echo '</div>';



				endforeach;
				echo '<div class="ajaxAnimation" style="display:none;"></div>';
				echo '</div>'/* .zerif-lite-tab-pane */;

				?>

		</div>

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