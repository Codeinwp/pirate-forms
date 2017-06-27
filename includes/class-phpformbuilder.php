<?php

/**
 * Builds the form
 *
 * @since    1.0.0
 */
class PhpFormBuilder {

	/**
	 * Stores all form inputs
	 *
	 * @var      string    $inputs
	 * @since    1.0.0
	 */
	private $inputs = array();

	/**
	 * Stores all form attributes
	 *
	 * @var      array    $form
	 * @since    1.0.0
	 */
	private $form = array();

	/**
	 * Does this form have a submit value?
	 *
	 * @var      bool    $has_submit
	 * @since    1.0.0
	 */
	private $has_submit = false;

	/**
	 * Constructor function to set form action and attributes
	 */
	function __construct( $action = '', $args = false ) {

		/* if the form has an attachment option change the enctype to multipart/form-data */

		$pirateformsopt_attachment_field = PirateForms::pirate_forms_get_key( 'pirateformsopt_attachment_field' );
		if ( ! empty( $pirateformsopt_attachment_field ) && ($pirateformsopt_attachment_field == 'yes') ) {
			$pirate_forms_enctype = 'multipart/form-data';
		} else {
			$pirate_forms_enctype = 'application/x-www-form-urlencoded';
		}

		// Default form attributes
		$defaults = array(
			'action'       => $action,
			'method'       => 'post',
			'enctype'      => $pirate_forms_enctype,
			'class'        => array(),
			'id'           => '',
			'markup'       => 'html',
			'novalidate'   => false,
			'add_nonce'    => false,
			'add_honeypot' => true,
			'form_element' => true,
			'add_submit'   => true,
		);

		// Merge with arguments, if present
		if ( $args ) {
			$settings = array_merge( $defaults, $args );
		} // End if().
		else {
			$settings = $defaults;
		}

		// Iterate through and save each option
		foreach ( $settings as $key => $val ) {
			// Try setting with user-passed setting
			// If not, try the default with the same key name
			if ( ! $this->set_att( $key, $val ) ) {
				$this->set_att( $key, $defaults[ $key ] );
			}
		}
	}

	/**
	 * Validate and set form
	 *
	 * @param string        $key A valid key; switch statement ensures validity.
	 * @param string | bool $val A valid value; validated for each key.
	 *
	 * @return bool
	 */
	function set_att( $key, $val ) {

		switch ( $key ) :

			case 'action':
				break;

			case 'method':
				if ( ! in_array( $val, array( 'post', 'get' ) ) ) {
					return false;
				}
				break;

			case 'enctype':
				if ( ! in_array( $val, array( 'application/x-www-form-urlencoded', 'multipart/form-data' ) ) ) {
					return false;
				}
				break;

			case 'markup':
				if ( ! in_array( $val, array( 'html', 'xhtml' ) ) ) {
					return false;
				}
				break;

			case 'class':
			case 'id':
				if ( ! $this->_check_valid_attr( $val ) ) {
					return false;
				}
				break;

			case 'novalidate':
			case 'add_honeypot':
			case 'form_element':
			case 'add_submit':
				if ( ! is_bool( $val ) ) {
					return false;
				}
				break;

			case 'add_nonce':
				if ( ! is_string( $val ) && ! is_bool( $val ) ) {
					return false;
				}
				break;

			default:
				return false;

		endswitch;

		$this->form[ $key ] = $val;

		return true;

	}

	/**
	 * Add an input field to the form for outputting later
	 */
	function add_input( $label, $args = '', $slug = '' ) {

		if ( empty( $args ) ) {
			$args = array();
		}

		// Create a valid id or class attribute
		if ( empty( $slug ) ) {
			$slug = $this->_make_slug( $label );
		}

		$defaults = array(
			'type'             => 'text',
			'name'             => $slug,
			'id'               => $slug,
			'label'            => $label,
			'value'            => '',
			'placeholder'      => '',
			'class'            => array(),
			'min'              => '',
			'max'              => '',
			'step'             => '',
			'autofocus'        => false,
			'checked'          => false,
			'selected'         => false,
			'required'         => false,
			'add_label'        => true,
			'options'          => array(),
			'wrap_tag'         => 'div',
			'wrap_class'       => array( 'form_field_wrap' ),
			'wrap_id'          => '',
			'wrap_style'       => '',
			'before_html'      => '',
			'after_html'       => '',
			'request_populate' => true,
		);

		// Combined defaults and arguments
		// Arguments override defaults
		$args                  = array_merge( $defaults, $args );
		$this->inputs[ $slug ] = $args;

	}

