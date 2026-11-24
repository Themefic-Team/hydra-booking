<?php
namespace HydraBooking\Services\Integrations\Woocommerce;
use HydraBooking\Admin\Controller\Notification;

// don't load directly
defined( 'ABSPATH' ) || exit;


use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Transactions;
use HydraBooking\DB\Meeting;

/**
 *
 *
 * Extend WooCommerce Product data
 *
 * @since 1.0.0
 */
class WooBooking {

	public function __construct() {
	}

	private function map_woo_status_to_booking_status( $woo_status ) {
		$woo_status = strtolower( (string) $woo_status );

		if ( in_array( $woo_status, array( 'processing', 'completed' ), true ) ) {
			return 'confirmed';
		}

		if ( in_array( $woo_status, array( 'cancelled', 'refunded', 'failed' ), true ) ) {
			return 'canceled';
		}

		return 'pending';
	}

	public function sync_booking_status_from_woo_order_status( $order_id, $old_status, $new_status, $order = null ) {
		if ( ! $order instanceof \WC_Order ) {
			$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
		}

		if ( ! $order ) {
			return;
		}

		$booking_status = $this->map_woo_status_to_booking_status( $new_status );
		$booking = new Booking();
		$attendees = new Attendees();

		foreach ( $order->get_items() as $item ) {
			$booking_id  = $item->get_meta( '_tfhb_booking_id' );
			$attendee_id = $item->get_meta( '_tfhb_attendee_id' );

			if ( empty( $booking_id ) || empty( $attendee_id ) ) {
				continue;
			}

			$current_attendee = $attendees->getAttendeeWithBooking(
				array(
					array( 'id', '=', $attendee_id ),
				),
				1,
				'DESC'
			);

			if ( ! $current_attendee ) {
				continue;
			}

			$previous_booking_status = ! empty( $current_attendee->status ) ? $current_attendee->status : '';

			// Always sync payment_status to match WooCommerce order state.
			$attendees->update(
				array(
					'id'             => $attendee_id,
					'payment_status' => $new_status,
				)
			);

			// Prevent duplicate notifications and emails if the booking status did not change.
			if ( $previous_booking_status === $booking_status ) {
				continue;
			}

			// WooCommerce can save one status change twice at the same moment (two
			// requests in the same second). Claim the change atomically: only the request
			// that moves the attendee from its previous status sends emails and runs
			// integrations; the other one stops here.
			if ( ! $attendees->claimStatusChange( $attendee_id, $previous_booking_status, $booking_status ) ) {
				continue;
			}

			// One-to-group meetings share the booking row and Zoom meeting: while another
			// confirmed attendee is on it, only this attendee's status changes.
			$others_confirmed = tfhb_other_confirmed_attendees_count( $booking_id, $attendee_id );

			// Confirmed -> pending (e.g. order put on hold). Confirmed -> canceled is
			// handled by the after_booking_canceled hook below.
			if ( 'confirmed' === $previous_booking_status && 'pending' === $booking_status ) {
				// Zoom is one shared meeting: revoke it only when nobody else on it is confirmed;
				// otherwise block just this attendee's personal registration link.
				if ( 0 === $others_confirmed ) {
					$this->revoke_zoom_meeting_if_confirmed_demoted( $current_attendee );
				} elseif ( class_exists( '\HydraBooking\Services\Integrations\Zoom\ZoomServices' ) ) {
					( new \HydraBooking\Services\Integrations\Zoom\ZoomServices() )->tfhb_cancel_zoom_registrant( $current_attendee );
				}
				// Google: removes only this attendee in a group; deletes the event for one-to-one.
				// It is created again when the booking is confirmed.
				$this->remove_google_event_if_confirmed_demoted( $current_attendee );
			}

			// The attendee status was already set by claimStatusChange() above; the shared
			// booking row follows its attendees (any confirmed -> confirmed, etc.).
			tfhb_recalculate_booking_status( $booking_id );

			$attendee_booking = $attendees->getAttendeeWithBooking(
				array(
					array( 'id', '=', $attendee_id ),
				),
				1,
				'DESC'
			);

			if ( ! $attendee_booking ) {
				continue;
			}

			if ( 'confirmed' === $booking_status ) {
				do_action( 'hydra_booking/after_booking_confirmed', $attendee_booking );
			}

			if ( 'pending' === $booking_status ) {
				do_action( 'hydra_booking/after_booking_pending', $attendee_booking );
			}

			if ( 'canceled' === $booking_status ) {
				do_action( 'hydra_booking/after_booking_canceled', $attendee_booking );
			}

			$notification = new Notification();
			$notification->AddNotification( $attendee_booking );
		}
	}

