<?php
namespace HydraBooking\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) { exit; }


// Use Namespace
use HydraBooking\DB\Meta;
use HydraBooking\DB\Host;
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
        // Frontend Dashboard Profile
        register_rest_route(
			'hydra-booking/v1',
			'/fd-dashboard/user-auth',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'GetFdUserAuth' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 
        // Logout
        register_rest_route(
			'hydra-booking/v1',
			'/fd-dashboard/logout',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'LogoutFdUser' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 

        // Update user Profile
        register_rest_route(
			'hydra-booking/v1',
			'/fd-dashboard/update-profile',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'UpdateFdUserProfile' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 

        // Change Password 
        register_rest_route(
			'hydra-booking/v1',
			'/fd-dashboard/change-password',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ChangeFdUserPassword' ),
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
                'value' => strval($page->ID),
                'name' => $page->post_title
            );
        }

        // get frontend Dashboard Settings
        $frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
        $settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
        
        $settings['signup']['registration_page'] =  isset($settings['signup']['registration_page']) && !empty($settings['signup']['registration_page']) ? $settings['signup']['registration_page'] :  get_option( 'tfhb_register_page_id' );

        $settings['signup']['after_registration_redirect_type'] =  isset($settings['signup']['after_registration_redirect_type']) && !empty($settings['signup']['after_registration_redirect_type']) ? $settings['signup']['after_registration_redirect_type'] :  'page';
        $settings['signup']['after_registration_redirect'] =  isset($settings['signup']['after_registration_redirect']) && !empty($settings['signup']['after_registration_redirect']) ? $settings['signup']['after_registration_redirect'] :  get_option( 'tfhb_login_page_id' );

        // Login
        $settings['login']['login_page'] =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );

        $settings['login']['after_login_redirect_type'] =  isset($settings['login']['after_login_redirect_type']) && !empty($settings['login']['after_login_redirect_type']) ? $settings['login']['after_login_redirect_type'] :  'page';
        $settings['login']['after_login_redirect'] =  isset($settings['login']['after_login_redirect']) && !empty($settings['login']['after_login_redirect']) ? $settings['login']['after_login_redirect'] :  get_option( 'tfhb_dashboard_page_id' );
 

        

        // return response
        $data = array(
            'status' => true,
            'pages' => $page_list,
            'settings' => $settings
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

    /**
     * Get Frontend Dashboard user auth
     * 
     * @return void
     * 
     * */

     public function GetFdUserAuth(){
        $request =  json_decode(file_get_contents('php://input'), true);
        $userAuthData = isset($request['userAuthData']) ? $request['userAuthData'] : array();
        $user = wp_get_current_user();
        $user_id = $user->ID;
       
        if($userAuthData['id'] != $user_id){
            
            // return response
            $data = array(
                'status' => false,
                'message' => 'You are not authorized to access this endpoint.'
            );
    
            // log out 
            wp_logout();
            return rest_ensure_response($data);
            
        }
        $host = new Host();
        $host_data = $host->getHostById( $userAuthData['host_id'] ); 

        $data = array(
            'status' => true,
            'userAuth' => $host_data, 
        );
 
        return rest_ensure_response($data);

        // 
     }

     /**
      * Logout
      *
      * @return void
      * 
      * */

     public function LogoutFdUser(){
        $request =  json_decode(file_get_contents('php://input'), true);
        $userAuth = isset($request['userAuth']) ? $request['userAuth'] : array();
        
        $user = wp_get_current_user();
        $user_id = $user->ID;
        if($user_id != $userAuth['user_id']){ 
            // return response
            $data = array(
                'status' => false,
                'message' => 'You are not authorized to access this endpoint.'
            );
            return rest_ensure_response($data);
        }
        $frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
        $settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
        $login_page_id =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );
        $get_login_page_url = get_permalink( $login_page_id ); 
        wp_logout();  
        // return response
        $data = array(
            'status' => true,
            'redirect' => $get_login_page_url,
            'message' => 'You are logged out successfully.'
        );
        return rest_ensure_response($data);
     }

     /**
      * Update user Profile
      *
      * @return void
      * 
      * */

     public function UpdateFdUserProfile(){
        $request =  json_decode(file_get_contents('php://input'), true);
        $userAuth = isset($request['userAuth']) ? $request['userAuth'] : array();
        $userEmail = isset($userAuth['email']) ? $userAuth['email'] : '';
        
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $user_data = array();
        $user_data['ID'] = $user_id;
        if($user_id != $userAuth['user_id']){ 
            // return response
            $data = array(
                'status' => false,
                'message' => 'You are not authorized to access this endpoint.'
            );
            return rest_ensure_response($data);
        }
        // Check user email change or not
        if($user->user_email != $userEmail){
            // check user email already exist or not
            $user_by_email = get_user_by( 'email', $userEmail );
            if ( !empty($user_by_email) ) {
                // return response
                $data = array(
                    'status' => false,
                    'message' => 'User email already exist.'
                );
                return rest_ensure_response($data);
            }
            $user_data['user_email'] = $userEmail;
             
        }
        // update first name
        $user_data['first_name'] = isset($userAuth['first_name']) ? $userAuth['first_name'] : '';
        // update last name 
        $user_data['last_name'] = isset($userAuth['last_name']) ? $userAuth['last_name'] : '';
        // update display name 
        
        $host = new Host();
       
        $hostUpdate = $host->update( $userAuth);
        // tfhb_print_r($user_data);
        wp_update_user($user_data);
      
        // update user 
        // return response
        if($hostUpdate['status'] == true){
            $data = array(
                'status' => true,
                'message' => 'User profile updated successfully.'
            );
        }else{
            $data = array(
                'status' => false,

                'message' => 'User profile updated failed.'
            );
        } 
        return rest_ensure_response($data);

     }


      /**
      * Change Password
      *
      * @return void
      * 
      * */
     public function ChangeFdUserPassword(){
         
        $request =  json_decode(file_get_contents('php://input'), true);
        $userAuth = isset($request['userAuth']) ? $request['userAuth'] : array();
        $pass_data = isset($request['pass_data']) ? $request['pass_data'] : array();
        $old_password = isset($pass_data['old_password']) ? $pass_data['old_password'] : '';
        $new_password = isset($pass_data['new_password']) ? $pass_data['new_password'] : '';
        $confirm_password = isset($pass_data['confirm_password']) ? $pass_data['confirm_password'] : '';
        $user = wp_get_current_user();
        $user_id = $user->ID; 
        $response = array();
     
        if($user_id != $userAuth['user_id']){ 
            // return response
           $response['status'] = false;
           $response['message'] = 'You are not authorized to access this endpoint.';
            return rest_ensure_response($response);
        } 

 
        if ( !wp_check_password( $old_password, $user->user_pass, $user->ID ) ) {
            // return response 
            $response['status'] = false;
            $response['message'] = 'Old password is incorrect.';
            return rest_ensure_response($response);
        }
        if ( empty( $new_password ) ) {
            
            // return response
            $response['status'] = false;
            $response['message'] = esc_html__( 'Please enter your password', 'hydra-booking' );

        } elseif ( ! preg_match( '@[A-Z]@', $new_password ) ) { 
      
            $response['status'] = false;
            $response['message'] = esc_html__( 'Password must be include at least one uppercase letter', 'hydra-booking' );

        } elseif ( ! preg_match( '@[0-9]@', $new_password ) ) {
            $response['status'] = false;
            $response['message'] = esc_html__( 'Password must be include at least one number', 'hydra-booking' );
            
        } elseif ( ! preg_match( '@[^\w]@', $new_password ) ) {
            $response['status'] = false;
            $response['message'] = esc_html__( 'Password must be include at least one special character', 'hydra-booking' );
            
        } elseif ( strlen( $new_password ) < 8 ) {
            $response['status'] = false;
            $response['message'] = esc_html__( 'Password must be at least 8 characters', 'hydra-booking' );
            
        }
       
        if(isset($response['status']) && $response['status'] == false){
             return rest_ensure_response($response);
        }
         
        if($new_password != $confirm_password){
            // return response 
            $response['status'] = false;
            $response['message'] = 'Password and confirm password does not match.';
            return rest_ensure_response($response);
        } 
         
        wp_set_password($new_password, $user_id); 
        // return response 
        $response['status'] = true;
        $response['message'] = 'Password changed successfully.';
        
        return rest_ensure_response($response);
     }

 
    
}
