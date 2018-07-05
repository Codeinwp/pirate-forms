<?php

/**
 * Utility functions
 *
 * @since    1.0.0
 */
class PirateForms_Util {

	const MAGIC_TAG_PREFIX      = '{';
	const MAGIC_TAG_POSTFIX     = '}';

	/**
	 * The default fields used by the plugin.
	 *
	 * @access   private
	 * @var      array    $DEFAULT_FIELDS    The default fields used by the plugin.
	 */
	public static $DEFAULT_FIELDS      = array( 'name', 'email', 'subject', 'message', 'checkbox' );

	/**
	 * Return the table.
	 *
	 * @since    1.0.0
	 */
	public static function get_table( $body ) {
		$html       = '';
		foreach ( $body as $type => $value ) {
			switch ( $type ) {
				case 'heading':
					$html   .= '<h2>' . $value . '</h2>';
					break;
				case 'body':
					$html   .= '<table>';
					foreach ( $value as $k => $v ) {
						$html   .= self::table_row( $k . ':', $v );
					}
					if ( isset( $body['rows'] ) ) {
						// special case for new lite and old pro where the old pro returns the table rows as an HTML string.
						$html   .= $body['rows'];
					}
					$html   .= '</table>';
					break;
			}
		}
		return $html;
	}

	/**
	 * Return the table row
	 *
	 * @since    1.0.0
	 */
	public static function table_row( $key, $value ) {
		return '<tr><th>' . $key . '</th><td>' . $value . '</td></tr>';
	}

	/**
	 * Returns if the domain is localhost
	 *
	 * @since     1.0.0
	 */
	public static function is_localhost() {
		$server_name = strtolower( $_SERVER['SERVER_NAME'] );
		return in_array( $server_name, array( 'localhost', '127.0.0.1' ) );
	}

	/**
	 * Gets the from email
	 *
	 * @since     1.0.0
	 */
	public static function get_from_email() {
		$admin_email = get_option( 'admin_email' );
		$sitename    = strtolower( $_SERVER['SERVER_NAME'] );
		if ( PirateForms_Util::is_localhost() ) {
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
	public static function get_option( $id = null ) {
		$pirate_forms_options = get_option( 'pirate_forms_settings_array' );
		if ( is_null( $id ) ) {
			return $pirate_forms_options;
		}
		return isset( $pirate_forms_options[ $id ] ) ? $pirate_forms_options[ $id ] : '';
	}

	/**
	 * Set all the settings
	 *
	 * @since     1.0.0
	 */
	public static function set_option( $data ) {
		update_option( 'pirate_forms_settings_array', $data );
	}

	/**
	 * Update a key in the settings
	 *
	 * @since     1.0.0
	 */
	public static function update_option( $id, $value ) {
		$pirate_forms_options = get_option( 'pirate_forms_settings_array' );
		if ( is_null( $id ) ) {
			return false;
		}
		$pirate_forms_options[ $id ] = $value;
		self::set_option( $pirate_forms_options );
		return true;
	}

	/**
	 * Check if the email/IP is blacklisted
	 *
	 * @param string $error_key the key for the session object.
	 * @param string $email the email id to check.
	 * @param string $ip the IP to check.
	 *
	 * @since    1.0.0
	 */
	public static function is_blacklisted( $error_key, $email, $ip ) {
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

		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'email = %s, IP = %s, final_blocked_arr = %s', $email, $ip, print_r( $final_blocked_arr, true ) ), 'debug', __FILE__, __LINE__ );

		if ( ! empty( $final_blocked_arr ) ) {
			if (
				in_array( $email, $final_blocked_arr ) ||
				in_array( $ip, $final_blocked_arr )
			) {
				$_SESSION[ $error_key ]['blacklist-blocked'] = __( 'Form submission blocked!', 'pirate-forms' );

				return true;
			}
		}

		return false;
	}

