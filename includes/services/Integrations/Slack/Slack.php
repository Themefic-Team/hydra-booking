<?php
namespace HydraBooking\Services\Integrations\Slack;

use HydraBooking\DB\Meeting;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\DB\BookingMeta;
use HydraBooking\Admin\Controller\DateTimeController;

class Slack {

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

		
		$webhook_url = ""; // Replace with your Webhook URL
		$message = "Ki obosta re sydur vaio";
		$payload = json_encode(["text" => $message, "username" => "Anonymous"]);
	
		$args = [
			'body'        => $payload,
			'headers'     => ['Content-Type' => 'application/json'],
			'method'      => 'POST',
			'data_format' => 'body',
		];
	
		$response = wp_remote_post($webhook_url, $args);
		var_dump($response); exit();
	
		if (is_wp_error($response)) {
			return "Error: " . $response->get_error_message();
		}
	
		return wp_remote_retrieve_body($response);
		
		
		// Example Usage:
		send_slack_message("Hello, this is an anonymous message from WordPress!");
		
        $_tfhb_notification_settings = !empty(get_option( '_tfhb_notification_settings' )) && get_option( '_tfhb_notification_settings' ) != false ? get_option( '_tfhb_notification_settings' ) : array();
        if(!empty($_tfhb_notification_settings['telegram']['booking_confirmation']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_confirmation']['body'])){
            $telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_confirmation']['body'], $attendees);
        }
       
	}


	// If booking Status is Pending
	public function pushBookingToPending( $attendees ) {

        $_tfhb_notification_settings = !empty(get_option( '_tfhb_notification_settings' )) && get_option( '_tfhb_notification_settings' ) != false ? get_option( '_tfhb_notification_settings' ) : array();
        if(!empty($_tfhb_notification_settings['telegram']['booking_pending']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_pending']['body'])){
            $telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_pending']['body'], $attendees);
        }

	}

	// If booking Status is Cancel
	public function pushBookingToCanceled( $attendees ) {

        $_tfhb_notification_settings = !empty(get_option( '_tfhb_notification_settings' )) && get_option( '_tfhb_notification_settings' ) != false ? get_option( '_tfhb_notification_settings' ) : array();
        if(!empty($_tfhb_notification_settings['telegram']['booking_cancel']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_cancel']['body'])){
            $telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_cancel']['body'], $attendees);
        }

	}

	// If booking Status is ReSchedule
	public function pushBookingToscheduled( $attendees ) {
		
        $_tfhb_notification_settings = !empty(get_option( '_tfhb_notification_settings' )) && get_option( '_tfhb_notification_settings' ) != false ? get_option( '_tfhb_notification_settings' ) : array();
        if(!empty($_tfhb_notification_settings['telegram']['booking_reschedule']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_reschedule']['body'])){
            $telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_reschedule']['body'], $attendees);
        }

	}

    function tfhb_telegram_callback($body, $attendees) {

		$_tfhb_host_integration_settings = is_array( get_user_meta( $attendees->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $attendees->host_id, '_tfhb_host_integration_settings', true ) : array();
        $_tfhb_integration_settings = !empty(get_option( '_tfhb_integration_settings' )) && get_option( '_tfhb_integration_settings' ) != false ? get_option( '_tfhb_integration_settings' ) : array();
        
		if(!empty($_tfhb_host_integration_settings)){
			$telegram_status = !empty($_tfhb_host_integration_settings['telegram']['status']) ? $_tfhb_host_integration_settings['telegram']['status'] : '';
			$telegram_bot_token = !empty($_tfhb_host_integration_settings['telegram']['bot_token']) ? $_tfhb_host_integration_settings['telegram']['bot_token'] : '';
			$telegram_chat_id = !empty($_tfhb_host_integration_settings['telegram']['chat_id']) ? $_tfhb_host_integration_settings['telegram']['chat_id'] : '';
		}

		$global_telegram_status = !empty($_tfhb_integration_settings['telegram']['status']) ? $_tfhb_integration_settings['telegram']['status'] : '';
		$global_telegram_bot_token = !empty($_tfhb_integration_settings['telegram']['bot_token']) ? $_tfhb_integration_settings['telegram']['bot_token'] : '';
		$global_telegram_chat_id = !empty($_tfhb_integration_settings['telegram']['chat_id']) ? $_tfhb_integration_settings['telegram']['chat_id'] : '';

		$telegram_status = $telegram_status ? $telegram_status : $global_telegram_status;
		$telegram_bot_token = $telegram_bot_token ? $telegram_bot_token : $global_telegram_bot_token;
		$telegram_chat_id = $telegram_chat_id ? $telegram_chat_id : $global_telegram_chat_id;

        if(!empty($telegram_status) && !empty($telegram_bot_token) && !empty($telegram_chat_id)){

            $mailbody = $this->replace_mail_tags($body, $attendees->id);
            $plain_text = preg_replace('/<\/?p>/', "\n", $mailbody);
            $plain_text = wp_strip_all_tags($plain_text);
            $plain_text = trim($plain_text);

            $api_url = "https://api.telegram.org/bot$telegram_bot_token/sendMessage";
			$args = array(
				'chat_id' => $telegram_chat_id,
				'text' => $plain_text,
                'parse_mode' => 'MarkdownV2'
			);

			$response = wp_remote_post( $api_url, array(
				'body' => json_encode( $args ),
				'headers' => array( 'Content-Type' => 'application/json' ),
			) );
            return $response;


			if ( is_wp_error( $response ) ) {
				error_log( 'Telegram API request failed: ' . $response->get_error_message() );
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
