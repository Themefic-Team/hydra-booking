<?php

namespace HydraBooking\Admin\Controller;

// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timezone-safe date/time helpers.
 *
 * Bookings are stored as host-local wall-clock values: `meeting_dates` (Y-m-d, comma
 * separated for recurring meetings), `start_time` / `end_time` ('h:i A') and the
 * `availability_time_zone` they were taken in. Every comparison or conversion should
 * turn those values into an absolute moment through this class first, and only format
 * them for a viewer's timezone at display time.
 */
class BookingTime {

	/**
	 * First usable timezone from the candidates, then the site timezone, then UTC.
	 * Never throws, so an empty or invalid stored value can't break a request.
	 *
	 * @return \DateTimeZone
	 */
	public static function tz( ...$candidates ) {
		if ( function_exists( 'wp_timezone_string' ) ) {
			$candidates[] = wp_timezone_string();
		}

		foreach ( $candidates as $candidate ) {
			if ( $candidate instanceof \DateTimeZone ) {
				return $candidate;
			}
			if ( ! is_string( $candidate ) || '' === trim( $candidate ) ) {
				continue;
			}
			try {
				return new \DateTimeZone( trim( $candidate ) );
			} catch ( \Exception $e ) {
				continue;
			}
		}

		return new \DateTimeZone( 'UTC' );
	}

	/**
	 * Timezone name usable in an IANA-only dropdown. Offsets such as '+05:30' are
	 * mapped to a matching region when possible.
	 *
	 * @return string
	 */
	public static function iana_name( $timezone, $fallback = 'UTC' ) {
		$timezone = is_string( $timezone ) ? trim( $timezone ) : '';
		if ( '' === $timezone ) {
			return $fallback;
		}

		if ( in_array( $timezone, \DateTimeZone::listIdentifiers( \DateTimeZone::ALL_WITH_BC ), true ) ) {
			return $timezone;
		}

		if ( preg_match( '/^(?:UTC)?([+-])(\d{1,2}):?(\d{2})?$/i', $timezone, $m ) ) {
			$seconds = ( (int) $m[2] * 3600 + ( isset( $m[3] ) ? (int) $m[3] : 0 ) * 60 ) * ( '-' === $m[1] ? -1 : 1 );
			$name    = timezone_name_from_abbr( '', $seconds, 0 );
			if ( false !== $name ) {
				return $name;
			}
		}

		return $fallback;
	}

	public static function utc() {
		return new \DateTimeZone( 'UTC' );
	}

	public static function now() {
		return new \DateTimeImmutable( 'now', self::utc() );
	}

	/**
	 * Absolute moment for a wall-clock date and time in a timezone.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public static function instant( $date, $time, $timezone ) {
		$date = trim( (string) $date );
		$time = trim( (string) $time );
		if ( '' === $date ) {
			return null;
		}

		$timezone = self::tz( $timezone );
		$value    = '' === $time ? $date : $date . ' ' . $time;
		$formats  = '' === $time ? array( '!Y-m-d' ) : array( '!Y-m-d h:i A', '!Y-m-d g:i A', '!Y-m-d H:i', '!Y-m-d H:i:s' );

		foreach ( $formats as $format ) {
			$instant = \DateTimeImmutable::createFromFormat( $format, $value, $timezone );
			$errors  = \DateTimeImmutable::getLastErrors();
			if ( $instant && ( false === $errors || ( 0 === $errors['warning_count'] && 0 === $errors['error_count'] ) ) ) {
				return $instant;
			}
		}

		try {
			return new \DateTimeImmutable( $value, $timezone );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Parse an ISO 8601 string ('2026-11-02T18:30:00Z' or with an offset) as UTC.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public static function from_iso( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value || ! preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?(Z|[+-]\d{2}:?\d{2})$/', $value ) ) {
			return null;
		}
		try {
			return ( new \DateTimeImmutable( $value ) )->setTimezone( self::utc() );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	public static function to_tz( \DateTimeInterface $moment, $timezone ) {
		return \DateTimeImmutable::createFromFormat( 'U', (string) $moment->getTimestamp() )->setTimezone( self::tz( $timezone ) );
	}

	public static function format( \DateTimeInterface $moment, $timezone, $format ) {
		return self::to_tz( $moment, $timezone )->format( $format );
	}

	public static function iso_utc( \DateTimeInterface $moment ) {
		return self::format( $moment, 'UTC', 'Y-m-d\TH:i:s\Z' );
	}

	/**
	 * Individual dates of a `meeting_dates` value ('2026-11-03' or '2026-11-03, 2026-11-10').
	 *
	 * @return string[]
	 */
	public static function dates( $meeting_dates ) {
		$dates = array_map( 'trim', explode( ',', (string) $meeting_dates ) );
		return array_values( array_filter( $dates, 'strlen' ) );
	}

