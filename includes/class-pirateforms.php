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
		$this->version = '1.2.5';

		$this->load_dependencies();
		$this->set_locale();
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

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pirateforms-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pirateforms-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-pirateforms-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-pirateforms-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widget.php';

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
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new PirateForms_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles_and_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'pirate_forms_add_to_admin' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'pirate_forms_settings_init' );
		$this->loader->add_filter( 'plugin_action_links_' . PIRATEFORMS_BASENAME__, $plugin_admin, 'pirate_forms_add_settings_link' );
		$this->loader->add_action( 'init', $this, 'pirate_forms_register_content_type' );
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
		$this->loader->add_action( 'template_redirect', $plugin_public, 'pirate_forms_process_contact' );
		$this->loader->add_action( 'wp_ajax_pirate_forms_save', $plugin_public, 'pirate_forms_save_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_pirate_forms_save', $plugin_public, 'pirate_forms_save_callback' );
		$this->loader->add_filter( 'widget_text', $plugin_public, 'pirate_forms_widget_text_filter', 9 );
		$this->loader->add_action( 'init', $this, 'pirate_forms_register_content_type' );

		add_shortcode( 'pirate_forms', array( $plugin_public, 'pirate_forms_display_form' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		add_action( 'widgets_init', create_function( '', 'return register_widget("pirate_forms_contact_widget");' ) );

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
	 * Returns if the domain is localhost
	 *
	 * @since     1.0.0
	 */
	public static function pirate_forms_is_localhost() {
		$server_name = strtolower( $_SERVER['SERVER_NAME'] );
		return in_array( $server_name, array( 'localhost', '127.0.0.1' ) );
	}

	/**
	 * Gets the from email
	 *
	 * @since     1.0.0
	 */
	public static function pirate_forms_from_email() {
		$admin_email = get_option( 'admin_email' );
		$sitename    = strtolower( $_SERVER['SERVER_NAME'] );
		if ( self::pirate_forms_is_localhost() ) {
			return $admin_email;
		}
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
			return $admin_email;
		}

		return 'wordpress@' . $sitename;
	}

	/**
	 * Get the settings key
	 *
	 * @since     1.0.0
	 */
	public static function pirate_forms_get_key( $id ) {
		$pirate_forms_options = get_option( 'pirate_forms_settings_array' );
		return isset( $pirate_forms_options[ $id ] ) ? $pirate_forms_options[ $id ] : '';
	}

	/**
	 * Register the contacts CPT
	 *
	 * @since     1.0.0
	 */
	public function pirate_forms_register_content_type() {
		if ( self::pirate_forms_get_key( 'pirateformsopt_store' ) === 'yes' ) {
			$labels = array(
				'name'               => _x( 'Contacts', 'post type general name', 'pirate-forms' ),
				'singular_name'      => _x( 'Contact', 'post type singular name', 'pirate-forms' ),
				'menu_name'          => _x( 'Contacts', 'admin menu', 'pirate-forms' ),
				'name_admin_bar'     => _x( 'Contact', 'add new on admin bar', 'pirate-forms' ),
				'add_new'            => _x( 'Add New', 'contact', 'pirate-forms' ),
				'add_new_item'       => __( 'Add New Contact', 'pirate-forms' ),
				'new_item'           => __( 'New Contact', 'pirate-forms' ),
				'edit_item'          => __( 'Edit Contact', 'pirate-forms' ),
				'view_item'          => __( 'View Contact', 'pirate-forms' ),
				'all_items'          => __( 'All Contacts', 'pirate-forms' ),
				'search_items'       => __( 'Search Contacts', 'pirate-forms' ),
				'parent_item_colon'  => __( 'Parent Contacts:', 'pirate-forms' ),
				'not_found'          => __( 'No contacts found.', 'pirate-forms' ),
				'not_found_in_trash' => __( 'No contacts found in Trash.', 'pirate-forms' ),
			);
			$args   = array(
				'labels'             => $labels,
				'description'        => __( 'Contacts from Pirate Forms', 'pirate-forms' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'pirateforms-admin',
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'custom-fields' ),
			);
			register_post_type( 'pf_contact', $args );
	    }
	}
}
