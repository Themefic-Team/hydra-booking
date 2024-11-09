<?php

namespace HydraBooking\Services\Integrations\GoogleCalendar;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

use HydraBooking\Admin\Controller\RouteController;
use HydraBooking\DB\Booking;
use HydraBooking\DB\BookingMeta;
use HydraBooking\DB\Host;
use HydraBooking\DB\Meeting;
class GoogleCalendar {

	public $clientId;
	public $clientSecret;
	public $redirectUrl;

	private $accessToken;

	public $revokeUrl        = 'https://oauth2.googleapis.com/revoke';
	public $tokenUrl         = 'https://oauth2.googleapis.com/token';
	private $refreshTokenUrl = 'https://www.googleapis.com/oauth2/v3/token';
	public $authUrl          = 'https://accounts.google.com/o/oauth2/auth';

	public $calendarEvent = 'https://www.googleapis.com/calendar/v3/calendars/';

	public $authScope = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events';



	public function __construct() {

		$this->setClientData();
 

		
	}

	// Update Google Calender
	public function checkConnectionStatus(){
		if($this->clientId != '' && $this->clientSecret != '' && $this->redirectUrl != ''){
			return true;
		}
		return false;
	}

	// Set Client Data
	public function setClientData() {
		// Get the Google Calendar Data
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$google_calendar            = isset( $_tfhb_integration_settings['google_calendar'] ) ? $_tfhb_integration_settings['google_calendar'] : array();

		// Set the Client Data
		$this->clientId     = isset( $google_calendar['client_id'] ) ? $google_calendar['client_id'] : '';
		$this->clientSecret = isset( $google_calendar['secret_key'] ) ? $google_calendar['secret_key'] : '';
		$this->redirectUrl  = isset( $google_calendar['redirect_url'] ) ? $google_calendar['redirect_url'] : $this->setRredirectUrl();
	}

