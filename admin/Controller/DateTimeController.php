<?php
namespace HydraBooking\Admin\Controller;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

	use HydraBooking\DB\Booking;
	use HydraBooking\DB\Meeting;
	use HydraBooking\DB\Host;


class DateTimeController extends \DateTimeZone {
	public function TimeZone() {
		$time_zone_data = $this->listIdentifiers();
		$time_zone      = array();
		// make array in this format { value: 'New York', name: 'NY' },

		foreach ( $time_zone_data as $key => $value ) {
			$time_zone[] = array(
				'value' => $value,
				'name'  => $value,
			);
		}
		return $time_zone;
	}

	// Get Current Time Zone
	public function getCurrentTimeZone() {
		//  get current time zone based on current location
		// Get the user's current local time zone
		// Get the current time zone
		$currentTimeZone = new \DateTimeZone(date_default_timezone_get());

		// Get the time zone name
		$timeZoneName = $currentTimeZone->getName();

		// Output the time zone name
		echo $timeZoneName;
	}

	public function convert_time_based_on_timezone( $meeting_date, $time, $time_zone, $selected_time_zone, $time_format ) {
		  
		
		// Combine meeting date and start time into a single string
		$date_str = $meeting_date . ' ' . $time;

		// Create DateTime object for the given time in the availability timezone
		$date = new \DateTime($date_str, new \DateTimeZone($selected_time_zone));
	
		// Get the timezone offset for both timezones
		$attendeeTimeZoneObj = new \DateTimeZone($time_zone);
		$attendeeOffset = $attendeeTimeZoneObj->getOffset(new \DateTime()); // Offset in seconds for attendee timezone
		$availabilityOffset = $date->getOffset(); // Offset in seconds for availability timezone
	
		// Calculate the time difference in seconds
		$offsetDifference = $attendeeOffset - $availabilityOffset;
	
		// Modify the DateTime object by the offset difference
		$date->modify(($offsetDifference > 0 ? "+" : "-") . abs($offsetDifference) . " seconds");
		// $time = $date->format('H:i'); 
		//  time format meanse 12hr or 24hr
		if ( $time_format == '12' ) {
			// Return in this format: 2024-09-24 11:30 AM - 12:00 PM (America/New_York)
			 $date->format('h:i A');
		} else {
			// Return in this format: 2024-09-24 11:30 - 12:00 (America/New_York)
			 $date->format('H:i ');
		}

	
		return $date;
	}
	 
