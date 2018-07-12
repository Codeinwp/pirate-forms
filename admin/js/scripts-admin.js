/* global cwp_top_ajaxload */
/* global console */
/* global tinyMCE */

(function($, pf){

    $(document).ready(function() {
        initAll();
    });

    function initAll(){
        $('.pirate-forms-nav-tabs a').click(function (event) {
            event.preventDefault();
            $(this).parent().addClass('active');
            $(this).parent().siblings().removeClass('active');
            var tab = $(this).attr('href');
            $('.pirate-forms-tab-pane').not(tab).css('display', 'none');
            $(tab).fadeIn();
        });

        $('.pirate-forms-save-button').click(function (e) {
            e.preventDefault();
            tinyMCE.triggerSave();
            cwpTopUpdateForm();
            return false;
        });

        $('.pirate-forms-test-button').click(function (e) {
            e.preventDefault();
            cwpSendTestEmail();
            return false;
        });


        $('input[name="pirateformsopt_recaptcha_field"]').on('click', function(){
            if($(this).val() === 'yes'){
                $('.pirateformsopt_recaptcha').show();
            }else{
                $('.pirateformsopt_recaptcha').hide();
            }
        });

        if( $('input[name="pirateformsopt_recaptcha_field"]:checked').val() !== 'yes' ){
            $('.pirateformsopt_recaptcha').hide();
        }

        if(jQuery.isFunction(jQuery.fn.tooltip)){
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
        }else{
                jQuery('.pirate-forms-grouped').each(function(i, x){
                    var desc = jQuery(x).find('.pirate_forms_option_description');
                    if(desc.length === 0){
                        return;
                    }
                    var text = desc.html();
                    jQuery('<p class="description" style="margin-bottom: ' + jQuery(x).css('margin-bottom') + '"><span class="dashicons dashicons-editor-help"></span>' + text + '</p>').insertAfter(jQuery(x));
                    jQuery(x).css('margin-bottom', 0);
                    desc.remove();
                    jQuery(x).find('span.dashicons-editor-help').remove();
                });
        }

        /* Recaptcha site key and secret key should appear only when Add a recaptcha is selected */
        $('input#pirateformsopt_recaptcha_field').change(function(){
            $('.pirate-forms-grouped #pirateformsopt_recaptcha_sitekey').parent().addClass('pirate-forms-hidden');
            $('.pirate-forms-grouped #pirateformsopt_recaptcha_secretkey').parent().addClass('pirate-forms-hidden');
            if( $(this).is(':checked') ) {
                $('.pirate-forms-grouped #pirateformsopt_recaptcha_sitekey').parent().removeClass('pirate-forms-hidden');
                $('.pirate-forms-grouped #pirateformsopt_recaptcha_secretkey').parent().removeClass('pirate-forms-hidden');
            }
        });

        // add visibility toggle to password type fields
        $('.pirate-forms-password-toggle').append('<span class="dashicons dashicons-visibility"></span>');
        $('.pirate-forms-password-toggle span').on('click', function(){
            var span = $(this);
            if(span.hasClass('dashicons-visibility')){
                span.parent().find('input[type="password"]').attr('type', 'text');
                span.removeClass('dashicons-visibility').addClass('dashicons-hidden');
            }else{
                span.parent().find('input[type="text"]').attr('type', 'password');
                span.removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });

        $('.pirateforms-notice-gdpr.is-dismissible').on('click', '.notice-dismiss', function(){
            $.ajax({
                url         : pf.ajaxurl,
                type        : 'POST',
                data        : {
                    id          : $(this).parent().attr('data-dismissible'),
                    _action     : 'dismiss-notice',
                    security    : pf.nonce,
                    action      : pf.slug
                }
           });
        });
    }

    function cwpSendTestEmail() {
        $('.pirate-forms-test-message').html('');
        startAjaxIntro();
        $.ajax({
            type: 'POST',
            url: pf.ajaxurl,
            data: {
                action      : 'pirate_forms_test',
                security    : pf.nonce
            },
            success: function (data) {
                $('.pirate-forms-test-message').html(data.data.message);
            },
            error: function (MLHttpRequest, textStatus, errorThrown) {
                console.log('There was an error: ' + errorThrown);
            }
        });
        endAjaxIntro();
        return false;
    }

    function cwpTopUpdateForm() {
        if($('#pirateformsopt_recaptcha_fieldyes').is(':checked') && ($('#pirateformsopt_recaptcha_sitekey').val() === '' || $('#pirateformsopt_recaptcha_secretkey').val() === '')){
            window.alert(pf.i10n.recaptcha);
            return;
        }

        startAjaxIntro();

        var data = $('.pirate_forms_contact_settings').serialize();

        $.ajax({
            type: 'POST',
            url: pf.ajaxurl,

            data: {
                action      : 'pirate_forms_save',
                dataSent    : data,
                security    : pf.nonce
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
        $('.ajaxAnimation').fadeIn();
    }

    // Ending the AJAX intro animation
    function endAjaxIntro() {
        $('.ajaxAnimation').fadeOut();
    }

})(jQuery, cwp_top_ajaxload);