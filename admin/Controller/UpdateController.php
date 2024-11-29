<?php
namespace HydraBooking\Admin\Controller;

use HydraBooking\Admin\Controller\TransStrings;
use HydraBooking\Admin\Controller\AuthController;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Booking;

	// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

class UpdateController {

    public $version  = THB_VERSION; 

	// constaract
	public function __construct() {  

        // update version 1.0.8 to 1.0.9.
        // $this->update_1_0_9();
		 
	} 

    /**
     * Update Database table structure
     * update version 1.0.8 to 1.0.9
     * @author Sydur Rahman
     * @since 1.0.9
     */

     public function update_1_0_9() { 

        $tfhb_update_status = get_option('tfhb_update_status', false); 
          
        if( $this->version == '1.0.4' && $tfhb_update_status != '1.0.4' ) { 
            // tfhb_print_r('Update 1.0.5 to 1.0.6');
            $Attendees = new Attendees();
            // $Attendees->migrate();

            // get all booking data
            $Booking = new Booking(); 
            $bookings = $Booking->get(); 
            // add data to attendees table
            foreach( $bookings as $key => $booking ) { 
                $data = array(
                    'booking_id' => $booking->id,
                    'meeting_id' => $booking->meeting_id,
                    'host_id' => $booking->host_id,
                    'hash' => $booking->hash,
                    'attendee_time_zone' => $booking->attendee_time_zone,
                    'attendee_name' => $booking->attendee_name,
                    'email' => $booking->email,
                    'address' => $booking->address,
                    'others_info' => json_decode($booking->others_info),
                    'country' => $booking->country,
                    'ip_address' => $booking->ip_address,
                    'device' => $booking->device,
                    'cancelled_by' => $booking->cancelled_by,
                    'status' => $booking->status,
                    'reason' => $booking->reason,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                );

                // $Attendees->add($data);

            }


            // Delete Column from booking table
            global $wpdb;
            $table_name = $wpdb->prefix . 'tfhb_bookings';
            // drop column in one query
            // $wpdb->query("ALTER TABLE $table_name 
            //     DROP COLUMN order_id, 
            //     DROP COLUMN attendee_time_zone, 
            //     DROP COLUMN attendee_name, 
            //     DROP COLUMN email, 
            //     DROP COLUMN address, 
            //     DROP COLUMN others_info, 
            //     DROP COLUMN country, 
            //     DROP COLUMN ip_address, 
            //     DROP COLUMN device, 
            //     DROP COLUMN payment_method,
            //     DROP COLUMN payment_status"
            // );
            // tfhb_print_r($wpdb->last_query);

            // update version
            // update_option('tfhb__update_status', '1.0.4');

            
            
        }
        
     }

}
