<?php
namespace HydraBooking\Services\Integrations\Zoom;

use HydraBooking\DB\Booking;
use HydraBooking\DB\BookingMeta;
use HydraBooking\DB\Host;
use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\BookingTime;

class ZoomServices {

	public $account_id;
	protected $client_id;

	protected $client_secret;


	protected $access_token;

	public $revokeUrl = 'https://zoom.us/oauth/revoke';

	public $tokenUrl = 'https://zoom.us/oauth/token';



	public function __construct( ) {
 
		$this->setApiDetails();
	}

	public function setApiDetails(  ) {
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) && $_tfhb_integration_settings['zoom_meeting']['status'] == true ) {
			$this->account_id     = $_tfhb_integration_settings['zoom_meeting']['account_id'];
			$this->client_id  = $_tfhb_integration_settings['zoom_meeting']['app_client_id'];
			$this->client_secret = $_tfhb_integration_settings['zoom_meeting']['app_secret_key'];
		}
	}

	// setHostApiDetails
	public function setHostApiDetails( $host_id ) {
		$host = new Host();
		$host_meta = $host->get( $host_id );
		if ( ! $host_meta || ! is_object( $host_meta ) ) {
			return;
		}
		$_tfhb_host_integration_settings = get_user_meta( $host_meta->user_id, '_tfhb_host_integration_settings', true );
		
		if ( ! empty( $_tfhb_host_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_host_integration_settings['zoom_meeting']['connection_status'] ) && ! empty( $_tfhb_host_integration_settings['zoom_meeting']['status'] ) && $_tfhb_host_integration_settings['zoom_meeting']['status'] == true ) {
		 
			$this->account_id     = $_tfhb_host_integration_settings['zoom_meeting']['account_id'];
			$this->client_id  = $_tfhb_host_integration_settings['zoom_meeting']['app_client_id'];
			$this->client_secret = $_tfhb_host_integration_settings['zoom_meeting']['app_secret_key'];
		}
	}

	// Set Client Data for Zoom API.
	public function setClientData(  ) {
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$google_calendar            = isset( $_tfhb_integration_settings['google_calendar'] ) ? $_tfhb_integration_settings['google_calendar'] : array();

	}

	// Generate Access Token. 
	public function generateAccessToken() {
		// Fetch the access token
		$body    = array(
			'grant_type' => 'account_credentials',
			'account_id' => $this->account_id,
		);
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),

		);

		$response = wp_remote_post(
			$this->tokenUrl,
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			$body               = wp_remote_retrieve_body( $response );
			$body               = json_decode( $body, true );
			$this->access_token = is_array( $body ) && isset( $body['access_token'] ) ? $body['access_token'] : '';
			return $body;
		}
	}


	/* Create Zoom Meeting
	 * @param $single_booking_meta
	 * @param $meta_data
	 * @param $host_meta
	 * @return void
	 */
	public function tfhb_create_zoom_meeting( $booking) { 

		
		$BookingMeta = new BookingMeta();
		// check if the meeting id is available
		$get_booking_meta = $BookingMeta->getWithIdKey( $booking->booking_id, 'zoom_meeting', 1 ); 
		

		if ( $get_booking_meta ) { 
			// if the meeting id is available then update the meeting and add the attendees data
			$this->tfhb_add_attendee_to_zoom_meeting( $booking, $get_booking_meta );
			return false;

		}else{
			
			$location =  $this->create_zoom_meeting( $booking);
			
			return $location;
		}
		
 
	}

	// Add new Attendee to Zoom Meeting.
	public function tfhb_add_attendee_to_zoom_meeting( $booking, $get_booking_meta ) {

		$this->setHostApiDetails( $booking->host_id ); 
		$access_response = $this->generateAccessToken();

		$BookingMeta = new BookingMeta();
		$events = json_decode( $get_booking_meta->value, true );

		// The booking may have been rescheduled since the meeting was created: send its current time too
		$current_bodies = $this->zoomMeetingBody( $booking );

		$new_events_data = array();
		foreach ($events as $key => $event) {
			$attendees_data = isset( $event['settings']['meeting_invitees'] ) && is_array( $event['settings']['meeting_invitees'] ) ? $event['settings']['meeting_invitees'] : array();

			$attendees_data[] = array(
				'email' => $booking->email,
				'name'  => $booking->attendee_name,
			);

			$event['settings']['meeting_invitees'] = $attendees_data;

			if ( isset( $current_bodies[ $key ] ) ) {
				$event['start_time'] = $current_bodies[ $key ]['start_time'];
				$event['timezone']   = $current_bodies[ $key ]['timezone'];
				$event['duration']   = $current_bodies[ $key ]['duration'];
			}
			
			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $event['id'],
				array(
					'method'  => 'PATCH',
					'body'    => wp_json_encode( $event ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $this->access_token,
						'Content-Type'  => 'application/json',
					),
				)
			);
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

		
			$response_body = wp_remote_retrieve_body( $response );
			$events[$key] = $event;
		}  

		$bookingMetaData = array(
			'id' => $get_booking_meta->id,
			'value'      => wp_json_encode( $events, true ),
		);

		$BookingMeta->update( $bookingMetaData );

		// Registration meetings: give the joining attendee a personal join link.
		$this->tfhb_register_zoom_attendee( $booking, $events );

	}

	// Remove Attendee from Zoom Meeting.
	public function remove_attendde_location_from_existing_booking($booking_data, $attendee){

		// Group reschedule: the attendee leaves the old slot, so their personal link
		// to the old meeting is canceled (the registration is stored on the old booking).
		if ( is_object( $booking_data ) && ! empty( $booking_data->id ) ) {
			$this->tfhb_cancel_zoom_registrant( $attendee, $booking_data->id );
		}

		$this->setHostApiDetails( $booking_data->host_id );
		$access_response = $this->generateAccessToken();
		$BookingMeta = new BookingMeta();
		$booking_meta = $BookingMeta->getWithIdKey( $booking_data->id, 'zoom_meeting', 1 );
		$events = json_decode( $booking_meta->value, true );

		$new_events_data = array();
		foreach ($events as $key => $event) { 
			$attendees = json_decode( $booking_data->attendees, true );
			$attendees_data = $event['settings']['meeting_invitees']; 
			
			$attendees_data = array_filter($attendees_data, function($value) use ($attendee) {
				return $value['email'] != $attendee->email;
			});
			$event['settings']['meeting_invitees'] = $attendees_data; 
			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $event['id'],
				array(
					'method'  => 'PATCH',
					'body'    => wp_json_encode( $event ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

			$response_body = wp_remote_retrieve_body( $response );
			$events[$key] = $event;
		}  

		$bookingMetaData = array(
			'id' => $booking_meta->id,
			'value'      => wp_json_encode( $events, true ),
		);

		$BookingMeta->update( $bookingMetaData );
 
	}

	// Cancel Zoom Meeting.
	public function tfhb_cancel_zoom_meeting( $single_booking_meta ) {
		if ( ! is_object( $single_booking_meta ) ) {
			return false;
		}

		$booking_id = ! empty( $single_booking_meta->booking_id ) ? (int) $single_booking_meta->booking_id : ( ! empty( $single_booking_meta->id ) ? (int) $single_booking_meta->id : 0 );
		if ( empty( $booking_id ) ) {
			return false;
		}

		$host_id = ! empty( $single_booking_meta->host_id ) ? $single_booking_meta->host_id : 0;
		if ( empty( $host_id ) ) {
			$booking_obj = new Booking();
			$bdata = $booking_obj->get( $booking_id );
			if ( $bdata && ! empty( $bdata->host_id ) ) {
				$host_id = $bdata->host_id;
			}
		}

		if ( ! empty( $host_id ) ) {
			$this->setHostApiDetails( $host_id );
		}
		$access_response = $this->generateAccessToken();

		$BookingMeta = new BookingMeta();
		$booking_meta = $BookingMeta->getWithIdKey( $booking_id, 'zoom_meeting', 1 );
		if ( ! $booking_meta ) {
			$booking_meta = $BookingMeta->getWithIdKey( $booking_id, 'zoom_calendar', 1 );
		}

		if ( $booking_meta && ! empty( $access_response['access_token'] ) ) {
			$zoom_calendar = json_decode( $booking_meta->value );
			if ( is_array( $zoom_calendar ) || is_object( $zoom_calendar ) ) {
				foreach ( $zoom_calendar as $key => $value ) {
					$meeting_id = is_object( $value ) && ! empty( $value->id ) ? $value->id : ( is_array( $value ) && ! empty( $value['id'] ) ? $value['id'] : '' );
					if ( ! empty( $meeting_id ) ) {
						wp_remote_request(
							'https://api.zoom.us/v2/meetings/' . $meeting_id,
							array(
								'method'  => 'DELETE',
								'headers' => array(
									'Authorization' => 'Bearer ' . $access_response['access_token'],
									'Content-Type'  => 'application/json',
								),
							)
						);
					}
				}
			}
			// Remove Zoom Meta
			$BookingMeta->delete( $booking_meta->id );
		}

		// The meeting is gone, so are its personal registration links.
		$registrants_meta = $BookingMeta->getWithIdKey( $booking_id, 'zoom_registrants', 1 );
		if ( $registrants_meta ) {
			$BookingMeta->delete( $registrants_meta->id );
		}

		// remove zoom meeting from booking data 
		$booking = new Booking();
		$getBookingData = $booking->get( $booking_id );

		if ( $getBookingData && ! empty( $getBookingData->meeting_locations ) ) {
			$meeting_locations = $getBookingData->meeting_locations;
			if ( is_string( $meeting_locations ) ) {
				$meeting_locations = json_decode( $meeting_locations );
			}

			if ( is_object( $meeting_locations ) && isset( $meeting_locations->zoom ) ) {
				$meeting_locations->zoom->address = array(
					'link'     => '',
					'password' => '',
				);
			} elseif ( is_array( $meeting_locations ) && isset( $meeting_locations['zoom'] ) ) {
				$meeting_locations['zoom']['address'] = array(
					'link'     => '',
					'password' => '',
				);
			}

			$update                      = array();
			$update['id']                = $booking_id;
			$update['meeting_locations'] = $meeting_locations;

			$booking->update( $update );
		}

		return true;
	}


	// reshedule Zoom Meeting.
	public function tfhb_reshedule_zoom_meeting( $single_booking_meta ) {
		

		$this->setHostApiDetails( $single_booking_meta->host_id );
		$access_response = $this->generateAccessToken();
		$BookingMeta = new BookingMeta();
		$host = new Host();
		$meeting = new Meeting();
		$meeting_data = $meeting->get( $single_booking_meta->meeting_id );
		$host_meta = $host->get( $single_booking_meta->host_id );

		$booking_meta = $BookingMeta->getWithIdKey( $single_booking_meta->id, 'zoom_calendar', 1 );
	
		if ( ! $booking_meta ) {
			return false;
		}

		$zoom_calendar = json_decode( $booking_meta->value );

		$zoom_event_body = array();
		// Stored times are in the booking's availability timezone, not the attendee's
		$occurrences = BookingTime::booking_instants( $single_booking_meta );
		$time_zone   = BookingTime::booking_tz( $single_booking_meta )->getName();
		foreach ( $zoom_calendar as $key => $value ) {
			if ( ! isset( $occurrences[ $key ] ) ) {
				continue;
			}

			$attendee_data = array(
				array(
					'email' => $single_booking_meta->email,
					'name'  => $single_booking_meta->attendee_name,
				),
			);

			$data = array(
				'topic'      => ! empty( $meeting_data->title ) ? $meeting_data->title : '',
				'type'       => 2, // Scheduled Meeting
				'start_time' => BookingTime::iso_utc( $occurrences[ $key ]['start'] ),
				'timezone'   => $time_zone,
				'duration'   => $meeting_data->duration,
				'password'   => function_exists( 'wp_generate_password' ) ? wp_generate_password( 8, false, false ) : ( function_exists( 'wp_rand' ) ? substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 ) : substr( bin2hex( random_bytes( 4 ) ), 0, 8 ) ),
				'settings'   => array(
					'join_before_host' => false,
					'mute_upon_entry'  => true,
					'waiting_room'     => true,
				),
				'contact_email' => $host_meta->email,
				'contact_name'  => $host_meta->email,
			);
	 

			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $value->id,
				array(
					'method'  => 'PATCH',
					'body'    => wp_json_encode( $data ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);
			
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

			$response_body = wp_remote_retrieve_body( $response ); 
			
			$zoom_event_body[] = json_decode( $response_body, true );
		}
		


		$bookingMetaData = array(
			'id' => $booking_meta->id, 
			'value'      => wp_json_encode( $zoom_event_body, true ),
		);
 
		$BookingMeta->update( $bookingMetaData );
		
		$booking = new Booking();

		$getBookingData = $booking->get( $single_booking_meta->id );
			
		$meeting_locations =  $getBookingData->meeting_locations;
		 
	 
		$zoom_link = '';
		$password = '';
		foreach ( $zoom_event_body as $key => $mvalue ) {
			$zoom_link .=  $mvalue['join_url'] . ' | ';
			$password .=  $mvalue['password'] . ' | ';
		} 

		$meeting_locations->zoom->address = array(
			'link' => $zoom_link,
			'password' => $password,
		);

	 
		$meeting_locations = !is_array($meeting_locations) ?  json_decode($meeting_locations)  :  $meeting_locations;
		
	 
		$update                     = array();
		$update['id']               = $booking_meta->id;
		$update['meeting_locations'] = $meeting_locations;


		$booking->update( $update ); 

		return  true;
		
	}

	// Update Zoom Settings in the database.
	public function updateZoomSettings( $data = null ) {

		if ( $data == null ) {
			return array(
				'status'  => false,
				'message' => __( 'Invalid Data', 'hydra-booking' ),
			);
		}

		$this->account_id = sanitize_text_field( $data['account_id'] );
		$this->client_id = sanitize_text_field( $data['app_client_id'] );
		$this->client_secret = sanitize_text_field( $data['app_secret_key'] );  
	
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		// return error message if data is not set
		if ( ! isset( $data['account_id'] ) || ! isset( $data['app_client_id'] ) || ! isset( $data['app_secret_key'] ) ) {
			$data = array(
				'status'  => false,
				'message' => __( 'Invalid Data', 'hydra-booking' ),
			);
			return $data;
		}

		$zoom_meeting['type']              = isset( $data['meeting'] ) ? sanitize_text_field( $data['meeting'] ) : '';
		$zoom_meeting['status']            = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : '';
		$zoom_meeting['connection_status'] = 1;
		$zoom_meeting['account_id']        = sanitize_text_field( $data['account_id'] );
		$zoom_meeting['app_client_id']     = sanitize_text_field( $data['app_client_id'] );
		$zoom_meeting['app_secret_key']    = sanitize_text_field( $data['app_secret_key'] );

		$response = $this->generateAccessToken();

		if ( isset( $response['error'] ) ) {

			$data = array(
				'status'  => false,
				'message' => $response['reason'],
			);

			return $data;

		} else {

			$_tfhb_integration_settings['zoom_meeting'] = $zoom_meeting;

			// update option
			update_option( '_tfhb_integration_settings', $_tfhb_integration_settings );


			// get Option 
			$option = get_option( '_tfhb_integration_settings' );


			$data = array(
				'status'  => true,
				'integration_settings'  => $option,
				'message' => __( 'Zoom Integration Settings Updated Successfully', 'hydra-booking' ),
			);
			return $data;
		}
	}

	// Update Zoom Settings in the database.
	public function updateHostsZoomSettings( $data = null, $user_id = null ) {
		

		if ( $data == null || $user_id == null ) {
			return array(
				'status'  => false,
				'message' => __( 'Invalid Data', 'hydra-booking' ),
			);
		}
 
		$this->account_id = sanitize_text_field( $data['account_id'] );
		$this->client_id = sanitize_text_field( $data['app_client_id'] );
		$this->client_secret = sanitize_text_field( $data['app_secret_key'] );   

		$_tfhb_host_integration_settings = get_user_meta( $user_id, '_tfhb_host_integration_settings', true );

		if(empty($_tfhb_host_integration_settings) || !is_array($_tfhb_host_integration_settings)){
			$_tfhb_host_integration_settings = array();
		} 
		// return error message if data is not set
		if ( ! isset( $data['account_id'] ) || ! isset( $data['app_client_id'] ) || ! isset( $data['app_secret_key'] ) ) {

			$data = array(
				'status'  => false,
				'message' => __( 'Invalid Data', 'hydra-booking' ),
			);
			return $data;
		}
		
		$zoom_meeting['type']              = 'zoom-meeting';
		$zoom_meeting['status']            = sanitize_text_field( $data['status'] );
		$zoom_meeting['connection_status'] = 1;
		$zoom_meeting['account_id']        = sanitize_text_field( $data['account_id'] );
		$zoom_meeting['app_client_id']     = sanitize_text_field( $data['app_client_id'] );
		$zoom_meeting['app_secret_key']    = sanitize_text_field( $data['app_secret_key'] );

		$response = $this->generateAccessToken();

		if ( isset( $response['error'] ) ) {
			$data = array(
				'status'  => false,
				'message' => $response['reason'],
			);
			return $data;
		} else { 
			$_tfhb_host_integration_settings['zoom_meeting'] = $zoom_meeting; 

			// update user meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );


			$data = array(
				'status'  => true,
				'message' => __( 'Zoom Integration Settings Updated Successfully', 'hydra-booking' ),
			);
			return $data;
		}
	}

	public function create_zoom_meeting( $booking ) {

		$this->setHostApiDetails( $booking->host_id );
		$access_response = $this->generateAccessToken();

		// Group meetings: try Zoom registration (personal join links); plain bodies are the fallback.
		$use_registration = $this->tfhb_group_registration_wanted( $booking );
		$event_data       = $this->zoomMeetingBody( $booking, $use_registration );
		$plain_data       = $use_registration ? $this->zoomMeetingBody( $booking, false ) : $event_data;


		$zoom_event_body = array();
		foreach($event_data as $index => $data){
			$response = wp_remote_post(
				'https://api.zoom.us/v2/users/me/meetings',
				array(
					'body'    => wp_json_encode( $data ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);

			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $use_registration ) {
				$code = (int) wp_remote_retrieve_response_code( $response );
				if ( $code >= 300 || empty( $response_body['id'] ) ) {
					// Zoom refused registration (e.g. Basic plan): remember it and create the plain meeting.
					set_transient( $this->tfhb_registration_unsupported_key(), 1, DAY_IN_SECONDS );
					$retry = wp_remote_post(
						'https://api.zoom.us/v2/users/me/meetings',
						array(
							'body'    => wp_json_encode( $plain_data[ $index ] ),
							'headers' => array(
								'Authorization' => 'Bearer ' . $access_response['access_token'],
								'Content-Type'  => 'application/json',
							),
						)
					);
					if ( is_wp_error( $retry ) ) {
						return $retry;
					}
					$response_body = json_decode( wp_remote_retrieve_body( $retry ), true );
				} elseif ( ! self::tfhb_meeting_uses_registration( $response_body ) ) {
					// Created, but Zoom ignored the registration settings: same as unsupported.
					set_transient( $this->tfhb_registration_unsupported_key(), 1, DAY_IN_SECONDS );
				}
			}

			$zoom_event_body[] = $response_body;
		}
		$bookingMetaData = array(
			'booking_id' => $booking->booking_id,
			'meta_key'   => 'zoom_meeting', // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'value'      => wp_json_encode( $zoom_event_body, true ),
		);
 
		$BookingMeta = new BookingMeta();
		$BookingMeta->add( $bookingMetaData );

		// Registration meetings: give this attendee a personal join link.
		$this->tfhb_register_zoom_attendee( $booking, $zoom_event_body );

		$zoom_link = '';
		$password = '';
		foreach ( $zoom_event_body as $key => $mvalue ) {
			$zoom_link .=  $mvalue['join_url'] . ' | ';
			$password .=  $mvalue['password'] . ' | ';
		} 
		$address = array(
			'link' => $zoom_link,
			'password' => $password,
		);
		
		return $address;
 
	}

	public function update_zoom_meeting( $meeting_schedule_id, $booking_meta, $meeting_meta, $host_meta ) {
		
		$this->setHostApiDetails( $booking_meta->host_id );
		$access_response = $this->generateAccessToken();

		$data = $this->zoomMeetingBody( $booking_meta );

		$response = wp_remote_request(
			'https://api.zoom.us/v2/meetings/' . $meeting_schedule_id,
			array(
				'method'  => 'PATCH',
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_response['access_token'],
					'Content-Type'  => 'application/json',
				),
			)
		);

		// Handle the response
		if ( is_wp_error( $response ) ) {
			return $response; // Return the WP_Error object
		}

		$response_body = wp_remote_retrieve_body( $response );

		return $response_body;
	}



 

	/**
	 * Group meetings use Zoom registration when the host's plan allows it: one
	 * meeting, and every attendee gets a personal join link to it, so a canceled
	 * attendee can be blocked by email without affecting the others.
	 *
	 * Registration needs a licensed (Pro or higher) Zoom host. On a Basic plan
	 * Zoom refuses it; the plugin then remembers that for a day and keeps using
	 * the shared meeting link (the behaviour before this feature).
	 *
	 * @param object $booking Attendee booking row.
	 * @return bool
	 */
	public function tfhb_group_registration_wanted( $booking ) {
		$type = '';
		if ( isset( $booking->booking_type ) && '' !== (string) $booking->booking_type ) {
			$type = $booking->booking_type;
		} elseif ( isset( $booking->meeting_type ) ) {
			$type = $booking->meeting_type;
		}
		if ( 'one-to-group' !== $type ) {
			return false;
		}
		if ( get_transient( $this->tfhb_registration_unsupported_key() ) ) {
			return false;
		}
		return (bool) apply_filters( 'hydra_booking/zoom_group_registration', true, $booking );
	}

	/** Transient key remembering that this Zoom account cannot use registration. */
	private function tfhb_registration_unsupported_key() {
		return 'tfhb_zoom_reg_unsupported_' . md5( (string) $this->account_id );
	}

	/** Whether a stored / returned Zoom meeting has registration enabled. */
	public static function tfhb_meeting_uses_registration( $event ) {
		return is_array( $event )
			&& ! empty( $event['id'] )
			&& isset( $event['settings']['approval_type'] )
			&& in_array( (int) $event['settings']['approval_type'], array( 0, 1 ), true );
	}

	/** Bearer token for the current API details, generated once per request. */
	private function tfhb_bearer_token() {
		if ( empty( $this->access_token ) ) {
			$this->generateAccessToken();
		}
		return (string) $this->access_token;
	}

	/**
	 * Register an attendee for every registration-enabled Zoom meeting of the
	 * booking and store their personal join links (booking meta `zoom_registrants`).
	 *
	 * @param object $booking Attendee booking row (id, booking_id, email, attendee_name).
	 * @param array  $events  Zoom meetings as stored in the `zoom_meeting` booking meta.
	 * @return array Stored entries for this attendee (empty when nothing was registered).
	 */
	public function tfhb_register_zoom_attendee( $booking, $events ) {
		if ( ! is_object( $booking ) || empty( $booking->email ) || empty( $booking->booking_id ) ) {
			return array();
		}

		$name       = trim( isset( $booking->attendee_name ) ? (string) $booking->attendee_name : '' );
		$parts      = '' !== $name ? preg_split( '/\s+/', $name, 2 ) : array( $booking->email );
		$first_name = $parts[0];
		$last_name  = isset( $parts[1] ) ? $parts[1] : '';
		$token      = '';
		$entries    = array();

		foreach ( (array) $events as $event ) {
			if ( ! self::tfhb_meeting_uses_registration( $event ) ) {
				continue;
			}
			if ( '' === $token ) {
				$token = $this->tfhb_bearer_token();
			}

			$response = wp_remote_post(
				'https://api.zoom.us/v2/meetings/' . rawurlencode( (string) $event['id'] ) . '/registrants',
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode(
						array(
							'email'        => $booking->email,
							'first_name'   => $first_name,
							'last_name'    => $last_name,
							// Meetings are created with manual approval so strangers who
							// register themselves wait for the host; our attendees are approved now.
							'auto_approve' => true,
						)
					),
					'timeout' => 20,
				)
			);
			if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 300 ) {
				continue;
			}
			$body          = json_decode( wp_remote_retrieve_body( $response ), true );
			$registrant_id = is_array( $body ) && ! empty( $body['registrant_id'] ) ? (string) $body['registrant_id'] : '';
			$join_url      = is_array( $body ) && ! empty( $body['join_url'] ) ? (string) $body['join_url'] : '';
			if ( '' === $registrant_id ) {
				continue;
			}

			// Zoom may leave join_url out for manually approved meetings: read it back.
			if ( '' === $join_url ) {
				$lookup = wp_remote_request(
					'https://api.zoom.us/v2/meetings/' . rawurlencode( (string) $event['id'] ) . '/registrants/' . rawurlencode( $registrant_id ),
					array(
						'method'  => 'GET',
						'headers' => array( 'Authorization' => 'Bearer ' . $token ),
						'timeout' => 20,
					)
				);
				if ( ! is_wp_error( $lookup ) && (int) wp_remote_retrieve_response_code( $lookup ) < 300 ) {
					$lookup_body = json_decode( wp_remote_retrieve_body( $lookup ), true );
					$join_url    = is_array( $lookup_body ) && ! empty( $lookup_body['join_url'] ) ? (string) $lookup_body['join_url'] : '';
				}
			}

			$entries[] = array(
				'meeting_id'    => (string) $event['id'],
				'registrant_id' => $registrant_id,
				'join_url'      => $join_url,
				'email'         => (string) $booking->email,
			);
		}

		if ( ! empty( $entries ) ) {
			$this->tfhb_save_registrants( $booking->booking_id, $booking->id, $entries );
		}
		return $entries;
	}

	/**
	 * Block one attendee from the shared Zoom meeting by canceling their
	 * registration (their personal link stops being valid); others are untouched.
	 *
	 * @param object   $attendee   Attendee booking row.
	 * @param int|null $booking_id Booking row holding the registration (defaults to the attendee's).
	 * @return bool True when at least one registration was canceled.
	 */
	public function tfhb_cancel_zoom_registrant( $attendee, $booking_id = null ) {
		if ( ! is_object( $attendee ) || empty( $attendee->id ) ) {
			return false;
		}
		$booking_id = $booking_id ? $booking_id : ( isset( $attendee->booking_id ) ? $attendee->booking_id : 0 );
		$map        = $this->tfhb_get_registrants( $booking_id );
		$key        = (string) $attendee->id;
		if ( empty( $map[ $key ] ) ) {
			return false;
		}

		if ( ! empty( $attendee->host_id ) ) {
			$this->setHostApiDetails( $attendee->host_id );
		}
		$token    = $this->tfhb_bearer_token();
		$canceled = false;

		foreach ( (array) $map[ $key ] as $entry ) {
			if ( empty( $entry['meeting_id'] ) ) {
				continue;
			}
			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . rawurlencode( (string) $entry['meeting_id'] ) . '/registrants/status',
				array(
					'method'  => 'PUT',
					'headers' => array(
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode(
						array(
							'action'      => 'cancel',
							'registrants' => array(
								array(
									'id'    => isset( $entry['registrant_id'] ) ? $entry['registrant_id'] : '',
									'email' => isset( $entry['email'] ) ? $entry['email'] : '',
								),
							),
						)
					),
					'timeout' => 20,
				)
			);
			if ( ! is_wp_error( $response ) && (int) wp_remote_retrieve_response_code( $response ) < 300 ) {
				$canceled = true;
			}
		}

		unset( $map[ $key ] );
		$this->tfhb_put_registrants( $booking_id, $map );
		return $canceled;
	}

	/** Registrations of a booking: [ attendee_id => [ {meeting_id, registrant_id, join_url, email} ] ]. */
	public function tfhb_get_registrants( $booking_id ) {
		$meta = ( new BookingMeta() )->getWithIdKey( $booking_id, 'zoom_registrants', 1 );
		if ( ! $meta ) {
			return array();
		}
		$map = json_decode( is_string( $meta->value ) ? $meta->value : wp_json_encode( $meta->value ), true );
		return is_array( $map ) ? $map : array();
	}

	private function tfhb_save_registrants( $booking_id, $attendee_id, $entries ) {
		$map                          = $this->tfhb_get_registrants( $booking_id );
		$map[ (string) $attendee_id ] = $entries;
		$this->tfhb_put_registrants( $booking_id, $map );
	}

	private function tfhb_put_registrants( $booking_id, $map ) {
		$BookingMeta = new BookingMeta();
		$meta        = $BookingMeta->getWithIdKey( $booking_id, 'zoom_registrants', 1 );
		$value       = wp_json_encode( (object) $map );
		if ( $meta ) {
			$BookingMeta->update(
				array(
					'id'    => $meta->id,
					'value' => $value,
				)
			);
		} else {
			$BookingMeta->add(
				array(
					'booking_id' => $booking_id,
					'meta_key'   => 'zoom_registrants', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'value'      => $value,
				)
			);
		}
	}

	public function zoomMeetingBody( $booking, $with_registration = false ) {
		$event_data = array();

		$attendees_data = array();
		$attendees_data[] = array(
			'email' => $booking->host_email,
			'name'  => $booking->host_first_name,
		);
		$attendees_data[] = array(
			'email' => $booking->email,
			'name'  => $booking->attendee_name,
		);
		
		// The Zoom meeting lasts as long as the meeting itself; buffers and intervals are only gaps between bookings
		$duration = (int) $booking->duration;

		$time_zone = BookingTime::booking_tz( $booking );

		foreach ( BookingTime::booking_instants( $booking ) as $occurrence ) {
			$data = array(
				'topic'      => ! empty( $booking->title ) ? $booking->title : '',
				'type'       => 2, // Scheduled Meeting
				// Absolute UTC start, so the date is right even when UTC is on another day than the host
				'start_time' => BookingTime::iso_utc( $occurrence['start'] ),
				'timezone'   => $time_zone->getName(),
				'duration'   => $duration,
				'password'   => function_exists( 'wp_generate_password' ) ? wp_generate_password( 8, false, false ) : ( function_exists( 'wp_rand' ) ? substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 ) : substr( bin2hex( random_bytes( 4 ) ), 0, 8 ) ),
				'settings'   => array(
					'join_before_host' => false,
					'mute_upon_entry'  => true,
					'waiting_room'     => true, 
					'meeting_invitees' => $attendees_data,
				),
				'contact_email' => $booking->host_email,
				'contact_name'  => $booking->host_email,
			);

			if ( $with_registration ) {
				// One meeting, a personal join link per attendee (see tfhb_group_registration_wanted()).
				$data['settings']['approval_type']                  = 1; // manual: self-registered strangers wait for the host
				$data['settings']['registration_type']              = 1;
				$data['settings']['registrants_confirmation_email'] = false; // Hydra sends its own emails
				$data['settings']['registrants_email_notification'] = false;
				$data['settings']['allow_multiple_devices']         = false; // a personal link works on one device at a time
			}

			$event_data[] = $data;
		}
		
		return $event_data;
	}


	// zoom reshcedule meeting 

}
