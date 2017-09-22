/* global cwp_top_ajaxload */
/* global console */

jQuery(document).ready(function() {
    jQuery('.pirate-forms-nav-tabs a').click(function (event) {
        event.preventDefault();
        jQuery(this).parent().addClass('active');
        jQuery(this).parent().siblings().removeClass('active');
        var tab = jQuery(this).attr('href');
        jQuery('.pirate-forms-tab-pane').not(tab).css('display', 'none');
        jQuery(tab).fadeIn();
    });

    jQuery('.pirate-forms-save-button').click(function (e) {
        e.preventDefault();
        cwpTopUpdateForm();
        return false;
    });

    jQuery('.pirate-forms-test-button').click(function (e) {
        e.preventDefault();
        cwpSendTestEmail();
        return false;
    });

    jQuery('input[name="pirateformsopt_recaptcha_field"]').on('click', function(){
        if(jQuery(this).val() === 'yes'){
            jQuery('.pirateformsopt_recaptcha').show();
        }else{
            jQuery('.pirateformsopt_recaptcha').hide();
        }
    });

    if( jQuery('input[name="pirateformsopt_recaptcha_field"]:checked').val() !== 'yes' ){
        jQuery('.pirateformsopt_recaptcha').hide();
    }

    function cwpSendTestEmail() {
        jQuery('.pirate-forms-test-message').html('');
        startAjaxIntro();
        jQuery.ajax({
            type: 'POST',
            url: cwp_top_ajaxload.ajaxurl,
            data: {
                action      : 'pirate_forms_test',
                security    : cwp_top_ajaxload.nonce
            },
            success: function (data) {
                jQuery('.pirate-forms-test-message').html(data.data.message);
            },
            error: function (MLHttpRequest, textStatus, errorThrown) {
                console.log('There was an error: ' + errorThrown);
            }
        });
        endAjaxIntro();
        return false;
    }

    function cwpTopUpdateForm() {

        startAjaxIntro();

        var data = jQuery('.pirate_forms_contact_settings').serialize();

        jQuery.ajax({
            type: 'POST',
            url: cwp_top_ajaxload.ajaxurl,

            data: {
                action      : 'pirate_forms_save',
                dataSent    : data,
                security    : cwp_top_ajaxload.nonce
            },
            success: function (response) {
                console.log(response);
            },
            error: function (MLHttpRequest, textStatus, errorThrown) {
                console.log('There was an error: ' + errorThrown);
            }
        });

        endAjaxIntro();
        return false;
    }

    // Starting the AJAX intro animation
    function startAjaxIntro() {
        jQuery('.ajaxAnimation').fadeIn();
    }

    // Ending the AJAX intro animation
    function endAjaxIntro() {
        jQuery('.ajaxAnimation').fadeOut();
    }

    /* Recaptcha site key and secret key should appear only when Add a recaptcha is selected */
    jQuery('input#pirateformsopt_recaptcha_field').change(function(){
        jQuery('.pirate-forms-grouped #pirateformsopt_recaptcha_sitekey').parent().addClass('pirate-forms-hidden');
        jQuery('.pirate-forms-grouped #pirateformsopt_recaptcha_secretkey').parent().addClass('pirate-forms-hidden');
        if( jQuery(this).is(':checked') ) {
            jQuery('.pirate-forms-grouped #pirateformsopt_recaptcha_sitekey').parent().removeClass('pirate-forms-hidden');
            jQuery('.pirate-forms-grouped #pirateformsopt_recaptcha_secretkey').parent().removeClass('pirate-forms-hidden');
        }
    });

});
