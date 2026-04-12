<?php
namespace HydraBooking\Admin\Controller;
use HydraBooking\Admin\Controller\DashboardController;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DashboardWidget {

    private static $instance = null;

    public function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'tfhb_register_dashboard_widget' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'tfhb_widget_enqueue_assets' ) );
    }

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function tfhb_register_dashboard_widget() {
        wp_add_dashboard_widget( 'tfhb_widget', __( 'TFHB Overview', 'bafg' ), array( $this, 'tfhb_display_dashboard_widget' ) , null, null, 'normal', 'high' );
    }

    public function tfhb_widget_enqueue_assets( $screen ) {

        /**
		 * Admin Dashboard CSS
		 */
		if ( $screen == 'index.php' ) {
			wp_enqueue_style( 'tfhb-admin-dashboard', TFHB_URL . 'assets/admin/css/tfhb-admin-dashboard.css', '', BEAF_VERSION );
		}

    }

    public function tfhb_display_dashboard_widget() {
        $dasboard = new DashboardController();
        $dasboard_data = $dasboard->getDashboardsData();
        $bookings = $dasboard_data->data['total_bookings'];
        $earnings = $dasboard_data->data['total_earning'];

        $meetings_url       = admin_url( 'admin.php?page=hydra-booking#/meetings' );
        $booking_url       = admin_url( 'admin.php?page=hydra-booking#/bookings' );
        $settings_url       = admin_url( 'admin.php?page=hydra-booking#/settings/general' );
        $integration_url       = admin_url( 'admin.php?page=hydra-booking#/settings/integrations' );
        // tfhb_print_r($dasboard_data);
        ?>
        <div class="tfhb-widget">

            <!-- Stats Row -->
            <div class="tfhb-stats">
                <div class="tfhb-stat"> 
                    <strong><?php echo esc_html( $bookings['total'] ); ?></strong>
                    <span><?php esc_html_e( 'Total Bookings', 'bafg' ); ?></span>
                </div>
                <div class="tfhb-stat"> 
                    <strong><?php echo esc_html( $earnings['total'] ); ?></strong>
                    <span><?php esc_html_e( 'Total Earnings', 'bafg' ); ?></span>
                </div>
                
            </div>

            <!-- Quick Actions -->
            <div class="tfhb-actions">
                <a href="<?php echo esc_url( $meetings_url ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Create Meeting', 'bafg' ); ?>
                </a>
                <a href="<?php echo esc_url( $booking_url ); ?>" class="button">
                    <?php esc_html_e( 'View Bookings', 'bafg' ); ?>
                </a>
                <a href="<?php echo esc_url( $settings_url ); ?>" class="button">
                    <?php esc_html_e( 'Hydra Settings', 'bafg' ); ?>
                </a>
            </div>
            
            <!-- Popular Integrations -->
            <div class="tfhb-section tfhb-integrations">
                <h4><?php esc_html_e( 'Popular Features', 'bafg' ); ?></h4>

                <div class="tfhb-integration-grid">
                    <div  class="tfhb-integration-item">
                        <img style="width: 32px; " src="<?php echo esc_url( TFHB_URL . 'assets/images/google-calendar.png' ); ?>" alt="">
                        <span><?php esc_html_e( 'Google Calendar/Meet', 'bafg' ); ?></span>
                    </div>

                    <div class="tfhb-integration-item">
                        <img style="width: 32px; " src="<?php echo esc_url( TFHB_URL . 'assets/images/Woo.png' ); ?>" alt="">
                        <span><?php esc_html_e( 'Woo Payment', 'bafg' ); ?></span>
                    </div> 

                    <div class="tfhb-integration-item">
                        <img style="width: 32px; " src="<?php echo esc_url( TFHB_URL . 'assets/images/Mailchimp.svg' ); ?>" alt="">
                        <span><?php esc_html_e( 'Mailchimp Interaction', 'bafg' ); ?></span>
                    </div>

                    <div class="tfhb-integration-item">
                        <img style="width: 32px; " src="<?php echo esc_url( TFHB_URL . 'assets/images/paypal.png' ); ?>" alt="">
                  
                        <span><?php esc_html_e( 'PayPal Interaction', 'bafg' ); ?></span>
                    </div>

                </div>
            </div>

            <!-- Button for more integrations -->
            <div class="tfhb-actions">
                <a href="<?php echo esc_url( $integration_url ); ?>" target="_blank" class="button">
                    <?php esc_html_e( 'Check More Features', 'bafg' ); ?>
                </a>
            </div>

            <?php if(!class_exists('TFHB_INIT_PRO')){  ?>
            <!-- Upsell -->
            <div class="tfhb-upsell">
                <h4><?php esc_html_e( 'Unlock Pro Features', 'bafg' ); ?></h4>
                <ul>
                    <li><?php esc_html_e( '✔ One-to-Group booking', 'bafg' ); ?></li>
                    <li><?php esc_html_e( '✔ Recurring booking', 'bafg' ); ?></li>
                    <li><?php esc_html_e( '✔ 10+ Pro Features', 'bafg' ); ?></li> 
                </ul>
                <a href="<?php echo esc_url( 'https://hydrabooking.com/pricing/' ); ?>" target="_blank" class="button button-primary go-pro">
                    <?php esc_html_e( 'Upgrade Now', 'bafg' ); ?>
                </a>
            </div>
            <?php } ?>

            <!-- Blog Section -->
			<div class="tfhb-section-title"><?php esc_html_e( 'Latest posts from Hydra Booking', 'bafg' ); ?></div>
			<ul class="tfhb-blog-list">
				<li>
					<span class="tfhb-badge"><?php esc_html_e( 'NEW', 'bafg' ); ?></span>
					<a href="<?php echo esc_url( 'https://hydrabooking.com/how-to-sync-two-google-calendars/' ); ?>" target="_blank"><?php esc_html_e( 'How to Sync Two Google Calendars? Step-by-Step Guide', 'bafg' ); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( 'https://hydrabooking.com/maximize-efficiency-with-google-booking-calendar/' ); ?>" target="_blank"><?php esc_html_e( 'Maximize Efficiency with Google Booking Calendar', 'bafg' ); ?></a>
				</li>
			</ul>

            <!-- Footer -->
            <div class="tfhb-footer">
                <a href="<?php echo esc_url( 'https://themefic.com/docs/hydrabooking/' ); ?>" target="_blank">
                    <?php esc_html_e( 'Docs', 'bafg' ); ?>
                    <span aria-hidden="true" class="dashicons dashicons-external"></span>
                </a>
                <a href="<?php echo esc_url( 'https://portal.themefic.com/support/' ); ?>" target="_blank">
                    <?php esc_html_e( 'Support', 'bafg' ); ?>
                    <span aria-hidden="true" class="dashicons dashicons-external"></span>
                </a>
                <a href="<?php echo esc_url( 'https://hydrabooking.com/blog/' ); ?>" target="_blank">
                    <?php esc_html_e( 'Blog', 'bafg' ); ?>
                    <span aria-hidden="true" class="dashicons dashicons-external"></span>
                </a>
                <a href="<?php echo esc_url( 'https://hydrabooking.com/pricing/' ); ?>" target="_blank" class="go-pro">
                    <?php esc_html_e( 'Buy Now', 'bafg' ); ?>
                    <span aria-hidden="true" class="dashicons dashicons-external"></span>
                </a>
            </div>

        </div>
        <?php
    }



}
 
