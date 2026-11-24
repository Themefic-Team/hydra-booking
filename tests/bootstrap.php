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
$GLOBALS['tfhb_test_actions']   = array();

function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['tfhb_test_options'] ) ? $GLOBALS['tfhb_test_options'][ $name ] : $default;
}
function get_post_meta( $post_id, $key = '', $single = false ) {
	return isset( $GLOBALS['tfhb_test_post_meta'][ $post_id ][ $key ] ) ? $GLOBALS['tfhb_test_post_meta'][ $post_id ][ $key ] : '';
}
function get_user_meta( $user_id, $key = '', $single = false ) {
	return '';
}
function update_user_meta( $user_id, $key, $value ) {
	return true;
}
function apply_filters( $hook, $value ) {
	return $value;
}
function do_action( $hook, ...$args ) {
	$GLOBALS['tfhb_test_actions'][] = array( 'hook' => $hook, 'args' => $args );
}
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
}
function add_shortcode( $tag, $callback ) {
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
function wp_rand( $min = 0, $max = null ) {
	return null === $max ? mt_rand() : mt_rand( $min, $max );
}
function wp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
	return substr( bin2hex( random_bytes( (int) ceil( $length / 2 ) ) ), 0, $length );
}
function wp_json_encode( $data, $options = 0, $depth = 512 ) {
	return json_encode( $data, $options, $depth );
}
/**
 * HTTP fakes. A test can set $GLOBALS['tfhb_test_http_handler'] = function ( $method, $url, $args ) { ... }
 * returning a response array (or null for the default). Every call is logged in
 * $GLOBALS['tfhb_test_http_log'] as array( method, url, decoded JSON body ).
 */
function tfhb_test_http( $method, $url, $args, $default ) {
	$body = isset( $args['body'] ) && is_string( $args['body'] ) ? json_decode( $args['body'], true ) : ( isset( $args['body'] ) ? $args['body'] : null );
	$GLOBALS['tfhb_test_http_log'][] = array( 'method' => $method, 'url' => $url, 'body' => $body );
	if ( isset( $GLOBALS['tfhb_test_http_handler'] ) && is_callable( $GLOBALS['tfhb_test_http_handler'] ) ) {
		$response = call_user_func( $GLOBALS['tfhb_test_http_handler'], $method, $url, $args );
		if ( null !== $response ) {
			return $response;
		}
	}
	return $default;
}
function wp_remote_request( $url, $args = array() ) {
	$method = isset( $args['method'] ) ? $args['method'] : 'GET';
	return tfhb_test_http( $method, $url, $args, array( 'response' => array( 'code' => 200 ), 'body' => '{}' ) );
}
function wp_remote_post( $url, $args = array() ) {
	return tfhb_test_http( 'POST', $url, $args, array( 'response' => array( 'code' => 200 ), 'body' => '{"access_token":"mock_access_token_123"}' ) );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
function get_transient( $key ) {
	return isset( $GLOBALS['tfhb_test_transients'][ $key ] ) ? $GLOBALS['tfhb_test_transients'][ $key ] : false;
}
function set_transient( $key, $value, $expiration = 0 ) {
	$GLOBALS['tfhb_test_transients'][ $key ] = $value;
	return true;
}
function wp_remote_retrieve_response_code( $response ) {
	return is_array( $response ) && isset( $response['response']['code'] ) ? $response['response']['code'] : 200;
}
function wp_remote_retrieve_body( $response ) {
	return is_array( $response ) && isset( $response['body'] ) ? $response['body'] : '';
}
function is_wp_error( $thing ) {
	return is_object( $thing ) && is_a( $thing, 'WP_Error' );
}
if ( ! class_exists( 'WC_Order' ) ) {
	class WC_Order {}
}
function wc_get_order( $id ) {
	return isset( \Tfhb_Test_Store::$orders[ $id ] ) ? \Tfhb_Test_Store::$orders[ $id ] : null;
}

class Tfhb_Stub_Wpdb {
	public $prefix = 'wp_';
	public $insert_id = 1;
	public function insert( $table, $data, $format = null ) { $this->insert_id++; return 1; }
	public function update( $table, $data, $where, $format = null, $where_format = null ) { return 1; }
	public function delete( $table, $where, $where_format = null ) { return 1; }
	public function prepare( $query, ...$args ) { return vsprintf( str_replace( '%s', "'%s'", $query ), $args ); }
	public function esc_like( $text ) { return addcslashes( $text, '_%\\' ); }
	public function get_var( $query ) { return null; }
	public function get_results( $query ) { return array(); }
}
global $wpdb;
$wpdb = new Tfhb_Stub_Wpdb();
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
	public static $attendees = array();
	public static $booking_meta = array();
	public static $orders = array();

	public static function reset() {
		self::$meetings                 = array();
		self::$bookings                 = array();
		self::$attendees                = array();
		self::$booking_meta             = array();
		self::$orders                   = array();
		$GLOBALS['tfhb_test_options']   = array();
		$GLOBALS['tfhb_test_post_meta'] = array();
		$GLOBALS['tfhb_test_actions']   = array();
		$GLOBALS['tfhb_test_site_tz']   = 'UTC';
		$GLOBALS['tfhb_test_http_log']     = array();
		$GLOBALS['tfhb_test_http_handler'] = null;
		$GLOBALS['tfhb_test_transients']   = array();
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
require_once __DIR__ . '/../includes/helper/helper-functions.php';
