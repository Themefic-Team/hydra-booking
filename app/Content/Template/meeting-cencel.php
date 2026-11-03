<?php

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Meeting Cencel
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */
tfhb_get_header();

global $wp_query;


 
$data    = isset( $args['attendeeBooking'] ) ? $args['attendeeBooking'] : array(); 
$helper = new \HydraBooking\Admin\Controller\Helper();
$display_date_format = $helper->get_date_format_from_settings( 'l, F j' );

$attendee_can_cancel = ! empty( $data->attendee_can_cancel );
$auth = new \HydraBooking\Admin\Controller\AuthController();
$userRole = $auth->userRole();
$user_id = $auth->userID();
$is_privileged = in_array( 'administrator', $userRole, true ) || ( in_array( 'tfhb_host', $userRole, true ) && isset($data->user_id) && $user_id == $data->user_id );

if ( ! $attendee_can_cancel && ! $is_privileged ) {
	echo '<div class="tfhb-single-meeting-section tfhb-p-16">';
	echo '<h2>' . esc_html__( 'You are not allowed to cancel this booking.', 'hydra-booking' ) . '</h2>';
	echo '</div>';
	tfhb_get_footer();
	return;
}

?>
<div class=" tfhb-booking-cencel tfhb-meeting-<?php echo esc_attr( $data->meeting_id ); ?>" data-calendar="<?php echo esc_attr( $data->meeting_id ); ?>">
	<form method="post" action="" class="tfhb-meeting-cencel-form ajax-submit"  enctype="multipart/form-data">
		<div class="tfhb-meeting-card tfhb-p-16">
			<div class="tfhb-meeting-confirmation">  

				<div class="tfhb-confirmation-seccess"> 
					<h3><?php echo esc_html( __( 'Your meeting has been', 'hydra-booking' ) ); ?> <?php echo esc_html( $data->status ); ?></h3>
					<p><?php echo esc_html( __( 'Please check your email for more information. Now you can reschedule or cancel booking from here.', 'hydra-booking' ) ); ?></p>
				</div>

				<div class="tfhb-meeting-hostinfo"> 
					<ul>
						<li class="tfhb-flexbox tfhb-gap-8">
							<div class="tfhb-icon">
								<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/user.svg'); ?>" alt="User">
							</div>
							<?php echo ! empty( $data->host_first_name ) ? '' . esc_html( $data->host_first_name ) . '  ' . esc_html( $data->host_last_name ) . '' : ''; ?>
							<span><?php echo esc_html( __( 'Host', 'hydra-booking' ) ); ?></span>
						</li>
						<li class="tfhb-flexbox tfhb-gap-8">
							<div class="tfhb-icon">
								<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/Meeting.svg'); ?>" alt="Meeting">
							</div>
							<!--date stored in this format  2024-05-24  9:00pm-9:45pm, Saturday, April 25 -->
							<?php

							// Stored in the host's timezone, shown in the attendee's
							$general_settings = get_option( '_tfhb_general_settings' );
							$time_display     = isset( $general_settings['time_format'] ) && '24' === $general_settings['time_format'] ? 'H:i' : 'h:i A';
							$viewer_time_zone = \HydraBooking\Admin\Controller\BookingTime::tz( $data->attendee_time_zone, $data->availability_time_zone );
							$occurrences      = \HydraBooking\Admin\Controller\BookingTime::booking_instants( $data );

							$date_strings = array();
							foreach ( $occurrences as $occurrence ) {
								$date_strings[] = wp_date( $display_date_format, $occurrence['start']->getTimestamp(), $viewer_time_zone );
							}

							if ( ! empty( $occurrences ) ) {
								$first_occurrence = $occurrences[0];
								echo esc_html( \HydraBooking\Admin\Controller\BookingTime::format( $first_occurrence['start'], $viewer_time_zone, $time_display ) ) . ' - ' . esc_html( \HydraBooking\Admin\Controller\BookingTime::format( $first_occurrence['end'], $viewer_time_zone, $time_display ) ) . ' ' . esc_html( implode( ', ', $date_strings ) );
							}
							?>
						</li>
						<li class="tfhb-flexbox tfhb-gap-8">
							<div class="tfhb-icon">
								<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/location.svg'); ?>" alt="location">
							</div>
							<!-- Asia/Dhaka  -->
							<?php echo ! empty( $data->attendee_time_zone ) ? '' . esc_html( $data->attendee_time_zone ) . '' : ''; ?>

						</li>

						<!-- Meeting location -->
						<?php
						if ( ! empty( $data->meeting_locations ) ) {
							$locations = json_decode( $data->meeting_locations );
							foreach ( $locations as $key => $location ) {
								if ( empty( $location->location ) ) {
									continue;
								}
								echo '<li class="tfhb-flexbox tfhb-gap-8">
                                            <div class="tfhb-icon">
                                                <img src="' . esc_url( TFHB_URL . 'assets/app/images/location.svg' ) . '" alt="location">   
                                            </div> 
                                            ' . esc_html( $location->location ) . '
                                        </li>';
							}
						}
						?>
					</ul>
				</div> 
				<?php if ( $data->status == 'canceled' ) : ?>
					<div class="tfhb-notice notice-error" > 
						<?php 
							$cancelled_text = 'the host';
							if (is_numeric($data->cancelled_by)) {
								$user = get_userdata($data->cancelled_by);
								if ($user) {
									$cancelled_text = $user->display_name;
								}
							} elseif (!empty($data->cancelled_by)) {
								if ($data->cancelled_by === 'attendee' || $data->cancelled_by === 'host') {
									$cancelled_text = 'the ' . $data->cancelled_by;
								} else {
									$cancelled_text = $data->cancelled_by;
								}
							}
						?>
						<span><?php echo esc_html__( 'This meeting has been cancelled by ', 'hydra-booking' ) . esc_attr($cancelled_text) . '.'; ?></span>
					</div>
				<?php else : ?>
				<div class="hidden-field"> 
					<input type="hidden" id="attendee_hash" name="attendee_hash" value="<?php echo esc_attr($data->hash); ?>">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'tfhb_cancel_' . $data->hash ) ); ?>">
				</div>  
				<div class="tfhb-forms" >
					<div  class="tfhb-single-form">
						<br>
						<label for="attendee_name"> <?php echo esc_html__( 'Reason for Cancellation', 'hydra-booking' ); ?> </label>
						<br>

						<textarea name="reason" required id="reason"></textarea>
						<input type="hidden" name="hash" value="<?php echo esc_attr($data->hash) ?>">
						<br>
						<br>
					</div> 

					<div class="tfhb-confirmation-button tfhb-flexbox tfhb-gap-8">
				   
						<button class="tfhb-flexbox tfhb-gap-8 tfhb-booking-submit">
							<?php echo esc_attr( 'Cancel Booking' ); ?>
							<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/arrow-right.svg'); ?>" alt="arrow"> 
						</button>
					</div>

				</div>
				<?php endif; ?>
				
			</div>
		</div>
	</form>
	
</div>
<?php



tfhb_get_footer();

