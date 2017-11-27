/* global pirateFormsObject */
/* global jQuery */
jQuery(document).ready(function() {

    var session_var = pirateFormsObject.errors;

    if( (typeof session_var !== 'undefined') && (session_var !== '') && (typeof jQuery('#contact') !== 'undefined') && (typeof jQuery('#contact').offset() !== 'undefined') ) {

        jQuery('html, body').animate({
            scrollTop: jQuery('#contact').offset().top
        }, 'slow');
    }
	
    if(jQuery('.pirate-forms-maps-custom').length > 0){
        jQuery('.pirate-forms-maps-custom').html(jQuery('<input id="xobkcehc" name="xobkcehc" type="' + 'xobkcehc'.split('').reverse().join('') + '" value="' + pirateFormsObject.spam.value + '"><label for="xobkcehc"><span>' + pirateFormsObject.spam.label + '</span></label>'));
    }

});
