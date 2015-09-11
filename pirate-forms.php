<?php

/*
Plugin Name: Pirate Forms
Plugin URI: http://themeisle.com/plugins/pirate-forms/
Description: A simple, nice looking contact form
Version: 1.0.0
Author: Themeisle
Author URI: http://themeisle.com
License: GPL2
*/

if ( ! function_exists( 'add_action' ) ) {
	die( 'Nothing to do...' );
}

/* Important constants */
define( 'PIRATE_FORMS_VERSION', '1.0.0' );
define( 'PIRATE_FORMS_URL', plugin_dir_url( __FILE__ ) );

/* Required helper functions */
include_once( dirname( __FILE__ ) . '/inc/helpers.php' );
include_once( dirname( __FILE__ ) . '/inc/settings.php' );
include_once( dirname( __FILE__ ) . '/inc/widget.php' );

wp_enqueue_script( 'pirate_forms_scripts', plugins_url( 'js/scripts.js', __FILE__ ), array('jquery') );
wp_localize_script( 'pirate_forms_scripts', 'cwp_top_ajaxload', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

/**
 * Display the contact form or a confirmation message is submitted
 *
 * @param      $atts
 * @param null $content
 *
 * @return string
 */

add_shortcode( 'pirate_forms', 'pirate_forms_display_form' );
function pirate_forms_display_form( $atts, $content = NULL ) {

	// Looking for a submitted form if not redirect
	if ( isset( $_GET['pcf'] ) && $_GET['pcf'] == 1 ) {
		return '
		<div class="proper_contact_thankyou_wrap">
			<h2>' . sanitize_text_field( pirate_forms_get_key( 'pirateformsopt_label_submit' ) ) . '</h2>
		</div>';
	}

	/*********************************/
	/********** FormBuilder **********/
	/*********************************/

	if ( !class_exists('PhpFormBuilder')) {
		require_once( dirname( __FILE__ ) . '/inc/PhpFormBuilder.php' );
	}

	$pirate_form = new PhpFormBuilder();

	$pirate_form->set_att( 'id', 'pirate_forms_' . ( get_the_id() ? get_the_id() : 1 ) );
	$pirate_form->set_att( 'class', array( 'pirate_forms' ) );
	$pirate_form->set_att( 'add_nonce', get_bloginfo( 'admin_email' ) );

	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	if( !empty($pirate_forms_options) ):

		$pirate_forms_params = array();

		parse_str((string)$pirate_forms_options, $pirate_forms_params);

		if( !empty($pirate_forms_params) ):

			/******************************/
			/********  Name field *********/
			/******************************/

			if( !empty($pirate_forms_params['pirateformsopt_name_field']) && !empty($pirate_forms_params['pirateformsopt_label_name']) ):

				$pirateformsopt_name_field = $pirate_forms_params['pirateformsopt_name_field'];
				$pirateformsopt_name_label = $pirate_forms_params['pirateformsopt_label_name'];

				if ( !empty($pirateformsopt_name_field) && !empty($pirateformsopt_name_label) ):

					$required     = $pirateformsopt_name_field === 'req' ? TRUE : FALSE;
					$wrap_classes = array( 'form_field_wrap', 'contact_name_wrap col-lg-4 col-sm-4' );

					// If this field was submitted with invalid data
					if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-name'] ) ) {
						$wrap_classes[] = 'error';
					}

					$pirate_form->add_input(
						'',
						array(
							'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_name_label) ),
							'required'   => $required,
							'wrap_class' => $wrap_classes,
						),
						'pirate-forms-contact-name'
					);

				endif;
			endif;

			/********************************/
			/********  Email field **********/
			/********************************/

			if( !empty($pirate_forms_params['pirateformsopt_email_field']) && !empty($pirate_forms_params['pirateformsopt_label_email']) ):

				$pirateformsopt_email_field = $pirate_forms_params['pirateformsopt_email_field'];
				$pirateformsopt_email_label = $pirate_forms_params['pirateformsopt_label_email'];

				if ( !empty($pirateformsopt_email_field) && !empty($pirateformsopt_email_label) ):

					$required     = $pirateformsopt_email_field === 'req' ? TRUE : FALSE;
					$wrap_classes = array( 'form_field_wrap', 'contact_email_wrap col-lg-4 col-sm-4' );

					// If this field was submitted with invalid data
					if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-email'] ) ) {
						$wrap_classes[] = 'error';
					}

					$pirate_form->add_input(
						'',
						array(
							'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_email_label) ),
							'required'   => $required,
							'type'       => 'email',
							'wrap_class' => $wrap_classes,
						),
						'pirate-forms-contact-email'
					);

				endif;
			endif;

			/********************************/
			/********  Subject field ********/
			/********************************/

			if( !empty($pirate_forms_params['pirateformsopt_subject_field']) && !empty($pirate_forms_params['pirateformsopt_label_subject']) ):

				$pirateformsopt_subject_field = $pirate_forms_params['pirateformsopt_subject_field'];
				$pirateformsopt_subject_label = $pirate_forms_params['pirateformsopt_label_subject'];

				if ( !empty($pirateformsopt_subject_field) && !empty($pirateformsopt_subject_label) ):

					$required     = $pirateformsopt_subject_field === 'req' ? TRUE : FALSE;
					$wrap_classes = array( 'form_field_wrap', 'contact_subject_wrap col-lg-4 col-sm-4' );

					// If this field was submitted with invalid data
					if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-subject'] ) ) {
						$wrap_classes[] = 'error';
					}

					$pirate_form->add_input(
						'',
						array(
							'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_subject_label) ),
							'required'   => $required,
							'wrap_class' => $wrap_classes,
						),
						'pirate-forms-contact-subject'
					);

				endif;
			endif;

			/********************************/
			/********  Message field ********/
			/********************************/

			if( !empty($pirate_forms_params['pirateformsopt_message_field']) && !empty($pirate_forms_params['pirateformsopt_label_message']) ):

				$pirateformsopt_message_field = $pirate_forms_params['pirateformsopt_message_field'];
				$pirateformsopt_message_label = $pirate_forms_params['pirateformsopt_label_message'];

				if ( !empty($pirateformsopt_message_field) && !empty($pirateformsopt_message_label) ):


					$required     = $pirateformsopt_message_field === 'req' ? TRUE : FALSE;
					$wrap_classes = array( 'form_field_wrap', 'contact_message_wrap col-lg-12 col-sm-12' );

					// If this field was submitted with invalid data
					if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-message'] ) ) {
						$wrap_classes[] = 'error';
					}

					$pirate_form->add_input(
						'',
						array(
							'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_message_label) ),
							'required'   => $required,
							'wrap_class' => $wrap_classes,
							'type' => 'textarea'
						),
						'pirate-forms-contact-message'
					);

				endif;
			endif;

			/********************************/
			/********  Submit button ********/
			/********************************/

			if( !empty($pirate_forms_params['pirateformsopt_label_submit_btn']) ):

				$pirateformsopt_label_submit_btn = $pirate_forms_params['pirateformsopt_label_submit_btn'];

				if ( !empty($pirateformsopt_label_submit_btn) ):

					$wrap_classes = array( 'form_field_wrap', 'contact_submit_wrap' );

					$pirate_form->add_input(
						'',
						array(
							'value' => stripslashes( sanitize_text_field($pirateformsopt_label_submit_btn) ),
							'wrap_class' => $wrap_classes,
							'type' => 'submit'
						),
						'pirate-forms-contact-submit'
					);

				endif;
			endif;

			/******************************/
			/********* ReCaptcha **********/
			/******************************/

			if( !empty($pirate_forms_params['pirateformsopt_recaptcha_secretkey']) && !empty($pirate_forms_params['pirateformsopt_recaptcha_sitekey']) && !empty($pirate_forms_params['pirateformsopt_recaptcha_field']) && ($pirate_forms_params['pirateformsopt_recaptcha_field'] == 'yes') ):

				$pirateformsopt_recaptcha_sitekey = $pirate_forms_params['pirateformsopt_recaptcha_sitekey'];
				$pirateformsopt_recaptcha_secretkey = $pirate_forms_params['pirateformsopt_recaptcha_secretkey'];

				echo '<div class="g-recaptcha pirate-forms-g-recaptcha" data-sitekey="' . $pirateformsopt_recaptcha_sitekey . '"></div>';

			endif;


		endif;

	endif;

	/* Referring site or page, if any */
	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$pirate_form->add_input(
			__( 'Contact Referrer','pirate-forms' ),
			array(
				'type'  => 'hidden',
				'value' => $_SERVER['HTTP_REFERER']
			)
		);
	}

	/* Referring page, if sent via URL query */
	if ( ! empty( $_REQUEST['src'] ) || ! empty( $_REQUEST['ref'] ) ) {
		$pirate_form->add_input(
			__( 'Referring page','pirate-forms' ),
			array(
				'type'  => 'hidden',
				'value' => ! empty( $_REQUEST['src'] ) ? $_REQUEST['src'] : $_REQUEST['ref']
			)
		);
	}

	// Are there any submission errors?
	$errors = '';
	if ( ! empty( $_SESSION['pirate_forms_contact_errors'] ) ) {
		$errors = proper_display_errors( $_SESSION['pirate_forms_contact_errors'] );
		unset( $_SESSION['pirate_forms_contact_errors'] );
	}

	// Display that beautiful form!
	return '
	<div class="pirate_forms_wrap">
	' . $errors . '
	' . $pirate_form->build_form( FALSE ) . '
	</div>';

}

