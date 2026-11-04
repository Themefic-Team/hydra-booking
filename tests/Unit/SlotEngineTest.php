<?php

use HydraBooking\Admin\Controller\BookingTime;
use HydraBooking\Admin\Controller\DateTimeController;
use PHPUnit\Framework\TestCase;

/**
 * Host: Asia/Taipei, 09:00-17:00 every day, 60 minute meetings.
 * Dates are in January 2027: Los Angeles is on PST (UTC-8), Taipei is UTC+8.
 */
class SlotEngineTest extends TestCase {

	const MEETING = 1;

	protected function setUp(): void {
		Tfhb_Test_Store::reset();
		$this->givenMeeting( 'Asia/Taipei' );
	}

	private function givenMeeting( $time_zone, $meta = array(), $time_slots = null, $date_slots = array() ) {
		$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
		if ( null === $time_slots ) {
			$time_slots = array();
			foreach ( $days as $day ) {
				$time_slots[] = array(
					'day'    => $day,
					'status' => true,
					'times'  => array( array( 'start' => '09:00', 'end' => '17:00' ) ),
				);
			}
		}

		Tfhb_Test_Store::$meetings[ self::MEETING ] = (object) array(
			'id'                  => self::MEETING,
			'host_id'             => 7,
			'post_id'             => 100,
			'availability_type'   => 'custom',
			'availability_custom' => array(
				'time_zone'  => $time_zone,
				'time_slots' => $time_slots,
				'date_slots' => $date_slots,
			),
		);

		$GLOBALS['tfhb_test_post_meta'][100]['__tfhb_meeting_opt'] = array_merge(
			array(
				'meeting_type' => 'one-to-one',
				'duration'     => 60,
			),
			$meta
		);
	}

	private function givenBooking( $dates, $start, $end, $time_zone = 'Asia/Taipei', $extra = array() ) {
		Tfhb_Test_Store::$bookings[] = (object) array_merge(
			array(
				'id'                     => count( Tfhb_Test_Store::$bookings ) + 1,
				'meeting_id'             => self::MEETING,
				'host_id'                => 7,
				'meeting_dates'          => $dates,
				'start_time'             => $start,
				'end_time'               => $end,
				'availability_time_zone' => $time_zone,
				'status'                 => 'confirmed',
				'attendees'              => array( (object) array( 'id' => 1 ) ),
			),
			$extra
		);
	}

	private function slotsByDate( $dates, $time_zone = 'America/Los_Angeles' ) {
		return ( new DateTimeController( 'UTC' ) )->getAvailableSlotsByDate( self::MEETING, $dates, $time_zone, '12' );
	}

	private function starts( $slots ) {
		return array_map(
			function ( $slot ) {
				return $slot['start'];
			},
			$slots
		);
	}

	/** Rachel's report: a Taipei morning slot belongs to the previous evening in Los Angeles. */
	public function test_cross_day_slots_are_grouped_under_the_visitor_date() {
		$grouped = $this->slotsByDate( array( '2027-01-11', '2027-01-12' ) );

		foreach ( $grouped as $date => $slots ) {
			foreach ( $slots as $slot ) {
				$this->assertSame( $date, $slot['date'] );
			}
		}

		// Taipei Jan 12 10:00 AM is Los Angeles Jan 11 6:00 PM
		$match = array_values(
			array_filter(
				$grouped['2027-01-11'],
				function ( $slot ) {
					return '06:00 PM' === $slot['start'];
				}
			)
		);
		$this->assertCount( 1, $match );
		$this->assertSame( '2027-01-12', $match[0]['host_date'] );
		$this->assertSame( '10:00 AM', $match[0]['host_start'] );
		$this->assertSame( '2027-01-12T02:00:00Z', $match[0]['start_utc'] );
	}

	public function test_visitor_day_combines_two_host_days_in_order() {
		// LA Jan 12: Taipei Jan 12 4:00 PM (00:00 LA) through Taipei Jan 13 slots (from 5:00 PM LA)
		$slots = $this->slotsByDate( array( '2027-01-12' ) )['2027-01-12'];
		$this->assertSame( '12:00 AM', $slots[0]['start'] );
		$this->assertSame( '2027-01-12', $slots[0]['host_date'] );
		$this->assertSame( '05:00 PM', $slots[1]['start'] );
		$this->assertSame( '2027-01-13', $slots[1]['host_date'] );
		$this->assertSame( '11:00 PM', end( $slots )['start'] );
	}

	public function test_same_timezone_matches_previous_behaviour() {
		$slots = $this->slotsByDate( array( '2027-01-12' ), 'Asia/Taipei' )['2027-01-12'];
		$this->assertSame(
			array( '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM' ),
			$this->starts( $slots )
		);
	}

