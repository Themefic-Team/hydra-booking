<?php
defined('ABSPATH') || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

tfhb_get_header();

use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\AuthController;

// The attendee booking is looked up by hash in App::tfhb_single_page_template()
// before this template is loaded. Request data is never read here.
$attendeeBooking = isset($args['attendeeBooking']) ? $args['attendeeBooking'] : null;

if (empty($attendeeBooking) || ! is_object($attendeeBooking)) {
	echo '<div class="tfhb-single-meeting-section">';
	echo '<h2>' . esc_html__('Invalid Booking', 'hydra-booking') . '</h2>';
	echo '</div>';
	tfhb_get_footer();
	return;
}

// Do not open the calendar when the booking can no longer be rescheduled
// (canceled, already rescheduled, completed, inside the time limit, or already started).
$reschedule_block = tfhb_reschedule_block_message($attendeeBooking);
if ('' !== $reschedule_block) {
	echo '<div class="tfhb-single-meeting-section">';
	echo '<h2>' . esc_html($reschedule_block) . '</h2>';
	echo '</div>';
	tfhb_get_footer();
	return;
}

$meeting_id = isset($attendeeBooking->meeting_id) ? absint($attendeeBooking->meeting_id) : 0;
$hash       = isset($attendeeBooking->hash) ? preg_replace('/[^A-Za-z0-9]/', '', (string) $attendeeBooking->hash) : '';

$meeting = new Meeting();
$meeting = $meeting->get($meeting_id);
if (empty($meeting) || ! is_object($meeting)) {
	echo '<div class="tfhb-single-meeting-section">';
	echo '<h2>' . esc_html__('Invalid meeting ID.', 'hydra-booking') . '</h2>';
	echo '</div>';
	tfhb_get_footer();
	return;
}
$attendee_can_reschedule = isset($meeting->attendee_can_reschedule) ? $meeting->attendee_can_reschedule : false;

// Get Current User
$auth            = new AuthController();
$userRole        = (array) $auth->userRole();
$user_id         = $auth->userID();
$meeting_user_id = isset($meeting->user_id) ? $meeting->user_id : 0;

if ($attendee_can_reschedule == false) {
	// Allow administrators, or the host who owns this meeting.
	$is_admin      = current_user_can('manage_options');
	$is_owner_host = in_array('tfhb_host', $userRole, true) && (int) $user_id === (int) $meeting_user_id;

	if (! $is_admin && ! $is_owner_host) {
		echo '<div class="tfhb-single-meeting-section">';
		echo '<h2>' . esc_html__('You are not allowed to reschedule this meeting', 'hydra-booking') . '</h2>';
		echo '</div>';
		tfhb_get_footer();
		return;
	}
}

if (! empty($meeting_id) && '' !== $hash) {
	global $shortcode_tags;
	?>
	<div class="tfhb-single-meeting-section">
		<?php
		// Call the shortcode callback directly with validated attributes
		// instead of building a shortcode string from request data.
		if (isset($shortcode_tags['hydra_booking']) && is_callable($shortcode_tags['hydra_booking'])) {
			echo call_user_func( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output.
				$shortcode_tags['hydra_booking'],
				array(
					'id'   => $meeting_id,
					'hash' => $hash,
					'type' => 'reschedule',
				),
				'',
				'hydra_booking'
			);
		}
		?>
	</div>
	<?php
}

tfhb_get_footer();
