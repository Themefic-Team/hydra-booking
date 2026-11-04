<?php
/**
 * In-memory replacements for the database and calendar classes used by the slot engine.
 */

namespace HydraBooking\DB {

class Meeting {
	public function get( $id ) {
		return isset( \Tfhb_Test_Store::$meetings[ $id ] ) ? \Tfhb_Test_Store::$meetings[ $id ] : null;
	}
}

class Booking {
	public function getBookingWithAttendees( $where = null, $limit = null, $orderBy = null ) {
		$rows = array();
		foreach ( \Tfhb_Test_Store::$bookings as $booking ) {
			if ( \Tfhb_Test_Store::matches( $booking, $where ) ) {
				$rows[] = clone $booking;
			}
		}
		if ( 1 === $limit ) {
			return $rows ? $rows[0] : null;
		}
		return $rows;
	}
}

class Host {
	public function get( $id ) {
		return null;
	}
}

}

namespace HydraBooking\Services\Integrations\GoogleCalendar {

class GoogleCalendar {
	public function getBusyTimeSlots( $host_id, $selected_date, $selected_time_zone, $selected_time_format ) {
		return array();
	}
}
}
