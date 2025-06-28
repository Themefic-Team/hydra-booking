<?php
namespace HydraBooking\Hooks; 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; } 

use HydraBooking\DB\BookingMeta;
class BookingActivityHandler {
 // Use a static variable to collect all activities in this request.
    private static $activities = array();


    /**
     * Call this from each integration hook to register an activity.
     *
     * @param array $activity_data Array of data for the booking activity.
     */
    public static function add_activity($activity_data) {
         if(self::is_booking_Activity() == false){
            return;
        }
        
        self::$activities[] = $activity_data;
    }

    public static function get_activity() {
        tfhb_print_r(self::$activities);
        exit;
    }

    // checked booking is_booking_Activity 
    public static function is_booking_Activity() {
        $_tfhb_general_settings = get_option( '_tfhb_general_settings' );
        $is_bokking_activity = isset($_tfhb_general_settings['is_bokking_activity']) ? $_tfhb_general_settings['is_bokking_activity'] : 1; 

        if($is_bokking_activity == 1) {
            return true;
        } else {
            return false;
        }
    }


     /**
     * Perform a bulk insert of all collected activities.
     */
    public static function bulk_insert() {
        if(self::is_booking_Activity() == false){
            return;
        }

        if ( empty(self::$activities) ) {
            return;
        }  
        
        $BookingMeta = new BookingMeta();
        $BookingMeta->bulkInsert(self::$activities);
        // Empty the collected activities.
        self::$activities = array();
    }
}
