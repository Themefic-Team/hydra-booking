<?php
namespace HydraBooking\Hooks;

class DeactivationHooks {

	public function __construct() {

		register_deactivation_hook( TFHB_PATH . 'hydra-booking.php', array( $this, 'tfhb_deactivation' ) );
	}

	public function tfhb_deactivation() {
		// Remove Host Role
		$this->tfhb_remove_host_role();

		// Remove Capabilities to the role
		$this->tfhb_add_capabilities_to_role();

		$this->remove_cron_job_schedule();

		// Deactivate Options
		$this->tfhb_options_deactivation_hooks();

		// remove option
		delete_option( 'tfhb_update_status' );
	}

	public function tfhb_remove_host_role() {
		if ( get_role( 'tfhb_host' ) ) {
			remove_role( 'tfhb_host' );
		}
	}
	// Remove Capabilities to the role
	public function tfhb_add_capabilities_to_role() {
		// administrator
		$role = get_role( 'administrator' );
		$role->remove_cap( 'tfhb_manage_options' );
		$role->remove_cap( 'tfhb_manage_dashboard' );
		$role->remove_cap( 'tfhb_manage_meetings' );
		$role->remove_cap( 'tfhb_manage_booking' );
		$role->remove_cap( 'tfhb_manage_settings' );
		$role->remove_cap( 'tfhb_manage_hosts' );
		$role->remove_cap( 'tfhb_manage_custom_availability' );
		$role->remove_cap( 'tfhb_manage_integrations' );
	}

	public function remove_cron_job_schedule() {
		// Every cron event the plugin schedules
		$hooks = array(
			'tfhb_after_booking_completed_schedule',
			'tfhb_cart_auto_remover_schedule',
			'tfhb_calendar_sync_schedule',
		);
		foreach ( $hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				wp_clear_scheduled_hook( $hook );
			}
		}

		// Promo notice cron and its stored options
		\HydraBooking\Admin\Controller\PromoNotice::tfhb_promo_notice_deactivation_hook();
	}

	// Deactivate Options
	public function tfhb_options_deactivation_hooks() {

		// Delete Quick Setup Option
		// delete_option( 'tfhb_hydra_quick_setup' );
	}
}
