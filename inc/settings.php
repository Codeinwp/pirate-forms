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
				'req',
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
				'req',
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
				'req',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Message */
			'pirateformsopt_message_field' => array(
				__( 'Message','pirate-forms' ),
				'',
				'select',
				'req',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Recaptcha */
			'pirateformsopt_recaptcha_field' => array(
				__( 'Add a reCAPTCHA','pirate-forms' ),
				'',
				'checkbox',
				'',
			),
		),
		'second_tab' => array(
			'header_labels' => array(
				__( 'Labels','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_label_name' => array(
				__( 'Name','pirate-forms' ),
				'',
				'text',
				__( 'Your Name','pirate-forms' ),
			),
			'pirateformsopt_label_email' => array(
				__( 'Email','pirate-forms' ),
				'',
				'text',
				__( 'Your Email','pirate-forms' )
			),
			'pirateformsopt_label_subject' => array(
				__( 'Subject','pirate-forms' ),
				'',
				'text',
				__( 'Subject','pirate-forms' )
			),
			'pirateformsopt_label_message' => array(
				__( 'Message','pirate-forms' ),
				'',
				'text',
				__( 'Your message','pirate-forms' )
			),
			'pirateformsopt_label_submit_btn' => array(
				__( 'Submit button','pirate-forms' ),
				'',
				'text',
				__( 'Send Message','pirate-forms' )
			)
		),
		'third_tab' => array(
			'header_messages' => array(
				__( 'Messages','pirate-forms' ),
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
			'pirateformsopt_label_submit' => array(
				__( 'Successful form submission text','pirate-forms' ),
				__( 'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.','pirate-forms' ),
				'text',
				__( 'Thank you for your contact!','pirate-forms' )
			)
		),
		'fourth_tab' => array(
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

	if( !empty($propercfp_options) ):
		parse_str($propercfp_options, $pirate_forms_params);
	endif;

	$plugin_options = pirate_forms_plugin_options();
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Pirate Forms','pirate-forms' ); ?></h1>



		<ul class="pirate-forms-nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#0" aria-controls="how_to_use" role="tab" data-toggle="tab"><?php esc_html_e( 'How to use','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#1" aria-controls="fields" role="tab" data-toggle="tab"><?php esc_html_e( 'Fields','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#2" aria-controls="labels" role="tab" data-toggle="tab"><?php esc_html_e( 'Labels','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#3" aria-controls="messages" role="tab" data-toggle="tab"><?php esc_html_e( 'Messages','pirate-forms'); ?></a></li>
			<li role="presentation"><a href="#4" aria-controls="options" role="tab" data-toggle="tab"><?php esc_html_e( 'Options','pirate-forms'); ?></a></li>
		</ul>

		<div class="pirate-forms-tab-content">

			<div id="0" class="pirate-forms-tab-pane active">

				<p><?php esc_html_e( 'Welcome to Pirate Forms!','pirate-forms' ); ?><p>
				<p><?php esc_html_e( 'To get started, just configure all the options you need, hit save and start using the created form.','pirate-forms' ); ?><p>

				<hr>

				<p><?php esc_html_e( 'There are 3 ways of using the newly created form:','pirate-forms' ); ?><p>
				<p><?php esc_html_e( '1. Use the shortcode ','pirate-forms' ); ?><code>[pirate_forms] </code><?php esc_html_e( 'in any page or post.','pirate-forms' ); ?><p>
				<p><?php esc_html_e( '2. Add a ','pirate-forms' ); ?><a href="<?php echo admin_url( 'widgets.php' ); ?>"><?php esc_html_e( 'widget','pirate-forms' ); ?></a><p>
				<p><?php esc_html_e( '3. Use the shortcode ','pirate-forms' ); ?><code>&lt;?php echo do_shortcode( '[pirate_forms]' ) ?&gt;</code><?php esc_html_e( 'in the theme\'s files.','pirate-forms' ); ?><p>

			</div>

			<?php

			$pirate_forms_nr_tabs = 1;

			foreach ( $plugin_options as $plugin_options_tab ) :

				echo '<div id="'.$pirate_forms_nr_tabs.'" class="pirate-forms-tab-pane">';

				?>
				<form method="post" class="pirate_forms_contact_settings">

					<?php
					$pirate_forms_nr_tabs++;
					foreach ( $plugin_options_tab as $key => $value ) :

						/* Label */
						if( !empty($value[0]) ):
							$opt_name = $value[0];
						endif;

						/* ID */
						$opt_id = $key;

						/* Description */
						if( !empty($value[1]) ):
							$opt_desc = $value[1];
						endif;

						/* Input type */
						if( !empty($value[2]) ):
							$opt_type = $value[2];
						endif;

						/* Default value */
						if( !empty($value[3]) ):
							$opt_default = $value[3];
						endif;

						/* Value */
						$opt_val = isset( $pirate_forms_params[$opt_id] ) ? $pirate_forms_params[$opt_id] : $opt_default;

						/* Options if checkbox, select, or radio */
						$opt_options = empty( $value[4] ) ? array() : $value[4];

						switch ($opt_type) {
							case "title":

								if( !empty($opt_name) ):
									echo '<h3 class="title">'.$opt_name.'</h3><hr />';
								endif;

								break;

							case "text":
								?>

								<div class="pirate-forms-grouped">
									<label for="<?php echo $opt_id; ?>"><?php echo $opt_name; ?><span><?php echo $opt_desc; ?></span></label>
									<input name="<?php echo $opt_id; ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type; ?>" value="<?php echo stripslashes( $opt_val ); ?>" class="widefat">
								</div>

								<?php
								break;
							case "select":
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
											<option value="<?php echo $key ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
										<?php endforeach; ?>
									</select>


								</div>

							<?php
								break;
							case "checkbox":
								?>
								<div class="pirate-forms-grouped">
									<label for="<?php echo $opt_id; ?>"><?php echo $opt_name.'<br>'.'<span>'.$opt_desc.'</span>'; ?></label>

									<?php

										$checked = '';
										if ( $opt_val == 'yes' )
											$checked = 'checked';
										?>

										<input type="checkbox" value="yes" name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>" <?php echo $checked; ?>>Yes

								</div>
								<hr>

							<?php
								break;
						}

					endforeach;
					?>
					<input name="save" type="submit" value="<?php _e( 'Save changes', 'pirate-forms' ) ?>" class="button-primary pirate-forms-save-button">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="proper_nonce" value="<?php echo wp_create_nonce( $current_user->user_email ) ?>">

				</form><!-- .pirate_forms_contact_settings -->
				<div class="ajaxAnimation"></div>
			</div><!-- .pirate-forms-tab-pane -->

			<?php endforeach; ?>

		</div><!-- .pirate-forms-tab-content -->


	</div><!-- .wrap -->

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