	/**
	 * Timezone a stored booking's wall-clock values are in.
	 */
	public static function booking_tz( $booking, ...$fallbacks ) {
		$booking    = (object) $booking;
		$candidates = array_merge(
			array( isset( $booking->availability_time_zone ) ? $booking->availability_time_zone : '' ),
			$fallbacks,
			array( isset( $booking->host_time_zone ) ? $booking->host_time_zone : '' )
		);
		return self::tz( ...$candidates );
	}

	/**
	 * Start/end moments of every occurrence of a stored booking, in date order.
	 *
	 * @return array[] Each item is array( 'start' => DateTimeImmutable, 'end' => DateTimeImmutable ).
	 */
	public static function booking_instants( $booking, ...$fallback_tz ) {
		$booking  = (object) $booking;
		$timezone = self::booking_tz( $booking, ...$fallback_tz );
		$start    = isset( $booking->start_time ) ? $booking->start_time : '';
		$end      = isset( $booking->end_time ) ? $booking->end_time : '';
		$meeting  = isset( $booking->meeting_dates ) ? $booking->meeting_dates : '';

		$instants = array();
		foreach ( self::dates( $meeting ) as $date ) {
			$start_at = self::instant( $date, $start, $timezone );
			$end_at   = self::instant( $date, $end, $timezone );
			if ( ! $start_at ) {
				continue;
			}
			if ( ! $end_at ) {
				$end_at = $start_at;
			}
			// An end at or before the start ends after midnight.
			if ( $end_at < $start_at ) {
				$end_at = $end_at->modify( '+1 day' );
			}
			$instants[] = array(
				'start' => $start_at,
				'end'   => $end_at,
			);
		}

		usort( $instants, array( __CLASS__, 'compare_start' ) );
		return $instants;
	}

	public static function compare_start( $a, $b ) {
		$a = isset( $a['start'] ) ? $a['start']->getTimestamp() : 0;
		$b = isset( $b['start'] ) ? $b['start']->getTimestamp() : 0;
		return $a - $b;
	}

	/**
	 * The occurrence a "time left until the meeting" check should use: the first one
	 * that hasn't started yet, otherwise the last one.
	 *
	 * @return array|null
	 */
	public static function next_occurrence( $booking, ...$fallback_tz ) {
		$instants = self::booking_instants( $booking, ...$fallback_tz );
		if ( empty( $instants ) ) {
			return null;
		}
		$now = self::now();
		foreach ( $instants as $instant ) {
			if ( $instant['start'] > $now ) {
				return $instant;
			}
		}
		return end( $instants );
	}

	/**
	 * Seconds until the next occurrence starts (negative once it has started),
	 * or null when the booking has no parsable date.
	 */
	public static function seconds_until_start( $booking, ...$fallback_tz ) {
		$occurrence = self::next_occurrence( $booking, ...$fallback_tz );
		if ( ! $occurrence ) {
			return null;
		}
		return $occurrence['start']->getTimestamp() - self::now()->getTimestamp();
	}

	/**
	 * Whether the last occurrence ended at least `$after_seconds` ago.
	 */
	public static function is_past( $booking, $after_seconds = 0, ...$fallback_tz ) {
		$instants = self::booking_instants( $booking, ...$fallback_tz );
		if ( empty( $instants ) ) {
			return false;
		}
		$last = end( $instants );
		return $last['end']->getTimestamp() + (int) $after_seconds <= self::now()->getTimestamp();
	}

