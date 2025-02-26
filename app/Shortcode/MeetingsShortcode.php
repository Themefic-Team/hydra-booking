<?php
namespace HydraBooking\App\Shortcode;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace 
use HydraBooking\DB\Meeting;
/**
 * Signup Class
 * 
 * @author Sydur Rahman
 */
class MeetingsShortcode {

    /**
     * Constructor
     * 
     * @author Sydur Rahman 
     */
    public function __construct() { 
       
        // Add Shortcode
        add_shortcode( 'tfhb_meetings', array( $this, 'tfhb_meetings_callback' ) );
 
    }

    /*
     * Meeting Shortcode Callback
     * 
     * /
     * @author Sydur Rahman 
     * */

    public function tfhb_meetings_callback($atts) { 
        $atts = shortcode_atts([
            'title'        => '',
            'sub_title'    => '',
            'category'     => 'all', // Comma-separated category IDs
            'hosts'        => 'all', // Comma-separated host IDs
            'sort_by'      => 'id',
            'order_by'     => 'DESC',
            'limit' => '10',
        ], $atts, 'tfhb_meetings');

        // tfhb_print_r($atts);
        // get all meetings based on given parameters
        $meeting = new Meeting();
        $query = array( );
        // meeting_category
        if('all' != $atts['category'] && !empty($atts['category'])) {
            $query[] = array('meeting_category', 'IN', $atts['category']);
        }
        //  tfhb_print_r($atts['category']);

        // meeting_host
        if('all'!= $atts['hosts'] && !empty($atts['hosts'])) {
            $query[] = array('host_id', 'IN', $atts['hosts']);
        } 
        $meetings = $meeting->getAll( $query, $atts['sort_by'], $atts['order_by'], $atts['limit'] );
     
        // tfhb_print_r($atts);
        ob_start();
        ?>
        <div class="tfhb-meeting-list">
            <div class="tfhb-booking-list__heading">
                <h2><?php echo esc_html( $atts['title'] );?></h2>
                <p><?php echo esc_html( $atts['sub_title'] );?></p>
            </div>
            <div class="tfhb-booking-list__wrap">
                <?php foreach ($meetings as $meeting) :
                    
                    
                ?>
                <div class="tfhb-booking-list__wrap__items">
                    <div class="tfhb-booking-list__wrap__items__content">
                        <h3><?php echo esc_html($meeting->title) ?></h3>
                        <p><?php echo esc_html($meeting->description) ?></p>
                    </div>
                    <div class="tfhb-booking-list__wrap__items__tags"> 
                        <span><b>Host: </b> <?php echo esc_html($meeting->host_id) ?></span> 
                        <span><b>Category: </b> <?php echo esc_html($meeting->meeting_category) ?></span> 
                        <span><b>Duration: </b> <?php echo esc_html($meeting->duration) ?><</span>
                        <span><b>Meeting Type: </b> <?php echo esc_html($meeting->availability_type) ?></span>
                        <span><b>Price: </b> <?php echo esc_html($meeting->meeting_price) ?></span>
                    </div>
                    <div class="tfhb-booking-list__wrap__items__actions">
                        <a href="#" class="tfhb-btn tfhb-btn--primary">Book Now</a>
                    </div>
                </div>

                <?php endforeach;?>

            </div>
        </div>
        <?php 
        $html = ob_get_clean();
        // tfhb_print_r($meetings);
 
        return $html;  // return the generated HTML for the shortcode

    }
 
 

}