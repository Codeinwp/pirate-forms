<?php

/**
 * Provide a public-facing view for the input checkbox field.
 *
 * This file provides a public-facing view for the input checkbox field.
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

	$options    = apply_filters( 'pirate_forms_checkbox_options', $args['options'], $name );
if ( count( $options ) > 1 ) {
	$args['name']    = $name . '[]';
}

?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
<?php
foreach ( $options as $key => $val ) {
	$extra  = isset( $args['value'] ) && $key == $args['value'] ? 'checked' : '';
?>
<input type="checkbox" class="<?php echo apply_filters( "pirateform_field_classes_{$name}", '', $name, $args['type'] ); ?>" <?php echo $extra; ?> <?php echo $this->get_common( $args ); ?> value="<?php echo esc_attr( $key ); ?>">&nbsp;<?php echo $val; ?>
<?php
}
?>
</div>
