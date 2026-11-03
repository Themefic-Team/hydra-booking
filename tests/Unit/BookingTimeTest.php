<?php

use HydraBooking\Admin\Controller\BookingTime;
use PHPUnit\Framework\TestCase;

class BookingTimeTest extends TestCase {

	protected function setUp(): void {
		Tfhb_Test_Store::reset();
	}

	public function test_tz_falls_back_instead_of_throwing() {
		$this->assertSame( 'Asia/Taipei', BookingTime::tz( '', 'Not/AZone', 'Asia/Taipei' )->getName() );

		$GLOBALS['tfhb_test_site_tz'] = 'Europe/Berlin';
		$this->assertSame( 'Europe/Berlin', BookingTime::tz( '', null )->getName() );

		$GLOBALS['tfhb_test_site_tz'] = '';
		$this->assertSame( 'UTC', BookingTime::tz( '' )->getName() );
	}

	public function test_iana_name_maps_offsets() {
		$this->assertSame( 'Asia/Taipei', BookingTime::iana_name( 'Asia/Taipei' ) );
		$this->assertSame( 'UTC', BookingTime::iana_name( '' ) );
		$this->assertNotSame( 'UTC', BookingTime::iana_name( '+05:30' ) );
		$this->assertSame( 19800, ( new DateTime( '2027-01-01', new DateTimeZone( BookingTime::iana_name( '+05:30' ) ) ) )->getOffset() );
	}

	public function test_instant_parses_stored_formats() {
		$expected = '2026-11-03T02:30:00Z';
		$this->assertSame( $expected, BookingTime::iso_utc( BookingTime::instant( '2026-11-03', '10:30 AM', 'Asia/Taipei' ) ) );
		$this->assertSame( $expected, BookingTime::iso_utc( BookingTime::instant( '2026-11-03', '10:30', 'Asia/Taipei' ) ) );
		$this->assertSame( $expected, BookingTime::iso_utc( BookingTime::instant( '2026-11-03', '10:30:00', 'Asia/Taipei' ) ) );
		$this->assertSame( '2026-11-03T02:30:00Z', BookingTime::iso_utc( BookingTime::instant( '2026-11-02', '6:30 PM', 'America/Los_Angeles' ) ) );
		$this->assertNull( BookingTime::instant( '', '10:30 AM', 'UTC' ) );
	}

	public function test_from_iso_rejects_garbage() {
		$this->assertSame( '2026-11-02T18:30:00Z', BookingTime::iso_utc( BookingTime::from_iso( '2026-11-02T10:30:00-08:00' ) ) );
		$this->assertNull( BookingTime::from_iso( 'tomorrow' ) );
		$this->assertNull( BookingTime::from_iso( '' ) );
	}

	public function test_booking_instants_handles_recurring_and_midnight() {
		$instants = BookingTime::booking_instants(
			(object) array(
				'meeting_dates'          => '2027-01-19, 2027-01-12',
				'start_time'             => '11:30 PM',
				'end_time'               => '12:00 AM',
				'availability_time_zone' => 'Asia/Taipei',
			)
		);

		$this->assertCount( 2, $instants );
		// Sorted by start, and the end rolls over to the next day
		$this->assertSame( '2027-01-12T15:30:00Z', BookingTime::iso_utc( $instants[0]['start'] ) );
		$this->assertSame( '2027-01-12T16:00:00Z', BookingTime::iso_utc( $instants[0]['end'] ) );
		$this->assertSame( '2027-01-19T15:30:00Z', BookingTime::iso_utc( $instants[1]['start'] ) );
	}

	public function test_booking_instants_uses_fallback_timezone_when_missing() {
		$instants = BookingTime::booking_instants(
			(object) array(
				'meeting_dates'          => '2027-01-12',
				'start_time'             => '10:00 AM',
				'end_time'               => '11:00 AM',
				'availability_time_zone' => '',
			),
			'Asia/Taipei'
		);
		$this->assertSame( '2027-01-12T02:00:00Z', BookingTime::iso_utc( $instants[0]['start'] ) );
	}

	public function test_time_left_ignores_site_timezone() {
		// Meeting one hour from now in Taipei, while the site runs on a very different timezone
		$GLOBALS['tfhb_test_site_tz'] = 'America/Los_Angeles';
		$start   = BookingTime::to_tz( BookingTime::now()->modify( '+1 hour' ), 'Asia/Taipei' );
		$booking = (object) array(
			'meeting_dates'          => $start->format( 'Y-m-d' ),
			'start_time'             => $start->format( 'h:i A' ),
			'end_time'               => $start->modify( '+30 minutes' )->format( 'h:i A' ),
			'availability_time_zone' => 'Asia/Taipei',
		);

		$left = BookingTime::seconds_until_start( $booking );
		$this->assertGreaterThan( 3600 - 120, $left );
		$this->assertLessThanOrEqual( 3600, $left );
		$this->assertFalse( BookingTime::is_past( $booking ) );
	}

	public function test_recurring_time_left_uses_next_occurrence() {
		$past   = BookingTime::to_tz( BookingTime::now()->modify( '-7 days' ), 'UTC' );
		$future = BookingTime::to_tz( BookingTime::now()->modify( '+2 days' ), 'UTC' );
		$booking = (object) array(
			'meeting_dates'          => $past->format( 'Y-m-d' ) . ',' . $future->format( 'Y-m-d' ),
			'start_time'             => '12:00 PM',
			'end_time'               => '12:30 PM',
			'availability_time_zone' => 'UTC',
		);

		$this->assertGreaterThan( 86400, BookingTime::seconds_until_start( $booking ) );
		$this->assertFalse( BookingTime::is_past( $booking ) );
	}

	public function test_is_past_after_delay() {
		$ended = BookingTime::to_tz( BookingTime::now()->modify( '-90 minutes' ), 'Asia/Taipei' );
		$booking = (object) array(
			'meeting_dates'          => $ended->modify( '-30 minutes' )->format( 'Y-m-d' ),
			'start_time'             => $ended->modify( '-30 minutes' )->format( 'h:i A' ),
			'end_time'               => $ended->format( 'h:i A' ),
			'availability_time_zone' => 'Asia/Taipei',
		);

		$this->assertTrue( BookingTime::is_past( $booking, 60 * 60 ) );
		$this->assertFalse( BookingTime::is_past( $booking, 120 * 60 ) );
	}

	public function test_date_range() {
		$this->assertSame( array( '2026-12-31', '2027-01-01', '2027-01-02' ), BookingTime::date_range( '2027-01-01', '2027-01-01', 1 ) );
		$this->assertSame( array(), BookingTime::date_range( 'bad', '2027-01-01' ) );
	}
}
