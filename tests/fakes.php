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
	public function get( $id ) {
		return isset( \Tfhb_Test_Store::$bookings[ $id ] ) ? clone \Tfhb_Test_Store::$bookings[ $id ] : null;
	}

	public function update( $data ) {
		if ( isset( $data['id'] ) ) {
			$id = $data['id'];
			if ( ! isset( \Tfhb_Test_Store::$bookings[ $id ] ) ) {
				\Tfhb_Test_Store::$bookings[ $id ] = (object) array( 'id' => $id );
			}
			foreach ( $data as $k => $v ) {
				\Tfhb_Test_Store::$bookings[ $id ]->$k = $v;
			}
		}
		return true;
	}

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

class Attendees {
	public function claimStatusChange( $id, $expected_status, $new_status ) {
		if ( ! isset( \Tfhb_Test_Store::$attendees[ $id ] ) || (string) \Tfhb_Test_Store::$attendees[ $id ]->status !== (string) $expected_status ) {
			return false;
		}
		\Tfhb_Test_Store::$attendees[ $id ]->status = $new_status;
		return true;
	}

	public function update( $data ) {
		if ( isset( $data['id'] ) ) {
			$id = $data['id'];
			if ( ! isset( \Tfhb_Test_Store::$attendees[ $id ] ) ) {
				\Tfhb_Test_Store::$attendees[ $id ] = (object) array( 'id' => $id );
			}
			foreach ( $data as $k => $v ) {
				\Tfhb_Test_Store::$attendees[ $id ]->$k = $v;
			}
		}
		return true;
	}

	public function getAttendeeWithBooking( $where = null, $limit = null, $orderBy = null ) {
		$attendee_id = null;
		if ( is_array( $where ) ) {
			foreach ( $where as $cond ) {
				if ( is_array( $cond ) && 'id' === $cond[0] && '=' === $cond[1] ) {
					$attendee_id = $cond[2];
				}
			}
		}
		// Query without an id (e.g. booking_id + status): return every matching attendee.
		if ( ! $attendee_id && is_array( $where ) ) {
			$rows = array();
			foreach ( \Tfhb_Test_Store::$attendees as $att ) {
				$match = true;
				foreach ( $where as $cond ) {
					$field = $cond[0];
					$actual = isset( $att->$field ) ? $att->$field : null;
					if ( ( '=' === $cond[1] && $actual != $cond[2] ) || ( '!=' === $cond[1] && $actual == $cond[2] ) ) {
						$match = false;
					}
				}
				if ( $match ) {
					$rows[] = clone $att;
				}
			}
			return 1 === $limit ? ( $rows ? $rows[0] : null ) : $rows;
		}
		if ( $attendee_id && isset( \Tfhb_Test_Store::$attendees[ $attendee_id ] ) ) {
			$att = clone \Tfhb_Test_Store::$attendees[ $attendee_id ];
			// Simulate a concurrent request that read the row before another request changed it.
			if ( isset( $GLOBALS['tfhb_test_stale_status'][ $attendee_id ] ) ) {
				$att->status = $GLOBALS['tfhb_test_stale_status'][ $attendee_id ];
				unset( $GLOBALS['tfhb_test_stale_status'][ $attendee_id ] );
			}
			if ( ! empty( $att->booking_id ) && isset( \Tfhb_Test_Store::$bookings[ $att->booking_id ] ) ) {
				$b = \Tfhb_Test_Store::$bookings[ $att->booking_id ];
				foreach ( get_object_vars( $b ) as $k => $v ) {
					if ( ! isset( $att->$k ) ) {
						$att->$k = $v;
					}
				}
			}
			return $att;
		}
		return null;
	}
}

class BookingMeta {
	public function getWithIdKey( $booking_id, $meta_key, $limit = null ) {
		foreach ( \Tfhb_Test_Store::$booking_meta as $meta ) {
			if ( (int) $meta->booking_id === (int) $booking_id && $meta->meta_key === $meta_key ) {
				return clone $meta;
			}
		}
		return null;
	}

	public function add( $data ) {
		$id = count( \Tfhb_Test_Store::$booking_meta ) + 1;
		$obj = (object) array_merge( array( 'id' => $id ), $data );
		\Tfhb_Test_Store::$booking_meta[ $id ] = $obj;
		return $id;
	}

	public function update( $data ) {
		if ( isset( $data['id'] ) && isset( \Tfhb_Test_Store::$booking_meta[ $data['id'] ] ) ) {
			foreach ( $data as $k => $v ) {
				\Tfhb_Test_Store::$booking_meta[ $data['id'] ]->$k = $v;
			}
		}
		return true;
	}

	public function delete( $id ) {
		unset( \Tfhb_Test_Store::$booking_meta[ $id ] );
		return true;
	}
}

class Transactions {
	public function add( $data ) { return 1; }
	public function update( $data ) { return true; }
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

	// Records which attendees were taken off the Google event.
	public function deleteGoogleCalender( $data ) {
		$GLOBALS['tfhb_test_google_removed'][] = is_object( $data ) && isset( $data->id ) ? $data->id : null;
		return true;
	}
}
}
