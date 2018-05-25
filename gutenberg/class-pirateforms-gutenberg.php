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
			'pirate-forms-block-js',
			PIRATEFORMS_URL . 'gutenberg/js/block.build.js',
			array( 'wp-i18n', 'wp-blocks', 'wp-components' ),
			filemtime( PIRATEFORMS_DIR . '/gutenberg/js/block.build.js' )
		);

		wp_localize_script(
			'pirate-forms-block-js', 'pfjs', array(
				'html'  => $this->render_block(),
			)
		);

		wp_enqueue_style( 'pirate-forms-block-css', PIRATEFORMS_URL . 'public/css/front.css' );
	}

	/**
	 * Register the block.
	 */
	public function register_block() {
		register_block_type(
			'pirate-forms/default', array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the default pirate form block.
	 */
	function render_block( $attributes = null ) {
		return do_shortcode( '[pirate_forms]' );
	}
}
