<?php
namespace HydraBooking\App\Shortcode;
// use Classes
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Availability; 
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Booking;
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;
class HydraBookingShortcode {
    public function __construct() { 

        // Add Shortcode
        add_shortcode('hydra_booking', array($this, 'hydra_booking_shortcode'));

        //  Add Action
        add_action('hydra_booking/after_meeting_render', array($this, 'after_meeting_render'));
        add_action('hydra_booking/before_meeting_render', array($this, 'before_meeting_render'));

        // Already Booked Times
        add_action('wp_ajax_nopriv_tfhb_already_booked_times', array($this, 'tfhb_already_booked_times_callback'));
        add_action('wp_ajax_tfhb_already_booked_times', array($this, 'tfhb_already_booked_times_callback'));

        // Form Submit 
        add_action('wp_ajax_nopriv_tfhb_meeting_form_submit', array($this, 'tfhb_meeting_form_submit_callback'));
        add_action('wp_ajax_tfhb_meeting_form_submit', array($this, 'tfhb_meeting_form_submit_callback'));
    }

    public function hydra_booking_shortcode($atts) { 

        
        if(!isset($atts['id']) || $atts['id'] == 0){
            return 'Please provide a valid Meeting id';
        }  

        // Attributes
        $atts = shortcode_atts(
            array( 
                'id' => 0, 
            ),
            $atts,
            'hydra_booking'
        );

        $calendar_id = $atts['id']; 

        // Get Meeting
        $meeting = new Meeting();
        $MeetingData = $meeting->get( $calendar_id ); 

        if(!$MeetingData){
            return 'Invalid Meeting ID';
        }

        $meta_data = get_post_meta($MeetingData->post_id, '__tfhb_meeting_opt', true);

  
 

        // GetHost meta Data
        $host_id = isset($meta_data['host_id']) ? $meta_data['host_id'] : 0;
        $host_meta = get_user_meta($host_id, '_tfhb_host', true);

        // Time Zone
        $DateTimeZone = new DateTimeController('UTC');
        $time_zone = $DateTimeZone->TimeZone();

     
        // Start Buffer
        ob_start();


        //Before Load the Calendar.
        do_action('hydra_booking/before_meeting_render', $meta_data);

        ?>
        <div class="tfhb-meeting-box tfhb-meeting-<?php echo esc_attr($calendar_id) ?>" data-calendar="<?php echo esc_attr($calendar_id) ?>">

            <form  method="post" action="" class="tfhb-meeting-form ajax-submit"  enctype="multipart/form-data">
                <div class="tfhb-meeting-card">
                        <?php  

                            // Load Meeting Info Template  
                            load_template(THB_PATH . '/app/Content/Template/meeting-info.php', false, [
                                'meeting' => $meta_data,
                                'host' => $host_meta, 
                                'time_zone' => $time_zone, 
                            ]); 

                            // Load Meeting Calendar Template
                            load_template(THB_PATH . '/app/Content/Template/meeting-calendar.php', false, $meta_data);

                            // Load Meeting Time Template
                            load_template(THB_PATH . '/app/Content/Template/meeting-times.php', false, $meta_data);

                            // Load Meeting Form Template
                            load_template(THB_PATH . '/app/Content/Template/meeting-form.php', false, [
                                'questions' => isset($meta_data['questions']) ? $meta_data['questions'] : [],  
                            ]);
                        ?>
                </div>

            </form>
                
        </div>
        <?php 

        //After Load the Calendar.
        do_action('hydra_booking/after_meeting_render', $meta_data);

        // Return Buffer
        return  ob_get_clean();
    }
 
    // Before Render
    public function before_meeting_render(){
        // Enqueue Styles 
        if(!wp_style_is('tfhb-select2-style', 'enqueued')) {
            wp_enqueue_style('tfhb-select2-style');
        }
    }

