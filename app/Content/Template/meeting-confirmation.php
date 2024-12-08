<?php
defined( 'ABSPATH' ) || exit;

#

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Meeting Calendar
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */

use HydraBooking\Admin\Controller\DateTimeController;

$data = isset( $args['attendeeBooking'] ) ? $args['attendeeBooking'] : array(); 

$date_time = new DateTimeController( 'UTC' );
$availability_data = $date_time->GetAvailabilityData($data);    
?> 
<div class="tfhb-meeting-confirmation" >
	<?php
		// Hook for before confirmation
		do_action( 'hydra_booking/before_meeting_confirmation' );

	?>
	<div class="tfhb-confirmation-seccess">
		<img src="<?php echo esc_url(THB_URL . 'assets/app/images/sucess.gif'); ?>" alt="Success"> 
		<h3><?php echo esc_html( __( 'Booking', 'hydra-booking' ) ); ?> <?php echo esc_html( $data->status ); ?></h3>
		<!-- <p>Please check your email for more information. Now you can reschedule or cancel booking from here.</p> -->
	</div>

	<div class="tfhb-meeting-hostinfo">
		<h4 class="tfhb-mb-16"><?php echo esc_html($data->title); ?></h4>
		<ul>
			<li class="tfhb-flexbox tfhb-gap-8">
				<div class="tfhb-icon">
					<img src="<?php echo esc_url(THB_URL . 'assets/app/images/user.svg'); ?>" alt="User">
				</div>
				<?php echo ! empty( $data->first_name ) ? '' . esc_html( $data->first_name ) . '  ' . esc_html( $data->last_name ) . '' : ''; ?>
				<span>Host</span>
			</li> 
			<?php if ( ! empty( $data->start_time ) ) { ?>
			<li class="tfhb-flexbox tfhb-gap-8">
				<div class="tfhb-icon">
					<img src="<?php echo esc_url(THB_URL . 'assets/app/images/Meeting.svg'); ?>" alt="User">
				</div>
				<!--date stored in this format  2024-05-24  9:00pm-9:45pm, Saturday, April 25 -->
				<?php 
					$meeting_dates = explode( ',', $data->meeting_dates ); 

					$start_time = $data->start_time;
					$end_time = $data->end_time;
					$date = $meeting_dates[0]; 
				
					$start_time = $date_time->convert_time_based_on_timezone( $date, $start_time, $data->availability_time_zone,  $data->attendee_time_zone, '' );
					
					$end_time   = $date_time->convert_time_based_on_timezone($meeting_date, $end_time, $data->availability_time_zone, $data->attendee_time_zone , '' ); 
					$date_strings = '';
				foreach ( $meeting_dates as $key => $date ) {
					$formate_date = $date_time->convert_time_based_on_timezone( $date, $data->start_time, $data->availability_time_zone, $data->attendee_time_zone , '' );
					$date_strings .= $formate_date->format('l, F j');
					$date_strings .= '| ';
				}
				$date_strings = rtrim( $date_strings, '| ' );

					echo ! empty( $start_time->format('h:i A') ) ? '' . esc_html( $start_time->format('h:i A') ) . ' - ' . esc_html( $start_time->format('h:i A') ) . ', ' . esc_html( $date_strings ) . '' : ''
				?>
			</li>
			<?php } ?>
			<?php if ( ! empty( $data->attendee_time_zone ) ) { ?>
			<li class="tfhb-flexbox tfhb-gap-8">
				<div class="tfhb-icon">
					<img src="<?php echo esc_url(THB_URL . 'assets/app/images/globe.svg'); ?>" alt="User">
				</div>
				<!-- Asia/Dhaka  -->
				<?php echo ! empty( $data->attendee_time_zone ) ? '' . esc_html( $data->attendee_time_zone ) . '' : ''; ?>

			</li>
			<?php } ?>
			<!-- Meeting location -->
			<?php
			
			if ( ! empty( $data->meeting_locations ) ) {
				$meeting_location = json_decode( $data->meeting_locations, true );
				 
				foreach ( $meeting_location as $key => $location ) {

					
					$address =  isset($location['address']) && !empty($location['address']) ? $location['address'] : $key;
					 
					if($key == 'zoom'){
						$address = isset($location['address']['link']) ? $location['address']['link'] : $key;
					}
					if($location['location'] == 'Attendee Phone Number' || $location['location'] == 'Organizer Phone Number'){
						$icon = '<img src="'.esc_url(THB_URL . 'assets/app/images/phone.svg').'" alt="Phone">';
					 }elseif($location['location'] == 'zoom'){
						$icon =  '<img src="'.esc_url(THB_URL . 'assets/app/images/zoom.png').'" alt="Zoom">';
					 }elseif($location['location'] == 'meet'){
						$icon =  '<img src="'.esc_url(THB_URL . 'assets/app/images/google-meet small.png').'" alt="meet">';
					 }else{
						$icon =  '<img src="'.esc_url(THB_URL . 'assets/app/images/location.svg').'" alt="Location">';
					 }
					echo '<li class="tfhb-flexbox tfhb-gap-8">
                                <div class="tfhb-icon">
                                    '.$icon.'  
                                </div> 
                                ' . 
								esc_html( $address ) . '
                            </li>';
				}
			}
			?>
		</ul>
	</div>

 

	<div class="tfhb-meeting-confirmation-action tfhb-flexbox tfhb-gap-16">
		
		<?php

		if ( true == $data->attendee_can_cancel ) {
			$cancel = add_query_arg(
				array(
					'hydra-booking' => 'booking',
					'hash'          => $data->hash,
					'meetingId'    => $data->meeting_id,
					'type'          => 'cancel',
				),
				home_url()
			);
			echo '<a href="' . esc_attr( $cancel ) . '">Cancel booking</a>';
		}
		if ( true == $data->attendee_can_reschedule ) {

			$reschedule_url = add_query_arg(
				array(
					'hydra-booking' => 'booking',
					'hash'          => $data->hash,
					'meetingId'    => $data->meeting_id,
					'type'          => 'reschedule',
				),
				home_url()
			);

			echo '<a href="' . esc_url( $reschedule_url ) . '">Reschedule</a>';
		}
		?>
	</div>

	<?php
		// Hook for After confirmation
		do_action( 'hydra_booking/after_meeting_confirmation' );

	?>

</div>