	/**
	 * A stored booking's dates and times formatted for one timezone (localized through wp_date()).
	 * Times are those of the first occurrence; `dates` has one entry per occurrence.
	 *
	 * @return array array( 'dates' => string[], 'start' => string, 'end' => string, 'timezone' => string )
	 */
	public static function display( $booking, $timezone, $date_format, $time_format, ...$fallback_tz ) {
		$timezone    = self::tz( $timezone );
		$occurrences = self::booking_instants( $booking, ...$fallback_tz );
		$values      = array(
			'dates'    => array(),
			'start'    => '',
			'end'      => '',
			'timezone' => $timezone->getName(),
		);

		foreach ( $occurrences as $occurrence ) {
			$values['dates'][] = self::localized( $date_format, $occurrence['start'], $timezone );
		}
		if ( ! empty( $occurrences ) ) {
			$values['start'] = self::localized( $time_format, $occurrences[0]['start'], $timezone );
			$values['end']   = self::localized( $time_format, $occurrences[0]['end'], $timezone );
		}

		return $values;
	}

	/**
	 * Date/time message tags of a stored booking for chat and SMS notifications.
	 * Tags without a timezone in their name use the recipient's timezone ('host' or 'attendee').
	 *
	 * @return array Tag => value.
	 */
	public static function time_tags( $booking, $recipient = 'host', $date_format = 'Y-m-d', $time_format = 'h:i A' ) {
		$booking            = (object) $booking;
		$host_time_zone     = self::booking_tz( $booking )->getName();
		$attendee_time_zone = self::tz( isset( $booking->attendee_time_zone ) ? $booking->attendee_time_zone : '', $host_time_zone )->getName();

		$recipient_values = self::display( $booking, 'host' === $recipient ? $host_time_zone : $attendee_time_zone, $date_format, $time_format );
		$attendee_values  = self::display( $booking, $attendee_time_zone, $date_format, $time_format );
		$host_values      = self::display( $booking, $host_time_zone, $date_format, $time_format );

		if ( empty( $recipient_values['dates'] ) ) {
			return array();
		}

		$first_host_date = $host_values['dates'][0];

		return array(
			'{{meeting.date}}'                            => implode( ', ', $recipient_values['dates'] ),
			'{{meeting.time}}'                            => $recipient_values['start'] . '-' . $recipient_values['end'],
			'{{booking.full_start_end_attendee_timezone}}' => $attendee_values['start'] . ' - ' . $attendee_values['end'] . ' (' . $attendee_time_zone . ')',
			'{{booking.start_date_time_for_attendee}}'    => $attendee_values['start'] . ' (' . $attendee_time_zone . ')',
			'{{booking.full_start_end_host_timezone}}'    => $first_host_date . ' ' . $host_values['start'] . ' - ' . $host_values['end'] . ' (' . $host_time_zone . ')',
			'{{booking.start_date_time_for_host}}'        => $first_host_date . ' ' . $host_values['start'] . ' (' . $host_time_zone . ')',
		);
	}

	/**
	 * A booking prepared for webhook / automation payloads: adds the field names the
	 * integration settings offer (meeting_date, timezone, attendee_timezone, host_timezone,
	 * host_name) and absolute UTC start/end times. Existing fields are kept as they are.
	 *
	 * @return object
	 */
	public static function payload( $booking ) {
		$payload            = clone (object) $booking;
		$host_time_zone     = self::booking_tz( $payload )->getName();
		$attendee_time_zone = isset( $payload->attendee_time_zone ) ? $payload->attendee_time_zone : '';
		$occurrences        = self::booking_instants( $payload );

		$extra = array(
			'meeting_date'           => isset( $payload->meeting_dates ) ? $payload->meeting_dates : '',
			'timezone'               => $attendee_time_zone,
			'attendee_timezone'      => $attendee_time_zone,
			'host_timezone'          => $host_time_zone,
			'availability_time_zone' => $host_time_zone,
			'host_name'              => trim( ( isset( $payload->host_first_name ) ? $payload->host_first_name : '' ) . ' ' . ( isset( $payload->host_last_name ) ? $payload->host_last_name : '' ) ),
			'start_utc'              => ! empty( $occurrences ) ? self::iso_utc( $occurrences[0]['start'] ) : '',
			'end_utc'                => ! empty( $occurrences ) ? self::iso_utc( $occurrences[0]['end'] ) : '',
		);

		foreach ( $extra as $key => $value ) {
			if ( ! isset( $payload->$key ) || '' === $payload->$key ) {
				$payload->$key = $value;
			}
		}

		return $payload;
	}