	public function convert_full_start_end_host_timezone_with_date( $start_time, $end_time, $time_zone, $selected_time_zone,  $selected_date, $type ) {
	


		$_tfhb_general_settings = get_option( '_tfhb_general_settings' );
		$time_format 		 = isset( $_tfhb_general_settings['time_format'] ) ? $_tfhb_general_settings['time_format'] : '12';
		$start_time = new \DateTime( $selected_date . ' ' . $start_time, new \DateTimeZone( $time_zone ) );
		$end_time   = new \DateTime( $selected_date . ' ' . $end_time, new \DateTimeZone( $time_zone ) );
		 
		$start_time->setTimezone( new \DateTimeZone( $selected_time_zone ) );
		
		$end_time->setTimezone( new \DateTimeZone( $selected_time_zone ) );

		// Prepare formatted output
		if ( $time_format == '12' ) {
			// Return in this format: 2024-09-24 11:30 AM - 12:00 PM (America/New_York)
			$start_format = $start_time->format('Y-m-d g:i A');
			$end_format = $end_time->format('g:i A');
		} else {
			// Return in this format: 2024-09-24 11:30 - 12:00 (America/New_York)
			$start_format = $start_time->format('Y-m-d H:i');
			$end_format = $end_time->format('H:i');
		}
	
		if($type == 'start'){
			return $start_format . ' (' . $selected_time_zone . ')';
		}elseif($type == 'full'){

			// Return formatted time range with the time zone name ex: 2024-09-24  | 11:30 AM - 12:00 PM (America/New_York)
			return $start_format . ' - ' . $end_format . ' (' . $selected_time_zone . ')';
		}
	}
	public function getAvailableTimeData( $meeting_id, $selected_date, $selected_time_zone, $selected_time_format ) {

		$meeting_id = isset( $meeting_id ) ? $meeting_id : '';

		$selected_date = isset( $selected_date ) ? sanitize_text_field( $selected_date ) : '';

		$selected_time_zone = isset( $selected_time_zone ) ? sanitize_text_field( $selected_time_zone ) : 'UTC';

		$selected_time_format = isset( $selected_time_format ) ? sanitize_text_field( $selected_time_format ) : '12';

		$meeting      = new Meeting();
		
		$MeetingsData = $meeting->get( $meeting_id );
		
		// Meeting Information
		$data = get_post_meta( $MeetingsData->post_id, '__tfhb_meeting_opt', true );
	 

		$meeting_type      = isset( $data['meeting_type'] ) ? $data['meeting_type'] : 'one-to-single';
		$max_book_per_slot = isset( $data['max_book_per_slot'] ) ? $data['max_book_per_slot'] : 1;

		$availability_data = $this->GetAvailabilityData($MeetingsData); 

		// Meeting time zone
		$time_zone = isset( $availability_data['time_zone'] ) && !empty($availability_data['time_zone']) ? $availability_data['time_zone'] : 'UTC';

		// Disable Unavailable days
		$time_slots = isset( $availability_data['time_slots'] ) ? $availability_data['time_slots'] : array();

		// Disable Unavailable days
		$date_slots = isset( $availability_data['date_slots'] ) ? $availability_data['date_slots'] : array();

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		if($duration == 'custom'){
			$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		}  
		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		// Disable Dates

		// Get All Booking Data.
		$booking = new Booking();

		$bookings = $booking->getByMeetingIdDates( $meeting_id, $selected_date ); 

		$disabled_times = array();
		foreach ( $bookings as $booking ) {
			$meeting_dates = $booking->meeting_dates;
			$start_time    = $booking->start_time;
			$end_time      = $booking->end_time;
			// $time_zone     = $booking->attendee_time_zone;

			if ( 'one-to-group' == $meeting_type ) {

				// Get All Booking Data.
				$booking       = new Booking();
				 
				$where = array(
					array('meeting_id', '=', $meeting_id),
					array('meeting_dates', '=', $meeting_dates),
					array('start_time', '=', $start_time),
					array('end_time', '=', $end_time),
				);
				$check_booking = $booking->getBookingWithAttendees( 
					$where,
					1,
					'DESC' 
				); 
				$attendees = json_decode($check_booking->attendees); 

				if ( count( $attendees ) != $max_book_per_slot ) {
					continue;
				}
			} 
			$meeting_dates_array = explode( ',', $meeting_dates );
			// get the first date
			$meeting_date = $meeting_dates_array[0]; 

			$start_time = $this->convert_time_based_on_timezone( $meeting_date, $start_time, $time_zone, $selected_time_zone, $selected_time_format );
			$end_time   = $this->convert_time_based_on_timezone( $meeting_date, $end_time, $time_zone, $selected_time_zone, $selected_time_format );

			$disabled_times[] = array(
				'start' => $start_time,
				'end'   => $end_time,
			);

		}
	 
		// Time Slot
		$time_slots_data = array();
		// get Selected Date day
		$selected_day = gmdate( 'l', strtotime( $selected_date ) );

		// only get selected day time slot in single array using array finter
		$selected_available = array();
		foreach ( $time_slots as $single ) {
			if ( $single['day'] == $selected_day ) {
				$selected_available = $single;
			}
		}

		$times = $selected_available ? $selected_available['times'] : array();

		foreach ( $date_slots as $key => $value ) {

			// in strings 2024-06-12, 2024-06-13, 2024-06-14 has date
			if ( strpos( $value['date'], $selected_date ) !== false && $value['available'] == false ) {

				$times = $value['times'];
			}
		}

		foreach ( $times as $key => $value ) {

			$start_time = $value['start'];

			$end_time = $value['end'];

			$generatedSlots = $this->generateTimeSlots( $start_time, $end_time, $duration, $meeting_interval, $buffer_time_before, $buffer_time_after, $selected_date, $selected_time_format, $time_zone, $selected_time_zone );

			$time_slots_data = array_merge( $time_slots_data, $generatedSlots );

		}

		// if date time_slots_data any array match with disabled_times any array  exists remove that array without loop form
		$time_slots_data = array_filter(
			$time_slots_data,
			function ( $slot ) use ( $disabled_times ) {
				return ! in_array( $slot, $disabled_times );
			}
		);
		// return $time_slots_data;
		$data = array();
		foreach ( $time_slots_data as $key => $value ) {
			// array filter and remove if date already exists
			$data[] = $value;

		}
		return $data;
	}