	public function add_to_cart( $product_id, $data, $attendee_data ) { 
		$product                                      = wc_get_product( $product_id );
		$order_meta                                   = array();
		$order_meta['tfhb_order_meta']['booking_id']  = $data['booking_id'];
		$order_meta['tfhb_order_meta']['attendee_id']  = $attendee_data['id'];
		$order_meta['tfhb_order_meta']['added_time']  = time();
		$order_meta['tfhb_order_meta']['Appointment'] = $this->appointment_label( $data, $attendee_data );
		$cart = WC()->cart;
		$cart->add_to_cart( $product_id, 1, 0, array(), $order_meta );

		return true;
	}

	/**
	 * "2026-11-02 06:30 PM - 07:30 PM ( America/Los_Angeles )": the booking's host-timezone
	 * values shown in the attendee's timezone, one date per occurrence.
	 */
	public function appointment_label( $data, $attendee_data ) {
		$attendee_time_zone = isset( $attendee_data['attendee_time_zone'] ) ? $attendee_data['attendee_time_zone'] : '';
		$viewer_time_zone   = \HydraBooking\Admin\Controller\BookingTime::tz( $attendee_time_zone, isset( $data['availability_time_zone'] ) ? $data['availability_time_zone'] : '' );
		$occurrences        = \HydraBooking\Admin\Controller\BookingTime::booking_instants( $data );

		if ( empty( $occurrences ) ) {
			return $data['meeting_dates'] . ' ' . $data['start_time'] . ' - ' . $data['end_time'] . ' ( ' . $attendee_time_zone . ' )';
		}

		$dates = array();
		foreach ( $occurrences as $occurrence ) {
			$dates[] = \HydraBooking\Admin\Controller\BookingTime::format( $occurrence['start'], $viewer_time_zone, 'Y-m-d' );
		}

		return implode( ',', $dates ) . ' '
			. \HydraBooking\Admin\Controller\BookingTime::format( $occurrences[0]['start'], $viewer_time_zone, 'h:i A' ) . ' - '
			. \HydraBooking\Admin\Controller\BookingTime::format( $occurrences[0]['end'], $viewer_time_zone, 'h:i A' )
			. ' ( ' . $viewer_time_zone->getName() . ' )';
	}

	// display booking_id  into checkout page
	public function tfhb_woocommerce_get_item_data( $item_data, $cart_item ) { 
		if ( ! empty( $cart_item['tfhb_order_meta']['booking_id'] ) ) {
			$item_data[] = array(
				'key'   => esc_html( __( 'Appointment ', 'hydra-booking' ) ),
				'value' => $cart_item['tfhb_order_meta']['Appointment'],
			);
		}
		return $item_data;
	}

	public function woocommerce_cart_loaded_from_session_callback($cart) {
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		$expire_minutes = !empty($general_settings['after_cart_expire']) ? intval($general_settings['after_cart_expire']) : 60;
		$expire_seconds = $expire_minutes * 60;
	
		foreach ($cart->get_cart() as $key => $item) {
			$added_time = isset($item['tfhb_order_meta']['added_time']) ? intval($item['tfhb_order_meta']['added_time']) : 0;
			if ($added_time && (time() - $added_time) > $expire_seconds) {
	
				$this->release_cart_booking( $item );
	
				$cart->remove_cart_item($key);
			}
		}
	}

	public function woocommerce_remove_cart_item_callback($cart_item_key, $cart) {
		$cart_item = $cart->get_cart_item($cart_item_key);
		if (!$cart_item) return;
	
		$this->release_cart_booking( $cart_item );
	
	}

