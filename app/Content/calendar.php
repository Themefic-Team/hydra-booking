<?php
defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Meeting Times
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */ 
$atts          = isset( $args['atts'] ) ? $args['atts'] : array();
$meeting          = isset( $args['meeting'] ) ? $args['meeting'] : array();
$host 		   = isset( $args['host'] ) ? $args['host'] : array();
$time_zone = isset( $args['time_zone'] ) ? $args['time_zone'] : array();  
$booking_data = isset( $args['booking_data'] ) ? $args['booking_data'] : array();  
$general_settings = isset( $args['general_settings'] ) ? $args['general_settings'] : array();  
$calendar_id  = isset( $meeting['id'] ) ? $meeting['id'] : 0;

	// Before Load the Calendar.
    do_action( 'hydra_booking/before_meeting_render', $meeting );

    ?>
    <div class="tfhb-meeting-box tfhb-meeting-<?php echo esc_attr( $calendar_id ); ?>" data-calendar="<?php echo esc_attr( $calendar_id ); ?>">
        
        <?php

        if ( ! empty( $booking_data ) && 'reschedule' == $atts['type'] ) {
            // Load Reschedule Template
            // You are rescheduling the booking: 3:15 pm - 3:30 pm, May 27, 2024 (Asia/Dhaka)
            echo '<div class="tfhb-reschedule-box">';
            echo '<p>' . esc_html__( 'You are rescheduling the booking:', 'hydra-booking' ) . ' ' . esc_html( $booking_data->start_time ) . ' - ' . esc_html( $booking_data->end_time ) . ', ';

            $meeting_dates = explode( ',', $booking_data->meeting_dates );
   

            $date_strings = '';
                foreach ( $meeting_dates as $key => $date ) {

                    $date_strings .= gmdate( 'l, F j', strtotime( $date ) );
                    $date_strings .= '| ';
                } 
                $date_strings = rtrim( $date_strings, '| ' );

                echo  $date_strings;
              
             echo ' (' . esc_html( $booking_data->attendee_time_zone ) . ')</p>';
            echo '</div>';

        }

        ?>
        <!-- <form  method="post" action="" class="tfhb-meeting-form ajax-submit"  enctype="multipart/form-data"> -->
            <div class="tfhb-meeting-card">
            
                    <?php
                    // Load Meeting Info Template
                    load_template(
                        TFHB_PATH . '/app/Content/Template/meeting-info.php',
                        false,
                        array(
                            'meeting'      => $meeting,
                            'host'         => $host,
                            'time_zone'    => $time_zone,
                            'booking_data' => $booking_data,
                        )
                    );
                    ?>
                    <div class="tfhb-calander-times tfhb-flexbox"> 
                        <?php
                        // Load Meeting Calendar Template
                        load_template( TFHB_PATH . '/app/Content/Template/meeting-calendar.php', false, $meeting );

                        // Load Meeting Time Template
                        load_template(
                            TFHB_PATH . '/app/Content/Template/meeting-times.php',
                            false,
                            array(
                                'meeting'          => $meeting,
                                'host'         => $host,
                                'general_settings' => $general_settings,
                            )
                        );
                        ?>
                    </div>
                    <?php
                    // Load Meeting Form Template
                    load_template(
                        TFHB_PATH . '/app/Content/Template/meeting-form.php',
                        false,
                        array(
                            'meeting'      => $meeting,
                            'booking_data' => $booking_data,
                        )
                    );
                    ?>
            </div>

        <!-- </form> -->
            
    </div>
    <?php

    // After Load the Calendar.
    do_action( 'hydra_booking/after_meeting_render', $meeting );
