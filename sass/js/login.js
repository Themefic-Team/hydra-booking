(function ($) {

    $(document).ready(function () { 
        // Cencel Booking If .tfhb-meeting-cencel-form form submit using ajax
		
        $(document).on('submit', '#tfhb-login-from', function (e) {  
            e.preventDefault();  
            
            $this = $(this);
            var data  = new FormData(this); 

            // Remove all error dom and class 
            $this.find('.tfhb-error').removeClass('tfhb-error');
            $this.find('.tfhb-error-text').remove();
            
            data.append('action', 'tfhb_sign_in');   
            $.ajax({
                url: tfhb_app_booking.ajax_url, 
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: function (response) {
                    if(response.success){ 

                        // redirect to dashboard 
                        window.location.href = response.redirect_url;


                    }else{  
                        let fieldErrors = response.fieldErrors;
                        console.log(response);
                        // make a loop 
                        for (let key in fieldErrors) {
                            let error = fieldErrors[key];
                            let error_message = '<small class="tfhb-error-text">' + error + '</small>';
                            $this.find(`[name="${key}"]`).addClass('tfhb-error');
                            $this.find(`[name="${key}"]`).after(error_message);
                        }
                        if(response.message){
                            $this.append(`<div class="tfhb-notice tfhb-error">${response.message}</div>`);
                        }
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        // Forgot Passowrd 
        $(document).on('submit', '#tfhb-forgot-password-from', function (e) {  
            e.preventDefault();  
            
            $this = $(this);
            var data  = new FormData(this); 

            // Remove all error dom and class 
            $this.find('.tfhb-error').removeClass('tfhb-error');
            $this.find('.tfhb-error-text').remove();
            
            data.append('action', 'tfhb_forgot_password');   
            $.ajax({
                url: tfhb_app_booking.ajax_url, 
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: function (response) {
                    if(response.success){  
                        // display message
                        $this.append(`<div class="tfhb-notice tfhb-success">${response.message}</div>`);



                    }else{  
                        let fieldErrors = response.fieldErrors;
                        console.log(response);
                        // make a loop 
                        for (let key in fieldErrors) {
                            let error = fieldErrors[key];
                            let error_message = '<small class="tfhb-error-text">' + error + '</small>';
                            $this.find(`[name="${key}"]`).addClass('tfhb-error');
                            $this.find(`[name="${key}"]`).after(error_message);
                        }
                        if(response.message){
                            $this.append(`<div class="tfhb-notice tfhb-error">${response.message}</div>`);
                        }
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        // Reset Passowrd
        $(document).on('submit', '#tfhb-reset-password-from', function (e) {  
            e.preventDefault();  
            
            $this = $(this);
            var data  = new FormData(this);

            // Remove all error dom and class
            $this.find('.tfhb-error').removeClass('tfhb-error');
            $this.find('.tfhb-error-text').remove();
            
            data.append('action', 'tfhb_reset_password');   
            $.ajax({
                url: tfhb_app_booking.ajax_url, 
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: function (response) {
                    if(response.success){ 
                        // display message
                        $this.append(`<div class="tfhb-notice tfhb-success">${response.message}</div>`);
                        
                    }else{
                        let fieldErrors = response.fieldErrors;
                        console.log(response);
                        // make a loop 
                        for (let key in fieldErrors) {
                            let error = fieldErrors[key];
                            let error_message = '<small class="tfhb-error-text">' + error + '</small>';
                            $this.find(`[name="${key}"]`).addClass('tfhb-error');
                            $this.find(`[name="${key}"]`).after(error_message);
                        }
                        if(response.message){
                            $this.append(`<div class="tfhb-notice tfhb-error">${response.message}</div>`);
                        }
                    }

                        
                }}
            );
        });
	}) 

})(jQuery);

 