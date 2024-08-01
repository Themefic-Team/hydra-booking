(function ($) {

    $(document).ready(function () {
	 
        /**
         * Time Zone Change
         * @author Jahid
         */
        $(document).on('click', '.tfhb-timezone-tabs ul li', function (e) {
            $('.tfhb-timezone-tabs ul li').removeClass('active');
            var $this = $(this);
            $this.addClass('active');
        });

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
     
	
		/**
		 * Time Select
		 * @author Sydur
		 * */ 
		$('.tfhb-meeting-box').each(function(){

			// Get Calender Id
			let $this = $(this),
			 	calenderId = $this.attr('data-calendar'),
				calenderData =  eval("tfhb_app_booking_" + calenderId),
				preloader =  `<div class="tfhb-preloader"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="192" height="192" xmlns:xlink="http://www.w3.org/1999/xlink"><g><path stroke="none" fill="#f62681" d="M8 50A42 42 0 0 0 92 50A42 45 0 0 1 8 50">
				<animateTransform values="0 50 51.5;360 50 51.5" keyTimes="0;1" repeatCount="indefinite" dur="1.3157894736842106s" type="rotate" attributeName="transform"></animateTransform>
			  </path><g></g></g><!-- [ldio] generated by https://loading.io --></svg></span></div>`;
			// Select 2 Time Zone 
			$this.find('.tfhb-time-zone-select').select2({
				dropdownCssClass: 'tfhb-select2-dropdown',
			}); 
			let date = new Date();
			let year = date.getFullYear();
			let month = date.getMonth();
		
			const tfhb_calendar_navs = $this.find(".tfhb-calendar-navigation span");
 

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


			tfhb_date_manipulate( $this, calenderData, year, month, date, months );


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
		
					// Call the tfhb_date_manipulate function to 
					// update the tfhb-calendar display
					tfhb_date_manipulate( $this, calenderData, year, month, date, months );
				});
			});

			// Select Date
			$(document).on('click', '.tfhb-calendar-dates li', function (e) {
				var $this_li = $(this);

				$this.find('.tfhb-meeting-card').append(preloader);

				setTimeout(function(){
					if($this_li.hasClass('inactive')){
						return false;
					}
					$this.find('.tfhb-calendar-dates li').removeClass('active');  
					$this_li.addClass('active');	
					
					// meeting ID
					var meeting_id = $this.find("input[name='meeting_id']").val();
					// Get the first day of the month
					tfhb_times_manipulate( $this, meeting_id, $this_li );
					
					$this.find('.tfhb-preloader').remove();
				}, 1000); // 2000 milliseconds = 2 seconds
 
			});

			$this.find('input[name="tfhb_time_format"], .tfhb-time-zone-select').on('change', function (e) {  
				var $this_li = $this.find('.tfhb-calendar-dates li.active');  
				// Get the first day of the month
				$this.find('.tfhb-meeting-card').append(preloader);
				var meeting_id = $this.find("input[name='meeting_id']").val();
				setTimeout(function(){
					// Your code here 
					tfhb_times_manipulate( $this, meeting_id, $this_li ); 
					$this.find('.tfhb-preloader').remove();
				}, 1000); // 2000 milliseconds = 2 seconds

				
			});


			/**
			* Time Select
			* @author Jahid
			*/
			// $this.find('input[name="tfhb_time_format"], .tfhb-time-zone-select').on('change', function (e) { 
			$(document).on('click', '.tfhb-available-times li .time', function (e) {
				$this.find('.tfhb-meeting-card').append(preloader);
				$this_time = $(this);

				setTimeout(function(){
					// Your code here 
					$('.tfhb-available-times li .next').remove(); 
					var selected_time_start = $this_time.attr('data-time-start');  
					var selected_time_end = $this_time.attr('data-time-end');
					$this.find("input[name='meeting_time_start']").val(selected_time_start);
					$this.find("input[name='meeting_time_end']").val(selected_time_end);
				
					$this_time.parent().append('<span class="next tfhb-flexbox tfhb-gap-8"> Next<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 10L14 10" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 4.16666L14.8333 9.99999L9 15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>');
				
					$this.find('.tfhb-preloader').remove();
				}, 1000); // 2000 milliseconds = 2 seconds

			});
 
 			$(document).on('click', '.tfhb-available-times li .next', function (e) {
				$this.find('.tfhb-meeting-card').append(preloader);

				setTimeout(function(){
					// Your code here 
					$this.find('.tfhb-calander-times').hide();
					$this.find('.tfhb-meeting-booking-form').show(); 

					$this.find('.tfhb-preloader').remove();
				}, 1000); // 2000 milliseconds = 2 seconds

				
			});

			$(document).on('click', '.tfhb-meeting-booking-form .tfhb-back-btn', function (e) {
				$this.find('.tfhb-meeting-card').append(preloader);

				setTimeout(function(){
					
					$this.find('.tfhb-calander-times').css('display', 'flex');
					$this.find('.tfhb-meeting-booking-form').hide();

					$this.find('.tfhb-preloader').remove();
				}, 1000); // 2000 milliseconds = 2 seconds

				
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
				console.log(data);
				var InformationData = {};
				data.forEach(function(value, key){
					InformationData[key] = value;
				}); 
				
				tfhb_from_submission($this, preloader, InformationData, calenderData);
			});
			// WPForms Form Submission
			// $(document).on('wpformsAjaxSubmitSuccess', function(event ) { 
			// 	var data = new FormData(event.target);
			// 	// get all wpforms[fields] data as object
				
			// 	var InformationData = {};
			// 	data.forEach(function(value, key){
			// 		InformationData[key] = value;
			// 	});
			// 	console.log(InformationData);
			// 	console.log(event);
			// });

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

			// gravity Forms Form Submission
			// $(document).on('gform_confirmation_loaded', function(event, response ) {  
			// 	event.preventDefault();
			// 	 console.log(response); 
			// });
		
		});


		function tfhb_from_submission($this, preloader, InformationData, calenderData){
			
			$this.find('.tfhb-notice').hide();
			$this.find('.tfhb-meeting-card').append(preloader);

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
		   var attendee_time_zone = $this.find("#attendee_time_zone").val(); 
		   var tfhb_time_format = $this.find("#tfhb_time_format").val(); 
		   var name = $this.find("#name").val(); 
		   var email = $this.find("#email").val(); 
		   var address = $this.find("#address").val(); 
		   var tfhb_booking_checkbox = $this.find("#tfhb_booking_checkbox").val(); 

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

		   if(payment_status != 1 || ( payment_status == 1 && "woo_payment"==payment_type)){
			  
			 
			   $.ajax({
				   url: tfhb_app_booking.ajax_url, 
				   type: 'POST',
				   action: 'tfhb_meeting_form_submit',
				   data: data,
				   // data: data,
				   // processData: false,
				   // contentType: false,
				   success: function (response) {
					   if(response.success){
					   
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
						   $this.find('.tfhb-preloader').remove();
					   }else{
						   $this.find('.tfhb-preloader').remove();
						   $this.find('.tfhb-notice').append(response.data.message);
						   $this.find('.tfhb-notice').show();
						   
					   }
				   },
				   error: function (error) {
					   console.log(error);
				   }
			   });
		   }

		   if("stripe_payment"==payment_type && payment_status == 1){
				
			   var handler = StripeCheckout.configure({
			   key: stripe_public_key, // your publisher key id
			   locale: 'auto',
			   token: function (token) {
					data = Object.assign(data, {
						tokenId: token.id,
					});
				   jQuery.ajax({
					   url: tfhb_app_booking.ajax_url,
					   method: 'POST',
					   data: data,
					   dataType: "json",
					   success: function( response ) {
						   if(response.success){
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
							   $this.find('.tfhb-preloader').remove();
						   }else{
							   $this.find('.tfhb-preloader').remove();
							   $this.find('.tfhb-notice').append(response.data.message);
							   $this.find('.tfhb-notice').show();
							   
						   }
					   }
				   })
			   }
			   });
			   
			   handler.open({
				   name: 'Hydra Booking',
				   description: '2 widgets',
				   amount: meeting_price * 100,
				   currency: payment_currency,
				   // closed: function () {
				   // 	location.reload();
					 // }
			   });
		   }

		   if("paypal_payment"==payment_type && payment_status == 1){
			   // 'AQyFCpzKPySeYI-n5FvZZ91zosqIEjguVDGrkUVFsW74o89Rj620Tol_4n-4JnaB_Fu8WojSvlSpzifa'
			   $this.find('.tfhb-confirmation-button').empty();
			   paypal.Button.render({
				   // Configure environment
				   env: 'sandbox',
				   client: {
					   sandbox: paypal_public_key,
				   },
				   // Customize button (optional)
				   locale: 'en_US',
				   style: {
					   size: 'small',
					   color: 'gold',
					   shape: 'pill',
				   },
				   // Set up a payment
				   payment: function (data, actions) {
					   return actions.payment.create({
						   transactions: [{
							   amount: {
								   total: meeting_price,
								   currency: payment_currency
							   }
						   }]
					 });
				   },
				   // Execute the payment
				   onAuthorize: function (data, actions) {
					   return actions.payment.execute()
					   .then(function () {
						   jQuery.ajax({
							   url: tfhb_app_booking.ajax_url,
							   method: 'POST',
							   data: {
								   action: 'tfhb_meeting_form_submit',
								   nonce: tfhb_app_booking.nonce,
								   meeting_id: $this.find("#meeting_id").val(),
								   host_id: $this.find("#host_id").val(),
								   meeting_dates: $this.find("#meeting_dates").val(),
								   meeting_time_start: $this.find("#meeting_time_start").val(),
								   meeting_time_end: $this.find("#meeting_time_end").val(),
								   name: $this.find("#name").val(),
								   email: $this.find("#email").val(),
								   address: $this.find("#address").val(),
								   paymentID: data.paymentID,
								   paymentToken: data.paymentToken,
								   payerID: data.payerID,
							   },
							   dataType: "json",
							   success: function( response ) {
								   if(response.success){
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
									   $this.find('.tfhb-preloader').remove();
								   }else{
									   $this.find('.tfhb-preloader').remove();
									   $this.find('.tfhb-notice').append(response.data.message);
									   $this.find('.tfhb-notice').show();
									   
								   }
							   }
						   })

					   });
				   }
			   }, $this.find('.tfhb-confirmation-button').get(0)); // Ensure the element is correctly passed to PayPal
			   
			   $this.find('.tfhb-preloader').remove();

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
				lit += `<li class="inactive">${monthlastdate - i + 1}</li>`;
			}
	
			// Loop to add the dates of the current month
			for (let i = 1; i <= lastdate; i++) {
	
				// Check if the current date is today
				let isToday = i === date.getDate() && month === new Date().getMonth() && year === new Date().getFullYear() ? "active" : "";

				// Check if the current date has availability slots
				let dateKey = year + "-" + (month + 1).toString().padStart(2, '0') + "-" + i.toString().padStart(2, '0'); 
				let dateSlot = typeof date_slots !== 'undefined'  ? date_slots.find(slot => slot.date.match(dateKey) ) : "";
				let availabilityClass = typeof dateSlot !== 'undefined' && dateSlot !== '' && dateSlot.available == true   ? "inactive " : " ";
				let dataAvailable = typeof dateSlot !== 'undefined' && dateSlot !== '' && dateSlot.available != true   ? "available" : "";
				
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
 
				lit += `<li data-date="${dateKey}" data-available="${dataAvailable}" class="${isToday} current ${availabilityClass}">${i}</li>`;
		   }
	
			// Loop to add the first dates of the next month
			for (let i = dayend; i < 6; i++) {
				lit += `<li class="inactive">${i - dayend + 1}</li>`;
			}
	
			// Update the text of the current date element 
			// with the formatted current month and year
			currdate.text(`${months[month]} ${year}`);
	
			// update the HTML of the dates element 
			// with the generated tfhb-calendar
			day.html(lit);
		}

		// Function to generate the tfhb-calendar
		function tfhb_times_manipulate($this, meeting_id, $this_li) {
			 
 
			var selected_date = $this_li.attr('data-date'); 
		
			var data_available = $this_li.attr('data-available'); 
			//  input radio data name tfhb_time_format
			var time_format = $this.find('input[name="tfhb_time_format"]:checked').val();  
			var time_zone = $this.find('.tfhb-time-zone-select').val();
			$this.find('.tfhb-meeting-times .tfhb-select-date').html(selected_date);
			
			$this.find("input[name='meeting_dates']").val(selected_date); 
			
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
					if(response.success){  

						// var already_booked_times = response.data;
						// console.log(response.data);
						let data = response.data;
						$this.find('.tfhb-available-times ul').html('');

						for (var i = 0; i < data.length; i++) { 
 
							// Remove 
							$this.find('.tfhb-available-times ul').append('<li class="tfhb-flexbox"> <span class="time" data-time-start="'+ data[i].start +'" data-time-end="'+ data[i].end +'">' + data[i].start + '</span> </li>');
						
						}

						// tfhb-meeting-times  with animation 
						$this.find('.tfhb-meeting-times').show();

					}
				},
				error: function (error) {
					console.log(error);
				}
			});
		 
			

			// let dayNameText = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']; 
			// // Get Selected Date day
			// let selected_date_day = new Date(selected_date).getDay(),
			// 	selected_date_day_name = dayNameText[selected_date_day],
			//  	calender_data = calenderData,
			//  	duration = calender_data.duration,
			//  	meeting_interval = calender_data.meeting_interval,
			//  	buffer_time_before = calender_data.buffer_time_before,
			//  	buffer_time_after = calender_data.buffer_time_after,
			//  	availability = calender_data.availability,
			// 	date_slots = availability.date_slots,
			// 	time_slots = availability.time_slots, 
			// 	selected_date_slots = time_slots.find( slot => slot.day == selected_date_day_name ),
			// 	times = selected_date_slots.times, //array  
			// 	timesData = []; //array 
  
			
			// if(data_available == 'available'){
			// 	// Generate time slots  form date_slots
				
			// 	for (var i = 0; i < date_slots.length; i++) {
			// 		var date_slot = date_slots[i]; 
			// 		// if In array current day 
			// 		if(date_slot.status == false){
			// 			continue;
			// 		}

			// 		//  has current date in this string 2024-05-17, 2024-06-29, 2024-06-28, 2024-06-26
			// 		$dates = date_slot.date.split(',');
			// 		if(!$dates.includes(selected_date)){ 
			// 			continue;
			// 		} 
			// 		for (var i = 0; i < date_slot.times.length; i++) {
						

			// 			var startTime = date_slot.times[i].start;
			// 			var endTime = date_slot.times[i].end;
			// 			var generatedSlots = generateTimeSlots(startTime, endTime, duration, meeting_interval, buffer_time_before, buffer_time_after, selected_date, time_format, time_zone);
			// 			// merge with timesData 
			// 			// Current time 
			// 			timesData = timesData.concat(generatedSlots);
			// 		} 
			// 	}

			// }else{
			// 	// Generate time slots
			// 	for (var i = 0; i < times.length; i++) {
			// 		var startTime = times[i].start;
			// 		var endTime = times[i].end;
			// 		var generatedSlots = generateTimeSlots(startTime, endTime, duration, meeting_interval, buffer_time_before, buffer_time_after, selected_date, time_format, time_zone);
			// 		// merge with timesData 
			// 		timesData = timesData.concat(generatedSlots);
			// 	} 
			// }
			
		 
			// $.ajax({
			// 	url: tfhb_app_booking.ajax_url, 
			// 	type: 'POST',
			// 	data: {
			// 		action: 'tfhb_already_booked_times',
			// 		nonce: tfhb_app_booking.nonce,
			// 		selected_date: selected_date,
			// 		time_format: time_format,
			// 		time_zone: time_zone,
			// 	}, 
			// 	success: function (response) {  
			// 		if(response.success){  

			// 			var already_booked_times = response.data;
			// 			console.log(already_booked_times);
 
			// 			$this.find('.tfhb-available-times ul').html('');

			// 			for (var i = 0; i < timesData.length; i++) {
 
			// 				// if 24 hours format then convert to 12 hours format
			// 				var already_booked = already_booked_times.find( slot => slot.start_time == timesData[i].start && slot.end_time == timesData[i].end );
						 
			// 				if(already_booked){ 
								
			// 					// Remove
			// 					continue;
			// 				}
 
			// 				// Remove 
			// 				$this.find('.tfhb-available-times ul').append('<li class="tfhb-flexbox"> <span class="time" data-time-start="'+ timesData[i].start +'" data-time-end="'+ timesData[i].end +'">' + timesData[i].start + '</span> </li>');
						
			// 			}

			// 			// tfhb-meeting-times  with animation 
			// 			$this.find('.tfhb-meeting-times').show();

			// 		}
			// 	},
			// 	error: function (error) {
			// 		console.log(error);
			// 	}
			// });
			

			


			// loop times and add to html li
			 

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

