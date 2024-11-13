<?php
defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Meeting Info
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */

$meeting      = isset( $args['meeting'] ) ? $args['meeting'] : array();
$host         = isset( $args['host'] ) ? $args['host'] : array(); 
$time_zone    = isset( $args['time_zone'] ) ? $args['time_zone'] : array();
$booking_data = isset( $args['booking_data'] ) ? $args['booking_data'] : array(); 
// Stripe Public api Key
$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
$stripePublicKey            = ! empty( $_tfhb_integration_settings['stripe']['public_key'] ) ? $_tfhb_integration_settings['stripe']['public_key'] : '';
$paypalPublicKey            = ! empty( $_tfhb_integration_settings['paypal']['client_id'] ) ? $_tfhb_integration_settings['paypal']['client_id'] : '';

$_tfhb_host_integration_settings = get_user_meta( $host['user_id'], '_tfhb_host_integration_settings' );
$stripePublicKey                 = ! empty( $_tfhb_host_integration_settings['stripe']['public_key'] ) ? $_tfhb_host_integration_settings['stripe']['public_key'] : $stripePublicKey;
$paypalPublicKey                 = ! empty( $_tfhb_host_integration_settings['paypal']['client_id'] ) ? $_tfhb_host_integration_settings['paypal']['client_id'] : $paypalPublicKey;

// display short 


?> 

