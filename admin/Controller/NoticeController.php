<?php
namespace HydraBooking\Admin\Controller;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

// Use Namespace
use HydraBooking\Admin\Controller\NoticeController;

class NoticeController {
    // constaract
    public function __construct() {
        add_action('wp_ajax_tfhb_user_registration_license', array($this, 'tfhb_user_registration_license_callback'));
        add_action('wp_ajax_tfhb_cart_item_license', array($this, 'tfhb_cart_item_license_callback'));
        add_action('admin_notices', array($this, 'tfhb_user_registration_notice'));
    }

    
    // Notice Design
    function tfhb_user_registration_notice(){
        ?>
        <div class="notice notice-success is-dismissible">
            <button class="tfhb-license-register-btn">Register</button>
        </div>
        <?php
    }

    // Register Callback
    function tfhb_user_registration_license_callback(){
        check_ajax_referer('wp_rest', 'nonce');

        if ( ! isset($_POST['email']) || ! is_email($_POST['email']) ) {
            wp_send_json_error(['message' => 'Invalid email address.']);
        }
    
        $email = sanitize_email($_POST['email']);
        $api_url = add_query_arg('email', urlencode($email), 'https://portaltest.hydrabooking.com/wp-json/tourfic-users/v1/user/create');
    
        $response = wp_remote_get($api_url, [
            'timeout' => 10,
        ]);
    
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'API request failed: ' . $response->get_error_message()]);
        }
    
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
        if (!empty($response_body['message'])) {
            wp_send_json_success(['message' => 'Check your inbox and set a password for free licensing!']);
        } else {
            wp_send_json_error(['message' => $response_body['message'] ?? 'Something went wrong.']);
        }
        
        wp_die();
    }

    // Add To Cart Callback
    function tfhb_cart_item_license_callback() {
        check_ajax_referer('wp_rest', 'nonce');
    
        if (!isset($_POST['key'])) {
            wp_send_json_error(['message' => 'Invalid Key.']);
        }
    
        $key = sanitize_text_field($_POST['key']);
    
        // Target website
        $api_url = 'http://tourfic-development-site.local/wp-json/tourfic-users/v1/cart-item/create';
    
        // Send request with cookies for session
        $response = wp_remote_post($api_url, [
            'timeout' => 10,
            'body'    => ['key' => $key],
            'cookies' => $_COOKIE, // Send cookies for cart session tracking
        ]);
    
        // Handle response
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'API request failed: ' . $response->get_error_message()]);
        }
    
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($response_body['message'])) {
            wp_send_json_success([
                'message' => 'Product added to remote cart successfully!',
                'url' => $response_body['cart_url']
            ]);
        } else {
            wp_send_json_error(['message' => $response_body['message'] ?? 'Something went wrong.']);
        }
    
        wp_die();
    }
    
}