	/**
	 * Add multiple inputs to the input queue
	 *
	 * @return bool
	 */
	function add_inputs( $arr ) {

		if ( ! is_array( $arr ) ) {
			return false;
		}

		foreach ( $arr as $field ) {
			$this->add_input(
				$field[0], isset( $field[1] ) ? $field[1] : '',
				isset( $field[2] ) ? $field[2] : ''
			);
		}

		return true;
	}

	/**
	 * Build the HTML for the form based on the input queue
	 *
	 * @param bool $echo Should the HTML be echoed or returned?.
	 *
	 * @return string
	 */
	function build_form( $echo = true ) {

		$output = '';

		if ( $this->form['form_element'] ) {
			$output .= '<form method="' . $this->form['method'] . '" ';

			if ( ! empty( $this->form['enctype'] ) ) {
				$output .= ' enctype="' . $this->form['enctype'] . '"';
			}

			if ( ! empty( $this->form['action'] ) ) {
				$output .= ' action="' . $this->form['action'] . '"';
			}

			if ( ! empty( $this->form['id'] ) ) {
				$output .= ' id="' . $this->form['id'] . '"';
			}

			if ( count( $this->form['class'] ) > 0 ) {
				$output .= $this->_output_classes( $this->form['class'] );
			}

			if ( $this->form['novalidate'] ) {
				$output .= ' novalidate';
			}

			$output .= '>';
			$this->set_element( 'form_start', $output );
		}

		$form_end   = '';

		// Add honeypot anti-spam field
		if ( $this->form['add_honeypot'] ) {
			$this->add_input( 'Leave blank to submit', array(
				'name'             => 'honeypot',
				'slug'             => 'honeypot',
				'id'               => 'form_honeypot',
				'wrap_tag'         => 'div',
				'wrap_class'       => array( 'form_field_wrap', 'hidden' ),
				'wrap_id'          => '',
				'wrap_style'       => 'display: none',
				'request_populate' => false,
			) );
		}

		// Add a WordPress nonce field
		if ( $this->form['add_nonce'] && function_exists( 'wp_create_nonce' ) ) {
			$this->add_input( 'WordPress nonce', array(
				'value'            => wp_create_nonce( $this->form['add_nonce'] ),
				'add_label'        => false,
				'type'             => 'hidden',
				'request_populate' => false,
			) );
		}

		// Iterate through the input queue and add input HTML
		foreach ( $this->inputs as $val ) :

			$add_to_form_end    = false;
			$min_max_range      = '';
			$element            = '';
			$end                = '';
			$attr               = '';
			$field              = '';
			$label_html         = '';

			// Automatic population of values using $_REQUEST data
			if ( $val['request_populate'] && isset( $_REQUEST[ $val['name'] ] ) ) {

				// Can this field be populated directly?
				if ( ! in_array( $val['type'], array( 'html', 'title', 'radio', 'checkbox', 'select', 'submit' ) ) ) {
					$val['value'] = $_REQUEST[ $val['name'] ];
				}
			}

			// Automatic population for checkboxes and radios
			if (
				$val['request_populate'] &&
				( $val['type'] == 'radio' || $val['type'] == 'checkbox' ) &&
				empty( $val['options'] )
			) {
				$val['checked'] = isset( $_REQUEST[ $val['name'] ] ) ? true : $val['checked'];
			}

			switch ( $val['type'] ) {

				case 'html':
					$element = '';
					$end     = $val['label'];
					break;

				case 'title':
					$element = '';
					$end     = '
					<h3>' . $val['label'] . '</h3>';
					break;

				case 'textarea':
					$element = 'textarea';
					$end     = ' class="form-control" placeholder="' . $val['placeholder'] . '">' . esc_attr( $val['value'] ) . '</textarea>';
					break;

				case 'select':
					$element = 'select';
					$end     .= '>';
					foreach ( $val['options'] as $key => $opt ) {
						$opt_insert = '';
						if (
							// Is this field set to automatically populate?
							$val['request_populate'] &&

							// Do we have $_REQUEST data to use?
							isset( $_REQUEST[ $val['name'] ] ) &&

							// Are we currently outputting the selected value?
							$_REQUEST[ $val['name'] ] === $key
						) {
							$opt_insert = ' selected';

							// Does the field have a default selected value?
						} elseif ( $val['selected'] === $key ) {
							$opt_insert = ' selected';
						}
						$end .= '<option value="' . $key . '"' . $opt_insert . '>' . $opt . '</option>';
					}
					$end .= '</select>';
					break;
				case 'captcha':
					$element = 'div';
					$end     = ' class="g-recaptcha pirate-forms-g-recaptcha" data-sitekey="' . $val['value'] . '"></div>';
					break;
				case 'file':
					$element = 'input';
					$end     = ' class="" type="' . $val['type'] . '">';
					break;
				case 'radio':
				case 'checkbox':

					// Special case for multiple check boxes
					if ( count( $val['options'] ) > 0 ) :
						$element = '';
						foreach ( $val['options'] as $key => $opt ) {
							$slug = $this->_make_slug( $opt );
							$end .= sprintf(
								'<input type="%s" name="%s[]" value="%s" id="%s"',
								$val['type'],
								$val['name'],
								$key,
								$slug
							);
							if (
								// Is this field set to automatically populate?
								$val['request_populate'] &&

								// Do we have $_REQUEST data to use?
								isset( $_REQUEST[ $val['name'] ] ) &&

								// Is the selected item(s) in the $_REQUEST data?
								in_array( $key, $_REQUEST[ $val['name'] ] )
							) {
								$end .= ' checked';
							}
							$end .= $this->field_close();
							$end .= ' <label for="' . $slug . '">' . $opt . '</label>';
						}
						$label_html = '<div class="checkbox_header">' . $val['label'] . '</div>';
						break;
					endif;
				case 'submit':
					$element = 'div class="col-xs-12 col-sm-6 col-lg-6 form_field_wrap contact_submit_wrap"><button';
					$end .= ' type="' . $val['type'] . '">' . $val['value'] . '</button></div>';
					break;
				default :
					$element = 'input';

					/* don't add a placeholder attribute for input type=hidden */
					if ( ! empty( $val['type'] ) && ($val['type'] == 'hidden' ) ) {
						$add_to_form_end = true;
						$end .= ' class="form-control" type="' . $val['type'] . '" value="' . esc_attr( $val['value'] ) . '"';
					} else {
						$end .= ' class="form-control" type="' . $val['type'] . '" value="' . esc_attr( $val['value'] ) . '" placeholder="' . $val['placeholder'] . '"';
					}
					if ( 'form_honeypot' === $val['id'] ) {
						$add_to_form_end = true;
					}
					$end .= $val['checked'] ? ' checked' : '';
					$end .= $this->field_close();
					break;

			}// End switch().

			// Added a submit button, no need to auto-add one
			if ( $val['type'] === 'submit' ) {
				$this->has_submit = true;
			}

			// Special number values for range and number types
			if ( $val['type'] === 'range' || $val['type'] === 'number' ) {
				$min_max_range .= ! empty( $val['min'] ) ? ' min="' . $val['min'] . '"' : '';
				$min_max_range .= ! empty( $val['max'] ) ? ' max="' . $val['max'] . '"' : '';
				$min_max_range .= ! empty( $val['step'] ) ? ' step="' . $val['step'] . '"' : '';
			}

			// Add an ID field, if one is present
			$id = ! empty( $val['id'] ) ? ' id="' . $val['id'] . '"' : '';

			// Output classes
			$class = $this->_output_classes( $val['class'] );

			// Special HTML5 fields, if set
			$attr .= $val['autofocus'] ? ' autofocus' : '';
			$attr .= $val['checked'] ? ' checked' : '';
			$attr .= $val['required'] ? ' required' : '';

			// Build the label
			if ( ! empty( $label_html ) ) {
				$field .= $label_html;
			} elseif ( $val['add_label'] && ! in_array( $val['type'], array( 'hidden', 'submit', 'title', 'html', 'textarea', 'captcha' ) ) ) {
				$field .= '<label for="' . $val['id'] . '">' . $val['label'] . '</label>';
			}

			// An $element was set in the $val['type'] switch statement above so use that
			if ( ! empty( $element ) ) {
				if ( $val['type'] === 'checkbox' ) {
					$field = '
					<' . $element . $id . ' name="' . $val['name'] . '"' . $min_max_range . $class . $attr . $end .
					         $field;
				} elseif ( $val['type'] === 'captcha' ) { /* don't add name attribute to div's holding recaptcha keys */
					$field .= '
					<' . $element . $id . ' ' . $min_max_range . $class . $attr . $end;
				} else {
					$field .= '
					<' . $element . $id . ' name="' . $val['name'] . '"' . $min_max_range . $class . $attr . $end;
				}
			} else {
				$field .= $end;
			}

			// Parse and create wrap, if needed
			if ( $val['type'] != 'hidden' && $val['type'] != 'html' ) :

				$wrap_before = $val['before_html'];
				if ( ! empty( $val['wrap_tag'] ) ) {
					$wrap_before .= '<' . $val['wrap_tag'];
					$wrap_before .= count( $val['wrap_class'] ) > 0 ? $this->_output_classes( $val['wrap_class'] ) : '';
					$wrap_before .= ! empty( $val['wrap_style'] ) ? ' style="' . $val['wrap_style'] . '"' : '';
					$wrap_before .= ! empty( $val['wrap_id'] ) ? ' id="' . $val['wrap_id'] . '"' : '';
					$wrap_before .= '>';
				}

				$wrap_after = $val['after_html'];
				if ( ! empty( $val['wrap_tag'] ) ) {
					$wrap_after = '</' . $val['wrap_tag'] . '>' . $wrap_after;
				}

				$output .= $wrap_before . $field . $wrap_after;
			else :
				$output .= $field;
			endif;

			if ( $add_to_form_end ) {
				$form_end .= $output;
			} else {
				$this->set_element( $val['id'], $output );
			}

		endforeach;

		// Auto-add submit button
		if ( ! $this->has_submit && $this->form['add_submit'] ) {
			$output .= '<div class="form_field_wrap"><input type="submit" value="Submit" name="submit"></div>';
			$this->set_element( 'contact_submit', $output );
		}

		// Close the form tag if one was added
		if ( $this->form['form_element'] ) {
			$form_end .= '</form>';
			$this->set_element( 'form_end', $form_end );
		}

		$output = $this->load_theme();
		// Output or return?
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Easy way to auto-close fields, if necessary
	 *
	 * @since    1.0.0
	 */
	function field_close() {
		return $this->form['markup'] === 'xhtml' ? ' />' : '>';
	}

	/**
	 * Validates id and class attributes
	 *
	 * @since    1.0.0
	 */
	private function _check_valid_attr( $string ) {

		$result = true;

		// Check $name for correct characters
		// "^[a-zA-Z0-9_-]*$"
		return $result;

	}

	/**
	 * Create a slug from a label name
	 *
	 * @since    1.0.0
	 */
	private function _make_slug( $string ) {

		$result = '';

		$result = str_replace( '"', '', $string );
		$result = str_replace( "'", '', $result );
		$result = str_replace( '_', '-', $result );
		$result = preg_replace( '~[\W\s]~', '-', $result );

		$result = strtolower( $result );

		return $result;

	}

	/**
	 * Parses and builds the classes in multiple places
	 *
	 * @since    1.0.0
	 */
	private function _output_classes( $classes ) {

		$output = '';

		if ( is_array( $classes ) && count( $classes ) > 0 ) {
			$output .= ' class="';
			foreach ( $classes as $class ) {
				$output .= $class . ' ';
			}
			$output .= '"';
		} elseif ( is_string( $classes ) ) {
			$output .= ' class="' . $classes . '"';
		}

		return $output;
	}

	/**
	 * Sets the element as a variable that can be used in the templates
	 *
	 * @since    1.2.6
	 */
	public function set_element( $element_name, &$output ) {
		$name           = str_replace( array( 'pirate-forms-', '-' ), array( '', '_' ), $element_name );
		$this->$name    = $output;
		$output         = '';
	}

	/**
	 * Load the correct template
	 *
	 * @since    1.2.6
	 */
	private function load_theme() {
		$default    = PIRATEFORMS_DIR__ . 'public/partials/pirateforms-form.php';
		$custom     = trailingslashit( get_template_directory() ) . 'pirate-forms/form.php';
		$file       = $default;
		if ( is_readable( $custom ) ) {
			$file   = $custom;
		}
		ob_start();
		include $file;
		$output = ob_get_clean();
		return $output;
	}
}
