<?php
namespace HydraBooking\Hooks;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;

class ActionHooks {

	public function __construct() { 
		
		// Google Calendar
 		add_action( 'hydra_booking/after_booking_completed', array( new GoogleCalendar(), 'insert_calender_after_booking_completed' ), 10, 2 ); 
		add_action( 'hydra_booking/after_booking_canceled', array( new GoogleCalendar(), 'deleteGoogleCalender' ), 10, 2 );
 		add_action( 'hydra_booking/after_booking_schedule', array( new GoogleCalendar(), 'UpdateGoogleCalender' ), 10, 2 );
		
	}

 
}
