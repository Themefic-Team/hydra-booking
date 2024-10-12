<?php
namespace HydraBooking\App\Shortcode;

// use Classes
use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Host;
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\DB\Transactions;
use HydraBooking\DB\BookingMeta;


class HydraBookingShortcode {
	public function __construct() {

		// Add Shortcode
		add_shortcode( 'hydra_booking', array( $this, 'hydra_booking_shortcode' ) );

		// Add Action
		add_action( 'hydra_booking/after_meeting_render', array( $this, 'after_meeting_render' ) );
		add_action( 'hydra_booking/before_meeting_render', array( $this, 'before_meeting_render' ) );

		// Already Booked Times
		add_action( 'wp_ajax_nopriv_tfhb_already_booked_times', array( $this, 'tfhb_already_booked_times_callback' ) );
		add_action( 'wp_ajax_tfhb_already_booked_times', array( $this, 'tfhb_already_booked_times_callback' ) );

		// Form Submit
		add_action( 'wp_ajax_nopriv_tfhb_meeting_form_submit', array( $this, 'tfhb_meeting_form_submit_callback' ) );
		add_action( 'wp_ajax_tfhb_meeting_form_submit', array( $this, 'tfhb_meeting_form_submit_callback' ) );

		// Booking Cancel
		add_action( 'wp_ajax_nopriv_tfhb_meeting_form_cencel', array( $this, 'tfhb_meeting_form_cencel_callback' ) );
		add_action( 'wp_ajax_tfhb_meeting_form_cencel', array( $this, 'tfhb_meeting_form_cencel_callback' ) );

		add_action( 'hydra_booking/after_booking_completed', array( $this, 'insert_calender_after_booking_completed' ) );

		// Paypal Payment Confirmation
		add_action( 'wp_ajax_nopriv_tfhb_meeting_paypal_payment_confirmation', array( $this, 'tfhb_meeting_paypal_payment_confirmation_callback' ) );
		add_action( 'wp_ajax_tfhb_meeting_paypal_payment_confirmation', array( $this, 'tfhb_meeting_paypal_payment_confirmation_callback' ) );

		// $this->tfhdb_wp_mail_sent();
	}

	// Test Wp Mail Sent
	public function tfhdb_wp_mail_sent() {
		$to          = 'sydurrahmant1@gmail.com';
		$subject     = 'Hello World';
		$body        = 'Allah Mohan';
		$headers     = array();
		$attachments = array();

		wp_mail( $to, $subject, $body, $headers, $attachments );
	}

