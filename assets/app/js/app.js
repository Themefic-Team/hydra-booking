(function($){const{__,_x}=wp.i18n;$(document).ready(function(){const tfhb_local_timeZone=Intl.DateTimeFormat().resolvedOptions().timeZone;$(document).on("click",".tfhb-timezone-tabs ul li",function(t){$(".tfhb-timezone-tabs ul li").removeClass("active");var e=$(this);e.addClass("active")}),$(".tfhb-meeting-box").each(function(){let $this=$(this),calenderId=$this.attr("data-calendar"),calenderData=eval("tfhb_app_booking_"+calenderId),preloader=`<div class="tfhb-preloader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="200" height="200" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-linecap="round" fill="none" stroke-dasharray="51.83627878423159 51.83627878423159" stroke="#2e6b38" stroke-width="4" r="33" cy="50" cx="50">
				<animateTransform values="0 50 50;360 50 50" keyTimes="0;1" dur="1s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
			  </circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg></span></div>`,date=new Date,year=date.getFullYear(),month=date.getMonth();const tfhb_calendar_navs=$this.find(".tfhb-calendar-navigation span"),calendarLabels={months:{January:__("January","hydra-booking"),February:__("February","hydra-booking"),March:__("March","hydra-booking"),April:__("April","hydra-booking"),May:__("May","hydra-booking"),June:__("June","hydra-booking"),July:__("July","hydra-booking"),August:__("August","hydra-booking"),September:__("September","hydra-booking"),October:__("October","hydra-booking"),November:__("November","hydra-booking"),December:__("December","hydra-booking")}},months=["January","February","March","April","May","June","July","August","September","October","November","December"];tfhb_date_manipulate($this,calenderData,year,month,date,months,calendarLabels),tfhb_calendar_navs.each(function(){$(this).on("click",function(){month=$(this).attr("id")==="tfhb-calendar-prev"?month-1:month+1,month<0||month>11?(date=new Date(year,month,new Date().getDate()),year=date.getFullYear(),month=date.getMonth()):date=new Date,$this.find(".tfhb-meeting-times").animate({left:"-50%",width:0,opacity:0},300,function(){$(this).css("display","none")}),tfhb_date_manipulate($this,calenderData,year,month,date,months,calendarLabels);var t=new Date(year,month,2).toISOString().split("T")[0];setTimeout(function(){tfhb_times_manipulate($this,t,calenderData)},1e3)})}),$($this).on("click",".tfhb-calendar-dates li",function(t){var e=$(this);if(e.hasClass("inactive"))return!1;$this.find(".tfhb-meeting-times").css("display","block").animate({left:"0%",opacity:1,width:224},400),$this.find(".tfhb-available-times").removeClass("inactive"),$this.find(".tfhb-available-times").html(""),$this.find(".tfhb-available-times").append("<ul></ul>");var i=e.attr("data-date");$this.find("#meeting_dates").val(i),selected_date_format=new Date(i).toLocaleDateString("en-US",{weekday:"long",month:"long",day:"numeric"}),$this.find(".tfhb-meeting-times .tfhb-select-date").html(selected_date_format);var a=calenderData.calander_available_time_slot[i];if(a.length>0)for(var n=0;n<a.length;n++){var d="";a[n].remaining!=null&&(d='<span class="tfhb-time-slot-remaining tfhb-flexbox"><span></span>'+a[n].remaining+" seats left</span>"),$this.find(".tfhb-available-times ul").append('<li class="tfhb-flexbox"> <div class="time" data-time-start="'+a[n].start+'" data-time-end="'+a[n].end+'">'+a[n].start+d+" </div> </li>")}else $this.find(".tfhb-available-times ul").append('<li class="tfhb-flexbox"> <div>No time slot available in this date ! Try another date.</span> </div>');$this.find(".tfhb-calendar-dates li").removeClass("active"),e.addClass("active")}),$this.find("#attendee_time_zone_"+calenderId).on("change",function(t){$this.find(".tfhb-meeting-times").animate({left:"-50%",width:0,opacity:0},300,function(){$(this).css("display","none")});var e=new Date(year,month,2).toISOString().split("T")[0];tfhb_times_manipulate($this,e,calenderData)}),$this.find('input[name="tfhb_time_format"]').on("change",function(t){var e=new Date(year,month,2).toISOString().split("T")[0];tfhb_times_manipulate($this,e,calenderData,function(){$this.find(".tfhb-calendar-dates li.active").trigger("click")})});const $timezoneSelect=$this.find("#attendee_time_zone_"+calenderId);$timezoneSelect.length&&($timezoneSelect.select2({dropdownCssClass:"tfhb-select2-dropdown"}),$timezoneSelect.val(tfhb_local_timeZone).trigger("change")),$($this).on("click",".tfhb-available-times li .time",function(t){$this_time=$(this),$(".tfhb-available-times li .time").removeClass("active"),$(".tfhb-available-times li .next").remove();var e=$this_time.attr("data-time-start"),i=$this_time.attr("data-time-end");$this.find("input[name='meeting_time_start']").val(e),$this.find("input[name='meeting_time_end']").val(i),$this_time.parent().append('<span class="next tfhb-flexbox tfhb-gap-8"> Next<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 10L14 10" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 4.16666L14.8333 9.99999L9 15.8333" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>'),$this_time.addClass("active")}),$($this).on("click",".tfhb-available-times li .next",function(t){var e=$this.find("#attendee_time_zone_"+calenderId).val(),i=$this.find("#meeting_dates").val(),a=$this.find("#meeting_time_start").val();$this.find("#meeting_time_end").val();var n=`<li class="tfhb-flexbox tfhb_time_zone_info tfhb-gap-8">
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
							`+e+"("+a+`)
						</div>
					</li>`,d=new Date(i),s={weekday:"long",year:"numeric",month:"long",day:"numeric"},l=`<li class="tfhb-flexbox tfhb_date_time_info tfhb-gap-8">
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
							`+d.toLocaleDateString("en-US",s)+`
						</div>
					</li>`;$this.find(".tfhb-meeting-details ul").append(n),$this.find(".tfhb-meeting-details ul").append(l),$this.find(".tfhb-timezone").hide(),$this.find(".tfhb-meeting-times").animate({left:"-50%",width:0,opacity:0},300,function(){$(this).css("display","none")}),$this.find(".tfhb-calander-times").animate({left:"-5%",opacity:0},400,function(){$(this).css("display","none"),$this.find(".tfhb-meeting-booking-form").css("display","block").animate({left:"0",opacity:1,width:536},300)})}),$("span.tfhb-see-description").on("click",function(){$(".tfhb-short-description").slideUp(),$(".tfhb-full-description").slideDown()}),$("span.tfhb-see-less-description").on("click",function(){$(".tfhb-full-description").slideUp(),$(".tfhb-short-description").slideDown()}),$($this).on("click",".tfhb-meeting-booking-form .tfhb-back-btn",function(t){$this.find(".tfhb-meeting-booking-form").animate({left:"-5%",opacity:0},300,function(){$(this).css("display","none"),$this.find(".tfhb-timezone").show(),$this.find(".tfhb-calander-times").css("display","flex").animate({left:"0",opacity:1},200,function(){}),$this.find(".tfhb-meeting-times").css("display","block").animate({left:"0",width:224},400,function(){$(this).css("opacity","1"),$this.find(".tfhb-meeting-details ul .tfhb_time_zone_info").remove(),$this.find(".tfhb-meeting-details ul .tfhb_date_time_info").remove()})})}),$this.find(".tfhb-meeting-form.ajax-submit").on("submit",function(t){t.preventDefault();var e=new FormData(this),i={};e.forEach(function(a,n){i[n]=a}),tfhb_from_submission($this,preloader,i,calenderData)}),document.addEventListener("wpcf7mailsent",function(t){var e=t.detail.formData,i={};e.forEach(function(a,n){i[n]=a}),tfhb_from_submission($this,preloader,i,calenderData)}),$(document).on("forminator:form:submit:success",function(t,e){var i={};e.forEach(function(a,n){i[n]=a}),tfhb_from_submission($this,preloader,i,calenderData)}),$(document).on("fluentform_submission_success",function(t,e){var i=new FormData(e.form[0]),a={};i.forEach(function(n,d){a[d]=n}),tfhb_from_submission($this,preloader,a,calenderData)})});function tfhb_render_paypal_payment(t,e){t.find(".tfhb-paypal-button-container");var i=e.confirmation_template;t.find(".tfhb-confirmation-button").hide();let a=typeof tfhb_app_booking.general_settings.currency<"u"&&tfhb_app_booking.general_settings.currency!=""?tfhb_app_booking.general_settings.currency:"USD";paypal.Buttons({createOrder:function(n,d){return d.order.create({purchase_units:[{reference_id:e.data.attendee_data.id,description:e.data.meeting.title+" - "+e.data.meeting.duration+" Minutes | "+e.data.booking.start_time+" - "+e.data.booking.end_time+" | "+e.data.booking.meeting_dates,custom_id:e.data.attendee_data.id,amount:{currency_code:a,value:e.data.meeting.meeting_price}}]})},onApprove:function(n,d){return d.order.capture().then(function(s){$.ajax({url:tfhb_app_booking.ajax_url,type:"POST",data:{nonce:tfhb_app_booking.nonce,action:"tfhb_meeting_paypal_payment_confirmation",payment_details:s,responseData:e},success:function(l){l.success?(t.find(".tfhb-meeting-card").html(""),t.find(".tfhb-meeting-card").append(i)):(t.find(".tfhb-notice").html(l.data.message),t.find(".tfhb-notice").show())},error:function(l){console.log(l)}})})},onCancel:function(n){},onError:function(n){console.error("An error occurred during the transaction",n),alert("Payment could not be completed due to an error")}}).render(".tfhb-paypal-button-container")}function tfhb_render_stripe_payment(t,e,i,a){t.find(".tfhb-confirmation-button").hide();const n=t.find(".tfhb-stripe-button-container");n.html("<a href='#' class='tfhb-stripe-payment-btn'>Pay With Stripe</a>"),n.show();var d=e.confirmation_template;const s=StripeCheckout.configure({key:i,locale:"auto",token:function(l){const h={tokenId:l.id};jQuery.ajax({url:tfhb_app_booking.ajax_url,type:"POST",data:{nonce:tfhb_app_booking.nonce,action:"tfhb_meeting_stripe_payment_confirmation",payment_data:h,responseData:e},beforeSend:function(r){$(".tfhb-stripe-payment-btn").append(`<span class="tfhb-submit-preloader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="200" height="200" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-dasharray="188.49555921538757 64.83185307179586" r="40" stroke-width="4" stroke="#ffffff" fill="none" cy="50" cx="50">
							<animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="0.49751243781094534s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
						</circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg><span>`),$(".tfhb-stripe-payment-btn").addClass("disabled")},success:function(r){r.success?(t.find(".tfhb-meeting-card").html(""),t.find(".tfhb-meeting-card").append(d)):(t.find(".tfhb-notice").html(r.data.message),t.find(".tfhb-notice").show())},error:function(r){console.error("Payment processing error:",r)}})}});n.on("click",".tfhb-stripe-payment-btn",function(l){l.preventDefault(),s.open({name:a,amount:e.data.meeting.meeting_price*100,currency})}),window.addEventListener("popstate",function(){s.close()})}function tfhb_from_submission(t,e,i,a){t.find(".tfhb-booking-submit").append(`<span class="tfhb-submit-preloader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="shape-rendering: auto; display: block; background: transparent;" width="200" height="200" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle stroke-dasharray="188.49555921538757 64.83185307179586" r="40" stroke-width="4" stroke="#ffffff" fill="none" cy="50" cx="50">
			<animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="0.49751243781094534s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
		  </circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg><span>`),t.find(".tfhb-booking-submit").attr("disabled","disabled"),t.find(".tfhb-notice").hide(),t.find(".tfhb-notice").html("");var d=t.find(".tfhb-meeting-details h2").text(),s=t.find("#meeting_id").val(),l=t.find("#host_id").val(),h=t.find("#meeting_duration").val(),r=t.find("#meeting_dates").val(),_=t.find("#meeting_time_start").val(),v=t.find("#meeting_time_end").val(),u=t.find("#booking_hash").val(),w=t.find("#action_type").val(),b=t.find("#meeting_price").val(),x=t.find("#recurring_maximum").val(),D=t.find("#attendee_time_zone_"+a.meeting_id).val(),C=t.find("#tfhb_time_format").val();t.find("#name").val(),t.find("#email").val(),t.find("#address").val();var f=t.find("#payment_method").val(),b=t.find("#meeting_price").val(),S=t.find("#payment_amount").val(),p=t.find("#stpublic_key").val();t.find("#paypal_public_key").val();var y=t.find("#payment_currency").val(),c=a.payment_status,m={action:"tfhb_meeting_form_submit",nonce:tfhb_app_booking.nonce,meeting_id:s,host_id:l,meeting_dates:r,meeting_duration:h,meeting_time_start:_,meeting_time_end:v,booking_hash:u,action_type:w,recurring_maximum:x,attendee_time_zone:D,tfhb_time_format:C,payment_type:f,meeting_price:b,payment_amount:S,stripe_public_key:p,payment_currency:y};m=Object.assign(i,m),c==1&&f==""&&setTimeout(function(){t.find(".tfhb-notice").append(__("Payment Method Required","hydra-booking")),t.find(".tfhb-notice").show(),t.find(".tfhb-booking-submit .tfhb-submit-preloader").remove()},2e3),(c!=1||c==1&&f=="woo_payment"||c==1&&f=="paypal_payment"||c==1&&f=="stripe_payment")&&$.ajax({url:tfhb_app_booking.ajax_url,type:"POST",action:"tfhb_meeting_form_submit",data:m,success:function(o){if(t.find(".tfhb-booking-submit .tfhb-submit-preloader").remove(),t.find(".tfhb-booking-submit").removeAttr("disabled"),o.success){if(t.find(".tfhb-booking-submit").remove(".tfhb-submit-preloader"),t.find(".tfhb-booking-submit").removeAttr("disabled"),c==1&&f=="paypal_payment"&&o.data.data){tfhb_render_paypal_payment(t,o.data);return}if(c==1&&f=="stripe_payment"&&o.data.data){tfhb_render_stripe_payment(t,o.data,p,d);return}if(o.data.redirect){window.location.href=o.data.redirect;return}else t.find(".tfhb-meeting-card").html(""),t.find(".tfhb-meeting-card").append(o.data.confirmation_template),o.data.action=="rescheduled"&&t.find(".tfhb-meeting-hostinfo").append(`
										   <div class="tfhb-notice " > 
										   <span>`+o.data.message+` </span>
									   </div>`)}else return t.find(".tfhb-notice").append(o.data.message),t.find(".tfhb-notice").show(),!1},error:function(o){console.log(o)}})}function tfhb_date_manipulate(t,e,i,a,n,d,s){const l=t.find(".tfhb-calendar-dates"),h=t.find(".tfhb-calendar-current-date");let r=e,_=r.availability,v=_.date_slots,u=_.time_slots;r.availability_range;let w=r.availability_range_type,x=r.availability_range.start,D=r.availability_range.end,C=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],f=[];for(var b=0;b<u.length;b++)u[b].status==!1&&f.push(u[b].day);let S=new Date(i,a,1).getDay(),p=new Date(i,a+1,0).getDate(),y=new Date(i,a,p).getDay(),c=new Date(i,a,0).getDate(),m="";for(let o=S;o>0;o--)m+=`<li class="inactive">${c-o+1}</li>`;for(let o=1;o<=p;o++){let M=o===n.getDate()&&a===new Date().getMonth()&&i===new Date().getFullYear()?"active":"",j=i+"-"+(a+1).toString().padStart(2,"0")+"-"+o.toString().padStart(2,"0"),T=typeof v<"u"?v.find(z=>z.date.match(j)):"",g=typeof T<"u"&&T!==""&&T.available==!0?"inactive ":" ",k="available";new Date>new Date(i,a,o)&&o!==n.getDate()&&(g="inactive ",k="unavailable"),f.includes(C[new Date(i,a,o).getDay()])&&(g="inactive ",k="unavailable"),w!="indefinitely"&&(new Date(i,a,o)<new Date(x)||new Date(i,a,o)>new Date(D))&&(g="inactive ",k="unavailable",M=""),M=="active"&&(g=" "),m+=`<li data-date="${j}" data-available="${k}" class="${M} current ${g}">${o}</li>`}for(let o=y;o<6;o++)m+=`<li class="inactive">${o-y+1}</li>`;h.text(`${s.months[d[a]]} ${i}`),l.html(m)}function tfhb_times_manipulate(t,e,i,a=null){var n=t.find(".tfhb-calendar-dates li.active");t.find(".tfhb-available-times").addClass("inactive"),t.find(".tfhb-calendar-body").addClass("inactive");var d=t.find("input[name='meeting_id']").val(),s=e;n.attr("data-available");var l=t.find('input[name="tfhb_time_format"]:checked').val(),h=t.find(".tfhb-time-zone-select").val();$.ajax({url:tfhb_app_booking.ajax_url,type:"POST",data:{action:"tfhb_already_booked_times",nonce:tfhb_app_booking.nonce,selected_date:s,meeting_id:d,time_format:l,time_zone:h},success:function(r){r.success==!0&&(t.find(".tfhb-calendar-body").removeClass("inactive"),r.data,i.calander_available_time_slot=r.data,typeof a=="function"&&a()),r.success==!1&&(t.find(".tfhb-available-times").removeClass("inactive"),t.find(".tfhb-calendar-body").removeClass("inactive"),t.find(".tfhb-available-times").html(""),t.find(".tfhb-available-times").append("<ul></ul>"),t.find(".tfhb-available-times ul").append("<li > <p>"+r.data.message+"</p></li>"),t.find(".tfhb-meeting-times").css("display","block").animate({left:"0",opacity:1,width:224},400))},error:function(r){console.log(r)}})}})})(jQuery);
