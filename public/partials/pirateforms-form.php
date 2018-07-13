<?php

/**
 * Provide a public-facing view for the form
 *
 * This file provide a public-facing view for the form
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PirateForms
 * @subpackage PirateForms/public/partials
 */
?>

<?php
	$form_id    = isset( $this->pirate_forms_options['id'] ) ? $this->pirate_forms_options['id'] : 'default';
?>

<div class="pirate_forms_container <?php echo $this->container_class; ?>" id="pirate_forms_container_<?php echo $form_id; ?>">
	<!-- header -->
	<?php do_action( 'pirate_forms_render_header', $this ); ?>

	<!-- thank you -->
	<?php
	if ( ! empty( $this->thank_you_message ) ) {
		do_action( 'pirate_forms_before_thankyou_message', $this );
	?>
	<div class="col-xs-12 pirate_forms_thankyou_wrap">
	<p>
		<?php echo apply_filters( 'pirate_forms_thankyou_message', $this->thank_you_message ); ?>
	</p>
	</div>
	<?php
	do_action( 'pirate_forms_after_thankyou_message', $this );
	}
	?>

	<div class="pirate_forms_wrap">
	<!-- errors -->
	<?php
	if ( ! empty( $this->errors ) ) {
		do_action( 'pirate_forms_before_errors', $this );
	?>
	<div class="col-xs-12 pirate_forms_error_box pirate_forms_error_heading">
	<p>
		<?php echo apply_filters( 'pirate_forms_error_message', __( 'Sorry, an error occured.', 'pirate-forms' ), $this->pirate_forms_options ); ?>
	</p>
	</div>
	<?php
	foreach ( $this->errors as $err ) {
	?>
<div class="col-xs-12 pirate_forms_error_box">
	<p>
	<?php echo $err; ?>
	</p>
</div>
	<?php
	}

	do_action( 'pirate_forms_after_errors', $this );
	}
	?>

	<!-- form -->
<?php
	$enctype        = 'application/x-www-form-urlencoded';
	$pirateformsopt_attachment_field = $this->pirate_forms_options['pirateformsopt_attachment_field'];
if ( ! empty( $pirateformsopt_attachment_field ) ) {
	$enctype = 'multipart/form-data';
}

	$attributes         = '';
if ( $this->form_attributes ) {
	foreach ( $this->form_attributes as $k => $v ) {
		$attributes .= " $k=$v";
	}
}

	do_action( 'pirate_forms_before_form', $this );
?>

		<form
			method="post"
			enctype="<?php echo $enctype; ?>"
			class="pirate_forms <?php echo implode( ' ', $this->form_classes ); ?>"
			<?php echo $attributes; ?>
		>
			<div class="pirate_forms_three_inputs_wrap <?php echo apply_filters( 'pirate_forms_wrap_classes', '', $this->pirate_forms_options ); ?>">
	<?php
	if ( isset( $this->contact_name ) ) {
		echo $this->contact_name;
	}

	if ( isset( $this->contact_email ) ) {
		echo $this->contact_email;
	}

	if ( isset( $this->contact_subject ) ) {
		echo $this->contact_subject;
	}

	if ( isset( $this->custom_fields ) && ! apply_filters( 'pirate_forms_show_custom_fields_last', false ) ) {
		echo $this->custom_fields;
	}
	?>
			</div>

	<?php
	if ( isset( $this->contact_message ) ) {
		echo $this->contact_message;
	}

	if ( isset( $this->attachment ) ) {
		echo $this->attachment;
	}

	if ( isset( $this->contact_checkbox ) ) {
		echo $this->contact_checkbox;
	}

	if ( isset( $this->custom_fields ) && apply_filters( 'pirate_forms_show_custom_fields_last', false ) ) {
	?>
		<div class="pirate_forms_three_inputs_wrap <?php echo apply_filters( 'pirate_forms_wrap_classes', '', $this->pirate_forms_options ); ?>">
	<?php
		echo $this->custom_fields;
	?>
		</div>
	<?php
	}

	if ( isset( $this->captcha ) ) {
		echo $this->captcha;
	}

	if ( isset( $this->contact_submit ) ) {
		echo $this->contact_submit;
	}

	if ( isset( $this->form_hidden ) ) {
		echo $this->form_hidden;
	}
	?>
		</form>

	<?php
		do_action( 'pirate_forms_after_form', $this );
	?>

		<div class="pirate_forms_clearfix"></div>
	</div>

	<!-- footer -->
	<?php do_action( 'pirate_forms_render_footer', $this ); ?>

</div>
