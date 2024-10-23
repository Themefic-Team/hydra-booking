<?php  
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


/**
 *  Include Files
 *  Require all the files in the includes folder
 */

    // Helper Functions file
    if ( file_exists( THB_PATH . '/includes/helper/helper-functions.php' ) ) {

        require_once THB_PATH . '/includes/helper/helper-functions.php';
    }

/**
 *  Class Include
 *  Require Hooks files
 */

    // Activation Hooks
    new HydraBooking\Hooks\ActivationHooks();

    // Deactivation Hooks
    new HydraBooking\Hooks\DeactivationHooks();

    // Mail Hooks
    new HydraBooking\Hooks\MailHooks();
    
    // Filter Hooks
    new HydraBooking\Hooks\FilterHooks();

/**
 *  Class Include
 *  Load Integrations Class
 */

    // Web Hooks
    new HydraBooking\Services\Integrations\WebHook\WebHook();

    // Integrations
    new HydraBooking\Services\Integrations\MailChimp\MailChimp();
    new HydraBooking\Services\Integrations\Zoho\Zoho();
    new HydraBooking\Services\Integrations\FluentCRM\FluentCRM();
 
 

    
?>