<?php
namespace HydraBooking\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) { exit; }


// Use Namespace
use HydraBooking\DB\Meta;
use HydraBooking\Admin\Controller\RouteController;
class Notification {

	public function __construct() {
		// add_action('admin_init', array($this, 'init')); 
        add_action('hydra_booking/after_booking_completed', array($this, 'AddNotification'));
	}
	public function create_endpoint() { 
		register_rest_route(
			'hydra-booking/v1',
			'/notifaction',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getNotification' ),
                'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

        register_rest_route(
			'hydra-booking/v1',
			'/notifaction/markasread',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'MarkAsRead' ),
                'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
	}


	public function getNotification(){
        $current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;
        $data_Query = [];
        if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			 $data_Query[] = array(
                'column'   => 'object_id',
                'operator' => '=',
                'value'    => ''.$current_user_id.'',
             );
		}
        $data_Query[] = array(
            'column'   => 'meta_key',
            'operator' => '=',
            'value'    => "'notification'"
        );
        $meta = new Meta();
        $notifications = $meta->get($data_Query, false, false, 10);
        // count total unread notification
        $total_unread = 0;

        foreach ($notifications as $key => $value) {
       
            $notifications[$key]->value = json_decode($value->value);

            if($value->value->status == 'unread'){
                $total_unread++;
            }
        }

		$data = array(
			'status'     => true, 
			'notifications'     => $notifications, 
			'total_unread'     => $total_unread, 
		);

		return rest_ensure_response( $data );
	}

    // Add Notification
    public function AddNotification($data){ 
     
        $meta = new Meta();

        $value = array( 
            'booking_id' =>  $data->meeting_id,
            'meeting_id' =>  $data->meeting_id, 
            'attendee_id' =>  $data->attendee_id,
            'attendee_name' =>  $data->attendee_name,
            'message' =>  __('Has booked a meeting', 'hydra-booking'),
            'status' => 'unread', 
        );
        $data = array(
            'object_id' => $data->host_id,
            'object_type' => 'booking',
            'meta_key' =>  'notification',
            'value' => json_encode($value), 
        ); 

        $meta->add($data);
    }

    // Mark as Read
    public function MarkAsRead(){
        $request = json_decode( file_get_contents( 'php://input' ), true );

        if(is_array($request)){
            $meta = new Meta();

             foreach ($request as $key => $data) { 
                 $data['value']['status'] = 'read';
                 $data['value'] = json_encode($data['value']);

                $meta->update($data);
            }
        }
        $data = array(
            'status'     => true, 
            'message'     =>  __('Notification Marked as Read', 'hydra-booking'),
        );

        return rest_ensure_response( $data );
    }
    
}
