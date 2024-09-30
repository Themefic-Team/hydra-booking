<?php
namespace HydraBooking\DB;

class Meeting {

	public $table = 'tfhb_meetings';
	public function __construct() {
	}

	/**
	 * Run the database migration.
	 */
	public function migrate() {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "CREATE TABLE $table_name (
                id INT(11) NOT NULL AUTO_INCREMENT, 
                slug VARCHAR(255) NULL,
                host_id INT(11) NULL,
                user_id INT(11) NOT NULL,
                post_id INT(11) NOT NULL,
                title VARCHAR(255) NULL,
                description LONGTEXT NULL,
                meeting_type VARCHAR(255) NOT NULL,
                duration VARCHAR(20) NULL,
                custom_duration VARCHAR(20) NULL,
                meeting_locations LONGTEXT NULL,
                meeting_category VARCHAR(20) NULL,
                availability_range_type VARCHAR(20) NULL,
                availability_range LONGTEXT NULL, 
                availability_type VARCHAR(20) NULL,
                availability_id VARCHAR(11) NULL,
                availability_custom LONGTEXT NULL, 
                buffer_time_before VARCHAR(20) NULL,
                buffer_time_after VARCHAR(20) NULL,
                booking_frequency LONGTEXT NULL,
                meeting_interval VARCHAR(20) NULL,
                recurring_status VARCHAR(20) NULL,
                recurring_repeat LONGTEXT NULL,
                recurring_maximum VARCHAR(20) NULL,
                attendee_can_cancel VARCHAR(20) NULL,
                attendee_can_reschedule VARCHAR(20) NULL,
                questions_type VARCHAR(20) NULL,
                questions_form_type LONGTEXT NULL,
                questions_form LONGTEXT NULL,
                questions LONGTEXT NULL, 
                notification LONGTEXT NULL, 
                payment_status VARCHAR(20) NULL, 
                payment_method VARCHAR(20) NULL, 
                payment_currency VARCHAR(20) NULL, 
                meeting_price VARCHAR(20) NULL, 
                payment_meta LONGTEXT NULL, 
                webhook LONGTEXT NULL, 
                integrations LONGTEXT NULL, 
                max_book_per_slot VARCHAR(20) NULL, 
                is_display_max_book_slot VARCHAR(20) NULL, 
                status VARCHAR(20) NULL, 
                created_by VARCHAR(20) NOT NULL, 
                updated_by VARCHAR(20) NOT NULL, 
                created_at DATE NOT NULL, 
                updated_at DATE NOT NULL, 
                PRIMARY KEY (id)
            ) $charset_collate";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Rollback the database migration.
	 */
	public function rollback() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_meetings" );
	}

	/**
	 * Create the database meeting.
	 */
	public function add( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		// insert meeting
		$result = $wpdb->insert(
			$table_name,
			$request
		);

		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status'    => true,
				'insert_id' => $wpdb->insert_id,
			);
		}
	}
	/**
	 * Update the database meeting.
	 */
	public function update( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$id = $request['id'];
		unset( $request['id'] );

		// encode json in array data
		if ( $request['meeting_locations'] ) {
			$request['meeting_locations'] = wp_json_encode( $request['meeting_locations'] );
		}
		if ( $request['availability_range'] ) {
			$request['availability_range'] = wp_json_encode( $request['availability_range'] );
		}
		if ( $request['availability_custom'] ) {
			$request['availability_custom'] = wp_json_encode( $request['availability_custom'] );
		}
		if ( $request['booking_frequency'] ) {
			$request['booking_frequency'] = wp_json_encode( $request['booking_frequency'] );
		}
		if ( $request['recurring_repeat'] ) {
			$request['recurring_repeat'] = wp_json_encode( $request['recurring_repeat'] );
		}
		if ( $request['questions'] ) {
			$request['questions'] = wp_json_encode( $request['questions'] );
		}
		if ( $request['notification'] ) {
			$request['notification'] = wp_json_encode( $request['notification'] );
		}
		if ( $request['payment_meta'] ) {
			$request['payment_meta'] = wp_json_encode( $request['payment_meta'] );
		}

		// Update meeting
		$result = $wpdb->update(
			$table_name,
			$request,
			array( 'id' => $id )
		);

		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status'    => true,
				'update_id' => $wpdb->insert_id,
			);
		}
	}
	/**
	 * Get all  meeting Data.
	 */
	public function get( $id = null, $filterData = null, $user_id = null ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$host_table = $wpdb->prefix . 'tfhb_hosts';
		$booking_table = $wpdb->prefix . 'tfhb_bookings';
		if ( $id ) {
			$data = $wpdb->get_row(
				$wpdb->prepare( "SELECT $table_name.*, COUNT($booking_table.id) as total_booking FROM $table_name
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id
				WHERE $table_name.id = %s GROUP BY $table_name.id", $id )
			);
		} elseif ( ! empty( $filterData['title'] ) || ! empty( $filterData['fhosts'] ) || ! empty( $filterData['fcategory'] ) || ( ! empty( $filterData['startDate'] ) && ! empty( $filterData['endDate'] ) ) ) {
			$sql = "SELECT $table_name.*, COUNT($booking_table.id) as total_booking FROM $table_name LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id WHERE";

			if ( ! empty( $filterData['title'] ) ) {
				$title = '%' . $filterData['title'] . '%'; // Wrap title with % for LIKE comparison
				// $sql  .= $wpdb->prepare( $table_name.'.title LIKE %s', $title );
				$sql  .= " $table_name.title LIKE '$title'";
			}

			if ( isset( $filterData['fhosts'] ) ) {
				$host_ids = implode( ',', array_map( 'intval', $filterData['fhosts'] ) );
				$sql     .= ! empty( $filterData['title'] ) ? ' AND' : '';
				$sql     .= " $table_name.host_id IN ($host_ids)";
			}

			if ( isset( $filterData['fcategory'] ) ) {
				$category_ids = implode( ',', array_map( 'intval', $filterData['fcategory'] ) );
				$sql         .= ( ! empty( $filterData['title'] ) || isset( $filterData['fhosts'] ) ) ? ' AND' : '';
				$sql         .= " '$table_name.meeting_category IN ($category_ids)";
			}

			$sql .= " GROUP BY $table_name.id" ; 
			$data = $wpdb->get_results( $sql );

		 
		} elseif ( ! empty( $user_id ) ) {
			$data = $wpdb->get_results(
				$wpdb->prepare( "SELECT $table_name.*, COUNT($booking_table.id) as total_booking FROM $table_name
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id WHERE $table_name.user_id = %s GROUP BY $table_name.id", $user_id )
			);
		} else {

			$data = $wpdb->get_results(
				"SELECT $table_name.*, COUNT($booking_table.id) as total_booking FROM $table_name
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id
				GROUP BY $table_name.id
				"
			);
		}

		// if any data has json data decode that data

		// Get all data

		return $data;
	}

	/**
	 * Get all  meeting Data. with total booking count also host id
	 * 
	 */
	public function getWithBookingCount( $id = null, $filterData = null, $user_id = null ) {

		global $wpdb;

		
		$table_name = $wpdb->prefix . $this->table;
		$host_table = $wpdb->prefix . 'tfhb_hosts'; 
		$booking_table = $wpdb->prefix . 'tfhb_bookings';

		$sql = "SELECT $table_name.*, COUNT($booking_table.id) as total_booking
				FROM $table_name  
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id";

		if($user_id) {
			$sql .= $wpdb->prepare( " WHERE $table_name.user_id = %s", $user_id );
		}
		$sql .= " GROUP BY $table_name.id" ;

		$data = $wpdb->get_results( $wpdb->prepare( $sql )); 
		return $data;
	}


	// delete
	public function delete( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$result     = $wpdb->delete( $table_name, array( 'id' => $id ) );
		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status'    => true,
				'delete_id' => $id,
			);
		}
	}
}
