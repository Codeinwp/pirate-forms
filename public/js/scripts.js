/* global jQuery */
jQuery(document).ready(function() {

    jQuery('.pirate-forms-file-upload-button').on('click', function () {
        var $button = jQuery(this);
        $button.parent().find('input[type=file]').on('change', function(){
            $button.parent().find('input[type=text]').val(jQuery(this).val()).change();
        });
	    $button.parent().find('input[type=file]').focus().click();
    });

    jQuery('.pirate-forms-file-upload-input').on('click', function(){
        jQuery(this).parent().find('.pirate-forms-file-upload-button').trigger('click');
    });
    jQuery('.pirate-forms-file-upload-input').on('focus', function(){
        jQuery(this).blur();
    });
});