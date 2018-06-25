<?php

/**
 * Provide a public-facing view for the input textarea field.
 *
 * This file provides a public-facing view for the input textarea field.
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

	$cols       = isset( $args['cols'] ) ? $args['cols'] : 30;
	$rows       = isset( $args['rows'] ) ? $args['rows'] : 5;

?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
	<?php echo $label; ?>
	<textarea rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" class="<?php echo apply_filters( "pirateform_field_classes_{$name}", 'form-control', $name, $args['type'] ); ?>" <?php echo $this->get_common( $args ); ?> ><?php echo isset( $args['value'] ) ? esc_attr( $args['value'] ) : ''; ?></textarea>
</div>