	public function test_submitted_start_maps_back_to_the_host_slot() {
		$controller = new DateTimeController( 'UTC' );

		// New payload: absolute start from the slot
		$slot = $controller->findAvailableSlot( self::MEETING, BookingTime::from_iso( '2027-01-12T02:00:00Z' ), 'America/Los_Angeles' );
		$this->assertSame( '2027-01-12', $slot['host_date'] );
		$this->assertSame( '10:00 AM', $slot['host_start'] );
		$this->assertSame( '11:00 AM', $slot['host_end'] );

		// Legacy payload: visitor date + visitor time
		$legacy = $controller->findAvailableSlot( self::MEETING, BookingTime::instant( '2027-01-11', '06:00 PM', 'America/Los_Angeles' ), 'America/Los_Angeles' );
		$this->assertSame( $slot['start_utc'], $legacy['start_utc'] );

		// A time that isn't a slot is rejected
		$this->assertNull( $controller->findAvailableSlot( self::MEETING, BookingTime::from_iso( '2027-01-12T02:30:00Z' ), 'America/Los_Angeles' ) );
	}

	public function test_booking_on_the_neighbouring_host_date_blocks_the_slot() {
		$this->givenBooking( '2027-01-12', '10:00 AM', '11:00 AM' );

		$slots = $this->slotsByDate( array( '2027-01-11' ) )['2027-01-11'];
		$this->assertNotContains( '06:00 PM', $this->starts( $slots ) );
		$this->assertContains( '07:00 PM', $this->starts( $slots ) );
	}

	public function test_recurring_occurrence_blocks_the_slot() {
		$this->givenBooking( '2027-01-05, 2027-01-12', '10:00 AM', '11:00 AM' );

		$slots = $this->slotsByDate( array( '2027-01-11' ) )['2027-01-11'];
		$this->assertNotContains( '06:00 PM', $this->starts( $slots ) );
	}

	public function test_booking_keeps_the_timezone_it_was_made_in() {
		// Taken while the host was on Asia/Dhaka: 08:00 Dhaka is 10:00 Taipei
		$this->givenBooking( '2027-01-12', '08:00 AM', '09:00 AM', 'Asia/Dhaka' );

		$slots = $this->slotsByDate( array( '2027-01-12' ), 'Asia/Taipei' )['2027-01-12'];
		$this->assertNotContains( '10:00 AM', $this->starts( $slots ) );
		$this->assertContains( '09:00 AM', $this->starts( $slots ) );
	}

	public function test_slot_ending_at_visitor_midnight_is_blocked() {
		// Taipei 3:00-4:00 PM is 11:00 PM-12:00 AM in Los Angeles
		$this->givenBooking( '2027-01-12', '03:00 PM', '04:00 PM' );

		$slots = $this->slotsByDate( array( '2027-01-11' ) )['2027-01-11'];
		$this->assertNotContains( '11:00 PM', $this->starts( $slots ) );
	}

	public function test_canceled_bookings_do_not_block_but_holds_do() {
		$this->givenBooking( '2027-01-12', '10:00 AM', '11:00 AM', 'Asia/Taipei', array( 'status' => 'canceled' ) );
		$this->givenBooking( '2027-01-12', '11:00 AM', '12:00 PM', 'Asia/Taipei', array( 'status' => 'hold' ) );

		$slots = $this->slotsByDate( array( '2027-01-12' ), 'Asia/Taipei' )['2027-01-12'];
		$this->assertContains( '10:00 AM', $this->starts( $slots ) );
		$this->assertNotContains( '11:00 AM', $this->starts( $slots ) );
	}

	public function test_partly_booked_group_slot_stays_open_until_full() {
		$this->givenMeeting( 'Asia/Taipei', array( 'meeting_type' => 'one-to-group', 'max_book_per_slot' => 2 ) );
		$this->givenBooking( '2027-01-12', '10:00 AM', '11:00 AM' );
		$this->givenBooking( '2027-01-12', '11:00 AM', '12:00 PM', 'Asia/Taipei', array( 'attendees' => array( (object) array(), (object) array() ) ) );

		$slots = $this->slotsByDate( array( '2027-01-12' ), 'Asia/Taipei' )['2027-01-12'];
		$this->assertContains( '10:00 AM', $this->starts( $slots ) );
		$this->assertNotContains( '11:00 AM', $this->starts( $slots ) );
	}

	public function test_buffers_widen_existing_bookings() {
		$this->givenMeeting( 'Asia/Taipei', array( 'buffer_time_before' => 15, 'buffer_time_after' => 15, 'meeting_interval' => 60 ) );
		$this->givenBooking( '2027-01-12', '12:00 PM', '01:00 PM' );

		$slots = $this->slotsByDate( array( '2027-01-12' ), 'Asia/Taipei' )['2027-01-12'];
		$this->assertNotContains( '11:00 AM', $this->starts( $slots ) );
		$this->assertNotContains( '01:00 PM', $this->starts( $slots ) );
		$this->assertContains( '10:00 AM', $this->starts( $slots ) );
	}

