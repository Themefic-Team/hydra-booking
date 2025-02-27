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
            'subtitle'    => '',
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
        // meeting_host
        if('all'!= $atts['hosts'] && !empty($atts['hosts'])) {
            $query[] = array('host_id', 'IN', $atts['hosts']);
        } 
        $limit = $atts['limit'] ? $atts['limit'] : 10; 
        $meetings = $meeting->getAll( $query, $atts['sort_by'], $atts['order_by'], $limit );
        
        ob_start();
        ?>
        <div class="tfhb-meeting-list">
            <div class="tfhb-meeting-list__heading">
                <h2><?php echo esc_html( $atts['title'] );?></h2>
                <p><?php echo esc_html( $atts['subtitle'] );?></p>
            </div>
            <div class="tfhb-meeting-list__wrap">
                <?php 
                    if(count($meetings) > 0):
                        foreach ($meetings as $meeting) : 
                        // Get  all treams details based on trames id 
                        $meeting_category = $meeting->meeting_category; // meeting_category is a trems id 
                        $terms = get_term( $meeting_category ); 
                        $permalink = get_permalink($meeting->post_id);
                        // tfhb_print_r($terms);
                        $price = !empty($meeting->meeting_price) ? $meeting->meeting_price : esc_html(__('Free', 'hydra_booking'));
                ?>
                <div class="tfhb-meeting-list__wrap__items">
                    <div class="tfhb-meeting-list__wrap__items__content">
                        <h3>
                            <a href="<?php echo esc_url($permalink) ?>"><?php echo esc_html($meeting->title) ?></a>
                        </h3>
                        <p><?php echo esc_html($meeting->description) ?></p>
                    </div>
                    <div class="tfhb-meeting-list__wrap__items__tags"> 
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <?php echo esc_html($meeting->host_first_name) ?> <?php echo esc_html($meeting->host_last_name) ?>
                        </span> 
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tags"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/><path d="M9.586 5.586A2 2 0 0 0 8.172 5H3a1 1 0 0 0-1 1v5.172a2 2 0 0 0 .586 1.414L8.29 18.29a2.426 2.426 0 0 0 3.42 0l3.58-3.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="6.5" cy="9.5" r=".5" fill="currentColor"/></svg>
                            <?php echo esc_html($terms->name) ?>    
                        </span> 
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>    
                            <?php echo esc_html($meeting->duration) ?> minutes
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-presentation"><path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/></svg> 
                            <?php echo esc_html($meeting->meeting_type) ?>
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg> 
                            <?php echo esc_html($price) ?>
                        </span>
                    </div>
                    <!-- <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                        <a href="#" class="tfhb-btn boxed-btn">Book Now</a>
                    </div> -->
                </div>

                <?php endforeach; else: ?>
                    <div class="tfhb-meeting-list__wrap__no-found">
                        <p><?php esc_html_e('No meetings found.', 'hydra_booking')?></p>
                    </div>
                <?php endif;?>

            </div>
        </div>
        <?php 
        $html = ob_get_clean();
        // tfhb_print_r($meetings);
 
        return $html;  // return the generated HTML for the shortcode

    }
 
 

}