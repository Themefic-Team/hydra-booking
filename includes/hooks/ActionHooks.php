<?php
namespace HydraBooking\Hooks;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;
use HydraBooking\Admin\Controller\HostsController;

class ActionHooks {

	public function __construct() { 
		
		// Google Calendar
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' ); 
		$google_calendar            = isset( $_tfhb_integration_settings['google_calendar'] ) ? $_tfhb_integration_settings['google_calendar'] : array();

		if(!empty($google_calendar)){
			add_action( 'hydra_booking/after_booking_completed', array( new GoogleCalendar(), 'insert_calender_after_booking_completed' ), 10, 2 ); 
			add_action( 'hydra_booking/after_booking_canceled', array( new GoogleCalendar(), 'deleteGoogleCalender' ), 10, 2 );
			add_action( 'hydra_booking/after_booking_schedule', array( new GoogleCalendar(), 'UpdateGoogleCalender' ), 10, 2 );
		}

		// host update email 
		add_action('profile_update', array(new HostsController(), 'update_host_email'), 10, 2);
	}

 
}
