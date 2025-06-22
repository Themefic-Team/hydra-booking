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
        self::$activities[] = $activity_data;
    }

    public static function get_activity() {
        tfhb_print_r(self::$activities);
        exit;
    }


     /**
     * Perform a bulk insert of all collected activities.
     */
    public static function bulk_insert() {
        if ( empty(self::$activities) ) {
            return;
        }  
        
        $BookingMeta = new BookingMeta();
        $BookingMeta->bulkInsert(self::$activities);
        // Empty the collected activities.
        self::$activities = array();
    }
}