/**
 * Process the incoming contact form data, if any
 */
add_action( 'template_redirect', 'pirate_forms_process_contact' );
function pirate_forms_process_contact() {

	// If POST, nonce and honeypot are not set, beat it
	if ( empty( $_POST ) || empty( $_POST['wordpress-nonce'] ) || !isset( $_POST['honeypot'] )) {
		return false;
	}

	// Session variable for form errors
	$_SESSION['pirate_forms_contact_errors'] = array();

	// If nonce is not valid, beat it
	if ( ! wp_verify_nonce( $_POST['wordpress-nonce'], get_bloginfo( 'admin_email' ) ) ) {
		$_SESSION['pirate_forms_contact_errors']['nonce'] = __( 'Nonce failed!', 'pirate-forms' );
		return false;
	}

	// If the honeypot caught a bear, beat it
	if ( ! empty( $_POST['honeypot'] ) ) {
		$_SESSION['pirate_forms_contact_errors']['honeypot'] = __( 'Form submission failed!', 'pirate-forms' );
		return false;
	}

	// Start the body of the contact email
	$body = "*** " . __( 'Contact form submission on', 'pirate-forms' ) . " " .
		get_bloginfo( 'name' ) . " (" . site_url() . ") *** \n\n";


	/***********************************************/
	/*********   Sanitize and validate name *******/
	/**********************************************/

	$pirate_forms_contact_name = isset( $_POST['pirate-forms-contact-name'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-name'] ) ) : '';

	// if name is required and is missing
	if ( (pirate_forms_get_key( 'pirateformsopt_name_field' ) === 'req') && empty( $pirate_forms_contact_name ) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-name'] = pirate_forms_get_key( 'pirateformsopt_label_err_name' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_name ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_name' ) ) . ": $pirate_forms_contact_name \r";
	}


	/***********************************************/
	/*******  Sanitize and validate email **********/
	/***********************************************/
	
	$pirate_forms_contact_email = isset( $_POST['pirate-forms-contact-email'] ) ?
			sanitize_email( $_POST['pirate-forms-contact-email'] ) : '';

	// If required, is it valid?
	if ( (pirate_forms_get_key( 'pirateformsopt_email_field' ) === 'req') && ! filter_var( $pirate_forms_contact_email, FILTER_VALIDATE_EMAIL )) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-email'] = pirate_forms_get_key( 'pirateformsopt_label_err_email' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_email ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_email' ) )
				. ": $pirate_forms_contact_email \r"
				. __( 'Google it', 'proper-contact' )
				. ": https://www.google.com/#q=$pirate_forms_contact_email \r";
	}

	/***********************************************/
	/*********   Sanitize and validate subject *****/
	/**********************************************/

	$pirate_forms_contact_subject = isset( $_POST['pirate-forms-contact-subject'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-subject'] ) ) : '';

	// if subject is required and is missing
	if ( (pirate_forms_get_key( 'pirateformsopt_subject_field' ) === 'req') && empty( $pirate_forms_contact_subject ) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-subject'] = pirate_forms_get_key( 'pirateformsopt_label_err_subject' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_subject ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_subject' ) ) . ": $pirate_forms_contact_subject \r";
	}

	/***********************************************/
	/*********   Sanitize and validate message *****/
	/**********************************************/

	$pirate_forms_contact_message = isset( $_POST['pirate-forms-contact-message'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-message'] ) ) : '';

	// if message is required and is missing
	if ( (pirate_forms_get_key( 'pirateformsopt_message_field' ) === 'req') && empty( $pirate_forms_contact_message ) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-message'] = pirate_forms_get_key( 'pirateformsopt_label_err_message' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_message ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_message' ) ) . ": $pirate_forms_contact_message \r";
	}

	/**********************************************/
	/********   Sanitize and validate IP  *********/
	/**********************************************/

	$contact_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );

	// If valid and present, create a link to an IP search
	if ( ! empty( $contact_ip ) ) {
		$body .= "IP address: $contact_ip \r IP search: http://whatismyipaddress.com/ip/$contact_ip \n\n";
	}

	// Sanitize and prepare referrer;
	if ( ! empty( $_POST['contact-referrer'] ) ) {
		$body .= "Came from: " . sanitize_text_field( $_POST['pirate-forms-contact-referrer'] ) . " \r";
	}

	// Show the page this contact form was submitted on
	$body .= 'Sent from page: ' . get_permalink( get_the_id() );

	// Check the blacklist
	$blocked = proper_get_blacklist();
	if ( ! empty( $blocked ) ) {
		if (
				in_array( $pirate_forms_contact_email, $blocked ) ||
				in_array( $contact_ip, $blocked )
		) {
			$_SESSION['pirate_forms_contact_errors']['blacklist-blocked'] = __( 'Form submission blocked!','pirate-forms' );
			return false;
		}
	}

	// No errors? Go ahead and process the contact
	if ( empty( $_SESSION['pirate_forms_contact_errors'] ) ) {

		$site_email = sanitize_email( pirate_forms_get_key( 'pirateformsopt_email' ) );
		$site_name  = htmlspecialchars_decode( get_bloginfo( 'name' ) );

		// Notification recipients
		$site_recipients = sanitize_text_field( pirate_forms_get_key( 'pirateformsopt_email_recipients' ) );
		$site_recipients = explode(',', $site_recipients);
		$site_recipients = array_map( 'trim', $site_recipients );
		$site_recipients = array_map( 'sanitize_email', $site_recipients );
		$site_recipients = implode( ',', $site_recipients );

		// No name? Use the submitter email address, if one is present
		if ( empty( $pirate_forms_contact_name ) ) {
			$pirate_forms_contact_name = ! empty( $pirate_forms_contact_email ) ? $pirate_forms_contact_email : '[None given]';
		}

		// Need an email address for the email notification
		if ( pirate_forms_get_key( 'pirateformsopt_reply_to_admin' ) == 'yes' ) {
			$send_from = $site_email;
			$send_from_name = $site_name;
		} else {
			$send_from = ! empty( $pirate_forms_contact_email ) ? $pirate_forms_contact_email : $site_email;
			$send_from_name = $pirate_forms_contact_name;
		}

		// Sent an email notification to the correct address
		$headers   = "From: $send_from_name <$send_from>\r\nReply-To: $send_from_name <$send_from>";

		wp_mail( $site_recipients, 'Contact on ' . $site_name, $body, $headers );

		// Should a confirm email be sent?
		$confirm_body = stripslashes( trim( pirate_forms_get_key( 'pirateformsopt_confirm_email' ) ) );
		if ( ! empty( $confirm_body ) && ! empty( $pirate_forms_contact_email ) ) {

			// Removing entities
			$confirm_body = htmlspecialchars_decode( $confirm_body );
			$confirm_body = html_entity_decode( $confirm_body );
			$confirm_body = str_replace( '&#39;', "'", $confirm_body );

			$headers = "From: $site_name <$site_email>\r\nReply-To: $site_name <$site_email>";

			wp_mail(
				$pirate_forms_contact_email,
				pirate_forms_get_key( 'pirateformsopt_label_submit' ) . ' - ' . $site_name,
				$confirm_body,
				$headers
			);
		}

		/************************************************************/
		/*************   Store the entries in the DB ****************/
		/************************************************************/

		if ( pirate_forms_get_key( 'pirateformsopt_store' ) === 'yes' ) {
			$new_post_id = wp_insert_post(
				array(
					'post_type'    => 'pirate_forms_contact_pt',
					'post_title'   => date( 'l, M j, Y', time() ) . ' by "' . $pirate_forms_contact_name . '"',
					'post_content' => $body,
					'post_author'  => 1,
					'post_status'  => 'private'
				)
			);

			if ( isset( $pirate_forms_contact_email ) && ! empty( $pirate_forms_contact_email ) ) {
				add_post_meta( $new_post_id, 'Contact email', $pirate_forms_contact_email );
			}
		}

		// Should the user get redirected?
		if ( pirate_forms_get_key( 'pirateformsopt_result_url' ) ) {
			$redirect_id = intval( pirate_forms_get_key( 'pirateformsopt_result_url' ) );
			$redirect    = get_permalink( $redirect_id );
		}
		else {
			$redirect = $_SERVER["HTTP_REFERER"] . ( strpos( $_SERVER["HTTP_REFERER"], '?' ) === FALSE ? '?' : '&' ) . 'pcf=1';
		}

		wp_safe_redirect( $redirect );

	}

}

