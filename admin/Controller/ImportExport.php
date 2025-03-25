<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Use DB 
use HydraBooking\Admin\Controller\RouteController;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Meeting;

 
class ImportExport {


	// constaract
	public function __construct() {
		
 
	}

	public function create_endpoint() {
		// tfhb_print_r('hello world');
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetImportExportData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/export-meetings',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ExportMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/import-export/import-meeting',
		// 	array(
		// 		'methods'  => 'POST',
		// 		'callback' => array( $this, 'ImportMeeting' ),
		// 		'permission_callback' =>  array(new RouteController() , 'permission_callback'),
		// 	)
		// );
		// // Import Booking
		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export/import-booking',
		// 	array(
		// 		'methods'  => 'POST',
		// 		'callback' => array( $this, 'ImportBooking' ),
		// 		'permission_callback' =>  array(new RouteController() , 'permission_callback'),
		// 	)
		// );
	}

	public function GetImportExportData() {
		// Get booking Data
		$booking  = new Booking();
		$bookings = $booking->getColumns();

		$data = array(
			'status'         => true,
			'booking_column' => $bookings,
		);
		return rest_ensure_response( $data );
	}

	// Export booking Data
	public function ImportBooking() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['columns'] ) ? $request['columns'] : array();

		// rearrange data first array value based on columns
		$firstData = $data[0];
		$newData   = array();
		foreach ( $columns as $key => $column ) {
			// if column name is match with first data value update that frist data value form column value
			// get the first data key
			$firstDataKey = array_search( $column, $firstData );
			if ( $firstDataKey !== false ) {
				$firstData[ $firstDataKey ] = $data[0][ $key ];
			}
		}
		$data[0] = $newData;
		 

		$booking = new Booking();
		$booking->importBooking( $data );
		 
		$data = array(
			'status'  => true,
			'data'    => true,
			'message' =>  __( 'Booking Data Imported Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	/**
	 * Export Meetings Data
	 * @since 1.1.0
	 * @author Sydur Rahman 
	 */
	public function ExportMeeting(){
		$request = json_decode( file_get_contents( 'php://input' ), true );
		// tfhb_print_r( $request );
		// 2024-07-03 23:48:25
		$time         = '00:00:00';
		$current_time = '23:59:59';
		// Get Current Date baded on time
		$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
		$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( $current_date ) ) );

		$Meeting = new Meeting();
		if ( ! empty( $request['date_range'] == 'custom' ) ) {
			// in this format 2024-07-03 23:48:25 form 2024-07-03 request['start_date'] variable
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $request['end_date'] ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( $request['start_date'] ) );

		} elseif ( $request['date_range'] == 'today' ) {
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( $current_date ) ) );
		} elseif ( $request['date_range'] == 'weeks' ) {
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-7 day', strtotime( $current_date ) ) );
		} elseif ( $request['date_range'] == 'months' ) {  // current month
			// This month end date
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( 'last day of this month', strtotime( $current_time ) ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( 'first day of last month', strtotime( $current_time ) ) );
		} elseif ( $request['date_range'] == 'years' ) {  // current year
			// This year end date
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( 'last day of this year', strtotime( $current_time ) ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( 'first day of last year', strtotime( $current_time ) ) );
		}
		if ( $request['date_range'] == 'all' ) {
			$file_name = 'meeting-data';

		} else {
			$file_name = 'meeting-data-' . gmdate( 'Y-m-d', strtotime( $previous_date ) ) . '-' . gmdate( 'Y-m-d', strtotime( $current_date ) ) . '';

		}

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID; 
			$host     = new Host();
			$HostData = $host->getHostByUserId( $current_user_id ); 
	 
		$where = array();
		if($current_user_role != 'administrator'){
			$where[] = array('host_id', '=', $HostData->id);
		} 
		if ( $request['date_range'] == 'all' ) {
			
			$bookingsList = $Meeting->getMeetings($where);
		} else { 
			$where[] = array('created_at', 'BETWEEN', [$previous_date, $current_date]);
			 $bookingsList = $Meeting->getMeetings(  
				$where,
				NULL,
				'DESC',
			);  
		}
		if('CSV' == $request['type']){
			$booking_array  = array();
			$booking_column = array();
			foreach ( $bookingsList as $key => $book ) {
				
				if ( $key == 0 ) {
					foreach ( $book as $c_key => $c_value ) {
						$booking_column[] = $c_key;
					}
				}
				$book->attendees = json_encode($book->attendees); 
				$booking_array[] = (array) $book;
			} 

			ob_start();
			$file = fopen( 'php://output', 'w' );
			fputcsv( $file, $booking_column );

			foreach ( $booking_array as $booking ) {
				fputcsv( $file, $booking );
			}

			fclose( $file );
			$data = ob_get_clean();
			// Return response
			$data = array(
				'status'    => true,
				'data'      => $data,
				'file_name' => $file_name.'.csv',
				'message'   =>  __('Booking Data Exported Successfully!', 'hydra-booking'),
			);
			return rest_ensure_response( $data );
		}
	}
}