	/**
	 * Get the list of all pages
	 *
	 * @since    1.0.0
	 */
	public static function get_thank_you_pages() {
		$content = array(
			'' => __( 'None', 'pirate-forms' ),
		);
		$items   = get_posts(
			apply_filters(
				'pirate_forms_thank_you_pages_args',
				array(
					'post_type'     => 'page',
					'numberposts'   => 300,
					'post_status'   => 'publish',
				)
			)
		);
		if ( ! empty( $items ) ) :
			foreach ( $items as $item ) :
				$content[ $item->ID ] = $item->post_title;
			endforeach;
		endif;

		return $content;

	}

	/**
	 * Get the post meta value
	 *
	 * @since    1.0.0
	 */
	public static function get_post_meta( $id, $key, $single = false ) {
		return get_post_meta( $id, PIRATEFORMS_SLUG . $key, $single );
	}

	/**
	 * Get the form options for the custom form id, else default
	 *
	 * @since    1.0.0
	 */
	public static function get_form_options( $id = null ) {
		if ( empty( $id ) ) {
			$id = null;
		}
		$pirate_forms_options = self::get_option();
		return apply_filters( 'pirateformpro_get_form_attributes', $pirate_forms_options, $id );
	}


	/**
	 * Start sessing if it does not exist.
	 */
	public static function session_start() {
		if ( session_id() === '' ) {
			// @codingStandardsIgnoreStart
			@session_start();
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Seed the session variable that contains the error(s).
	 */
	public static function save_error( $error_key, $new_error_key ) {
		$array      = $_SESSION[ $error_key ];
		$_SESSION[ 'error' . $new_error_key ] = $array;
		unset( $_SESSION[ $error_key ] );
		return false;
	}

	/**
	 * The default email content.
	 */
	public static function get_default_email_content( $html = true, $id = null, $first_time = false ) {
		$body               = array();
		$body['heading']    = sprintf( __( 'Contact form submission from %s', 'pirate-forms' ), get_bloginfo( 'name' ) . ' (' . site_url() . ')' );
		$body['body']       = array();
		$pirate_forms_options = PirateForms_Util::get_form_options( $id );

		$elements           = self::$DEFAULT_FIELDS;
		foreach ( $elements as $k ) {
			if ( is_array( $pirate_forms_options ) && ! array_key_exists( 'pirateformsopt_' . $k . '_field', $pirate_forms_options ) ) {
				continue;
			}
			$display        = $pirate_forms_options[ 'pirateformsopt_' . $k . '_field' ];
			if ( ! $first_time && empty( $display ) ) {
				continue;
			}
			$val            = $pirate_forms_options[ 'pirateformsopt_label_' . $k ];
			if ( empty( $val ) ) {
				$val        = ucwords( $k );
			}
			$body['body'][ $val ] = self::MAGIC_TAG_PREFIX . $k . self::MAGIC_TAG_POSTFIX;
		}

		if ( isset( $pirate_forms_options['pirateformsopt_store_ip'] ) && 'yes' === $pirate_forms_options['pirateformsopt_store_ip'] ) {
			$body['body'][ __( 'IP address', 'pirate-forms' ) ] = self::MAGIC_TAG_PREFIX . 'ip' . self::MAGIC_TAG_POSTFIX;
		}

		$body['body'][ __( 'IP search', 'pirate-forms' ) ]  = 'http://whatismyipaddress.com/ip/' . self::MAGIC_TAG_PREFIX . 'ip' . self::MAGIC_TAG_POSTFIX;
		$body['body'][ __( 'Came from', 'pirate-forms' ) ]  = self::MAGIC_TAG_PREFIX . 'referer' . self::MAGIC_TAG_POSTFIX;
		$body['body'][ __( 'Sent from page', 'pirate-forms' ) ] = self::MAGIC_TAG_PREFIX . 'permalink' . self::MAGIC_TAG_POSTFIX;

		if ( ! empty( $id ) ) {
			$fields     = self::get_post_meta( $id, 'custom' );
			if ( $fields ) {
				foreach ( $fields[0] as $custom ) {
					if ( empty( $custom['label'] ) || empty( $custom['display'] ) ) {
						continue;
					}
					// replace . and space with _ (PHP does not like dots in variable names so it automatically converts them to _).
					$field = strtolower( str_replace( array( ' ', '.' ), '_', stripslashes( sanitize_text_field( $custom['label'] ) ) ) );
					$body['body'][ stripslashes( $custom['label'] ) ] = self::MAGIC_TAG_PREFIX . $field . self::MAGIC_TAG_POSTFIX;
				}
			}
		}

		if ( $html ) {
			return self::get_table( $body );
		}

		return $body;
	}

	/**
	 * Replace the magic tags with their values.
	 */
	public static function get_magic_tags( $id = null ) {
		$pirate_forms_options = PirateForms_Util::get_form_options( $id );

		$elements           = self::$DEFAULT_FIELDS;
		foreach ( $elements as $k ) {
			if ( is_array( $pirate_forms_options ) && ! array_key_exists( 'pirateformsopt_label_' . $k, $pirate_forms_options ) ) {
				continue;
			}
			$val            = $pirate_forms_options[ 'pirateformsopt_label_' . $k ];
			if ( empty( $val ) ) {
				$val        = ucwords( $k );
			}
			$tags[ $k ]     = $val;
		}

		if ( isset( $pirate_forms_options['pirateformsopt_save_attachment'] ) && 'yes' === $pirate_forms_options['pirateformsopt_save_attachment'] ) {
			$tags   += array(
				'attachments'        => __( 'Attachment(s)', 'pirate-forms' ),
			);
		}

		if ( isset( $pirate_forms_options['pirateformsopt_store_ip'] ) && 'yes' === $pirate_forms_options['pirateformsopt_store_ip'] ) {
			$tags   += array(
				'ip'        => __( 'IP address', 'pirate-forms' ),
			);
		}

		$tags   += array(
			'referer'   => __( 'Came from', 'pirate-forms' ),
			'permalink' => __( 'Sent from page', 'pirate-forms' ),
		);
		if ( ! empty( $id ) ) {
			$fields     = self::get_post_meta( $id, 'custom' );
			if ( $fields ) {
				foreach ( $fields[0] as $custom ) {
					if ( empty( $custom['label'] ) ) {
						continue;
					}
					// replace . and space with _ (PHP does not like dots in variable names so it automatically converts them to _).
					$field = strtolower( str_replace( array( ' ', '.' ), '_', stripslashes( sanitize_text_field( $custom['label'] ) ) ) );
					$tags[ $field ] = stripslashes( $custom['label'] );
				}
			}
		}

		$tags   = apply_filters( 'pirate_forms_register_magic_tags', $tags );
		$html   = '';
		foreach ( $tags as $k => $v ) {
			$html   .= '<b>' . self::MAGIC_TAG_PREFIX . $k . self::MAGIC_TAG_POSTFIX . '</b>: ' . $v . '<br/>';
		}
		return $html;
	}

	/**
	 * Replace the magic tags with their values.
	 */
	public static function replace_magic_tags( $content, $body ) {
		$html           = $content;
		foreach ( $body['magic_tags'] as $tag => $value ) {
			$from       = htmlspecialchars( self::MAGIC_TAG_PREFIX . $tag . self::MAGIC_TAG_POSTFIX );
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, "replacing $from with $value", 'debug', __FILE__, __LINE__ );
			$html       = str_replace( $from, stripslashes( $value ), $html );
		}

		$html           = apply_filters( 'pirate_forms_replace_magic_tags', $html, $body['magic_tags'] );

		// any tags that are left, should be replaced with an empty string.
		$html       = preg_replace( '/\{.+\}/', '', $html );

		do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'replace tags in %s with %s to finally give %s', $content, print_r( $body['magic_tags'], true ), $html ), 'debug', __FILE__, __LINE__ );
		return $html;
	}
}
