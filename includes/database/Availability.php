<?php
namespace HydraBooking\DB;

class Availability {

	public $table = 'tfhb_availability';
	public function __construct() {
	}

	/**
	 * Run the database migration.
	 */
	public function migrate() {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

	$charset_collate = $wpdb->get_charset_collate();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		$sql = "CREATE TABLE $table_name (
                id INT(11) NOT NULL AUTO_INCREMENT,
                host INT(11) NULL,
                title VARCHAR(100) NOT NULL,
                time_zone VARCHAR(50) NULL, 
                override VARCHAR(255) NULL,
                time_slots LONGTEXT NULL, 
                date_status LONGTEXT NULL, 
                date_slots LONGTEXT NULL, 
                status VARCHAR(20) NULL,
				default_status INT(11) NULL, 
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
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_availability");
}

	/**
	 * Create the database availability.
	 */
	public function add( $request ) {

		global $wpdb;

		$table_name            = $wpdb->prefix . $this->table;
		$request['time_slots'] = maybe_serialize( $request['time_slots'] );
		if(isset($request['date_status'])){
			$request['date_status'] = maybe_serialize( $request['date_status'] );
	} 

	// insert availability
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
	 * Update the database availability.
	 */
	public function update( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

	$id = $request['id'];
	unset( $request['id'] );
	// Update availability
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
	 * Get all  availability Data.
	 */
	public function get( $where = null, $join = false, $FirstOrFaill = false, $limit = false ) {

	global $wpdb;

	if ( $where  != null && !is_array($where) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tfhb_availability WHERE id = %d", $where )
		);
		}elseif ( $where  != null && is_array($where) ) {
			$sql = "SELECT * FROM {$wpdb->prefix}tfhb_availability WHERE ";
			$i   = 0;
			foreach ( $where as $k => $v ) {
				if ( $i == 0 ) {
					$sql .= " $k = $v";
				} else {
					$sql .= " AND $k = $v";
				}
				++$i;
			}

		if($FirstOrFaill == true){
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$data = $wpdb->get_row(
				$wpdb->prepare( $sql )
			);
		}else{
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$data = $wpdb->get_results(
				$wpdb->prepare( $sql )
			);
		}
			
	}
	 else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tfhb_availability");
	}

		// Get all data

		return $data;
	}

// delete
public function delete( $id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . $this->table;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