    // After Render
    public function after_meeting_render($data) { 
        if(!is_array($data) || empty($data)) {
            return;
        }

        $id = isset($data['id']) ? $data['id'] : 0;
        $host_id = isset($data['host_id']) ? $data['host_id'] : 0;

        // Check if id is not set
        if(0 === $id && 0 === $host_id) {
            return;
        }
        
        if( isset($data['availability_type']) && 'custom' === $data['availability_type']){
            $availability_data = isset($data['availability_custom']) ? $data['availability_custom'] : array(); 

        }else{ 
            $availability = new Availability();
            $availability_data =  isset($data['availability_id']) &&  0 != $data['availability_id'] ? $availability->get($data['availability_id']) : array();
        }

        // Availability Range
        $availability_range = isset($data['availability_range']) ? $data['availability_range'] : array();

        // Duration
        $duration = isset($data['duration']) && !empty($data['duration'])? $data['duration'] : 30;

        $duration = isset($data['custom_duration']) && !empty($data['custom_duration']) ? $data['custom_duration'] : $duration;

        // Buffer Time Before
        $buffer_time_before = isset($data['buffer_time_before']) && !empty($data['buffer_time_before']) ? $data['buffer_time_before'] : 0;

        // Buffer Time After
        $buffer_time_after = isset($data['buffer_time_after']) && !empty($data['buffer_time_after']) ? $data['buffer_time_after'] : 0;

        // Meeting Interval
        $meeting_interval = isset($data['meeting_interval']) && !empty($data['meeting_interval']) ? $data['meeting_interval'] : 0;


        // Enqueue Scripts Register scripts 
        if(!wp_script_is('tfhb-app-script', 'enqueued')) {
            wp_enqueue_script('tfhb-app-script');
        } 

        // Enqueue Select2
        if(!wp_script_is('tfhb-select2-script', 'enqueued')) {
            wp_enqueue_script('tfhb-select2-script');
        }
        

        // Localize Script
        wp_localize_script('tfhb-app-script', 'tfhb_app_booking_'.$id, array( 
            'meeting_id' => $id,
            'host_id' => $host_id,
            'duration' => $duration,
            'meeting_interval' => $meeting_interval,
            'buffer_time_before' => $buffer_time_before,
            'buffer_time_after' => $buffer_time_after,
            'availability' => $availability_data,
            'availability_range' => $availability_range,
        ));

    }


    // Form Submit Callback
    public function tfhb_meeting_form_submit_callback() { 
        // Checked Nonce validation
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'tfhb_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        } 

        // Check if the request is POST
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            wp_send_json_error( array( 'message' => 'Invalid request method' ) );
        }

        // Check if the request is not empty
        if ( empty( $_POST ) ) {
            wp_send_json_error( array( 'message' => 'Invalid request' ) );
        }

        if($_POST['meeting_id'] == 0){
            wp_send_json_error( array( 'message' => 'Invalid Meeting ID' ) );
        }

        $data = array();
        $response = array();
        // sanitize the data 
        $data['meeting_id'] = isset($_POST['meeting_id']) ? sanitize_text_field($_POST['meeting_id']) : 0;
        $data['host_id'] = isset($_POST['host_id']) ? sanitize_text_field($_POST['host_id']) : 0;
        $data['attendee_id'] = isset($_POST['attendee_id']) ? sanitize_text_field($_POST['attendee_id']) : 0; 
        $data['order_id'] = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : 0; 
        $data['attendee_time_zone'] = isset($_POST['attendee_time_zone']) ? sanitize_text_field($_POST['attendee_time_zone']) : 0; 
        $data['meeting_dates'] = isset($_POST['meeting_dates']) ? sanitize_text_field($_POST['meeting_dates']) : '';
        $data['start_time'] = isset($_POST['meeting_time_start']) ? sanitize_text_field($_POST['meeting_time_start']) : '';
        $data['end_time'] = isset($_POST['meeting_time_end']) ? sanitize_text_field($_POST['meeting_time_end']) : '';
        $data['slot_minutes'] = isset($_POST['slot_minutes']) ? sanitize_text_field($_POST['slot_minutes']) : '';
        $data['duration'] = isset($_POST['duration']) ? sanitize_text_field($_POST['duration']) : 0;
        $data['attendee_name'] = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $data['email'] = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $data['address'] = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';

        $data['others_info'] = array();
        if(isset($_POST['question']) && !empty($_POST['question'])){ 
            foreach($_POST['question'] as $key => $question){
                $data['others_info'][$key] = sanitize_text_field($question);
            } 
        }

        $data['country'] = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
        $data['ip_address'] = isset($_POST['ip_address']) ? sanitize_text_field($_POST['ip_address']) : '';
        $data['device'] = isset($_POST['device']) ? sanitize_text_field($_POST['device']) : '';