// let date = new Date();
// let year = date.getFullYear();
// let month = date.getMonth();

// const day = document.querySelector(".tfhb-calendar-dates");
// const currdate = document.querySelector(".tfhb-calendar-current-date");
// const tfhb_calendar_navs = document.querySelectorAll(".tfhb-calendar-navigation span");

// // Array of month names
// const months = [
// 	"January",
// 	"February",
// 	"March",
// 	"April",
// 	"May",
// 	"June",
// 	"July",
// 	"August",
// 	"September",
// 	"October",
// 	"November",
// 	"December"
// ];

// // Function to generate the tfhb-calendar
// const tfhb_date_manipulate = () => {

// 	// Get the first day of the month
// 	let dayone = new Date(year, month, 1).getDay();

// 	// Get the last date of the month
// 	let lastdate = new Date(year, month + 1, 0).getDate();

// 	// Get the day of the last date of the month
// 	let dayend = new Date(year, month, lastdate).getDay();

// 	// Get the last date of the previous month
// 	let monthlastdate = new Date(year, month, 0).getDate();

// 	// Variable to store the generated tfhb-calendar HTML
// 	let lit = "";

// 	// Loop to add the last dates of the previous month
// 	for (let i = dayone; i > 0; i--) {
// 		lit +=
// 			`<li class="inactive">${monthlastdate - i + 1}</li>`;
// 	}

