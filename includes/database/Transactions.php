<?php
namespace HydraBooking\DB;

class Transactions {

	public $table = 'tfhb_transactions';
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
                booking_id INT(11) NOT NULL,
                attendee_id INT(11) NOT NULL,
                meeting_id INT(11) NOT NULL,
				host_id INT(11) NOT NULL,
                customer_id VARCHAR(100) NULL,
                payment_method VARCHAR(100) NULL,
                total VARCHAR(100) NULL,
                transation_history LONGTEXT NULL, 
                status LONGTEXT NULL, 
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
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
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_transactions" );
	}

	/**
	 * Create the database transactions.
	 */
	public function add( $request ) {

		global $wpdb;
 
		$table_name                    = $wpdb->prefix . $this->table;
		$request['transation_history'] = is_array( $request['transation_history'] ) ? json_encode( $request['transation_history'], true ) : $request['transation_history'];

		// insert transactions
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
	 * Update the database transactions.
	 */
	public function update( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$id = $request['id'];
		unset( $request['id'] );
		// Update transactions
		$result = $wpdb->update(
			$table_name,
			$request,
			array( 'id' => $id )
		);

		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status' => true,
			);
		}
	}
	/**
	 * Get all  transactions Data.
	 */
	public function get( $id = null ) {

		global $wpdb;
		
		if ( $id ) {
			$data = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tfhb_transactions WHERE id = %d",$id )
			);
		} else {
			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tfhb_transactions");
		}

		// Get all data

		return $data;
	}

	public function totalEarning($previous_date, $current_date, $user_id = false) {
		// where "created_at BETWEEN '$previous_date' AND '$current_date'",  
		global $wpdb;
		$host_table = $wpdb->prefix . 'tfhb_hosts';
		$table_name = $wpdb->prefix . $this->table;
		// Join the tables transactions and meetings
		$sql = "SELECT  SUM($table_name.total) AS total_sum FROM $table_name
		LEFT JOIN $host_table ON $table_name.host_id = $host_table.id
		WHERE $table_name.created_at BETWEEN '$previous_date' AND '$current_date'";
		if ($user_id) {
			$sql .= " AND $host_table.id = '$user_id'";
		} 
		$data = $wpdb->get_var($sql);
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
