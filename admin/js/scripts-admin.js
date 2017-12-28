/* global cwp_top_ajaxload */
/* global console */
/* global tinyMCE */

jQuery(document).ready(function() {
    initAll();
});

function initAll(){
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
        tinyMCE.triggerSave();
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
        if(jQuery('#pirateformsopt_recaptcha_fieldyes').is(':checked') && (jQuery('#pirateformsopt_recaptcha_sitekey').val() === '' || jQuery('#pirateformsopt_recaptcha_secretkey').val() === '')){
            window.alert(cwp_top_ajaxload.i10n.recaptcha);
            return;
        }

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

    // add visibility toggle to password type fields
    jQuery('.pirate-forms-password-toggle').append('<span class="dashicons dashicons-visibility"></span>');
    jQuery('.pirate-forms-password-toggle span').on('click', function(){
        var span = jQuery(this);
        if(span.hasClass('dashicons-visibility')){
            span.parent().find('input[type="password"]').attr('type', 'text');
            span.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        }else{
            span.parent().find('input[type="text"]').attr('type', 'password');
            span.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

	// tootips in settings.
	jQuery(document).tooltip({
		items: '.dashicons-editor-help',
		hide: 200,
		position: {within: '#pirate-forms-main'},

		content: function () {
			return jQuery(this).find('div').html();
		},
		show: null,
		close: function (event, ui) {
			ui.tooltip.hover(
				function () {
					jQuery(this).stop(true).fadeTo(400, 1);
				},
				function () {
					jQuery(this).fadeOut('400', function () {
						jQuery(this).remove();
					});
				});
		}
	});
}