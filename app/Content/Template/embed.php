 
<?php 
defined( 'ABSPATH' ) || exit;

/**
 * Embed Template
 * 
 * @link       https://hydrabooking.com
 */

 wp_head();
/*
 <div id="hydra-booking-embad-container" data-meeting-id="64"></div> 
<script src="http://hydra-booking.local/wp-content/plugins/hydra-booking/assets/app/js/widget.js"></script> <!-- Load your widget -->
<script>
    // Initialize the widget
    Widget.init({
        containerId: 'hydra-booking-embad-container', // The div where the widget will be rendered
        meetingId: 64, // The ID for the meeting
    });
 
</script>
 
*/
 global $wp_query;
if ( isset( $wp_query->query_vars['hydra-booking'] ) ) {
	$meeting_id = intval( $wp_query->query_vars['meetingId'] );
	$type       = esc_attr( $wp_query->query_vars['type'] );
	if ( ! empty( $meeting_id ) ) {
		?>
		<div class="tfhb-single-meeting-section tfhb-meeting-embed-section">
			<?php echo do_shortcode( '[hydra_booking id=' . $meeting_id . ']' ); ?>
		</div>
		<?php
	}
}
wp_footer();
?>
 