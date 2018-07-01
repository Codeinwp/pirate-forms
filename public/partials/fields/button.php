<?php

/**
 * Provide a public-facing view for the button field.
 *
 * This file provides a public-facing view for the button field.
 *
 * @package    PirateForms
 * @subpackage PirateForms/public/partials
 */
?>

<?php

$name   = 'submit';

if ( is_null( $wrap_classes ) ) {
	$wrap_classes = array(
		'col-xs-12 col-sm-6 form_field_wrap',
		"contact_{$name}_wrap",
	);
}
?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
	<button type="submit" class="<?php echo apply_filters( "pirateform_field_classes_{$name}", $args['class'], $name, $args['type'] ); ?>" <?php echo $this->get_common( $args ); ?>><?php echo isset( $args['value'] ) ? $args['value'] : ''; ?></button>
</div>
