<?php
namespace HydraBooking\Hooks;

use HydraBooking\Admin\Controller\AuthController;

class FilterHooks {

	public function __construct() {

                // Restrict unverified user
                add_filter( 'authenticate', array( new AuthController(), 'tfhb_restrict_unverified_user' ), 10, 3 );
		
                // Redirect Host after login if woocommerce is active
                add_filter('woocommerce_prevent_admin_access', array( new AuthController(),  'tfhb_woocommerce_prevent_admin_access' ), 10, 3);
      }


       
            
 
}
