<?php
if (! defined('ABSPATH')) {
	exit;
}

if (! function_exists('tfhb_print_r')) {
	function tfhb_print_r($data)
	{
		echo '<pre>';
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		print_r($data);
		echo '</pre>';
		// exit;
	}
}


function tfhb_character_limit_callback($str, $limit, $dots = true)
{
	$str = wp_strip_all_tags((string) $str);
	if ( mb_strlen( $str ) > $limit ) {
		$sub        = mb_substr( $str, 0, $limit );
		$last_space = mb_strrpos( $sub, ' ' );
		if ( false !== $last_space && $last_space > ( $limit * 0.4 ) ) {
			$sub = mb_substr( $sub, 0, $last_space );
		}
		$sub = rtrim( $sub, " \t\n\r\0\x0B.,;:-" );
		if ( true === $dots ) {
			return $sub . '...';
		} else {
			return $sub;
		}
	} else {
		return $str;
	}
}

/**
 * checked pro plugins is active or not
 *
 * @return string
 */

function tfhb_is_hydra_booking_pro_active()
{
	if (class_exists('TFHB_INIT_PRO')) {
		return true;
	} else {
		return false;
	}
} 

/*
 * Load Template
 * 
 * @param string $template_path
 * @param array $data
 * @return string
 */


/**
 * Public link for an attendee's booking page (reschedule, cancel, confirmation).
 *
 * The secret attendee hash is the only identifier. The meeting id is not added:
 * the server always takes the meeting from the booking, and old links that still
 * carry meetingId keep working because that value is ignored.
 *
 * @param string $hash Attendee booking hash.
 * @param string $type reschedule | cancel | confirmation.
 * @return string
 */
if ( ! function_exists( 'tfhb_booking_action_url' ) ) {
	function tfhb_booking_action_url( $hash, $type ) {
		return add_query_arg(
			array(
				'hydra-booking' => 'booking',
				'hash'          => rawurlencode( (string) $hash ),
				'type'          => rawurlencode( (string) $type ),
			),
			home_url( '/' )
		);
	}
}

/**
 * Whether meeting links (Zoom / Google Meet join links, add-to-calendar) may be
 * shown for an attendee booking. Only bookings that are not pending and not
 * canceled get them: a pending booking has no meeting created yet, and a
 * canceled booking's meeting has been removed.
 *
 * @param object $attendee_booking Row from Attendees::getAttendeeWithBooking().
 * @return bool
 */
if ( ! function_exists( 'tfhb_booking_links_allowed' ) ) {
	function tfhb_booking_links_allowed( $attendee_booking ) {
		$status = is_object( $attendee_booking ) && isset( $attendee_booking->status ) ? strtolower( (string) $attendee_booking->status ) : '';
		return ! in_array( $status, array( '', 'pending', 'canceled', 'cancelled' ), true );
	}
}

/**
 * Whether a rescheduled booking already had a live meeting before the move.
 *
 * The public reschedule handler sets `tfhb_previous_status`; other callers fall
 * back to the attendee's current status. Pending or canceled bookings never had
 * a Zoom / calendar event, so a reschedule must not create one either.
 *
 * @param object $attendee_booking Row passed to hydra_booking/after_booking_schedule.
 * @return bool
 */
if ( ! function_exists( 'tfhb_reschedule_had_live_meeting' ) ) {
	function tfhb_reschedule_had_live_meeting( $attendee_booking ) {
		if ( ! is_object( $attendee_booking ) ) {
			return false;
		}
		$status = isset( $attendee_booking->tfhb_previous_status ) ? $attendee_booking->tfhb_previous_status : ( isset( $attendee_booking->status ) ? $attendee_booking->status : '' );
		return ! in_array( strtolower( (string) $status ), array( 'pending', 'canceled', 'cancelled' ), true );
	}
}

/**
 * Number of other confirmed attendees on the same booking row.
 *
 * One-to-group meetings share one booking row and one Zoom / calendar event.
 * A change for one attendee must not cancel the shared meeting while someone
 * else on it is still confirmed.
 *
 * @param int $booking_id          Booking row id.
 * @param int $exclude_attendee_id Attendee to leave out (the one being changed).
 * @return int
 */
if ( ! function_exists( 'tfhb_other_confirmed_attendees_count' ) ) {
	function tfhb_other_confirmed_attendees_count( $booking_id, $exclude_attendee_id = 0 ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id || ! class_exists( '\HydraBooking\DB\Attendees' ) ) {
			return 0;
		}

		$attendees = new \HydraBooking\DB\Attendees();
		$rows      = $attendees->getAttendeeWithBooking(
			array(
				array( 'booking_id', '=', $booking_id ),
				array( 'status', '=', 'confirmed' ),
			)
		);

		$count = 0;
		foreach ( (array) $rows as $row ) {
			if ( is_object( $row ) && (int) $row->id !== (int) $exclude_attendee_id ) {
				$count++;
			}
		}
		return $count;
	}
}