	public function hydra_booking_shortcode( $atts ) {

		// Country List form josn file

		if ( ! isset( $atts['id'] ) || $atts['id'] == 0 ) {
			return 'Please provide a valid Meeting id';
		}

		// Attributes
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'hash' => '',
				'type' => 'create',
			),
			$atts,
			'hydra_booking'
		);

		$calendar_id = $atts['id'];

		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $calendar_id );

		if ( ! $MeetingData ) {
			return 'Invalid Meeting ID';
		}

		$meta_data        = get_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', true );
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
	
		// Reschedule Booking
		$booking_data = array();

		if ( ! empty( $atts['hash'] ) && 'reschedule' == $atts['type'] ) {

			$booking     = new Booking();
			$get_booking = $booking->get(
				array( 'hash' => $atts['hash'] ),
				false,
				true
			);

			if ( ! $get_booking ) {
				return 'Invalid Booking ID';
			}

			$booking_data = $get_booking;
		}
 
		// GetHost meta Data
		$host_id   = isset( $meta_data['host_id'] ) ? $meta_data['host_id'] : 0;

	
		$hostData =  new Host();
		$host_meta = (array) $hostData->get( $host_id ); 
		 
		// tfhb_print_r($host_meta);
		// Time Zone
		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		// Start Buffer
		ob_start();

		// Before Load the Calendar.
		do_action( 'hydra_booking/before_meeting_render', $meta_data );

		?>
		<div class="tfhb-meeting-box tfhb-meeting-<?php echo esc_attr( $calendar_id ); ?>" data-calendar="<?php echo esc_attr( $calendar_id ); ?>">
			
			<?php

			if ( ! empty( $booking_data ) && 'reschedule' == $atts['type'] ) {
				// Load Reschedule Template
				// You are rescheduling the booking: 3:15 pm - 3:30 pm, May 27, 2024 (Asia/Dhaka)
				echo '<div class="tfhb-reschedule-box">';
				echo '<p>' . esc_html__( 'You are rescheduling the booking:', 'hydra-booking' ) . ' ' . esc_html( $booking_data->start_time ) . ' - ' . esc_html( $booking_data->end_time ) . ', ' . esc_html( gmdate( 'F j, Y', strtotime( $booking_data->meeting_dates ) ) ) . ' (' . esc_html( $booking_data->attendee_time_zone ) . ')</p>';
				echo '</div>';

			}

			?>
			<!-- <form  method="post" action="" class="tfhb-meeting-form ajax-submit"  enctype="multipart/form-data"> -->
				<div class="tfhb-meeting-card">
				
						<?php
						// Load Meeting Info Template
						load_template(
							THB_PATH . '/app/Content/Template/meeting-info.php',
							false,
							array(
								'meeting'      => $meta_data,
								'host'         => $host_meta,
								'time_zone'    => $time_zone,
								'booking_data' => $booking_data,
							)
						);
						?>
						<div class="tfhb-calander-times tfhb-flexbox"> 
							<?php
							// Load Meeting Calendar Template
							load_template( THB_PATH . '/app/Content/Template/meeting-calendar.php', false, $meta_data );

							// Load Meeting Time Template
							load_template(
								THB_PATH . '/app/Content/Template/meeting-times.php',
								false,
								array(
									'meeting'          => $meta_data,
									'host'         => $host_meta,
									'general_settings' => $general_settings,
								)
							);
							?>
						</div>
						<?php
						// Load Meeting Form Template
						load_template(
							THB_PATH . '/app/Content/Template/meeting-form.php',
							false,
							array(
								'meeting'      => $meta_data,
								'booking_data' => $booking_data,
							)
						);
						?>
				</div>

			<!-- </form> -->
				
		</div>
		<?php

		// After Load the Calendar.
		do_action( 'hydra_booking/after_meeting_render', $meta_data );

		// Return Buffer
		return ob_get_clean();
	}

	// Before Render
	public function before_meeting_render() {
		// Enqueue Styles
		if ( ! wp_style_is( 'tfhb-select2-style', 'enqueued' ) ) {
			wp_enqueue_style( 'tfhb-select2-style' );
		}
	}

	// After Render
	public function after_meeting_render( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}

		$id      = isset( $data['id'] ) ? $data['id'] : 0;
		$host_id = isset( $data['host_id'] ) ? $data['host_id'] : 0;

		// Check if id is not set
		if ( 0 === $id && 0 === $host_id ) {
			return;
		}

		if ( isset( $data['availability_type'] ) && 'settings' === $data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $host_id, '_tfhb_host', true );
			if ( in_array( $data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				$availability_data = $_tfhb_availability_settings['availability'][ $data['availability_id'] ];
			} else {
				$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
			}
		} else {

			$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
		}

		// Availability Range
		$availability_range      = isset( $data['availability_range'] ) ? $data['availability_range'] : array();
		$availability_range_type = isset( $data['availability_range_type'] ) ? $data['availability_range_type'] : array();

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		$payment_status = isset( $data['payment_status'] ) && ! empty( $data['payment_status'] ) ? $data['payment_status'] : 0;

		// 
		// Integration Settings
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$tfhb_paypal = isset( $_tfhb_integration_settings['paypal'] ) ? $_tfhb_integration_settings['paypal'] : array();
	 

		if(isset($tfhb_paypal['status']) && $tfhb_paypal['status'] == 1 &&  ! wp_script_is( 'tfhb-paypal-script', 'enqueued' )){ 
			wp_enqueue_script( 'tfhb-paypal-sdk',  ); 
		}
		// Enqueue Scripts Register scripts
		if ( ! wp_script_is( 'tfhb-app-script', 'enqueued' ) ) {
			wp_enqueue_script( 'tfhb-app-script',  );
		}

		// Enqueue Select2
		if ( ! wp_script_is( 'tfhb-select2-script', 'enqueued' ) ) {
			wp_enqueue_script( 'tfhb-select2-script' );
		}
	
		

		// Localize Script
		wp_localize_script(
			'tfhb-app-script',
			'tfhb_app_booking_' . $id,
			array(
				'meeting_id'              => $id,
				'host_id'                 => $host_id,
				'duration'                => $duration,
				'payment_status'          => $payment_status,
				'meeting_interval'        => $meeting_interval,
				'buffer_time_before'      => $buffer_time_before,
				'buffer_time_after'       => $buffer_time_after,
				'availability'            => $availability_data,
				'availability_range'      => $availability_range,
				'availability_range_type' => $availability_range_type,
			)
		);
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

		if ( $_POST['meeting_id'] == 0 ) {
			wp_send_json_error( array( 'message' => 'Invalid Meeting ID' ) );
		}

		$data     = array();
		$response = array();

		$booking = new Booking();

		// General Settings
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();

		// Generate Meeting Hash Based on start time and end time and Date And Meeting id + random number
		if ( isset( $_POST['booking_hash'] ) ) {

			$meeting_hash = sanitize_text_field( $_POST['booking_hash'] );

		} else {

			$meeting_hash = md5( sanitize_text_field( $_POST['meeting_dates'] ) . sanitize_text_field( $_POST['meeting_time_start'] ) . sanitize_text_field( $_POST['meeting_time_end'] ) . sanitize_text_field( $_POST['meeting_id'] ) . wp_rand( 1000, 9999 ) );

		}

		// sanitize the data
		$data['meeting_id'] = isset( $_POST['meeting_id'] ) ? sanitize_text_field( $_POST['meeting_id'] ) : 0;

		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $data['meeting_id'] );

		$meta_data = get_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', true );

		$data['host_id']            = isset( $_POST['host_id'] ) ? sanitize_text_field( $_POST['host_id'] ) : 0;
		$data['attendee_id']        = isset( $_POST['attendee_id'] ) ? sanitize_text_field( $_POST['attendee_id'] ) : 0;
		$data['hash']               = $meeting_hash;
		$data['order_id']           = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : 0;
		$data['attendee_time_zone'] = isset( $_POST['attendee_time_zone'] ) ? sanitize_text_field( $_POST['attendee_time_zone'] ) : 0;
		$data['meeting_dates']      = isset( $_POST['meeting_dates'] ) ? sanitize_text_field( $_POST['meeting_dates'] ) : '';
		$data['start_time']         = isset( $_POST['meeting_time_start'] ) ? sanitize_text_field( $_POST['meeting_time_start'] ) : '';
		$data['end_time']           = isset( $_POST['meeting_time_end'] ) ? sanitize_text_field( $_POST['meeting_time_end'] ) : '';
		$data['slot_minutes']       = isset( $_POST['slot_minutes'] ) ? sanitize_text_field( $_POST['slot_minutes'] ) : '';
		$data['duration']           = isset( $_POST['duration'] ) ? sanitize_text_field( $_POST['duration'] ) : 0;
		$data['attendee_name']      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$data['email']              = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$data['address']            = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
		$data['others_info']        = array();
		$questions                  = isset( $_POST['question'] ) ? $_POST['question'] : array();

		// Contact form fields
		if ( $meta_data['questions_type'] == 'existing' ) {

			if ( $meta_data['questions_form_type'] == 'wpcf7' ) {
				$questions = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'question_' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);
			}

			if ( $meta_data['questions_form_type'] == 'fluent-forms' ) {

				$questions = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'question_' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);

				if ( isset( $_POST['names'] ) && is_array( $_POST['names'] ) ) {
					$data['attendee_name'] = $_POST['names']['first_name'] . ' ' . $_POST['names']['last_name'];
				}
			}

			if ( $meta_data['questions_form_type'] == 'forminator' ) {

				$data['email'] = $_POST['email-1'];
				unset( $_POST['email-1'] );

				$attendee_names = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'name-1' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);

				$data['attendee_name'] = '';
				foreach ( $attendee_names as $key => $name ) {
					$data['attendee_name'] .= $name . ' ';
					unset( $_POST[ $key ] );
				}

				$address = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'address-1' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);

				foreach ( $address as $key => $name ) {
					$data['address'] .= $name . ' ';
					unset( $_POST[ $key ] );
				}
				$questions = $_POST;
				unset( $questions['_wp_http_referer'] );
				unset( $questions['action'] );
				unset( $questions['current_url'] );
				unset( $questions['form_id'] );
				unset( $questions['form_type'] );
				unset( $questions['forminator_nonce'] );
				unset( $questions['nonce'] );
				unset( $questions['page_id'] );
				unset( $questions['referer_url'] );
				unset( $questions['render_id'] );
				unset( $questions['nonce'] );
				unset( $questions['meeting_id'] );
				unset( $questions['host_id'] );
				unset( $questions['meeting_dates'] );
				unset( $questions['meeting_duration'] );
				unset( $questions['meeting_time_start'] );
				unset( $questions['meeting_time_end'] );
				unset( $questions['recurring_maximum'] );
				unset( $questions['attendee_time_zone'] );
				unset( $questions['payment_type'] );
				unset( $questions['meeting_price'] );
				unset( $questions['payment_amount'] );
				unset( $questions['stripe_public_key'] );
				unset( $questions['payment_currency'] );

			}
		}

		// Recurring Meeting
		if ( isset( $meta_data['recurring_status'] ) && $meta_data['recurring_status'] == true ) {
			$recurring_repeat       = isset( $meta_data['recurring_repeat'] ) ? $meta_data['recurring_repeat'] : array();
			$recurring_repeat_limit = isset( $recurring_repeat[0]['limit'] ) ? $recurring_repeat[0]['limit'] : array();
			$recurring_repeat_times = isset( $recurring_repeat[0]['times'] ) ? $recurring_repeat[0]['times'] : array();
			$recurring_maximum      = isset( $meta_data['recurring_maximum'] ) ? $meta_data['recurring_maximum'] : 0;
			$meeting_dates          = isset( $_POST['meeting_dates'] ) ? sanitize_text_field( $_POST['meeting_dates'] ) : '';
			// based on the recurring repeat limit and times
			$next_date = $meeting_dates;

			for ( $i = 1; $i < $recurring_maximum; $i++ ) {

				$recurring_current_date = gmdate( 'Y-m-d', strtotime( $next_date . ' + ' . $recurring_repeat_limit . ' ' . $recurring_repeat_times . '' ) );
				$meeting_dates         .= ',' . $recurring_current_date;
				$next_date              = $recurring_current_date;
			}
			$data['meeting_dates'] = $meeting_dates;

		}

		if ( isset( $questions ) && ! empty( $questions ) ) {
			foreach ( $questions as $key => $question ) {
				$data['others_info'][ $key ] = sanitize_text_field( $question );
			}
		}
		$data['country']    = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
		$data['ip_address'] = isset( $_POST['ip_address'] ) ? sanitize_text_field( $_POST['ip_address'] ) : '';
		$data['device']     = isset( $_POST['device'] ) ? sanitize_text_field( $_POST['device'] ) : '';

		$data['meeting_locations'] = array();
		if ( isset( $_POST['meeting_locations'] ) && ! empty( $_POST['meeting_locations'] ) ) {
			foreach ( $_POST['meeting_locations'] as $key => $location ) {
				$data['meeting_locations'][ $location['location'] ] = array(
					'location' => sanitize_text_field( $location['location'] ),
					'address'  => sanitize_text_field( $location['address'] ),
				);
			}
		}
		$data['cancelled_by'] = '';
		$data['status']       = isset( $general_settings['booking_status'] ) && $general_settings['booking_status'] == 1 ? 'confirmed' : 'pending';
		$data['reason']       = '';
		$data['booking_type'] = 'single';

		// Payment Method
		if ( true == $meta_data['payment_status'] ) {

			$data['payment_method'] = $meta_data['payment_method'];
			$data['payment_status'] = 'pending';

		} else {

			$data['payment_method'] = 'free';
			$data['payment_status'] = 'completed';

		}

		// Before Booking Hooks Action
		do_action( 'hydra_booking/before_booking_confirmation', $data );

		// Filter Hooks After Booking
		$data = apply_filters( 'hydra_booking/after_booking_confirmation_filters', $data );

		// GetHost meta Data
		$host_id   = isset( $meta_data['host_id'] ) ? $meta_data['host_id'] : 0;
		$host_meta = get_user_meta( $host_id, '_tfhb_host', true );

		$check_booking = $booking->get(
			array(
				'meeting_id'    => $data['meeting_id'],
				'meeting_dates' => $data['meeting_dates'],
				'start_time'    => $data['start_time'],
				'end_time'      => $data['end_time'],
			)
		);

		if ( 'one-to-group' == $meta_data['meeting_type'] ) {
			$max_book_per_slot = isset( $meta_data['max_book_per_slot'] ) ? $meta_data['max_book_per_slot'] : 1;
			if ( count( $check_booking ) >= $max_book_per_slot ) {
				wp_send_json_error( array( 'message' => 'Already Booked' ) );
			}
		} elseif ( $check_booking ) {
				wp_send_json_error( array( 'message' => 'Already Booked' ) );
		}

		// Get booking Data using Hash
		if ( isset( $_POST['action_type'] ) && 'reschedule' == $_POST['action_type'] ) {

			// if general_settings['allowed_reschedule_before_meeting_start'] is available exp 100 then check the time before reschedule
			$this->tfhb_reschedule_booking( $meeting_hash, $meta_data,  $general_settings, $MeetingData, $host_meta ); 
		}
		$this->tfhb_create_new_booking($data, $meta_data, $MeetingData, $host_meta);



	}

	/**
	 * Create New Booking
	 * @param $data
	 * @return void
	 */
	public function tfhb_create_new_booking( $data, $meta_data, $MeetingData, $host_meta  ) {
		// Get Booking Data
		$booking = new Booking();

		// Booking Frequency
		$current_user_booking = $booking->get( array( 'meeting_id' => $data['meeting_id'], 'meeting_dates' => $data['meeting_dates'] ) );
		if ( $current_user_booking ) {
			$this->tfhb_checked_booking_frequency_limit( $current_user_booking, $meta_data,);
		}

		// Create a New Booking Into Post Type
		$meeting_post_id = $this->tfhb_create_custom_post_booking($data);



		$data['post_id'] = $meeting_post_id; // set post id into booking data
		$result          = $booking->add( $data );  // add booking data into booking table

		if ( $result === false ) {
			wp_send_json_error( array( 'message' => 'Booking Failed' ) );
		}

		


		// Woocommerce Payment Method
		if ( true == $meta_data['payment_status'] && 'woo_payment' == $meta_data['payment_method'] ) {
			// Add to cart
			$product_id = $meta_data['payment_meta']['product_id'];
			$data['booking_id'] = $result['insert_id'];

			$woo_booking = new WooBooking();
			$woo_booking->add_to_cart( $product_id, $data );
			$response['redirect'] = wc_get_checkout_url();
		}
		
		// payment methood check and process the payment
		$this->tfhb_booking_payment_method( $meta_data, $MeetingData, $result );



		$single_booking_meta = $booking->get(
			array( 'id' => $result['insert_id'] ),
			false,
			true
		);

		// Zoom Meeting
		$this->tfhb_create_zoom_meeting( $single_booking_meta, $meta_data, $host_meta);


		// After Booking Hooks
		do_action( 'hydra_booking/after_booking_confirmation', $single_booking_meta );


		// Single Booking & Mail Notification, Google Calendar // Zoom Meeting
		do_action( 'hydra_booking/after_booking_completed', $single_booking_meta );
  
		// Load Meeting Confirmation Template
		$confirmation_template = $this->tfhb_booking_confirmation( $data, $MeetingData, $host_meta );

		$response['message']               = 'Booking Successful';
		$response['action']                = 'create';
		
		if('paypal_payment' == $meta_data['payment_method']){
			$response['data']                = array( 
				'hash' 	  => $data['hash'], 
				'booking_id' => $result['insert_id'],
				'booking' => $data,
				'meeting' => $MeetingData,
			);
		}
		
		$response['confirmation_template'] = $confirmation_template;

		wp_send_json_success( $response );
		wp_die();
	}

	/* Checked Booking frequency limit
	 * @param $current_user_booking
	 * @param $meta_data
	 * @return void
	 */
	public function tfhb_checked_booking_frequency_limit($current_user_booking, $meta_data){
		$last_items_of_booking = end( $current_user_booking );

		$booking_frequency = isset( $meta_data['booking_frequency'] ) ? $meta_data['booking_frequency'] : array(); 
		if ( $booking_frequency != NULL ) {
			$booking_frequency = !is_array( $booking_frequency ) ? json_decode( $booking_frequency, true ) : $booking_frequency;
			$created_date = $last_items_of_booking->created_at; // 2024-07-02 14:26:29
			$current_date = gmdate( 'Y-m-d H:i:s' );

			$last_created_date = gmdate( 'Y-m-d', strtotime( $created_date ) );
			foreach ( $booking_frequency as $key => $value ) {
				$times  = isset( $value['times'] ) ? $value['times'] : 'days';
				$limit = isset( $value['limit'] ) ? $value['limit'] : 5;

				$booking_frequency_date = gmdate( 'Y-m-d', strtotime( $last_created_date . ' + ' . $limit . ' '.$times ) );
				$total_booking          = count(
					array_filter(
						$current_user_booking,
						function ( $booking ) use ( $booking_frequency_date, $last_created_date ) {
							$created_date = gmdate( 'Y-m-d', strtotime( $booking->created_at ) );
							// Check if the created date is between last_created_date and booking_frequency_date
							return strtotime( $last_created_date ) >= strtotime( $created_date ) || strtotime( $created_date ) <= strtotime( $booking_frequency_date );
						}
					)
				);

				// if currentdate is greater than booking_frequency_date then you can book the meeting
				if ( strtotime( $current_date ) > strtotime( $booking_frequency_date ) ) {
					continue;
				}
				if ( $total_booking >= $limit ) {
					wp_send_json_error( array( 'message' => ' Meeting Frequency Limit Reached' ) );

				}
			}
		}
	}

	/* Create Custom Post Booking
	 * @param $data
	 * @return $id
	 */
	public function tfhb_create_custom_post_booking($data) {

		// Create a new booking
		$title = 'New booking Booking '; // default title

		// Create an array to store the post data for meeting the current row
		$meeting_post_data = array(
			'post_type'   => 'tfhb_booking',
			'post_title'  => esc_html( $title ),
			'post_status' => 'publish',
		);

		// Insert the post into the database
		$meeting_post_id = wp_insert_post( $meeting_post_data );
		update_post_meta( $meeting_post_id, '_tfhb_booking_opt', $data );

		return $meeting_post_id;
	}

	/* payment methood check and process the payment
	 * @param $meta_data
	 * @param $MeetingData
	 * @param $result
	 * @return void
	 */
	public function tfhb_booking_payment_method( $meta_data, $MeetingData, $result ) {

		if ( true == $meta_data['payment_status'] && 'stripe_payment' == $meta_data['payment_method'] ) {
			$data['tokenId']  = ! empty( $_POST['tokenId'] ) ? $_POST['tokenId'] : '';
			$data['price']    = ! empty( $MeetingData->meeting_price ) ? $MeetingData->meeting_price : '';
			$data['currency'] = ! empty( $MeetingData->payment_currency ) ? $MeetingData->payment_currency : 'USD';
			if ( empty( $_POST['action_type'] ) ) {
				do_action( 'hydra_booking/stripe_payment_method', $data, $result['insert_id'] );
			}
		}
		if ( true == $meta_data['payment_status'] && 'paypal_payment' == $meta_data['payment_method'] ) {
			$data['paymentID']    = ! empty( $_POST['paymentID'] ) ? $_POST['paymentID'] : '';
			$data['paymentToken'] = ! empty( $_POST['paymentToken'] ) ? $_POST['paymentToken'] : '';
			$data['payerID']      = ! empty( $_POST['payerID'] ) ? $_POST['payerID'] : '';
			$data['price']        = ! empty( $MeetingData->meeting_price ) ? $MeetingData->meeting_price : '';
			$data['currency']     = ! empty( $MeetingData->payment_currency ) ? $MeetingData->payment_currency : 'USD';
			if ( empty( $_POST['action_type'] ) ) {
				do_action( 'hydra_booking/paypal_payment_method', $data, $result['insert_id'] );
			}
		}
	}

	/* Create Zoom Meeting
	 * @param $single_booking_meta
	 * @param $meta_data
	 * @param $host_meta
	 * @return void
	 */
	public function tfhb_create_zoom_meeting( $single_booking_meta, $meta_data, $host_meta) {

		$booking = new Booking();
		// Host Meta by Booking Id
		$_tfhb_host_integration_settings = get_user_meta( $single_booking_meta->host_id, '_tfhb_host_integration_settings', true );

		// Booking Table Meeting Location Data
		$meeting_location_data = json_decode( $single_booking_meta->meeting_locations, true );

		// Meeting Location Check
		$meeting_locations = $meta_data['meeting_locations'];
		
		
		$zoom_exists = false;
		if ( is_array( $meeting_locations ) ) {
			
			// if in array location value is meet then set google meet using array filter
			$meeting_location = array_filter(
				$meeting_locations,
				function ( $location ) {
					return $location['location'] == 'zoom';
				}
			);

			$zoom_exists = count( $meeting_location ) > 0 ? true : false;
		} 

		// Global Integration
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );

		if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) ) {
			$account_id     = $_tfhb_integration_settings['zoom_meeting']['account_id'];
			$app_client_id  = $_tfhb_integration_settings['zoom_meeting']['app_client_id'];
			$app_secret_key = $_tfhb_integration_settings['zoom_meeting']['app_secret_key'];
		}

		// Host Integration
		if ( ! empty( $_tfhb_host_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_host_integration_settings['zoom_meeting']['connection_status'] ) ) {
			$account_id     = $_tfhb_host_integration_settings['zoom_meeting']['account_id'];
			$app_client_id  = $_tfhb_host_integration_settings['zoom_meeting']['app_client_id'];
			$app_secret_key = $_tfhb_host_integration_settings['zoom_meeting']['app_secret_key'];
		}

		if ( $zoom_exists == true && ! empty( $account_id ) && ! empty( $app_client_id ) && ! empty( $app_secret_key ) ) { 
			

			$zoom             = new ZoomServices( );
			$zoom->setApiDetails( $account_id, $app_client_id, $app_secret_key );
			$meeting_creation = $zoom->create_zoom_meeting( $single_booking_meta, $meta_data, $host_meta );
			

			$meeting_location_data['zoom']['address'] = $meeting_creation;

			// Get Post Meta
			$meeting_address_data = array(
				'id'                => $single_booking_meta->id,
				'meeting_locations' => wp_json_encode( $meeting_location_data ),
			);
			$booking->update( $meeting_address_data );

		}
	}


	/* Reschedule Booking
	 * @param $data
	 * @return void
	 */
	public function tfhb_reschedule_booking( $meeting_hash, $meta_data,  $general_settings, $MeetingData, $host_meta ) {

		// Booking Class
		$booking = new Booking();

		$get_booking = $booking->get(
			array( 'hash' => $meeting_hash ),
			false,
			true
		);
		// Get Post Meta
		$booking_meta = get_post_meta( $get_booking->post_id, '_tfhb_booking_opt', true );
		

		if ( isset( $general_settings['allowed_reschedule_before_meeting_start'] ) && ! empty( $general_settings['allowed_reschedule_before_meeting_start'] ) ) {
			$allowed_reschedule_before_meeting_start = $general_settings['allowed_reschedule_before_meeting_start']; // 100 minutes
			if ( isset( $general_settings['allowed_reschedule_before_meeting_start'] ) && ! empty( $general_settings['allowed_reschedule_before_meeting_start'] ) ) {
				$allowed_reschedule_before_meeting_start = $general_settings['allowed_reschedule_before_meeting_start']; // 100 minutes
				$DateTime                                = new DateTimeController( $booking_meta['attendee_time_zone'] );
				// Time format if has AM and PM into start time
				$time_format  = strpos( $booking_meta['start_time'], 'AM' ) || strpos( $booking_meta['start_time'], 'PM' ) ? '12' : '24';
				$current_time = strtotime( $DateTime->convert_time_based_on_timezone( gmdate( 'Y-m-d H:i:s' ), 'UTC', $booking_meta['attendee_time_zone'], $time_format ) );
				$meeting_time = strtotime( $booking_meta['meeting_dates'] . ' ' . $booking_meta['start_time'] );
				$time_diff    = $meeting_time - $current_time;
				$time_diff    = $time_diff / 60; // convert to minutes

				if ( $time_diff < $allowed_reschedule_before_meeting_start ) {
					wp_send_json_error( array( 'message' => 'You can not reschedule the meeting before ' . $allowed_reschedule_before_meeting_start . ' minutes' ) );
				}
			}
		}

		$reschedule_data = array(
			'id'                 => $get_booking->id,
			'attendee_time_zone' => isset( $_POST['attendee_time_zone'] ) ? sanitize_text_field( $_POST['attendee_time_zone'] ) : '',
			'meeting_dates'      => sanitize_text_field( $_POST['meeting_dates'] ),
			'start_time'         => sanitize_text_field( $_POST['meeting_time_start'] ),
			'end_time'           => sanitize_text_field( $_POST['meeting_time_end'] ),
			'reason'             => isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '',
			'status'             => isset( $general_settings['reschedule_status'] ) && $general_settings['reschedule_status'] == 1 ? 'rescheduled' : 'pending',
		);

		$booking_meta = array_merge( $booking_meta, $reschedule_data );

		// Update Post Meta
		update_post_meta( $get_booking->post_id, '_tfhb_booking_opt', $booking_meta );

		$booking->update( $reschedule_data );
		$confirmation_template = $this->tfhb_booking_confirmation( $booking_meta, $MeetingData, $host_meta );

		// Single Booking & Mail Notification
		$single_booking_meta = $booking->get( $get_booking->id );
		do_action( 'hydra_booking/after_booking_schedule', $single_booking_meta );

		// Host Meta by Booking Id
		$_tfhb_host_integration_settings = get_user_meta( $single_booking_meta->host_id, '_tfhb_host_integration_settings', true );

		// Booking Table Meeting Location Data
		$meeting_location_data = json_decode( $single_booking_meta->meeting_locations, true );

		// Meeting Location Check
		$meeting_locations = json_decode( $single_booking_meta->meeting_location );
		$zoom_exists       = false;
		if ( is_array( $meeting_locations ) ) {
			foreach ( $meeting_locations as $location ) {
				if ( isset( $location->location ) && $location->location === 'zoom' ) {
					$zoom_exists = true;
					break;
				}
			}
		}

		$meeting_schedule_id = ! empty( $meeting_location_data['zoom']['address']['id'] ) ? $meeting_location_data['zoom']['address']['id'] : '';
		// Global Integration
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) ) {
			$account_id     = $_tfhb_integration_settings['zoom_meeting']['account_id'];
			$app_client_id  = $_tfhb_integration_settings['zoom_meeting']['app_client_id'];
			$app_secret_key = $_tfhb_integration_settings['zoom_meeting']['app_secret_key'];
		}

		// Host Integration
		if ( ! empty( $_tfhb_host_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_host_integration_settings['zoom_meeting']['connection_status'] ) ) {
			$account_id     = $_tfhb_host_integration_settings['zoom_meeting']['account_id'];
			$app_client_id  = $_tfhb_host_integration_settings['zoom_meeting']['app_client_id'];
			$app_secret_key = $_tfhb_host_integration_settings['zoom_meeting']['app_secret_key'];
		}

		if ( $zoom_exists && ! empty( $account_id ) && ! empty( $app_client_id ) && ! empty( $app_secret_key ) ) {
			$zoom                                     = new ZoomServices( );
			$zoom->setApiDetails( $account_id, $app_client_id, $app_secret_key );
			$meeting_creation                         = $zoom->update_zoom_meeting( $meeting_schedule_id, $single_booking_meta, $meta_data, $host_meta );
			$meeting_location_data['zoom']['address'] = $meeting_creation;

			// Get Post Meta
			$meeting_address_data = array(
				'id'                => $single_booking_meta->id,
				'meeting_locations' => wp_json_encode( $meeting_location_data ),
			);
			$booking->update( $meeting_address_data );

		}

		$response['message']               = 'Rescheduled Successfully';
		$response['action']                = 'rescheduled';
		$response['confirmation_template'] = $confirmation_template;
		// $booking_meta, $MeetingData, $host_meta
		wp_send_json_success( $response );
		wp_die();
	}


	public function tfhb_booking_confirmation( $data, $meta_data, $host_meta ) {

		// Load Meeting Confirmation Template
		ob_start();

		load_template(
			THB_PATH . '/app/Content/Template/meeting-confirmation.php',
			false,
			array(
				'meeting' => $meta_data,
				'host'    => $host_meta,
				'booking' => $data,
			)
		);

		$confirmation_template = ob_get_clean();

		return $confirmation_template;
	}


	// Insert Calender After Booking Schedule
	public function insert_calender_after_booking_completed( $data ) {

		$booking     = new Booking();
		$meeting     = new Meeting();
		$BookingMeta = new BookingMeta();
		$MeetingData = $meeting->get( $data->meeting_id );
		$meta_data   = get_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', true );
		if ( 'one-to-group' == $meta_data['meeting_type'] ) {
			$max_book_per_slot = isset( $meta_data['max_book_per_slot'] ) ? $meta_data['max_book_per_slot'] : 1;
			$check_booking     = $booking->get(
				array(
					'meeting_id'    => $data->meeting_id,
					'meeting_dates' => $data->meeting_dates,
					'start_time'    => $data->start_time,
					'end_time'      => $data->end_time,
				),
				false,
				false,
				false,
				'id DESC',
			);

			// unset if check_booking has current booking data->id without loop and array maps or filter
			$check_booking = array_filter(
				$check_booking,
				function ( $booking ) use ( $data ) {
					return $booking->id !== $data->id;
				}
			);
			// Get First Items form the array
			$first_item = reset( $check_booking );

			if ( $first_item->meeting_calendar != 'null' && ! empty( $first_item->meeting_calendar ) && $first_item->meeting_calendar != 0 ) {

				$booking_calendar           = $BookingMeta->getFirstOrFail(
					$first_item->meeting_calendar
				);
				$update                     = array();
				$update['id']               = $data->id;
				$update['meeting_calendar'] = $booking_calendar->id;

				$booking->update( $update );
				$booking_calendar_value = json_decode( $booking_calendar->value );

				$booking_calendar_value = apply_filters( 'hydra_booking_calendar_add_new_attendee', $booking_calendar_value, $data );

				// Update the Booking meta
				$booking_meta = array(
					'id'    => $booking_calendar->id,
					'value' => wp_json_encode( $booking_calendar_value, true ),
				);

				$BookingMeta->update( $booking_meta );

				return;

			}
		} else {

			// Update the Booking
			$calendar_data = apply_filters( 'after_booking_completed_calendar_data', array(), $data );

			$booking_meta = array(
				'booking_id' => $data->id,
				'meta_key'   => 'booking_calendar',
				'value'      => wp_json_encode( $calendar_data, true ),
			);

			$insert = $BookingMeta->add( $booking_meta );

			$insert_id = $insert['insert_id'];
			if ( $insert_id === false ) {
				return false;
			}

			$update                     = array();
			$update['id']               = $data->id;
			$update['meeting_calendar'] = $insert_id;

			$booking->update( $update );
		}

		return true;
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

		$selected_date        = isset( $_POST['selected_date'] ) ? sanitize_text_field( $_POST['selected_date'] ) : '';
		$meeting_id           = isset( $_POST['meeting_id'] ) ? sanitize_text_field( $_POST['meeting_id'] ) : 0;
		$selected_time_format = isset( $_POST['time_format'] ) ? sanitize_text_field( $_POST['time_format'] ) : '12';
		$selected_time_zone   = isset( $_POST['time_zone'] ) ? sanitize_text_field( $_POST['time_zone'] ) : 'UTC';

		$date_time = new DateTimeController( $selected_time_zone );
		$data      = $date_time->getAvailableTimeData( $meeting_id, $selected_date, $selected_time_zone, $selected_time_format );

		if ( empty( $data ) ) {
			wp_send_json_error( array( 'message' => 'No time slots are currently available.' ) );
		}
		wp_send_json_success( $data );
		wp_die();
	}


	// Booking Cancel Callback
	public function tfhb_meeting_form_cencel_callback() {
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

		$data     = array();
		$response = array();

		$booking_hash = isset( $_POST['booking_hash'] ) ? sanitize_text_field( $_POST['booking_hash'] ) : '';
		$reason       = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';

		$booking     = new Booking();
		$get_booking = $booking->get(
			array( 'hash' => $booking_hash ),
			false,
			true
		);

		if ( ! $get_booking ) {
			wp_send_json_error( array( 'message' => 'Invalid Booking ID' ) );
		}

		$booking_data = array(
			'id'           => $get_booking->id,
			'reason'       => $reason,
			'status'       => 'cancelled',
			'cancelled_by' => 'attendee',
		);

		$booking->update( $booking_data );

		$response['message'] = 'Booking Cancelled Successfully';

		wp_send_json_success( $response );

		wp_die();
	}

	/**
	 * Get Booking Data
	 * @param $booking_id
	 * @return $booking
	 */
	public function tfhb_meeting_paypal_payment_confirmation_callback(){
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
		$payment_details = isset( $_POST['payment_details'] ) ? $_POST['payment_details'] : array();
		$responseData = isset( $_POST['responseData'] ) ? $_POST['responseData'] : array();

		
		$payment_id = isset( $payment_details['id'] ) ? sanitize_text_field( $payment_details['id'] ) : '';
		$payer_id = isset( $payment_details['payer']['payer_id'] ) ? sanitize_text_field( $payment_details['payer']['payer_id'] ) : '';
		$hash = isset( $responseData['data']['hash'] ) ? sanitize_text_field( $responseData['data']['hash'] ) : '';
		$booking_id = isset( $responseData['data']['booking_id'] ) ? sanitize_text_field( $responseData['data']['booking_id'] ) : '';
		$meeting_id = isset( $responseData['data']['booking']['meeting_id'] ) ? sanitize_text_field( $responseData['data']['booking']['meeting_id'] ) : '';	
		$host_id = isset( $responseData['data']['booking']['host_id'] ) ? sanitize_text_field( $responseData['data']['booking']['host_id'] ) : '';	
		$customer_id = isset( $responseData['data']['booking']['attendee_id'] ) ? sanitize_text_field( $responseData['data']['booking']['attendee_id'] ) : '';	
		$payment_method = isset( $responseData['data']['booking']['payment_method'] ) ? sanitize_text_field( $responseData['data']['booking']['payment_method'] ) : '';	
		$total =  isset($payment_details['purchase_units'][0]['amount']['value']) ? sanitize_text_field( $payment_details['purchase_units'][0]['amount']['value'] ) : '';
		$booking     = new Booking();

		$bookingdata = array(
			'id'             => $booking_id,
			'payment_status' => 'Completed',
		);
		
		// Booking Update
		$bookingUpdate = $booking->update( $bookingdata );

		$charge = array(
			'payment_id'    => ! empty( $payment_id ) ? $payment_id : '', 
			'payer_id'      => ! empty( $payer_id  ) ? $payer_id  : '',
			'hash'      => ! empty( $hash  ) ? $hash  : '', 
		);
		// Data for Transactions Table
		$tdata        = array(
			'booking_id'         => $booking_id,
			'meeting_id'         => $meeting_id,
			'host_id'         => $host_id,
			'customer_id'         => $customer_id,
			'payment_method'         => $payment_method, 
			'total'         => $total,
			'transation_history' => wp_json_encode( $charge ),
			'status' => 'completed',
		);
 
		$Transactions = new Transactions();
		$Transactions = $Transactions->add( $tdata );

		// After Booking Hooks
		do_action( 'hydra_booking/after_booking_payment_complete', $bookingdata );

		// return success message
		$response['message'] = 'Payment Completed Successfully';
		wp_send_json_success( $response );
		
	}
}

?>
