<?php
/**
 * Class for testing pirate forms GDPR support.
 *
 * @package     PirateForms
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Class for testing pirate forms GDPR support.
 */
class Test_GDPR extends WP_Ajax_UnitTestCase {

	/**
	 * Looks like they forgot to add the privacy hooks to their test cases!
	 *
	 * @access public
	 */
	public static function setUpBeforeClass() {
		require_once ABSPATH . '/wp-admin/includes/ajax-actions.php';

		$actions	= array( 'wp-privacy-export-personal-data', 'wp-privacy-erase-personal-data', );
		// Register the core actions that were forgotten for 4.9.6.
		foreach ( $actions as $action ) {
			if ( ! in_array( $action, self::$_core_actions_post ) ) {
				self::$_core_actions_post[] = $action;
			}
		}

		parent::setUpBeforeClass();
	}

	/**
	 * Testing data erase.
	 *
	 * @access public
	 */
	public function test_data_erase() {
		global $wp_version;
		if ( version_compare( $wp_version, '4.9.6', '<' ) ) {
			$this->markTestSkipped( 'Can only be tested with WP 4.9.6+' );
		}

		do_action( 'admin_head' );

		$settings   = PirateForms_Util::get_option();
		$this->assertEquals( 'yes', $settings['pirateformsopt_nonce'] );

		$settings['pirateformsopt_nonce']   = 'no';
		$settings['pirateformsopt_recaptcha_field']   = 'no';
		$settings['pirateformsopt_store']   = 'yes';
		$settings['pirateformsopt_email_content']   = '<h2>Contact form submission from Test Blog (http://example.org)</h2><table><tr><th>Your Name:</th><td>{name}</td></tr><tr><th>Your Email:</th><td>{email}</td></tr><tr><th>Subject:</th><td>{subject}</td></tr><tr><th>Your message:</th><td>{message}</td></tr><tr><th>IP address:</th><td>{ip}</td></tr><tr><th>IP search:</th><td>http://whatismyipaddress.com/ip/{ip}</td></tr><tr><th>Sent from page:</th><td>{permalink}</td></tr></table>';

		PirateForms_Util::set_option( $settings );

		$settings   = PirateForms_Util::get_option();

		$_POST  = array(
			'honeypot'                  => '',
			'pirate-forms-contact-name' => 'x',
			'pirate-forms-contact-email' => 'x@x.com',
			'pirate-forms-contact-subject' => 'x',
			'pirate-forms-contact-message' => 'x',
		);
		do_action( 'pirate_unittesting_template_redirect' );

		$posts  = get_posts(
			array(
				'post_type'     => 'pf_contact',
				'post_author'  => 1,
				'post_status'  => 'private',
				'numberposts'   => 1,
				'fields'        => 'ids',
			)
		);

		$this->assertEquals( 1, count( $posts ) );

		$this->erase( 'x@x.com' );

		$posts  = get_posts(
			array(
				'post_type'     => 'pf_contact',
				'post_author'  => 1,
				'post_status'  => 'private',
				'numberposts'   => 1,
				'fields'        => 'ids',
			)
		);

		$this->assertEquals( 0, count( $posts ) );
	}

	private function erase( $email ) {
		$this->_setRole( 'administrator' );

		$GLOBALS['hook_suffix'] = '';
		$_POST	= array(
			'action'	=> 'add_remove_personal_data_request',
			'type_of_action'	=> 'remove_personal_data',
			'username_or_email_to_export'	=> $email,
			'_wpnonce'	=> wp_create_nonce( 'personal-data-request' ),
		);
		$_REQUEST	= $_POST;

		ob_start();
		// we'll have to call this directly as there is no other option.
		_wp_personal_data_removal_page();
		ob_end_clean();

		// check that the request was saved.
		$requests	= get_posts(array(
			'post_type'	=> 'user_request',
			'posts_per_page' => -1,
			'post_status'    => 'request-pending',
			'fields'         => 'ids',
		));

		$this->assertEquals( 1, count( $requests ) );

		$request_id		= $requests[0];
		$this->assertGreaterThan( 0, $request_id );

		$nonce			= wp_create_nonce( 'wp-privacy-erase-personal-data-' . $request_id );
		$erasers		= apply_filters( 'wp_privacy_personal_data_erasers', array() );

		$_POST	= array(
			'security'	=> $nonce,
			'id'	=> $request_id,
			'page'	=> 1,
			'eraser'	=> count( $erasers ),
		);

		ob_start();
		try {
			$this->_handleAjax( 'wp-privacy-erase-personal-data' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();
	}

 }