	/**
	 * Release the booking held by a cart item that was removed or expired.
	 *
	 * Only that cart's attendee is canceled, and only while still pending (an
	 * attendee whose order was placed is never touched here). The shared booking
	 * row then follows its attendees, so in a group meeting the people who already
	 * paid keep their booking.
	 *
	 * @param array $cart_item WooCommerce cart item.
	 * @return void
	 */
	private function release_cart_booking( $cart_item ) {
		$meta        = isset( $cart_item['tfhb_order_meta'] ) && is_array( $cart_item['tfhb_order_meta'] ) ? $cart_item['tfhb_order_meta'] : array();
		$attendee_id = ! empty( $meta['attendee_id'] ) ? absint( $meta['attendee_id'] ) : 0;
		$booking_id  = ! empty( $meta['booking_id'] ) ? absint( $meta['booking_id'] ) : 0;

		if ( $attendee_id ) {
			$attendees = new Attendees();
			$attendees->claimStatusChange( $attendee_id, 'pending', 'canceled' );
		}

		if ( $booking_id ) {
			tfhb_recalculate_booking_status( $booking_id );
		}
	}
	
	
	// update order meta data
	public function tfhb_booking_custom_order_data( $item, $cart_item_key, $values, $order ) {

		// Assigning data into variables.
		$booking_id  = ! empty( $values['tfhb_order_meta']['booking_id'] ) ? $values['tfhb_order_meta']['booking_id'] : '';
		$attendee_id  = ! empty( $values['tfhb_order_meta']['attendee_id'] ) ? $values['tfhb_order_meta']['attendee_id'] : '';
		$appointment = ! empty( $values['tfhb_order_meta']['Appointment'] ) ? $values['tfhb_order_meta']['Appointment'] : '';
		$added_time  = ! empty( $values['tfhb_order_meta']['added_time'] ) ? $values['tfhb_order_meta']['added_time'] : '';

		if ( $booking_id ) {
			$item->update_meta_data( '_tfhb_booking_id', $booking_id, true );
		}
		if ( $attendee_id ) {
			$item->update_meta_data( '_tfhb_attendee_id', $attendee_id, true );
		}

		if ( $appointment ) {
			$item->update_meta_data( 'tfhb_appointment', $appointment, true );
		}
		if ( $appointment ) {
			$item->update_meta_data( '_added_time', $added_time, true );
		}
	}

	// Add order id to the hotel room meta field
	public function tfhb_add_booking_data_checkout_order_processed( $order_id, $posted_data = array(), $order = null ) {

		if ( ! $order instanceof \WC_Order ) {
			$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
		}

		if ( ! $order ) {
			return;
		}

		$this->apply_checkout_to_booking_items( $order, $order_id );
	}

	/**
	 * Block Checkout Transaction
	 */
	public function tfhb_add_booking_data_checkout_order_processed_block_checkout($order){
		$this->apply_checkout_to_booking_items( $order, $order->get_id() );
	}

	/**
	 * Link a placed WooCommerce order to its booking items and set each attendee's
	 * status from the payment: paid (with auto-confirm on) -> confirmed, otherwise
	 * pending. Shared by the classic and the block checkout handlers.
	 *
	 * @param \WC_Order $order
	 * @param int       $order_id
	 * @return void
	 */
	private function apply_checkout_to_booking_items( $order, $order_id ) {
		$general_settings     = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		$booking_auto_confirm = ! isset( $general_settings['booking_status'] ) || '1' === (string) $general_settings['booking_status'];
		$is_order_paid        = method_exists( $order, 'is_paid' ) ? $order->is_paid() : ( method_exists( $order, 'get_status' ) && in_array( $order->get_status(), array( 'processing', 'completed' ), true ) );
		$target_status        = ( $is_order_paid && $booking_auto_confirm ) ? 'confirmed' : 'pending';
		$order_payment_status = method_exists( $order, 'get_status' ) ? $order->get_status() : ( $is_order_paid ? 'processing' : 'pending' );

		foreach ( $order->get_items() as $item_id => $item ) {
			if ( empty( $item->get_meta( '_tfhb_booking_id' ) ) ) {
				continue;
			}

			$items_price = $item->get_total();
			$booking_id  = $item->get_meta( '_tfhb_booking_id' );
			$attendee_id = $item->get_meta( '_tfhb_attendee_id' );
			$appointment = $item->get_meta( 'tfhb_appointment' );
			$order->update_meta_data(
				'tfhb_order_meta',
				array(
					'booking_id'  => $booking_id,
					'attendee_id' => $attendee_id,
					'Appointment' => $appointment,
				)
			);

			$Attendees      = new Attendees();
			$current        = $Attendees->getAttendeeWithBooking( array( array( 'id', '=', $attendee_id ) ), 1, 'DESC' );
			$current_status = ( $current && isset( $current->status ) ) ? (string) $current->status : '';

			// Claim the status change so a duplicate checkout event cannot send the emails twice.
			if ( $current_status !== $target_status && ! $Attendees->claimStatusChange( $attendee_id, $current_status, $target_status ) ) {
				continue;
			}

			$Attendees->update(
				array(
					'id'             => $attendee_id,
					'payment_status' => $order_payment_status,
				)
			);

			// The shared booking row follows its attendees: an unpaid attendee joining a
			// group slot does not turn it pending while someone else is confirmed.
			tfhb_recalculate_booking_status( $booking_id );

			$attendeeBooking = $Attendees->getAttendeeWithBooking( array( array( 'id', '=', $attendee_id ) ), 1, 'DESC' );
			if ( ! $attendeeBooking ) {
				continue;
			}

			if ( 'confirmed' === $attendeeBooking->status ) {
				// Single Booking & Mail Notification, Google Calendar // Zoom Meeting
				do_action( 'hydra_booking/after_booking_confirmed', $attendeeBooking );
			} else {
				do_action( 'hydra_booking/after_booking_pending', $attendeeBooking );
			}

			$notification = new Notification();
			$notification->AddNotification( $attendeeBooking );

			// Add transaction
			$transactions       = new Transactions();
			$transation_history = array(
				'wc_order_id' => $order_id,
				'item_id'     => $item_id,
			);
			$transactions->add(
				array(
					'booking_id'         => $booking_id,
					'attendee_id'        => $attendee_id,
					'meeting_id'         => $attendeeBooking->meeting_id,
					'host_id'            => $attendeeBooking->host_id,
					'customer_id'        => $attendee_id,
					'payment_method'     => ! empty( $attendeeBooking->payment_method ) ? $attendeeBooking->payment_method : 'woocommerce',
					'total'              => $items_price,
					'status'             => $order->get_status(),
					'transation_history' => json_encode( $transation_history, true ),
				)
			);
		}
	}

