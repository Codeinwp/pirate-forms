<?php

/**
 * Provide a public-facing view for the input phone field.
 *
 * This file provides a public-facing view for the input phone field.
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
?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
	<?php echo $label; ?>
	<input type="tel" class="<?php echo apply_filters( "pirateform_field_classes_{$name}", 'form-control', $name, $args['type'] ); ?>" <?php echo $this->get_common( $args, array( 'value' ) ); ?> >
</div>