	public function test_closed_weekday_has_no_slots() {
		$time_slots = array(
			array( 'day' => 'Tuesday', 'status' => false, 'times' => array( array( 'start' => '09:00', 'end' => '17:00' ) ) ),
			array( 'day' => 'Wednesday', 'status' => true, 'times' => array( array( 'start' => '09:00', 'end' => '17:00' ) ) ),
		);
		$this->givenMeeting( 'Asia/Taipei', array(), $time_slots );

		$controller = new DateTimeController( 'UTC' );
		$this->assertSame( array(), $controller->getAvailableTimeData( self::MEETING, '2027-01-12', 'Asia/Taipei', '12' ) ); // Tuesday
		$this->assertNotEmpty( $controller->getAvailableTimeData( self::MEETING, '2027-01-13', 'Asia/Taipei', '12' ) );
	}

	public function test_date_overrides() {
		$this->givenMeeting(
			'Asia/Taipei',
			array(),
			null,
			array(
				array( 'date' => '2027-01-12', 'available' => 1, 'times' => array() ),
				array( 'date' => '2027-01-13, 2027-01-14', 'available' => false, 'times' => array( array( 'start' => '13:00', 'end' => '15:00' ) ) ),
			)
		);

		$controller = new DateTimeController( 'UTC' );
		$this->assertSame( array(), $controller->getAvailableTimeData( self::MEETING, '2027-01-12', 'Asia/Taipei', '12' ) );
		$this->assertSame( array( '01:00 PM', '02:00 PM' ), $this->starts( $controller->getAvailableTimeData( self::MEETING, '2027-01-14', 'Asia/Taipei', '12' ) ) );
	}

	public function test_booking_range_is_in_host_dates() {
		$this->givenMeeting(
			'Asia/Taipei',
			array(
				'availability_range_type' => 'range',
				'availability_range'      => array( 'start' => '2027-01-12', 'end' => '2027-01-12' ),
			)
		);

		$grouped = $this->slotsByDate( array( '2027-01-11', '2027-01-12' ) );
		// Only the host's Jan 12 is bookable: LA Jan 11 evening plus the first LA Jan 12 slot
		$this->assertCount( 7, $grouped['2027-01-11'] );
		$this->assertSame( array( '12:00 AM' ), $this->starts( $grouped['2027-01-12'] ) );
	}

	public function test_frequency_limit_closes_only_that_period() {
		$this->givenMeeting( 'Asia/Taipei', array( 'booking_frequency' => array( array( 'limit' => 1, 'times' => 'days' ) ) ) );
		$this->givenBooking( '2027-01-12', '09:00 AM', '10:00 AM' );

		$controller = new DateTimeController( 'UTC' );
		$this->assertSame( array(), $controller->getAvailableTimeData( self::MEETING, '2027-01-12', 'Asia/Taipei', '12' ) );
		$this->assertNotEmpty( $controller->getAvailableTimeData( self::MEETING, '2027-01-13', 'Asia/Taipei', '12' ) );
	}

	public function test_frequency_periods() {
		$this->assertSame( array( 'start' => '2027-01-11', 'end' => '2027-01-17' ), DateTimeController::getFrequencyPeriod( '2027-01-17', 'weeks' ) );
		$this->assertSame( array( 'start' => '2027-02-01', 'end' => '2027-02-28' ), DateTimeController::getFrequencyPeriod( '2027-02-10', 'Month' ) );
		$this->assertSame( array( 'start' => '2027-02-10', 'end' => '2027-02-10' ), DateTimeController::getFrequencyPeriod( '2027-02-10', 'days' ) );
	}

	public function test_daylight_saving_change_in_host_timezone() {
		// New York switches to EDT on 2027-03-14
		$this->givenMeeting( 'America/New_York' );
		$controller = new DateTimeController( 'UTC' );

		$before = $controller->getAvailableTimeData( self::MEETING, '2027-03-13', 'UTC', '24' );
		$after  = $controller->getAvailableTimeData( self::MEETING, '2027-03-14', 'UTC', '24' );

		$this->assertSame( '2027-03-13T14:00:00Z', $before[0]['start_utc'] );
		$this->assertSame( '2027-03-14T13:00:00Z', $after[0]['start_utc'] );
		$this->assertSame( '13:00', $after[0]['start'] );
	}

	public function test_empty_visitor_timezone_does_not_throw() {
		$slots = ( new DateTimeController( 'UTC' ) )->getAvailableTimeData( self::MEETING, '2027-01-12', '', '12' );
		$this->assertSame( '2027-01-12T01:00:00Z', $slots[0]['start_utc'] );
	}
}