<div class="tfhb-meeting-info">
	<div class="hidden-field">
		<input type="hidden" id="meeting_id" name="meeting_id" value="<?php echo esc_attr($meeting['id']); ?>">
		<input type="hidden" id="host_id" name="host_id" value="<?php echo esc_attr($host['id']); ?>"> 
		<input type="hidden" id="meeting_duration" name="meeting_dates" value="<?php echo esc_attr($meeting['duration']); ?>">
		<input type="hidden" id="meeting_dates" name="meeting_dates" value="">
		<input type="hidden" id="meeting_time_start" name="meeting_time_start" value="">
		<input type="hidden" id="meeting_time_end" name="meeting_time_end" value="">
		<input type="hidden" id="payment_method" name="payment_method" value="<?php echo esc_attr($meeting['payment_method']); ?>">
		<input type="hidden" id="payment_amount" name="payment_amount" value="<?php echo ! empty( $meeting['meeting_price'] ) ? esc_attr($meeting['meeting_price']) : ''; ?>">
		<input type="hidden" id="payment_currency" name="payment_currency" value="<?php echo ! empty( $meeting['payment_currency'] ) ? esc_attr($meeting['payment_currency']) : esc_attr('USD'); ?>">
		<input type="hidden" id="stpublic_key" name="public_key" value="<?php echo esc_attr($stripePublicKey); ?>">
		<input type="hidden" id="paypal_public_key" name="public_key" value="<?php echo esc_attr($paypalPublicKey); ?>">
		<?php
		if ( ! empty( $booking_data ) ) {
			echo '<input type="hidden" id="booking_hash" name="booking_hash" value="' . esc_attr( $booking_data->hash ) . '">';
			echo '<input type="hidden" id="action_type" name="action_type" value="' . esc_attr( 'reschedule' ) . '">';
		}
		?>
	</div>  
	<div class="tfhb-host-info" style="background-image: url(<?php echo esc_attr(THB_URL . 'assets/app/images/meeting-cover.png'); ?>) ;">
		<div class="tfhb-host-profile tfhb-flexbox tfhb-gap-8">
			<?php echo ! empty( $host['avatar'] ) ? '<img src="' . esc_url( $host['avatar'] ) . '" alt="">' : '<img src="' . THB_URL.'assets/images/avator.png' . '" alt="">'; ?>
			
			<div class="tfhb-host-name">
				<?php echo ! empty( $host['first_name'] ) ? '<h3>' . esc_html( $host['first_name'] ) . '  ' . esc_html( $host['last_name'] ) . '</h3>' : ''; ?>
				<?php echo ! empty( $host['about'] ) ? '<p>' . esc_html( $host['about'] ) . '</p>' : ''; ?>
				
			</div>
		</div>
	</div>

	<div class="tfhb-meeting-details">
		<?php echo ! empty( $meeting['title'] ) ? '<h2>' . esc_html(  wp_strip_all_tags(tfhb_character_limit_callback($meeting['title'], 60)) ) . '</h2>' : ''; ?> 

		<div class="tfhb-short-description">
            <?php 
            if(strlen($meeting['description']) > 100 ){
                echo wp_kses_post(wp_strip_all_tags(tfhb_character_limit_callback($meeting['description'], 100))) . '<span class="tfhb-see-description">See more</span>';
            }else{
                echo ! empty( $meeting['description'] ) ? '<p>' . wp_kses_post( $meeting['description'] ) . '</p>' : ''; 
            }
            ?>
        </div>
        <div class="tfhb-full-description">
            <?php 
                echo ! empty( $meeting['description'] ) ? '<p>' . wp_kses_post( $meeting['description'] ) . '</p>' : '';
                echo '<span class="tfhb-see-less-description">See less</span>';
            ?>
        </div>
		

		<ul>
			<li class="tfhb-flexbox tfhb-gap-8">
				<div class="tfhb-icon">
					<!-- <img src="<?php echo esc_url(THB_URL . 'assets/app/images/clock.svg'); ?>" alt="Clock"> -->

					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clip-path="url(#clip0_1911_10275)">
						<path d="M8.00065 14.6666C11.6825 14.6666 14.6673 11.6819 14.6673 7.99998C14.6673 4.31808 11.6825 1.33331 8.00065 1.33331C4.31875 1.33331 1.33398 4.31808 1.33398 7.99998C1.33398 11.6819 4.31875 14.6666 8.00065 14.6666Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8 4V8L10.6667 9.33333" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</g>
					<defs>
						<clipPath id="clip0_1911_10275">
						<rect width="16" height="16" fill="white"/>
						</clipPath>
					</defs>
					</svg>
				</div>
				<?php echo ! empty( $meeting['duration'] ) ? esc_html( $meeting['duration'] . ' minutes' ) : '0 minutes'; ?>
				
			</li>
			<?php
			if ( ! empty( $meeting['meeting_locations'] ) ) {
				foreach ( $meeting['meeting_locations'] as $key => $location ) { 

					$location_value = $location['address'];
					if($location['location'] == 'zoom'){
						$location_value = 'Zoom';
					}elseif($location['location'] == 'meet'){
						$location_value = 'Google Meet';
					}elseif($location['location'] == 'Attendee Phone Number'){
						$location_value = 'Attendee Phone Number';
					}elseif($location['location'] == 'Organizer Phone Number'){
						$location_value = 'Organizer Phone Number';
					
					}elseif($location['location'] == 'In Person (Attendee Address)'){
						$location_value = 'In Person (Attendee Address) ';
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
                                <input type="hidden" id="meeting_locations[' . esc_attr($key) . '][location]" name="meeting_locations[' . esc_attr($key) . '][location]" value="' . esc_attr( $location['location'] ) . '">
                                <input type="hidden" id="meeting_locations[' . esc_attr($key) . '][address]" name="meeting_locations[' . esc_attr($key) . '][address]" value="' . esc_attr( $location['address'] ) . '">
                                <div class="tfhb-icon">'.$icon.'</div> 
                                ' . esc_html( $location_value ) . '
                            </li>';
				}
			}

			?>
			<?php
			if ( ! empty( $meeting['payment_status'] ) && true == $meeting['payment_status'] ) {


				$price = ! empty( $meeting['meeting_price'] ) ? $meeting['meeting_price'] : 'Free';
				echo '<li class="tfhb-flexbox tfhb-gap-8">
                            <input type="hidden" id="meeting_price" name="meeting_price" value="' . esc_attr( $price ) . '">
                            <div class="tfhb-icon">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.334 4H2.66732C1.93094 4 1.33398 4.59695 1.33398 5.33333V10.6667C1.33398 11.403 1.93094 12 2.66732 12H13.334C14.0704 12 14.6673 11.403 14.6673 10.6667V5.33333C14.6673 4.59695 14.0704 4 13.334 4Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.99935 9.33335C8.73573 9.33335 9.33268 8.7364 9.33268 8.00002C9.33268 7.26364 8.73573 6.66669 7.99935 6.66669C7.26297 6.66669 6.66602 7.26364 6.66602 8.00002C6.66602 8.7364 7.26297 9.33335 7.99935 9.33335Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 8H4.00667M12 8H12.0067" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div> 
                            ' . esc_html( $price ) . '
                        </li>';
			}
			?>
			<?php
			if ( ! empty( $meeting['recurring_status'] ) && true == $meeting['recurring_status'] ) {

			// 	echo '<li class="tfhb-flexbox tfhb-gap-8">
			//     <input type="hidden" id="recurring_maximum" name="recurring_maximum" value="' . esc_attr( $meeting['recurring_maximum'] ) . '">
			//     <div class="tfhb-icon">  
			//         <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			//         <path d="M2 8C2 6.4087 2.63214 4.88258 3.75736 3.75736C4.88258 2.63214 6.4087 2 8 2C9.67737 2.00631 11.2874 2.66082 12.4933 3.82667L14 5.33333" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			//         <path d="M13.9993 2V5.33333H10.666" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			//         <path d="M14 8C14 9.5913 13.3679 11.1174 12.2426 12.2426C11.1174 13.3679 9.5913 14 8 14C6.32263 13.9937 4.71265 13.3392 3.50667 12.1733L2 10.6667" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			//         <path d="M5.33333 10.6667H2V14" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			//         </svg>
			//     </div> 
			//     <div>
			//         Recurring every  <span>' . esc_attr( $meeting['recurring_repeat'][0]['limit'] ) . '</span> ' . esc_attr( $meeting['recurring_repeat'][0]['times'] ) . ' for  <span>' . esc_attr( $meeting['recurring_maximum'] ) . '</span>  Bookings
			//     </div>
			// </li>';
			}
			?>
 
		</ul> 
		<div class="tfhb-timezone ">   
			
			<select class="tfhb-time-zone-select" name="attendee_time_zone" id="attendee_time_zone_<?php echo esc_attr($meeting['id']) ?>">
				<?php
				if ( ! empty( $time_zone ) ) {

					$selected_timezone = !empty($meeting['availability_custom']['time_zone'])  ? $meeting['availability_custom']['time_zone'] : 'UTC';
					if ( 'settings' === $meeting['availability_type'] ) {
						$_tfhb_availability_settings = get_user_meta( $meeting['host_id'], '_tfhb_host', true );
						 
						if($_tfhb_availability_settings['availability_type'] === 'settings' ){
							// Get Global Settings
							$_tfhb_availability_settings_global = get_option( '_tfhb_availability_settings' ); 
							
							$key = array_search( $meeting['availability_id'], array_column( $_tfhb_availability_settings_global, 'id' ) );

							if ( in_array( $key, array_keys( $_tfhb_availability_settings_global ) ) ) {
								$selected_timezone = $_tfhb_availability_settings_global[ $key ]['time_zone']; 
							}

					
						}elseif ( in_array( $meeting['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
							$selected_timezone = $_tfhb_availability_settings['availability'][ $meeting['availability_id'] ]['time_zone'];
						}
						 
					}
					$selected_timezone = isset( $booking_data->attendee_time_zone ) ? $booking_data->attendee_time_zone : $selected_timezone;

					foreach ( $time_zone as $key => $zone ) {
						$selected = ( $zone['value'] == $selected_timezone ) ? 'selected' : '';
						echo '<option value="' . esc_attr( $zone['value'] ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $zone['name'] ) . '</option>';
					}
				}

				?>
			</select>
			<div class="tfhb-timezone-icon ">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g clip-path="url(#clip0_1911_10296)">
					<path d="M9.99935 18.3334C14.6017 18.3334 18.3327 14.6024 18.3327 10C18.3327 5.39765 14.6017 1.66669 9.99935 1.66669C5.39698 1.66669 1.66602 5.39765 1.66602 10C1.66602 14.6024 5.39698 18.3334 9.99935 18.3334Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M9.99935 1.66669C7.85954 3.91348 6.66602 6.8973 6.66602 10C6.66602 13.1027 7.85954 16.0866 9.99935 18.3334C12.1392 16.0866 13.3327 13.1027 13.3327 10C13.3327 6.8973 12.1392 3.91348 9.99935 1.66669Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M1.66602 10H18.3327" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</g>
				<defs>
					<clipPath id="clip0_1911_10296">
					<rect width="20" height="20" fill="white"/>
					</clipPath>
				</defs>
				</svg>
			</div>
		</div>
	</div>

	<?php
		// Hooks After Meeting Info
		do_action( 'hydra_booking/after_meeting_info' );

	?>
</div>

