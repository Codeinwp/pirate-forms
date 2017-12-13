<?php

/**
 * Provide a public-facing view for the input number field.
 *
 * This file provides a public-facing view for the input number field.
 *
 * @package    PirateForms
 * @subpackage PirateForms/public/partials
 */
?>

<?php
if ( is_null( $wrap_classes ) ) {
	$wrap_classes = array(
		'col-xs-12',
		'pirate_forms_three_inputs form_field_wrap',
		"contact_{$name}_wrap",
		isset( $args['wrap_class'] ) ? $args['wrap_class'] : '',
	);
}

	$min    = isset( $args['min'] ) ? $args['min'] : 0;
	$max    = isset( $args['max'] ) ? $args['max'] : PHP_INT_MAX;

?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
	<?php echo $label; ?>
	<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" class="<?php echo apply_filters( "pirateform_field_classes_{$name}", 'form-control', $name, $args['type'] ); ?>" <?php echo $this->get_common( $args, array( 'value' ) ); ?> >
</div>
