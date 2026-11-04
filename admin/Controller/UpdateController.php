<?php
namespace HydraBooking\Admin\Controller;

use HydraBooking\Admin\Controller\TransStrings;
use HydraBooking\Admin\Controller\AuthController;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Booking;

	// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

class UpdateController {

    public $version  = TFHB_VERSION; 

	// constaract
	public function __construct() {  

        // update version 1.0.12 to 1.0.13.
        $this->update_1_0_13();

        // Remove it after few releases
        $this->tfhb_check_and_add_upload_cap();

        // Remove it after few releases
        $this->tfhb_check_and_remove_host_settings_cap();

        $this->tfhb_security_patch_remove_manage_options_cap(); // Security fix v1.2.4
        $this->tfhb_security_patch_strip_cap_from_host_users(); // Security fix v1.2.4

        $this->tfhb_backfill_booking_time_zones();
	}

	/**
	 * Fill in availability_time_zone for bookings stored without one (made before 1.0.14 or by
	 * the old admin create form), so every booking says which timezone its times are in.
	 * Runs in batches until none are left, then never again.
	 */
	public function tfhb_backfill_booking_time_zones() {
	    if ( get_option( 'tfhb_booking_time_zone_backfill_done' ) ) {
	        return;
	    }

	    global $wpdb;
	    $booking_table = $wpdb->prefix . 'tfhb_bookings';
	    $host_table    = $wpdb->prefix . 'tfhb_hosts';

	    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	    $rows = $wpdb->get_results(
	        "SELECT b.id, b.meeting_id, h.time_zone AS host_time_zone
	         FROM $booking_table AS b
	         LEFT JOIN $host_table AS h ON h.id = b.host_id
	         WHERE b.availability_time_zone IS NULL OR b.availability_time_zone = ''
	         LIMIT 200"
	    );

	    if ( empty( $rows ) ) {
	        update_option( 'tfhb_booking_time_zone_backfill_done', true );
	        return;
	    }

	    $meeting       = new \HydraBooking\DB\Meeting();
	    $date_time     = new DateTimeController( 'UTC' );
	    $meeting_zones = array();

	    foreach ( $rows as $row ) {
	        if ( ! array_key_exists( $row->meeting_id, $meeting_zones ) ) {
	            $meeting_data      = $meeting->get( $row->meeting_id );
	            $availability_data = $meeting_data ? $date_time->GetAvailabilityData( $meeting_data ) : array();
	            $meeting_zones[ $row->meeting_id ] = isset( $availability_data['time_zone'] ) ? $availability_data['time_zone'] : '';
	        }

	        // The meeting's availability timezone, then the host's profile one, then the site's
	        $time_zone = BookingTime::tz( $meeting_zones[ $row->meeting_id ], $row->host_time_zone )->getName();
	        $wpdb->update( $booking_table, array( 'availability_time_zone' => $time_zone ), array( 'id' => $row->id ) );
	    }
	    // phpcs:enable
	}

    /**
     * Update Database table structure
     * update version 1.0.8 to 1.0.9
     * @author Sydur Rahman
     * @since 1.0.9
     */

