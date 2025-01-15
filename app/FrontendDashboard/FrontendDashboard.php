<?php
namespace HydraBooking\FdDashboard;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\Enqueue;

/**
 * Frontend Dashboard Class
 * 
 * @author Sydur Rahman
 */
class FrontendDashboard {

    /**
     * Constructor
     * 
     * @author Sydur Rahman 
     */
    public function __construct() { 

        // Define Constants
        $this->define_constants();

        // Load Frontend Dashboard 
        add_filter( 'theme_page_templates', array( $this, 'set_page_template' ), 10, 4 );

        add_filter( 'page_template', array( $this, 'load_page_templates' ) );

        

       
    }

    /**
     * Define Constants
     * 
     * @return void
     * @since 1.0.0
     * @author Sydur Rahman
     */

    public function define_constants() {
        define( 'TFHB_FD_DASHBOARD_PATH', plugin_dir_path( __FILE__ ) ); 
        define( 'TFHB_FD_DASHBOARD_URL', plugin_dir_url( __FILE__ ) );
        define( 'TFHB_FD_DASHBOARD_TEMPLATE_PATH', TFHB_FD_DASHBOARD_PATH . 'Template/' );

    }

    /**
     * Load Frontend Dashboard
     * 
     * @return void
     * @author Sydur Rahman
     */
    public function set_page_template($template){
        $template['frontend-dashboard.php'] = 'Hydra - Dashbaord';
        return $template;
    }

    /**
     * Load Frontend Dashboard
     * 
     * @return void
     * @author Sydur Rahman
     */

    public function load_page_templates($template){ 
        $page_template = get_page_template_slug();
        // load frontend dashboard template
        if ( $page_template == 'frontend-dashboard.php' ) {
            new Enqueue();
            $template = TFHB_FD_DASHBOARD_TEMPLATE_PATH . 'frontend-dashboard.php';
        }
        return $template;
    }

}