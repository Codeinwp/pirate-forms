<?php

/**
 * Builds the form
 *
 * @since    1.0.0
 */
class PirateForms_PhpFormBuilder {

	/**
	 * The array of options for the form.
	 *
	 * @access   public
	 * @var      array $pirate_forms_options The array of options for the form.
	 */
	public $pirate_forms_options;

	/**
	 * Build the HTML for the form based on the input queue
	 *
	 * @param array $elements The array of HTML elements.
	 * @param array $pirate_forms_options The array of options for the form.
	 * @param bpol  $from_widget Is the form in the widget.
	 *
	 * @return string
	 */
	function build_form( $elements, $pirate_forms_options, $from_widget ) {
		$this->pirate_forms_options = $pirate_forms_options;
		$pirateformsopt_attachment_field = $pirate_forms_options['pirateformsopt_attachment_field'];
		if ( ! empty( $pirateformsopt_attachment_field ) ) {
			$pirate_forms_enctype = 'multipart/form-data';
		} else {
			$pirate_forms_enctype = 'application/x-www-form-urlencoded';
		}

		$classes    = array();
		$classes[]  = $from_widget ? 'widget-on' : '';
		$form_start = '<form method="post" enctype="' . $pirate_forms_enctype . '" class="pirate_forms ';

		$html_helper        = new PirateForms_HTML();
		$form_end           = '';
		$custom_fields      = '';
		foreach ( $elements as $val ) {
			if ( isset( $val['is_custom'] ) && $val['is_custom'] ) {
				// we will combine the HTML for all the custom fields and save it under one element name.
				$custom_fields  .= $html_helper->add( $val, false );
				$classes[]      = $val['id'] . '-on';
			} else {
				$element    = $html_helper->add( $val, false );
				if ( ( 'form_honeypot' === $val['id'] || in_array( $val['type'], array( 'hidden', 'div' ) ) ) && $val['id'] !== 'pirate-forms-maps-custom'
				) {
					$form_end .= $element;
				}
				if ( $val['id'] === 'pirate-forms-maps-custom' ) {
					$this->set_element( 'captcha', $element );
				}
				$this->set_element( $val['id'], $element );
				$classes[]      = $val['id'] . '-on';
			}
		}

		$this->set_element( 'custom_fields', $custom_fields );

		$form_attributes    = array_filter( apply_filters( 'pirate_forms_form_attributes', array( 'action' => '' ) ) );
		if ( $form_attributes ) {
			// if additiona classes are provided, add them to our classes.
			if ( array_key_exists( 'class', $form_attributes ) ) {
				$form_classes   = explode( ' ', $form_attributes['class'] );
				$classes        = array_merge( $classes, $form_classes );
				unset( $form_attributes['class'] );
			}

			// don't allow overriding of method or enctype.
			if ( array_key_exists( 'method', $form_attributes ) ) {
				unset( $form_attributes['method'] );
			}
			if ( array_key_exists( 'enctype', $form_attributes ) ) {
				unset( $form_attributes['enctype'] );
			}
		}

		$form_start .= implode( ' ', $classes ) . '"';
		if ( $form_attributes ) {
			foreach ( $form_attributes as $k => $v ) {
				$form_start .= " $k=$v";
			}
		}
		$form_start .= '>';
		$this->set_element( 'form_start', $form_start );

		$form_end .= '</form>';
		$this->set_element( 'form_end', $form_end );

		$output = $this->load_theme();
		return $output;
	}

	/**
	 * Sets the element as a variable that can be used in the templates
	 *
	 * @since    1.2.6
	 */
	public function set_element( $element_name, $output ) {
		$name           = str_replace( array( 'pirate-forms-', '-' ), array( '', '_' ), $element_name );
		$this->$name    = $output;
	}

	/**
	 * Load the correct template
	 *
	 * @since    1.2.6
	 */
	private function load_theme() {
		$default    = PIRATEFORMS_DIR . 'public/partials/pirateforms-form.php';
		$custom     = trailingslashit( get_template_directory() ) . 'pirate-forms/form.php';
		$file       = $default;
		if ( is_readable( $custom ) ) {
			$file   = $custom;
		} elseif ( file_exists( $custom ) ) {
			do_action( 'themeisle_log_event', PIRATEFORMS_NAME, sprintf( 'cannot access theme = %s', $custom ), 'error', __FILE__, __LINE__ );
		}
		ob_start();
		include $file;
		$output = ob_get_clean();
		return $output;
	}

}