     public function update_1_0_13() { 

        $tfhb_update_status = get_option('tfhb_update_status', false); 
          
        if( $this->version == '1.0.14' && $tfhb_update_status != '1.0.14' ) { 

            // Add column in transaction table
            global $wpdb;
            $table_name = $wpdb->prefix . 'tfhb_transactions';
            // add column in one query
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            if( $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'booking_id'") != 'attendees_id' ) {
                $wpdb->query("ALTER TABLE $table_name ADD attendee_id INT(11) NOT NULL AFTER id");
            }
            // phpcs:enable
            // tfhb_print_r('Update 1.0.5 to 1.0.6');
            $Attendees = new Attendees();
            $Attendees->migrate();

            // get all booking data
            $Booking_data = new Booking(); 
            $bookings = $Booking_data->get(); 
            // add data to attendees table
            foreach( $bookings as $key => $booking ) {  
                $booking_id = $booking->id;
                $check_booking = $Booking_data->get(
					array(
						'meeting_id'    => $booking->meeting_id,
						'meeting_dates' => $booking->meeting_dates,
						'start_time'    => $booking->start_time,
						'end_time'      => $booking->end_time,
					)
				); 
                if( !empty($check_booking) ) { 
                    if(count($check_booking) > 1) {
                        
                        $get_last_booking = end( $check_booking);
                        
                        if( $get_last_booking->id != $booking->id ) { 

                            $booking_id = $get_last_booking->id;
                            $Booking_data->delete($booking->id);
                        }

                        // $booking->delete($booking->id);
                    }
                }
                

                $data = array(
                    'booking_id' => $booking_id,
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

                $Attendees->add($data);

                if( !empty($check_booking) ) { 
                    if(count($check_booking) > 1) {
                        
                        $get_last_booking = end( $check_booking);
                        
                        if( $get_last_booking->id != $booking->id ) { 
 
                            $Booking_data->delete($booking->id);
                        }
 
                    }
                }
 

            }  

            // Delete Column from booking table
            global $wpdb;
            $table_name = $wpdb->prefix . 'tfhb_bookings';
            // drop column in one query
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            $wpdb->query("ALTER TABLE $table_name 
                DROP COLUMN order_id, 
                DROP COLUMN attendee_time_zone, 
                DROP COLUMN attendee_name, 
                DROP COLUMN email, 
                DROP COLUMN address, 
                DROP COLUMN others_info, 
                DROP COLUMN country, 
                DROP COLUMN ip_address, 
                DROP COLUMN device, 
                DROP COLUMN meeting_calendar,
                DROP COLUMN payment_method,
                DROP COLUMN payment_status,
                -- add column
                ADD COLUMN availability_time_zone  VARCHAR(255) NOT NULL AFTER meeting_dates
                ",
                
            );
            // phpcs:enable

           
            // update version
            update_option('tfhb_update_status', '1.0.14');

            
            
        }
        
     }

    //  update Upload Files Capabilities
     public function tfhb_check_and_add_upload_cap() {
        $role_name = 'tfhb_host'; // Change this to the role you want to modify
        $role = get_role($role_name);

        if ($role && !$role->has_cap('upload_files')) {
            $role->add_cap('upload_files');
        }
    }

    // Remove the explicit `false` value for 'tfhb_manage_settings' from the
    // tfhb_host role, left over from before this capability was made
    // "omitted" instead of "false". An explicit false on one of a user's
    // roles overrides a true granted by another role of theirs (e.g. an
    // administrator who was also auto-added as a host), which was hiding
    // the Settings/Integrations/Notifications menu in the Frontend
    // Dashboard for such users even though they're admins.
    public function tfhb_check_and_remove_host_settings_cap() {
        $role = get_role('tfhb_host');

        if ($role && isset($role->capabilities['tfhb_manage_settings']) && false === $role->capabilities['tfhb_manage_settings']) {
            $role->remove_cap('tfhb_manage_settings');
        }
    }

	/**
	 * Security patch v1.2.4: Remove tfhb_manage_options from tfhb_host role in DB.
	 * @since 1.2.4
	 */
	public function tfhb_security_patch_remove_manage_options_cap() {
	    if ( get_option( 'tfhb_security_patch_role_1_2_4' ) ) {
	        return;
	    }
	    $role = get_role( 'tfhb_host' );
	    if ( $role && $role->has_cap( 'tfhb_manage_options' ) ) {
	        $role->remove_cap( 'tfhb_manage_options' );
	    }
	    update_option( 'tfhb_security_patch_role_1_2_4', true );
	}

	/**
	 * Security patch v1.2.4: Strip tfhb_manage_options from individual user caps.
	 * @since 1.2.4
	 */
	public function tfhb_security_patch_strip_cap_from_host_users() {
	    if ( get_option( 'tfhb_security_patch_users_1_2_4' ) ) {
	        return;
	    }
	    $host_users = get_users( array( 'role' => 'tfhb_host' ) );
	    foreach ( $host_users as $user ) {
	        if ( $user->has_cap( 'tfhb_manage_options' ) ) {
	            $user->remove_cap( 'tfhb_manage_options' );
	        }
	    }
	    update_option( 'tfhb_security_patch_users_1_2_4', true );
	}
}
