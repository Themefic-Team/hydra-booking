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
		add_action( 'admin_head', array( $this, 'admin_menu_css' ) );
	}



	public function admin_menu() {

		// Get User Role
		// $userRole = $this->auth->userAllCaps();

		 

		add_menu_page(
			esc_html__( 'Hydra Booking', 'hydra-booking' ),
			esc_html__( 'Hydra Booking', 'hydra-booking' ),
			$this->get_menu_capability(),
			// array($this, 'hydra_booking_access'),
			'hydra-booking',
			array( $this, 'hydra_booking_page' ),
			// load faveconn
			 plugin_dir_url( __FILE__ ) . '../../assets/images/hydra-booking-logo.png',
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
				// Matches the Vue route guard (meta.Capabilities) and the
				// "Allow host to adjust system and personal settings" permission.
				'capability' => 'tfhb_manage_settings',
			),
			array(
				'id'         => 'setup-wizard',
				'Title'      => esc_html__( 'Setup Wizard', 'hydra-booking' ),
				'capability' => 'tfhb_manage_options',
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

		// Add Upgrade to Pro link if Pro version is not active
		if ( ! function_exists( 'tfhb_is_hydra_booking_pro_active' ) || ! tfhb_is_hydra_booking_pro_active() ) {
			global $submenu;
			$submenu['hydra-booking'][] = array(
				'<span class="tfhb-pro-upgrade-btn"><span class="dashicons dashicons-star-filled"></span> Upgrade to Pro</span>',
				'tfhb_manage_options',
				'https://hydrabooking.com/'
			);
		}
	}

	/**
	 * Capability for the top-level "Hydra Booking" page.
	 *
	 * Since the v1.2.4 security fix the tfhb_host role no longer holds
	 * tfhb_manage_options, so gating the top-level page on it blocked every
	 * host ("Sorry, you are not allowed to access this page."). The Vue app
	 * gates each route by its own capability, so the page itself only needs
	 * the user to hold at least one Hydra Booking capability.
	 *
	 * @return string
	 */
	private function get_menu_capability() {
		$caps = array(
			'tfhb_manage_options',
			'tfhb_manage_dashboard',
			'tfhb_manage_meetings',
			'tfhb_manage_booking',
			'tfhb_manage_hosts',
			'tfhb_manage_settings',
			'tfhb_manage_custom_availability',
			'tfhb_manage_integrations',
		);

		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				return $cap;
			}
		}

		// No Hydra Booking capability: keep the page admin-only (hidden).
		return 'tfhb_manage_options';
	}

	public function hydra_booking_page() {
		echo '<div id="tfhb-admin-app"></div>';
	}
	public function hydra_booking_access() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'hydra-booking' ) );
		}
	}

	public function admin_menu_css() {
		if ( ! function_exists( 'tfhb_is_hydra_booking_pro_active' ) || ! tfhb_is_hydra_booking_pro_active() ) {
			?>
			<style>
				.tfhb-pro-upgrade-btn {
					color: #fff !important;
					background: var(--tfhb-admin-primary-default, #2E6B38) !important;
					padding: 6px 12px;
					border-radius: 8px;
					display: inline-flex;
					align-items: center;
					gap: 3px;
					font-weight: 700;
					font-size: 11px;
					transition: 0.3s;
				}
				.tfhb-pro-upgrade-btn:hover {
					background: var(--tfhb-admin-primary-hover, #4C9959) !important;
					color: #fff !important;
				}
				.tfhb-pro-upgrade-btn .dashicons {
					font-size: 12px;
					width: 12px;
					height: 12px;
					line-height: 12px;
				}
			</style>
			<?php
		}
	}
}
