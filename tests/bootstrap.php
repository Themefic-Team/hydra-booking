<?php
/**
 * Test bootstrap: minimal WordPress stubs plus in-memory Meeting / Booking repositories,
 * so the date/time logic can run without a WordPress install or database.
 */

// The PHP default timezone is UTC under WordPress; keep the tests honest about that.
date_default_timezone_set( 'UTC' );

define( 'ABSPATH', __DIR__ . '/' );

require_once __DIR__ . '/../vendor/autoload.php';

$GLOBALS['tfhb_test_options']   = array();
$GLOBALS['tfhb_test_post_meta'] = array();

function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['tfhb_test_options'] ) ? $GLOBALS['tfhb_test_options'][ $name ] : $default;
}
function get_post_meta( $post_id, $key = '', $single = false ) {
	return isset( $GLOBALS['tfhb_test_post_meta'][ $post_id ][ $key ] ) ? $GLOBALS['tfhb_test_post_meta'][ $post_id ][ $key ] : '';
}
function get_user_meta( $user_id, $key = '', $single = false ) {
	return '';
}
function apply_filters( $hook, $value ) {
	return $value;
}
function do_action( $hook ) {
}
function __( $text, $domain = 'default' ) {
	return $text;
}
function esc_html__( $text, $domain = 'default' ) {
	return $text;
}
function esc_html( $text ) {
	return $text;
}
function sanitize_text_field( $value ) {
	return is_string( $value ) ? trim( $value ) : $value;
}
function wp_unslash( $value ) {
	return $value;
}
function absint( $value ) {
	return abs( (int) $value );
}
function wp_timezone_string() {
	return isset( $GLOBALS['tfhb_test_site_tz'] ) ? $GLOBALS['tfhb_test_site_tz'] : 'UTC';
}
function wp_timezone() {
	return new DateTimeZone( wp_timezone_string() );
}
function wp_date( $format, $timestamp = null, $timezone = null ) {
	$date = new DateTime( '@' . ( null === $timestamp ? time() : $timestamp ) );
	$date->setTimezone( $timezone ? $timezone : wp_timezone() );
	return $date->format( $format );
}

/**
 * In-memory replacements for the database classes used by the slot engine.
 */
class Tfhb_Test_Store {
	public static $meetings = array();
	public static $bookings = array();

	public static function reset() {
		self::$meetings                 = array();
		self::$bookings                 = array();
		$GLOBALS['tfhb_test_options']   = array();
		$GLOBALS['tfhb_test_post_meta'] = array();
		$GLOBALS['tfhb_test_site_tz']   = 'UTC';
	}

	/**
	 * Same where-clause shapes as Booking::getBookingWithAttendees().
	 */
	public static function matches( $booking, $where ) {
		foreach ( (array) $where as $key => $condition ) {
			if ( 'OR' === $key ) {
				$any = false;
				foreach ( $condition as $or ) {
					$any = $any || self::compare( $booking, $or );
				}
				if ( ! $any ) {
					return false;
				}
				continue;
			}
			if ( ! self::compare( $booking, $condition ) ) {
				return false;
			}
		}
		return true;
	}

	private static function compare( $booking, $condition ) {
		list( $field, $operator, $value ) = $condition;
		$actual = isset( $booking->$field ) ? $booking->$field : null;
		switch ( $operator ) {
			case '=':
				return $actual == $value;
			case '!=':
				return $actual != $value;
			case 'IN':
				return in_array( $actual, (array) $value );
			case 'IN_DATES':
				$dates = array_map( 'trim', explode( ',', (string) $actual ) );
				return count( array_intersect( $dates, (array) $value ) ) > 0;
		}
		throw new InvalidArgumentException( 'Unsupported operator ' . $operator );
	}
}

require_once __DIR__ . "/fakes.php";
