<?php
namespace HydraBooking\Admin\Controller;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

use HydraBooking\Admin\Controller\TransStrings;
use HydraBooking\Admin\Controller\AuthController;

/**
 * Enqueue Class
 * 
 * @package HydraBooking\Admin\Controller
 * @since 1.0.0
 * 
 * @author Sydur Rahman
 */ 
class Enqueue {

	// constaract
	public function __construct() { 
		
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); 
		add_action( 'wp_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'thb_loadScriptAsModule' ), 10, 3 );
	}
	public function thb_loadScriptAsModule( $tag, $handle, $src ) {
		if ( 'tfhb-admin-core' !== $handle ) {
			return $tag;
		}
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		return $tag;
	}
	public function admin_enqueue_scripts() {
		
		wp_enqueue_script( 'tfhb-admin-script', TFHB_URL . 'assets/admin/js/main.js', array( 'jquery' ), time(), true );
		wp_localize_script(
			'tfhb-admin-script',
			'tfhb_admin_notice',
			array(
				'_nonce'           => wp_create_nonce( 'wp_notice' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
			)
		);

		$front_end_dashboard = false;
		// if is admin page
		if ( is_admin() ) {
			if ( ! isset( $_GET['page'] ) || 'hydra-booking' !== $_GET['page'] ) { 
				return;
			}
		}
	
		// if its load in frontend then get page template
		if ( ! is_admin() ) {
			// get current page and page template
			$current_page = get_queried_object();
			$page_template = get_page_template_slug( $current_page->ID );
			if ( 'tfhb-frontend-dashboard.php' !== $page_template ) {
				return;
			}
			$front_end_dashboard = true;
		}

		$user      = new AuthController();
		$user_auth = array(
			'id'   => $user->userID(),
			'host_id'  => $user->userHostID(),
			'role' => $user->userRole(),
			'caps' => $user->userAllCaps(),
		);

		// enqueue styles
		wp_enqueue_style( 'tfhb-admin-style', TFHB_URL . 'assets/admin/css/tfhb-admin-style.css', array(), null );
 
		
		// wp_enqueue_script( 'tfhb-admin-core', apply_filters('tfhb_admin_core_script', 'http://localhost:5173/src/main.js'), array(), time(), true );

		//  Build the core script
		wp_enqueue_script('tfhb-admin-core',  apply_filters('tfhb_admin_core_script', TFHB_URL .'build/assets/tfhb-admin-app-script.js'), [], time(), true); 
		wp_enqueue_style('tfhb-admin-style-core',  apply_filters('tfhb_admin_core_style', TFHB_URL .'build/assets/tfhb-admin-app.css'), [], time(), 'all');
 
		// Localize the script
		 
		$embed_script_link = esc_html('<script src="' .TFHB_URL . 'assets/app/js/widget.js"></script>');
 
		wp_localize_script(
			'tfhb-admin-core',
			'tfhb_core_apps',
			array(
				// 'url' => TFHB_URL,
				'rest_nonce'           => wp_create_nonce( 'wp_rest' ),
				'tfhb_is_pro'          => wp_create_nonce( 'wp_rest' ),
				'admin_url'            => site_url(),
				'rest_route'           => get_rest_url(),
				'embed_script_link'    => esc_html( $embed_script_link ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'front_end_dashboard'  => $front_end_dashboard,
				'tfhb_url'             => TFHB_URL,
				'tfhb_hydra_admin_url' => admin_url( 'admin.php?page=hydra-booking#/' ),
				'user'                 => $user_auth, 
				'trans'				   => TransStrings::getTransStrings(),
			)
		);

		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}
}