// Get a settings value
function pirate_forms_get_key( $id ) {
	$propercfp_options = get_option( 'pirate_forms_settings_array' );
	$pirate_forms_params = array();

	if( !empty($propercfp_options) ):
		parse_str((string)$propercfp_options, $pirate_forms_params);
	endif;
	return isset( $pirate_forms_params[$id] ) ? $pirate_forms_params[$id] : '';
}


add_action( 'wp_enqueue_scripts', 'pirate_forms_add_styles_and_scripts' );

function pirate_forms_add_styles_and_scripts() {

	/* style for frontpage contact */
	wp_enqueue_style( 'pirate_forms_front_styles', plugins_url( 'css/front.css', __FILE__ ) );

	/* recaptcha js */
	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	if( !empty($pirate_forms_options) ):

		$pirate_forms_params = array();

		parse_str((string)$pirate_forms_options, $pirate_forms_params);

		if( !empty($pirate_forms_params) && !empty($pirate_forms_params['pirateformsopt_recaptcha_secretkey']) && !empty($pirate_forms_params['pirateformsopt_recaptcha_sitekey']) && !empty($pirate_forms_params['pirateformsopt_recaptcha_field']) && ($pirate_forms_params['pirateformsopt_recaptcha_field'] == 'yes') ):

			wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );

		endif;

	endif;

}