/**
 * Whether an attendee entry still takes a seat (anything except canceled).
 *
 * @param object|array $attendee Attendee row, or an entry of Booking::getBookingWithAttendees()->attendees.
 * @return bool
 */
if ( ! function_exists( 'tfhb_attendee_is_active' ) ) {
	function tfhb_attendee_is_active( $attendee ) {
		$attendee = is_array( $attendee ) ? (object) $attendee : $attendee;
		if ( ! is_object( $attendee ) || empty( $attendee->id ) ) {
			return false; // LEFT JOIN placeholder row of a booking without attendees.
		}
		$status = isset( $attendee->status ) ? strtolower( (string) $attendee->status ) : '';
		return ! in_array( $status, array( 'canceled', 'cancelled' ), true );
	}
}

/**
 * Number of seats taken in a group slot: attendees that are not canceled.
 *
 * @param array|string $attendees Attendees list (array, or the JSON string from the query).
 * @return int
 */
if ( ! function_exists( 'tfhb_count_active_attendees' ) ) {
	function tfhb_count_active_attendees( $attendees ) {
		if ( is_string( $attendees ) ) {
			$attendees = json_decode( $attendees );
		}
		$count = 0;
		foreach ( (array) $attendees as $attendee ) {
			if ( tfhb_attendee_is_active( $attendee ) ) {
				$count++;
			}
		}
		return $count;
	}
}

/**
 * Set a booking row's status from its attendees, so every code path agrees:
 * any confirmed attendee -> confirmed; otherwise any pending -> pending;
 * otherwise (all canceled, or none left) -> canceled. Other attendee
 * statuses (e.g. rescheduled) leave the booking status as it is, and a
 * completed booking is not moved back to confirmed.
 *
 * @param int $booking_id Booking row id.
 * @return string The booking status after the update ('' when unknown).
 */
if ( ! function_exists( 'tfhb_recalculate_booking_status' ) ) {
	function tfhb_recalculate_booking_status( $booking_id ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id || ! class_exists( '\HydraBooking\DB\Attendees' ) || ! class_exists( '\HydraBooking\DB\Booking' ) ) {
			return '';
		}

		$attendees = new \HydraBooking\DB\Attendees();
		$rows      = $attendees->getAttendeeWithBooking( array( array( 'booking_id', '=', $booking_id ) ) );

		$has_confirmed = false;
		$has_pending   = false;
		$has_other     = false;
		foreach ( (array) $rows as $row ) {
			if ( ! is_object( $row ) ) {
				continue;
			}
			$status = strtolower( (string) $row->status );
			if ( in_array( $status, array( 'confirmed', 'completed', 'approved' ), true ) ) {
				$has_confirmed = true;
			} elseif ( 'pending' === $status ) {
				$has_pending = true;
			} elseif ( ! in_array( $status, array( 'canceled', 'cancelled' ), true ) ) {
				$has_other = true;
			}
		}

		$booking = new \HydraBooking\DB\Booking();
		$current = $booking->get( $booking_id );
		$current = is_object( $current ) && isset( $current->status ) ? strtolower( (string) $current->status ) : '';

		if ( $has_confirmed ) {
			$new = 'completed' === $current ? 'completed' : 'confirmed';
		} elseif ( $has_pending ) {
			$new = 'pending';
		} elseif ( $has_other ) {
			return $current; // Unknown mix (e.g. rescheduled): do not guess.
		} else {
			$new = 'canceled';
		}

		if ( $new !== $current ) {
			$booking->update(
				array(
					'id'     => $booking_id,
					'status' => $new,
				)
			);
		}
		return $new;
	}
}

/**
 * An attendee's personal Zoom join links (group meetings with Zoom registration).
 * Empty when the meeting uses the shared link (one-to-one, or a Basic Zoom plan).
 *
 * @param object $attendee_booking Attendee booking row (id, booking_id).
 * @return string[] One join URL per Zoom meeting (several for recurring dates).
 */
if ( ! function_exists( 'tfhb_zoom_personal_join_urls' ) ) {
	function tfhb_zoom_personal_join_urls( $attendee_booking ) {
		if ( ! is_object( $attendee_booking ) || empty( $attendee_booking->id ) || empty( $attendee_booking->booking_id ) || ! class_exists( '\HydraBooking\DB\BookingMeta' ) ) {
			return array();
		}
		$meta = ( new \HydraBooking\DB\BookingMeta() )->getWithIdKey( $attendee_booking->booking_id, 'zoom_registrants', 1 );
		if ( ! $meta ) {
			return array();
		}
		$map = json_decode( is_string( $meta->value ) ? $meta->value : wp_json_encode( $meta->value ), true );
		$key = (string) $attendee_booking->id;
		if ( ! is_array( $map ) || empty( $map[ $key ] ) || ! is_array( $map[ $key ] ) ) {
			return array();
		}
		$urls = array();
		foreach ( $map[ $key ] as $entry ) {
			if ( ! empty( $entry['join_url'] ) ) {
				$urls[] = (string) $entry['join_url'];
			}
		}
		return $urls;
	}
}

