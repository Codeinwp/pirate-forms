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

<?php do_action( 'pirate_forms_render_thankyou', $this ); ?>

<div class="pirate_forms_wrap">
	<?php do_action( 'pirate_forms_render_errors', $this ); ?>

	<?php echo $this->form_start; ?>

	<?php do_action( 'pirate_forms_render_fields', $this ); ?>

	<?php echo $this->form_end; ?>
	<div class="pirate_forms_clearfix"></div>
</div>

<?php do_action( 'pirate_forms_render_footer', $this ); ?>
