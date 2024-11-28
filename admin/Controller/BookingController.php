<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;

// Use DB
use HydraBooking\DB\Booking;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Meeting;

 

class BookingController {


	// constaract
	public function __construct() {
	}

	public function init() {
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/booking/lists',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getBookingsData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/create',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'CreateBooking' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeleteBooking' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		// Get Single Booking based on id
		register_rest_route(
			'hydra-booking/v1',
			'/booking/(?P<id>[0-9]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getBookingData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateBooking' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/bulk-update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateBulkStatus' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		// booking reminder email.
		register_rest_route(
			'hydra-booking/v1',
			'/booking/send-reminder',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'sendReminderEmail' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		// Pre Booking Data
		register_rest_route(
			'hydra-booking/v1',
			'/booking/pre',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getPreBookingsData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/meeting',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getpreMeetingData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/availabletime',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getAvailableTimeData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		// Export Booking Data as csv
		register_rest_route(
			'hydra-booking/v1',
			'/booking/export-csv',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'exportBookingDataCSV' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		// Filter Booking
		register_rest_route(
			'hydra-booking/v1',
			'/booking/filter',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'filterBookings' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
				'args'     => array(
					'title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}


	// Booking List
	public function getBookingsData() {

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		// Booking Lists
		$booking = new Booking();

		if ( ! empty( $current_user_role ) && 'administrator' == $current_user_role ) {
			$bookingsList = $booking->get( null, true );
		}
		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host     = new Host();
			$HostData = $host->getHostByUserId( $current_user_id );

			$bookingsList = $booking->get( null, true, false, false, false, false, $HostData->user_id );
		}

		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {

			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];

			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'bookings'         => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          => 'Booking Data Successfully Retrieve!',
		);
		return rest_ensure_response( $data );
	}

	// Pre Booking Data
	public function getPreBookingsData() {
		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		$meeting      = new Meeting();
		$MeetingsList = $meeting->get();

		$meeting_array = array();
		foreach ( $MeetingsList as $single ) {
			$meeting_array[] = array(
				'name'  => $single->title,
				'value' => '' . $single->id . '',
			);
		}

		$data = array(
			'status'    => true,
			'time_zone' => $time_zone,
			'meetings'  => $meeting_array,
		);
		return rest_ensure_response( $data );
	}

	// Pre Meeting Data
	public function getpreMeetingData() {
		$request    = json_decode( file_get_contents( 'php://input' ), true );
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';

		// Single Meeting Data
		$meeting      = new Meeting();
		$MeetingsData = $meeting->get( $meeting_id );

		// Meeting Location
		$meeting_locations = ! empty( $MeetingsData->meeting_locations ) ? json_decode( $MeetingsData->meeting_locations ) : array( '' );

		$meeting_location_array = array();
		if ( ! empty( $meeting_locations ) ) {
			foreach ( $meeting_locations as $single ) {
				if ( $single->location ) {
					$meeting_location_array[] = array(
						'name'  => $single->location,
						'value' => '' . $single->location . '',
					);
				}
			}
		}

		// Host List
		$host     = new Host();
		$HostData = $host->get( $MeetingsData->host_id );

		$meeting_host_array = array();
		if ( $HostData->first_name ) {
			$meeting_host_array[] = array(
				'name'  => $HostData->first_name . ' ' . $HostData->last_name,
				'value' => '' . $HostData->id . '',
			);
		}

		// Meeting Information
		$data = get_post_meta( $MeetingsData->post_id, '__tfhb_meeting_opt', true );

		if ( isset( $data['availability_type'] ) && 'settings' === $data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $MeetingsData->host_id, '_tfhb_host', true );
			if ( in_array( $data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				$availability_data = $_tfhb_availability_settings['availability'][ $data['availability_id'] ];
			} else {
				$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
			}
		} else {
			$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
		}

		// Availability Range
		$availability_range      = isset( $data['availability_range'] ) ? $data['availability_range'] : array();
		$availability_range_type = isset( $data['availability_range_type'] ) ? $data['availability_range_type'] : array();

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		// Disable Dates
		$disabled_dates = array();
		if ( $availability_data['date_slots'] != '' ) {
			$date_slots = $availability_data['date_slots'];
			foreach ( $date_slots as $single ) {
				if ( $single['available'] == true ) {
					// string to array
					$dates = explode( ',', $single['date'] );
					foreach ( $dates as $date ) {
						$disabled_dates[] = $date;
					}
				}
			}
		}
		// Disable Unavailable days
		$unavailable_days = isset( $availability_data['time_slots'] ) ? $availability_data['time_slots'] : array();

		// day array based on js date key value
		$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

		$unavailable_days_array = array();

		foreach ( $unavailable_days as $single ) {
			$unavailable_days_array[ $single['day'] ] = $single['status'] == false ? array_search( $single['day'], $days ) : 8;
		}

		// flatpickr configuration only for date not time
		$config = array(
			// 'enableTime' => false,
			'dateFormat'   => 'Y-m-d',
			'minDate'      => 'today',
			'defaultDate'  => 'today',
			'disable'      => $disabled_dates,
			'disable_days' => $unavailable_days_array,
		);

		if ( $availability_range_type != 'indefinitely' ) {
			$config['maxDate'] = $availability_range['end'];
		}

		$data = array(
			'status'         => true,
			'locations'      => $meeting_location_array,
			'hosts'          => $meeting_host_array,
			'available_slot' => $unavailable_days_array,
			'flatpickr_date' => $config,
		);
		return rest_ensure_response( $data );
	}

	// Pre Meeting Data
	public function getAvailableTimeData() {
		$request    = json_decode( file_get_contents( 'php://input' ), true );
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';

		$selected_date = isset( $request['date'] ) ? sanitize_text_field( $request['date'] ) : '';

		$selected_time_zone = isset( $request['time_zone'] ) ? sanitize_text_field( $request['time_zone'] ) : 'UTC';

		$selected_time_format = '12';

		$date_time = new DateTimeController( $selected_time_zone );
		$data      = $date_time->getAvailableTimeData( $meeting_id, $selected_date, $selected_time_zone, $selected_time_format );

		return rest_ensure_response(
			array(
				'status'          => true,
				'time_slots_data' => $data,
			)
		);
	}

	// Booking Filter
	public function filterBookings( $request ) {
		$filterData = $request->get_param( 'filterData' );

		// Booking Lists
		$booking      = new Booking();
		$bookingsList = $booking->getFilter( $filterData );
		
		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {

			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];

			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'bookings'         => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          => 'Booking Data Successfully Retrieve!',
		);
		return rest_ensure_response( $data );
	}

	// Create Booking
	public function CreateBooking() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Check if user is already a booking
		$booking = new Booking();

		if ( ! empty( $request['id'] ) ) {
			$data = array(
				'id'                 => isset( $request['id'] ) ? $request['id'] : '',
				'meeting_id'         => isset( $request['meeting'] ) ? $request['meeting'] : '',
				'attendee_name'      => isset( $request['name'] ) ? $request['name'] : '',
				'email'              => isset( $request['email'] ) ? $request['email'] : '',
				'attendee_time_zone' => isset( $request['time_zone'] ) ? $request['time_zone'] : '',
				'start_time'         => isset( $request['time']['start'] ) ? $request['time']['start'] : '',
				'end_time'           => isset( $request['time']['end'] ) ? $request['time']['end'] : '',
				'meeting_dates'      => isset( $request['date'] ) ? $request['date'] : '',
				'status'             => isset( $request['status'] ) ? $request['status'] : '',
			);

			// Booking Update
			$bookingUpdate = $booking->update( $data );
		} else {
			$data = array(
				'meeting_id'         => isset( $request['meeting'] ) ? $request['meeting'] : '',
				'attendee_name'      => isset( $request['name'] ) ? $request['name'] : '',
				'email'              => isset( $request['email'] ) ? $request['email'] : '',
				'attendee_time_zone' => isset( $request['time_zone'] ) ? $request['time_zone'] : '',
				'host_id'            => isset( $request['host'] ) ? $request['host'] : '',
				'meeting_dates'      => isset( $request['date'] ) ? $request['date'] : '',
				'start_time'         => isset( $request['time']['start'] ) ? $request['time']['start'] : '',
				'end_time'           => isset( $request['time']['end'] ) ? $request['time']['end'] : '',
				'status'             => isset( $request['status'] ) ? $request['status'] : '',
				'payment_method'     => 'backend',
				'payment_status'     => 'pending',
			);

			// Insert booking
			$bookingInsert = $booking->add( $data );
			if ( ! $bookingInsert['status'] ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' => 'Error while creating Booking',
					)
				);
			}
		}

		$single_booking_meta = $booking->get(
			array( 'id' => $request['id'] ),
			false,
		);

		if ( 'approved' == $request['status'] ) {
			do_action( 'hydra_booking/after_booking_completed', $single_booking_meta );
		}

		if ( 'canceled' == $request['status'] ) { 
			do_action( 'hydra_booking/after_booking_canceled', $single_booking_meta );
		}

		if ( 'schedule' == $request['status'] ) {
			do_action( 'hydra_booking/after_booking_schedule', $single_booking_meta );
		}


		// booking Lists
		$booking_List = $booking->get();
		// Return response
		$data = array(
			'status'  => true,
			'booking' => $booking_List,
			'message' => ! empty( $request['id'] ) ? 'Booking Updated Successfully' : 'Booking Created Successfully',
		);

		return rest_ensure_response( $data );
	}

	// Delete Booking
	public function DeleteBooking() {

		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$booking_id    = $request['id'];
		$booking_owner = $request['host'];

		if ( empty( $booking_id ) || $booking_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => 'Invalid Booking',
				)
			);
		}
		// Delete Booking
		$booking       = new Booking();
		$bookingDelete = $booking->delete( $booking_id );
		$current_user  = get_userdata( $booking_owner );
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		if ( ! empty( $current_user_role ) && 'administrator' == $current_user_role ) {
			$bookingsList = $booking->get( null, true );
		}
		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host         = new Host();
			$HostData     = $host->getHostByUserId( $current_user_id );
			$bookingsList = $booking->get( null, true, false, false, false, false, $HostData->host_id );

		}

		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {
			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );
		 


			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];
			
			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'bookings'         => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          => 'Booking Data Successfully Deleted!',
		);
		return rest_ensure_response( $data );
	}

	// Send Reminder Email
	public function sendReminderEmail(){
		$request = json_decode( file_get_contents( 'php://input' ), true ); 

		$booking_id =  isset( $request['booking_id'] ) ? $request['booking_id'] : 0;
		if( empty( $booking_id ) && $booking_id == 0 ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => 'Invalid Booking',
				)
			);
		}

		// Get Booking Data
		$booking = new Booking(); 
		$single_booking_meta = $booking->get(
			array( 'id' => $booking_id ),
			false,
		);

		if( empty( $single_booking_meta ) ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => 'Invalid Booking',
				)
			);
		}

		if( 'confirmed' != $single_booking_meta->booking_status ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => 'This Booking is not Confirmed',
				)
			);
		}

		do_action( 'hydra_booking/send_booking_reminder', $single_booking_meta );

		// Return response
		$data = array(
			'status'  => true,
			'message' => 'Reminder Email Sent Successfully!',
		);
		return rest_ensure_response( $data );
	}

	// Get Single Booking
	public function getBookingData( $request ) {
		$booking_id = $request['id'];

		// Check if user is already a booking
		$booking = new Booking();
		// Insert booking
		$singlebooking = $booking->get(
			array( 'id' => $booking_id ),
			false,
			true
		);

		$meeting_id   = $singlebooking->meeting_id;
		$meeting      = new Meeting();
		$MeetingsData = $meeting->get( $meeting_id );

		$selected_date = $singlebooking->meeting_dates;

		$selected_time_zone = $singlebooking->attendee_time_zone;

		$selected_time_format = '12';
		// Meeting Information
		$data = get_post_meta( $MeetingsData->post_id, '__tfhb_meeting_opt', true );

		if ( isset( $data['availability_type'] ) && 'settings' === $data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $MeetingsData->host_id, '_tfhb_host', true );
			if ( in_array( $data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				$availability_data = $_tfhb_availability_settings['availability'][ $data['availability_id'] ];
			} else {
				$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
			}
		} else {
			$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
		}

		// Disable Unavailable days
		$time_slots = isset( $availability_data['time_slots'] ) ? $availability_data['time_slots'] : array();

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		// Disable Dates

		// Get All Booking Data.
		$bookings = $booking->get( array( 'meeting_dates' => $selected_date ) );
		// $date_time = new DateTimeController( $selected_time_zone );
		$date_time = new DateTimeController( $selected_time_zone );
		$data_time = $date_time->getAvailableTimeData( $meeting_id, $selected_date, $selected_time_zone, $selected_time_format );

	 

		$singlebooking->times = array(
			'start' => $singlebooking->start_time,
			'end'   => $singlebooking->end_time,
		);
		// Return response
		$data = array(
			'status'  => true,
			'booking' => $singlebooking,
			'times'   => $data_time,
			'message' => 'Booking Data',
		);
		return rest_ensure_response( $data );
	}

	// Update Booking Information
	public function updateBooking() {
		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$booking_id    = $request['id'];
		$booking_owner = $request['host'];

		if ( empty( $booking_id ) || $booking_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => 'Invalid Booking',
				)
			);
		}

		$data = array(
			'id'     => $request['id'],
			'status' => isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : '',
		);

		$booking = new Booking();
		// Booking Update
		$bookingUpdate = $booking->update( $data );

		$host = new Host();
		$hostData = $host->getHostById( $booking_owner );

		$current_user = get_userdata( $hostData->user_id );
		$booking = new Booking();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		if ( ! empty( $current_user_role ) && 'administrator' == $current_user_role ) {
			$bookingsList = $booking->get( null, true );
		}
		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host         = new Host();
			$HostData     = $host->getHostByUserId( $current_user_id );
			$bookingsList = $booking->get( null, true, false, false, false, false, $HostData->host_id ); 

		} 
		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {
			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];

			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Single Booking
		// $single_booking_meta = $booking->get(['id'=>$request['id']],false, true);
		// $single_booking_meta = $booking->get($request['id'], false, true);
		$single_booking_meta = $booking->get(
			array( 'id' => $request['id'] ),
			false,
		);

		if ( 'confirmed' == $request['status'] ) {
		 
			do_action( 'hydra_booking/after_booking_completed', $single_booking_meta );
		}

		if ( 'pending' == $request['status'] ) {
			do_action( 'hydra_booking/after_booking_pending', $single_booking_meta );
		}
		if ( 'canceled' == $request['status'] ) { 
			do_action( 'hydra_booking/after_booking_canceled', $single_booking_meta );
		}

		if ( 'schedule' == $request['status'] ) {
			do_action( 'hydra_booking/after_booking_schedule', $single_booking_meta );
		}

		// Return response
		$data = array(
			'status'           => true,
			'booking'          => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          => 'Booking Updated Successfully',
		);
		return rest_ensure_response( $data );
	}

	// Update Booking Bulk Option
	public function updateBulkStatus() {
		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$status    = $request['status'];
		$items    = $request['items'];
		$booking_owner = !empty($request['host']) ? $request['host'] : '';


		$booking = new Booking();
		if($status == 'delete'){
			if(!empty($items)){
				foreach($items as $item){
					$bookingDelete = $booking->delete( $item );
				}
			}

		}else{
			if(!empty($items)){
				foreach($items as $item){
					$data = array(
						'id'     => $item,
						'status' => isset( $status ) ? sanitize_text_field( $status ) : '',
					);
	
					// Booking Update
					$bookingUpdate = $booking->update( $data );
				}
			}
		}
		

		if(!empty($booking_owner)){
			$current_user = get_userdata( $booking_owner );
			// get user role
			$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
			$current_user_id   = $current_user->ID;
		}

		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host         = new Host();
			$HostData     = $host->getHostByUserId( $current_user_id );
			$bookingsList = $booking->get( null, true, false, false, false, false, $HostData->user_id );

		}else{
			$bookingsList = $booking->get( null, true );
		}

		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {
			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];
			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'booking'          => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          => 'Booking Updated Successfully',
		);
		return rest_ensure_response( $data );
	}

	// Export Booking Data as CSV.
	public function exportBookingDataCSV() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		// 2024-07-03 23:48:25
		$time         = '00:00:00';
		$current_time = '23:59:59';
		// Get Current Date baded on time
		$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
		$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( $current_date ) ) );

		$booking = new Booking();
		if ( ! empty( $request['date_range'] == 'custom' ) ) {
			// in this format 2024-07-03 23:48:25 form 2024-07-03 request['start_date'] variable
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $request['end_date'] ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( $request['start_date'] ) );

		} elseif ( $request['date_range'] == 'today' ) {
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( $current_date ) ) );
		} elseif ( $request['date_range'] == 'weeks' ) {
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-7 day', strtotime( $current_date ) ) );
		} elseif ( $request['date_range'] == 'months' ) {  // current month
			// This month end date
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( 'last day of this month', strtotime( $current_time ) ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( 'first day of last month', strtotime( $current_time ) ) );
		} elseif ( $request['date_range'] == 'years' ) {  // current year
			// This year end date
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( 'last day of this year', strtotime( $current_time ) ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( 'first day of last year', strtotime( $current_time ) ) );
		}
		if ( $request['date_range'] == 'all' ) {
			$file_name = 'booking-data.csv';

		} else {
			$file_name = 'booking-data-' . gmdate( 'Y-m-d', strtotime( $previous_date ) ) . '-' . gmdate( 'Y-m-d', strtotime( $current_date ) ) . '.csv';

		}
	 

		if ( $request['date_range'] == 'all' ) {
			$bookingsList = $booking->export();
		} else {
			$bookingsList = $booking->export(
				array(
					array(
						'column'   => 'created_at',
						'operator' => 'BETWEEN',
						'value'    => "'" . $previous_date . "' AND  '" . $current_date . "'",
					),
				)
			);
		}

		$booking_array  = array();
		$booking_column = array();
		foreach ( $bookingsList as $key => $book ) {
			if ( $key == 0 ) {
				foreach ( $book as $c_key => $c_value ) {
					$booking_column[] = $c_key;
				}
			}
			$booking_array[] = (array) $book;
		} 

		ob_start();
		$file = fopen( 'php://output', 'w' );
		fputcsv( $file, $booking_column );

		foreach ( $booking_array as $booking ) {
			fputcsv( $file, $booking );
		}

		fclose( $file );
		$data = ob_get_clean();
		// Return response
		$data = array(
			'status'    => true,
			'data'      => $data,
			'file_name' => $file_name,
			'message'   => 'Booking Data Exported Successfully',
		);
		return rest_ensure_response( $data );
	}
}
