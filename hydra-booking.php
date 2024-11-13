<?php
/**
 * Plugin Name: Hydra Booking
 * Plugin URI: https://themefic.com/hydra-booking
 * Description: Create a booking / Appointment Form using Contact Form 7. You can insert Calendar, Time on the form and manage your booking. User can pay using WooCommerce.
 * Version: 1.0.3
 * Author: Themefic
 * Author URI: https://themefic.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hydra-booking
 * Domain Path: /languages
 */

// don't load directly
defined( 'ABSPATH' ) || exit;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

class THB_INIT {
	// CONSTARACT
	public function __construct() {
		// DEFINE PATH
		define( 'THB_PATH', plugin_dir_path( __FILE__ ) );
		define( 'THB_URL', plugin_dir_url( __FILE__ ) );
		define( 'THB_VERSION', '1.0.3' );

		// Load Vendor Auto Load
		if ( file_exists( THB_PATH . '/vendor/autoload.php' ) ) {

			require_once THB_PATH . '/vendor/autoload.php';
		}

		// Helper Function
		// Load Vendor Auto Load
		if ( file_exists( THB_PATH . '/includes/Includes.php' ) ) {

			require_once THB_PATH . '/includes/Includes.php';
		}
		

	

		add_action( 'init', array( $this, 'init' ) ); 
		add_action( 'current_screen', array( $this, 'tfhb_get_plugin_screen' ) );
		
	}


	public function init() {
		new HydraBooking\Admin\Controller\ScheduleController();

		// Post Type
		new HydraBooking\PostType\Meeting\Meeting_CPT();
		new HydraBooking\PostType\Booking\Booking_CPT();

		// Create a New host Role
		new HydraBooking\Admin\Controller\RouteController();

		if ( is_admin() ) {
			// Load Admin Class
			new HydraBooking\Admin\Admin();
		}

		// Load App Class
		new HydraBooking\App\App();
	}




	public function tfhb_get_plugin_screen() {
		$current_screen = get_current_screen();
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'hydra-booking' ) {
			// remove admin notice
			add_action( 'in_admin_header', array( $this, 'tfhb_hide_notices' ), 99 );
		}
	}

	public function tfhb_hide_notices() {
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'admin_notices' );
	}


}



new THB_INIT();
