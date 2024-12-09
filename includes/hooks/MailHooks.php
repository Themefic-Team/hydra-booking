<?php
namespace HydraBooking\Hooks;

// Use
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;


class MailHooks {
	// Approved
	// Pending
	// Re-schedule
	// Canceled
	public function __construct() {
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'pushBookingToConfirmed' ), 20, 1 ); 
		add_action( 'hydra_booking/after_booking_pending', array( $this, 'pushBookingToPending' ), 10, 1 );
		add_action( 'hydra_booking/after_booking_canceled', array( $this, 'pushBookingToCanceled' ), 10, 1 );
		add_action( 'hydra_booking/after_booking_schedule', array( $this, 'pushBookingToscheduled' ), 10, 1 );
		add_action( 'hydra_booking/send_booking_reminder', array( $this, 'send_booking_reminder' ), 10, 1 );
	}

	// Get Meeting Data
	public function getMeetingData( $meeting_id ) {
		$meeting      = new Meeting();
		$meeting_data = $meeting->get( $meeting_id );
		return get_post_meta( $meeting_data->post_id, '__tfhb_meeting_opt', true );
	}

	// Get Host Data
	public function getHostData( $host_id ) {
		$host      = new Host();
		$host_data = $host->getHostById(  $host_id );
		return $host_data;
	}

	// If booking Status is Complted
	public function pushBookingToConfirmed( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );  
		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host Confirmation Email, If Settings Enable for Host Confirmation
			if ( ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['status'] ) ) {
				
				
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['form'] ) ? $_tfhb_notification_settings['host']['booking_confirmation']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['subject'] ) ? $_tfhb_notification_settings['host']['booking_confirmation']['subject'] : 'Booking Confirmation';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );
				
				
				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['body'] ) ? $_tfhb_notification_settings['host']['booking_confirmation']['body'] : ''; 

				
				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );
			
				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// tfhb_print_r($body);
				// Host Email
				$mailto = ! empty( $hostData->email ) ? $hostData->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				
				Mailer::send( $mailto, $subject, $body, $headers );
			}

			// Attendee Confirmation Email, If Settings Enable for Attendee Confirmation
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['status'] ) ) {
				
				
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['subject'] : 'Booking Confirmation';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );
				
				
				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['body'] : ''; 

				
				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );
			
				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// tfhb_print_r($body);
				// Host Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				
				Mailer::send( $mailto, $subject, $body, $headers );
			}
		}
	}


	// If booking Status is Pending
	public function pushBookingToPending( $attendees ) {


		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );  

		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host Pending Email, If Settings Enable for Host Pending
			if ( ! empty( $_tfhb_notification_settings['host']['booking_pending']['status'] ) ) {
				
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_pending']['form'] ) ? $_tfhb_notification_settings['host']['booking_pending']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_pending']['subject'] ) ? $_tfhb_notification_settings['host']['booking_pending']['subject'] : 'Booking Pending';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );
				
				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_pending']['body'] ) ? $_tfhb_notification_settings['host']['booking_pending']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->email ) ? $hostData->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}

			// Attendee Pending Email, If Settings Enable for Attendee Pending
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['subject'] : 'Booking Pending';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );


				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}
		}
	}

	// If booking Status is Cancel
	public function pushBookingToCanceled( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );

		
		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host Canceled Email, If Settings Enable for Host Canceled
			if ( ! empty( $_tfhb_notification_settings['host']['booking_cancel']['status'] ) ) {

				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_cancel']['form'] ) ? $_tfhb_notification_settings['host']['booking_cancel']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_cancel']['subject'] ) ? $_tfhb_notification_settings['host']['booking_cancel']['subject'] : 'Booking Canceled';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_cancel']['body'] ) ? $_tfhb_notification_settings['host']['booking_cancel']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->email ) ? $hostData->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);
				Mailer::send( $mailto, $subject, $body, $headers );
			}

			// Attendee Canceled Email, If Settings Enable for Attendee Canceled
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['subject'] : 'Booking Canceled';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}
		}
	}

	// If booking Status is ReSchedule
	public function pushBookingToscheduled( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );

		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host ReSchedule Email, If Settings Enable for Host ReSchedule
			if ( ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['form'] ) ? $_tfhb_notification_settings['host']['booking_reschedule']['form'] : get_option( 'admin_email' );
 
				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['subject'] ) ? $_tfhb_notification_settings['host']['booking_reschedule']['subject'] : 'Booking ReSchedule';
				
				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );


				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['body'] ) ? $_tfhb_notification_settings['host']['booking_reschedule']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->host_email ) ? $hostData->host_email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}

			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['subject'] : 'Booking ReSchedule';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}
		}
	}


		// If booking Status is ReSchedule
	public function send_booking_reminder( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );

		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host ReSchedule Email, If Settings Enable for Host ReSchedule
			if ( ! empty( $_tfhb_notification_settings['host']['booking_reminder']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_reminder']['form'] ) ? $_tfhb_notification_settings['host']['booking_reminder']['form'] : get_option( 'admin_email' );
	
				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_reminder']['subject'] ) ? $_tfhb_notification_settings['host']['booking_reminder']['subject'] : 'Booking ReSchedule';
				
				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );


				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_reminder']['body'] ) ? $_tfhb_notification_settings['host']['booking_reminder']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );
				
				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->host_email ) ? $hostData->host_email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}

			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_reminder']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_reminder']['subject'] : 'Booking ReSchedule';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_reminder']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );
			}
		}
	}

	/**
	 * email body open markup
	 */
	public function email_body_open() {
		// email body open
		$email_body_open = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="preconnect" href="https://fonts.googleapis.com"></head><body>';
		return $email_body_open;
	}

	/**
	 * email body close markup
	 */
	public function email_body_close() {
		// email body close
		$email_body_close = '</body></html>';
		return $email_body_close;
	}

	/**
	 * Replace all available mail tags
	 */
	public function replace_mail_tags( $template, $attendee_id ) {
		
		$Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=',$attendee_id),
			),
			1,
			'DESC'
		); 
		  
		// $tfhb_booking_table = $wpdb->prefix . 'tfhb_bookings';
		// $meeting_table      = $wpdb->prefix . 'tfhb_meetings';
		// $host_table         = $wpdb->prefix . 'tfhb_hosts';

		// $sql          = "
        //     SELECT $tfhb_booking_table.attendee_name, 
        //     $tfhb_booking_table.email AS attendee_email,
        //     $tfhb_booking_table.attendee_time_zone AS attendee_time_zone,
        //     $tfhb_booking_table.meeting_locations AS booking_locations,
        //     $tfhb_booking_table.meeting_dates,
        //     $tfhb_booking_table.others_info,
        //     $tfhb_booking_table.hash,
        //     $tfhb_booking_table.reason,
        //     $tfhb_booking_table.start_time,
        //     $tfhb_booking_table.end_time,
        //     $tfhb_booking_table.duration AS meeting_duration,
        //     $host_table.email AS host_email,
        //     $host_table.first_name AS host_first_name,
        //     $host_table.last_name AS host_last_name,
        //     $host_table.time_zone AS host_time_zone,
        //     $meeting_table.title AS meeting_title,
        //     $meeting_table.attendee_can_cancel AS attendee_can_cancel,
        //     $meeting_table.attendee_can_reschedule AS attendee_can_reschedule,
        //     $meeting_table.meeting_locations AS meeting_location
        //     FROM $tfhb_booking_table
        //     INNER JOIN $host_table ON $tfhb_booking_table.host_id = $host_table.id
        //     INNER JOIN $meeting_table ON $tfhb_booking_table.meeting_id = $meeting_table.id
        //     WHERE $tfhb_booking_table.id = %d
        // ";
		// $booking_data = $wpdb->get_row( $wpdb->prepare( $sql, $booking_id ) );

		// Meeting Location Check
		$meeting_locations = json_decode( $attendeeBooking->meeting_location );
		$locations         = array();
		if ( is_array( $meeting_locations ) ) {
			foreach ( $meeting_locations as $location ) {
				if ( isset( $location->location ) ) {
					$locations[] = $location->location;
				}
			}
		}
		// 

		$replacements = array(
			'{{meeting.title}}'    => ! empty( $attendeeBooking->meeting_title ) ? $attendeeBooking->meeting_title : '',
			'{{meeting.date}}'     => ! empty( $attendeeBooking->meeting_dates ) ? $attendeeBooking->meeting_dates : '',
			'{{meeting.location}}' => implode( ', ', $locations ),
			'{{meeting.duration}}' => $attendeeBooking->meeting_duration,
			'{{meeting.time}}'     => $attendeeBooking->start_time . '-' . $attendeeBooking->end_time,
			'{{host.name}}'        => $attendeeBooking->host_first_name . ' ' . $attendeeBooking->host_last_name,
			'{{host.email}}'       => ! empty( $attendeeBooking->host_email ) ? $attendeeBooking->host_email : '',
			'{{attendee.name}}'    => ! empty( $attendeeBooking->attendee_name ) ? $attendeeBooking->attendee_name : '',
			'{{attendee.email}}'   => ! empty( $attendeeBooking->attendee_email ) ? $attendeeBooking->attendee_email : '', 

		);
		
		// Additional Data
		if( !empty($attendeeBooking->others_info) && $attendeeBooking->others_info != NULL ){
			$additional_data = json_decode($attendeeBooking->others_info);
			$others_info_html = '<ul>';
			foreach ($additional_data as $key => $value) {
				$others_info_html .= '<li>'.$key.' : '.$value.'</li>'; 
			}
			$others_info_html .= '</ul>';
			$replacements['{{attendee.additional_data}}'] = $others_info_html;
		}
		 
		// reason
		if( !empty($attendeeBooking->reason) && $attendeeBooking->reason != NULL ){
			$replacements['{{booking.cancel_reason}}'] = $attendeeBooking->reason;
			$replacements['{{booking.rescheduled_reason}}'] = $attendeeBooking->reason;
		}
		
		
		if($attendeeBooking->attendee_can_cancel == 1){ 
		
			$cancel_link = home_url( '?hydra-booking=booking&hash=' . $attendeeBooking->hash . '&meetingId=' . $attendeeBooking->meeting_id  . '&type=cancel' );
			$replacements['{{booking.cancel_link}}'] = $cancel_link;
		}
		if( $attendeeBooking->attendee_can_cancel == 1){ 
			$rescheduled_link = home_url( '?hydra-booking=booking&hash=' . $attendeeBooking->hash . '&meetingId=' . $attendeeBooking->meeting_id . '&type=reschedule' );
			$replacements['{{booking.rescheduled_link}}'] = $rescheduled_link;
		}
		// Full start end time with timezone for attendee 
		$replacements['{{booking.full_start_end_attendee_timezone}}'] = $attendeeBooking->start_time.' - '.$attendeeBooking->end_time.' ('.$attendeeBooking->attendee_time_zone.')';
		$replacements['{{booking.start_date_time_for_attendee}}'] = $attendeeBooking->start_time. ' ('.$attendeeBooking->attendee_time_zone.')';
		
	
		// Full start end time with timezone for host
		$dateTime = new DateTimeController( 'UTC' );
		$metting_dates = explode(',', $attendeeBooking->meeting_dates);
		if($attendeeBooking->host_time_zone != ''){
			$full_start_end_host_timezone = $dateTime->convert_full_start_end_host_timezone_with_date( $attendeeBooking->start_time, $attendeeBooking->end_time, $attendeeBooking->attendee_time_zone, $attendeeBooking->host_time_zone,  $metting_dates[0], 'full' );  
			$replacements['{{booking.full_start_end_host_timezone}}'] = $full_start_end_host_timezone;

			$start_date_time_for_host = $dateTime->convert_full_start_end_host_timezone_with_date( $attendeeBooking->start_time, $attendeeBooking->end_time, $attendeeBooking->attendee_time_zone, $attendeeBooking->host_time_zone,  $metting_dates[0], 'start' );
			$replacements['{{booking.start_date_time_for_host}}'] =  $start_date_time_for_host;
		}else{
			$replacements['{{booking.full_start_end_host_timezone}}'] = $attendeeBooking->start_time.' - '.$attendeeBooking->end_time.' ('.$attendeeBooking->attendee_time_zone.')';

			$replacements['{{booking.start_date_time_for_host}}'] = $attendeeBooking->start_time. ' ('.$attendeeBooking->attendee_time_zone.')';
		}

		if( !empty($attendeeBooking->booking_locations) && $attendeeBooking->booking_locations != NULL  ){
			$booking_locations = json_decode($attendeeBooking->booking_locations);
			

			
			$booking_locations_html = '<ul>';
			foreach ($booking_locations as $key => $value) { 
				if($key == 'zoom'){
					$link = $value->address->link;
					$password = $value->address->password; 
					$booking_locations_html .= '<li> <b>'.$value->location.' :</b> <a href="'.esc_url($link).'" target="_blank">Join Meeting</a> <br> <b>Password :</b> '.esc_html($password).'</li>';
				}else{

					$booking_locations_html .= '<li> <b>'.$value->location.' :</b> '.$value->address.'</li>'; 
				}
			}
			$booking_locations_html .= '</ul>';
			$replacements['{{booking.location_details_html}}'] = $booking_locations_html;
		}  
		$tags   = array_keys( $replacements );
		$values = array_values( $replacements ); 
		return str_replace( $tags, $values, $template );
	}
}
