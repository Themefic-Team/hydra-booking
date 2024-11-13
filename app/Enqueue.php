<?php
namespace HydraBooking\App;


	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class Enqueue {
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'tfhb_enqueue_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'tfhb_enqueue_scripts' ) );
	} 

    public function tfhb_enqueue_scripts() {
		wp_enqueue_style( 'tfhb-style', THB_URL . 'assets/app/css/style.css', '', THB_VERSION );
		wp_register_style( 'tfhb-select2-style', THB_URL . 'assets/app/css/select2.min.css', array(), THB_VERSION );

		// Global General Settings
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();

		$_tfhb_appearance_settings = get_option( '_tfhb_appearance_settings' );

		// Integration Settings
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$tfhb_paypal = isset( $_tfhb_integration_settings['paypal'] ) ? $_tfhb_integration_settings['paypal'] : array();
		 
		
		$tfhb_primary_color   = ! empty( $_tfhb_appearance_settings['primary_color'] ) ? $_tfhb_appearance_settings['primary_color'] : '#2E6B38';
		$tfhb_secondary_color = ! empty( $_tfhb_appearance_settings['secondary_color'] ) ? $_tfhb_appearance_settings['secondary_color'] : '#273F2B';
		$tfhb_paragraph_color = ! empty( $_tfhb_appearance_settings['paragraph_color'] ) ? $_tfhb_appearance_settings['paragraph_color'] : '#273F2B';
		$tfhb_theme_css       = "
        :root {
            --tfhb-primary-color: $tfhb_primary_color;
            --tfhb-secondary-color: $tfhb_secondary_color;
            --tfhb-paragraph-color: $tfhb_paragraph_color;
          }
        ";
		wp_add_inline_style( 'tfhb-style', $tfhb_theme_css ); 
		// register script
		wp_register_script( 'tfhb-stripe-script', '//checkout.stripe.com/checkout.js', array( 'jquery' ), '1.0.0' );
		if(isset($tfhb_paypal['status']) && $tfhb_paypal['status'] == 1){
			if($tfhb_paypal['environment'] == 'live'){
				$sdk_url = 'https://www.paypal.com/sdk/js?client-id='.$tfhb_paypal['client_id'].'';
			}else{ 
				$sdk_url = 'https://www.sandbox.paypal.com/sdk/js?client-id='.$tfhb_paypal['client_id'].'';
			}
			// if
			wp_register_script( 'tfhb-paypal-sdk', esc_url($sdk_url), array(), null, true );
		}
		wp_register_script( 'tfhb-paypal-script', '//paypalobjects.com/api/checkout.js', array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'tfhb-select2-script', THB_URL . 'assets/app/js/select2.min.js', array( 'jquery', 'tfhb-app-script' ), THB_VERSION, true );
		wp_register_script( 'tfhb-app-script', THB_URL . 'assets/app/js/main.js', array( 'jquery' ), THB_VERSION, true );

		wp_localize_script(
			'tfhb-app-script',
			'tfhb_app_booking',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'tfhb_nonce' ),
				'general_settings' => $general_settings,
			)
		);
	}


}