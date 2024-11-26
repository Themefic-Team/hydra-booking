(function ($) {

    $(document).ready(function () { 
        // Cencel Booking If .tfhb-meeting-cencel-form form submit using ajax
		
        $(document).on('submit', '.tfhb-meeting-cencel-form', function (e) {  
            e.preventDefault();  
            $this = $(this);
            var data  = new FormData(this); 
            
            data.append('action', 'tfhb_meeting_form_cencel'); 
            data.append('nonce', tfhb_app_booking.nonce); 
            $.ajax({
                url: tfhb_app_booking.ajax_url, 
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: function (response) {
                    if(response.success){
                    
                        $this.find('.tfhb-meeting-confirmation .tfhb-forms').html('');
                        $this.find('.tfhb-meeting-confirmation').append(`<div class="tfhb-notice tfhb-success">${response.data.message}</div>`);

                    }else{ 
                        $this.find('.tfhb-meeting-confirmation').append(`<div class="tfhb-notice tfhb-error">${response.data.message}</div>`);
                        
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
	}) 

})(jQuery);

 