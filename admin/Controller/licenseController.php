<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


// Use HydraBooking 
use HydraBooking\License\HydraBooking; 
use HydraBooking\License\HydraBookingBase; 
 

class licenseController {
    private static $instance = null; // Holds the single instance
    private static $cached_result = null;

	// constaract
	public function __construct() {
        
	}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/settings/license',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetLicenseData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 
        register_rest_route(
			'hydra-booking/v1',
			'/settings/license/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'UpdateLicenseData' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 

        register_rest_route(
			'hydra-booking/v1',
			'/settings/license/deactivate',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeactiveLicense' ),
				'permission_callback' =>  array(new RouteController() , 'permission_callback'),
			)
		); 
	}


    public function GetLicenseData(){

        $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name =HydraBookingBase::get_lic_key_param($main_lic_key);
        $license_key=get_option($lic_key_name,"");
        $license_email=get_option("HydraBooking_lic_email","");

        $HydraBooking = new HydraBooking();
        $response = $HydraBooking->response_obj;

        if(empty($response)){
            wp_send_json_error( array( 
                'status' => false,
                'message' => 'Invalid License Key'
            ) );
        }

        wp_send_json_success( array( 
            'status' => true,
            'message' => 'License Data',
            'data' => $response,
            'license_key' => $license_key,
            'license_email' => $license_email,
        ) );

    }

    /**
     * Update License Data
     *
     * @param \WP_REST_Request $request
     * @return void
     */
    public function UpdateLicenseData(){
        $request = json_decode( file_get_contents( 'php://input' ), true );
        $license_key = !empty($request['license_key'])?sanitize_text_field(wp_unslash($request['license_key'])):"";
        $license_email = !empty($request['license_email'])?sanitize_email(wp_unslash($request['license_email'])):"";
        $main_lic_key="HydraBooking_lic_Key";

      
      
	    $lic_key_name = HydraBookingBase::get_lic_key_param($main_lic_key);  
        update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
        update_option($main_lic_key,$license_key) || add_option($main_lic_key,$license_key);
        update_option("HydraBooking_lic_email",$license_email) || add_option("HydraBooking_lic_email",$license_email);
        update_option('_site_transient_update_plugins',''); 
        $HydraBooking = new HydraBooking();  
     
        $response = $HydraBooking->response_obj;  
         
        if(empty($response)){ 
            delete_option($lic_key_name);
            delete_option("HydraBooking_lic_email");

            
            wp_send_json_error( array( 
                'status' => false,
                'message' => 'Invalid License Key'
            ) );
        }
	 
        wp_send_json_success( array( 
            'status' => true,
            'message' => 'License Updated Successfully',
            'data' => $response,
            'license_key' => $license_key,
            'license_email' => $license_email,
        ) );
   
        
    }

    /**
     * Deactive License
     *
     * @param \WP_REST_Request $request
     * @return void
     */

    public function DeactiveLicense(){
        $message="";
	    $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name =HydraBookingBase::get_lic_key_param($main_lic_key);
        if(HydraBookingBase::remove_license_key(TFHB_BASE_FILE,$message)){
            update_option($lic_key_name,"") || add_option($lic_key_name,"");
            update_option($main_lic_key,"") || add_option($main_lic_key,"");
            update_option('_site_transient_update_plugins','');
        }

        wp_send_json_success( array( 
            'status' => true,
            'message' => 'License Deactivated Successfully',
            'data' => array(
                'is_valid' => false,
            ),
        ) );
    }

    /**
     * Check License
     *
     * @return void
     */
    public function check_license() {
        // Ensure the license check runs only once per request
        if (self::$cached_result !== null) {
            return self::$cached_result; // Return cached result if already checked
        }
    
        // Initialize necessary classes
        $HydraBooking = new HydraBooking();
        
        // Default response structure
        self::$cached_result = [
            'is_valid' => false,
            'license_type' => false, // Default to 'free'
        ];
    
        if (!empty($HydraBooking->response_obj)) {
            self::$cached_result['is_valid'] = !empty($HydraBooking->response_obj->is_valid) ? $HydraBooking->response_obj->is_valid : false;
            
            // Determine license type based on 'license_title'
            if (!empty($HydraBooking->response_obj->license_title) && stripos($HydraBooking->response_obj->license_title, 'free') !== false) {
                self::$cached_result['license_type'] = false;
            } else {
                self::$cached_result['license_type'] = true;
            }
        }
    
        return self::$cached_result;
    }
    
    
}