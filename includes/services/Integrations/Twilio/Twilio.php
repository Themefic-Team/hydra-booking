<?php
namespace HydraBooking\Services\Integrations\Twilio;

use HydraBooking\DB\Meeting;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\DB\BookingMeta;
use HydraBooking\Admin\Controller\DateTimeController;

class Twilio {

	public function __construct( ) {
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'pushBookingToConfirmed' ), 20, 1 ); 
		add_action( 'hydra_booking/after_booking_pending', array( $this, 'pushBookingToPending' ), 20, 1 );
		add_action( 'hydra_booking/after_booking_canceled', array( $this, 'pushBookingToCanceled' ), 20, 1 );
		add_action( 'hydra_booking/after_booking_schedule', array( $this, 'pushBookingToscheduled' ), 20, 1 );
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

		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['twilio']['booking_confirmation']['status']) && !empty($_tfhb_notification_settings['twilio']['booking_confirmation']['body'])){
				$twilio_data = $this->tfhb_twilio_callback($_tfhb_notification_settings['twilio']['booking_confirmation']['body'], $attendees);
			}
		}
       
	}


	// If booking Status is Pending
	public function pushBookingToPending( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';

		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['twilio']['booking_pending']['status']) && !empty($_tfhb_notification_settings['twilio']['booking_pending']['body'])){
				$twilio_data = $this->tfhb_twilio_callback($_tfhb_notification_settings['twilio']['booking_pending']['body'], $attendees);
			}
		}

	}

	// If booking Status is Cancel
	public function pushBookingToCanceled( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';

		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['twilio']['booking_cancel']['status']) && !empty($_tfhb_notification_settings['twilio']['booking_cancel']['body'])){
				$twilio_data = $this->tfhb_twilio_callback($_tfhb_notification_settings['twilio']['booking_cancel']['body'], $attendees);
			}
		}

	}

	// If booking Status is ReSchedule
	public function pushBookingToscheduled( $attendees ) {
		
		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';

		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['twilio']['booking_reschedule']['status']) && !empty($_tfhb_notification_settings['twilio']['booking_reschedule']['body'])){
				$twilio_data = $this->tfhb_twilio_callback($_tfhb_notification_settings['twilio']['booking_reschedule']['body'], $attendees);
			}
		}

	}

    function tfhb_twilio_callback($body, $attendees) {

		$_tfhb_host_integration_settings = is_array( get_user_meta( $attendees->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $attendees->host_id, '_tfhb_host_integration_settings', true ) : array();
        $_tfhb_integration_settings = !empty(get_option( '_tfhb_integration_settings' )) && get_option( '_tfhb_integration_settings' ) != false ? get_option( '_tfhb_integration_settings' ) : array();
        
		if(!empty($_tfhb_host_integration_settings['twilio']) && !empty($_tfhb_host_integration_settings['twilio']['status'])){
			$twilio_status = !empty($_tfhb_host_integration_settings['twilio']['status']) ? $_tfhb_host_integration_settings['twilio']['status'] : '';
			$twilio_number = !empty($_tfhb_host_integration_settings['twilio']['receive_number']) ? $_tfhb_host_integration_settings['twilio']['receive_number'] : '';
			$twilio_from_number = !empty($_tfhb_host_integration_settings['twilio']['from_number']) ? $_tfhb_host_integration_settings['twilio']['from_number'] : '';
			$twilio_sid = !empty($_tfhb_host_integration_settings['twilio']['sid']) ? $_tfhb_host_integration_settings['twilio']['sid'] : '';
			$twilio_token = !empty($_tfhb_host_integration_settings['twilio']['token']) ? $_tfhb_host_integration_settings['twilio']['token'] : '';
			$twilio_otp_type = !empty($_tfhb_host_integration_settings['twilio']['otp_type']) ? $_tfhb_host_integration_settings['twilio']['otp_type'] : '';
		}else{
			$twilio_status = !empty($_tfhb_integration_settings['twilio']['status']) ? $_tfhb_integration_settings['twilio']['status'] : '';
			$twilio_number = !empty($_tfhb_integration_settings['twilio']['receive_number']) ? $_tfhb_integration_settings['twilio']['receive_number'] : '';
			$twilio_from_number = !empty($_tfhb_integration_settings['twilio']['from_number']) ? $_tfhb_integration_settings['twilio']['from_number'] : '';
			$twilio_sid = !empty($_tfhb_integration_settings['twilio']['sid']) ? $_tfhb_integration_settings['twilio']['sid'] : '';
			$twilio_token = !empty($_tfhb_integration_settings['twilio']['token']) ? $_tfhb_integration_settings['twilio']['token'] : '';
			$twilio_otp_type = !empty($_tfhb_integration_settings['twilio']['otp_type']) ? $_tfhb_integration_settings['twilio']['otp_type'] : '';
		}


        if(!empty($twilio_status) && !empty($twilio_number) && !empty($twilio_sid) && !empty($twilio_token)){

            $mailbody = $this->replace_mail_tags($body, $attendees->id);
            $plain_text = preg_replace('/<\/?p>/', "\n", $mailbody);
            $plain_text = wp_strip_all_tags($plain_text);
            $plain_text = trim($plain_text);
			$otp_type = $twilio_otp_type=='whatsapp' ? 'whatsapp:' : '';
			$args = [
				'body'    => [
					'From' => $otp_type . $twilio_from_number,
					'To'   => $otp_type . $twilio_number,
					'Body' => $plain_text,
				],
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode("$twilio_sid:$twilio_token"),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				],
				'timeout' => 30,
				'method'  => 'POST',
			];
			
			$response = wp_remote_post('https://api.twilio.com/2010-04-01/Accounts/' . $twilio_sid . '/Messages.json', $args);

			if (is_wp_error($response)) {
				error_log('Twilio API Error: ' . $response->get_error_message());
			}
        }
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
			'{{meeting.content}}'    => ! empty( $attendeeBooking->meeting_content ) ? $attendeeBooking->meeting_content : '',
			'{{meeting.date}}'     => ! empty( $attendeeBooking->meeting_dates ) ? $attendeeBooking->meeting_dates : '',
			'{{meeting.location}}' => implode( ', ', $locations ),
			'{{meeting.duration}}' => $attendeeBooking->meeting_duration,
			'{{meeting.time}}'     => $attendeeBooking->start_time . '-' . $attendeeBooking->end_time,
			'{{host.name}}'        => $attendeeBooking->host_first_name . ' ' . $attendeeBooking->host_last_name,
			'{{host.email}}'       => ! empty( $attendeeBooking->host_email ) ? $attendeeBooking->host_email : '',
			'{{host.phone}}'       => ! empty( $attendeeBooking->host_phone ) ? $attendeeBooking->host_phone : '',
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
 
		if( !empty($attendeeBooking->meeting_locations) && $attendeeBooking->meeting_locations != NULL  ){
			$booking_locations = json_decode($attendeeBooking->meeting_locations); 
			
			$booking_locations_html = '';
			foreach ($booking_locations as $key => $value) { 
				if($key == 'zoom'){
					$link = $value->address->link;
					$password = $value->address->password;  
					$booking_locations_html .= '<b>'.$value->location.' :</b> <a href="'.esc_url($link).'" target="_blank">Join Meeting</a> <br> <b>Password :</b> '.esc_html($password).'<br>';
				}else{
					$booking_locations_html .= '<b>'.$value->location.' :</b> '.$value->address.'<br>'; 
				}
			}

			$replacements['{{booking.location_details_html}}'] = $booking_locations_html;
		}  
		$tags   = array_keys( $replacements );
		$values = array_values( $replacements ); 
		return str_replace( $tags, $values, $template );
	}

}