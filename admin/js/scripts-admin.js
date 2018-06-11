/* global cwp_top_ajaxload */
/* global console */
/* global tinyMCE */

(function($, cwp_top_ajaxload){

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

        function cwpSendTestEmail() {
            $('.pirate-forms-test-message').html('');
            startAjaxIntro();
            $.ajax({
                type: 'POST',
                url: cwp_top_ajaxload.ajaxurl,
                data: {
                    action      : 'pirate_forms_test',
                    security    : cwp_top_ajaxload.nonce
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
                window.alert(cwp_top_ajaxload.i10n.recaptcha);
                return;
            }

            startAjaxIntro();

            var data = $('.pirate_forms_contact_settings').serialize();

            $.ajax({
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
            $('.ajaxAnimation').fadeIn();
        }

        // Ending the AJAX intro animation
        function endAjaxIntro() {
            $('.ajaxAnimation').fadeOut();
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

        // tootips in settings.
        $(document).tooltip({
            items: '.dashicons-editor-help',
            hide: 200,
            position: {within: '#pirate-forms-main'},

            content: function () {
                return $(this).find('div').html();
            },
            show: null,
            close: function (event, ui) {
                ui.tooltip.hover(
                    function () {
                        $(this).stop(true).fadeTo(400, 1);
                    },
                    function () {
                        $(this).fadeOut('400', function () {
                            $(this).remove();
                        });
                    });
            }
        });

        $('.pirateforms-notice-gdpr.is-dismissible').on('click', '.notice-dismiss', function(){
            $.ajax({
                url         : cwp_top_ajaxload.ajaxurl,
                type        : 'POST',
                data        : {
                    id          : $(this).parent().attr('data-dismissible'),
                    _action     : 'dismiss-notice',
                    security    : cwp_top_ajaxload.nonce,
                    action      : cwp_top_ajaxload.slug
                }
           });
        });
    }
})(jQuery, cwp_top_ajaxload);