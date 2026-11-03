<?php

use HydraBooking\Admin\Controller\BookingTime;
use HydraBooking\Services\Integrations\BookingBookmarks\BookingBookmarks;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use PHPUnit\Framework\TestCase;

/**
 * A booking stored as Asia/Taipei 2026-11-03 10:30-11:00 AM, made by an attendee in
 * America/Los_Angeles: for them it is 2026-11-02 6:30-7:00 PM.
 */
class NotificationAndExportTest extends TestCase {

	protected function setUp(): void {
		Tfhb_Test_Store::reset();
	}

	private function booking( $extra = array() ) {
		return (object) array_merge(
			array(
				'id'                     => 5,
				'title'                  => 'Consultation',
				'duration'               => 30,
				'meeting_dates'          => '2026-11-03',
				'start_time'             => '10:30 AM',
				'end_time'               => '11:00 AM',
				'availability_time_zone' => 'Asia/Taipei',
				'attendee_time_zone'     => 'America/Los_Angeles',
				'host_time_zone'         => 'Europe/London',
				'host_first_name'        => 'Mei',
				'host_last_name'         => 'Lin',
				'host_email'             => 'host@example.com',
				'email'                  => 'rachel@example.com',
				'attendee_name'          => 'Rachel',
				'buffer_time_before'     => 15,
				'buffer_time_after'      => 15,
				'meeting_interval'       => 10,
			),
			$extra
		);
	}

	public function test_attendee_tags_are_converted_and_host_tags_are_not() {
		$tags = BookingTime::time_tags( $this->booking(), 'attendee' );

		$this->assertSame( '2026-11-02', $tags['{{meeting.date}}'] );
		$this->assertSame( '06:30 PM-07:00 PM', $tags['{{meeting.time}}'] );
		$this->assertSame( '06:30 PM - 07:00 PM (America/Los_Angeles)', $tags['{{booking.full_start_end_attendee_timezone}}'] );
		$this->assertSame( '2026-11-03 10:30 AM - 11:00 AM (Asia/Taipei)', $tags['{{booking.full_start_end_host_timezone}}'] );
		$this->assertSame( '2026-11-03 10:30 AM (Asia/Taipei)', $tags['{{booking.start_date_time_for_host}}'] );
	}

	public function test_host_recipient_sees_host_time() {
		$tags = BookingTime::time_tags( $this->booking(), 'host' );

		$this->assertSame( '2026-11-03', $tags['{{meeting.date}}'] );
		$this->assertSame( '10:30 AM-11:00 AM', $tags['{{meeting.time}}'] );
		// The attendee tag is still the attendee's own time
		$this->assertSame( '06:30 PM (America/Los_Angeles)', $tags['{{booking.start_date_time_for_attendee}}'] );
	}

	public function test_recurring_dates_are_converted_per_occurrence() {
		$tags = BookingTime::time_tags( $this->booking( array( 'meeting_dates' => '2026-11-03,2026-11-10' ) ), 'attendee' );
		$this->assertSame( '2026-11-02, 2026-11-09', $tags['{{meeting.date}}'] );
	}

	public function test_missing_booking_timezone_falls_back_to_the_host_profile() {
		$tags = BookingTime::time_tags( $this->booking( array( 'availability_time_zone' => '' ) ), 'host' );
		$this->assertSame( '2026-11-03 10:30 AM (Europe/London)', $tags['{{booking.start_date_time_for_host}}'] );
	}

	public function test_webhook_payload_has_the_advertised_fields() {
		$payload = BookingTime::payload( $this->booking() );

		$this->assertSame( '2026-11-03', $payload->meeting_date );
		$this->assertSame( 'America/Los_Angeles', $payload->timezone );
		$this->assertSame( 'America/Los_Angeles', $payload->attendee_timezone );
		$this->assertSame( 'Asia/Taipei', $payload->host_timezone );
		$this->assertSame( 'Mei Lin', $payload->host_name );
		$this->assertSame( '2026-11-03T02:30:00Z', $payload->start_utc );
		$this->assertSame( '2026-11-03T03:00:00Z', $payload->end_utc );
		// Existing fields stay as they are
		$this->assertSame( '10:30 AM', $payload->start_time );
	}

	public function test_zoom_start_is_the_real_utc_moment() {
		// 07:00 Taipei is 23:00 UTC on the previous day
		$body = ( new ZoomServices() )->zoomMeetingBody( $this->booking( array( 'start_time' => '07:00 AM', 'end_time' => '07:30 AM' ) ) );

		$this->assertSame( '2026-11-02T23:00:00Z', $body[0]['start_time'] );
		$this->assertSame( 'Asia/Taipei', $body[0]['timezone'] );
		$this->assertSame( 30, $body[0]['duration'] );
	}

	public function test_zoom_body_has_one_meeting_per_occurrence() {
		$body = ( new ZoomServices() )->zoomMeetingBody( $this->booking( array( 'meeting_dates' => '2026-11-03, 2026-11-10' ) ) );
		$this->assertCount( 2, $body );
		$this->assertSame( '2026-11-10T02:30:00Z', $body[1]['start_time'] );
	}

	public function test_calendar_export_utc_and_empty_timezone() {
		$bookmarks = new BookingBookmarks();
		$this->assertSame( '20261103T023000Z', $bookmarks->formatToUTC( '2026-11-03', '10:30 AM', 'Asia/Taipei' ) );

		// No timezone stored: the site timezone, never an exception
		$GLOBALS['tfhb_test_site_tz'] = 'Asia/Tokyo';
		$this->assertSame( '20261103T013000Z', $bookmarks->formatToUTC( '2026-11-03', '10:30 AM', '' ) );
	}

	public function test_site_day_bounds() {
		$GLOBALS['tfhb_test_site_tz'] = 'America/Los_Angeles';

		$start = BookingTime::site_day( '2026-11-03' );
		$end   = BookingTime::site_day( '2026-11-03', true );
		$this->assertSame( '2026-11-03T08:00:00Z', BookingTime::iso_utc( $start ) );
		$this->assertSame( '2026-11-04T07:59:59Z', BookingTime::iso_utc( $end ) );

		// No database in tests: the session clock is UTC
		$this->assertSame( '2026-11-03 08:00:00', BookingTime::to_mysql_session( $start ) );
		$this->assertSame( '2026-11-03T08:00:00Z', BookingTime::iso_utc( BookingTime::from_mysql_session( '2026-11-03 08:00:00' ) ) );
	}
}
