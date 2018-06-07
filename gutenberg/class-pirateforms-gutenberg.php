<?php

/**
 * The gutenberg functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PirateForms
 * @subpackage PirateForms/gutenberg
 */

/**
 * The gutenberg functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PirateForms
 * @subpackage PirateForms/gutenberg
 * @author     Your Name <email@example.com>
 */
class PirateForms_Gutenberg {

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
	 * Load block assets for the editor.
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'pirate-forms-block',
			PIRATEFORMS_URL . 'gutenberg/js/block.build.js',
			array( 'wp-i18n', 'wp-blocks', 'wp-components' ),
			filemtime( PIRATEFORMS_DIR . '/gutenberg/js/block.build.js' )
		);

		wp_localize_script(
			'pirate-forms-block', 'pfjs', array(
				'url'  => PIRATEFORMS_SLUG . '/v' . intval( PIRATEFORMS_API_VERSION ) . '/get_form/#/',
				'forms' => $this->get_forms( true ),
				'settings'  => array(
					'default'   => admin_url( 'admin.php?page=pirateforms-admin' ),
					'form'      => admin_url( 'post.php?post=#&action=edit' ),
				),
				'i10n'  => array(
					'captcha'   => __( 'Save and reload the page to see the CAPTCHA', 'pirate-forms' ),
					'reload'    => __( 'Some forms have changed since the last time this post was saved. We have reloaded those forms. You may need to save the post again.', 'pirate-forms' ),
					'settings'  => __( 'Modify Settings', 'pirate-forms' ),
					'select_form'   => __( 'Select Form', 'pirate-forms' ),
					'select_ajax'   => __( 'Use Ajax to submit form', 'pirate-forms' ),
					'plugin'    => PIRATEFORMS_NAME,
				),
			)
		);

		wp_enqueue_script( 'pirate-forms-custom-spam', PIRATEFORMS_URL . 'public/js/custom-spam.js', array( 'jquery' ) );
		wp_localize_script(
			'pirate-forms-custom-spam', 'pf', array(
				'spam'   => array(
					'label' => apply_filters( 'pirate_forms_custom_spam_label', __( 'I\'m human!', 'pirate-forms' ) ),
					'value' => wp_create_nonce( PIRATEFORMS_NAME ),
					'gutenberg' => 1,
				),
			)
		);

		$language = get_locale();
		if ( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_current_language' ) ) {
			$language = pll_current_language();
		}
		wp_enqueue_script( 'recaptcha', "https://www.google.com/recaptcha/api.js?hl=$language" );

		wp_enqueue_style( 'pirate-forms-front-css', PIRATEFORMS_URL . 'public/css/front.css' );
		wp_enqueue_style( 'pirate-forms-block-css', PIRATEFORMS_URL . 'gutenberg/css/block.css' );
	}

	/**
	 * Register the block.
	 */
	public function register_block() {
		register_block_type(
			'pirate-forms/form', array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the pirate form block.
	 */
	function render_block( $atts = null ) {
		$arributes  = array();
		if ( is_array( $atts ) && $atts ) {
			if ( array_key_exists( 'form_id', $atts ) ) {
				$attributes['id'] = $atts['form_id'];
			}
			if ( array_key_exists( 'ajax', $atts ) ) {
				$attributes['ajax'] = $atts['ajax'];
			}
		} else {
			$attributes['id'] = $atts;
		}

		$params     = '';
		if ( $attributes ) {
			foreach ( $attributes as $key => $value ) {
				$params .= " $key=$value";
			}
		}

		return do_shortcode( "[pirate_forms $params]" );
	}

	/**
	 * Register the REST endpoints.
	 */
	public function register_endpoints() {
		register_rest_route(
			PIRATEFORMS_SLUG, '/v' . intval( PIRATEFORMS_API_VERSION ) . '/get_form/(?P<id>\d+)/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_form_html' ),
			)
		);
	}

	/**
	 * Get the requested form's HTML content.
	 */
	function get_form_html( WP_REST_Request $request ) {
		$return = $this->validate_params( $request, array( 'id' ) );
		if ( is_wp_error( $return ) ) {
			return $return;
		}

		return new WP_REST_Response( array( 'html' => $this->render_block( $request->get_param( 'id' ) ) ) );
	}

	/**
	 * Validate REST params.
	 */
	private function validate_params( WP_REST_Request $request, $params = array() ) {
		$return = array();
		foreach ( $params as $param ) {
			$value = $request->get_param( $param );
			if ( ! is_numeric( $value ) && empty( $value ) ) {
				return new WP_Error( $param . '_invalid', sprintf( __( 'Invalid %s', 'pirate-forms' ), $param ), array( 'status' => 403 ) );
			} else {
				$return[] = $value;
			}
		}

		return $return;
	}

	/**
	 * Get all the forms.
	 */
	private function get_forms( $include_default = false ) {
		$forms          = array();
		if ( $include_default ) {
			$forms[]    = array( 'label' => __( 'Default', 'pirate-forms' ), 'value' => 0 );
		}
		if ( defined( 'PIRATEFORMSPRO_NAME' ) ) {
			$query  = new WP_Query(
				apply_filters(
					'pirate_forms_get_forms_attributes', array(
						'post_type'     => 'pf_form',
						'numberposts'   => 300,
						'post_status'   => 'publish',
					), basename( __FILE__ )
				)
			);

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$forms[]    = array( 'label' => get_the_title(), 'value' => get_the_ID() );
				}
			}
		}
		return $forms;
	}

}
