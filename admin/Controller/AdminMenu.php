<?php
namespace HydraBooking\Admin\Controller;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

// Use Namespace
use HydraBooking\Admin\Controller\AuthController;


class AdminMenu {

	private $auth;
	 


	// constaract
	public function __construct() {

		$this->auth = new AuthController();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}



	public function admin_menu() {

		// Get User Role
		// $userRole = $this->auth->userAllCaps();

		 

		add_menu_page(
			esc_html__( 'Hydra Booking', 'hydra-booking' ),
			esc_html__( 'Hydra Booking', 'hydra-booking' ),
			'tfhb_manage_options',
			// array($this, 'hydra_booking_access'),
			'hydra-booking',
			array( $this, 'hydra_booking_page' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="12 8 48 56"><path fill="black" d="M39.3462 54.2936L29.4061 44.3534L27.0543 42.0016L25.6746 40.6219L20.2498 35.1972V57.9938H39.3462C40.4751 57.9938 41.5726 57.837 42.6074 57.5548L39.3462 54.2936Z"/><path fill="black" d="M29.4061 36.8591L20.2498 27.7028V14.0625H21.849L28.2772 20.4907L36.9945 29.2393L29.4061 36.8591Z"/><path fill="black" d="M52.0146 44.228L40.7574 32.9708L39.3463 34.3819L34.9877 38.7405L33.1376 40.5906L39.315 46.7679L47.4992 54.9521C50.2586 52.6317 52.0146 49.1197 52.0146 45.2314C52.0459 44.9179 52.0459 44.5729 52.0146 44.228Z"/><path fill="black" d="M51.2932 22.4348C50.6661 20.7102 49.6627 19.1423 48.377 17.8567C46.088 15.5049 42.8582 14.0625 39.3148 14.0625H29.3433L31.8205 16.5397L49.6 34.3192C51.1678 32.2182 52.0772 29.6156 52.0772 26.7935C52.0458 25.257 51.795 23.7832 51.2932 22.4348Z"/></svg>' ),
			25
		);

		add_submenu_page(
			'hydra-booking',
			esc_html__( 'Dashboard', 'hydra-booking' ),
			esc_html__( 'Dashboard', 'hydra-booking' ),
			'tfhb_manage_dashboard',
			'hydra-booking#',
			array( $this, 'hydra_booking_page' )
		);
		// Create a array for sub menu
		$sub_menu = array(
			array(
				'id'         => 'meetings',
				'Title'      => esc_html__( 'Meetings', 'hydra-booking' ),
				'capability' => 'tfhb_manage_meetings',
			),
			array(
				'id'         => 'bookings',
				'Title'      => esc_html__( 'Bookings', 'hydra-booking' ),
				'capability' => 'tfhb_manage_booking',
			),
			array(
				'id'         => 'hosts',
				'Title'      => esc_html__( 'Hosts', 'hydra-booking' ),
				'capability' => 'tfhb_manage_hosts',
			),
			array(
				'id'         => 'settings',
				'Title'      => esc_html__( 'Settings', 'hydra-booking' ),
				'capability' => 'tfhb_manage_settings',
			),
			array(
				'id'         => 'setup-wizard',
				'Title'      => esc_html__( 'Setup Wizard', 'hydra-booking' ),
				'capability' => 'tfhb_manage_settings',
			),

		);

		// Loop through array and create sub menu
		foreach ( $sub_menu as $menu ) {

			$menu_id = $menu['id'];
			add_submenu_page(
				'hydra-booking',
				$menu['Title'],
				$menu['Title'],
				$menu['capability'],
				'hydra-booking#/' . $menu_id,
				array( $this, 'hydra_booking_page' )
			);
		}

		// remove Sub Menu
		remove_submenu_page( 'hydra-booking', 'hydra-booking' );
	}

	public function hydra_booking_page() {
		echo '<div id="tfhb-admin-app"></div>';
	}
	public function hydra_booking_access() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hydra-booking' ) );
		}
	}
}
