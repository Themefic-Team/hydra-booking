<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;
// Use DB
use HydraBooking\DB\Host;
use HydraBooking\DB\Availability;
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Transactions;
use HydraBooking\Admin\Controller\DateTimeController;

// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class DashboardController {

	public function __construct() { 
		
	}
	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/dashboard',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getDashboardsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/dashboard/statistics',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getDashboardsStatisticsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
 
	}


	// get dashboard data
	public function getDashboardsData() {

		$request   = json_decode( file_get_contents( 'php://input' ), true );
		$days      = isset($request['days']) ? $request['days'] : 30; // exp 1
		$from_date = isset($request['from_date']) ? $request['from_date'] : null; // exp 2021-09-01
		$to_date   = isset($request['to_date']) ? $request['to_date'] : null; // exp 2021-09-01

		// calculate Days based on form day and to day
		$days = $from_date != null && $to_date != null ? ( strtotime( $to_date ) - strtotime( $from_date ) ) / ( 60 * 60 * 24 ) : $days; // exp 1

		// Day boundaries are site-timezone days, written as the values created_at holds in this
		// database session (it is a TIMESTAMP filled by MySQL's CURRENT_TIMESTAMP).
		$current_date = BookingTime::to_mysql_session( BookingTime::site_day( $to_date != null ? $to_date : '', true ) ); // exp 2021-09-01 23:59:59

		$previous_date        = BookingTime::to_mysql_session( BookingTime::site_day( $days != 1 ? '-' . (int) $days . ' days' : '' ) ); // exp 2021-09-01 00:00:00
		$previous_date        = $from_date != null ? BookingTime::to_mysql_session( BookingTime::site_day( $from_date ) ) : $previous_date;
		$previous_date_before = BookingTime::to_mysql_session( BookingTime::site_day( $days != 1 ? '-' . (int) ( $days * 2 ) . ' days' : '-1 days' ) );

		// Get booking
		$booking = new Booking();

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;
		$host              = new Host();
		$HostData          = $host->getHostByUserId( $current_user_id );

		$bookings_where = array(
			array('created_at', 'BETWEEN',  array($previous_date, $current_date)), 
		);
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$bookings_where[] = array('host_id', '=', $HostData->id);
		}
		$bookings = $booking->getBookingWithAttendees( 
			$bookings_where,
			null,
			'DESC' 
		); 

		 

		// Previous Date Booking
		$previous_date_bookings_where = array(
			array('created_at', 'BETWEEN',  array($previous_date_before, $previous_date)), 
		);
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$previous_date_bookings_where[] = array('host_id', '=', $HostData->id);
		}
		$previous_date_bookings = $booking->getBookingWithAttendees( 
			$previous_date_bookings_where,
			null,
			'DESC' 
		); 
 

		// Upcoming Booking: the next five meetings from now, whatever date range is selected.
		// meeting_dates are host-local, so the SQL keeps yesterday (UTC) and recurring series,
		// and the exact check uses each occurrence's absolute end.
		$upcoming_booking_where = array(
			'OR' => array(
				array('meeting_dates', '>=', BookingTime::now()->modify('-1 day')->format('Y-m-d')),
				array('meeting_dates', 'LIKE', '%,%'),
			),
			array('status', '!=', 'canceled'),
		);
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$upcoming_booking_where[] = array('host_id', '=', $HostData->id);
		}
		$upcoming_candidates = $booking->getBookingWithAttendees(
			$upcoming_booking_where,
			null,
			'DESC'
		);

		$now               = BookingTime::now();
		$upcoming_by_start = array();
		foreach ( is_array( $upcoming_candidates ) ? $upcoming_candidates : array() as $candidate ) {
			foreach ( BookingTime::booking_instants( $candidate ) as $occurrence ) {
				if ( $occurrence['end'] > $now ) {
					$upcoming_by_start[] = array( 'start' => $occurrence['start']->getTimestamp(), 'booking' => $candidate );
					break;
				}
			}
		}
		usort(
			$upcoming_by_start,
			function ( $a, $b ) {
				return $a['start'] - $b['start'];
			}
		);
		$upcoming_booking = array_map(
			function ( $item ) {
				return $item['booking'];
			},
			array_slice( $upcoming_by_start, 0, 5 )
		);


		 
		// Recent Booking
		$recent_booking_where = array();
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$recent_booking_where[] = array('host_id', '=', $HostData->id);
		}
		$recent_booking = $booking->getBookingWithAttendees( 
			$recent_booking_where,
			5,
			'DESC'
		);

		// Currency always comes from the global setting, not the meeting.
		$_tfhb_general_settings = get_option( '_tfhb_general_settings', array() );
		$global_currency        = ! empty( $_tfhb_general_settings['currency'] ) ? $_tfhb_general_settings['currency'] : 'USD';
		foreach ( array_merge( (array) $upcoming_booking, (array) $recent_booking ) as $booking_item ) {
			if ( is_object( $booking_item ) ) {
				$booking_item->payment_currency = $global_currency;
			}
		}
		// count total Booking and collect percentage
		$total_bookings['total']      = count( $bookings );
		$total_bookings_previous      = count( $previous_date_bookings );
		$total_bookings['percentage'] = $total_bookings_previous != 0 ? 100 * ( $total_bookings['total'] - $total_bookings_previous ) / $total_bookings_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$total_bookings['percentage'] = number_format( $total_bookings['percentage'], 0 );
		$total_bookings['percentage'] = $total_bookings['percentage'] == 100 ? 0 : $total_bookings['percentage'];
		$total_bookings['growth']     = $total_bookings['percentage'] < 0 ? 'decrease' : 'increase';

		// total cancelled Booking and collect percentage
		
		$cancelled['total']      = count(
			array_filter(
				$bookings,
				function ( $booking ) {
					return $booking->status == 'canceled';
				}
			)
		);
		$cancelled_previous      = count(
			array_filter(
				$previous_date_bookings,
				function ( $booking ) {
					return $booking->status == 'canceled';
				}
			)
		);
		$cancelled['percentage'] = $cancelled_previous != 0 ? 100 * ( $cancelled['total'] - $cancelled_previous ) / $cancelled_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$cancelled['percentage'] = number_format( $cancelled['percentage'], 0 );
		$cancelled['growth']     = $cancelled['percentage'] < 0 ? 'decrease' : 'increase';

		// count wich status is completed for Bookings array
		$completed['total']      = count(
			array_filter(
				$bookings,
				function ( $booking ) {
					return $booking->status == 'completed';
				}
			)
		);
		$completed_previous      = count(
			array_filter(
				$previous_date_bookings,
				function ( $booking ) {
					return $booking->status == 'completed';
				}
			)
		);
		$completed['percentage'] = $completed_previous != 0 ? 100 * ( $completed['total'] - $completed_previous ) / $completed_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$completed['percentage'] = number_format( $completed['percentage'], 0 );
		$completed['growth']     = $completed['percentage'] < 0 ? 'decrease' : 'increase';

		// Total Earning	
		$transactions = new Transactions();
		$earning      = $transactions->totalEarning($previous_date,  $current_date, ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ? $HostData->id : false); 

		$previous_earning = $transactions->totalEarning($previous_date_before,  $previous_date, ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ? $HostData->id : false);
		// tfhb_print_r($earning);
		$total_earning['total']      = !empty($earning) ? $earning : 0;
		// make only 2 decimal after dots exp 10.00
		$total_earning['total'] = number_format( $total_earning['total'], 2 );
		$total_earning_previous      = $previous_earning;
		$total_earning['percentage'] = $total_earning_previous != 0 ? 100 * ( $total_earning['total'] - $total_earning_previous ) / $total_earning_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$total_earning['percentage'] = number_format( $total_earning['percentage'], 0 );
		$total_earning['growth']     = $total_earning['percentage'] < 0 ? 'decrease' : 'increase';

		$data = array(
			'status'                   => true,
			'total_bookings'           => $total_bookings,
			'total_cancelled_bookings' => $cancelled,
			'total_completed_bookings' => $completed,
			'upcoming_booking'         => $upcoming_booking,
			'recent_booking'           => $recent_booking,
			'total_earning'            => $total_earning,
			'days'                     => $days,
		);

		return rest_ensure_response( $data );
	}
	// get dashboard data
	public function getDashboardsStatisticsData() {

		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$days          = $request['statistics_days']; // exp 2021-09-01
		$helper        = new Helper();
		$label_format  = $helper->get_date_format_from_settings( 'd M, y' );

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;
		$host              = new Host();
		$HostData          = $host->getHostByUserId( $current_user_id );

		$booking                          = new Booking();
		$statistics['total_bookings']     = array();
		$statistics['cancelled_bookings'] = array();
		$statistics['completed_bookings'] = array();

		// Each bucket is a range of site-timezone days: array( label, first day, last day )
		$site_today  = BookingTime::site_day();
		$month_start = $site_today->modify( 'first day of this month' );
		$buckets     = array();
		if ( $days == 7 ) {
			for ( $i = 6; $i >= 0; $i-- ) {
				$day       = $site_today->modify( '-' . $i . ' days' );
				$buckets[] = array( $day->format( $label_format ), $day, $day );
			}
		}
		if ( $days == 30 ) { // This Month every Days
			$days_in_month = (int) $month_start->format( 't' );
			for ( $day = 0; $day < $days_in_month; $day++ ) {
				$date      = $month_start->modify( '+' . $day . ' days' );
				$buckets[] = array( $date->format( $label_format ), $date, $date );
			}
		}
		if ( $days == 3 ) {  // last 3 Months
			for ( $i = 2; $i >= 0; $i-- ) {
				$first     = $month_start->modify( '-' . $i . ' months' );
				$buckets[] = array( $first->format( 'F' ), $first, $first->modify( 'last day of this month' ) );
			}
		}
		if ( $days == 12 ) {  // This year as month
			$january = $month_start->setDate( (int) $month_start->format( 'Y' ), 1, 1 );
			for ( $i = 0; $i < 12; $i++ ) {
				$first     = $january->modify( '+' . $i . ' months' );
				$buckets[] = array( $first->format( 'F' ), $first, $first->modify( 'last day of this month' ) );
			}
		}

		$statistics['label'] = array();
		foreach ( $buckets as $bucket ) {
			$statistics['label'][] = $bucket[0];
		}

		foreach ( $buckets as $bucket ) {
			// Get booking, created_at is compared in this database session's clock
			$bookings_where = array(
				array('created_at', 'BETWEEN',  array( BookingTime::to_mysql_session( BookingTime::site_day( $bucket[1]->format( 'Y-m-d' ) ) ), BookingTime::to_mysql_session( BookingTime::site_day( $bucket[2]->format( 'Y-m-d' ), true ) ) )),
			);
			if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
				$bookings_where[] = array('host_id', '=', $HostData->id);
			}
			$bookings = $booking->getBookingWithAttendees( 
				$bookings_where,
				null,
				'DESC' 
			); 
	
			$statistics['total_bookings'][]     = count( $bookings );
			$statistics['cancelled_bookings'][] = count(
				array_filter(
					$bookings,
					function ( $booking ) {
						return $booking->status == 'canceled';
					}
				)
			);
			$statistics['completed_bookings'][] = count(
				array_filter(
					$bookings,
					function ( $booking ) {
						return $booking->status == 'completed';
					}
				)
			);
		}

		$data = array(
			'status'     => true,
			'statistics' => $statistics, 
		);

		return rest_ensure_response( $data );
	}

 
}
