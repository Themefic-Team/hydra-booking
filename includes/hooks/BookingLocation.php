<?php
namespace HydraBooking\Hooks;


	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;
use HydraBooking\DB\Booking;
class BookingLocation {
	public function __construct() {
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'addLocation_after_booking_confirmed' ),  20, 1); 
		// add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'addLocation_after_booking_confirmed' ), 10, 1 );
		// add_action( 'hydra_booking/after_booking_canceled', array( $this, 'pushBookingToCanceled' ), 10, 1 );
		// add_action( 'hydra_booking/after_booking_schedule', array( $this, 'pushBookingToscheduled' ), 10, 1 );
		// add_action( 'hydra_booking/send_booking_reminder', array( $this, 'send_booking_reminder' ), 10, 1 );
	
	}

	/**
	 * Add Location to Booking
	 * 
	 * @param int $booking_id
	 * @author Sydur Rahman
	 * @return void
	 */
 

	public function addLocation_after_booking_confirmed( $attendee ) {
		// tfhb_print_r( $attendee );
		$booking_id = $attendee->booking_id;
		$locations = json_decode( $attendee->meeting_locations, true ); 
	

		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' ); 
		foreach($locations as $key => $location){ 
			if($key == 'zoom'){ // Booking Location is Zoom
				if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) && $_tfhb_integration_settings['zoom_meeting']['status'] == true ) {
					$zoom = new ZoomServices();
					$address = $zoom->tfhb_create_zoom_meeting($attendee);  
					if($address){
						$locations['zoom']['address'] = $address;
					}
				}
			}
 
		}  
		

		$update_data = array(
			'id' => $booking_id,
			'meeting_locations' => $locations

		); 

		$updateBooking = new Booking();
		$updateBooking->update($update_data); 




	}

}