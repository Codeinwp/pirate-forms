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
	 * The post parameters sent in the ajax request.
	 *
	 * @access   private
	 * @var      array $_post The post parameters sent in the ajax request.
	 */
	private $_post;

	/**
	 * The files sent in the ajax request.
	 *
	 * @access   private
	 * @var      array $_files The files sent in the ajax request.
	 */
	private $_files;

	/**
	 * The file types allowed to be uploaded. Can take a look at https://en.support.wordpress.com/accepted-filetypes/ for inspiration.
	 *
	 * @access   private
	 * @var      array $_file_types_allowed The file types allowed to be uploaded.
	 */
	private static $_file_types_allowed = array(
		'3g2',
		'3gp',
		'avi',
		'doc',
		'docx',
		'gif',
		'jpeg',
		'jpg',
		'key',
		'm4a',
		'm4v',
		'mov',
		'mp3',
		'mp4',
		'mpg',
		'odt',
		'ogg',
		'ogv',
		'pdf',
		'png',
		'pps',
		'ppsx',
		'ppt',
		'pptx',
		'txt',
		'wav',
		'wmv',
		'xls',
		'xlsx',
		'zip',
	);

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
		wp_register_style( 'pirate_forms_front_styles', PIRATEFORMS_URL . 'public/css/front.css', array(), $this->version );

		/* recaptcha js */
		$pirate_forms_contactus_language = get_locale();
		if ( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_current_language' ) ) {
			$pirate_forms_contactus_language = pll_current_language();
		}
		wp_register_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $pirate_forms_contactus_language . '', array( 'jquery' ) );

		wp_register_script( 'pirate_forms_scripts', PIRATEFORMS_URL . 'public/js/scripts.js', array( 'jquery' ), $this->version );

		$pirate_forms_errors = '';
		if ( ! empty( $_SESSION['pirate_forms_contact_errors'] ) ) {
			$pirate_forms_errors = $_SESSION['pirate_forms_contact_errors'];
		}
		wp_localize_script(
			'pirate_forms_scripts', 'pirateFormsObject', array(
				'errors' => $pirate_forms_errors,
				'rest'  => array(
					'submit'    => array(
						'url'   => get_rest_url( null, PIRATEFORMS_SLUG . '/v' . intval( PIRATEFORMS_API_VERSION ) . '/send_email/' ),
					),
					'nonce'     => wp_create_nonce( 'wp_rest' ),
				),
			)
		);

		wp_enqueue_script( 'pirate-forms-custom-spam', PIRATEFORMS_URL . 'public/js/custom-spam.js', array( 'jquery' ) );
		wp_localize_script(
			'pirate-forms-custom-spam', 'pf', array(
				'spam'   => array(
					'label' => apply_filters( 'pirate_forms_custom_spam_label', __( 'I\'m human!', 'pirate-forms' ) ),
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
		wp_enqueue_style( 'pirate_forms_front_styles' );

		PirateForms_Util::session_start();
		$atts = shortcode_atts(
			array(
				'from' => '',
				'id'   => '',
				'ajax'  => 'no',
			), $atts
		);

		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'displaying shortcode %s', print_r( $atts, true ) ), 'debug', __FILE__, __LINE__ );

		$form_id    = isset( $atts['id'] ) && ! empty( $atts['id'] ) ? intval( $atts['id'] ) : 0;
		$from_widget = ! empty( $atts['from'] );
		$elements    = array();
		$pirate_form = new PirateForms_PhpFormBuilder();

		$pirate_forms_options = PirateForms_Util::get_form_options( $form_id );
		if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_secretkey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_sitekey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) && ( 'yes' === $pirate_forms_options['pirateformsopt_recaptcha_field'] ) ) {
			wp_enqueue_script( 'google-recaptcha' );
		}
		wp_enqueue_script( 'pirate_forms_scripts' );

		$ajax       = 'yes' === $atts['ajax'];
		$elements[] = array(
			'type' => 'hidden',
			'id'   => 'pirate_forms_ajax',
			'name'   => 'pirate_forms_ajax',
			'value' => $ajax ? 1 : 0,
		);

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

		if ( $form_id > 0 ) {
			$pirate_forms_options = apply_filters( 'pirateformpro_get_form_attributes', $pirate_forms_options, $form_id );
			if ( isset( $pirate_forms_options['id'] ) ) {
				// add the form id to the form so that it can be used when we are processing the form
				$elements[] = array(
					'type'  => 'hidden',
					'id'    => 'pirate_forms_form_id',
					'value' => $form_id,
				);
			}
		}

		$nonce_append = $from_widget ? 'yes' : 'no';

		$error_key = wp_create_nonce( get_bloginfo( 'admin_email' ) . ( $from_widget ? 'yes' : 'no' ) );
		$new_error_key          = $nonce_append . '.' . $form_id;

		$thank_you_message = '';
		if ( isset( $_GET['done'] ) && ! empty( $_SESSION[ 'success' . $new_error_key ] ) ) {
			$thank_you_message = $_SESSION[ 'success' . $new_error_key ];
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
		if ( ! empty( $pirate_forms_options ) ) {
			$field = $pirate_forms_options['pirateformsopt_name_field'];
			$label = $pirate_forms_options['pirateformsopt_label_name'];
			$subjectField = $pirate_forms_options['pirateformsopt_subject_field'];

			/**
			 ******  Name field */
			if ( ! empty( $field ) && ! empty( $label ) ) {
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					( ! empty( $subjectField ) ? 'col-xs-12 col-sm-6' : 'col-xs-12' ) . ' contact_name_wrap pirate_forms_three_inputs form_field_wrap',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-name'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'front_end'     => true,
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $field === 'req' ? true : false,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_name'],
					'type'         => 'text',
					'id'           => 'pirate-forms-contact-name',
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-name'] ) ? $_REQUEST['pirate-forms-contact-name'] : '',
					'wrap_class'   => implode( ' ', $wrap_classes ),
				);
			}

			$field = $pirate_forms_options['pirateformsopt_email_field'];
			$label = $pirate_forms_options['pirateformsopt_label_email'];

			/**
			 ******  Email field */
			if ( ! empty( $field ) && ! empty( $label ) ) {
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					( ! empty( $subjectField ) ? 'col-xs-12 col-sm-6' : 'col-xs-12' ) . ' contact_email_wrap pirate_forms_three_inputs form_field_wrap',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-email'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'front_end'     => true,
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $field === 'req' ? true : false,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_email'],
					'type'         => 'email',
					'id'           => 'pirate-forms-contact-email',
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-email'] ) ? $_REQUEST['pirate-forms-contact-email'] : '',
					'wrap_class'   => implode( ' ', $wrap_classes ),
				);
			}

			$field = $pirate_forms_options['pirateformsopt_subject_field'];
			$label = $pirate_forms_options['pirateformsopt_label_subject'];

			/**
			 ******  Subject field */
			if ( ! empty( $field ) && ! empty( $label ) ) {
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					'col-xs-12 contact_subject_wrap pirate_forms_three_inputs form_field_wrap',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-subject'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'front_end'     => true,
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $field === 'req' ? true : false,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_subject'],
					'type'         => 'text',
					'id'           => 'pirate-forms-contact-subject',
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-subject'] ) ? $_REQUEST['pirate-forms-contact-subject'] : '',
					'wrap_class'   => implode( ' ', $wrap_classes ),
				);
			}

			$field = $pirate_forms_options['pirateformsopt_message_field'];
			$label = $pirate_forms_options['pirateformsopt_label_message'];

			/**
			 ******  Message field */
			if ( ! empty( $field ) && ! empty( $label ) ) {
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array(
					'col-xs-12 contact_message_wrap pirate_forms_three_inputs form_field_wrap',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-message'] ) ) {
					$wrap_classes[] = 'error';
				}

				$elements[] = array(
					'front_end'     => true,
					'placeholder'  => stripslashes( sanitize_text_field( $label ) ),
					'required'     => $field === 'req' ? true : false,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_no_content'],
					'type'         => 'textarea',
					'id'           => 'pirate-forms-contact-message',
					'value'        => empty( $thank_you_message ) && isset( $_REQUEST['pirate-forms-contact-message'] ) ? $_REQUEST['pirate-forms-contact-message'] : '',
					'wrap_class'   => implode( ' ', $wrap_classes ),
				);
			}

			$field = $pirate_forms_options['pirateformsopt_attachment_field'];

			/**
			 ******  Attachment field */
			if ( ! empty( $field ) && 'no' !== $field ) {
				$required     = $field === 'req' ? true : false;
				$wrap_classes = array( 'col-xs-12 form_field_wrap contact_attachment_wrap' );
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-attachment'] ) ) {
					$wrap_classes[] = 'error';
				}
				$elements[] = array(
					'front_end'     => true,
					'required'     => $field === 'req' ? true : false,
					'required_msg' => $pirate_forms_options['pirateformsopt_label_err_no_attachment'],
					'type'         => 'file',
					'title'        => __( 'Upload file', 'pirate-forms' ),
					'id'           => 'pirate-forms-attachment',
					'wrap_class'   => implode( ' ', $wrap_classes ),
				);
			}

			if ( array_key_exists( 'pirateformsopt_checkbox_field', $pirate_forms_options ) ) {
				$field = $pirate_forms_options['pirateformsopt_checkbox_field'];
				$label = $pirate_forms_options['pirateformsopt_label_checkbox'];

				/**
				 ******  checkbox field */
				if ( ! empty( $field ) && ! empty( $label ) ) {
					$required     = $field === 'req' ? true : false;
					$wrap_classes = array( 'col-xs-12 form_field_wrap contact_checkbox_wrap  ' );
					// If this field was submitted with invalid data
					if ( isset( $_SESSION[ $error_key ]['contact-checkbox'] ) ) {
						$wrap_classes[] = 'error';
					}
					$elements[] = array(
						'front_end'     => true,
						'required'     => $required,
						'required_msg' => $pirate_forms_options['pirateformsopt_label_err_no_checkbox'],
						'type'         => 'checkbox',
						'id'           => 'pirate-forms-contact-checkbox',
						'wrap'         => array(
							'type'  => 'div',
							'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_checkbox', $wrap_classes ) ),
						),
						'value'        => isset( $_REQUEST['pirate-forms-contact-checkbox'] ) ? $_REQUEST['pirate-forms-contact-checkbox'] : '',
						'options'       => array(
							'yes'       => stripslashes( $label ),
						),
					);
				}
			}

			/**
			 ******* ReCaptcha */
			if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_secretkey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_sitekey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) && 'yes' === $pirate_forms_options['pirateformsopt_recaptcha_field'] ) {
				$pirateformsopt_recaptcha_sitekey   = $pirate_forms_options['pirateformsopt_recaptcha_sitekey'];
				$pirateformsopt_recaptcha_secretkey = $pirate_forms_options['pirateformsopt_recaptcha_secretkey'];
				$elements[]                         = array(
					'front_end'   => true,
					'id'          => 'pirate-forms-captcha',
					'placeholder' => stripslashes( sanitize_text_field( $label ) ),
					'type'        => 'captcha',
					'custom'      => array( 'data-sitekey' => $pirateformsopt_recaptcha_sitekey ),
					'id'          => 'pirate-forms-captcha',
					'wrap'        => array(
						'type'  => 'div',
						'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_captcha', array( 'col-xs-12 ' . ( $pirate_forms_options['pirateformsopt_recaptcha_field'] !== 'yes' ? 'col-sm-6 ' : '' ) . 'form_field_wrap form_captcha_wrap' ) ) ),
					),
				);
			}

			if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) && 'custom' === $pirate_forms_options['pirateformsopt_recaptcha_field'] ) {
				$elements[] = array(
					'front_end' => true,
					'type'  => 'spam',
					'id'    => 'pirate-forms-maps-custom',
				);
			}

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
				'front_end' => true,
				'type'  => 'button',
				'id'    => 'pirate-forms-contact-submit',
				'name'  => 'pirate-forms-contact-submit',
				'class' => 'pirate-forms-submit-button btn btn-primary ' . ( $ajax ? 'pirate-forms-submit-button-ajax' : '' ),
				'wrap'  => array(
					'type'  => 'div',
					'class' => implode( ' ', apply_filters( 'pirateform_wrap_classes_submit', array( 'col-xs-12 ' . ( ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) && ( $pirate_forms_options['pirateformsopt_recaptcha_field'] !== 'yes' ) ) ? 'col-sm-6 ' : '' ) . 'form_field_wrap contact_submit_wrap' ) ) ),
				),
				'value' => $pirateformsopt_label_submit_btn,
			);
		}

		/* Referring site or page, if any */
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$elements[] = array(
				'type'  => 'hidden',
				'id'    => 'pirate-forms-contact-referrer',
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
		if ( ! empty( $_SESSION[ 'error' . $new_error_key ] ) ) {
			$pirate_form->set_element( 'errors', $_SESSION[ 'error' . $new_error_key ] );
			unset( $_SESSION[ 'error' . $new_error_key ] );
		}

		$elements = apply_filters( 'pirate_forms_get_custom_elements', $elements, $pirate_forms_options );

		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'displaying elements %s', print_r( $elements, true ) ), 'debug', __FILE__, __LINE__ );

		$output = $pirate_form->build_form( apply_filters( 'pirate_forms_public_controls', $elements, $pirate_forms_options, $from_widget ), $pirate_forms_options, $from_widget );

		unset( $_SESSION[ 'success' . $new_error_key ] );
		unset( $_SESSION[ 'error' . $new_error_key ] );

		return $output;
	}

	/**
	 * Process the form after submission
	 *
	 * @since    1.0.0
	 * @throws  Exception When file uploading fails.
	 */
	public function template_redirect() {
		if ( ! empty( $_POST ) ) {
			do_action( 'pirate_forms_send_email', false );
		}
	}

	/**
	 * Process the form after submission
	 *
	 * @since    1.0.0
	 * @throws  Exception When file uploading fails.
	 */
	public function send_email( $test = false, $ajax = false, $_post = null, $_files = null ) {
		$post_params    = $_POST;
		$post_files     = $_FILES;

		if ( $ajax ) {
			$post_params    = $_post;
			$post_files     = $_files;
		}
		$this->_post    = $post_params;
		$this->_files   = $post_files;

		PirateForms_Util::session_start();
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'POST data, FILE data = %s, %s', print_r( $post_params, true ), print_r( $post_files, true ) ), 'debug', __FILE__, __LINE__ );

		// If POST and honeypot are not set, beat it
		if ( empty( $post_params ) || ! isset( $post_params['honeypot'] ) ) {
			return false;
		}

		// separate the nonce from a form that is displayed in the widget vs. one that is not
		$nonce_append = isset( $post_params['pirate_forms_from_widget'] ) && intval( $post_params['pirate_forms_from_widget'] ) === 1 ? 'yes' : 'no';

		// Session variable for form errors
		$error_key              = wp_create_nonce( get_bloginfo( 'admin_email' ) . $nonce_append );
		$_SESSION[ $error_key ] = array();
		$form_id                = isset( $post_params['pirate_forms_form_id'] ) ? $post_params['pirate_forms_form_id'] : 0;
		$new_error_key          = $nonce_append . '.' . $form_id;

		// If nonce is not valid, beat it.
		if ( ! $test && 'yes' === PirateForms_Util::get_option( 'pirateformsopt_nonce' ) ) {
			if ( ! wp_verify_nonce( $post_params['wordpress-nonce'], get_bloginfo( 'admin_email' ) . $nonce_append ) ) {
				$_SESSION[ $error_key ]['nonce'] = __( 'Nonce failed!', 'pirate-forms' );
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, 'Nonce failed', 'error', __FILE__, __LINE__ );
				return PirateForms_Util::save_error( $error_key, $new_error_key );
			}
		}

		// If the honeypot caught a bear, beat it
		if ( ! empty( $post_params['honeypot'] ) ) {
			$_SESSION[ $error_key ]['honeypot'] = __( 'Form submission failed!', 'pirate-forms' );
			return PirateForms_Util::save_error( $error_key, $new_error_key );
		}

		$pirate_forms_options = PirateForms_Util::get_form_options( $form_id );

		if ( ! $this->validate_spam( $error_key, $pirate_forms_options ) ) {
			return PirateForms_Util::save_error( $error_key, $new_error_key );
		}

		// Start the body of the contact email
		$private_fields  = array();
		$body            = array();
		$body['heading'] = sprintf( __( 'Contact form submission from %s', 'pirate-forms' ), get_bloginfo( 'name' ) . ' (' . site_url() . ')' );
		$body['body']    = array();
		// lets collect the values for ALL potential magic tags in the form $tag_name => $tag_value e.g. 'name' => 'some name'.
		// the tag name should be without the curly braces.
		$body['magic_tags'] = array();

		list( $pirate_forms_contact_email, $pirate_forms_contact_name, $pirate_forms_contact_subject, $msg ) = $this->validate_request( $error_key, $pirate_forms_options, $body );

		/**
		 ******** Validate recipients email */
		$site_recipients = sanitize_text_field( $pirate_forms_options['pirateformsopt_email_recipients'] );
		if ( empty( $site_recipients ) ) {
			$_SESSION[ $error_key ]['pirate-forms-recipients-email'] = __( 'Please enter one or more Contact submission recipients', 'pirate-forms' );
		}

		$contact_ip     = null;
		if ( isset( $pirate_forms_options['pirateformsopt_store_ip'] ) && 'yes' === $pirate_forms_options['pirateformsopt_store_ip'] ) {
			$contact_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
			/* for the case of a Web server behind a reverse proxy */
			if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ) {
				$contact_ip_tmp = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
				if ( ! empty( $contact_ip_tmp ) ) {
					$contact_ip = array_pop( $contact_ip_tmp );
				}
			}

			// If valid and present, create a link to an IP search
			$body['body'][ __( 'IP address', 'pirate-forms' ) ] = $contact_ip;
			$body['body'][ __( 'IP search', 'pirate-forms' ) ]  = "http://whatismyipaddress.com/ip/$contact_ip";
			$body['magic_tags'] += array( 'ip' => $contact_ip );

			$private_fields[]   = __( 'IP address', 'pirate-forms' );
			$private_fields[]   = __( 'IP search', 'pirate-forms' );
		}

		// Sanitize and prepare referrer;
		if ( ! empty( $post_params['pirate-forms-contact-referrer'] ) ) {
			$page                                               = sanitize_text_field( $post_params['pirate-forms-contact-referrer'] );
			$body['body'][ __( 'Came from', 'pirate-forms' ) ]  = $page;
			$body['magic_tags'] += array( 'referer' => $page );

			$private_fields[]   = __( 'Came from', 'pirate-forms' );
		}

		// Show the page this contact form was submitted on
		$permalink                                              = get_permalink( get_the_id() );
		$body['body'][ __( 'Sent from page', 'pirate-forms' ) ] = $permalink;
		$body['magic_tags'] += array( 'permalink' => $permalink );

		$private_fields[]   = __( 'Sent from page', 'pirate-forms' );

		// Check the blacklist
		$blocked = PirateForms_Util::is_blacklisted( $error_key, $pirate_forms_contact_email, $contact_ip );
		if ( $blocked ) {
			return PirateForms_Util::save_error( $error_key, $new_error_key );
		}

		if ( $this->is_spam( $pirate_forms_options, $contact_ip, get_permalink( get_the_id() ), $msg ) ) {
			$_SESSION[ $error_key ]['honeypot'] = __( 'Form submission failed!!', 'pirate-forms' );
		}

		if ( ! empty( $_SESSION[ $error_key ] ) ) {
			return PirateForms_Util::save_error( $error_key, $new_error_key );
		}

		$_SESSION[ 'success' . $new_error_key ] = sanitize_text_field( $pirate_forms_options['pirateformsopt_label_submit'] );

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

		$site_email = sanitize_text_field( $pirate_forms_options['pirateformsopt_email'] );
		if ( ! empty( $pirate_forms_contact_name ) ) {
			$site_name = $pirate_forms_contact_name;
		} else {
			$site_name = htmlspecialchars_decode( get_bloginfo( 'name' ) );
		}

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
		// Send an email notification to the correct address
		$headers = "From: $send_from_name <$send_from>\r\nReply-To: $pirate_forms_contact_name <$pirate_forms_contact_email>\r\nContent-type: text/html";
		add_action( 'phpmailer_init', array( $this, 'phpmailer' ) );

		list( $attachments, $attachments_relative ) = $this->get_attachments( $error_key, $pirate_forms_options, $form_id, $body );
		if ( is_bool( $attachments ) ) {
			return PirateForms_Util::save_error( $error_key, $new_error_key );
		}

		$subject = apply_filters( 'pirate_forms_subject', 'Contact on ' . htmlspecialchars_decode( get_bloginfo( 'name' ) ) );
		if ( ! empty( $pirate_forms_contact_subject ) ) {
			$subject = $pirate_forms_contact_subject;
		}

		$mail_body = ! empty( $pirate_forms_options['pirateformsopt_email_content'] ) ? $pirate_forms_options['pirateformsopt_email_content'] : PirateForms_Util::get_default_email_content( true, $form_id, true );

		$mail_body = PirateForms_Util::replace_magic_tags( $mail_body, $body );

		do_action( 'pirate_forms_before_sending', $pirate_forms_contact_email, $site_recipients, $subject, $mail_body, $headers, $attachments );
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'before sending email to = %s, subject = %s, body = %s, headers = %s, attachments = %s', $site_recipients, $subject, $mail_body, $headers, print_r( $attachments, true ) ), 'debug', __FILE__, __LINE__ );
		$response = $this->finally_send_mail( $site_recipients, $subject, $mail_body, $headers, $attachments, true );
		if ( ! $response ) {
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, 'Email not sent', 'debug', __FILE__, __LINE__ );
			error_log( 'Email not sent' );
		}
		do_action( 'pirate_forms_after_sending', $pirate_forms_options, $response, $pirate_forms_contact_email, $site_recipients, $subject, $mail_body, $headers, $attachments );
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'after sending email, response = %s', $response ), 'debug', __FILE__, __LINE__ );

		$this->delete_tmp_dir();

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

			if ( isset( $pirate_forms_options['pirateformsopt_copy_email'] ) && 'yes' === $pirate_forms_options['pirateformsopt_copy_email'] ) {
				$confirm_body = $this->append_original_email( $confirm_body, $body, $private_fields );
			}

			do_action( 'pirate_forms_before_sending_confirm', $pirate_forms_contact_email, $pirate_forms_contact_email, $subject, $confirm_body, $headers );
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'before sending confirm email to = %s, subject = %s, body = %s, headers = %s', $pirate_forms_contact_email, $subject, $confirm_body, $headers ), 'debug', __FILE__, __LINE__ );
			$response_confirm = $this->finally_send_mail( $pirate_forms_contact_email, $subject, $confirm_body, $headers, null, false );
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
			add_post_meta( $new_post_id, PIRATEFORMS_SLUG . 'mail-status', $response ? 'true' : 'false' );
			if ( defined( 'PIRATEFORMS_EMAIL_ERROR' ) ) {
				add_post_meta( $new_post_id, PIRATEFORMS_SLUG . 'mail-status-reason', PIRATEFORMS_EMAIL_ERROR );
			}
			add_post_meta( $new_post_id, PIRATEFORMS_SLUG . 'attachments', $attachments_relative );
			add_post_meta( $new_post_id, PIRATEFORMS_SLUG . 'confirm-mail-status', $response_confirm ? 'true' : 'false' );
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

			if ( $ajax ) {
				return true;
			}

			$redirect_to        = null;
			$scroll_to          = isset( $post_params['pirate_forms_from_form'] ) ? $post_params['pirate_forms_from_form'] : '';

			/* If a Thank you page is selected, redirect to that page */
			if ( $pirate_forms_options['pirateformsopt_thank_you_url'] ) {
				$redirect_id = intval( $pirate_forms_options['pirateformsopt_thank_you_url'] );
				$redirect    = get_permalink( $redirect_id );
				if ( ! empty( $redirect ) ) {
					$redirect_to    = $redirect;
				}
			} elseif ( $is_our_theme ) {
				// the fragment identifier should always be the last argument, otherwise the thank you message will not show.
				// the fragment identifier is called pcf here so that the URL can tell us if our theme was recognized.
				$redirect_to = add_query_arg(
					array(
						'done' => 'done',
						'pcf'  => "#$scroll_to",
					), $_SERVER['HTTP_REFERER']
				);
			} elseif ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				// the fragment identifier is called pf here so that the URL can tell us if this is a not-our theme case.
				$redirect_to = add_query_arg(
					array(
						'done' => 'done',
						'pf'    => "#$scroll_to",
					), $_SERVER['HTTP_REFERER']
				);
			}

			if ( $redirect_to ) {
				wp_safe_redirect( $redirect_to );
				exit();
			}
		}
	}

	/**
	 * Appends the publicly displayed fields to the confirmation email.
	 *
	 * @param string $confirm_body The confirmation body.
	 * @param array  $body The collected body fields.
	 * @param array  $private_fields The private fields that should not be included from the body fields.
	 */
	private function append_original_email( $confirm_body, $body, $private_fields ) {
		$lines      = array();
		$lines[]    = '------ Original Email ------';

		foreach ( $body['body'] as $field => $value ) {
			if ( in_array( $field, $private_fields ) ) {
				continue;
			}
			$lines[] = "$field : $value";
		}

		return $confirm_body . implode( '<br/>', $lines );
	}

	/**
	 * Finally, really send email.
	 *
	 * @param string $to the email recipient.
	 * @param string $subject the email subject.
	 * @param string $body the email body.
	 * @param array  $headers the email headers.
	 * @param string $attachments the email attachments.
	 * @param bool   $capture_failure whether to capture failure reason or not.
	 */
	private function finally_send_mail( $to, $subject, $body, $headers, $attachments, $capture_failure = true ) {
		if ( $capture_failure ) {
			add_action( 'wp_mail_failed', array( $this, 'mail_sending_error' ) );
		}
		return wp_mail( $to, $subject, $body, $headers, $attachments );
	}

	/**
	 * Capture the reason for email failure.
	 *
	 * @param WP_Error $error the error object.
	 */
	public function mail_sending_error( $error ) {
		if ( is_wp_error( $error ) ) {
			if ( ! defined( 'PIRATEFORMS_EMAIL_ERROR' ) ) {
				define( 'PIRATEFORMS_EMAIL_ERROR', $error->get_error_message() );
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
				$captcha = $this->_post['g-recaptcha-response'];
			}
			if ( ! $captcha ) {
				$_SESSION[ $error_key ]['pirate-forms-captcha'] = __( 'Invalid CAPTCHA', 'pirate-forms' );

				return false;
			}
			$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $pirateformsopt_recaptcha_secretkey . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'] );
			if ( ! empty( $response ) ) {
				$response_body = wp_remote_retrieve_body( $response );
			}
			if ( ! empty( $response_body ) ) {
				$result = json_decode( $response_body, true );
			}
			if ( isset( $result['success'] ) && ( $result['success'] == false ) ) {
				$_SESSION[ $error_key ]['pirate-forms-captcha'] = __( 'Incorrect CAPTCHA', 'pirate-forms' );

				return false;
			}
		} elseif ( 'custom' === $pirateformsopt_recaptcha_field ) {
			if ( isset( $this->_post['xobkcehc'] ) && wp_verify_nonce( $this->_post['xobkcehc'], PIRATEFORMS_NAME ) ) {
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
		$fields          = PirateForms_Util::$DEFAULT_FIELDS;

		foreach ( $fields as $field ) {
			$value = isset( $this->_post[ 'pirate-forms-contact-' . $field ] ) ? sanitize_text_field( trim( $this->_post[ 'pirate-forms-contact-' . $field ] ) ) : '';
			$body['magic_tags'] += array( $field => $value );

			if ( ! array_key_exists( 'pirateformsopt_' . $field . '_field', $pirate_forms_options ) ) {
				continue;
			}

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
			$temp         = apply_filters( 'pirate_forms_validate_request', $temp, $error_key, $pirate_forms_options, $this->_post, $this->_files );
			$body['rows'] = $temp;
		} else {
			// new lite, new pro
			$body = apply_filters( 'pirate_forms_validate_request', $body, $error_key, $pirate_forms_options, $this->_post, $this->_files );
		}

		return array( $contact_email, $contact_name, $contact_subject, $message );
	}

	/**
	 * Check with akismet if the message is spam.
	 */
	function is_spam( $pirate_forms_options, $ip, $page_url, $msg ) {
		if ( 'yes' !== PirateForms_Util::get_option( 'pirateformsopt_akismet' ) ) {
			return false;
		}

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
	 * @param int    $form_id the form id.
	 * @param array  $body the body of the mail.
	 *
	 * @throws  Exception When file uploading fails.
	 */
	private function get_attachments( $error_key, $pirate_forms_options, $form_id, &$body ) {
		$attachments = array();
		$has_files   = $pirate_forms_options['pirateformsopt_attachment_field'];
		if ( ! empty( $has_files ) ) {
			$_files     = $this->_files;

			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'got %d files', count( $_files ) ), 'debug', __FILE__, __LINE__ );

			foreach ( $_files as $label => $file ) {
				if ( empty( $file['name'] ) ) {
					continue;
				}
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'processing %s file', $file['name'] ), 'debug', __FILE__, __LINE__ );
				/* Validate file type */
				$allowed = implode( '|', apply_filters( 'pirate_forms_allowed_file_types', self::$_file_types_allowed ) );
				$pirate_forms_file_types_allowed = '/\.(' . trim( $allowed, '|' ) . ')$/i';
				if ( ! preg_match( $pirate_forms_file_types_allowed, $file['name'] ) ) {
					do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'file invalid: expected %s got %s', $allowed, $file['name'] ), 'error', __FILE__, __LINE__ );
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
				$new_file   = $this->download_file( $error_key, $pirate_forms_options, $form_id, $file );
				if ( ! empty( $new_file ) ) {
					$attachments[] = $new_file;
				}
			}
		}
		$attachment_relative    = array();
		if ( $attachments ) {
			foreach ( $attachments as $file ) {
				$attachment_relative[] = _wp_relative_upload_path( $file );
			}
			$body['body'][ __( 'Attachment', 'pirate-forms' ) ] = implode( ',', $attachment_relative );
			$body['magic_tags'] += array( 'attachments' => $this->get_attachment_links( $attachment_relative ) );
		}
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'finally attaching attachment(s): %s', print_r( $attachments, true ) ), 'info', __FILE__, __LINE__ );

		return array( $attachments, $attachment_relative );
	}

	/**
	 * Show the links to download the attachment.
	 */
	private function get_attachment_links( $files ) {
		$urls       = array();
		foreach ( $files as $file ) {
			$path   = $this->get_upload_dir( 'dir' ) . "/$file";
			$url    = $this->get_upload_dir( 'url' ) . "/$file";
			$urls[] = "<a href='$url'>" . basename( $path ) . '</a>';
		}
		return implode( ',', $urls );
	}

	/**
	 * Download the attachment.
	 *
	 * @throws Exception When file could not be moved.
	 */
	private function download_file( $error_key, $pirate_forms_options, $form_id, $file ) {
		$uploads_dir = $this->get_upload_dir_with_folder( 'tmp' );
		$uploads_dir = $this->maybe_add_random_dir( $uploads_dir );
		$save_file   = array_key_exists( 'pirateformsopt_save_attachment', $pirate_forms_options ) && ! empty( $pirate_forms_options['pirateformsopt_save_attachment'] );
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'saving file = *%s*', $save_file ), 'debug', __FILE__, __LINE__ );
		if ( $save_file ) {
			// change uploads directory to /saved instead of /tmp.
			$dir_new = $this->get_upload_dir_with_folder( "saved/$form_id" );
			if ( ! wp_mkdir_p( $dir_new ) ) {
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'unable to create directory %s; saving in %s', $dir_new, $uploads_dir ), 'error', __FILE__, __LINE__ );
			} else {
				$save_dir = $this->maybe_add_random_dir( $dir_new );
				$uploads_dir = $save_dir;
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'saving file in %s', $uploads_dir ), 'debug', __FILE__, __LINE__ );
			}
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
		return $new_file;
	}

	/**
	 * Delete the temporary upload dir
	 */
	private function delete_tmp_dir() {
		// delete the tmp directory
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->delete( $this->get_upload_dir_with_folder( 'tmp' ), true, 'd' );
	}

	/**
	 * Return the temporary/permanent upload dir
	 *
	 * @since    1.0.0
	 */
	function get_upload_dir_with_folder( $folder ) {
		return $this->get_upload_dir( 'dir' ) . "/pirate-forms/$folder";
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
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'unable to create random tmp directory %s', $dir_new ), 'error', __FILE__, __LINE__ );

		return $dir;
	}

	/**
	 * Prepare the uploading process
	 *
	 * @since    1.0.0
	 * @throws   Exception When file could not be opened.
	 */
	function init_uploads() {
		$dir = $this->get_upload_dir_with_folder( 'tmp' );
		if ( ! wp_mkdir_p( $dir ) ) {
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'unable to create parent directories of %s', $dir ), 'error', __FILE__, __LINE__ );
		}

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
		$id                                     = null;
		if ( isset( $this->_post['pirate_forms_form_id'] ) && ! empty( $this->_post['pirate_forms_form_id'] ) ) {
			$id                                 = $this->_post['pirate_forms_form_id'];
		}
		$pirate_forms_options                   = PirateForms_Util::get_form_options( $id );
		$pirateformsopt_use_smtp                = $pirate_forms_options['pirateformsopt_use_smtp'];
		$pirateformsopt_smtp_host               = $pirate_forms_options['pirateformsopt_smtp_host'];
		$pirateformsopt_smtp_port               = $pirate_forms_options['pirateformsopt_smtp_port'];
		$pirateformsopt_smtp_username           = $pirate_forms_options['pirateformsopt_smtp_username'];
		$pirateformsopt_smtp_password           = $pirate_forms_options['pirateformsopt_smtp_password'];
		$pirateformsopt_use_secure              = $pirate_forms_options['pirateformsopt_use_secure'];
		$pirateformsopt_use_smtp_authentication = $pirate_forms_options['pirateformsopt_use_smtp_authentication'];
		if ( ! empty( $pirateformsopt_use_smtp ) && ( $pirateformsopt_use_smtp == 'yes' ) && ! empty( $pirateformsopt_smtp_host ) && ! empty( $pirateformsopt_smtp_port ) ) {
			// @codingStandardsIgnoreStart
			$phpmailer->isSMTP();
			$phpmailer->Host = $pirateformsopt_smtp_host;
			if ( ! empty( $pirateformsopt_use_smtp_authentication ) && ( $pirateformsopt_use_smtp_authentication == 'yes' ) && ! empty( $pirateformsopt_smtp_username ) && ! empty( $pirateformsopt_smtp_password ) ) {
				$phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
				$phpmailer->Port     = $pirateformsopt_smtp_port;
				$phpmailer->Username = $pirateformsopt_smtp_username;
				$phpmailer->Password = $pirateformsopt_smtp_password;
			}

			if ( ! empty( $pirateformsopt_use_secure ) ) {
				$phpmailer->SMTPSecure = $pirateformsopt_use_secure;
			}
			// @codingStandardsIgnoreEnd
		}

		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'phpmailer config %s', print_r( $phpmailer, true ) ), 'debug', __FILE__, __LINE__ );
	}

	/**
	 * Alter classes and wrapper of form elements for compatibility reasons with different themes.
	 *
	 * @param array $elements The form elements.
	 *
	 * @return array The form elements.
	 */
	public function compatibility_class( $elements ) {
		$theme      = null;
		if ( function_exists( 'zerif_setup' ) ) {
			$theme  = 'zerif';
		} else {
			$current_theme  = wp_get_theme();
			$theme          = sanitize_key( $current_theme->get( 'Name' ) );
		}

		if ( $theme ) {
			foreach ( $elements as $k => $element ) {
				if ( 'hidden' === $element['type'] ) {
					continue;
				}
				$id = str_replace( 'pirate-forms-contact-', '', $element['id'] );
				if ( method_exists( $this, "{$theme}_customization_wrap" ) ) {
					add_filter( "pirateform_wrap_classes_{$id}", array( $this, "{$theme}_customization_wrap" ), 10, 3 );
				}
				if ( method_exists( $this, "{$theme}_customization_field" ) ) {
					add_filter( "pirateform_field_classes_{$id}", array( $this, "{$theme}_customization_field" ), 10, 3 );
				}
			}
		}

		return $elements;
	}

	/**
	 * Alter classes of form elements for compatibility reasons with Zerif.
	 *
	 * @param string $classes The classes.
	 * @param string $name The name of the element.
	 * @param string $type The type of the element.
	 *
	 * @return string The classes to apply.
	 */
	public function zerif_customization_field( $classes, $name = null, $type = null ) {
		if ( $type ) {
			switch ( $type ) {
				case 'text':
					// fall-through.
				case 'tel':
					// fall-through.
				case 'number':
					// fall-through.
				case 'textarea':
					$classes    = 'form-control input';
					break;
				case 'button':
					$classes    = 'btn btn-primary custom-button red-btn pirate-forms-submit-button';
					break;
			}
		}

		return $classes;
	}

	/**
	 * Alter wrap classes of form elements for compatibility reasons with Zerif.
	 *
	 * @param array  $classes The classes.
	 * @param string $name The name of the element.
	 * @param string $type The type of the element.
	 *
	 * @return array The classes to apply.
	 */
	public function zerif_customization_wrap( $classes, $name = null, $type = null ) {
		if ( $type ) {
			switch ( $type ) {
				case 'checkbox':
					$classes[] = 'pirate_forms_three_inputs_wrap';
					break;
			}
		}

		if ( $name ) {
			switch ( $name ) {
				case 'name':
					// fall-through.
				case 'email':
					// fall-through.
				case 'subject':
					$classes    = array( 'col-lg-4 col-sm-4 form_field_wrap' );
					break;
				case 'message':
					$classes    = array( 'col-lg-12 col-sm-12 form_field_wrap' );
					break;
			}
		}
		return $classes;
	}

	/**
	 * Register REST endpoints.
	 */
	public function register_endpoint() {
		register_rest_route(
			PIRATEFORMS_SLUG . '/v' . intval( PIRATEFORMS_API_VERSION ),
			'/send_email/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'send_email_ajax' ),
			)
		);
	}

	/**
	 * The REST endpoint for sending email.
	 */
	public function send_email_ajax( WP_REST_Request $request ) {
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'calling ajax with %s, %s', print_r( $request->get_params(), true ), print_r( $request->get_file_params(), true ) ), 'debug', __FILE__, __LINE__ );

		$return     = $this->send_email( false, true, $request->get_params(), $request->get_file_params() );
		$form_id    = intval( $request->get_param( 'pirate_forms_form_id' ) );

		// errors?
		if ( is_bool( $return ) && ! $return ) {
			$nonce_append   = intval( $request->get_param( 'pirate_forms_from_widget' ) ) === 1 ? 'yes' : 'no';
			$errors         = $_SESSION[ 'error' . $nonce_append . '.' . $form_id ];
			if ( $errors ) {
				$messages   = array();
				foreach ( $errors as $k => $v ) {
					$messages[] = $v;
				}
				do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'got errors %s', print_r( $messages, true ) ), 'debug', __FILE__, __LINE__ );
				return new WP_REST_Response( array( 'error' => $this->display_errors( $messages ) ), 500 );
			}
		}

		// redirect?
		$pirate_forms_options = PirateForms_Util::get_form_options( $form_id );
		if ( $pirate_forms_options['pirateformsopt_thank_you_url'] ) {
			$redirect_id = intval( $pirate_forms_options['pirateformsopt_thank_you_url'] );
			$redirect    = get_permalink( $redirect_id );
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'redirecting to %s', $redirect ), 'debug', __FILE__, __LINE__ );
			if ( ! empty( $redirect ) ) {
				return new WP_REST_Response( array( 'redirect' => $redirect ), 200 );
			}
		}

		// thank you message.
		$message    = $this->display_thankyou( sanitize_text_field( $pirate_forms_options['pirateformsopt_label_submit'] ) );
		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'thankyou message: %s', $message ), 'debug', __FILE__, __LINE__ );
		return new WP_REST_Response( array( 'message' => $message ), 200 );
	}

	/**
	 * Displays the thank you message relevant to the form
	 *
	 * @param string $text The message.
	 */
	private function display_thankyou( $text ) {
		return apply_filters( 'pirate_forms_thankyou', sprintf( '<div class="col-xs-12 pirate_forms_thankyou_wrap"><p>%s</p></div>', $text ), $text );
	}

	/**
	 * Displays all the errors relevant to the form
	 *
	 * @param Array $errors The error messages.
	 */
	private function display_errors( $errors ) {
		$output = '';
		if ( ! empty( $errors ) ) {
			$output .= '<div class="col-xs-12 pirate_forms_error_box">';
			$output .= '<p>' . __( 'Sorry, an error occured.', 'pirate-forms' ) . '</p>';
			$output .= '</div>';
			foreach ( $errors as $err ) {
				$output .= '<div class="col-xs-12 pirate_forms_error_box">';
				$output .= "<p>$err</p>";
				$output .= '</div>';
			}
		}
		return apply_filters( 'pirate_forms_errors', $output, $errors );
	}
}