        $data['meeting_locations'] = array();
        if(isset($_POST['meeting_locations']) && !empty($_POST['meeting_locations'])){ 
            foreach($_POST['meeting_locations'] as $key => $location){ 
                $data['meeting_locations'][$key] = array(
                    'location' => sanitize_text_field($location['location']),
                    'address' => sanitize_text_field($location['address']),
                );
            } 
        }
        $data['cancelled_by'] = '';
        $data['status'] = 'pending';
        $data['booking_type'] = 'single';
      

        // Load The Thankyou Template 
        $meeting = new Meeting();
        $MeetingData = $meeting->get( $data['meeting_id'] ); 

        $meta_data = get_post_meta($MeetingData->post_id, '__tfhb_meeting_opt', true);

        // Payment Method
        if(true == $meta_data['payment_status']){ 

            $data['payment_method'] = $meta_data['payment_method'];
            $data['payment_status'] = 'pending';

        }else{

            $data['payment_method'] = 'free';
            $data['payment_status'] = 'completed';
            
        }


        // Before Booking Hooks Action
        do_action('hydra_booking/before_booking_confirmation', $data);


        // Filter Hooks After Booking
        $data = apply_filters('hydra_booking/after_booking_confirmation_filters', $data);

        // GetHost meta Data
        $host_id = isset($meta_data['host_id']) ? $meta_data['host_id'] : 0;
        $host_meta = get_user_meta($host_id, '_tfhb_host', true); 

        // Checked if the booking is already booked

         
        $booking = new Booking();

        $check_booking = $booking->get(
            array(
                'meeting_dates' => $data['meeting_dates'], 
                'start_time' => $data['start_time'], 
                'end_time' => $data['end_time']
            )
        );
 

        if($check_booking){
            wp_send_json_error( array( 'message' => 'Already Booked' ) );
        }

        $result = $booking->add($data);



        if($result === false){
            wp_send_json_error( array( 'message' => 'Booking Failed' ) );
        }


        $data['booking_id'] = $result['insert_id'];
        $title = 'Booking  #' . $result['insert_id'];

        // Create an array to store the post data for meeting the current row
        $meeting_post_data = array(
            'post_type'    => 'tfhb_booking',
            'post_title'   => esc_html($title),
            'post_status'  => 'publish', 
        );

        // Insert the post into the database
        $meeting_post_id = wp_insert_post( $meeting_post_data );

        
        update_post_meta($meeting_post_id, '_tfhb_booking_opt', $data);

      
    
        if(true == $meta_data['payment_status'] && 'woo_payment' == $meta_data['payment_method']){
            // Add to cart
            $product_id = $meta_data['payment_meta']['product_id'];
             
            $woo_booking = new WooBooking();
            $woo_booking->add_to_cart($product_id, $data); 
            $response['redirect'] =  wc_get_checkout_url();
            

        }  

      
 
        // Load Meeting Confirmation Template 
        $confirmation_template =  $this->tfhb_booking_confirmation($data, $MeetingData, $host_meta);

 

        // After Booking Hooks
        do_action('hydra_booking/after_booking_confirmation', $data);

        // Single Booking 
        $single_booking_meta = $booking->get($data['booking_id']);
        do_action('hydra_booking/after_booking_completed', $single_booking_meta);
        
        
        $response['message'] = 'Booking Successful';
        $response['confirmation_template'] = $confirmation_template;


        wp_send_json_success(  $response );
        
        wp_die();
    }

    public function tfhb_booking_confirmation($data, $meta_data, $host_meta){

     
        // Load Meeting Confirmation Template
        ob_start();

        load_template(THB_PATH . '/app/Content/Template/meeting-confirmation.php', false, [
            'meeting' => $meta_data,
            'host' => $host_meta,
            'booking' => $data,
        ]);

        $confirmation_template = ob_get_clean() ;

        return $confirmation_template;
    }


    // Already Booked Times Callback
    public function tfhb_already_booked_times_callback() { 
        // Checked Nonce validation.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'tfhb_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        } 
    

        // Check if the request is POST.
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            wp_send_json_error( array( 'message' => 'Invalid request method' ) );
        }

        // Check if the request is not empty.
        if ( empty( $_POST ) ) {
            wp_send_json_error( array( 'message' => 'Invalid request' ) );
        }  


        $selected_date = isset($_POST['selected_date']) ? sanitize_text_field($_POST['selected_date']) : '';
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

        wp_send_json_success(  $disabled_times );
        wp_die();
    }
}

?>