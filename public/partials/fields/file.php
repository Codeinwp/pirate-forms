<?php

/**
 * Provide a public-facing view for the input file field.
 *
 * This file provides a public-facing view for the input file field.
 *
 * @package    PirateForms
 * @subpackage PirateForms/public/partials
 */
?>

<?php
if ( is_null( $wrap_classes ) ) {
	$wrap_classes = array(
		'col-xs-12 form_field_wrap',
		"contact_{$name}_wrap",
		isset( $args['wrap_class'] ) ? $args['wrap_class'] : '',
	);
}

	// since the file field is going to be non-focussable, let's put the required attributes (if available) on the text field
	$text_args      = array(
		'id'        => $args['id'],
		'name'      => $args['name'],
		'class'     => 'pirate-forms-file-upload-input',
	);
	if ( isset( $args['required'] ) && $args['required'] && isset( $args['required_msg'] ) ) {
		$text_args['required']      = $args['required'];
		$text_args['required_msg']  = $args['required_msg'];
		unset( $args['required'] );
		unset( $args['required_msg'] );
	}

?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
	<div class="pirate-forms-file-upload-wrapper">
		<div class="pirate-forms-file-upload-wrapper"><input type="file" class="<?php echo apply_filters( "pirateform_field_classes_{$name}", 'form-control', $name, $args['type'] ); ?>" <?php echo $this->get_common( $text_args, array( 'value' ) ); ?>  tabindex="-1"></div>
	</div>
</div>
