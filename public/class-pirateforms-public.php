<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PirateForms
 * @subpackage PirateForms/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PirateForms
 * @subpackage PirateForms/public
 * @author     Your Name <email@example.com>
 */
class PirateForms_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Change plugin name from readme.txt.
	 *
	 * @param string $name Old name.
	 *
	 * @return string New name.
	 */
	public function change_name( $name ) {
		return 'Pirate Forms';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles_and_scripts() {

		/* style for frontpage contact */
		wp_enqueue_style( 'pirate_forms_front_styles', PIRATEFORMS_URL . 'public/css/front.css', array(), $this->version );
		/* recaptcha js */
		$pirate_forms_options = get_option( 'pirate_forms_settings_array' );
		if ( ! empty( $pirate_forms_options ) ) :
			if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_secretkey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_sitekey'] ) && 'yes' === $pirate_forms_options['pirateformsopt_recaptcha_field'] ) :
				if ( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_current_language' ) ) {
					$pirate_forms_contactus_language = pll_current_language();
				} else {
					$pirate_forms_contactus_language = get_locale();
				}
				wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $pirate_forms_contactus_language . '' );
				wp_enqueue_script(
					'pirate_forms_scripts', PIRATEFORMS_URL . 'public/js/scripts.js', array(
						'jquery',
						'recaptcha',
					), $this->version
				);
			endif;
		endif;
		wp_enqueue_script( 'pirate_forms_scripts_general', PIRATEFORMS_URL . 'public/js/scripts-general.js', array( 'jquery' ), $this->version );
		$pirate_forms_errors = '';
		if ( ! empty( $_SESSION['pirate_forms_contact_errors'] ) ) :
			$pirate_forms_errors = $_SESSION['pirate_forms_contact_errors'];
		endif;
		wp_localize_script(
			'pirate_forms_scripts_general', 'pirateFormsObject', array(
				'errors' => $pirate_forms_errors,
				'spam'   => array(
					'label' => __( 'I\'m human!', 'pirate-forms' ),
					'value' => wp_create_nonce( PIRATEFORMS_NAME ),
				),
			)
		);
	}

	/**
	 * Display the form
	 *
	 * @since    1.0.0
	 */
	public function display_form( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'from' => '',
				'id'   => '',
			), $atts
		);

		$from_widget = ! empty( $atts['from'] );
		$elements    = array();
		$pirate_form = new PirateForms_PhpFormBuilder();

		$elements[] = array(
			'type' => 'text',
			'id'   => 'form_honeypot',
			'name' => 'honeypot',
			'slug' => 'honeypot',
			'wrap' => array(
				'type'  => 'div',
				'class' => 'form_field_wrap hidden',
				'style' => 'display: none',
			),
		);

		$elements[] = array(
			'type'  => 'hidden',
			'id'    => 'pirate_forms_from_widget',
			'value' => $from_widget ? 1 : 0,
		);

		$pirate_forms_options = PirateForms_Util::get_option();

		if ( isset( $atts['id'] ) && ! empty( $atts['id'] ) ) {
			$pirate_forms_options = apply_filters( 'pirateformpro_get_form_attributes', $pirate_forms_options, $atts['id'] );
			if ( isset( $pirate_forms_options['id'] ) ) {
				// add the form id to the form so that it can be used when we are processing the form
				$elements[] = array(
					'type'  => 'hidden',
					'id'    => 'pirate_forms_form_id',
					'value' => $atts['id'],
				);
			}
		}

		$nonce_append = isset( $_POST['pirate_forms_from_widget'] ) && intval( $_POST['pirate_forms_from_widget'] ) === 1 ? 'yes' : 'no';

		$error_key = wp_create_nonce( get_bloginfo( 'admin_email' ) . ( $from_widget ? 'yes' : 'no' ) );

		$thank_you_message = '';
		if ( isset( $_GET['done'] ) && empty( $_SESSION[ $error_key ] ) ) {
			$thank_you_message = sanitize_text_field( $pirate_forms_options['pirateformsopt_label_submit'] );
		}

		$pirate_form->set_element( 'thank_you_message', $thank_you_message );

		/**
		 ******** FormBuilder */
		if ( 'yes' === PirateForms_Util::get_option( 'pirateformsopt_nonce' ) ) {
			$elements[] = array(
				'type'  => 'hidden',
				'id'    => 'wordpress-nonce',
				'value' => wp_create_nonce( get_bloginfo( 'admin_email' ) . $nonce_append ),
			);
		}
		if ( ! empty( $pirate_forms_options ) ) :
			$field = $pirate_forms_options['pirateformsopt_name_field'];
			$label = $pirate_forms_options['pirateformsopt_label_name'];

			/**
			 ******  Name field */
			if ( ! empty( $field ) && ! empty( $label ) ) :
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					'col-sm-6 col-lg-6 contact_name_wrap pirate_forms_three_inputs form_field_wrap',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-name'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $required,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_name'],
					'type'         => 'text',
					'id'           => 'pirate-forms-contact-name',
					'class'        => 'form-control',
					'wrap'         => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_name', $wrap_classes ) ),
					),
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-name'] ) ? $_REQUEST['pirate-forms-contact-name'] : '',
				);
			endif;

			$field = $pirate_forms_options['pirateformsopt_email_field'];
			$label = $pirate_forms_options['pirateformsopt_label_email'];

			/**
			 ******  Email field */
			if ( ! empty( $field ) && ! empty( $label ) ) :
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					'col-sm-6 col-lg-6 contact_email_wrap pirate_forms_three_inputs form_field_wrap',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-email'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $required,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_email'],
					'type'         => 'email',
					'id'           => 'pirate-forms-contact-email',
					'class'        => 'form-control',
					'wrap'         => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_email', $wrap_classes ) ),
					),
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-email'] ) ? $_REQUEST['pirate-forms-contact-email'] : '',
				);
			endif;

			$field = $pirate_forms_options['pirateformsopt_subject_field'];
			$label = $pirate_forms_options['pirateformsopt_label_subject'];

			/**
			 ******  Subject field */
			if ( ! empty( $field ) && ! empty( $label ) ) :
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					'contact_subject_wrap pirate_forms_three_inputs form_field_wrap col-md-12',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-subject'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $required,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_subject'],
					'type'         => 'text',
					'id'           => 'pirate-forms-contact-subject',
					'class'        => 'form-control',
					'wrap'         => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_subject', $wrap_classes ) ),
					),
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-subject'] ) ? $_REQUEST['pirate-forms-contact-subject'] : '',
				);
			endif;

			$field = $pirate_forms_options['pirateformsopt_message_field'];
			$label = $pirate_forms_options['pirateformsopt_label_message'];

			/**
			 ******  Message field */
			if ( ! empty( $field ) && ! empty( $label ) ) :
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array( 'col-sm-12 col-lg-12 form_field_wrap contact_message_wrap  ' );
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-message'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $required,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_no_content'],
					'type'         => 'textarea',
					'class'        => 'form-control',
					'id'           => 'pirate-forms-contact-message',
					'wrap'         => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_message', $wrap_classes ) ),
					),
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-message'] ) ? $_REQUEST['pirate-forms-contact-message'] : '',
				);
			endif;

			$field = $pirate_forms_options['pirateformsopt_attachment_field'];

			/**
			 ******  Message field */
			if ( ! empty( $field ) && 'no' !== $field ) :
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array( 'col-sm-12 col-lg-12 form_field_wrap contact_attachment_wrap' );
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-attachment'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'required'     => $required,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_no_attachment'],
					'type'         => 'file',
					'class'        => 'form-control',
					'id'           => 'pirate-forms-attachment',
					'wrap'         => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_attachment', $wrap_classes ) ),
					),
				);
			endif;
			/**
			 ******* ReCaptcha */
			if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_secretkey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_sitekey'] ) && 'yes' === $pirate_forms_options['pirateformsopt_recaptcha_field'] ) :
				$pirateformsopt_recaptcha_sitekey   = $pirate_forms_options['pirateformsopt_recaptcha_sitekey'];
				$pirateformsopt_recaptcha_secretkey = $pirate_forms_options['pirateformsopt_recaptcha_secretkey'];
				$elements[]                         = array(
					'placeholder' => stripslashes( sanitize_text_field( $label ) ),
					'type'        => 'div',
					'class'       => 'g-recaptcha pirate-forms-g-recaptcha',
					'custom'      => array( 'data-sitekey' => $pirateformsopt_recaptcha_sitekey ),
					'id'          => 'pirate-forms-captcha',
					'wrap'        => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_captcha', array( 'col-xs-12 col-sm-6 col-lg-6 form_field_wrap form_captcha_wrap' ) ) ),
					),
				);
			endif;

			if ( 'custom' === $pirate_forms_options['pirateformsopt_recaptcha_field'] ) :
				$elements[] = array(
					'type'  => 'div',
					'class' => 'pirate-forms-maps-custom',    // spam.reverse() = maps
					'id'    => 'pirate-forms-maps-custom',
					'wrap'  => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_spam', array( 'col-xs-12 col-sm-6 col-lg-6 form_field_wrap pirateform_wrap_classes_spam_wrap' ) ) ),
					),
				);
			endif;

			/**
			 ******  Submit button */
			$pirateformsopt_label_submit_btn = '';
			if ( ! empty( $pirate_forms_options['pirateformsopt_label_submit_btn'] ) ) {
				$pirateformsopt_label_submit_btn = $pirate_forms_options['pirateformsopt_label_submit_btn'];
			}
			if ( empty( $pirateformsopt_label_submit_btn ) ) {
				$pirateformsopt_label_submit_btn = __( 'Submit', 'pirate-forms' );
			}
			$elements[] = array(
				'type'  => 'button',
				'id'    => 'pirate-forms-contact-submit',
				'class' => 'pirate-forms-submit-button btn btn-primary',
				'wrap'  => array(
					'type'  => 'div',
					'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_submit', array( 'col-xs-12 col-sm-6 col-lg-6 form_field_wrap contact_submit_wrap' ) ) ),
				),
				'value' => $pirateformsopt_label_submit_btn,
			);
		endif;

		/* Referring site or page, if any */
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$elements[] = array(
				'type'  => 'hidden',
				'id'    => 'contact-referrer',
				'value' => $_SERVER['HTTP_REFERER'],
			);
		}

		/* Referring page, if sent via URL query */
		if ( ! empty( $_REQUEST['src'] ) || ! empty( $_REQUEST['ref'] ) ) {
			$elements[] = array(
				'type'  => 'hidden',
				'id'    => 'referring-page',
				'value' => ! empty( $_REQUEST['src'] ) ? $_REQUEST['src'] : $_REQUEST['ref'],
			);
		}

		/* Are there any submission errors? */
		$errors = '';
		if ( ! empty( $_SESSION[ $error_key ] ) ) {
			$pirate_form->set_element( 'errors', $_SESSION[ $error_key ] );
			unset( $_SESSION[ $error_key ] );
		}

		$elements = apply_filters( 'pirate_forms_get_custom_elements', $elements, $pirate_forms_options );

		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'displaying elements %s', print_r( $elements, true ) ), 'debug', __FILE__, __LINE__ );

		return $pirate_form->build_form( apply_filters( 'pirate_forms_public_controls', $elements, $pirate_forms_options, $from_widget ), $pirate_forms_options, $from_widget );
	}

	/**
	 * Renders all the fields relevant to the form
	 *
	 * @param PirateForms_PhpFormBuilder $form_builder The form builder object.
	 */
	public function render_fields( $form_builder ) {
		echo '<div class="pirate_forms_three_inputs_wrap">';

		if ( isset( $form_builder->contact_name ) ) {
			echo $form_builder->contact_name;
		}

		if ( isset( $form_builder->contact_email ) ) {
			echo $form_builder->contact_email;
		}

		if ( isset( $form_builder->contact_subject ) ) {
			echo $form_builder->contact_subject;
		}

		if ( isset( $form_builder->custom_fields ) ) {
			echo $form_builder->custom_fields;
		}

		echo '</div>';

		if ( isset( $form_builder->contact_message ) ) {
			echo $form_builder->contact_message;
		}

		if ( isset( $form_builder->attachment ) ) {
			echo $form_builder->attachment;
		}

		if ( isset( $form_builder->captcha ) ) {
			echo $form_builder->captcha;
		}

		echo $form_builder->contact_submit;
	}

	/**
	 * Renders all the errors relevant to the form
	 *
	 * @param PirateForms_PhpFormBuilder $form_builder The form builder object.
	 */
	public function render_errors( $form_builder ) {
		$output = '';
		if ( ! empty( $form_builder->errors ) ) :
			$output .= '<div class="col-sm-12 col-lg-12 pirate_forms_error_box">';
			$output .= '<p>' . __( 'Sorry, an error occured.', 'pirate-forms' ) . '</p>';
			$output .= '</div>';
			foreach ( $form_builder->errors as $err ) :
				$output .= '<div class="col-sm-12 col-lg-12 pirate_forms_error_box">';
				$output .= "<p>$err</p>";
				$output .= '</div>';
			endforeach;

		endif;

		echo $output;
	}

	/**
	 * Renders the thank you message relevant to the form
	 *
	 * @param PirateForms_PhpFormBuilder $form_builder The form builder object.
	 */
	public function render_thankyou( $form_builder ) {
		if ( ! empty( $form_builder->thank_you_message ) ) {
			echo '<div class="col-sm-12 col-lg-12 pirate_forms_thankyou_wrap"><p>' . $form_builder->thank_you_message . '</p></div>';
		}
	}

	/**
	 * Process the form after submission
	 *
	 * @since    1.0.0
	 * @throws  Exception When file uploading fails.
	 */
	public function template_redirect() {
		do_action( 'pirate_forms_send_email', false );
	}

	/**
	 * Process the form after submission
	 *
	 * @since    1.0.0
	 * @throws  Exception When file uploading fails.
	 */
	public function send_email( $test = false ) {
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'POST data = %s', print_r( $_POST, true ) ), 'debug', __FILE__, __LINE__ );

		// If POST and honeypot are not set, beat it
		if ( empty( $_POST ) || ! isset( $_POST['honeypot'] ) ) {
			return false;
		}

		// separate the nonce from a form that is displayed in the widget vs. one that is not
		$nonce_append = isset( $_POST['pirate_forms_from_widget'] ) && intval( $_POST['pirate_forms_from_widget'] ) === 1 ? 'yes' : 'no';

		// Session variable for form errors
		$error_key              = wp_create_nonce( get_bloginfo( 'admin_email' ) . $nonce_append );
		$_SESSION[ $error_key ] = array();

		// If nonce is not valid, beat it
		if ( ! $test && 'yes' === PirateForms_Util::get_option( 'pirateformsopt_nonce' ) ) {
			if ( ! wp_verify_nonce( $_POST['wordpress-nonce'], get_bloginfo( 'admin_email' ) . $nonce_append ) ) {
				$_SESSION[ $error_key ]['nonce'] = __( 'Nonce failed!', 'pirate-forms' );
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, 'Nonce failed', 'error', __FILE__, __LINE__ );

				return false;
			}
		}

		// If the honeypot caught a bear, beat it
		if ( ! empty( $_POST['honeypot'] ) ) {
			$_SESSION[ $error_key ]['honeypot'] = __( 'Form submission failed!', 'pirate-forms' );

			return false;
		}

		$pirate_forms_options = PirateForms_Util::get_form_options( isset( $_POST['pirate_forms_form_id'] ) ? $_POST['pirate_forms_form_id'] : null );

		if ( ! $this->validate_spam( $error_key, $pirate_forms_options ) ) {
			return false;
		}

		// Start the body of the contact email
		$body            = array();
		$body['heading'] = sprintf( __( 'Contact form submission from %s', 'pirate-forms' ), get_bloginfo( 'name' ) . ' (' . site_url() . ')' );
		$body['body']    = array();

		list( $pirate_forms_contact_email, $pirate_forms_contact_name, $pirate_forms_contact_subject, $msg ) = $this->validate_request( $error_key, $pirate_forms_options, $body );

		/**
		 ******** Validate recipients email */
		$site_recipients = sanitize_text_field( $pirate_forms_options['pirateformsopt_email_recipients'] );
		if ( empty( $site_recipients ) ) {
			$_SESSION[ $error_key ]['pirate-forms-recipients-email'] = __( 'Please enter one or more Contact submission recipients', 'pirate-forms' );
		}
		/**
		 ******   Sanitize and validate IP  */
		$contact_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
		/* for the case of a Web server behind a reverse proxy */
		if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
			$contact_ip_tmp = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			if ( ! empty( $contact_ip_tmp ) ) {
				$contact_ip = array_pop( $contact_ip_tmp );
			}
		}

		// If valid and present, create a link to an IP search
		if ( ! empty( $contact_ip ) ) {
			$body['body'][ __( 'IP address', 'pirate-forms' ) ] = $contact_ip;
			$body['body'][ __( 'IP search', 'pirate-forms' ) ]  = "http://whatismyipaddress.com/ip/$contact_ip";
		}

		// Sanitize and prepare referrer;
		if ( ! empty( $_POST['pirate-forms-contact-referrer'] ) ) {
			$body['body'][ __( 'Came from', 'pirate-forms' ) ] = sanitize_text_field( $_POST['pirate-forms-contact-referrer'] );
		}

		// Show the page this contact form was submitted on
		$body['body'][ __( 'Sent from page', 'pirate-forms' ) ] = get_permalink( get_the_id() );

		// Check the blacklist
		$blocked = PirateForms_Util::is_blacklisted( $error_key, $pirate_forms_contact_email, $contact_ip );
		if ( $blocked ) {
			return false;
		}

		if ( $this->is_spam( $pirate_forms_options, $contact_ip, get_permalink( get_the_id() ), $msg ) ) {
			$_SESSION[ $error_key ]['honeypot'] = __( 'Form submission failed!', 'pirate-forms' );
		}

		// No errors? Go ahead and process the contact
		if ( empty( $_SESSION[ $error_key ] ) ) {
			$site_email = sanitize_text_field( $pirate_forms_options['pirateformsopt_email'] );
			if ( ! empty( $pirate_forms_contact_name ) ) :
				$site_name = $pirate_forms_contact_name;
			else :
				$site_name = htmlspecialchars_decode( get_bloginfo( 'name' ) );
			endif;
			// Notification recipients
			$site_recipients = sanitize_text_field( $pirate_forms_options['pirateformsopt_email_recipients'] );
			$site_recipients = explode( ',', $site_recipients );
			$site_recipients = array_map( 'trim', $site_recipients );
			$site_recipients = array_map( 'sanitize_email', $site_recipients );
			$site_recipients = implode( ',', $site_recipients );
			// No name? Use the submitter email address, if one is present
			if ( empty( $pirate_forms_contact_name ) ) {
				$pirate_forms_contact_name = ! empty( $pirate_forms_contact_email ) ? $pirate_forms_contact_email : '[None given]';
			}

			// Need an email address for the email notification
			$send_from = '';
			if ( '[email]' == $site_email && ! empty( $pirate_forms_contact_email ) ) {
				$send_from = $pirate_forms_contact_email;
			} elseif ( ! empty( $site_email ) ) {
				$send_from = $site_email;
			} else {
				$send_from = PirateForms_Util::get_from_email();
			}
			$send_from_name = $site_name;

			// Send an email notification to the correct address
			$headers = "From: $send_from_name <$send_from>\r\nReply-To: $pirate_forms_contact_name <$pirate_forms_contact_email>\r\nContent-type: text/html";
			add_action( 'phpmailer_init', array( $this, 'phpmailer' ) );

			$attachments = $this->get_attachments( $error_key, $pirate_forms_options, $body );
			if ( is_bool( $attachments ) ) {
				return false;
			}

			$subject = 'Contact on ' . htmlspecialchars_decode( get_bloginfo( 'name' ) );
			if ( ! empty( $pirate_forms_contact_subject ) ) {
				$subject = $pirate_forms_contact_subject;
			}

			$mail_body = apply_filters( 'pirate_forms_get_mail_body', $body );
			if ( is_array( $mail_body ) ) {
				$mail_body = PirateForms_Util::get_table( $mail_body );
			}

			do_action( 'pirate_forms_before_sending', $pirate_forms_contact_email, $site_recipients, $subject, $mail_body, $headers, $attachments );
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'before sending email to = %s, subject = %s, body = %s, headers = %s, attachments = %s', $site_recipients, $subject, $mail_body, $headers, print_r( $attachments, true ) ), 'debug', __FILE__, __LINE__ );
			$response = wp_mail( $site_recipients, $subject, $mail_body, $headers, $attachments );
			if ( ! $response ) {
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, 'Email not sent', 'debug', __FILE__, __LINE__ );
				error_log( 'Email not sent' );
			}
			do_action( 'pirate_forms_after_sending', $pirate_forms_options, $response, $pirate_forms_contact_email, $site_recipients, $subject, $mail_body, $headers, $attachments );
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'after sending email, response = %s', $response ), 'debug', __FILE__, __LINE__ );

			// delete the tmp directory
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->delete( $this->get_upload_tmp_dir(), true, 'd' );

			// Should a confirm email be sent?
			$confirm_body     = stripslashes( trim( $pirate_forms_options['pirateformsopt_confirm_email'] ) );
			$response_confirm = '';
			if ( ! empty( $confirm_body ) && ! empty( $pirate_forms_contact_email ) ) {
				// Removing entities
				$confirm_body = htmlspecialchars_decode( $confirm_body );
				$confirm_body = html_entity_decode( $confirm_body );
				$confirm_body = str_replace( '&#39;', "'", $confirm_body );
				$send_from    = PirateForms_Util::get_from_email();
				if ( ! empty( $site_email ) && '[email]' !== $site_email ) {
					$send_from = $site_email;
				}
				$site_name = htmlspecialchars_decode( get_bloginfo( 'name' ) );

				$headers = "From: $site_name <$send_from>\r\nReply-To: $site_name <$send_from>";
				$subject = $pirate_forms_options['pirateformsopt_label_submit'] . ' - ' . $site_name;

				do_action( 'pirate_forms_before_sending_confirm', $pirate_forms_contact_email, $pirate_forms_contact_email, $subject, $confirm_body, $headers );
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'before sending confirm email to = %s, subject = %s, body = %s, headers = %s', $pirate_forms_contact_email, $subject, $confirm_body, $headers ), 'debug', __FILE__, __LINE__ );
				$response_confirm = wp_mail( $pirate_forms_contact_email, $subject, $confirm_body, $headers );
				do_action( 'pirate_forms_after_sending_confirm', $pirate_forms_options, $response_confirm, $pirate_forms_contact_email, $pirate_forms_contact_email, $subject, $confirm_body, $headers );
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'after sending confirm email response = %s', $response_confirm ), 'debug', __FILE__, __LINE__ );
				if ( ! $response_confirm ) {
					do_action( 'themeisle_log_event', PIRATEFORMS_NAME, 'Confirm email not sent', 'debug', __FILE__, __LINE__ );
					error_log( 'Confirm email not sent' );
				}
			}

			/**
			 ***********   Store the entries in the DB */
			if ( 'yes' === $pirate_forms_options['pirateformsopt_store'] ) {
				$new_post_id = wp_insert_post(
					array(
						'post_type'    => 'pf_contact',
						'post_title'   => date( 'l, M j, Y', time() ) . ' by "' . $pirate_forms_contact_name . '"',
						'post_content' => $mail_body,
						'post_author'  => 1,
						'post_status'  => 'private',
					)
				);
				if ( isset( $pirate_forms_contact_email ) && ! empty( $pirate_forms_contact_email ) ) {
					add_post_meta( $new_post_id, 'Contact email', $pirate_forms_contact_email );
				}
				add_post_meta( $new_post_id, PIRATEFORMS_SLUG . 'mail-status', $response );
				add_post_meta( $new_post_id, PIRATEFORMS_SLUG . 'confirm-mail-status', $response_confirm );
				do_action( 'pirate_forms_update_contact', $pirate_forms_options, $new_post_id );
			}

			do_action( 'pirate_forms_after_processing', $response );

			if ( ! $test ) {
				$pirate_forms_current_theme = wp_get_theme();
				$is_our_theme               = array_intersect(
					array(
						'Zerif Lite',
						'Zerif PRO',
						'Hestia Pro',
					), array( $pirate_forms_current_theme->name, $pirate_forms_current_theme->parent_theme )
				);

				/* If a Thank you page is selected, redirect to that page */
				if ( $pirate_forms_options['pirateformsopt_thank_you_url'] ) {
					$redirect_id = intval( $pirate_forms_options['pirateformsopt_thank_you_url'] );
					$redirect    = get_permalink( $redirect_id );
					if ( ! empty( $redirect ) ) {
						wp_safe_redirect( $redirect );
					}
				} elseif ( $is_our_theme ) {
					// the fragment identifier should always be the last argument, otherwise the thank you message will not show.
					$redirect = add_query_arg(
						array(
							'done' => 'done',
							'pcf'  => '1#contact',
						), $_SERVER['HTTP_REFERER']
					);
					wp_safe_redirect( $redirect );
				} elseif ( isset( $_SERVER['HTTP_REFERER'] ) ) {
					$redirect = add_query_arg( array( 'done' => 'done' ), $_SERVER['HTTP_REFERER'] );
					wp_safe_redirect( $redirect );
				}
			}
		}
	}

	/**
	 * Validate SPAM/reCAPTCHA
	 *
	 * @param string $error_key the key for the session object.
	 * @param array  $pirate_forms_options the array of options for this form.
	 */
	function validate_spam( $error_key, $pirate_forms_options ) {
		$pirateformsopt_recaptcha_field = isset( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) ? $pirate_forms_options['pirateformsopt_recaptcha_field'] : '';
		if ( 'yes' === $pirateformsopt_recaptcha_field ) {
			$pirateformsopt_recaptcha_sitekey   = $pirate_forms_options['pirateformsopt_recaptcha_sitekey'];
			$pirateformsopt_recaptcha_secretkey = $pirate_forms_options['pirateformsopt_recaptcha_secretkey'];
			if ( ! empty( $pirateformsopt_recaptcha_secretkey ) && ! empty( $pirateformsopt_recaptcha_sitekey ) ) {
				$captcha = $_POST['g-recaptcha-response'];
			}
			if ( ! $captcha ) {
				$_SESSION[ $error_key ]['pirate-forms-captcha'] = __( 'Wrong reCAPTCHA', 'pirate-forms' );

				return false;
			}
			$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $pirateformsopt_recaptcha_secretkey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'] );
			if ( ! empty( $response ) ) :
				$response_body = wp_remote_retrieve_body( $response );
			endif;
			if ( ! empty( $response_body ) ) :
				$result = json_decode( $response_body, true );
			endif;
			if ( isset( $result['success'] ) && ( $result['success'] == false ) ) {
				$_SESSION[ $error_key ]['pirate-forms-captcha'] = __( 'Wrong reCAPTCHA', 'pirate-forms' );

				return false;
			}
		} elseif ( 'custom' === $pirateformsopt_recaptcha_field ) {
			if ( isset( $_POST['xobkcehc'] ) && wp_verify_nonce( $_POST['xobkcehc'], PIRATEFORMS_NAME ) ) {
				return true;
			}

			$_SESSION[ $error_key ]['pirate-forms-captcha'] = __( 'Submission blocked!', 'pirate-forms' );

			return false;
		}

		return true;
	}

	/**
	 * Validate request
	 *
	 * @param string $error_key the key for the session object.
	 * @param array  $pirate_forms_options the array of options for this form.
	 * @param string $body the body to save as the CPT.
	 */
	function validate_request( $error_key, $pirate_forms_options, &$body ) {
		$contact_email   = null;
		$contact_name    = null;
		$contact_subject = null;
		$message         = null;
		$fields          = array( 'name', 'email', 'subject', 'message' );

		foreach ( $fields as $field ) {
			$value = isset( $_POST[ 'pirate-forms-contact-' . $field ] ) ? sanitize_text_field( trim( $_POST[ 'pirate-forms-contact-' . $field ] ) ) : '';
			if ( 'req' === $pirate_forms_options[ 'pirateformsopt_' . $field . '_field' ] && empty( $value ) ) {
				$_SESSION[ $error_key ][ 'pirate-forms-contact-' . $field ] = $pirate_forms_options[ 'pirateformsopt_label_err_' . $field ];
			} elseif ( ! empty( $value ) ) {
				if ( 'email' === $field && ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
					$_SESSION[ $error_key ][ 'pirate-forms-contact-' . $field ] = $pirate_forms_options[ 'pirateformsopt_label_err_' . $field ];
				} else {
					if ( 'email' === $field ) {
						$contact_email = $value;
					} elseif ( 'name' === $field ) {
						$contact_name = $value;
					} elseif ( 'subject' === $field ) {
						$contact_subject = $value;
					} elseif ( 'message' === $field ) {
						$message = $value;
					}
					$body['body'][ stripslashes( $pirate_forms_options[ 'pirateformsopt_label_' . $field ] ) ] = $value;
				}
			}
		}

		$customize = apply_filters( 'pirate_forms_customize_body', false );
		if ( ! $customize ) {
			// new lite, old pro
			$temp         = '';
			$temp         = apply_filters( 'pirate_forms_validate_request', $temp, $error_key, $pirate_forms_options );
			$body['rows'] = $temp;
		} else {
			// new lite, new pro
			$body = apply_filters( 'pirate_forms_validate_request', $body, $error_key, $pirate_forms_options );
		}

		return array( $contact_email, $contact_name, $contact_subject, $message );
	}

	/**
	 * Check with akismet if the message is spam.
	 */
	function is_spam( $pirate_forms_options, $ip, $page_url, $msg ) {
		// check if akismet is installed and key provided
		$key = get_option( 'wordpress_api_key' );
		if ( empty( $key ) ) {
			// see if we have provided the key in the options
			$key = isset( $pirate_forms_options['akismet_api_key'] ) ? $pirate_forms_options['akismet_api_key'] : '';
		}
		if ( empty( $key ) ) {
			return false;
		}

		$data     = array(
			'blog'            => home_url(),
			'user_ip'         => $ip,
			'user_agent'      => $_SERVER['HTTP_USER_AGENT'],
			'referrer'        => $_SERVER['HTTP_REFERER'],
			'permalink'       => $page_url,
			'comment_type'    => 'contact-form',
			'comment_content' => $msg,
		);
		$response = wp_remote_retrieve_body(
			wp_remote_post(
				"https://{$key}.rest.akismet.com/1.1/comment-check", array(
					'body'    => $data,
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
						'User-Agent'   => sprintf( 'WordPress/%s | Akismet/3.1.7', get_bloginfo( 'version' ) ),
					),
				)
			)
		);

		if ( 'true' == $response ) {
			return true;
		}

		return false;
	}

	/**
	 * Get attachments, if any
	 *
	 * @param string $error_key the key for the session object.
	 * @param array  $pirate_forms_options the array of options for the form.
	 * @param array  $body the body of the mail.
	 *
	 * @throws  Exception When file uploading fails.
	 */
	function get_attachments( $error_key, $pirate_forms_options, &$body ) {
		$attachments = array();
		$has_files   = $pirate_forms_options['pirateformsopt_attachment_field'];
		if ( ! empty( $has_files ) ) {
			$uploads_dir = $this->get_upload_tmp_dir();
			$uploads_dir = $this->maybe_add_random_dir( $uploads_dir );

			foreach ( $_FILES as $label => $file ) {
				if ( empty( $file['name'] ) ) {
					continue;
				}
				/* Validate file type */
				$file_types_allowed              = implode( '|', apply_filters( 'pirate_forms_allowed_file_types', explode( '|', 'jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv|xls|xlsx|txt' ) ) );
				$pirate_forms_file_types_allowed = $file_types_allowed;
				$pirate_forms_file_types_allowed = trim( $pirate_forms_file_types_allowed, '|' );
				$pirate_forms_file_types_allowed = '(' . $pirate_forms_file_types_allowed . ')';
				$pirate_forms_file_types_allowed = '/\.' . $pirate_forms_file_types_allowed . '$/i';
				if ( ! preg_match( $pirate_forms_file_types_allowed, $file['name'] ) ) {
					do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'file invalid: expected %s got %s', $file_types_allowed, $file['name'] ), 'error', __FILE__, __LINE__ );
					$_SESSION[ $error_key ]['pirate-forms-upload-failed-type'] = sprintf( __( 'Uploaded file type is not allowed for %s', 'pirate-forms' ), $file['name'] );

					return false;
				}
				/* Validate file size */
				$pirate_forms_file_size_allowed = 1048576; // default size 1 MB
				if ( $file['size'] > $pirate_forms_file_size_allowed ) {
					do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'file too large: expected %d got %d', $pirate_forms_file_size_allowed, $file['size'] ), 'error', __FILE__, __LINE__ );
					$_SESSION[ $error_key ]['pirate-forms-upload-failed-size'] = sprintf( __( 'Uploaded file is too large %s', 'pirate-forms' ), $file['name'] );

					return false;
				}
				$this->init_uploads();
				$filename = $file['name'];
				$filename = $this->canonicalize( $filename );
				$filename = sanitize_file_name( $filename );
				$filename = $this->antiscript_file_name( $filename );
				$filename = wp_unique_filename( $uploads_dir, $filename );
				$new_file = trailingslashit( $uploads_dir ) . $filename;
				try {
					if ( false === move_uploaded_file( $file['tmp_name'], $new_file ) ) {
						do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'unable to move the uploaded file from %s to %s', $file['tmp_name'], $new_file ), 'error', __FILE__, __LINE__ );
						throw new Exception( sprintf( __( 'There was an unknown error uploading the file %s', 'pirate-forms' ), $file['name'] ) );
					}
				} catch ( Exception $ex ) {
					do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'unable to move the uploaded file from %s to %s with error %s', $file['tmp_name'], $new_file, $ex->getMessage() ), 'error', __FILE__, __LINE__ );
					$_SESSION[ $error_key ]['pirate-forms-upload-failed-general'] = $ex->getMessage();
				}
				if ( ! empty( $new_file ) ) {
					$attachments[] = $new_file;
				}
			}
		}
		if ( $attachments ) {
			$files = array();
			foreach ( $attachments as $file ) {
				$files[] = basename( $file );
			}
			$body['body'][ __( 'Attachment', 'pirate-forms' ) ] = implode( ',', $files );
		}
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'finally attaching attachment(s): %s', print_r( $attachments, true ) ), 'info', __FILE__, __LINE__ );

		return $attachments;
	}

	/**
	 * Return the temporary upload dir
	 *
	 * @since    1.0.0
	 */
	function get_upload_tmp_dir() {
		return $this->get_upload_dir( 'dir' ) . '/pirate_forms_uploads';
	}

	/**
	 * Return the upload dir
	 *
	 * @since    1.0.0
	 */
	function get_upload_dir( $type = false ) {
		$uploads = wp_upload_dir();
		$uploads = apply_filters(
			'pirate_forms_upload_dir', array(
				'dir' => $uploads['basedir'],
				'url' => $uploads['baseurl'],
			)
		);
		if ( 'dir' == $type ) {
			return $uploads['dir'];
		}
		if ( 'url' == $type ) {
			return $uploads['url'];
		}

		return $uploads;
	}

	/**
	 * Add a random directory for uploading
	 *
	 * @since    1.0.0
	 */
	function maybe_add_random_dir( $dir ) {
		do {
			$rand_max = mt_getrandmax();
			$rand     = zeroise( mt_rand( 0, $rand_max ), strlen( $rand_max ) );
			$dir_new  = path_join( $dir, $rand );
		} while ( file_exists( $dir_new ) );
		if ( wp_mkdir_p( $dir_new ) ) {
			return $dir_new;
		}

		return $dir;
	}

	/**
	 * Prepare the uploading process
	 *
	 * @since    1.0.0
	 * @throws   Exception When file could not be opened.
	 */
	function init_uploads() {
		$dir = $this->get_upload_tmp_dir();
		wp_mkdir_p( $dir );
		$htaccess_file = trailingslashit( $dir ) . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			return;
		}
		try {
			$handle = fopen( $htaccess_file, 'w' );

			if ( ! $handle ) {
				throw new Exception( 'File open failed.' );
			} else {
				fwrite( $handle, "Deny from all\n" );
				fclose( $handle );
			}
		} catch ( Exception $e ) {
			// nothing
		}
	}

	/**
	 * Functions to Process uploaded files
	 */
	function canonicalize( $text ) {
		if ( function_exists( 'mb_convert_kana' )
			 && 'UTF-8' == get_option( 'blog_charset' )
		) {
			$text = mb_convert_kana( $text, 'asKV', 'UTF-8' );
		}
		$text = strtolower( $text );
		$text = trim( $text );

		return $text;
	}

	/**
	 * Prevent uploading any script files
	 *
	 * @since    1.0.0
	 */
	function antiscript_file_name( $filename ) {
		$filename = basename( $filename );
		$parts    = explode( '.', $filename );
		if ( count( $parts ) < 2 ) {
			return $filename;
		}
		$script_pattern = '/^(php|phtml|pl|py|rb|cgi|asp|aspx)\d?$/i';
		$filename       = array_shift( $parts );
		$extension      = array_pop( $parts );
		foreach ( (array) $parts as $part ) {
			if ( preg_match( $script_pattern, $part ) ) {
				$filename .= '.' . $part . '_';
			} else {
				$filename .= '.' . $part;
			}
		}
		if ( preg_match( $script_pattern, $extension ) ) {
			$filename .= '.' . $extension . '_.txt';
		} else {
			$filename .= '.' . $extension;
		}

		return $filename;
	}

	/**
	 * Change the content of the widget
	 *
	 * @since    1.0.0
	 */
	public function widget_text_filter( $content ) {
		if ( ! preg_match( '[pirate_forms]', $content ) ) {
			return $content;
		}
		$content = do_shortcode( $content );

		return $content;
	}

	/**
	 * Alter the phpmailer object
	 *
	 * @param object $phpmailer PHPMailer object.
	 */
	function phpmailer( $phpmailer ) {
		$pirate_forms_options                   = PirateForms_Util::get_form_options( isset( $_POST['pirate_forms_form_id'] ) && ! empty( $_POST['pirate_forms_form_id'] ) ? $_POST['pirate_forms_form_id'] : null );
		$pirateformsopt_use_smtp                = $pirate_forms_options['pirateformsopt_use_smtp'];
		$pirateformsopt_smtp_host               = $pirate_forms_options['pirateformsopt_smtp_host'];
		$pirateformsopt_smtp_port               = $pirate_forms_options['pirateformsopt_smtp_port'];
		$pirateformsopt_smtp_username           = $pirate_forms_options['pirateformsopt_smtp_username'];
		$pirateformsopt_smtp_password           = $pirate_forms_options['pirateformsopt_smtp_password'];
		$pirateformsopt_use_secure              = $pirate_forms_options['pirateformsopt_use_secure'];
		$pirateformsopt_use_smtp_authentication = $pirate_forms_options['pirateformsopt_use_smtp_authentication'];
		if ( ! empty( $pirateformsopt_use_smtp ) && ( $pirateformsopt_use_smtp == 'yes' ) && ! empty( $pirateformsopt_smtp_host ) && ! empty( $pirateformsopt_smtp_port ) ) :
			// @codingStandardsIgnoreStart
			$phpmailer->isSMTP();
			$phpmailer->Host = $pirateformsopt_smtp_host;
			if ( ! empty( $pirateformsopt_use_smtp_authentication ) && ( $pirateformsopt_use_smtp_authentication == 'yes' ) && ! empty( $pirateformsopt_smtp_username ) && ! empty( $pirateformsopt_smtp_password ) ) :
				$phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
				$phpmailer->Port     = $pirateformsopt_smtp_port;
				$phpmailer->Username = $pirateformsopt_smtp_username;
				$phpmailer->Password = $pirateformsopt_smtp_password;
			endif;

			if ( ! empty( $pirateformsopt_use_secure ) ) {
				$phpmailer->SMTPSecure = $pirateformsopt_use_secure;
			}
			// @codingStandardsIgnoreEnd
		endif;
	}

	/**
	 * Alter classes and wrapper of form elements for compatibility reasons with differen themes.
	 *
	 * @param array $elements The form elements.
	 *
	 * @return array The form elements.
	 */
	public function compatibility_class( $elements ) {
		if ( function_exists( 'zerif_setup' ) ) {
			foreach ( $elements as $k => $element ) {
				if ( $element['id'] == 'pirate-forms-contact-submit' ) {
					$elements[ $k ]['class'] = 'btn btn-primary custom-button red-btn pirate-forms-submit-button';
				}
				if ( $element['id'] == 'pirate-forms-contact-name' ) {
					$elements[ $k ]['wrap']['class'] = 'col-lg-4 col-sm-4 form_field_wrap';
					$elements[ $k ]['class']         = 'form-control input';
				}
				if ( $element['id'] == 'pirate-forms-contact-email' ) {
					$elements[ $k ]['wrap']['class'] = 'col-lg-4 col-sm-4 form_field_wrap';
					$elements[ $k ]['class']         = 'form-control input';
				}
				if ( $element['id'] == 'pirate-forms-contact-subject' ) {
					$elements[ $k ]['wrap']['class'] = 'col-lg-4 col-sm-4 form_field_wrap';
					$elements[ $k ]['class']         = 'form-control input';
				}
				if ( $element['id'] == 'pirate-forms-contact-message' ) {
					$elements[ $k ]['wrap']['class'] = 'col-lg-12 col-sm-12 form_field_wrap';
					$elements[ $k ]['class']         = 'form-control input';
				}
			}
		}

		return $elements;
	}
}