	public function setRredirectUrl() {
		// example : wp-json/hydra-booking/v1/integration/google-api
		return get_rest_url() . 'hydra-booking/v1/integration/google-api';
	}
	// Set Access Token
	public function setAccessToken( $host_id ) {

		$_tfhb_host_integration_settings = is_array( get_user_meta( $host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $host_id, '_tfhb_host_integration_settings', true ) : array();
		$accessToken                     = isset( $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['access_token'] ) ? $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['access_token'] : '';

		$this->accessToken = $accessToken;
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/integration/google-api',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetAccessData' ),
				'permission_callback' =>  array($this, 'permission_callback'),
			)
		);
	}
	public function permission_callback() {
		// get header data form request "capability' 

		 return true;
		// check current user have capability
		return current_user_can( 'tfhb_manage_hosts' );
	}

	public function GetAccessData() {

		// Set the Client Data
		if ( isset( $_GET['code'] ) && isset( $_GET['state'] ) ) {

			try {

				$host_id = $_GET['state'];

				$data  = $this->GetAccessToken( $_GET['code'] ); 
				$email = $this->getEmailByIdToken( $data['id_token'] );

				// Get all calendar in the account
				$url      = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
				$response = wp_remote_get( $url, array( 'headers' => array( 'Authorization' => 'Bearer ' . $data['access_token'] ) ) );
				$body     = wp_remote_retrieve_body( $response );
				$body     = json_decode( $body, true );

				$data['email'] = $email;

				foreach ( $body['items'] as $calendar ) {
					if ( $calendar['accessRole'] == 'owner' || $calendar['accessRole'] == 'writer' ) {
						$data['items'][] = array(
							'id'           => $calendar['id'],
							'title'        => $calendar['summary'],
							'write_status' => 0,
						);
					}
				}

				// remove the Id Token
				unset( $data['id_token'] );

				$_tfhb_host_integration_settings = is_array( get_user_meta( $host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $host_id, '_tfhb_host_integration_settings', true ) : array();

				$_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar'] = $data;

				// save to user metadata
				update_user_meta( $host_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings, true );

				$redirect_url = get_site_url() . '/wp-admin/admin.php?page=hydra-booking#/hosts/profile/' . $host_id . '/calendars';

				wp_redirect( $redirect_url );
				// wp_die();

			} catch ( Exception $e ) {
				echo esc_html($e->getMessage());
				exit();
			}
		}
	}


	// if Access token is experired
	public function refreshToken( $host_id ) {
		$_tfhb_host_integration_settings = is_array( get_user_meta( $host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $host_id, '_tfhb_host_integration_settings', true ) : array();
		$refreshToken                    = isset( $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['refresh_token'] ) ? $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['refresh_token'] : '';

		$url          = $this->refreshTokenUrl;
		$clientId     = $this->clientId;
		$clientSecret = $this->clientSecret;
		$post_fields  = array(
			'client_id'     => $clientId,
			'client_secret' => $clientSecret,
			'refresh_token' => $refreshToken,
			'grant_type'    => 'refresh_token',
		);
		// use Wp Remote Request
		$response = wp_remote_post(
			$url,
			array(
				'body' => $post_fields,
			)
		);
		$body     = wp_remote_retrieve_body( $response );
		$body     = json_decode( $body, true );

		if ( $body['access_token'] ) {

			$this->accessToken = $body['access_token'];
		} else {
			$this->setAccessToken( $host_id );
		}

		return $body;
	}

	public function GetAccessToken( $code ) {
		$url          = $this->tokenUrl;
		$clientId     = $this->clientId;
		$clientSecret = $this->clientSecret;
		$redirectUrl  = $this->redirectUrl;
		$post_fields  = array(
			'code'          => $code,
			'client_id'     => $clientId,
			'client_secret' => $clientSecret,
			'redirect_uri'  => $redirectUrl,
			'grant_type'    => 'authorization_code',
		);
		// use Wp Remote Request
		$response = wp_remote_post(
			$url,
			array(
				'body' => $post_fields,
			)
		);
		$body     = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	public function GetAccessTokenUrl( $host_id ) {
		return $this->authUrl . '?client_id=' . $this->clientId . '&redirect_uri=' . $this->redirectUrl . '&scope=' . $this->authScope . '&response_type=code&access_type=offline&prompt=consent&state=' . $host_id;
	}


	/**
	 * Get the email by id token
	 *
	 * @param $token
	 * @return mixed
	 */
	public function getEmailByIdToken( $id_token ) {
		$tokenParts   = explode( '.', $id_token );
		$tokenPayload = base64_decode( $tokenParts[1] );
		$jwtPayload   = json_decode( $tokenPayload, true );
		return $jwtPayload['email'];
	}


	public function generateAuthCode( $code ) {
		$body = array(
			'client_id'     => $this->clientId,
			'client_secret' => $this->clientSecret,
			'redirect_uri'  => $this->redirectUrl,
			'grant_type'    => 'authorization_code',
			'code'          => $code,
		);

		$type    = 'GET';
		$url     = $this->tokenUrl;
		$headers = array(
			'Content-Type'              => 'application/http',
			'Content-Transfer-Encoding' => 'binary',
			'MIME-Version'              => '1.0',
		);

		$args = array(
			'headers' => $headers,
			'method'  => $type,
			'timeout' => 20,
		);

		if ( $body ) {

			$url = add_query_arg( $body, $url );
		}

		$request = wp_remote_request( $url, $args );
	}



	// Insert Booking to Google Calendar
	public function InsertGoogleCalender($data ) {
	
		$value = array();
	
		if ( ! isset( $data->id ) ) {
			return;
		}

		$settings        = get_option( '_tfhb_integration_settings' );
		$google_calender = isset( $settings['google_calendar'] ) ? $settings['google_calendar'] : array();
		if ( isset( $google_calender['status'] ) && $google_calender['status'] == 0 ) {
			return $value;
		}
		if ( isset( $google_calender['connection_status'] ) && $google_calender['connection_status'] == 0 ) {
			return $value;
		}

		// set event data google meet shedule
		$start_time    = strtotime( $data->start_time ); // 03:45 AM
		$end_time      = strtotime( $data->end_time ); // 04:30 AM
		$meeting_dates = $data->meeting_dates; // 2024-07-10,2024-07-17,2024-07-24,2024-07-31

		// Set the Access Token
		$this->refreshToken( $data->host_id );

		$meeting_dates = explode( ',', $meeting_dates );

		$host     = new Host();
		$hostData = $host->get( $data->host_id );

		$meeting           = new Meeting();
		$meetingData       = $meeting->get( $data->meeting_id );
		$meeting_locations = json_decode( $meetingData->meeting_locations, true );
		
		$enable_meeting_location = false;
		// if in array location value is meet then set google meet using array filter
		$meeting_location        = array_filter(
			$meeting_locations,
			function ( $location ) {
				return $location['location'] == 'meet';
			}
		); 
		
		$enable_meeting_location = count( $meeting_location ) > 0 ? true : false;

		$google_calendar_body = array(); 

		
		
		foreach ( $meeting_dates as $meeting_date ) {
			$start_date = gmdate( 'Y-m-d', strtotime( $meeting_date ) ) . 'T' . gmdate( 'H:i:s', $start_time );
			$end_date   = gmdate( 'Y-m-d', strtotime( $meeting_date ) ) . 'T' . gmdate( 'H:i:s', $end_time );

			// Meeting location google meeting
			$setData = array(
				'title'          => 'Meeting with ' . $data->attendee_name,
				'summary'        => 'Title: ' . $meetingData->title,
				// 'location' => 'Location: ' . $data->meeting_location,
				'description'    => 'Description: ',
				'start'          => array(
					'dateTime' => $start_date,
					'timeZone' => $data->attendee_time_zone,
				),
				'end'            => array(
					'dateTime' => $end_date,
					'timeZone' => $data->attendee_time_zone,
				),
				'attendees'      => array(
					array( 'email' => $data->email ),
					array( 'email' => $hostData->email ),
				),
				'reminders'      => array(
					'useDefault' => false,
					'overrides'  => array(
						array(
							'method'  => 'email',
							'minutes' => 24 * 60,
						),
						array(
							'method'  => 'popup',
							'minutes' => 10,
						),
					),
				),
				'conferenceData' => array(
					'createRequest' => array(
						'requestId'             => 'sample123', // Provide a unique ID for the request
						'conferenceSolutionKey' => array(
							'type' => 'hangoutsMeet',
						),
					),
				),
			);
			if ( $enable_meeting_location == true ) {
				$setData['conferenceData'] = array(
					'createRequest' => array(
						'requestId'             => 'sample123', // Provide a unique ID for the request
						'conferenceSolutionKey' => array(
							'type' => 'hangoutsMeet',
						),
					),
				);
			}

			$_tfhb_host_integration_settings = is_array( get_user_meta( $data->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $data->host_id, '_tfhb_host_integration_settings', true ) : array();
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) ? $google_calendar['selected_calendar_id'] : '';

			if ( $calendarId ) {

				$url = $this->calendarEvent . $calendarId . '/events?sendUpdates=all';
				if ( $enable_meeting_location == true ) {
					$url .= '&conferenceDataVersion=1';


				}
				// set all events
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array(
							'Authorization' => 'Bearer ' . $this->accessToken,
						),
						'body'        => wp_json_encode( $setData ),
						'method'      => 'POST',
						'data_format' => 'body',
					)
				);

				$body = wp_remote_retrieve_body( $response );
				
				

				$google_calendar_body[ ] = json_decode( $body, true );
 
			}
			
		}
		
		$meet_link = '';
		foreach ( $google_calendar_body as $key => $mvalue ) {
			$hangoutLink = isset( $mvalue['hangoutLink'] ) ? $mvalue['hangoutLink'] : '';
			if ( $hangoutLink != '' ) {
				$meet_link .= $hangoutLink . ' | ';
			}
		} 
		
		if($meet_link != '' && $enable_meeting_location == true ){
			$booking = new Booking();
			$getBookingData = $booking->get( $data->id ); 
			$meeting_loaction  = json_decode($getBookingData->meeting_locations); 
			
			// remove the last pipe
			$meet_link = rtrim($meet_link, '|');

			if(isset($meeting_loaction->meet)){
				$meeting_loaction->meet->address = $meet_link;
			} 

			$data->meeting_locations = $meeting_loaction;
		
			
		}
		
		$data = array(
			'meeting_locations' => $data->meeting_locations,
			'google_calendar' => $google_calendar_body,
		); 
	
		return $data; 

	}


	// Insert Calender After Booking Schedule
	public function insert_calender_after_booking_completed( $data ) {

		
		if($this->checkConnectionStatus() == false){
			return;
		}


		$booking     = new Booking();
		$meeting     = new Meeting();
		$BookingMeta = new BookingMeta(); 
		$get_booking_meta = $BookingMeta->getWithIdKey( $data->id, 'booking_calendar' );

		if ( $get_booking_meta ) {
			return false;
		}
		$MeetingData = $meeting->get( $data->meeting_id );
		
		$meta_data   = get_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', true );
		if ( 'one-to-group' == $meta_data['meeting_type'] ) {
			
			$max_book_per_slot = isset( $meta_data['max_book_per_slot'] ) ? $meta_data['max_book_per_slot'] : 1;
			$check_booking     = $booking->get(
				array(
					'meeting_id'    => $data->meeting_id,
					'meeting_dates' => $data->meeting_dates,
					'start_time'    => $data->start_time,
					'end_time'      => $data->end_time,
				),
				false,
				false,
				false,
				'id DESC',
			);

			// unset if check_booking has current booking data->id without loop and array maps or filter
			$check_booking = array_filter(
				$check_booking,
				function ( $booking ) use ( $data ) {
					return $booking->id !== $data->id;
				}
			);
			// Get First Items form the array
			$first_item = reset( $check_booking );

			if ( $first_item->meeting_calendar != 'null' && ! empty( $first_item->meeting_calendar ) && $first_item->meeting_calendar != 0 ) {

				$booking_calendar           = $BookingMeta->getFirstOrFail(
					$first_item->meeting_calendar
				);
				$update                     = array();
				$update['id']               = $data->id;
				$update['meeting_calendar'] = $booking_calendar->id;

				$booking->update( $update );
				$booking_calendar_value = json_decode( $booking_calendar->value );

				$booking_calendar_value = apply_filters( 'hydra_booking_calendar_add_new_attendee', $booking_calendar_value, $data );

				// Update the Booking meta
				$booking_meta = array(
					'id'    => $booking_calendar->id,
					'value' => wp_json_encode( $booking_calendar_value, true ),
				);

				$BookingMeta->update( $booking_meta );

				return;

			}
		} else {
			
			// Update the Booking
			$calendar_data = $this->InsertGoogleCalender($data);
			// $calendar_data = apply_filters( 'after_booking_completed_calendar_data', array(), $data );
			
			$value = array(
				'google_calendar' => $calendar_data['google_calendar'],
			);
		
			$booking_meta = array(
				'booking_id' => $data->id,
				'meta_key'   => 'booking_calendar',
				'value'      => wp_json_encode( $value, true ),
			);

			$insert = $BookingMeta->add( $booking_meta );

			$insert_id = $insert['insert_id'];
			if ( $insert_id === false ) {
				return false;
			} 
			$meeting_loaction = (array) $calendar_data['meeting_locations'];
 			$meeting_locations = is_array($meeting_loaction) ? $meeting_loaction  : wp_json_decode($meeting_loaction);
		

			
			$update                     = array();
			$update['id']               = $data->id;
			$update['meeting_calendar'] = $insert_id;
			$update['meeting_locations'] = $meeting_locations; 

			$booking->update( $update );
		}

		return true;
	}

	// add new attendee existing Booking to Google Calendar
	public function addAttendeeGoogleCalender( $data, $booking ) {

		// Set the Access Token
		$this->refreshToken( $booking->host_id );
		$events = $data->google_calendar;

		$google_calendar_body = array();
		foreach ( $events as $event ) {
			$event_id = $event->id;

			$new_attendees = array( 'email' => $booking->email );
			// add new attendee also remaing existing attendee
			$event->attendees[] = $new_attendees;

			$_tfhb_host_integration_settings = is_array( get_user_meta( $booking->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $booking->host_id, '_tfhb_host_integration_settings', true ) : array();
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) ? $google_calendar['selected_calendar_id'] : '';

			if ( $calendarId ) {

				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id;
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'body'        => wp_json_encode( $event ),
						'method'      => 'PUT',
						'data_format' => 'body',
					)
				);
				$body     = wp_remote_retrieve_body( $response );

				$google_calendar_body[] = json_decode( $body, true );

			}
		}

		// Update the Booking
		$data->google_calendar = $google_calendar_body;

		return $data;
	}

	/**
	 * Delete Google Calendar
	 *
	 * @param $data
	 * @param $booking
	 */
	public function deleteGoogleCalender( $booking ) { 
		if($this->checkConnectionStatus() == false){
			return;
		}
		// Get Meeting Calendar Data
		$meeting = new Meeting();
		$meetingData = $meeting->get( $booking->meeting_id );
		$meeting_locations = json_decode( $meetingData->meeting_locations, true ); // [{"location":"meet","address":""}]

		//  if !in array location value is meet then  return false 
		$meeting_location = array_filter(
			$meeting_locations,
			function ( $location ) {
				return $location['location'] == 'meet';
			}
		);
		if ( count( $meeting_location ) == 0 ) {
			return false;
		}

		// 		
		
		$bookingMeta = new BookingMeta(); 
		$booking_calendarData = $bookingMeta->getWithIdKey( $booking->id, 'booking_calendar' );


		// if booking calendar data is not found then return false
		if ( ! $booking_calendarData ) {
			return false;
		}
 

		// Set the Access Token
		$this->refreshToken( $booking->host_id );

		
		$value = json_decode($booking_calendarData->value);
		$events = $value->google_calendar;
		
  
		foreach($events as $event){
			$event_id = $event->id;

			$_tfhb_host_integration_settings = is_array( get_user_meta( $booking->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $booking->host_id, '_tfhb_host_integration_settings', true ) : array();
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) ? $google_calendar['selected_calendar_id'] : '';

			if ( $calendarId ) {

				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id;
				$response = wp_remote_request(
					$url,
					array(
						'headers' => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'method'  => 'DELETE',
					)
				);
				$body     = wp_remote_retrieve_body( $response );
	

			} 
		}
		// unest google calendar form value
		unset($value->google_calendar);
		$booking_calendarData->value = json_encode($value, true);

		$Update = $bookingMeta->update( array('id' => $booking_calendarData->id, 'value' => $booking_calendarData->value) );

		
		if ( $Update ) {
				// update meeting location
				$updateBooking = new Booking();
				$updateBookingData = $updateBooking->get( $booking->id ); 
				$meeting_loaction  = json_decode($updateBookingData->meeting_locations); 
				$meeting_loaction->meet->address = 'Cancelled'; 


				$updateBooking->update( array('id' => $booking->id, 'meeting_locations' => $meeting_loaction) );
				

			return true;
		} else {
			return false;
		}

	
	}


	/**
	 * Update Google Calendar edit existing Booking to Google Calendar
	 *
	 * @param $data
	 * @param $booking
	 */

	public function UpdateGoogleCalender( $booking ) {

		if($this->checkConnectionStatus() == false){
			return;
		}

		// Get Meeting Calendar Data
		$meeting = new Meeting();
		$meetingData = $meeting->get( $booking->meeting_id );
		$meeting_locations = json_decode( $meetingData->meeting_locations, true ); // [{"location":"meet","address":""}]

		//  if !in array location value is meet then  return false 
		$meeting_location = array_filter(
			$meeting_locations,
			function ( $location ) {
				return $location['location'] == 'meet';
			}
		);
		if ( count( $meeting_location ) == 0 ) {
			return false;
		}

		// 		
		
		$bookingMeta = new BookingMeta(); 
		$booking_calendarData = $bookingMeta->getWithIdKey( $booking->id, 'booking_calendar' );


		// if booking calendar data is not found then return false

		if ( ! $booking_calendarData ) {
			return false;
		}

		// Set the Access Token
		$this->refreshToken( $booking->host_id );


		$value = json_decode($booking_calendarData->value);

		$events = $value->google_calendar;

		$_tfhb_host_integration_settings = is_array( get_user_meta( $booking->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $booking->host_id, '_tfhb_host_integration_settings', true ) : array();
		$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
		$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) ? $google_calendar['selected_calendar_id'] : '';

		$google_calendar_body = array();
		foreach ($events as $event){
			$event_id = $event->id;

			// update event time date and time zone everyting based on reshedule details
			$start_time    = strtotime( $booking->start_time ); // 03:45 AM
			$end_time      = strtotime( $booking->end_time ); // 04:30 AM
			$meeting_dates = $booking->meeting_dates; // 2024-07-10,2024-07-17,2024-07-24,2024-07-31
 
			$start_date = gmdate( 'Y-m-d', strtotime( $meeting_dates ) ) . 'T' . gmdate( 'H:i:s', $start_time );
			$end_date   = gmdate( 'Y-m-d', strtotime( $meeting_dates ) ) . 'T' . gmdate( 'H:i:s', $end_time );

			$event->start = array(
				'dateTime' => $start_date,
				'timeZone' => $booking->attendee_time_zone,
			);
			$event->end = array(
				'dateTime' => $end_date,
				'timeZone' => $booking->attendee_time_zone,
			);

			// change attendee email
			$attendees = $event->attendees;
			foreach($attendees as $key => $attendee){
				if($attendee->email == $booking->email){
					$attendees[$key]->email = $booking->email;
				}
			}


			
			if ( $calendarId ) {
	
				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id;
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'body'        => wp_json_encode( $event ),
						'method'      => 'PUT',
						'data_format' => 'body',
					)
				);
				$body     = wp_remote_retrieve_body( $response );

				$google_calendar_body[] = json_decode( $body, true );
	
			}
		}

		// update booking calendar data
		$value->google_calendar = $google_calendar_body;

		$booking_calendarData->value = json_encode($value, true);

		$Update = $bookingMeta->update( array('id' => $booking_calendarData->id, 'value' => $booking_calendarData->value) );

		if ( $Update ) {
			return true;
		} else {
			return false;
		}

		
	 

	}
}
