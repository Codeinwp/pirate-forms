jQuery(document).ready(function() {

    var session_var = pirateFormsObject.errors;

    if( (typeof session_var != undefined) && (session_var != '') && (typeof jQuery('#contact') != undefined) ) {

        jQuery('html, body').animate({
            scrollTop: jQuery('#contact').offset().top
        }, 'slow');
    }
});