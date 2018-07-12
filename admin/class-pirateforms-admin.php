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
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles_and_scripts() {
		$current_screen = get_current_screen();

		if ( ! isset( $current_screen->id ) ) {
			return;
		}

		if ( in_array( $current_screen->id, array( 'edit-pf_contact', 'edit-pf_form', 'pf_form', 'toplevel_page_pirateforms-admin' ) ) ) {
			wp_enqueue_style( 'pirateforms_admin_styles', PIRATEFORMS_URL . 'admin/css/wp-admin.css', array(), $this->version );
			wp_enqueue_script( 'pirateforms_scripts_admin', PIRATEFORMS_URL . 'admin/js/scripts-admin.js', array( 'jquery', 'jquery-ui-tooltip' ), $this->version );
			wp_localize_script(
				'pirateforms_scripts_admin', 'cwp_top_ajaxload', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( PIRATEFORMS_SLUG ),
					'slug'    => PIRATEFORMS_SLUG,
					'i10n'    => array(
						'recaptcha' => __( 'Please specify the Site Key and Secret Key.', 'pirate-forms' ),
					),
				)
			);
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'pf_more_features' ) {
			wp_enqueue_style( 'pirateforms_upsell_styles', PIRATEFORMS_URL . 'admin/css/upsell.css', array(), $this->version );
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
	 * @since 1.0.0
	 */
	public function add_to_admin() {
		add_menu_page(
			PIRATEFORMS_NAME, PIRATEFORMS_NAME, 'manage_options', 'pirateforms-admin', array(
				$this,
				'settings',
			), 'dashicons-feedback'
		);
		add_submenu_page(
			'pirateforms-admin', PIRATEFORMS_NAME, __( 'Settings', 'pirate-forms' ), 'manage_options', 'pirateforms-admin', array(
				$this,
				'settings',
			)
		);
		if ( ! defined( 'PIRATEFORMSPRO_URL' ) ) {
			add_submenu_page(
				'pirateforms-admin', __( 'More Features', 'pirate-forms' ), __( 'More Features', 'pirate-forms' ) . '<span class="dashicons 
		dashicons-star-filled more-features-icon" style="width: 17px;height: 17px; margin-left: 4px; color: #ffca54;font-size: 17px;vertical-align: -3px;"></span>', 'manage_options', 'pf_more_features',
				array(
					$this,
					'render_upsell',
				)
			);
		}
	}

	/**
	 * Render the upsell page.
	 */
	public function render_upsell() {

		include_once PIRATEFORMS_DIR . 'admin/partials/upsell.php';
	}

	/**
	 *  Admin area setting page for the plugin
	 *
	 * @since 1.0.0
	 */
	function settings() {
		global $current_user;
		$pirate_forms_options = PirateForms_Util::get_option();
		$plugin_options       = $this->get_plugin_options();
		include_once PIRATEFORMS_DIR . 'admin/partials/pirateforms-settings-display.php';
	}

	/**
	 *  Get any options that might be configured through the theme.
	 */
	function get_theme_options() {
		$recaptcha_show = '';
		$button_label   = __( 'Send Message', 'pirate-forms' );
		$email          = get_bloginfo( 'admin_email' );

		$theme      = strtolower( wp_get_theme()->__get( 'name' ) );

		// Default values from Zerif Lite.
		if ( strpos( $theme, 'zerif' ) === 0 ) {
			$zerif_contactus_recaptcha_show = get_theme_mod( 'zerif_contactus_recaptcha_show' );
			if ( isset( $zerif_contactus_recaptcha_show ) && ( $zerif_contactus_recaptcha_show == '1' ) ) {
				$recaptcha_show = '';
			} else {
				$recaptcha_show = 'custom';
			}

			$zerif_contactus_button_label = get_theme_mod( 'zerif_contactus_button_label', __( 'Send Message', 'pirate-forms' ) );
			if ( ! empty( $zerif_contactus_button_label ) ) {
				$button_label = $zerif_contactus_button_label;
			}

			$zerif_contactus_email        = get_theme_mod( 'zerif_contactus_email' );
			$zerif_email                  = get_theme_mod( 'zerif_email' );
			if ( ! empty( $zerif_contactus_email ) ) {
				$email = $zerif_contactus_email;
			} elseif ( ! empty( $zerif_email ) ) {
				$email = $zerif_email;
			}
		}

		return array(
			$recaptcha_show,
			$button_label,
			$email,
		);
	}


	/**
	 *
	 * OPTIONS
	 *
	 * @since 1.0.0
	 * name; id; desc; type; default; options
	 */
	function get_plugin_options() {
		list(
			$pirate_forms_contactus_recaptcha_show,
			$pirate_forms_contactus_button_label,
			$pirate_forms_contactus_email
		) = $this->get_theme_options();

		// check if akismet is installed
		$akismet_status = false;
		if ( is_plugin_active( 'akismet/akismet.php' ) ) {
			$akismet_key = get_option( 'wordpress_api_key' );
			if ( ! empty( $akismet_key ) ) {
				$akismet_status = true;
			}
		}

		$akismet_msg = '';
		if ( ! $akismet_status ) {
			$akismet_msg = __( 'To use this option, please ensure Akismet is activated with a valid key.', 'pirate-forms' );
		}

		// the key(s) will be added to the div as class names
		// to enable tooltip popup add 'pirate_tooltip'
		return apply_filters(
			'pirate_forms_admin_controls', array(
				'pirate_options pirate_tooltip' => array(
					'heading'  => __( 'Form processing options', 'pirate-forms' ),
					'controls' => apply_filters(
						'pirate_forms_admin_controls_for_options', array(
							array(
								'id'      => 'pirateformsopt_email',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Contact notification email address', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => '<strong>' . __( "Insert [email] to use the contact form submitter's email.", 'pirate-forms' ) . '</strong><br>' . __( "The notification email will be sent from this address both to the recipients below and the contact form submitter (if this is activated below in email confirmation, in which case the domain for this email address should match your site's domain).", 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => PirateForms_Util::get_from_email(),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_email' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'class'   => 'widefat',
							),
							array(
								'id'      => 'pirateformsopt_email_recipients',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Contact submission recipients', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Email address(es) to receive contact submission notifications. You can separate multiple emails with a comma.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => $pirate_forms_contactus_email,
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_email_recipients' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'class'   => 'widefat',
							),
							array(
								'id'      => 'pirateformsopt_store',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Store submissions in the database', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => sprintf( '%s<br>%s', __( 'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved under "All Entries" on the left (appears after this option is activated).', 'pirate-forms' ), __( 'According to GDPR we recommend you to ask for consent in order to store user data', 'pirate-forms' ) ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => 'no',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_store' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
								'title' => __( 'According to GDPR, we recommend you to ask for consent in order to store user data.', 'pirate-forms' ),
							),
							array(
								'id'      => 'pirateformsopt_store_ip',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Track and store IP of user', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => sprintf( '%s<br>%s<br>%s', __( 'Should the IP of the customer be tracked, stored and displayed in the email content?', 'pirate-forms' ), __( 'According to GDPR we recommend you to ask for consent in order to store user data', 'pirate-forms' ), __( 'If this option is not selected, we may not be able to determine whether this is a spam message.', 'pirate-forms' ) ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => 'no',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_store_ip' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
								'title' => __( 'According to GDPR, we recommend you to ask for consent in order to store user data.', 'pirate-forms' ),
							),
							array(
								'id'      => 'pirateformsopt_nonce',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Add a nonce to the contact form', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Should the form use a WordPress nonce? This helps reduce spam by ensuring that the form submittor is on the site when submitting the form rather than submitting remotely. This could, however, cause problems with sites using a page caching plugin. Turn this off if you are getting complaints about forms not being able to be submitted with an error of "Nonce failed!"', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => 'yes',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_nonce' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
							),
							array(
								'id'    => 'pirateformsopt_confirm_email',
								'type'  => 'textarea',
								'label' => array(
									'value' => __( 'Send email confirmation to form submitter', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Adding text here will send an email to the form submitter. The email uses the "Successful form submission text" field from the "Alert Messages" tab as the subject line. Plain text only here, no HTML.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'value' => stripslashes( PirateForms_Util::get_option( 'pirateformsopt_confirm_email' ) ),
								'wrap'  => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'cols'  => 70,
								'rows'  => 5,
							),
							array(
								'id'      => 'pirateformsopt_copy_email',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Add copy of mail to confirmation email', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Should a copy of the email be appended to the confirmation email? Only the fields that are being displayed will be sent to the sender. Please note that this will only be appended if confirmation email text is provided above.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => '',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_copy_email' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
							),
							array(
								'id'      => 'pirateformsopt_save_attachment',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Save Attachment?', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Enabling this option will save the attachment(s) otherwise attachments can only be found in the email that is received.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => '',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_save_attachment' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
							),
							array(
								'id'      => 'pirateformsopt_thank_you_url',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Success Page', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Select the page that displays after a successful form submission. The page will be displayed without pausing on the email form, so please be sure to configure a relevant thank you message in this page.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_thank_you_url' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => PirateForms_Util::get_thank_you_pages(),
							),
							array(
								'id'       => 'pirateformsopt_akismet',
								'type'     => 'checkbox',
								'label'    => array(
									'value' => __( 'Integrate with Akismet?', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => sprintf( __( 'Checking this option will verify the content of the message with Akismet to check if it\'s spam. If it is determined to be spam, the message will be blocked. %s', 'pirate-forms' ), $akismet_msg ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'value'    => PirateForms_Util::get_option( 'pirateformsopt_akismet' ),
								'wrap'     => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options'  => array(
									'yes' => __( 'Yes', 'pirate-forms' ),
								),
								'disabled' => ! empty( $akismet_msg ),
							),
						)
					),
				),
				'pirate_fields pirate_tooltip'  => array(
					'heading'  => __( 'Fields Settings', 'pirate-forms' ),
					'controls' => apply_filters(
						'pirate_forms_admin_controls_for_fields', array(
							/* Name */
							array(
								'id'      => 'pirateformsopt_name_field',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Name', 'pirate-forms' ),
								),
								'default' => 'req',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_name_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''    => __( 'Do not display', 'pirate-forms' ),
									'yes' => __( 'Display but not required', 'pirate-forms' ),
									'req' => __( 'Required', 'pirate-forms' ),
								),
							),
							/* Email */
							array(
								'id'      => 'pirateformsopt_email_field',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Email address', 'pirate-forms' ),
								),
								'default' => 'req',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_email_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''    => __( 'Do not display', 'pirate-forms' ),
									'yes' => __( 'Display but not required', 'pirate-forms' ),
									'req' => __( 'Required', 'pirate-forms' ),
								),
							),
							/* Subject */
							array(
								'id'      => 'pirateformsopt_subject_field',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Subject', 'pirate-forms' ),
								),
								'default' => 'req',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_subject_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''    => __( 'Do not display', 'pirate-forms' ),
									'yes' => __( 'Display but not required', 'pirate-forms' ),
									'req' => __( 'Required', 'pirate-forms' ),
								),
							),
							/* Message */
							array(
								'id'      => 'pirateformsopt_message_field',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Message', 'pirate-forms' ),
								),
								'default' => 'req',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_message_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''    => __( 'Do not display', 'pirate-forms' ),
									'yes' => __( 'Display but not required', 'pirate-forms' ),
									'req' => __( 'Required', 'pirate-forms' ),
								),
							),
							/* Attachment */
							array(
								'id'      => 'pirateformsopt_attachment_field',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Attachment', 'pirate-forms' ),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_attachment_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''    => __( 'Do not display', 'pirate-forms' ),
									'yes' => __( 'Display but not required', 'pirate-forms' ),
									'req' => __( 'Required', 'pirate-forms' ),
								),
							),
							array(
								'id'      => 'pirateformsopt_checkbox_field',
								'type'    => 'select',
								'label'   => array(
									'value' => __( 'Checkbox', 'pirate-forms' ),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_checkbox_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped pirateformsopt_checkbox',
								),
								'options' => array(
									''    => __( 'Do not display', 'pirate-forms' ),
									'yes' => __( 'Display but not required', 'pirate-forms' ),
									'req' => __( 'Required', 'pirate-forms' ),
								),
							),
							/* Recaptcha */
							array(
								'id'      => 'pirateformsopt_recaptcha_field',
								'type'    => 'radio',
								'label'   => array(
									'value' => __( 'Add a spam trap', 'pirate-forms' ),
								),
								'default' => $pirate_forms_contactus_recaptcha_show,
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_recaptcha_field' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''       => __( 'No', 'pirate-forms' ),
									'custom' => __( 'Custom', 'pirate-forms' ),
									'yes'    => __( 'Google reCAPTCHA', 'pirate-forms' ),
								),
							),
							/* Site key */
							array(
								'id'      => 'pirateformsopt_recaptcha_sitekey',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Site key', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">' . __( 'Create an account here ', 'pirate-forms' ) . '</a>' . __( 'to get the Site key and the Secret key for the reCaptcha.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_recaptcha_sitekey' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped pirateformsopt_recaptcha',
								),
							),
							/* Secret key */
							array(
								'id'      => 'pirateformsopt_recaptcha_secretkey',
								'type'    => 'password',
								'label'   => array(
									'value' => __( 'Secret key', 'pirate-forms' ),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_recaptcha_secretkey' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped pirateformsopt_recaptcha pirate-forms-password-toggle',
								),
							),
						)
					),
				),
				'pirate_labels pirate_tooltip'  => array(
					'heading'  => __( 'Fields Labels', 'pirate-forms' ),
					'controls' => apply_filters(
						'pirate_forms_admin_controls_for_field_labels', array(
							array(
								'id'      => 'pirateformsopt_label_name',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Name', 'pirate-forms' ),
								),
								'default' => __( 'Your Name', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_name' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_email',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Email', 'pirate-forms' ),
								),
								'default' => __( 'Your Email', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_email' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_subject',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Subject', 'pirate-forms' ),
								),
								'default' => __( 'Subject', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_subject' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_message',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Message', 'pirate-forms' ),
								),
								'default' => __( 'Your message', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_message' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_submit_btn',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Submit button', 'pirate-forms' ),
								),
								'default' => $pirate_forms_contactus_button_label,
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_submit_btn' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_checkbox',
								'type'    => 'wysiwyg',
								'label'   => array(
									'value' => __( 'Checkbox', 'pirate-forms' ),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_checkbox' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'wysiwyg' => array(
									'editor_class'  => 'pirate-forms-wysiwyg',
									'quicktags' => false,
									'teeny' => true,
									'media_buttons' => false,
								),
							),
							array(
								'id'      => 'pirateformsopt_email_content',
								'type'    => 'wysiwyg',
								'label'   => array(
									'value' => __( 'Email content', 'pirate-forms' ),
									'html'  => '<br/><br/>' . esc_attr( __( 'You can use the following magic tags:', 'pirate-forms' ) ) . '<br/>' . PirateForms_Util::get_magic_tags(),
								),
								'default' => PirateForms_Util::get_default_email_content( true, null, true ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_email_content' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'wysiwyg' => array(
									'editor_class'  => 'pirate-forms-wysiwyg',
									'editor_height' => 500,
								),
							),
						)
					),
				),
				'pirate_alerts pirate_tooltip'  => array(
					'heading'  => __( 'Alert Messages', 'pirate-forms' ),
					'controls' => apply_filters(
						'pirate_forms_admin_controls_for_alerts', array(
							array(
								'id'      => 'pirateformsopt_label_err_name',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Name required and missing', 'pirate-forms' ),
								),
								'default' => __( 'Enter your name', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_err_name' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_err_email',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'E-mail required and missing', 'pirate-forms' ),
								),
								'default' => __( 'Enter valid email', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_err_email' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_err_subject',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Subject required and missing', 'pirate-forms' ),
								),
								'default' => __( 'Please enter a subject', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_err_subject' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_err_no_content',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Question/comment is missing', 'pirate-forms' ),
								),
								'default' => __( 'Enter your question or comment', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_err_no_content' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_err_no_attachment',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Attachment is missing', 'pirate-forms' ),
								),
								'default' => __( 'Please add an attachment', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_err_no_attachment' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_err_no_checkbox',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Checkbox is not checked', 'pirate-forms' ),
								),
								'default' => __( 'Please select the checkbox', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_err_no_checkbox' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_label_submit',
								'type'    => 'text',
								'label'   => array(
									'value' => __( 'Successful form submission text', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'This text is used on the page if no Success Page is chosen above. This is also used as the confirmation email title, if one is set to send out.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => __( 'Thanks, your email was sent successfully!', 'pirate-forms' ),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_label_submit' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
						)
					),
				),
				'pirate_smtp pirate_tooltip'    => array(
					'heading'  => __( 'SMTP Options', 'pirate-forms' ),
					'controls' => apply_filters(
						'pirate_forms_admin_controls_for_smtp', array(
							array(
								'id'      => 'pirateformsopt_use_smtp',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Use SMTP to send emails?', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'Instead of PHP mail function', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_use_smtp' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
							),
							array(
								'id'    => 'pirateformsopt_smtp_host',
								'type'  => 'text',
								'label' => array(
									'value' => __( 'SMTP Host', 'pirate-forms' ),
								),
								'value' => PirateForms_Util::get_option( 'pirateformsopt_smtp_host' ),
								'wrap'  => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'    => 'pirateformsopt_smtp_port',
								'type'  => 'text',
								'label' => array(
									'value' => __( 'SMTP Port', 'pirate-forms' ),
								),
								'value' => PirateForms_Util::get_option( 'pirateformsopt_smtp_port' ),
								'wrap'  => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'      => 'pirateformsopt_use_smtp_authentication',
								'type'    => 'checkbox',
								'label'   => array(
									'value' => __( 'Use SMTP Authentication?', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'If you check this box, make sure the SMTP Username and SMTP Password are completed.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'default' => 'yes',
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_use_smtp_authentication' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array( 'yes' => __( 'Yes', 'pirate-forms' ) ),
							),
							array(
								'id'      => 'pirateformsopt_use_secure',
								'type'    => 'radio',
								'label'   => array(
									'value' => __( 'Security?', 'pirate-forms' ),
									'html'  => '<span class="dashicons dashicons-editor-help"></span>',
									'desc'  => array(
										'value' => __( 'If you check this box, make sure the SMTP Username and SMTP Password are completed.', 'pirate-forms' ),
										'class' => 'pirate_forms_option_description',
									),
								),
								'value'   => PirateForms_Util::get_option( 'pirateformsopt_use_secure' ),
								'wrap'    => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
								'options' => array(
									''    => __( 'No', 'pirate-forms' ),
									'ssl' => __( 'SSL', 'pirate-forms' ),
									'tls' => __( 'TLS', 'pirate-forms' ),
								),
							),
							array(
								'id'    => 'pirateformsopt_smtp_username',
								'type'  => 'text',
								'label' => array(
									'value' => __( 'SMTP Username', 'pirate-forms' ),
								),
								'value' => PirateForms_Util::get_option( 'pirateformsopt_smtp_username' ),
								'wrap'  => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped',
								),
							),
							array(
								'id'    => 'pirateformsopt_smtp_password',
								'type'  => 'password',
								'label' => array(
									'value' => __( 'SMTP Password', 'pirate-forms' ),
								),
								'value' => PirateForms_Util::get_option( 'pirateformsopt_smtp_password' ),
								'wrap'  => array(
									'type'  => 'div',
									'class' => 'pirate-forms-grouped pirate-forms-password-toggle',
								),
							),
						)
					),
				),
			)
		);
	}

	/**
	 * ******** Save default options if none exist ***********/
	public function settings_init() {
		if ( ! PirateForms_Util::get_option() ) {
			$new_opt = array();
			foreach ( $this->get_plugin_options() as $tab => $array ) {
				foreach ( $array['controls'] as $controls ) {
					$new_opt[ $controls['id'] ] = isset( $controls['default'] ) ? $controls['default'] : '';
				}
			}
			PirateForms_Util::set_option( $new_opt );
		}
	}

	/**
	 * Save the data
	 *
	 * @since    1.0.0
	 */
	public function save_callback() {
		check_ajax_referer( PIRATEFORMS_SLUG, 'security' );

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
				if ( isset( $params['pirateformsopt_recaptcha_field'] ) && ( $params['pirateformsopt_recaptcha_field'] == 'custom' ) ) :
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
	 * Add the columns for contacts listing
	 *
	 * @param array $columns array of columns.
	 *
	 * @since    1.0.0
	 */
	public function manage_contact_posts_columns( $columns ) {
		$tmp     = $columns;
		$columns = array();
		/**
		 * Remove redundant columns.
		 */
		$allowed_keys = array( 'cb', 'title', 'pf_mailstatus', 'pf_form', 'date' );

		foreach ( $tmp as $key => $val ) {
			if ( 'date' === $key ) {
				// ensure our columns are added before the date.
				$columns['pf_mailstatus'] = __( 'Mail Status', 'pirate-forms' );
			}
			if ( in_array( $key, $allowed_keys ) ) {
				$columns[ $key ] = $val;
			}
		}

		return $columns;
	}

	/**
	 * Show the additional columns for contacts listing
	 *
	 * @param string  $column the column name.
	 * @param numeric $id the post id.
	 *
	 * @since    1.0.0
	 */
	public function manage_contact_posts_custom_column( $column, $id ) {
		switch ( $column ) {
			case 'pf_mailstatus':
				$response = get_post_meta( $id, PIRATEFORMS_SLUG . 'mail-status', true );
				$failed   = $response == 'false';
				echo empty( $response ) ? __( 'Status not captured', 'pirate-forms' ) : ( $failed ? __( 'Mail sending failed!', 'pirate-forms' ) : __( 'Mail sent successfully!', 'pirate-forms' ) );

				if ( $failed ) {
					$reason = get_post_meta( $id, PIRATEFORMS_SLUG . 'mail-status-reason', true );
					if ( ! empty( $reason ) ) {
						echo ' (' . $reason . ')';
					}
				}
				break;
		}

		do_action( 'pirate_forms_listing_display', $column, $id );

	}

	/**
	 * Test sending the email.
	 */
	public function test_email() {
		check_ajax_referer( PIRATEFORMS_SLUG, 'security' );
		add_filter( 'pirateformpro_get_form_attributes', array( $this, 'test_configuration' ), 999, 2 );
		add_action( 'pirate_forms_after_processing', array( $this, 'test_result' ), 10, 1 );
		add_filter( 'pirate_forms_validate_request', array( $this, 'test_alter_session' ), 10, 3 );
		$_POST = array(
			'honeypot'                     => '',
			'pirate_forms_form_id'         => isset( $_POST['pirate_forms_form_id'] ) ? $_POST['pirate_forms_form_id'] : '',
			'pirate-forms-contact-name'    => 'Test Name',
			'pirate-forms-contact-email'   => get_bloginfo( 'admin_email' ),
			'pirate-forms-contact-subject' => 'Test Email',
			'pirate-forms-contact-message' => 'This is a test.',
		);
		do_action( 'pirate_forms_send_email', true );
	}

	/**
	 * Change the options for testing.
	 */
	public function test_configuration( $options, $id ) {
		// disable captcha
		$options['pirateformsopt_recaptcha_field'] = 'no';
		// disable attachments
		$options['pirateformsopt_attachment_field'] = 'no';

		return $options;
	}

	/**
	 * Show admin notices.
	 */
	public function admin_notices() {
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return;
		}
		if ( ! isset( $screen->base ) ) {
			return;
		}

		if ( ! in_array( $screen->id, array( 'toplevel_page_pirateforms-admin' ) ) ) {
			return;
		}

		$options = PirateForms_Util::get_option();

		// check if store submissions is enabled without having a checkbox.
		if ( 'yes' !== $options['pirateformsopt_store'] ) {
			return;
		}

		if ( empty( $options['pirateformsopt_checkbox_field'] ) && false === ( $x = get_transient( 'pirate_forms_gdpr_notice0' ) ) ) {
			echo sprintf( '<div data-dismissible="0" class="notice notice-warning pirateforms-notice pirateforms-notice-checkbox pirateforms-notice-gdpr is-dismissible"><p><strong>%s</strong></p></div>', __( 'According to GDPR we recommend you to ask for consent in order to store user data', 'pirate-forms' ) );
		}
	}

	/**
	 * Generic ajax handler.
	 */
	public function ajax() {
		check_ajax_referer( PIRATEFORMS_SLUG, 'security' );

		switch ( $_POST['_action'] ) {
			case 'dismiss-notice':
				set_transient( 'pirate_forms_gdpr_notice' . $_POST['id'], 'yes' );
				break;
		}
		wp_die();
	}

	/**
	 * Hook into the sent result.
	 */
	public function test_result( $response ) {
		wp_send_json_success( array( 'message' => $response ? __( 'Sent email successfully!', 'pirate-forms' ) : __( 'Sent email failed!', 'pirate-forms' ) ) );
	}

	/**
	 * Clear the session of any errors.
	 */
	public function test_alter_session( $body, $error_key, $pirate_forms_options ) {
		$_SESSION[ $error_key ] = '';

		return $body;
	}

	/**
	 * Register the private data exporter.
	 */
	public function register_private_data_exporter( $exporters ) {
		$exporters[ PIRATEFORMS_SLUG ] = array(
			'exporter_friendly_name' => PIRATEFORMS_NAME,
			'callback' => array( $this, 'private_data_exporter' ),
		);
		return $exporters;
	}

	/**
	 * Export the private data.
	 */
	public function private_data_exporter( $email_address, $page = 1 ) {
		$export_items = array();
		$query  = new WP_Query(
			array(
				'post_type'     => 'pf_contact',
				'numberposts'   => 300,
				'post_status'   => array( 'publish', 'private' ),
				'meta_query'    => array(
					array(
						'key'   => 'Contact email',
						'value' => $email_address,
					),
				),
			)
		);

		$pirate_forms_options = PirateForms_Util::get_option();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$data       = array();
				$data[]     = array(
					'name'  => __( 'Email content', 'pirate-forms' ),
					'value' => nl2br( strip_tags( $query->post->post_content ) ),
				);

				$data       = apply_filters( 'pirate_forms_private_data_exporter', $data, $query->post->ID, $email_address, $page, $pirate_forms_options );

				$export_items[] = array(
					'group_id' => 'pf_contact',
					'group_label' => PIRATEFORMS_NAME,
					'item_id' => "pf_contact-{$query->post->ID}",
					'data' => $data,
				);
			}
		}

		return array(
			'data'  => $export_items,
			'done'  => true,
		);
	}

	/**
	 * Register the private data eraser.
	 */
	public function register_private_data_eraser( $erasers ) {
		$erasers[ PIRATEFORMS_SLUG ] = array(
			'eraser_friendly_name' => PIRATEFORMS_NAME,
			'callback' => array( $this, 'private_data_eraser' ),
		);
		return $erasers;
	}

	/**
	 * Erase the private data.
	 */
	public function private_data_eraser( $email_address, $page = 1 ) {
		$query  = new WP_Query(
			array(
				'post_type'     => 'pf_contact',
				'numberposts'   => 300,
				'post_status'   => array( 'publish', 'private' ),
				'fields'        => 'ids',
				'meta_query'    => array(
					array(
						'key'   => 'Contact email',
						'value' => $email_address,
					),
				),
			)
		);

		$retained   = array();
		$removed    = 0;
		$pirate_forms_options = PirateForms_Util::get_option();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				list( $retained, $removed ) = apply_filters( 'pirate_forms_private_data_eraser', array( $retained, $removed ), $query->post, $email_address, $page, $pirate_forms_options );

				// delete the post last so that all dependent operations are complete.
				if ( false !== ( $post_id = wp_delete_post( $query->post, true ) ) ) {
					$removed++;
				} else {
					$retained[] = $query->post;
				}
			}
		}

		return array(
			'items_removed' => $removed,
			'items_retained' => ! empty( $retained ) ? count( $retained ) : false,
			'messages'  => ! empty( $retained ) ? array(sprintf( __( 'Unable to delete %d entries', 'pirate-forms' ), count( $retained ) )) : array(),
			'done'  => true,
		);
	}
}
