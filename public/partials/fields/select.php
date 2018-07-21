<?php

/**
 * Provide a public-facing view for the select field.
 *
 * This file provides a public-facing view for the select field.
 *
 * @package    PirateForms
 * @subpackage PirateForms/public/partials
 */
?>

<?php
if ( is_null( $wrap_classes ) ) {
	$wrap_classes = array(
		"contact_{$name}_wrap",
		isset( $args['wrap_class'] ) ? $args['wrap_class'] : '',
	);
}


?>

<div class="<?php echo implode( ' ', apply_filters( "pirateform_wrap_classes_{$name}", $wrap_classes, $name, $args['type'] ) ); ?>">
	<?php echo $label; ?>
	<select class="<?php echo apply_filters( "pirateform_field_classes_{$name}", 'form-control', $name, $args['type'] ); ?>" <?php echo $this->get_common( $args ); ?> >
	<?php
	if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
		foreach ( $args['options'] as $key => $val ) {
			$extra  = isset( $args['value'] ) && $key == $args['value'] ? 'selected' : '';
			?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php echo $extra; ?>><?php echo esc_html( $val ); ?></option>
	<?php
		}
	}
	?>
	</select>
</div>