/**
 * Constant-time check of a code taken from a link against the stored code.
 *
 * Both values must be non-empty strings. A JSON boolean or number from a
 * user-controlled link (e.g. {"code":true}) never matches, unlike PHP's loose ==.
 *
 * @param mixed $saved_code Code stored on the server.
 * @param mixed $given_code Code decoded from the request.
 * @return bool
 */
if ( ! function_exists( 'tfhb_verification_code_matches' ) ) {
	function tfhb_verification_code_matches( $saved_code, $given_code ) {
		if ( ! is_string( $saved_code ) || ! is_string( $given_code ) || '' === $saved_code || '' === $given_code ) {
			return false;
		}
		return hash_equals( $saved_code, $given_code );
	}
}

/**
 * Why an attendee booking can no longer be rescheduled, or '' when it can.
 *
 * Shared by the reschedule page (so the calendar does not open) and the
 * reschedule submit handler (so the rule cannot be skipped).
 *
 * @param object $attendee_booking Row from Attendees::getAttendeeWithBooking().
 * @param string $fallback_tz      Timezone used when the booking has none stored.
 * @return string Translated message, or '' when rescheduling is allowed.
 */
if ( ! function_exists( 'tfhb_reschedule_block_message' ) ) {
	function tfhb_reschedule_block_message( $attendee_booking, $fallback_tz = '' ) {
		if ( ! is_object( $attendee_booking ) ) {
			return __( 'Invalid Booking', 'hydra-booking' );
		}

		$status = isset( $attendee_booking->status ) ? strtolower( (string) $attendee_booking->status ) : '';
		if ( in_array( $status, array( 'canceled', 'cancelled' ), true ) ) {
			return __( 'Booking is already Canceled', 'hydra-booking' );
		}
		if ( 'rescheduled' === $status ) {
			return __( 'Booking is already rescheduled', 'hydra-booking' );
		}
		if ( 'completed' === $status ) {
			return __( 'Booking is already completed', 'hydra-booking' );
		}

		$tz_args = '' !== (string) $fallback_tz ? array( (string) $fallback_tz ) : array();
		$seconds = \HydraBooking\Admin\Controller\BookingTime::seconds_until_start( $attendee_booking, ...$tz_args );
		if ( null === $seconds ) {
			return '';
		}

		// Same "allowed reschedule before meeting start" rule as the submit handler.
		$settings = get_option( '_tfhb_general_settings' );
		$limit    = is_array( $settings ) && ! empty( $settings['allowed_reschedule_before_meeting_start'] ) ? $settings['allowed_reschedule_before_meeting_start'] : 0;
		if ( is_array( $limit ) ) {
			$limit_value  = isset( $limit[0]['limit'] ) ? (int) $limit[0]['limit'] : 0;
			$limit_format = isset( $limit[0]['times'] ) ? $limit[0]['times'] : 'minutes';
		} else {
			$limit_value  = (int) $limit;
			$limit_format = 'minutes';
		}
		if ( $limit_value > 0 ) {
			$gap = ( 'hours' === $limit_format ) ? $limit_value * 3600 : $limit_value * 60;
			if ( $seconds < $gap ) {
				return __( 'You can not reschedule the meeting before ', 'hydra-booking' ) . $limit_value . ' ' . $limit_format;
			}
		}

		// A meeting that has already started cannot be rescheduled from the public link.
		// Admins and booking managers may still move a missed meeting.
		$can_manage = current_user_can( 'manage_options' ) || current_user_can( 'tfhb_manage_booking' );
		if ( $seconds <= 0 && ! $can_manage ) {
			return __( 'This meeting has already started, so it can no longer be rescheduled.', 'hydra-booking' );
		}

		return '';
	}
}

/**
 * Safely load header template without triggering deprecated notices on block themes (FSE).
 *
 * @param string|null $name The name of the specialized header.
 */
if ( ! function_exists( 'tfhb_get_header' ) ) {
	function tfhb_get_header( $name = null ) {
		$templates = array();
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "header-{$name}.php";
		}
		$templates[] = 'header.php';

		if ( locate_template( $templates ) ) {
			get_header( $name );
		} else {
			?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
			<?php
		}
	}
}

/**
 * Safely load footer template without triggering deprecated notices on block themes (FSE).
 *
 * @param string|null $name The name of the specialized footer.
 */
if ( ! function_exists( 'tfhb_get_footer' ) ) {
	function tfhb_get_footer( $name = null ) {
		$templates = array();
		$name      = (string) $name;
		if ( '' !== $name ) {
			$templates[] = "footer-{$name}.php";
		}
		$templates[] = 'footer.php';

		if ( locate_template( $templates ) ) {
			get_footer( $name );
		} else {
			wp_footer();
			?>
</body>
</html>
			<?php
		}
	}
}
