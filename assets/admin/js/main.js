(function ($) {

    $(document).ready(function () { 
        // hydra-booking-insights-data-we-collect hide 
        $('.hydra-booking-insights-data-we-collect').parents('.updated').find('p.description').hide();

        $('.tfhb-license-register-btn').on('click', function() {
            var button = $(this);
            button.prop('disabled', true).text('Registering...');

            $.ajax({
                url: tfhb_admin_notice.ajax_url,
                type: 'POST',
                data: {
                    action: 'tfhb_user_registration_license',
                    nonce: tfhb_admin_notice._nonce,
                    email: 'joybanglaasd@gmail.com'
                },
                success: function(response) {
                    
                    button.prop('disabled', false).text('Register');
                }
            });
        });

    });

})(jQuery);