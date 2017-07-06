<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PirateForms
 * @subpackage PirateForms/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PirateForms
 * @subpackage PirateForms/admin
 * @author     Your Name <email@example.com>
 */
class PirateForms_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles_and_scripts() {
		global $pagenow;
		if ( ! empty( $pagenow ) && ( $pagenow == 'options-general.php' || $pagenow == 'admin.php' )
			 && isset( $_GET['page'] ) && $_GET['page'] == 'pirateforms-admin'
		) {
			wp_enqueue_style( 'pirateforms_admin_styles', PIRATEFORMS_URL . 'admin/css/wp-admin.css', array(), $this->version );
			wp_enqueue_script( 'pirateforms_scripts_admin', PIRATEFORMS_URL . 'admin/js/scripts-admin.js', array( 'jquery' ), $this->version );
			wp_localize_script( 'pirateforms_scripts_admin', 'cwp_top_ajaxload', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}


	/**
	 * Loads the sidebar
	 *
	 * @since    1.0.0
	 */
	public function load_sidebar() {
		ob_start();
		do_action( 'pirate_forms_load_sidebar_theme' );
		do_action( 'pirate_forms_load_sidebar_subscribe' );
		echo ob_get_clean();
	}

	/**
	 * Loads the theme-specific sidebar box
	 *
	 * @since    1.0.0
	 */
	public function load_sidebar_theme() {
		include_once PIRATEFORMS_DIR . 'admin/partials/pirateforms-settings-sidebar-theme.php';
	}

	/**
	 * Loads the sidebar subscription box
	 *
	 * @since    1.0.0
	 */
	public function load_sidebar_subscribe() {
		include_once PIRATEFORMS_DIR . 'admin/partials/pirateforms-settings-sidebar-subscribe.php';
	}

	/**
	 * Add the settings link in the plugin page
	 *
	 * @since    1.0.0
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=pirateforms-admin">' . __( 'Settings', 'pirate-forms' ) . '</a>';
		if ( function_exists( 'array_unshift' ) ) :
			array_unshift( $links, $settings_link );
		else :
			array_push( $links, $settings_link );
		endif;

		return $links;
	}

	/**
	 *
	 *  Add page to the dashbord menu
	 *
	 *  @since 1.0.0
	 */
	public function add_to_admin() {
		add_menu_page( PIRATEFORMS_NAME, PIRATEFORMS_NAME, 'manage_options', 'pirateforms-admin', array( $this, 'pirate_forms_admin' ), 'dashicons-feedback' );
		add_submenu_page( 'pirateforms-admin', PIRATEFORMS_NAME, __( 'Settings', 'pirate-forms' ), 'manage_options', 'pirateforms-admin', array( $this, 'pirate_forms_admin' ) );
	}


	/**
	 *  Admin area setting page for the plugin
	 *
	 * @since 1.0.0
	 */
	function pirate_forms_admin() {
		global $current_user;
		$pirate_forms_options = PirateForms_Util::get_option();
		$plugin_options       = $this->pirate_forms_plugin_options();
		include_once PIRATEFORMS_DIR . 'admin/partials/pirateforms-settings-display.php';
	}
	/**
	 * ******** Save default options if none exist ***********/
	public function settings_init() {
		if ( ! PirateForms_Util::get_option() ) {
			$new_opt = array();
			foreach ( $this->pirate_forms_plugin_options() as $temparr ) {
				foreach ( $temparr as $key => $opt ) {
					$new_opt[ $key ] = $opt[3];
				}
			}
			PirateForms_Util::set_option( $new_opt );
		}
	}

	/**
	 * Get the list of all pages
	 *
	 * @since    1.0.0
	 */
	function pirate_forms_get_pages_array( $type = 'page' ) {
		$content = array(
			'' => __( 'None', 'pirate-forms' ),
		);
		$items   = get_posts( array(
			'post_type'   => $type,
			'numberposts' => - 1,
		) );
		if ( ! empty( $items ) ) :
			foreach ( $items as $item ) :
				$content[ $item->ID ] = $item->post_title;
			endforeach;
		endif;

		return $content;

	}

	/**
	 *
	 * OPTIONS
	 *
	 * @since 1.0.0
	 * name; id; desc; type; default; options
	 */
	function pirate_forms_plugin_options() {
		/**
		 **********  Default values from Zerif Lite */
		$zerif_contactus_sitekey = get_theme_mod( 'zerif_contactus_sitekey' );
		if ( ! empty( $zerif_contactus_sitekey ) ) :
			$pirate_forms_contactus_sitekey = $zerif_contactus_sitekey;
		else :
			$pirate_forms_contactus_sitekey = '';
		endif;
		$zerif_contactus_secretkey = get_theme_mod( 'zerif_contactus_secretkey' );
		if ( ! empty( $zerif_contactus_secretkey ) ) :
			$pirate_forms_contactus_secretkey = $zerif_contactus_secretkey;
		else :
			$pirate_forms_contactus_secretkey = '';
		endif;
		$zerif_contactus_recaptcha_show = get_theme_mod( 'zerif_contactus_recaptcha_show' );
		if ( isset( $zerif_contactus_recaptcha_show ) && ( $zerif_contactus_recaptcha_show == '1' ) ) :
			$pirate_forms_contactus_recaptcha_show = '';
		else :
			$pirate_forms_contactus_recaptcha_show = 'yes';
		endif;
		$zerif_contactus_button_label = get_theme_mod( 'zerif_contactus_button_label', __( 'Send Message', 'pirate-forms' ) );
		if ( ! empty( $zerif_contactus_button_label ) ) :
			$pirate_forms_contactus_button_label = $zerif_contactus_button_label;
		else :
			$pirate_forms_contactus_button_label = __( 'Send Message', 'pirate-forms' );
		endif;
		$zerif_contactus_email        = get_theme_mod( 'zerif_contactus_email' );
		$zerif_email                  = get_theme_mod( 'zerif_email' );
		$pirate_forms_contactus_email = '';
		if ( ! empty( $zerif_contactus_email ) ) :
			$pirate_forms_contactus_email = $zerif_contactus_email;
		elseif ( ! empty( $zerif_email ) ) :
			$pirate_forms_contactus_email = $zerif_email;
		else :
			$pirate_forms_contactus_email = get_bloginfo( 'admin_email' );
		endif;

		return array(
			'fourth_tab' => array(
				'header_options'                  => array(
					__( 'Form processing options', 'pirate-forms' ),
					'',
					'title',
					'',
				),
				'pirateformsopt_email'            => array(
					__( 'Contact notification sender email', 'pirate-forms' ),
					'<strong>' . __( "Insert [email] to use the contact form submitter's email.", 'pirate-forms' ) . '</strong><br>' . __( "Email to use for the sender of the contact form emails both to the recipients below and the contact form submitter (if this is activated below). The domain for this email address should match your site's domain.", 'pirate-forms' ),
					'text',
					PirateForms_Util::get_from_email(),
				),
				'pirateformsopt_email_recipients' => array(
					__( 'Contact submission recipients', 'pirate-forms' ),
					__( 'Email address(es) to receive contact submission notifications. You can separate multiple emails with a comma.', 'pirate-forms' ),
					'text',
					PirateForms_Util::get_option( 'pirateformsopt_email' ) ? PirateForms_Util::get_option( 'pirateformsopt_email' ) : $pirate_forms_contactus_email,
				),
				'pirateformsopt_store'            => array(
					__( 'Store submissions in the database', 'pirate-forms' ),
					__( 'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved in Contacts on the left (appears after this option is activated).', 'pirate-forms' ),
					'checkbox',
					'yes',
				),
				'pirateformsopt_nonce'            => array(
					__( 'Add a nonce to the contact form:', 'pirate-forms' ),
					__( 'Should the form use a WordPress nonce? This helps reduce spam by ensuring that the form submittor is on the site when submitting the form rather than submitting remotely. This could, however, cause problems with sites using a page caching plugin. Turn this off if you are getting complaints about forms not being able to be submitted with an error of "Nonce failed!"', 'pirate-forms' ),
					'checkbox',
					'yes',
				),
				'pirateformsopt_confirm_email'    => array(
					__( 'Send email confirmation to form submitter', 'pirate-forms' ),
					__( 'Adding text here will send an email to the form submitter. The email uses the "Successful form submission text" field from the "Alert Messages" tab as the subject line. Plain text only here, no HTML.', 'pirate-forms' ),
					'textarea',
					'',
				),
				'pirateformsopt_thank_you_url'    => array(
					__( '"Thank You" URL', 'pirate-forms' ),
					__( 'Select the post-submit page for all forms submitted', 'pirate-forms' ),
					'select',
					'',
					$this->pirate_forms_get_pages_array(),
				),
			),
			'first_tab'  => array(
				'header_fields'                      => array(
					__( 'Fields Settings', 'pirate-forms' ),
					'',
					'title',
					'',
				),
				/* Name */
				'pirateformsopt_name_field'          => array(
					__( 'Name', 'pirate-forms' ),
					__( 'Do you want the name field to be displayed?', 'pirate-forms' ),
					'select',
					'req',
					array(
						''    => __( 'None', 'pirate-forms' ),
						'yes' => __( 'Yes but not required', 'pirate-forms' ),
						'req' => __( 'Required', 'pirate-forms' ),
					),
				),
				/* Email */
				'pirateformsopt_email_field'         => array(
					__( 'Email address', 'pirate-forms' ),
					__( 'Do you want the email address field be displayed?', 'pirate-forms' ),
					'select',
					'req',
					array(
						''    => __( 'None', 'pirate-forms' ),
						'yes' => __( 'Yes but not required', 'pirate-forms' ),
						'req' => __( 'Required', 'pirate-forms' ),
					),
				),
				/* Subject */
				'pirateformsopt_subject_field'       => array(
					__( 'Subject', 'pirate-forms' ),
					__( 'Do you want the subject field be displayed?', 'pirate-forms' ),
					'select',
					'req',
					array(
						''    => __( 'None', 'pirate-forms' ),
						'yes' => __( 'Yes but not required', 'pirate-forms' ),
						'req' => __( 'Required', 'pirate-forms' ),
					),
				),
				/* Message */
				'pirateformsopt_message_field'       => array(
					__( 'Message', 'pirate-forms' ),
					'',
					'select',
					'req',
					array(
						''    => __( 'None', 'pirate-forms' ),
						'yes' => __( 'Yes but not required', 'pirate-forms' ),
						'req' => __( 'Required', 'pirate-forms' ),
					),
				),
				/* Recaptcha */
				'pirateformsopt_recaptcha_field'     => array(
					__( 'Add a reCAPTCHA', 'pirate-forms' ),
					'',
					'checkbox',
					$pirate_forms_contactus_recaptcha_show,
				),
				/* Site key */
				'pirateformsopt_recaptcha_sitekey'   => array(
					__( 'Site key', 'pirate-forms' ),
					'<a href="https://www.google.com/recaptcha/admin#list" target="_blank">' . __( 'Create an account here ', 'pirate-forms' ) . '</a>' . __( 'to get the Site key and the Secret key for the reCaptcha.', 'pirate-forms' ),
					'text',
					$pirate_forms_contactus_sitekey,
				),
				/* Secret key */
				'pirateformsopt_recaptcha_secretkey' => array(
					__( 'Secret key', 'pirate-forms' ),
					'',
					'text',
					$pirate_forms_contactus_secretkey,
				),
				/* Attachment */
				'pirateformsopt_attachment_field'    => array(
					__( 'Add an attachment field', 'pirate-forms' ),
					'',
					'checkbox',
					'',
				),
			),
			'second_tab' => array(
				'header_labels'                   => array(
					__( 'Fields Labels', 'pirate-forms' ),
					'',
					'title',
					'',
				),
				'pirateformsopt_label_name'       => array(
					__( 'Name', 'pirate-forms' ),
					'',
					'text',
					__( 'Your Name', 'pirate-forms' ),
				),
				'pirateformsopt_label_email'      => array(
					__( 'Email', 'pirate-forms' ),
					'',
					'text',
					__( 'Your Email', 'pirate-forms' ),
				),
				'pirateformsopt_label_subject'    => array(
					__( 'Subject', 'pirate-forms' ),
					'',
					'text',
					__( 'Subject', 'pirate-forms' ),
				),
				'pirateformsopt_label_message'    => array(
					__( 'Message', 'pirate-forms' ),
					'',
					'text',
					__( 'Your message', 'pirate-forms' ),
				),
				'pirateformsopt_label_submit_btn' => array(
					__( 'Submit button', 'pirate-forms' ),
					'',
					'text',
					$pirate_forms_contactus_button_label,
				),
			),
			'third_tab'  => array(
				'header_messages'                     => array(
					__( 'Alert Messages', 'pirate-forms' ),
					'',
					'title',
					'',
				),
				'pirateformsopt_label_err_name'       => array(
					__( 'Name required and missing', 'pirate-forms' ),
					'',
					'text',
					__( 'Enter your name', 'pirate-forms' ),
				),
				'pirateformsopt_label_err_email'      => array(
					__( 'E-mail required and missing', 'pirate-forms' ),
					'',
					'text',
					__( 'Enter a valid email', 'pirate-forms' ),
				),
				'pirateformsopt_label_err_subject'    => array(
					__( 'Subject required and missing', 'pirate-forms' ),
					'',
					'text',
					__( 'Please enter a subject', 'pirate-forms' ),
				),
				'pirateformsopt_label_err_no_content' => array(
					__( 'Question/comment is missing', 'pirate-forms' ),
					'',
					'text',
					__( 'Enter your question or comment', 'pirate-forms' ),
				),
				'pirateformsopt_label_submit'         => array(
					__( 'Successful form submission text', 'pirate-forms' ),
					__( 'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.', 'pirate-forms' ),
					'text',
					__( 'Thanks, your email was sent successfully!', 'pirate-forms' ),
				),
			),
			'fifth_tab'  => array(
				'header_smtp'                            => array(
					__( 'SMTP Options', 'pirate-forms' ),
					'',
					'title',
					'',
				),
				'pirateformsopt_use_smtp'                => array(
					__( 'Use SMTP to send emails?', 'pirate-forms' ),
					__( 'Instead of PHP mail function', 'pirate-forms' ),
					'checkbox',
					'',
				),
				'pirateformsopt_smtp_host'               => array(
					__( 'SMTP Host', 'pirate-forms' ),
					'',
					'text',
					'',
				),
				'pirateformsopt_smtp_port'               => array(
					__( 'SMTP Port', 'pirate-forms' ),
					'',
					'text',
					'',
				),
				'pirateformsopt_use_smtp_authentication' => array(
					__( 'Use SMTP Authentication?', 'pirate-forms' ),
					__( 'If you check this box, make sure the SMTP Username and SMTP Password are completed.', 'pirate-forms' ),
					'checkbox',
					'yes',
				),
				'pirateformsopt_use_secure' => array(
					__( 'Security?', 'pirate-forms' ),
					__( 'If you check this box, make sure the SMTP Username and SMTP Password are completed.', 'pirate-forms' ),
					'radio',
					array(
						''      => __( 'No', 'pirate-forms' ),
						'ssl'   => __( 'SSL', 'pirate-forms' ),
						'tls'   => __( 'TLS', 'pirate-forms' ),
					),
				),
				'pirateformsopt_smtp_username'           => array(
					__( 'SMTP Username', 'pirate-forms' ),
					'',
					'text',
					'',
				),
				'pirateformsopt_smtp_password'           => array(
					__( 'SMTP Password', 'pirate-forms' ),
					'',
					'text',
					'',
				),
			),
		);
	}

	/**
	 * Save the data
	 *
	 * @since    1.0.0
	 */
	public function save_callback() {
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
				PirateForms_Util::set_option( $params );
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
}
