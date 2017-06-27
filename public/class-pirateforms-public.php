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
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles_and_scripts() {

		/* style for frontpage contact */
		wp_enqueue_style( 'pirate_forms_front_styles', PIRATEFORMS_URL__ . 'public/css/front.css' );
		/* recaptcha js */
		$pirate_forms_options = get_option( 'pirate_forms_settings_array' );
		if ( ! empty( $pirate_forms_options ) ) :
			if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_secretkey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_sitekey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) && ( $pirate_forms_options['pirateformsopt_recaptcha_field'] == 'yes' ) ) :
				if ( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_current_language' ) ) {
					$pirate_forms_contactus_language = pll_current_language();
				} else {
					$pirate_forms_contactus_language = get_locale();
				}
				wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $pirate_forms_contactus_language . '' );
				wp_enqueue_script( 'pirate_forms_scripts', PIRATEFORMS_URL__ . 'public/js/scripts.js', array(
					'jquery',
					'recaptcha',
				) );
			endif;
		endif;
		wp_enqueue_script( 'pirate_forms_scripts_general', PIRATEFORMS_URL__ . 'public/js/scripts-general.js', array( 'jquery' ) );
		$pirate_forms_errors = '';
		if ( ! empty( $_SESSION['pirate_forms_contact_errors'] ) ) :
			$pirate_forms_errors = $_SESSION['pirate_forms_contact_errors'];
		endif;
		wp_localize_script( 'pirate_forms_scripts_general', 'pirateFormsObject', array(
			'errors' => $pirate_forms_errors,
		) );
	}

	/**
	 * Process the form after submission
	 *
	 * @since    1.0.0
	 * @throws  Exception When file uploading fails.
	 */
	public function pirate_forms_process_contact() {
		// If POST and honeypot are not set, beat it
		if ( empty( $_POST ) || ! isset( $_POST['honeypot'] ) ) {
			return false;
		}

		// separate the nonce from a form that is displayed in the widget vs. one that is not
		$nonce_append   = isset( $_POST['pirate_forms_from_widget'] ) && intval( $_POST['pirate_forms_from_widget'] ) === 1 ? 'yes' : 'no';

		// Session variable for form errors
		$error_key          = wp_create_nonce( get_bloginfo( 'admin_email' ) . $nonce_append );
		$_SESSION[ $error_key ] = array();
		// If nonce is not valid, beat it
		if ( 'yes' === PirateForms::pirate_forms_get_key( 'pirateformsopt_nonce' ) ) {
			if ( ! wp_verify_nonce( $_POST['wordpress-nonce'], get_bloginfo( 'admin_email' ) . $nonce_append ) ) {
				$_SESSION[ $error_key ]['nonce'] = __( 'Nonce failed!', 'pirate-forms' );

				return false;
			}
		}
		// If the honeypot caught a bear, beat it
		if ( ! empty( $_POST['honeypot'] ) ) {
			$_SESSION[ $error_key ]['honeypot'] = __( 'Form submission failed!', 'pirate-forms' );

			return false;
		}
		// Start the body of the contact email
		$body = '<h2>' . __( 'Contact form submission from', 'pirate-forms' ) . ' ' .
				get_bloginfo( 'name' ) . ' (' . site_url() . ') </h2>';

		$body .= '<table>';
		/**
		 *******   Sanitize and validate name */
		$pirate_forms_contact_name = isset( $_POST['pirate-forms-contact-name'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-name'] ) ) : '';
		// if name is required and is missing
		if ( ( PirateForms::pirate_forms_get_key( 'pirateformsopt_name_field' ) === 'req' ) && empty( $pirate_forms_contact_name ) ) {
			$_SESSION[ $error_key ]['pirate-forms-contact-name'] = PirateForms::pirate_forms_get_key( 'pirateformsopt_label_err_name' );
		} // End if().
		elseif ( ! empty( $pirate_forms_contact_name ) ) {
			$body .= $this->pirate_forms_table_row( stripslashes( PirateForms::pirate_forms_get_key( 'pirateformsopt_label_name' ) ), $pirate_forms_contact_name );
		}
		/**
		 *****  Sanitize and validate email */
		$pirate_forms_contact_email = isset( $_POST['pirate-forms-contact-email'] ) ? sanitize_email( $_POST['pirate-forms-contact-email'] ) : '';
		// If required, is it valid?
		if ( ( PirateForms::pirate_forms_get_key( 'pirateformsopt_email_field' ) === 'req' ) && ! filter_var( $pirate_forms_contact_email, FILTER_VALIDATE_EMAIL ) ) {
			$_SESSION[ $error_key ]['pirate-forms-contact-email'] = PirateForms::pirate_forms_get_key( 'pirateformsopt_label_err_email' );
		} // End if().
		elseif ( ! empty( $pirate_forms_contact_email ) ) {
			$body .= $this->pirate_forms_table_row( stripslashes( PirateForms::pirate_forms_get_key( 'pirateformsopt_label_email' ) ), $pirate_forms_contact_email );
		}
		/**
		 *******   Sanitize and validate subject */
		$pirate_forms_contact_subject = isset( $_POST['pirate-forms-contact-subject'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-subject'] ) ) : '';
		// if subject is required and is missing
		if ( ( PirateForms::pirate_forms_get_key( 'pirateformsopt_subject_field' ) === 'req' ) && empty( $pirate_forms_contact_subject ) ) {
			$_SESSION[ $error_key ]['pirate-forms-contact-subject'] = PirateForms::pirate_forms_get_key( 'pirateformsopt_label_err_subject' );
		} // End if().
		elseif ( ! empty( $pirate_forms_contact_subject ) ) {
			$body .= $this->pirate_forms_table_row( stripslashes( PirateForms::pirate_forms_get_key( 'pirateformsopt_label_subject' ) ), $pirate_forms_contact_subject );
		}
		/**
		 *******   Sanitize and validate message */
		$pirate_forms_contact_message = isset( $_POST['pirate-forms-contact-message'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-message'] ) ) : '';
		// if message is required and is missing
		if ( ( PirateForms::pirate_forms_get_key( 'pirateformsopt_message_field' ) === 'req' ) && empty( $pirate_forms_contact_message ) ) {
			$_SESSION[ $error_key ]['pirate-forms-contact-message'] = PirateForms::pirate_forms_get_key( 'pirateformsopt_label_err_message' );
		} // End if().
		elseif ( ! empty( $pirate_forms_contact_message ) ) {
			$body .= $this->pirate_forms_table_row( stripslashes( PirateForms::pirate_forms_get_key( 'pirateformsopt_label_message' ) ), $pirate_forms_contact_message );
		}
		/**
		 *********** Validate reCAPTCHA */
		$pirateformsopt_recaptcha_sitekey   = PirateForms::pirate_forms_get_key( 'pirateformsopt_recaptcha_sitekey' );
		$pirateformsopt_recaptcha_secretkey = PirateForms::pirate_forms_get_key( 'pirateformsopt_recaptcha_secretkey' );
		$pirateformsopt_recaptcha_field     = PirateForms::pirate_forms_get_key( 'pirateformsopt_recaptcha_field' );
		if ( ! empty( $pirateformsopt_recaptcha_secretkey ) && ! empty( $pirateformsopt_recaptcha_sitekey ) && ! empty( $pirateformsopt_recaptcha_field ) && ( $pirateformsopt_recaptcha_field == 'yes' ) ) :
			if ( isset( $_POST['g-recaptcha-response'] ) ) {
				$captcha = $_POST['g-recaptcha-response'];
			}
			if ( ! $captcha ) {
				$_SESSION[ $error_key ]['pirate-forms-captcha'] = __( 'Wrong reCAPTCHA', 'pirate-forms' );
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
			}
		endif;
		/**
		 ******** Validate recipients email */
		$site_recipients = sanitize_text_field( PirateForms::pirate_forms_get_key( 'pirateformsopt_email_recipients' ) );
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
			$body .= $this->pirate_forms_table_row( __( 'IP address: ', 'pirate-forms' ), $contact_ip );
			$body .= $this->pirate_forms_table_row( __( 'IP search:', 'pirate-forms' ), "http://whatismyipaddress.com/ip/$contact_ip" );
		}
		// Sanitize and prepare referrer;
		if ( ! empty( $_POST['pirate-forms-contact-referrer'] ) ) {
			$body .= $this->pirate_forms_table_row( __( 'Came from: ', 'pirate-forms' ), sanitize_text_field( $_POST['pirate-forms-contact-referrer'] ) );
		}
		// Show the page this contact form was submitted on
		$body .= $this->pirate_forms_table_row( __( 'Sent from page: ', 'pirate-forms' ), get_permalink( get_the_id() ) );
		// Check the blacklist
		$blocked = $this->pirate_forms_get_blacklist();
		if ( ! empty( $blocked ) ) {
			if (
				in_array( $pirate_forms_contact_email, $blocked ) ||
				in_array( $contact_ip, $blocked )
			) {
				$_SESSION[ $error_key ]['blacklist-blocked'] = __( 'Form submission blocked!', 'pirate-forms' );

				return false;
			}
		}
		$body .= '</table>';

		// No errors? Go ahead and process the contact
		if ( empty( $_SESSION[ $error_key ] ) ) {
			$pirate_forms_options_tmp = get_option( 'pirate_forms_settings_array' );
			if ( isset( $pirate_forms_options_tmp['pirateformsopt_email'] ) ) {
				$site_email = $pirate_forms_options_tmp['pirateformsopt_email'];
			}
			if ( ! empty( $pirate_forms_contact_name ) ) :
				$site_name = $pirate_forms_contact_name;
			else :
				$site_name = htmlspecialchars_decode( get_bloginfo( 'name' ) );
			endif;
			// Notification recipients
			$site_recipients = sanitize_text_field( PirateForms::pirate_forms_get_key( 'pirateformsopt_email_recipients' ) );
			$site_recipients = explode( ',', $site_recipients );
			$site_recipients = array_map( 'trim', $site_recipients );
			$site_recipients = array_map( 'sanitize_email', $site_recipients );
			$site_recipients = implode( ',', $site_recipients );
			// No name? Use the submitter email address, if one is present
			if ( empty( $pirate_forms_contact_name ) ) {
				$pirate_forms_contact_name = ! empty( $pirate_forms_contact_email ) ? $pirate_forms_contact_email : '[None given]';
			}
			// Need an email address for the email notification
			if ( ! empty( $site_email ) ) {
				if ( $site_email == '[email]' ) {
					if ( ! empty( $pirate_forms_contact_email ) ) {
						$send_from = $pirate_forms_contact_email;
					} else {
						$send_from = PirateForms::pirate_forms_from_email();
					}
				} else {
					$send_from = $site_email;
				}
			} else {
				$send_from = PirateForms::pirate_forms_from_email();
			}
			$send_from_name = $site_name;
			// Sent an email notification to the correct address
			$headers = "From: $send_from_name <$send_from>\r\nReply-To: $pirate_forms_contact_name <$pirate_forms_contact_email>\r\nContent-type: text/html";
			add_action( 'phpmailer_init', array( $this, 'pirate_forms_phpmailer' ) );
			/**
			 ******* Validate Attachment */
			$attachments = '';
			$use_files   = PirateForms::pirate_forms_get_key( 'pirateformsopt_attachment_field' );
			if ( ! empty( $use_files ) && ( $use_files == 'yes' ) ) {
				$attachments              = '';
				$pirate_forms_attach_file = isset( $_FILES['pirate-forms-attachment'] ) ? $_FILES['pirate-forms-attachment'] : '';
				if ( ! empty( $pirate_forms_attach_file ) && ! empty( $pirate_forms_attach_file['name'] ) ) {
					/* Validate file type */
					$pirate_forms_file_types_allowed = 'jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv';
					$pirate_forms_file_types_allowed = trim( $pirate_forms_file_types_allowed, '|' );
					$pirate_forms_file_types_allowed = '(' . $pirate_forms_file_types_allowed . ')';
					$pirate_forms_file_types_allowed = '/\.' . $pirate_forms_file_types_allowed . '$/i';
					if ( ! preg_match( $pirate_forms_file_types_allowed, $pirate_forms_attach_file['name'] ) ) {
						$_SESSION[ $error_key ]['pirate-forms-upload-failed-type'] = __( 'Uploaded file is not allowed for file type', 'pirate-forms' );

						return false;
					}
					/* Validate file size */
					$pirate_forms_file_size_allowed = 1048576; // default size 1 MB
					if ( $pirate_forms_attach_file['size'] > $pirate_forms_file_size_allowed ) {
						$_SESSION[ $error_key ]['pirate-forms-upload-failed-size'] = __( 'Uploaded file is too large', 'pirate-forms' );
					}
					$this->pirate_forms_init_uploads();
					$uploads_dir = $this->pirate_forms_upload_tmp_dir();
					$uploads_dir = $this->pirate_forms_maybe_add_random_dir( $uploads_dir );
					$filename    = $pirate_forms_attach_file['name'];
					$filename    = $this->pirate_forms_canonicalize( $filename );
					$filename    = sanitize_file_name( $filename );
					$filename    = $this->pirate_forms_antiscript_file_name( $filename );
					$filename    = wp_unique_filename( $uploads_dir, $filename );
					$new_file    = trailingslashit( $uploads_dir ) . $filename;
					try {
						if ( false === move_uploaded_file( $pirate_forms_attach_file['tmp_name'], $new_file ) ) {
							throw new Exception( __( 'There was an unknown error uploading the file.', 'pirate-forms' ) );
						}
					} catch ( Exception $ex ) {
						$_SESSION[ $error_key ]['pirate-forms-upload-failed-general'] = $ex->getMessage();
					}
					if ( ! empty( $new_file ) ) {
						$attachments = $new_file;
					}
				}// End if().
			}// End if().

			wp_mail( $site_recipients, 'Contact on ' . htmlspecialchars_decode( get_bloginfo( 'name' ) ), $body, $headers, $attachments );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->delete( $this->pirate_forms_upload_tmp_dir(), true, 'd' );
			// Should a confirm email be sent?
			$confirm_body = stripslashes( trim( PirateForms::pirate_forms_get_key( 'pirateformsopt_confirm_email' ) ) );
			if ( ! empty( $confirm_body ) && ! empty( $pirate_forms_contact_email ) ) {
				// Removing entities
				$confirm_body = htmlspecialchars_decode( $confirm_body );
				$confirm_body = html_entity_decode( $confirm_body );
				$confirm_body = str_replace( '&#39;', "'", $confirm_body );
				$headers      = "From: $site_name <$site_email>\r\nReply-To: $site_name <$site_email>";
				$response     = wp_mail(
					$pirate_forms_contact_email,
					PirateForms::pirate_forms_get_key( 'pirateformsopt_label_submit' ) . ' - ' . $site_name,
					$confirm_body,
					$headers
				);
				if ( ! $response ) {
					error_log( 'Email not sent' );
				}
			}
			/**
			 ***********   Store the entries in the DB */
			if ( PirateForms::pirate_forms_get_key( 'pirateformsopt_store' ) === 'yes' ) {
				$new_post_id = wp_insert_post(
					array(
						'post_type'    => 'pf_contact',
						'post_title'   => date( 'l, M j, Y', time() ) . ' by "' . $pirate_forms_contact_name . '"',
						'post_content' => $body,
						'post_author'  => 1,
						'post_status'  => 'private',
					)
				);
				if ( isset( $pirate_forms_contact_email ) && ! empty( $pirate_forms_contact_email ) ) {
					add_post_meta( $new_post_id, 'Contact email', $pirate_forms_contact_email );
				}
			}
			$pirate_forms_current_theme = wp_get_theme();
			/* If a Thank you page is selected, redirect to that page */
			if ( PirateForms::pirate_forms_get_key( 'pirateformsopt_thank_you_url' ) ) {
				$redirect_id = intval( PirateForms::pirate_forms_get_key( 'pirateformsopt_thank_you_url' ) );
				$redirect    = get_permalink( $redirect_id );
				wp_safe_redirect( $redirect );
			} // End if().

			elseif ( ( 'Zerif Lite' == $pirate_forms_current_theme->name ) || ( 'Zerif Lite' == $pirate_forms_current_theme->parent_theme ) || ( 'Zerif PRO' == $pirate_forms_current_theme->name ) || ( 'Zerif PRO' == $pirate_forms_current_theme->parent_theme ) ) {
				$redirect = $_SERVER['HTTP_REFERER'] . ( strpos( $_SERVER['HTTP_REFERER'], '?' ) === false ? '?' : '&' ) . 'pcf=1#contact';
				wp_safe_redirect( $redirect );
			}
		}// End if().
	}

	/**
	 * Change the content of the widget
	 *
	 * @since    1.0.0
	 */
	public function pirate_forms_widget_text_filter( $content ) {
		if ( ! preg_match( '[pirate_forms]', $content ) ) {
			return $content;
		}
		$content = do_shortcode( $content );

		return $content;
	}

	/**
	 * Display the form
	 *
	 * @since    1.0.0
	 */
	public function pirate_forms_display_form( $atts, $content = null ) {
		$atts   = shortcode_atts( array(
			'from' => '',
		), $atts );

		if ( ! class_exists( 'PhpFormBuilder' ) ) {
			require_once PIRATEFORMS_DIR__ . '/includes/class-phpformbuilder.php';
		}
		$pirate_form = new PhpFormBuilder();

		$pirate_form->add_input(
			'',
			array(
				'type'      => 'hidden',
				'name'      => 'pirate_forms_from_widget',
				'id'        => 'pirate_forms_from_widget',
				'value'     => empty( $atts['from'] ) ? 0 : 1,
				'request_populate'     => false,
			),
			'pirate-forms-from-widget'
		);

		$nonce_append   = isset( $_POST['pirate_forms_from_widget'] ) && intval( $_POST['pirate_forms_from_widget'] ) === 1 ? 'yes' : 'no';

		$error_key          = wp_create_nonce( get_bloginfo( 'admin_email' ) . ( empty( $atts['from'] ) ? 'no' : 'yes' ) );

		$thank_you_message    = '';
		/* thank you message */
		if ( ( ( isset( $_GET['pcf'] ) && $_GET['pcf'] == 1 ) || ( isset( $_POST['pirate-forms-contact-submit'] ) ) )
				&& empty( $_SESSION[ $error_key ] )
				&& wp_verify_nonce( $_POST['wordpress-nonce'], get_bloginfo( 'admin_email' ) . ( empty( $atts['from'] ) ? 'no' : 'yes' ) )
		) {
			$thank_you_message = sanitize_text_field( PirateForms::pirate_forms_get_key( 'pirateformsopt_label_submit' ) );
		}
		$pirate_form->set_element( 'thank_you_message', $thank_you_message );
		/**
		 ******** FormBuilder */
		$pirate_form->set_att( 'id', 'pirate_forms_' . ( get_the_id() ? get_the_id() : 1 ) );
		$pirate_form->set_att( 'class', array( 'pirate_forms' ) );
		if ( 'yes' === PirateForms::pirate_forms_get_key( 'pirateformsopt_nonce' ) ) {
			$pirate_form->set_att( 'add_nonce', get_bloginfo( 'admin_email' ) . ( empty( $atts['from'] ) ? 'no' : 'yes' ) );
		}
		$pirate_forms_options = get_option( 'pirate_forms_settings_array' );
		if ( ! empty( $pirate_forms_options ) ) :
			/* Count the number of requested fields from Name, Email and Subject to add a certain class col-12, col-6 or col-4 */
			$pirate_forms_required_fields = 0;
			if ( ! empty( $pirate_forms_options['pirateformsopt_name_field'] ) && ! empty( $pirate_forms_options['pirateformsopt_label_name'] ) ) :
				$pirateformsopt_name_field = $pirate_forms_options['pirateformsopt_name_field'];
				$pirateformsopt_name_label = $pirate_forms_options['pirateformsopt_label_name'];
				if ( ! empty( $pirateformsopt_name_field ) && ! empty( $pirateformsopt_name_label ) && ( $pirateformsopt_name_field != '' ) ) :
					$pirate_forms_required_fields ++;
				endif;
			endif;
			if ( ! empty( $pirate_forms_options['pirateformsopt_email_field'] ) && ! empty( $pirate_forms_options['pirateformsopt_label_email'] ) ) :
				$pirateformsopt_email_field = $pirate_forms_options['pirateformsopt_email_field'];
				$pirateformsopt_email_label = $pirate_forms_options['pirateformsopt_label_email'];
				if ( ! empty( $pirateformsopt_email_field ) && ! empty( $pirateformsopt_email_label ) && ( $pirateformsopt_email_field != '' ) ) :
					$pirate_forms_required_fields ++;
				endif;
			endif;
			if ( ! empty( $pirate_forms_options['pirateformsopt_subject_field'] ) && ! empty( $pirate_forms_options['pirateformsopt_label_subject'] ) ) :
				$pirateformsopt_subject_field = $pirate_forms_options['pirateformsopt_subject_field'];
				$pirateformsopt_subject_label = $pirate_forms_options['pirateformsopt_label_subject'];
				if ( ! empty( $pirateformsopt_subject_field ) && ! empty( $pirateformsopt_subject_label ) && ( $pirateformsopt_subject_field != '' ) ) :
					$pirate_forms_required_fields ++;
				endif;
			endif;
			$pirate_forms_layout_input = '';
			switch ( $pirate_forms_required_fields ) {
				case 1:
					$pirate_forms_layout_input = 'col-sm-12 col-lg-12';
					break;
				case 2:
					$pirate_forms_layout_input = 'col-sm-6 col-lg-6';
					break;
				case 3:
					$pirate_forms_layout_input = 'col-sm-4 col-lg-4';
					break;
				default:
					$pirate_forms_layout_input = 'col-sm-4 col-lg-4';
			}
			/**
			 ******  Name field */
			if ( ! empty( $pirateformsopt_name_field ) && ! empty( $pirateformsopt_name_label ) ) :
				$required     = $pirateformsopt_name_field === 'req' ? true : false;
				$wrap_classes = array(
					$pirate_forms_layout_input . ' form_field_wrap',
					'contact_name_wrap pirate_forms_three_inputs ',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-name'] ) ) {
					$wrap_classes[] = 'error';
				}
				$pirate_form->add_input(
					'',
					array(
						'placeholder' => stripslashes( sanitize_text_field( $pirateformsopt_name_label ) ),
						'required'    => $required,
						'wrap_class'  => $wrap_classes,
					),
					'pirate-forms-contact-name'
				);
			endif;
			/**
			 ******  Email field */
			if ( ! empty( $pirateformsopt_email_field ) && ! empty( $pirateformsopt_email_label ) ) :
				$required     = $pirateformsopt_email_field === 'req' ? true : false;
				$wrap_classes = array(
					$pirate_forms_layout_input . ' form_field_wrap',
					'contact_email_wrap pirate_forms_three_inputs ',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-email'] ) ) {
					$wrap_classes[] = 'error';
				}
				$pirate_form->add_input(
					'',
					array(
						'placeholder' => stripslashes( sanitize_text_field( $pirateformsopt_email_label ) ),
						'required'    => $required,
						'type'        => 'email',
						'wrap_class'  => $wrap_classes,
					),
					'pirate-forms-contact-email'
				);
			endif;
			/**
			 ******  Subject field */
			if ( ! empty( $pirateformsopt_subject_field ) && ! empty( $pirateformsopt_subject_label ) ) :
				$required     = $pirateformsopt_subject_field === 'req' ? true : false;
				$wrap_classes = array(
					$pirate_forms_layout_input . ' form_field_wrap',
					'contact_subject_wrap pirate_forms_three_inputs ',
				);
				// If this field was submitted with invalid data
				if ( isset( $_SESSION[ $error_key ]['contact-subject'] ) ) {
					$wrap_classes[] = 'error';
				}
				$pirate_form->add_input(
					'',
					array(
						'placeholder' => stripslashes( sanitize_text_field( $pirateformsopt_subject_label ) ),
						'required'    => $required,
						'wrap_class'  => $wrap_classes,
					),
					'pirate-forms-contact-subject'
				);
			endif;
			/**
			 ******  Message field */
			if ( ! empty( $pirate_forms_options['pirateformsopt_message_field'] ) && ! empty( $pirate_forms_options['pirateformsopt_label_message'] ) ) :
				$pirateformsopt_message_field = $pirate_forms_options['pirateformsopt_message_field'];
				$pirateformsopt_message_label = $pirate_forms_options['pirateformsopt_label_message'];
				if ( ! empty( $pirateformsopt_message_field ) && ! empty( $pirateformsopt_message_label ) ) :
					$required     = $pirateformsopt_message_field === 'req' ? true : false;
					$wrap_classes = array( 'col-sm-12 col-lg-12 form_field_wrap', 'contact_message_wrap ' );
					// If this field was submitted with invalid data
					if ( isset( $_SESSION[ $error_key ]['contact-message'] ) ) {
						$wrap_classes[] = 'error';
					}
					$pirate_form->add_input(
						'',
						array(
							'placeholder' => stripslashes( sanitize_text_field( $pirateformsopt_message_label ) ),
							'required'    => $required,
							'wrap_class'  => $wrap_classes,
							'type'        => 'textarea',
						),
						'pirate-forms-contact-message'
					);
				endif;
			endif;
			/**
			 ******* ReCaptcha */
			if ( ! empty( $pirate_forms_options['pirateformsopt_recaptcha_secretkey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_sitekey'] ) && ! empty( $pirate_forms_options['pirateformsopt_recaptcha_field'] ) && ( $pirate_forms_options['pirateformsopt_recaptcha_field'] == 'yes' ) ) :
				$pirateformsopt_recaptcha_sitekey   = $pirate_forms_options['pirateformsopt_recaptcha_sitekey'];
				$pirateformsopt_recaptcha_secretkey = $pirate_forms_options['pirateformsopt_recaptcha_secretkey'];
				$pirate_form->add_input(
					'',
					array(
						'value'      => $pirateformsopt_recaptcha_sitekey,
						'wrap_class' => 'col-xs-12 col-sm-6 col-lg-6 form_field_wrap form_captcha_wrap',
						'type'       => 'captcha',
					),
					'pirate-forms-captcha'
				);
			endif;
			/**
			 ******** Attachment */
			if ( ! empty( $pirate_forms_options['pirateformsopt_attachment_field'] ) && ( $pirate_forms_options['pirateformsopt_attachment_field'] == 'yes' ) ) {
				$pirate_form->add_input(
					'',
					array(
						'wrap_class' => $wrap_classes,
						'type'       => 'file',
					),
					'pirate-forms-attachment'
				);

			}
			/**
			 ******  Submit button */
			if ( ! empty( $pirate_forms_options['pirateformsopt_label_submit_btn'] ) ) :
				$pirateformsopt_label_submit_btn = $pirate_forms_options['pirateformsopt_label_submit_btn'];
				if ( ! empty( $pirateformsopt_label_submit_btn ) ) :
					$wrap_classes = array();
					$pirate_form->add_input(
						'',
						array(
							'value'      => stripslashes( sanitize_text_field( $pirateformsopt_label_submit_btn ) ),
							'wrap_class' => $wrap_classes,
							'type'       => 'submit',
							'wrap_tag'   => '',
							'class'      => 'pirate-forms-submit-button',
						),
						'pirate-forms-contact-submit'
					);
				endif;
			endif;
		endif;
		/* Referring site or page, if any */
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$pirate_form->add_input(
				__( 'Contact Referrer', 'pirate-forms' ),
				array(
					'type'  => 'hidden',
					'value' => $_SERVER['HTTP_REFERER'],
				)
			);
		}
		/* Referring page, if sent via URL query */
		if ( ! empty( $_REQUEST['src'] ) || ! empty( $_REQUEST['ref'] ) ) {
			$pirate_form->add_input(
				__( 'Referring page', 'pirate-forms' ),
				array(
					'type'  => 'hidden',
					'value' => ! empty( $_REQUEST['src'] ) ? $_REQUEST['src'] : $_REQUEST['ref'],
				)
			);
		}
		/* Are there any submission errors? */
		$errors = '';
		if ( ! empty( $_SESSION[ $error_key ] ) ) {
			$pirate_form->set_element( 'errors', $_SESSION[ $error_key ] );
			unset( $_SESSION[ $error_key ] );
		}

		return $pirate_form->build_form( false );

	}

	/**
	 * Save the data
	 *
	 * @since    1.0.0
	 */
	public function pirate_forms_save_callback() {
		if ( isset( $_POST['dataSent'] ) ) :
			$dataSent = $_POST['dataSent'];
			$params   = array();
			if ( ! empty( $dataSent ) ) :
				parse_str( $dataSent, $params );
			endif;
			if ( ! empty( $params ) ) :
				/**
				 ****** Important fix for saving inputs of type checkbox */
				if ( ! isset( $params['pirateformsopt_store'] ) ) {
					$params['pirateformsopt_store'] = '';
				}
				if ( ! isset( $params['pirateformsopt_recaptcha_field'] ) ) {
					$params['pirateformsopt_recaptcha_field'] = '';
				}
				if ( ! isset( $params['pirateformsopt_nonce'] ) ) {
					$params['pirateformsopt_nonce'] = '';
				}
				if ( ! isset( $params['pirateformsopt_attachment_field'] ) ) {
					$params['pirateformsopt_attachment_field'] = '';
				}
				if ( ! isset( $params['pirateformsopt_use_smtp'] ) ) {
					$params['pirateformsopt_use_smtp'] = '';
				}
				if ( ! isset( $params['pirateformsopt_use_smtp_authentication'] ) ) {
					$params['pirateformsopt_use_smtp_authentication'] = '';
				}
				update_option( 'pirate_forms_settings_array', $params );
				$pirate_forms_zerif_lite_mods = get_option( 'theme_mods_zerif-lite' );
				if ( empty( $pirate_forms_zerif_lite_mods ) ) :
					$pirate_forms_zerif_lite_mods = array();
				endif;
				if ( isset( $params['pirateformsopt_label_submit_btn'] ) ) :
					$pirate_forms_zerif_lite_mods['zerif_contactus_button_label'] = $params['pirateformsopt_label_submit_btn'];
				endif;
				if ( isset( $params['pirateformsopt_email'] ) ) :
					$pirate_forms_zerif_lite_mods['zerif_contactus_email'] = $params['pirateformsopt_email'];
				endif;
				if ( isset( $params['pirateformsopt_email_recipients'] ) ) :
					$pirate_forms_zerif_lite_mods['zerif_contactus_email'] = $params['pirateformsopt_email_recipients'];
				endif;
				if ( isset( $params['pirateformsopt_recaptcha_field'] ) && ( $params['pirateformsopt_recaptcha_field'] == 'yes' ) ) :
					$pirate_forms_zerif_lite_mods['zerif_contactus_recaptcha_show'] = 0;
				else :
					$pirate_forms_zerif_lite_mods['zerif_contactus_recaptcha_show'] = 1;
				endif;
				if ( isset( $params['pirateformsopt_recaptcha_sitekey'] ) ) :
					$pirate_forms_zerif_lite_mods['zerif_contactus_sitekey'] = $params['pirateformsopt_recaptcha_sitekey'];
				endif;
				if ( isset( $params['pirateformsopt_recaptcha_secretkey'] ) ) :
					$pirate_forms_zerif_lite_mods['zerif_contactus_secretkey'] = $params['pirateformsopt_recaptcha_secretkey'];
				endif;
				update_option( 'theme_mods_zerif-lite', $pirate_forms_zerif_lite_mods );
			endif;
		endif;
		die();

	}

	/**
	 * Return the table row
	 *
	 * @since    1.0.0
	 */
	public function pirate_forms_table_row( $key, $value ) {
		return '<tr><th>' . $key . '</th><td>' . $value . '</td></tr>';
	}

	/**
	 * Get the blacklisted emails
	 *
	 * @since    1.0.0
	 */
	public function pirate_forms_get_blacklist() {

		$final_blocked_arr = array();

		$blocked = get_option( 'blacklist_keys' );
		$blocked = str_replace( "\r", "\n", $blocked );

		$blocked_arr = explode( "\n", $blocked );
		$blocked_arr = array_map( 'trim', $blocked_arr );

		foreach ( $blocked_arr as $ip_or_email ) {
			$ip_or_email = trim( $ip_or_email );
			if (
					filter_var( $ip_or_email, FILTER_VALIDATE_IP ) ||
					filter_var( $ip_or_email, FILTER_VALIDATE_EMAIL )
			) {
				$final_blocked_arr[] = $ip_or_email;
			}
		}

		return $final_blocked_arr;
	}

	/**
	 * Return the upload dir
	 *
	 * @since    1.0.0
	 */
	function pirate_forms_upload_dir( $type = false ) {
		$uploads = wp_upload_dir();
		$uploads = apply_filters( 'pirate_forms_upload_dir', array(
			'dir' => $uploads['basedir'],
			'url' => $uploads['baseurl'],
		) );
		if ( 'dir' == $type ) {
			return $uploads['dir'];
		}
		if ( 'url' == $type ) {
			return $uploads['url'];
		}

		return $uploads;
	}

	/**
	 * Return the temporary upload dir
	 *
	 * @since    1.0.0
	 */
	function pirate_forms_upload_tmp_dir() {
		return $this->pirate_forms_upload_dir( 'dir' ) . '/pirate_forms_uploads';
	}

	/**
	 * Prepare the uploading process
	 *
	 * @since    1.0.0
	 * @throws   Exception When file could not be opened.
	 */
	function pirate_forms_init_uploads() {
		$dir = $this->pirate_forms_upload_tmp_dir();
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
	 * Add a random directory for uploading
	 *
	 * @since    1.0.0
	 */
	function pirate_forms_maybe_add_random_dir( $dir ) {
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
	 * Functions to Process uploaded files
	 */
	function pirate_forms_canonicalize( $text ) {
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
	function pirate_forms_antiscript_file_name( $filename ) {
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
	 * Alter the phpmailer object
	 *
	 * @param object $phpmailer PHPMailer object.
	 */
	function pirate_forms_phpmailer( $phpmailer ) {
		$pirateformsopt_use_smtp                = PirateForms::pirate_forms_get_key( 'pirateformsopt_use_smtp' );
		$pirateformsopt_smtp_host               = PirateForms::pirate_forms_get_key( 'pirateformsopt_smtp_host' );
		$pirateformsopt_smtp_port               = PirateForms::pirate_forms_get_key( 'pirateformsopt_smtp_port' );
		$pirateformsopt_smtp_username           = PirateForms::pirate_forms_get_key( 'pirateformsopt_smtp_username' );
		$pirateformsopt_smtp_password           = PirateForms::pirate_forms_get_key( 'pirateformsopt_smtp_password' );
		$pirateformsopt_use_smtp_authentication = PirateForms::pirate_forms_get_key( 'pirateformsopt_use_smtp_authentication' );
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
            // @codingStandardsIgnoreEnd
		endif;
	}
}