// 	// Loop to add the dates of the current month
// 	for (let i = 1; i <= lastdate; i++) {

// 		// Check if the current date is today
// 		let isToday = i === date.getDate()
// 			&& month === new Date().getMonth()
// 			&& year === new Date().getFullYear()
// 			? "active"
// 			: "";
// 		lit += `<li data-date="${i} ${months[month]}, ${year}" class="${isToday} current">${i}</li>`;
// 	}

// 	// Loop to add the first dates of the next month
// 	for (let i = dayend; i < 6; i++) {
// 		lit += `<li class="inactive">${i - dayend + 1}</li>`
// 	}

// 	// Update the text of the current date element 
// 	// with the formatted current month and year
// 	currdate.innerText = `${months[month]} ${year}`;

// 	// update the HTML of the dates element 
// 	// with the generated tfhb-calendar
// 	day.innerHTML = lit;
// }

// tfhb_date_manipulate();

// // Attach a click event listener to each icon
// tfhb_calendar_navs.forEach(icon => {

// 	// When an icon is clicked
// 	icon.addEventListener("click", () => {
		
// 		// Check if the icon is "tfhb-calendar-prev"
// 		// or "tfhb-calendar-next"
// 		month = icon.id === "tfhb-calendar-prev" ? month - 1 : month + 1;

// 		// Check if the month is out of range
// 		if (month < 0 || month > 11) {

// 			// Set the date to the first day of the 
// 			// month with the new year
// 			date = new Date(year, month, new Date().getDate());

// 			// Set the year to the new year
// 			year = date.getFullYear();

// 			// Set the month to the new month
// 			month = date.getMonth();
// 		}

// 		else {

// 			// Set the date to the current date
// 			date = new Date();
// 		}

// 		// Call the tfhb_date_manipulate function to 
// 		// update the tfhb-calendar display
// 		tfhb_date_manipulate();
// 	});
// });
