<?php
namespace HydraBooking\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) { exit; }


// Use Namespace
use HydraBooking\DB\Meta;
use HydraBooking\Admin\Controller\RouteController;

/**
 * Frontend Dashboard
 * 
 * @author Sydur Rahman
 */
class FrontendDashboard {

	public function __construct() {
		// add_action('admin_init', array($this, 'init'));  
	}

    /**
     * Get Frontend Dashboard Settings
     *
     * @return void
     */
	public function create_endpoint() { 
		register_rest_route(
			'hydra-booking/v1',
			'/settings/fd-dashboard',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetFrontendDashboardSettings' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 
		register_rest_route(
			'hydra-booking/v1',
			'/settings/fd-dashboard/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'UpdateFrontendDashboardSettings' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		);
 
	}
    
    /**
     * Get Frontend Dashboard Settings
     *
     * @return void
     */
    public function GetFrontendDashboardSettings(){
         
        // Get all Page list 
        $pages = get_pages();
        $page_list = [];
        foreach ($pages as $page) {
            $page_list[] = array(
                'value' => $page->ID,
                'name' => $page->post_title
            );
        }

        // get frontend Dashboard Settings
        $frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
        $frontend_dashboard_settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();

        // return response
        $data = array(
            'status' => true,
            'pages' => $page_list,
            'settings' => $frontend_dashboard_settings
        );

        return rest_ensure_response($data);

    }

    /**
     * Update Frontend Dashboard Settings
     *
     * @return void
     */

    public function UpdateFrontendDashboardSettings(){
        $request =  json_decode(file_get_contents('php://input'), true);
        $settings = get_option('_tfhb_frontend_dashboard_settings');
        $fd_dashboard = isset($request['fd_dashboard']) ? $request['fd_dashboard'] : array();
        $settings['general']['dashboard_logo'] = isset($fd_dashboard['general']['dashboard_logo']) ? esc_url($fd_dashboard['general']['dashboard_logo']) : '';
        $settings['general']['mobile_dashboard_logo'] = isset($fd_dashboard['general']['mobile_dashboard_logo']) ? esc_url($fd_dashboard['general']['mobile_dashboard_logo']) : '';
        $settings['signup']['registration_page'] = isset($fd_dashboard['signup']['registration_page']) ? esc_html($fd_dashboard['signup']['registration_page']) : '';
        $settings['signup']['after_registration_redirect_type'] = isset($fd_dashboard['signup']['after_registration_redirect_type']) ? esc_html($fd_dashboard['signup']['after_registration_redirect_type']) : '';
        $settings['signup']['after_registration_redirect'] = isset($fd_dashboard['signup']['after_registration_redirect']) ? esc_html($fd_dashboard['signup']['after_registration_redirect']) : '';
        $settings['signup']['after_registration_redirect_custom'] = isset($fd_dashboard['signup']['after_registration_redirect_custom']) ? esc_html($fd_dashboard['signup']['after_registration_redirect_custom']) : '';
        $settings['login']['login_page'] = isset($fd_dashboard['login']['login_page']) ? esc_html($fd_dashboard['login']['login_page']) : '';
        $settings['login']['after_login_redirect_type'] = isset($fd_dashboard['login']['after_login_redirect_type']) ? esc_html($fd_dashboard['login']['after_login_redirect_type']) : '';
        $settings['login']['after_login_redirect'] = isset($fd_dashboard['login']['after_login_redirect']) ? esc_html($fd_dashboard['login']['after_login_redirect']) : '';
        $settings['login']['after_login_redirect_custom'] = isset($fd_dashboard['login']['after_login_redirect_custom']) ? esc_html($fd_dashboard['login']['after_login_redirect_custom']) : '';
 
     
        update_option('_tfhb_frontend_dashboard_settings', $settings);
        $settings = get_option('_tfhb_frontend_dashboard_settings'); 
        // return response
        $data = array(
            'status' => true,
            'message' => 'Settings Updated Successfully'
        );

        return rest_ensure_response($data);
    }
 
    
}
