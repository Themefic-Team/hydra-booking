<?php
namespace HydraBooking\App;

// Use Classes
use HydraBooking\App\Shortcode\HydraBookingShortcode;
use HydraBooking\App\Enqueue;
use HydraBooking\App\BookingLocation;
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;

class App {
	public function __construct() {

		$this->init();
	}

	public function init() {

		// Load Enqueue Class
		new Enqueue();

		// Load Shortcode Class
		new HydraBookingShortcode();
 



		add_filter( 'query_vars', array( $this, 'tfhb_single_query_vars' ) );

		add_filter( 'template_include', array( $this, 'tfhb_single_page_template' ) );

	 

		add_rewrite_rule(
			'^meeting/([0-9]+)/?$',
			'index.php?hydra-booking=meeting&meeting-id=$matches[1]&type=$matches[2]',
			'top'
		);
		// Create Rewrite Rule for Reschedule hydra-booking.local/?hydra-booking=booking&hash=2121&type=reschedule
		add_rewrite_rule(
			'^booking/([0-9]+)/?$',
			'index.php?hydra-booking=booking&hash=$matches[1]&meeting-id=$matches[2]&type=$matches[3]',
			'top'
		);

		add_action( 'pre_get_posts', array( $this, 'tfhb_remove_posttype_request' ) );
		add_filter( 'single_template', array( $this, 'tfhb_single_meeting_template' ) );
		
		add_filter( 'post_type_link',  array( $this,'tfhb_meeting_permalink'), 10, 2 );
	
	 }

	public function tfhb_meeting_permalink( $permalink, $post ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( !empty($permalink_structure) && $post->post_type === 'tfhb_meeting' && '/%postname%/' == $permalink_structure) {
			return home_url( '/' . $post->post_name . '/' );
		}
		return $permalink;
	}
	public function tfhb_single_meeting_template( $single_template ) {
		global $post;

		/**
		 * Single Meeting
		 *
		 * single-meeting.php
		 */
		if ( 'tfhb_meeting' === $post->post_type ) {
			return THB_PATH . '/app/Content/Template/single-meeting.php';
		}

		return $single_template;
	}

	public function tfhb_remove_posttype_request( $query ) {
		// Only noop the main query.
		if ( ! $query->is_main_query() ) {
			return;
		}

		// Only noop our very specific rewrite rule match.
		if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
			return;
		}

		// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match.
		if ( ! empty( $query->query['name'] ) ) {
			$post_types = array(
				'post', // important to  not break your standard posts
				'page', // important to  not break your standard pages
				'tfhb_meeting',
			);

			$query->set( 'post_type', $post_types );

		}
	}

	public function tfhb_single_query_vars( $query_vars ) {
		$query_vars[] = 'hydra-booking';
		$query_vars[] = 'meeting';
		$query_vars[] = 'meeting-id';
		$query_vars[] = 'meetingId';
		$query_vars[] = 'hash';
		$query_vars[] = 'type';
		return $query_vars;
	}

	public function tfhb_single_page_template( $template ) {

		if ( get_query_var( 'hydra-booking' ) === 'meeting' && get_query_var( 'meetingId' )) {
			 

			$custom_template = load_template( THB_PATH . '/app/Content/Template/embed.php', true );
			return $custom_template;
		}
		if ( get_query_var( 'hydra-booking' ) === 'meeting' && get_query_var( 'meeting' ) ) {
			 
			$custom_template = load_template( THB_PATH . '/app/Content/Template/single-meeting.php', false );
			return $custom_template;
		} 

		// Reschedule Page
		if ( get_query_var( 'hydra-booking' ) === 'booking' && get_query_var( 'hash' ) && get_query_var( 'type' ) === 'reschedule' ) {
			$custom_template = load_template( THB_PATH . '/app/Content/Template/reschedule.php', false );
			return $custom_template;

		}
		// Cenceled Page
		if ( get_query_var( 'hydra-booking' ) === 'booking' && get_query_var( 'hash' ) && get_query_var( 'type' ) === 'cancel' ) {
			 
			if ( ! wp_script_is( 'tfhb-app-script', 'enqueued' ) ) {
				wp_enqueue_script( 'tfhb-app-script' );
			}
		 
			
			$Attendee = new Attendees();
			$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
				array(
					array('hash', '=',get_query_var( 'hash' )),
				),
				1,
				'DESC'
			); 
			if ( ! $attendeeBooking ) {
				return $template;
			} 
			$custom_template = load_template(
				THB_PATH . '/app/Content/Template/meeting-cencel.php',
				false,
				array(
					'attendeeBooking'         => $attendeeBooking, 
				)
			);
			return $custom_template;

		}
		return $template;
	}

	 
}