	private static function localized( $format, \DateTimeInterface $moment, \DateTimeZone $timezone ) {
		if ( function_exists( 'wp_date' ) ) {
			return wp_date( $format, $moment->getTimestamp(), $timezone );
		}
		return self::format( $moment, $timezone, $format );
	}

	public static function overlaps( \DateTimeInterface $a_start, \DateTimeInterface $a_end, \DateTimeInterface $b_start, \DateTimeInterface $b_end ) {
		return $a_start->getTimestamp() < $b_end->getTimestamp() && $a_end->getTimestamp() > $b_start->getTimestamp();
	}

	/**
	 * Y-m-d dates from `$from` to `$to` (inclusive), each moved by `$pad_days` on both ends.
	 *
	 * @return string[]
	 */
	public static function date_range( $from, $to, $pad_days = 0 ) {
		$utc   = self::utc();
		$start = \DateTimeImmutable::createFromFormat( '!Y-m-d', $from, $utc );
		$end   = \DateTimeImmutable::createFromFormat( '!Y-m-d', $to, $utc );
		if ( ! $start || ! $end ) {
			return array();
		}
		if ( $pad_days ) {
			$start = $start->modify( '-' . (int) $pad_days . ' days' );
			$end   = $end->modify( '+' . (int) $pad_days . ' days' );
		}

		$dates = array();
		for ( $day = $start; $day <= $end; $day = $day->modify( '+1 day' ) ) {
			$dates[] = $day->format( 'Y-m-d' );
		}
		return $dates;
	}

	/**
	 * Current UTC moment as MySQL DATETIME, for `created_at`-style columns.
	 */
	public static function mysql_utc_now() {
		return self::now()->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Seconds the database session clock is ahead of UTC. `created_at` TIMESTAMP columns are
	 * written with CURRENT_TIMESTAMP and read back in this session timezone, which WordPress
	 * does not set, so range filters on them have to be expressed in it.
	 */
	public static function mysql_session_offset() {
		static $offset = null;
		if ( null === $offset ) {
			global $wpdb;
			$offset = 0;
			if ( isset( $wpdb ) && is_object( $wpdb ) && method_exists( $wpdb, 'get_var' ) ) {
				$seconds = $wpdb->get_var( 'SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), NOW())' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$offset  = null === $seconds ? 0 : (int) ( round( (int) $seconds / 60 ) * 60 );
			}
		}
		return $offset;
	}

	/**
	 * A moment as the 'Y-m-d H:i:s' value a TIMESTAMP column holds for it in this database session.
	 */
	public static function to_mysql_session( \DateTimeInterface $moment ) {
		return gmdate( 'Y-m-d H:i:s', $moment->getTimestamp() + self::mysql_session_offset() );
	}

	/**
	 * A 'Y-m-d H:i:s' TIMESTAMP value read in this database session, as an absolute moment.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public static function from_mysql_session( $value ) {
		$moment = \DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', trim( (string) $value ), self::utc() );
		return $moment ? $moment->modify( ( -1 * self::mysql_session_offset() ) . ' seconds' ) : null;
	}

	/**
	 * Start (00:00:00) or end (23:59:59) of a day in the site timezone.
	 * `$day` is a 'Y-m-d' date or a relative day like '-7 days'; empty means today.
	 */
	public static function site_day( $day = '', $end = false ) {
		$site  = self::tz();
		$today = self::to_tz( self::now(), $site );
		if ( '' !== (string) $day && preg_match( '/^\d{4}-\d{2}-\d{2}/', (string) $day ) ) {
			$base = self::instant( substr( (string) $day, 0, 10 ), '', $site );
		} else {
			$base = self::instant( $today->format( 'Y-m-d' ), '', $site );
			$base = '' === (string) $day ? $base : $base->modify( (string) $day );
		}
		if ( ! $base ) {
			$base = self::instant( $today->format( 'Y-m-d' ), '', $site );
		}
		return $end ? $base->modify( '+1 day' )->modify( '-1 second' ) : $base;
	}
}
