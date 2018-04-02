<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PirateForms
 * @subpackage PirateForms/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    PirateForms
 * @subpackage PirateForms/includes
 * @author     Your Name <email@example.com>
 */
class PirateForms {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PirateForms_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'pirateforms';
		$this->version = '2.4.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - PirateForms_Loader. Orchestrates the hooks of the plugin.
	 * - PirateForms_I18n. Defines internationalization functionality.
	 * - PirateForms_Admin. Defines all hooks for the admin area.
	 * - PirateForms_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once PIRATEFORMS_DIR . 'includes/class-pirateforms-widget.php';

		$this->loader = new PirateForms_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the PirateForms_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new PirateForms_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to common functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_common_hooks() {
		$this->loader->add_action( 'init', $this, 'register_content_type', 10 );
		$this->loader->add_filter( 'pirate_forms_version_supports', $this, 'version_supports' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new PirateForms_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles_and_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_to_admin', 9 );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'settings_init' );
		$this->loader->add_filter( 'plugin_action_links_' . PIRATEFORMS_BASENAME, $plugin_admin, 'add_settings_link' );
		$this->loader->add_action( 'wp_ajax_pirate_forms_save', $plugin_admin, 'save_callback' );
		$this->loader->add_action( 'wp_ajax_pirate_forms_test', $plugin_admin, 'test_email' );
		$this->loader->add_action( 'pirate_forms_load_sidebar', $plugin_admin, 'load_sidebar' );
		$this->loader->add_action( 'pirate_forms_load_sidebar_theme', $plugin_admin, 'load_sidebar_theme' );
		$this->loader->add_action( 'pirate_forms_load_sidebar_subscribe', $plugin_admin, 'load_sidebar_subscribe' );

		// this informs the pro whether the lite will implement the custom spam checkbox or not.
		add_filter( 'pirate_forms_support_custom_spam', '__return_true' );

		$this->loader->add_filter( 'manage_pf_contact_posts_columns', $plugin_admin, 'manage_contact_posts_columns', PHP_INT_MAX );
		$this->loader->add_filter( 'manage_pf_contact_posts_custom_column', $plugin_admin, 'manage_contact_posts_custom_column', 10, 2 );

	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new PirateForms_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles_and_scripts' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect' );

		// ONLY FOR UNIT TESTING: we cannot fire template_redirect without errors, that is why we are creating a manual hook for this
		$this->loader->add_action( 'pirate_unittesting_template_redirect', $plugin_public, 'template_redirect' );
		$this->loader->add_action( 'pirate_forms_render_thankyou', $plugin_public, 'render_thankyou' );
		$this->loader->add_action( 'pirate_forms_render_errors', $plugin_public, 'render_errors' );
		$this->loader->add_action( 'pirate_forms_render_fields', $plugin_public, 'render_fields' );
		$this->loader->add_action( 'pirate_forms_send_email', $plugin_public, 'send_email' );

		$this->loader->add_filter( 'widget_text', $plugin_public, 'widget_text_filter', 9 );
		$this->loader->add_filter( 'pirate_forms_public_controls', $plugin_public, 'compatibility_class', 9 );

		/**
		 * SDK tweaks.
		 */

		$this->loader->add_filter( 'pirate_forms_friendly_name', $plugin_public, 'change_name' );

		add_shortcode( 'pirate_forms', array( $plugin_public, 'display_form' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->add_action( 'widgets_init', 'pirate_forms_contact_widget', 'register_widget' );

		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PirateForms_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register the contacts CPT
	 *
	 * @since     1.0.0
	 */
	public function register_content_type() {
		$labels = array(
			'name'               => _x( 'Entries', 'post type general name', 'pirate-forms' ),
			'singular_name'      => _x( 'Entry', 'post type singular name', 'pirate-forms' ),
			'menu_name'          => _x( 'Entries', 'admin menu', 'pirate-forms' ),
			'name_admin_bar'     => _x( 'Entry', 'add new on admin bar', 'pirate-forms' ),
			'edit_item'          => __( 'Edit Entry', 'pirate-forms' ),
			'view_item'          => __( 'View Entry', 'pirate-forms' ),
			'all_items'          => __( 'All Entries', 'pirate-forms' ),
			'search_items'       => __( 'Search Entries', 'pirate-forms' ),
			'parent_item_colon'  => __( 'Parent Entries:', 'pirate-forms' ),
			'not_found'          => __( 'No entries found.', 'pirate-forms' ),
			'not_found_in_trash' => __( 'No entries found in Trash.', 'pirate-forms' ),
		);
		$args   = array(
			'labels'             => $labels,
			'description'        => __( 'Entries from Pirate Forms', 'pirate-forms' ),
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'pirateforms-admin',
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'custom-fields' ),
			'capabilities'       => array(
				'create_posts'   => false,
			),
			'map_meta_cap'       => true,

		);
		register_post_type( 'pf_contact', $args );
	}

	/**
	 * Return the new features that have been introduced so that the pro plugin can take an action on the basis of that.
	 */
	public function version_supports( $null = null ) {
		return array( 'wysiwyg' );
	}
}
