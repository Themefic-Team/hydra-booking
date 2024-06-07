<?php 
namespace HydraBooking\Admin\Controller;
 
 //  Use Namespace
 use HydraBooking\Admin\Controller\RouteController;
 
 // Use DB 
use HydraBooking\DB\Booking;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Meeting;

if ( ! defined( 'ABSPATH' ) ) { exit; }

  class BookingController {
    

    // constaract
    public function __construct() { 
       
    }

    public function init() {
        
    }

    public function create_endpoint(){
        register_rest_route('hydra-booking/v1', '/booking/lists', array(
            'methods' => 'GET',
            'callback' => array($this, 'getBookingsData'),
        ));  
        register_rest_route('hydra-booking/v1', '/booking/create', array(
            'methods' => 'POST',
            'callback' => array($this, 'CreateBooking'),
        ));  
        register_rest_route('hydra-booking/v1', '/booking/delete', array(
            'methods' => 'POST',
            'callback' => array($this, 'DeleteBooking'),
        ));  
        // Get Single Booking based on id
        register_rest_route('hydra-booking/v1', '/booking/(?P<id>[0-9]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'getBookingData'),
        ));
        register_rest_route('hydra-booking/v1', '/booking/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'updateBooking'),
        ));   

        // Pre Booking Data
        register_rest_route('hydra-booking/v1', '/booking/pre', array(
            'methods' => 'GET',
            'callback' => array($this, 'getPreBookingsData'),
        )); 
        register_rest_route('hydra-booking/v1', '/booking/meeting', array(
            'methods' => 'POST',
            'callback' => array($this, 'getpreMeetingData'),
        ));  
        register_rest_route('hydra-booking/v1', '/booking/availabletime', array(
            'methods' => 'POST',
            'callback' => array($this, 'getAvailableTimeData'),
        ));  
       
    }
    // Booking List
    public function getBookingsData() { 

        $current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
        $current_user_id = $current_user->ID;

        // Booking Lists 
        $booking = new Booking();

        if(!empty($current_user_role) && "administrator"==$current_user_role){
            $bookingsList = $booking->get(null, true);
        }
        if(!empty($current_user_role) && "tfhb_host"==$current_user_role){
            $host = new Host();
            $HostData = $host->get( $current_user_id  );

            $bookingsList = $booking->get(null, true, false, false, false, false, $HostData->id);
        }
        
        // Return response
        $data = array(
            'status' => true, 
            'bookings' => $bookingsList,  
            'message' => 'Booking Data Successfully Retrieve!', 
        );
        return rest_ensure_response($data);
    }  

    // Pre Booking Data
    public function getPreBookingsData(){
        $DateTimeZone = new DateTimeController('UTC');
        $time_zone = $DateTimeZone->TimeZone();

        $meeting = new Meeting();
        $MeetingsList = $meeting->get();

        $meeting_array = array();
        foreach($MeetingsList as $single){
            $meeting_array[] = array(
                'name' => $single->title,
                'value' => "".$single->id."",
            );
        }

        $data = array(
            'status' => true, 
            'time_zone' => $time_zone,
            'meetings' => $meeting_array
        ); 
        return rest_ensure_response($data);
    }

    // Pre Meeting Data
    public function getpreMeetingData(){
        $request = json_decode(file_get_contents('php://input'), true);
        $meeting_id = isset($request['meeting_id']) ? $request['meeting_id'] : '';

        // Single Meeting Data
        $meeting = new Meeting();
        $MeetingsData = $meeting->get($meeting_id);
        
        // Meeting Location
        $meeting_locations = !empty($MeetingsData->meeting_locations) ? json_decode($MeetingsData->meeting_locations) : [''];

        $meeting_location_array = array();
        if(!empty($meeting_locations)){
            foreach($meeting_locations as $single){
                if($single->location){
                    $meeting_location_array[] = array(
                        'name' => $single->location,
                        'value' => "".$single->location."",
                    );
                }
            }
        }

        // Host List
        $host = new Host();
        $HostData = $host->get( $MeetingsData->host_id  );

        $meeting_host_array = array();
        if($HostData->first_name){
            $meeting_host_array[] = array(
                'name' => $HostData->first_name.' '.$HostData->last_name,
                'value' => "".$HostData->id."",
            );
        }

        // Meeting Information
        $data = get_post_meta($MeetingsData->post_id, '__tfhb_meeting_opt', true);

        if( isset($data['availability_type']) && 'settings' === $data['availability_type']){  
            $_tfhb_availability_settings = get_user_meta($host_id, '_tfhb_host', true); 
            if(in_array($data['availability_id'], array_keys($_tfhb_availability_settings['availability']))){
                $availability_data = $_tfhb_availability_settings['availability'][$data['availability_id']]; 
            }else{
                $availability_data = isset($data['availability_custom']) ? $data['availability_custom'] : array(); 
            } 
         
        }else{ 
            $availability_data = isset($data['availability_custom']) ? $data['availability_custom'] : array(); 
        }
        
        // Availability Range
        $availability_range = isset($data['availability_range']) ? $data['availability_range'] : array();
        $availability_range_type = isset($data['availability_range_type']) ? $data['availability_range_type'] : array();

        // Duration
        $duration = isset($data['duration']) && !empty($data['duration'])? $data['duration'] : 30;

        $duration = isset($data['custom_duration']) && !empty($data['custom_duration']) ? $data['custom_duration'] : $duration;

        // Buffer Time Before
        $buffer_time_before = isset($data['buffer_time_before']) && !empty($data['buffer_time_before']) ? $data['buffer_time_before'] : 0;

        // Buffer Time After
        $buffer_time_after = isset($data['buffer_time_after']) && !empty($data['buffer_time_after']) ? $data['buffer_time_after'] : 0;

        // Meeting Interval
        $meeting_interval = isset($data['meeting_interval']) && !empty($data['meeting_interval']) ? $data['meeting_interval'] : 0;

        // var_dump($availability_data['date_slots']);

        // Available and Unavailable date slot
        $available_slot = [];
        $unavailable_slot = [];
        if(!empty($availability_data['date_slots'])){
            foreach($availability_data['date_slots'] as $slot){
                if($slot['available']){
                    $unavailable_slot [] = $slot['date'];
                }else{
                    $available_slot [] = $slot['date'];
                }
            }
        }
        // Unavailable
        $unavailable_slot =  implode(", ",$unavailable_slot);
        $unavailable_slot =  explode(", ",$unavailable_slot);
        $unavailable_slot = array_map(function($date) {
            return "'$date'";
        }, $unavailable_slot);
        $unavailable_slot =  implode(",",$unavailable_slot);

        // Available
        $available_slot =  implode(", ",$available_slot);
        $available_slot =  explode(", ",$available_slot);
        $available_slot = array_map(function($date) {
            return "'$date'";
        }, $available_slot);
        $available_slot =  implode(",",$available_slot);

        $data = array(
            'status' => true, 
            'locations' => $meeting_location_array,
            'hosts' => $meeting_host_array,
            'available_slot' => $available_slot,
            'unavailable_slot' => $unavailable_slot,
        ); 
        return rest_ensure_response($data);

    }

    // Pre Meeting Data
    public function getAvailableTimeData(){
        $request = json_decode(file_get_contents('php://input'), true);
        $meeting_id = isset($request['meeting_id']) ? $request['meeting_id'] : '';

        $selected_date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $selected_time_format = isset($_POST['time_format']) ? sanitize_text_field($_POST['time_format']) : '12';
        $selected_time_zone = isset($_POST['time_zone']) ? sanitize_text_field($_POST['time_zone']) : 'UTC';


        // Get All Booking Data.
        $booking = new Booking();
        $bookings = $booking->get(array('meeting_dates' => $selected_date)); 
        $date_time = new DateTimeController( $selected_time_zone );
 

        $disabled_times = array();
        foreach($bookings as $booking){
            $start_time = $booking->start_time;
            $end_time = $booking->end_time;
            $time_zone = $booking->attendee_time_zone; 
 
            $start_time = $date_time->convert_time_based_on_timezone($start_time, $time_zone, $selected_time_zone, $selected_time_format);
            $end_time = $date_time->convert_time_based_on_timezone($end_time, $time_zone, $selected_time_zone, $selected_time_format);

            $disabled_times[] = array(
                'start_time' => $start_time,
                'end_time' => $end_time,
            );

        }

        var_dump($disabled_times); exit();
    }

    // Create Booking
    public function CreateBooking() { 
        
        $request = json_decode(file_get_contents('php://input'), true);

        $data = [ 
            'meeting_id' => isset($request['meeting']) ? $request['meeting'] : '',
            'first_name' => isset($request['name']) ? $request['name'] : '',
            'email' => isset($request['email']) ? $request['email'] : '',
            'phone' => isset($request['phone']) ? $request['phone'] : '',
            'location_details' => isset($request['address']) ? $request['address'] : '',
            'payment_method' => 'backend',
            'payment_status' => 'pending',
            'status'    => 'pending'
        ];

        // Check if user is already a booking
        $booking = new Booking();
        // Insert booking
        $bookingInsert = $booking->add($data);
        if(!$bookingInsert['status']) {
            return rest_ensure_response(array('status' => false, 'message' => 'Error while creating Booking'));
        }
        $booking_id = $bookingInsert['insert_id'];

        // booking Lists 
        $booking_List = $booking->get();
        // Return response
        $data = array(
            'status' => true, 
            'booking' => $booking_List,  
            'id' => $booking_id,  
            'message' => 'Booking Created Successfully', 
        );
        
        return rest_ensure_response($data);
    }

    // Delete Booking
    public function DeleteBooking(){
        
        // Meeting Lists
        $MeetingsList = $meeting->get(null, true);
        // Return response
        $data = array(
            'status' => true, 
            'meetings' => $MeetingsList,  
            'message' => 'Booking Deleted Successfully', 
        );
        return rest_ensure_response($data);
    }

    // Get Single Booking
    public function getBookingData($request){
        
        // Return response
        $data = array(
            'status' => true, 
            'meeting' => $MeetingData,  
            'message' => 'Booking Data',
        );
        return rest_ensure_response($data);

    }

    // Update Booking Information
    public function updateBooking(){
        $request = json_decode(file_get_contents('php://input'), true);
        $booking_id = $request['id'];
        if (empty($booking_id) || $booking_id == 0) {
            return rest_ensure_response(array('status' => false, 'message' => 'Invalid Booking'));
        }

        $data = [ 
            'id' => $request['id'],
            'status' => isset($request['status']) ? sanitize_text_field($request['status']) : ''
        ];

        $booking = new Booking();
        // Booking Update
        $bookingUpdate = $booking->update($data);

        // Booking Lists 
        $booking_List = $booking->get(null, true);

        // Single Booking 
        $single_booking_meta = $booking->get($request['id']);

        if("approved"==$request['status']){
            do_action('hydra_booking/after_booking_completed', $single_booking_meta);
        }

        if("canceled"==$request['status']){
            do_action('hydra_booking/after_booking_canceled', $single_booking_meta);
        }

        if("schedule"==$request['status']){
            do_action('hydra_booking/after_booking_schedule', $single_booking_meta);
        }

        // Return response
        $data = array(
            'status' => true,  
            'booking' => $booking_List, 
            'message' => 'Booking Updated Successfully', 
        );
        return rest_ensure_response($data);
    }
} 