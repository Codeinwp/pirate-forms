<?php

/**
 * Utility functions
 *
 * @since    1.0.0
 */
class PirateForms_Util {

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
		$pirate_forms_options = self::get_option();
		return apply_filters( 'pirateformpro_get_form_attributes', $pirate_forms_options, $id );
	}
}