	public function tfhb_woocommerce_thankyou( $id ) {
		$order = wc_get_order( $id );

		// check if order is not empty
		if ( ! $order ) {
			return;
		}

		$items = $order->get_items();
		foreach ( $items as $item_id => $item ) {

			if ( ! empty( $item->get_meta( '_tfhb_booking_id' ) ) ) { 

				$booking_id = $item->get_meta( '_tfhb_booking_id' );
				$attendee_id = $item->get_meta( '_tfhb_attendee_id' );
				$Attendees    = new Attendees();
				$updateData = array(
					'id'             => $attendee_id,  
					'payment_status' => $order->get_status(),
				);
				// update booking

				// update booking
				$Attendees->update( $updateData );

			}
		}
	}


	public function getAllProductList() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array();
		}
		$args        = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
		);
		$products    = new \WP_Query( $args );
		$currency 	=  get_woocommerce_currency();
		$productList = array();
		if ( $products->have_posts() ) {
			while ( $products->have_posts() ) {
				$products->the_post();
				$productList[] = array(
					'value' => get_the_ID(),
					'name'  => '[#' . get_the_ID() . '] - ' . get_the_title() . ' - ' . get_post_meta( get_the_ID(), '_price', true ) .' ( '.$currency.' )',
				);
			}
		}
		return $productList;
	}

	/**
	 * Revoke Zoom meeting when booking is demoted from confirmed to pending or canceled.
	 *
	 * @param object $attendee_booking
	 * @return void
	 */
	/**
	 * Take a demoted (confirmed -> pending) attendee off the Google Calendar event.
	 * In a group meeting only this attendee is removed; for one-to-one the event is
	 * deleted and created again when the booking is confirmed.
	 *
	 * @param object $attendee_booking
	 * @return void
	 */
	public function remove_google_event_if_confirmed_demoted( $attendee_booking ) {
		if ( ! is_object( $attendee_booking ) || ! class_exists( '\HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar' ) ) {
			return;
		}
		$google = new \HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar();
		if ( method_exists( $google, 'deleteGoogleCalender' ) ) {
			$google->deleteGoogleCalender( $attendee_booking );
		}
	}

	public function revoke_zoom_meeting_if_confirmed_demoted( $attendee_booking ) {
		if ( ! is_object( $attendee_booking ) ) {
			return;
		}

		$booking_id = ! empty( $attendee_booking->booking_id ) ? $attendee_booking->booking_id : ( ! empty( $attendee_booking->id ) ? $attendee_booking->id : 0 );
		if ( empty( $booking_id ) ) {
			return;
		}

		// Call ZoomServices cancel if class exists
		if ( class_exists( '\HydraBooking\Services\Integrations\Zoom\ZoomServices' ) ) {
			$zoom = new \HydraBooking\Services\Integrations\Zoom\ZoomServices();
			$zoom->tfhb_cancel_zoom_meeting( $attendee_booking );
		}

		// Ensure meeting_locations zoom address is cleared in booking record
		$booking = new Booking();
		$booking_data = $booking->get( $booking_id );
		if ( $booking_data && ! empty( $booking_data->meeting_locations ) ) {
			$locations = is_string( $booking_data->meeting_locations ) ? json_decode( $booking_data->meeting_locations ) : $booking_data->meeting_locations;
			if ( is_object( $locations ) && isset( $locations->zoom ) ) {
				$locations->zoom->address = array(
					'link'     => '',
					'password' => '',
				);
			} elseif ( is_array( $locations ) && isset( $locations['zoom'] ) ) {
				$locations['zoom']['address'] = array(
					'link'     => '',
					'password' => '',
				);
			}

			$booking->update(
				array(
					'id'                => $booking_id,
					'meeting_locations' => $locations,
				)
			);
		}
	}
}
