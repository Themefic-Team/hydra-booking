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
		// Import export Meeting
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/export-meetings',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ExportMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/import-meeting',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ImportMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		// import export host
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/export-hosts',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'ExportHosts' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/import-host',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ImportHost' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
		// Import Booking
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/import-booking',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ImportBooking' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
	}

	public function GetImportExportData() {
		// Get booking Data
		$booking  = new Booking();
		$bookings = $booking->getColumns(); 
		// Meeting 
		$meeting = new Meeting();
		$meetings = $meeting->getColumns();
		// Host
		$host = new Host();
		$hosts = $host->getColumns();

		$data = array(
			'status'         => true,
			'booking_column' => $bookings,
			'meeting_column' => $meetings,
			'host_column'    => $hosts,
		);
		return rest_ensure_response( $data );
	}

	// Export booking Data
	public function ImportMeeting() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['column'] ) ? $request['column'] : array();
		$is_overwrite = isset( $request['is_overwrite'] ) ? $request['is_overwrite'] : false;
		 // current user host id 
		$host = new Host();
		$host_id = $host->getHostByUserId( get_current_user_id() );
	
		if ( empty( $data ) || empty( $columns ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __( 'Invalid data provided.', 'hydra-booking' ),
			) );
		} 
		$header = $data[0];
		$data_rows = array_slice($data, 1);
		
		// Map header to index
		$header_map = array_flip($header);
	 
		$meeting = new Meeting();
		foreach ($data_rows as $row) {
			$new_row = []; 
			$data = []; 
			if (empty(array_filter($row))) {
				continue;
			}
			// tfhb_print_r($row);
			foreach ($columns as $key => $column) {
				if($column == '' || ( $key == 'id' && $is_overwrite == false)){
					continue; // skip empty column
				}
				if (isset($header_map[$column])) {
					$data[] = $row[$header_map[$column]];
					$new_row[$key] = $row[$header_map[$column]];
				} else {
					$data[] = null; // or empty string
					$new_row[$key] = null; // or empty string
				}
 
			}
			// Check if host is not available in the row
			 if($new_row['host_id'] !='' ){
				$check_host = $host->getHostById($new_row['host_id']);
				if(empty($check_host)){
					$new_row['host_id'] = $host_id;
					$new_row['user_id'] = get_current_user_id();
				}else{
					$new_row['user_id'] = $check_host->user_id;
				}
			 }else{
				$new_row['host_id'] = $host_id;
				$new_row['user_id'] = get_current_user_id();
			 } 
			 
			if($is_overwrite == true){
				$meeting->update($new_row);
			}else{
				// unset id 
				unset($new_row['id']);
				$meeting->add($new_row);
			}   
		}


		$data = array(
			'status'  => true,
			'data'    => true,
			'message' =>  __( 'Meetings Imported Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
 
	}
	// Export booking Data
	public function ImportHost() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['column'] ) ? $request['column'] : array();
		$is_overwrite = isset( $request['is_overwrite'] ) ? $request['is_overwrite'] : false;
		$is_create_new_user = isset( $request['is_create_new_user'] ) ? $request['is_create_new_user'] : false;
		 // current user host id 
		$host = new Host();
		$host_id = $host->getHostByUserId( get_current_user_id() );
	
		if ( empty( $data ) || empty( $columns ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __( 'Invalid data provided.', 'hydra-booking' ),
			) );
		} 
		$header = $data[0];
		$data_rows = array_slice($data, 1);
		
		// Map header to index
		$header_map = array_flip($header);
	 
		$host = new Host();
		foreach ($data_rows as $row) {
			$new_row = []; 
			$data = []; 
			if (empty(array_filter($row))) {
				continue;
			}
			// tfhb_print_r($row);
			foreach ($columns as $key => $column) {
				if($column == '' || ( $key == 'id' && $is_overwrite == false)){
					continue; // skip empty column
				}
				if (isset($header_map[$column])) {
					$data[] = $row[$header_map[$column]];
					$new_row[$key] = $row[$header_map[$column]];
				} else {
					$data[] = null; // or empty string
					$new_row[$key] = null; // or empty string
				}
 
			}  
			$user_id = $new_row['user_id'];
			// Check if user is not available in the row using id 
			if($user_id !='' ){ 
				$check_user = get_user_by( 'id', $user_id ); 
				
					if(empty($check_user)){
						// create new user
						// check user by email
						$check_user = get_user_by( 'email', $new_row['email'] );
						if(empty($check_user)){
							if($is_create_new_user == true){
							// create new user
								$user_id = wp_create_user( $new_row['email'], 'password', $new_row['email'] );
								$new_row['user_id'] = $user_id;

							}else{
								continue;
							}
						}else{
							$new_row['user_id'] = $check_user->ID;
						} 
					}else{
						$new_row['user_id'] = $check_user->ID;
					}
				
			 }else{
				$user_id = wp_create_user( $new_row['email'], 'password', $new_row['email'] );
				$new_row['user_id'] = $user_id;
			 }

			if($is_overwrite == true){
				$host->update($new_row);
			}else{
				unset($new_row['id']);
				$host->add($new_row);
			}   
		}


		$data = array(
			'status'  => true,
			'data'    => true,
			'message' =>  __( 'Host Imported Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
 
	}
	
	// Export booking Data
	public function ImportBooking() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['columns'] ) ? $request['columns'] : array();

		// current user host id 
		$host = new Host();
		$host_id = $host->getHostByUserId( get_current_user_id() );
	 
		// tfhb_print_r($columns);
		// tfhb_print_r($data);
		if ( empty( $data ) || empty( $columns ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __( 'Invalid data provided.', 'hydra-booking' ),
			) );
		}

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

	/**
	 * Export Hosts Data 
	 * @since 1.1.0
	 * @author Sydur Rahman
	 * */
	public function ExportHosts(){
		$host = new Host();
		$hostsLists = $host->get(); 
		$file_name = 'hydra-hosts-data';
		// check if current has manage option caps
		if ( ! current_user_can( 'tfhb_manage_options' ) ) {
			$data = array(
				'status'  => false,
				'message' =>  __('You do not have permission to export hosts data!', 'hydra-booking' ),
			);
			return rest_ensure_response( $data );
		} 
		$data_array  = array();
		$data_column = array();
		foreach ( $hostsLists as $key => $book ) {
			
			if ( $key == 0 ) {
				foreach ( $book as $c_key => $c_value ) {
					$data_column[] = $c_key;
				}
			}
			$book->attendees = json_encode($book->attendees); 
			$data_array[] = (array) $book;
		} 

		ob_start();
		$file = fopen( 'php://output', 'w' );
		fputcsv( $file, $data_column );

		foreach ( $data_array as $booking ) {
			fputcsv( $file, $booking );
		}

		fclose( $file );
		$data = ob_get_clean();
		// Return response
		$data = array(
			'status'    => true,
			'data'      => $data,
			'file_name' => $file_name.'.csv',
			'message'   =>  __('Hosts Data Exported Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}
}