/**************************************************************************/
/*** If submissions should be stored in the DB, create the Contacts CPT ***/
/*************************************************************************/

if ( pirate_forms_get_key( 'pirateformsopt_store' ) === 'yes' ) {

	add_action( 'init', 'pirate_forms_register_content_type' );

	function pirate_forms_register_content_type() {

		$labels = array(
			'name'               => __( 'Contacts', 'pirate-forms' ), 'post type general name',
			'singular_name'      => __( 'Contact', 'pirate-forms' ), 'post type singular name',
			'add_new'            => __( 'Add Contact', 'pirate-forms' ), 'proper_contact',
			'add_new_item'       => __( 'Add New Contact', 'pirate-forms' ),
			'edit_item'          => __( 'Edit Contact', 'pirate-forms' ),
			'new_item'           => __( 'New Contact', 'pirate-forms' ),
			'all_items'          => __( 'All Contacts', 'pirate-forms' ),
			'view_item'          => __( 'View Contact', 'pirate-forms' ),
			'not_found'          => __( 'No Contacts found', 'pirate-forms' ),
			'not_found_in_trash' => __( 'No Contacts found in Trash', 'pirate-forms' ),
			'menu_name'          => __( 'Contacts', 'pirate-forms' )
		);
		$args   = array(
			'labels'             => $labels,
			'public'             => FALSE,
			'show_ui'            => TRUE,
			'show_in_menu'       => TRUE,
			'hierarchical'       => FALSE,
			'menu_position'      => 27,
			'supports'           => array( 'title', 'editor', 'custom-fields' )
		);
		register_post_type( 'pirate_forms_contact_pt', $args );
	}

}