	public function generateTimeSlots( $startTime, $endTime, $duration, $meeting_interval, $buffer_time_before, $buffer_time_after, $selected_date, $time_format, $time_zone, $selected_time_zone ) {
		$timeSlots = array();

		$skip_before_meeting_start = 100; // Example value, replace with your actual setting
		$start                     = new \DateTime( $selected_date . ' ' . $startTime, new \DateTimeZone( $selected_time_zone ) );
		$end                       = new \DateTime( $selected_date . ' ' . $endTime, new \DateTimeZone( $selected_time_zone ) );
		$current                   = clone $start;
		$before                    = clone $start;
		$after                     = clone $start;

		$diff             = $duration * 60; // Convert to seconds
		$before_diff      = $buffer_time_before * 60; // Convert to seconds
		$after_diff       = $buffer_time_after * 60; // Convert to seconds
		$meeting_interval = $meeting_interval * 60; // Convert to seconds
		$total_diff       = $diff + $before_diff + $after_diff;

		while ( $current < $end ) {

			$start_time = $this->formatTime( $current, $time_format, $time_zone );
			$end_time   = $this->formatTime( ( clone $current )->modify( "+$total_diff seconds" ), $time_format, $time_zone );

			// if current time is passed then skip skip_before_meeting_start
			$current_minus_skip = ( clone $current )->modify( "-$skip_before_meeting_start minutes" );
			if ( new \DateTime( 'now', new \DateTimeZone( $time_zone ) ) > $current_minus_skip ) {
				$current->modify( "+$total_diff seconds" )->modify( "+$meeting_interval seconds" );

				continue;
			}

			$timeSlots[] = array(
				'start' => $start_time,
				'end'   => $end_time,
			);

			$current->modify( "+$total_diff seconds" )->modify( "+$meeting_interval seconds" );

		}

		return $timeSlots;
	}

	public function formatTime( $dateTime, $timeFormat, $timeZone ) {
		
		$dateTime->setTimezone( new \DateTimeZone( $timeZone ) );

		$format = '';
		if ( $timeFormat === '12' ) {
			$format = 'h:i A'; // 12-hour format with AM/PM
		} else {
			$format = 'H:i'; // 24-hour format
		}

		return $dateTime->format( $format );
	}

	/* convert date time format */
	public function convertDateTimeFormat( $date, $currentFormat, $newFormat ) {
		$date = \DateTime::createFromFormat( $currentFormat, $date );
		return $date->format( $newFormat );
	}

	//Get availability_data
	public function GetAvailabilityData ($MeetingsData){
		
		$availability_data = isset( $MeetingsData->availability_custom ) ? $MeetingsData->availability_custom : array();
		if ( isset( $MeetingsData->availability_type ) && 'settings' === $MeetingsData->availability_type ) {

			$host = new Host();
			$host = $host->getHostById( $MeetingsData->host_id );

			
			$_tfhb_availability_settings = get_user_meta( $host->user_id, '_tfhb_host', true );
			if ( isset($_tfhb_availability_settings['availability']) && in_array( $MeetingsData->availability_id, array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				$availability_data = $_tfhb_availability_settings['availability'][ $MeetingsData->availability_id ];
			} 
		}  

		$availability_data = !is_array($availability_data) ? json_decode($availability_data, true) : $availability_data;

		return $availability_data;
	}
}
