(function ($) {
	// wp.i18n.setLocaleData( { '': {} }, 'hydra-booking' ); // Ensure translations load
	// const { __, _x } = wp.i18n;
	// const { __ } = wp.i18n;

    $(document).ready(function () { 
		const tfhb_local_timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
		const tfhb_i18n = tfhb_app_booking.i18n;

		// function tfhb translate 
		function tfhbTranslate(key) {
            return tfhb_i18n[key] || key + 'not found';
        }
		function tfhbTranslateNumber(key) { 
			// provided key like 2025 breack down and replace each letter with translation
			return key.toString().split('').map(function (letter) { 
                return tfhbTranslate(letter);
            }).join('');
        }
		function tfhbTranslateTimeSlot(key) {  
			// key like "09:00 AM " AM string   
			if(key.slice(6, 8) != ''){
				return tfhbTranslateNumber(key.slice(0, 2)) + ':' + tfhbTranslateNumber(key.slice(3, 5)) +' ' + tfhbTranslate(key.slice(6, 8));

			}else{
				return tfhbTranslateNumber(key.slice(0, 2)) + ':' + tfhbTranslateNumber(key.slice(3, 5));
			}
        }

        // Initialize the Date Picker
        /**
         * Time Zone Change
         * @author Jahid
         */
        $(document).on('click', '.tfhb-timezone-tabs ul li', function (e) {
            $('.tfhb-timezone-tabs ul li').removeClass('active');
            var $this = $(this);
            $this.addClass('active');
        });

        // });
	
		/**
		 * Time Select
		 * @author Sydur
		 * */ 
		$('.tfhb-meeting-box').each(function(){

			// Get Calender Id
			let $this = $(this),
			 	calenderId = $this.attr('data-calendar'),
				calenderData =  eval("tfhb_app_booking_" + calenderId), 
				preloader =  `<div class="tfhb-preloader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="200" height="200" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-linecap="round" fill="none" stroke-dasharray="51.83627878423159 51.83627878423159" stroke="#2e6b38" stroke-width="4" r="33" cy="50" cx="50">
				<animateTransform values="0 50 50;360 50 50" keyTimes="0;1" dur="1s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
			  </circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg></span></div>`;
			  
 

			let date = new Date();
			let year = date.getFullYear();
			let month = date.getMonth();
		 
			const tfhb_calendar_navs = $this.find(".tfhb-calendar-navigation span") 
			console.log(tfhb_app_booking.i18n) 
			// Array of month names
			const months = [
				"January",
				"February",
				"March",
				"April",
				"May",
				"June",
				"July",
				"August",
				"September",
				"October",
				"November",
				"December"
			]; 


			tfhb_date_manipulate( $this, calenderData, year, month, date, months);


			// Attach a click event listener to each icon
			tfhb_calendar_navs.each(function() {
				// When an icon is clicked
				$(this).on("click", function() { 
					// or "tfhb-calendar-next"
					month = $(this).attr("id") === "tfhb-calendar-prev" ? month - 1 : month + 1;
		
					// Check if the month is out of range
					if (month < 0 || month > 11) {
		
						// Set the date to the first day of the 
						// month with the new year
						date = new Date(year, month, new Date().getDate());
		
						// Set the year to the new year
						year = date.getFullYear();
		
						// Set the month to the new month
						month = date.getMonth();
					} else {
		
						// Set the date to the current date
						date = new Date();
					}
		
					// Hide the time slot with animation
					$this.find('.tfhb-meeting-times').animate({left: "-50%", width: 0, opacity: 0 }, 300,
					function() {
						$(this).css("display", "none");

					});

					
					// Call the tfhb_date_manipulate function to 
					// update the tfhb-calendar display
					tfhb_date_manipulate( $this, calenderData, year, month, date, months);
				
					// first date of the month exp: 
					// 2021-04-01
					var current_date = new Date(year, month, 2).toISOString().split('T')[0];  
					setTimeout(function(){
						// Your code here 
						tfhb_times_manipulate( $this, current_date, calenderData);  
					}, 1000); // 2000 milliseconds = 2 seconds
 


				});
			});

			// Select Date 
            $($this).on('click', '.tfhb-calendar-dates li', function (e) {  
				var $this_li = $(this);
				if($this_li.hasClass('inactive')){
					return false;
				} 
				
				// tfhb-meeting-times  with animation 
				// $this.find('.tfhb-meeting-times').show();
				$this.find('.tfhb-meeting-times').css("display", "block").animate({left: "0%", opacity: 1, width: 224}, 400,);


				$this.find('.tfhb-available-times').removeClass('inactive');  
				$this.find('.tfhb-available-times').html('');
				$this.find('.tfhb-available-times').append('<ul></ul>');
				var selected_date = $this_li.attr('data-date'); 
				$this.find("#meeting_dates").val(selected_date);
				// selected_date_format = new Date(selected_date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
				// Get Only Day 
				var selected_date_format_weekday = tfhbTranslate(new Date(selected_date).toLocaleDateString('en-US', { weekday: 'long' }));  
				var selected_date_format_month = tfhbTranslate(new Date(selected_date).toLocaleDateString('en-US', { month: 'long' }));  
				var selected_date_format_day = tfhbTranslateNumber(new Date(selected_date).toLocaleDateString('en-US', { day: 'numeric' }));  
				// Format like tat "Tuesday, March 4"
				var selected_date_format =  selected_date_format_weekday+', '+selected_date_format_month+ ' '+selected_date_format_day;
                // Set the date in the select box
				// selected_date_format_day 
				// translate the selected
				// alert(selected_date_format_day);

				 

				$this.find('.tfhb-meeting-times .tfhb-select-date').html(selected_date_format); 
				// get selected date calenderData.calander_available_time_slot
				var selected_date_time_slot = calenderData.calander_available_time_slot[selected_date]; 
				// if selected_date_time_slot is not empty
				// then append the time slot
				// else show the message
				if(selected_date_time_slot.length > 0){
					for (var i = 0; i < selected_date_time_slot.length; i++) { 
						var remaining = '';
						// if selected_date_time_slot[i].remaining is not undefined then show the time slot
 						if(selected_date_time_slot[i].remaining != undefined){
							 remaining = '<span class="tfhb-time-slot-remaining tfhb-flexbox"><span></span>'+tfhbTranslateNumber(selected_date_time_slot[i].remaining)+' '+tfhbTranslate('seats left') +'</span>';
						}
						// Remove 
						// Add with animation when data available
						$this.find('.tfhb-available-times ul').append('<li class="tfhb-flexbox"> <div class="time" data-time-start="'+ selected_date_time_slot[i].start +'" data-time-end="'+ selected_date_time_slot[i].end +'">' + tfhbTranslateTimeSlot(selected_date_time_slot[i].start) + remaining + ' </div> </li>');

					}
				}else{
					$this.find('.tfhb-available-times ul').append('<li class="tfhb-flexbox"> <div>No time slot available in this date ! Try another date.</span> </div>');
				}

				$this.find('.tfhb-calendar-dates li').removeClass('active');  
				$this_li.addClass('active');
				 
 
			});
 
            $this.find('#attendee_time_zone_' + calenderId+'').on('change', function (e) {  
			 
				// Hide the time slot with animation 
				$this.find('.tfhb-meeting-times').animate({left: "-50%", width: 0, opacity: 0 }, 300,
				function() { 
					$(this).css("display", "none");

					
				});

				var current_date = new Date(year, month, 2).toISOString().split('T')[0];  
			 
				// Get the first day of the month 
				tfhb_times_manipulate( $this, current_date, calenderData);    
				
			});
            $this.find('input[name="tfhb_time_format"]').on('change', function (e) { 
				
				var current_date = new Date(year, month, 2).toISOString().split('T')[0];  
			  
				tfhb_times_manipulate($this, current_date, calenderData, function() { 
					$this.find('.tfhb-calendar-dates li.active').trigger('click');    
					// Your logic here
				});
				
			});

			// Get the Select2 dropdown element
			const $timezoneSelect = $this.find('#attendee_time_zone_' + calenderId);

			// Check if the element exists
			if ($timezoneSelect.length) {
				// Initialize Select2 with options
				$timezoneSelect.select2({
					dropdownCssClass: 'tfhb-select2-dropdown',
				});
		
				// Set the value and trigger the change event
				$timezoneSelect.val(tfhb_local_timeZone).trigger('change'); 
			}

			/**
			* Time Select
			* @author Jahid
			*/
			// $this.find('input[name="tfhb_time_format"], .tfhb-time-zone-select').on('change', function (e) { 
            // $this.find('.tfhb-available-times li .time').on('click', function (e) {
			$($this).on('click', '.tfhb-available-times li .time', function (e) { 
				$this_time = $(this);
				$('.tfhb-available-times li .time').removeClass('active');

				// Your code here 
				$('.tfhb-available-times li .next').remove(); 
				var selected_time_start = $this_time.attr('data-time-start');  
				var selected_time_end = $this_time.attr('data-time-end');
				$this.find("input[name='meeting_time_start']").val(selected_time_start);
				$this.find("input[name='meeting_time_end']").val(selected_time_end);
			
				$this_time.parent().append('<span class="next tfhb-flexbox tfhb-gap-8"> '+tfhbTranslate('Next')+'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 10L14 10" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 4.16666L14.8333 9.99999L9 15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>');
				$this_time.addClass('active');
				

			});
 
 			$($this).on('click', '.tfhb-available-times li .next', function (e) {  
				
				var time_zone = $this.find('#attendee_time_zone_'+calenderId).val();
				var meeting_dates = $this.find("#meeting_dates").val();
				var meeting_time_start = $this.find("#meeting_time_start").val();
				var meeting_time_end = $this.find("#meeting_time_end").val(); 

				var thimeZone_html = `<li class="tfhb-flexbox tfhb_time_zone_info tfhb-gap-8">
						<input type="hidden" id="recurring_maximum" name="recurring_maximum" value="' . esc_attr( $meeting['recurring_maximum'] ) . '">
						<div class="tfhb-icon">  
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g clip-path="url(#clip0_1840_26071)">
									<path d="M8.00065 14.6667C11.6825 14.6667 14.6673 11.6819 14.6673 8.00001C14.6673 4.31811 11.6825 1.33334 8.00065 1.33334C4.31875 1.33334 1.33398 4.31811 1.33398 8.00001C1.33398 11.6819 4.31875 14.6667 8.00065 14.6667Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M8.00065 1.33334C6.28881 3.13078 5.33398 5.51784 5.33398 8.00001C5.33398 10.4822 6.28881 12.8692 8.00065 14.6667C9.71249 12.8692 10.6673 10.4822 10.6673 8.00001C10.6673 5.51784 9.71249 3.13078 8.00065 1.33334Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M1.33398 8H14.6673" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</g>
									<defs>
									<clipPath id="clip0_1840_26071">
									<rect width="16" height="16" fill="white"/>
									</clipPath>
									</defs>
							</svg>
						</div> 
						<div>
							`+time_zone+`(`+tfhbTranslateTimeSlot(meeting_time_start)+`)
						</div>
					</li>`;
					// date time format like that  9:00pm, Saturday, April 25
					
				var date_time = new Date(meeting_dates);
				// var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
				var selected_date_format_weekday = tfhbTranslate(date_time.toLocaleDateString('en-US', { weekday: 'long' }));  
				var selected_date_format_month = tfhbTranslate(date_time.toLocaleDateString('en-US', { month: 'long' }));  
				var selected_date_format_day = tfhbTranslateNumber(date_time.toLocaleDateString('en-US', { day: 'numeric' }));  
				var selected_date_format_year = tfhbTranslateNumber(date_time.toLocaleDateString('en-US', { year: 'numeric' }));  
				// Format like tat "Tuesday, March 4"
				var selected_date_format =  selected_date_format_weekday+', '+selected_date_format_month+ ' '+selected_date_format_day +', ' + selected_date_format_year;
				var date_time_html = `<li class="tfhb-flexbox tfhb_date_time_info tfhb-gap-8">
						<input type="hidden" id="recurring_maximum" name="recurring_maximum" value="' . esc_attr( $meeting['recurring_maximum'] ) . '">
						<div class="tfhb-icon">  
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M5.33398 1.33334V4.00001" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M10.666 1.33334V4.00001" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M12.6667 2.66666H3.33333C2.59695 2.66666 2 3.26361 2 3.99999V13.3333C2 14.0697 2.59695 14.6667 3.33333 14.6667H12.6667C13.403 14.6667 14 14.0697 14 13.3333V3.99999C14 3.26361 13.403 2.66666 12.6667 2.66666Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M2 6.66666H14" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M6 10.6667L7.33333 12L10 9.33334" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div> 
						<div>
							`
							+selected_date_format+
							`
						</div>
					</li>`;

					$this.find('.tfhb-meeting-details ul').append(thimeZone_html);
					$this.find('.tfhb-meeting-details ul').append(date_time_html);

				// Your code here 
				$this.find('.tfhb-timezone').hide();
				

				$this.find('.tfhb-meeting-times').animate({left: "-50%", width: 0, opacity: 0 }, 300,
				function() {
					$(this).css("display", "none");

					
				});
				$this.find('.tfhb-calander-times').animate({
					left: "-5%", 
					opacity: 0, 
				}, 400, function() {
					// After animation is complete, hide the element
					$(this).css("display", "none");

					$this.find('.tfhb-meeting-booking-form').css("display", "block").animate({left: "0", opacity: 1, width: 536 }, 300);
				});
 

				
			});

			// Full Description Showing
			$('span.tfhb-see-description').on('click', function() {
				$('.tfhb-short-description').slideUp();
				$('.tfhb-full-description').slideDown();
			});
	
			// See Less Description Showing
			$('span.tfhb-see-less-description').on('click', function() {
				$('.tfhb-full-description').slideUp();
				$('.tfhb-short-description').slideDown();
			});

			$($this).on('click', '.tfhb-meeting-booking-form .tfhb-back-btn', function (e) { 

				 
				$this.find('.tfhb-meeting-booking-form').animate({
					left: "-5%", 
					opacity: 0
				}, 300, function() {
					// After animation is complete, hide the element
					$(this).css("display", "none"); 

					$this.find('.tfhb-timezone').show();

					$this.find('.tfhb-calander-times').css("display", "flex").animate({
						left: "0", 
						opacity: 1
					}, 200, function() { 
						// After animation is complete, hide the element 
					});


					$this.find('.tfhb-meeting-times').css("display", "block").animate({left: "0", width: 224}, 400, 
					function() {
						$(this).css("opacity", "1");

						$this.find('.tfhb-meeting-details ul .tfhb_time_zone_info').remove();
						$this.find('.tfhb-meeting-details ul .tfhb_date_time_info').remove();
					});
					
				});

				
			}); 

			// Ajax Submit .tfhb-meeting-form.ajax-submit'  
			$this.find('.tfhb-meeting-form.ajax-submit').on('submit', function (e) {
				e.preventDefault(); 
				var data  = new FormData(this); 
				// make this form data as Object
				var InformationData = {};
				data.forEach(function(value, key){
					InformationData[key] = value;
				}); 
				
				
				tfhb_from_submission($this, preloader, InformationData, calenderData);
			});
			document.addEventListener( 'wpcf7mailsent', function( event ) {
				var data = event.detail.formData; 
				var InformationData = {};
				data.forEach(function(value, key){
					InformationData[key] = value;
				}); 
				
				tfhb_from_submission($this, preloader, InformationData, calenderData);
			});
 
			// Forminator Form Submission
			$(document).on('forminator:form:submit:success', function(event, response ) { 
				
				var InformationData = {};
				response.forEach(function(value, key){
					InformationData[key] = value;
				}); 
				
				
				tfhb_from_submission($this, preloader, InformationData, calenderData);
			});

			// Fluent Forms Form Submission
			$(document).on('fluentform_submission_success', function(event, response) {
				var data = new FormData(response.form[0]);
				var InformationData = {};
				data.forEach(function(value, key){
					InformationData[key] = value;
				}); 
				
				
				tfhb_from_submission($this, preloader, InformationData, calenderData);
			});

 
		
		});

		function tfhb_form_validation($this) {

			// has attr required
			var error = [];
			var error_message = '';
			var validation = true;
			// Form Validation 
			$this.find('.tfhb-single-form').each(function(){
			
				var $this_form_field = $(this);
				
				 
				if($this_form_field.find('input').length > 0 && $this_form_field.find('input').attr('required') == 'required' && $this_form_field.find('input').val() == ''){
					// get the input name
					var input_name = $this_form_field.find('input').attr('name');
					error.push(true);
					error_message += '<p>'+input_name+' field is required.</p>';
					
				}
				
			});
 
			// if in array any error true then show the error
			if(error.includes(true)){ 
				$this.find('.tfhb-notice').html(error_message);
				$this.find('.tfhb-notice').show();
				validation = false;
			}

			return validation;
		}

		// render_paypal_payment
		function tfhb_render_paypal_payment($this, responseData){
			var paypal_button = $this.find('.tfhb-paypal-button-container');
			var confirmation_template = responseData.confirmation_template;
			// hide the form .tfhb-confirmation-button
			$this.find('.tfhb-confirmation-button').hide(); 
			let currency = typeof tfhb_app_booking.general_settings.currency !== 'undefined' && tfhb_app_booking.general_settings.currency != '' ? tfhb_app_booking.general_settings.currency : 'USD';
		 
			// Render PayPal button into the container
			paypal.Buttons({
				// Create an order
				createOrder: function (data, actions) {  

					return actions.order.create({
						
						purchase_units: [{
							reference_id : responseData.data.attendee_data.id,
							description: responseData.data.meeting.title + ' - ' + responseData.data.meeting.duration + ' Minutes | ' + responseData.data.booking.start_time + ' - ' + responseData.data.booking.end_time + ' | ' + responseData.data.booking.meeting_dates,
							custom_id: responseData.data.attendee_data.id,
							amount: {
								currency_code: currency,
								value: responseData.data.meeting.meeting_price,// Set the transaction amount
							}
						}]
					});
				},
		
				// On successful payment
				onApprove: function (data, actions) {
					return actions.order.capture().then(function (details) {
  
						$.ajax({
							url: tfhb_app_booking.ajax_url, 
							type: 'POST',
							data: {
								nonce: tfhb_app_booking.nonce, 
								action: 'tfhb_meeting_paypal_payment_confirmation',
								payment_details: details,
								responseData: responseData,
							}, 
							success: function (response) {
								if(response.success){
									$this.find('.tfhb-meeting-card').html(''); 
									$this.find('.tfhb-meeting-card').append(confirmation_template); 
								}else{
									$this.find('.tfhb-notice').html(response.data.message);
									$this.find('.tfhb-notice').show();
								}
							},
							error: function (error) {
								console.log(error);
							}
						}); 
						 
					});
				},
		
				// If the buyer cancels the payment
				onCancel: function (data) {
					
				},
		
				// If there is an error
				onError: function (err) {
					console.error('An error occurred during the transaction', err);
					alert('Payment could not be completed due to an error');
				}
		
			}).render('.tfhb-paypal-button-container');
		}


		function tfhb_render_stripe_payment($this, responseData, stripe_public_key, meeting_title) {
			$this.find('.tfhb-confirmation-button').hide();
			let currency = typeof tfhb_app_booking.general_settings.currency !== 'undefined' && tfhb_app_booking.general_settings.currency != '' ? tfhb_app_booking.general_settings.currency : 'USD';
		 
			// Add Stripe Payment Button
			const stripeButtonContainer = $this.find('.tfhb-stripe-button-container');
			stripeButtonContainer.html("<a href='#' class='tfhb-stripe-payment-btn'>Pay With Stripe</a>");
			stripeButtonContainer.show();
		
			var confirmation_template = responseData.confirmation_template;
			// if tfhb_app_booking.general_settings.currency is not undefined then set the currency
			
			// Configure Stripe Checkout
			const handler = StripeCheckout.configure({
				key: stripe_public_key,
				locale: 'auto',
				token: function (token) {
					// Handle successful token generation
					const paymentData = {
						tokenId: token.id
					};

					// Send payment data to the server for processing
					jQuery.ajax({
						url: tfhb_app_booking.ajax_url,
						type: 'POST',
						data: {
							nonce: tfhb_app_booking.nonce, 
							action: 'tfhb_meeting_stripe_payment_confirmation',
							payment_data: paymentData,
							responseData: responseData,
						},
						beforeSend: function (data) {
							let submitPreLoader = `<span class="tfhb-submit-preloader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="200" height="200" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-dasharray="188.49555921538757 64.83185307179586" r="40" stroke-width="4" stroke="#ffffff" fill="none" cy="50" cx="50">
							<animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="0.49751243781094534s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
						</circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg><span>`
				
							$('.tfhb-stripe-payment-btn').append(submitPreLoader); 
							$('.tfhb-stripe-payment-btn').addClass('disabled');
						},
						success: function (presponse) {
						
							if(presponse.success){
								$this.find('.tfhb-meeting-card').html(''); 
								$this.find('.tfhb-meeting-card').append(confirmation_template); 
							}else{
								$this.find('.tfhb-notice').html(presponse.data.message);
								$this.find('.tfhb-notice').show();
							}
						},
						error: function (error) {
							console.error('Payment processing error:', error);
						}
					});
				}
			});
		
			// Attach click event to the Stripe Payment Button
			stripeButtonContainer.on('click', '.tfhb-stripe-payment-btn', function (e) {
				e.preventDefault();
				handler.open({
					name: meeting_title,
					amount: responseData.data.meeting.meeting_price * 100,
					currency: currency,
				});
			});
		
			// Close Stripe Checkout on page navigation
			window.addEventListener('popstate', function () {
				handler.close();
			});
		}

		function tfhb_from_submission($this, preloader, InformationData, calenderData){ 
			let submitPreLoader = `<span class="tfhb-submit-preloader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="200" height="200" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-dasharray="188.49555921538757 64.83185307179586" r="40" stroke-width="4" stroke="#ffffff" fill="none" cy="50" cx="50">
			<animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="0.49751243781094534s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
		  </circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg><span>`

			$this.find('.tfhb-booking-submit').append(submitPreLoader); 
			$this.find('.tfhb-booking-submit').attr('disabled', 'disabled');
			
 
			// Interval to show the preloader
			$this.find('.tfhb-notice').hide();
			$this.find('.tfhb-notice').html(''); 

		   var meeting_title = $this.find(".tfhb-meeting-details h2").text();
		   var meeting_id = $this.find("#meeting_id").val();
		   var host_id = $this.find("#host_id").val();
		   var meeting_duration = $this.find("#meeting_duration").val();
		   var meeting_dates = $this.find("#meeting_dates").val();
		   var meeting_time_start = $this.find("#meeting_time_start").val();
		   var meeting_time_end = $this.find("#meeting_time_end").val(); 
		   var booking_hash = $this.find("#booking_hash").val(); 
		   var action_type = $this.find("#action_type").val(); 
		   var meeting_price = $this.find("#meeting_price").val(); 
		   var recurring_maximum = $this.find("#recurring_maximum").val(); 
		   var attendee_time_zone = $this.find("#attendee_time_zone_"+calenderData.meeting_id).val(); 
		   var tfhb_time_format = $this.find("#tfhb_time_format").val(); 
		   var name = $this.find("#name").val(); 
		   var email = $this.find("#email").val(); 
		   var address = $this.find("#address").val(); 

		   var payment_type = $this.find("#payment_method").val();
		   var meeting_price = $this.find("#meeting_price").val();
		   var payment_amount = $this.find("#payment_amount").val();
		   var stripe_public_key = $this.find("#stpublic_key").val();
		   var paypal_public_key = $this.find("#paypal_public_key").val();
		   var payment_currency = $this.find("#payment_currency").val(); 

		//    Payment Status
		   var payment_status = calenderData.payment_status;


		   var data  = {
			action: 'tfhb_meeting_form_submit',
			nonce: tfhb_app_booking.nonce,
			meeting_id: meeting_id,
			host_id: host_id,
			meeting_dates: meeting_dates,
			meeting_duration: meeting_duration,
			meeting_time_start: meeting_time_start,
			meeting_time_end: meeting_time_end,
			booking_hash: booking_hash,
			action_type: action_type,
			recurring_maximum: recurring_maximum,
			attendee_time_zone: attendee_time_zone,
			tfhb_time_format: tfhb_time_format,
			payment_type: payment_type,
			meeting_price: meeting_price,
			payment_amount: payment_amount,
			stripe_public_key: stripe_public_key,
			payment_currency: payment_currency,
		}; 
		// Push object information data to data
		data = Object.assign(InformationData, data); 
			if(payment_status == 1 && ""==payment_type){
				//   5 seconds
				setTimeout(function(){ 
					$this.find('.tfhb-notice').append( __('Payment Method Required', 'hydra-booking'));
					$this.find('.tfhb-notice').show();
					$this.find('.tfhb-booking-submit .tfhb-submit-preloader').remove();  
				},  2000); // 2000 milliseconds = 2 seconds
				
			}

		   if(payment_status != 1 || ( payment_status == 1 && "woo_payment"==payment_type ) || ( payment_status == 1 && "paypal_payment"==payment_type ) || ( payment_status == 1 && "stripe_payment"==payment_type ) ){
			  
			 
			   $.ajax({
				   url: tfhb_app_booking.ajax_url, 
				   type: 'POST',
				   action: 'tfhb_meeting_form_submit',
				   data: data, 
				   success: function (response) {
					
					$this.find('.tfhb-booking-submit .tfhb-submit-preloader').remove();  
					//   Remove Disabled
					$this.find('.tfhb-booking-submit').removeAttr('disabled');
					   if(response.success){
								
							$this.find('.tfhb-booking-submit').remove('.tfhb-submit-preloader'); 
							//    Remove Disabled
						   $this.find('.tfhb-booking-submit').removeAttr('disabled');
						   
						   // Render Paypal Payment System
						   if(payment_status == 1 && "paypal_payment" == payment_type && response.data.data){ 
								tfhb_render_paypal_payment($this, response.data);
								return
							}
							if(payment_status == 1 && "stripe_payment" == payment_type && response.data.data){
								tfhb_render_stripe_payment($this, response.data, stripe_public_key, meeting_title);
								return
							}
						   if(response.data.redirect){
							   window.location.href = response.data.redirect;
							   return;

						   }else{ 
							   $this.find('.tfhb-meeting-card').html(''); 
							   $this.find('.tfhb-meeting-card').append(response.data.confirmation_template); 
							   if(response.data.action == 'rescheduled'){
								   $this.find('.tfhb-meeting-hostinfo').append(`
										   <div class="tfhb-notice " > 
										   <span>`+response.data.message+` </span>
									   </div>`
								   )
							   }

						   } 

					   }else{ 
						   $this.find('.tfhb-notice').append(response.data.message);
						   $this.find('.tfhb-notice').show();
						   return false;
					   }
				   },
				   error: function (error) {
					   console.log(error);
				   }
			   });
		   }

		}

		// Function to generate the tfhb-calendar
		function tfhb_date_manipulate($this, calenderData, year, month, date, months) {

			const day = $this.find(".tfhb-calendar-dates");
			const currdate = $this.find(".tfhb-calendar-current-date");
  
			let calender_data = calenderData;
			let availability = calender_data.availability;
			let date_slots = availability.date_slots;  
			let time_slots = availability.time_slots;   
			let availability_range = calender_data.availability_range;   
			let availability_range_type = calender_data.availability_range_type;   
			let availabilitys_range_start = calender_data.availability_range.start;   
			let availabilitys_range_end = calender_data.availability_range.end;   
			let dayNameText = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
			let DisableDays = [];
  
			// Get Disable Days 
				for (var i = 0; i < time_slots.length; i++) { 
					if(time_slots[i].status == false){
						DisableDays.push(time_slots[i].day);
					}
				}  
			
			 

			// Get the first day of the month
			let dayone = new Date(year, month, 1).getDay();

			// Get the last date of the month
			let lastdate = new Date(year, month + 1, 0).getDate();
	
			// Get the day of the last date of the month
			let dayend = new Date(year, month, lastdate).getDay();
	
			// Get the last date of the previous month
			let monthlastdate = new Date(year, month, 0).getDate();
	
			// Variable to store the generated tfhb-calendar HTML
			let lit = "";
	
			// If Time slots status is not true disbale that day 
			// Loop to add the last dates of the previous month
			for (let i = dayone; i > 0; i--) { 
				lit += `<li class="inactive">${tfhbTranslateNumber(monthlastdate - i + 1)}</li>`;
			}
	
			// Loop to add the dates of the current month
			for (let i = 1; i <= lastdate; i++) {
	
				// Check if the current date is today
				let isToday = i === date.getDate() && month === new Date().getMonth() && year === new Date().getFullYear() ? "active" : "";

				// Check if the current date has availability slots
				let dateKey = year + "-" + (month + 1).toString().padStart(2, '0') + "-" + i.toString().padStart(2, '0'); 
				let dateSlot = typeof date_slots !== 'undefined'  ? date_slots.find(slot => slot.date.match(dateKey) ) : "";
				let availabilityClass = typeof dateSlot !== 'undefined' && dateSlot !== '' && dateSlot.available == true   ? "inactive " : " ";
				let dataAvailable = "available";
				
				// Before today Days Disable 
				if(new Date() > new Date(year, month, i) && i !== date.getDate() ){
					availabilityClass = "inactive ";
					dataAvailable = "unavailable";
				}

				// if current date day is disable then disable that day.
				if(DisableDays.includes(dayNameText[new Date(year, month, i).getDay()])){
					availabilityClass = "inactive ";
					dataAvailable = "unavailable";
				}
				// if current date is out of range then disable that day.

				if(availability_range_type != 'indefinitely'){
					if(new Date(year, month, i) < new Date(availabilitys_range_start) || new Date(year, month, i) > new Date(availabilitys_range_end)){
						availabilityClass = "inactive ";
						dataAvailable = "unavailable";
						isToday = "";
					}
				}
				if('active' == isToday){
					availabilityClass = " "; 
				}
 
				lit += `<li data-date="${dateKey}" data-available="${dataAvailable}" class="${isToday} current ${availabilityClass}">${tfhbTranslateNumber(i)}</li>`;
		   }
	
			// Loop to add the first dates of the next month
			for (let i = dayend; i < 6; i++) {
				lit += `<li class="inactive">${tfhbTranslateNumber(i - dayend + 1)}</li>`;
			}
			// console.log(calendarLabels.months);
			currdate.text(`${tfhbTranslate(months[month])} ${tfhbTranslateNumber(year)}`);
			// currdate.text(`${months[month]} ${year}`);
	
			// update the HTML of the dates element 
			// with the generated tfhb-calendar
			day.html(lit);
		}

		// Function to generate the tfhb-calendar
		function tfhb_times_manipulate($this, current_date, calenderData, callback = null) {
			  

			var $this_li = $this.find('.tfhb-calendar-dates li.active');   
			// $this.find('.tfhb-meeting-times').css("display", "block").animate({left: "0", opacity: 1, width: 224}, 400 );
			$this.find('.tfhb-available-times').addClass('inactive');
			$this.find('.tfhb-calendar-body').addClass('inactive');
			// Get the first day of the month 
			var meeting_id = $this.find("input[name='meeting_id']").val();
			var selected_date = current_date; 
			// Selectedate date format Saturday, 11 April 
			
			var data_available = $this_li.attr('data-available'); 
			//  input radio data name tfhb_time_format
			var time_format = $this.find('input[name="tfhb_time_format"]:checked').val();  
			var time_zone = $this.find('.tfhb-time-zone-select').val();
			 
			$.ajax({
				url: tfhb_app_booking.ajax_url, 
				type: 'POST',
				data: {
					action: 'tfhb_already_booked_times',
					nonce: tfhb_app_booking.nonce,
					selected_date: selected_date,
					meeting_id: meeting_id,
					time_format: time_format,
					time_zone: time_zone,
				}, 
				success: function (response) {  
					if(response.success == true){  
 
						$this.find('.tfhb-calendar-body').removeClass('inactive');  
						let data = response.data; 
						calenderData.calander_available_time_slot = response.data;
						 
						if (typeof callback === "function") {
							callback(); // Execute the callback function
						}
					}
					if(response.success == false){ 
						$this.find('.tfhb-available-times').removeClass('inactive');
						$this.find('.tfhb-calendar-body').removeClass('inactive');
						$this.find('.tfhb-available-times').html('');
						$this.find('.tfhb-available-times').append('<ul></ul>');
						$this.find('.tfhb-available-times ul').append('<li > <p>'+response.data.message+'</p></li>');
						$this.find('.tfhb-meeting-times').css("display", "block").animate({left: "0", opacity: 1, width: 224}, 400 );
						
					}
				},
				error: function (error) {
					console.log(error);
				}
			});
		 
			

			 

		}

		// Generate Time Slots
		function generateTimeSlots(startTime, endTime, duration, meeting_interval, buffer_time_before, buffer_time_after, selected_date, time_format, time_zone) {
			var timeSlots = [];

			var skip_before_meeting_start = tfhb_app_booking.general_settings.allowed_reschedule_before_meeting_start; // exp 100 minutes
			// start date data format =   2024-05-04 
			var start = new Date(selected_date + " " + startTime);
			var end = new Date(selected_date + " " + endTime);
			var current = new Date(start);
			var before = new Date(start);
			var after = new Date(start);
			var diff = duration * 60000;
			var before_diff = buffer_time_before * 60000;
			var after_diff = buffer_time_after * 60000;
			var meeting_interval = meeting_interval * 60000;
			var total_diff = diff +before_diff + after_diff;
			while (current < end) {
				
				
				// new Date(current.getTime() + total_diff).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
				var start_time = formatTime(current, time_format, time_zone);
				var end_time = formatTime(new Date(current.getTime() + total_diff), time_format, time_zone);

			   // if current time is passed then skip skip_before_meeting_start
			  
			   if(new Date() > new Date(current.getTime() - skip_before_meeting_start * 60000)){
					current = new Date(current.getTime() + total_diff + meeting_interval);
					continue;
				}
				timeSlots.push({

					start: start_time, 
					// before_diff and after_diff need to use 
					end: end_time,

				});
				current = new Date(current.getTime() + total_diff + meeting_interval);
			} 
			return timeSlots;
		}

		function formatTime(date, timeFormat, timeZone) {
			var options = {
				hour: '2-digit',
				minute: '2-digit',
				hour12: timeFormat === '12',
				timeZone: timeZone
			};
			return date.toLocaleTimeString([], options);
		} 

    });

})(jQuery);

 
