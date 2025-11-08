<?php
namespace HydraBooking\Admin;

use HydraBooking\Admin\Controller\AdminMenu; 
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\Admin\Controller\UpdateController;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Migration\Migration;
use HydraBooking\Admin\Controller\NoticeController;
use HydraBooking\Admin\Controller\licenseController;
use HydraBooking\License\HydraBooking; 
// Load Migrator
use HydraBooking\DB\Migrator;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class Admin {

	// constaract
	public function __construct() { 
		// run migrator
		new Migrator();
	

		// admin menu
		new AdminMenu();
 

		// update controller
		new UpdateController();
		
		// notice controller
		new NoticeController();

		// Notification controller
		// new Notification();

		// activation hooks
		register_activation_hook( TFHB_URL, array( $this, 'activate' ) );

		Migration::instance();

		// license controller
        new  HydraBooking();
		new licenseController();
		

		add_action( 'admin_init', array( $this, 'tfhb_hydra_activation_redirect' ) );

		// Update Existing User Role
		add_action( 'admin_init', array( $this, 'plugins_update_v_1_0_10' ) );

		// 
		// add dome in admin footer based one page template
		add_action( 'admin_footer', array( $this, 'add_admin_footer_content' ) );


		add_action('wp_ajax_tfhb_hydra_manage_plugin', array( $this, 'tfhb_hydra_manage_plugin' ) );
	}

	public function add_admin_footer_content() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( empty( $screen ) || 'toplevel_page_hydra-booking' !== $screen->id ) {
			return;
		}

		 echo $this->tf_sidebar(); 
	}


	public function tfhb_hydra_manage_plugin() {
		check_ajax_referer('wp_rest', 'security');

		if (!current_user_can('install_plugins')) {
			wp_send_json_error('You do not have permission to perform this action.');
		}

		$plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';
		$plugin_filename = isset($_POST['plugin_filename']) ? sanitize_text_field($_POST['plugin_filename']) : '';
		$plugin_action = isset($_POST['plugin_action']) ? sanitize_text_field($_POST['plugin_action']) : '';

		if (!$plugin_slug || !$plugin_action) {
			wp_send_json_error('Invalid request.');
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ($plugin_action === 'install') {
			$api = plugins_api('plugin_information', ['slug' => $plugin_slug]);

			if (is_wp_error($api)) {
				wp_send_json_error($api->get_error_message());
			}

			$upgrader = new \Plugin_Upgrader(new \WP_Ajax_Upgrader_Skin());
			$install_result = $upgrader->install($api->download_link);

			if (is_wp_error($install_result)) {
				wp_send_json_error($install_result->get_error_message());
			}

			wp_send_json_success(['message' => 'Installed successfully.']);
		}

		if ($plugin_action === 'activate') {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_filename . '.php';

			if (!file_exists($plugin_path)) {
				wp_send_json_error('Plugin file not found.');
			}

			$activate_result = activate_plugin($plugin_path);

			if (is_wp_error($activate_result)) {
				wp_send_json_error($activate_result->get_error_message());
			}

			wp_send_json_success(['message' => 'Activated successfully.']);
		}

		wp_send_json_error('Invalid action.');
	}


	public function activate() {
		// $Migrator = new Migrator();
		new Migrator();
	}

	public function tfhb_hydra_activation_redirect() {
		if ( wp_doing_ajax() ) {
			return;
		}

		if ( ! get_option( 'tfhb_hydra_quick_setup' ) ) {

			update_option( 'tfhb_hydra_quick_setup', 1 );
			wp_redirect( admin_url( 'admin.php?page=hydra-booking#/setup-wizard' ) );

			// exit;
		}
	}

	public function plugins_update_v_1_0_10(){

 
		if( TFHB_VERSION == '1.0.10' && get_option( 'tfhb_update_status' ) != '1.0.10' ) {
			$role = get_role( 'tfhb_host' );
			// remove capabilities
			$role->remove_cap( 'edit_posts' );
			$role->remove_cap( 'edit_pages' );
			$role->remove_cap( 'edit_others_posts' );
			$role->remove_cap( 'create_posts' );
			$role->remove_cap( 'manage_categories' );
			$role->remove_cap( 'publish_posts' );
			$role->remove_cap( 'edit_themes' );
			$role->remove_cap( 'install_plugins' );
			$role->remove_cap( 'update_plugin' );
			$role->remove_cap( 'update_core' );
			$role->remove_cap( 'manage_options' );

			// Tfhb Update Status
			update_option( 'tfhb_update_status', '1.0.10' );
		} 
	}


	public function tf_sidebar() {
		?>

		<div class="tfhb-dashboard-sidebar-content" style="display: none;">
			<div class="tfhb-sidebar-wrap">
				<!-- promo banner  -->
				 	<?php echo apply_filters('tfhb_dashboard_helper_banner', ''); ?>
				 
				<div class="tfhb-sidebar-content">

					<?php echo $this->tf_get_sidebar_plugin_list(); ?>

					<div class="tfhb-sidebar-customization-quote">
						<div class="tfhb-quote-header">
							<i class="fa-solid fa-code"></i>
							<a href="<?php echo esc_url(tfhb_utm_generator( 'https://portal.themefic.com/hire-us/', array( 'utm_medium' => 'dashboard_customization_quote') ) ); ?>" target="_blank" ><?php echo __('Get Free Quote', 'hydra-booking');  ?></a>
						</div>
						<div class="tfhb-quote-content">
							<h3><?php echo __('Need Help Customizing Your WordPress Site?', 'hydra-booking');  ?></h3>
							<p><?php echo __('Want to tweak a theme, adjust a plugin like Ultra Addons or add custom functionality to your site? Our expert WordPress developers can tailor it just the way you need. We only charge $29/hour.', 'hydra-booking');  ?></p>								
						</div>
					</div>

					<div class="tfhb-quick-access">
						<h3><?php echo __('Helpful Resources', 'hydra-booking');  ?></h3>
						<div class="tfhb-quick-access-wrapper">
							<div class="tfhb-access-item">
								<a href="<?php echo esc_url(tfhb_utm_generator( 'https://themefic.com/docs/tfhb/', array( 'utm_medium' => 'dashboard_doc_link') ) ); ?>" target="_blank">
									<span class="icon"><i class="fa-solid fa-folder-open"></i></span>
									<?php echo _e( 'Documentation', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="<?php echo esc_url(tfhb_utm_generator( 'https://portal.themefic.com/support/', array( 'utm_medium' => 'dashboard_support_link') ) ); ?>" target="_blank">
									<span class="icon"><i class="fa-solid fa-headset"></i></span>
									<?php echo _e( 'Get Support', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="https://www.facebook.com/groups/ultimate.cf7" target="_blank">
									<span class="icon"><i class="fa-solid fa-users"></i></span>
									<?php echo _e( 'Join our Community', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="https://app.loopedin.io/ultimate-addons-for-contact-form-7" target="_blank">
									<span class="icon"><i class="fa-solid fa-road-circle-check"></i></span>
									<?php echo _e( 'See our Roadmap', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="https://app.loopedin.io/ultimate-addons-for-contact-form-7#/ideas" target="_blank">
									<span class="icon"><i class="fa-solid fa-lightbulb"></i></span>
									<?php echo _e( 'Request a Feature', 'hydra-booking' ); ?>
								</a>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<?php
	}

	public function tf_get_sidebar_plugin_list(){

		$plugins = [
			// [
			// 	'name'       => 'tfhb',
			// 	'slug'       => 'ultimate-addons-for-contact-form-7',
			// 	'file_name'  => 'ultimate-addons-for-contact-form-7',
			// 	'subtitle'   => '40+ Essential Addons for Contact Form 7',
			// 	'image'      => 'https://ps.w.org/ultimate-addons-for-contact-form-7/assets/icon-128x128.png',
			// 	// 'pro'        => [
			// 	// 	'slug'      => 'ultimate-addons-for-contact-form-7-pro',
			// 	// 	'file_name' => 'ultimate-addons-for-contact-form-7-pro',
			// 	// 	'url'       => 'https://cf7addons.com/pricing/',
			// 	// ],
			// ],
			[
				'name'       => 'Hydra',
				'slug'       => 'hydra-booking',
				'file_name'  => 'hydra-booking',
				'subtitle'   => 'All in One Appointment Booking System',
				'image'      => 'https://ps.w.org/hydra-booking/assets/icon-128x128.jpg',
				// 'pro'        => [
				// 	'slug'      => 'hydra-booking-pro',
				// 	'file_name' => 'hydra-booking-pro',
				// 	'url'       => 'https://hydrabooking.com/',
				// ],
			],
			[
				'name'       => 'BEAF',
				'slug'       => 'beaf-before-and-after-gallery',
				'file_name'  => 'before-and-after-gallery',
				'subtitle'   => 'Ultimate Before After Image Slider & Gallery',
				'image'      => 'https://ps.w.org/beaf-before-and-after-gallery/assets/icon-128x128.png',
				// 'pro'        => [
				// 	'slug'      => 'beaf-before-and-after-gallery-pro',
				// 	'file_name' => 'before-and-after-gallery-pro',
				// 	'url'       => 'https://themefic.com/plugins/beaf/pro/',
				// ],
			],
			[
				'name'       => 'Tourfic',
				'slug'       => 'tourfic',
				'file_name'  => 'tourfic',
				'subtitle'   => 'Travel, Hotel Booking & Car Rental WP Plugin',
				'image'      => 'https://ps.w.org/tourfic/assets/icon-128x128.gif',
				// 'pro'        => [
				// 	'slug'      => 'tourfic-pro',
				// 	'file_name' => 'tourfic-pro',
				// 	'url'       => 'https://themefic.com/tourfic/',
				// ],
			],
			[
				'name'       => 'Instantio',
				'slug'       => 'instantio',
				'file_name'  => 'instantio',
				'subtitle'   => 'WooCommerce Quick & Direct Checkout',
				'image'      => 'https://ps.w.org/instantio/assets/icon-128x128.png',
				// 'pro'        => [
				// 	'slug'      => 'wooinstant',
				// 	'file_name' => 'wooinstant',
				// 	'url'       => 'https://themefic.com/instantio/',
				// ],
			],
			// [
			// 	'name'       => 'Before After Slider for WooCommerce – eBEAF',
			// 	'slug'       => 'before-after-for-woocommerce',
			// 	'file_name'  => 'before-after-for-woocommerce',
			// 	'image'      => 'https://ps.w.org/before-after-for-woocommerce/assets/icon-128x128.gif',
			// 	'pro_url'    => '',
			// 	'pro'        => [
			// 		'slug'      => 'before-after-for-woocommerce-pro',
			// 		'file_name' => 'before-after-for-woocommerce-pro',
			// 		'url'       => 'https://themefic.com/plugins/ebeaf/pro/',
			// 	],
			// ],
		];

		?>

		<ul>
			<?php foreach ($plugins as $plugin): 
				$plugin_path = $plugin['slug'] . '/' . $plugin['file_name'] . '.php';
				$installed = file_exists(WP_PLUGIN_DIR . '/' . $plugin_path);
				$activated = $installed && is_plugin_active($plugin_path);

				$pro_installed = false;
				$pro_activated = false;
				
				if (!empty($plugin['pro'])) {
					$pro_path = $plugin['pro']['slug'] . '/' . $plugin['pro']['file_name'] . '.php';
					$pro_installed = file_exists(WP_PLUGIN_DIR . '/' . $pro_path);
					$pro_activated = $pro_installed && is_plugin_active($pro_path);
				}

				?>

				<li class="tfhb-plugin-item <?php echo esc_attr($plugin['slug'] == 'hydra-booking' ? 'featured' : ''); ?>" data-plugin-slug="<?php echo esc_attr($plugin['slug']); ?>">
					<div class="tfhb-plugin-info-wrapper">
						<div class="tfhb-plugin-info">
							<img src="<?php echo esc_url($plugin['image']); ?>" alt="<?php echo esc_attr($plugin['name']); ?>" class="<?php echo esc_attr($plugin['name'] == 'BEAF' ? 'beaf-logo' : ''); ?>" width="40" height="40">
							<div class="tfhb-plugin-btn">
								<span class="badge free">Free</span>
								<?php if (!$installed): ?>
									<button class="tfhb-plugin-button install" data-action="install" data-plugin="<?php echo esc_attr($plugin['slug']); ?>" data-plugin_filename="<?php echo esc_attr($plugin['file_name']); ?>">
										Install <span class="loader"></span>
									</button>
								<?php elseif (!$activated): ?>
									<button class="tfhb-plugin-button activate" data-action="activate" data-plugin="<?php echo esc_attr($plugin['slug']); ?>" data-plugin_filename="<?php echo esc_attr($plugin['file_name']); ?>" >
										Activate <span class="loader"></span>
									</button>
								<?php else: ?>
									<span class="tfhb-plugin-button plugin-status active">Activated</span>
								<?php endif; ?>

								<?php if (!empty($plugin['pro'])): ?>
									<?php if (!$pro_installed): ?>
										<a href="<?php echo esc_url($plugin['pro']['url']); ?>" class="tfhb-plugin-button pro" target="_blank">Get Pro</a>
									<?php elseif (!$pro_activated): ?>
										<button class="tfhb-plugin-button activate-pro" data-action="activate" data-plugin="<?php echo esc_attr($plugin['pro']['slug']); ?>" data-plugin_filename="<?php echo esc_attr($plugin['pro']['file_name']); ?>">
											Activate Pro <span class="loader"></span>
										</button>
									<?php else: ?>
										<span class="tfhb-plugin-button plugin-status active-pro">Pro Activated</span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
						<div class="tfhb-plugin-content">
							<h4><?php echo esc_html($plugin['name']); ?></h4>
							<p><?php echo esc_html($plugin['subtitle']); ?></p>
							<strong></strong>
						</div>
					</div>
				</li>

			<?php endforeach; ?>

		</ul>

		<?php 
	}
}
