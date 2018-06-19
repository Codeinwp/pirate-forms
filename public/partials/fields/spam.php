<?php

/**
 * Provide a public-facing view for the custom spam field.
 *
 * This file provides a public-facing view for the custom spam field.
 *
 * @package    PirateForms
 * @subpackage PirateForms/public/partials
 */
?>

<?php
	$wrap_classes = array(
		'col-xs-12 col-sm-6 form_field_wrap pirateform_wrap_classes_spam_wrap',
	);

?>

<div class="<?php echo implode( ' ', apply_filters( 'pirateform_wrap_classes_spam', $wrap_classes, $name, $args['type'] ) ); ?>">
	<div id="<?php echo $args['id']; ?>" class="<?php echo apply_filters( 'pirateform_field_classes_spam', 'pirate-forms-maps-custom', $name, $args['type'] ); ?>"></div>
</div>
