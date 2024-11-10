<?php
namespace HydraBooking\Admin\Controller;

// use HydraBooking\Admin\Controller\TransStrings;
use HydraBooking\Admin\Controller\AuthController;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class Enqueue {

	// constaract
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
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

		// if page=hydra-booking then load the script
		if ( ! isset( $_GET['page'] ) || 'hydra-booking' !== $_GET['page'] ) {
			return;
		}
		$user      = new AuthController();
		$user_auth = array(
			'id'   => $user->userID(),
			'role' => $user->userRole(),
			'caps' => $user->userAllCaps(),
		);

		// enqueue styles
		wp_enqueue_style( 'tfhb-admin-style', THB_URL . 'assets/admin/css/tfhb-admin-style.css', array(), null );

		wp_enqueue_script( 'tfhb-app-script', THB_URL . 'assets/admin/js/main.js', array( 'jquery' ), null, true );
		
		
		// wp_enqueue_script( 'tfhb-admin-core', apply_filters('tfhb_admin_core_script', 'http://localhost:5173/src/main.js'), array(), time(), true );

		//  Build the core script
		wp_enqueue_script('tfhb-admin-core',  apply_filters('tfhb_admin_core_script', THB_URL .'build/assets/tfhb-admin-app-script.js'), [], time(), true); 
		wp_enqueue_style('tfhb-admin-style-core',  apply_filters('tfhb_admin_core_style', THB_URL .'build/assets/tfhb-admin-app.css'), [], time(), 'all');
 
 
		wp_localize_script(
			'tfhb-admin-core',
			'tfhb_core_apps',
			array(
				// 'url' => THB_URL,
				'rest_nonce'           => wp_create_nonce( 'wp_rest' ),
				'tfhb_is_pro'           => wp_create_nonce( 'wp_rest' ),
				'admin_url'            => site_url(),
				'rest_route'            => get_rest_url(),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'tfhb_url'             => THB_URL,
				'tfhb_hydra_admin_url' => admin_url( 'admin.php?page=hydra-booking#/' ),
				'user'                 => $user_auth, 
				'trans'                => array(),
			)
		);

		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}
}
