<?php

namespace HydraBooking\Admin\Controller;

// exit
if (! defined('ABSPATH')) {
	exit;
}

use HydraBooking\DB\Booking;
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Host;


class DateTimeController extends \DateTimeZone
{
	public function TimeZone()
	{
		// Include backward-compatible identifiers so browser-reported aliases
		// (for example Europe/Kiev or Asia/Calcutta) can still be matched.
		$time_zone_data = $this->listIdentifiers(\DateTimeZone::ALL_WITH_BC);
		$time_zone      = array();
		// make array in this format { value: 'New York', name: 'NY' },

		foreach ($time_zone_data as $key => $value) {
			$time_zone[] = array(
				'value' => $value,
				'name'  => $value,
			);
		}
		return $time_zone;
	}

	// Get Current Time Zone
	public function getCurrentTimeZone()
	{
		//  get current time zone based on current location
		// Get the user's current local time zone
		// Get the current time zone
		$currentTimeZone = new \DateTimeZone(date_default_timezone_get());

		// Get the time zone name
		$timeZoneName = $currentTimeZone->getName();

		// Output the time zone name
		echo esc_html($timeZoneName);
	}

	public function convert_time_based_on_timezone($meeting_date,  $time, $time_zone, $selected_time_zone, $time_format = '')
	{
		$time_zone          = ! empty( $time_zone ) ? $time_zone : 'UTC';
		$selected_time_zone = ! empty( $selected_time_zone ) ? $selected_time_zone : 'UTC';

		try {
			$tz_from = new \DateTimeZone($time_zone);
		} catch (\Exception $e) {
			$tz_from = new \DateTimeZone('UTC');
		}

		try {
			$tz_to = new \DateTimeZone($selected_time_zone);
		} catch (\Exception $e) {
			$tz_to = new \DateTimeZone('UTC');
		}

		try {
			if (! empty($meeting_date)) {
				$dt = new \DateTime($meeting_date . ' ' . $time, $tz_from);
			} else {
				$dt = new \DateTime($time, $tz_from);
			}
			$dt->setTimezone($tz_to);
		} catch (\Exception $e) {
			$dt = new \DateTime('now', $tz_to);
		}

		if ($time_format == '12') {
			return $dt->format('h:i A');
		}
		if ($time_format == '24') {
			return $dt->format('H:i');
		}

		return $dt;
	}




	public function convert_full_start_end_host_timezone_with_date($start_time, $end_time, $time_zone, $selected_time_zone,  $selected_date, $type)
	{



		$_tfhb_general_settings = get_option('_tfhb_general_settings');
		$time_format 		 = isset($_tfhb_general_settings['time_format']) ? $_tfhb_general_settings['time_format'] : '12';

		// Get date format from settings via Helper
		$helper = new Helper();
		$date_format = $helper->get_date_format_from_settings('M d, Y');

		$time_zone          = ! empty( $time_zone ) ? $time_zone : 'UTC';
		$selected_time_zone = ! empty( $selected_time_zone ) ? $selected_time_zone : 'UTC';

		try {
			$tz_from = new \DateTimeZone($time_zone);
		} catch (\Exception $e) {
			$tz_from = new \DateTimeZone('UTC');
		}

		try {
			$tz_to = new \DateTimeZone($selected_time_zone);
		} catch (\Exception $e) {
			$tz_to = new \DateTimeZone('UTC');
		}

		try {
			$start_time = new \DateTime($selected_date . ' ' . $start_time, $tz_from);
			$end_time   = new \DateTime($selected_date . ' ' . $end_time, $tz_from);
			$start_time->setTimezone($tz_to);
			$end_time->setTimezone($tz_to);
		} catch (\Exception $e) {
			$start_time = new \DateTime('now', $tz_to);
			$end_time   = new \DateTime('now', $tz_to);
		}

		// Prepare formatted output
		if ($time_format == '12') {
			// Return in this format using settings-based date format: M d, Y 11:30 AM - 12:00 PM (America/New_York)
			$start_format = $start_time->format($date_format . ' g:i A');
			$end_format = $end_time->format('g:i A');
		} else {
			// Return in this format using settings-based date format: M d, Y 11:30 - 12:00 (America/New_York)
			$start_format = $start_time->format($date_format . ' H:i');
			$end_format = $end_time->format('H:i');
		}

		if ($type == 'start') {
			return $start_format . ' (' . $selected_time_zone . ')';
		} elseif ($type == 'full') {

			// Return formatted time range with the time zone name using settings-based date format
			return $start_format . ' - ' . $end_format . ' (' . $selected_time_zone . ')';
		}
	}
	/**
	 * Available slots generated from one host-calendar date.
	 *
	 * `$selected_date` is a date in the meeting's availability timezone. Each returned slot
	 * keeps `start` / `end` in the selected timezone and format, and also carries the
	 * selected-timezone `date`, absolute `start_utc` / `end_utc` and the host-local
	 * `host_date` / `host_start` / `host_end`. A slot can land on the previous or next
	 * date in the selected timezone, so group slots by `date`, never by `$selected_date`
	 * (see getAvailableSlotsByDate()).
	 */
	public function getAvailableTimeData($meeting_id,  $selected_date, $selected_time_zone, $selected_time_format, $host_id = 0, $exclude_booking_id = 0)
	{

		$meeting_id = isset($meeting_id) ? $meeting_id : '';
		$host_id = isset($host_id) ? $host_id : 0;

		$selected_date = isset($selected_date) ? str_replace('/', '-', sanitize_text_field($selected_date)) : '';

		$selected_time_zone = isset($selected_time_zone) ? sanitize_text_field($selected_time_zone) : 'UTC';
		$selected_time_zone = BookingTime::tz($selected_time_zone, 'UTC')->getName();

		$selected_time_format = isset($selected_time_format) ? sanitize_text_field($selected_time_format) : '12';

		$meeting      = new Meeting();

		$MeetingsData = $meeting->get($meeting_id);
		if ( empty( $MeetingsData ) || ! is_object( $MeetingsData ) ) {
			return array();
		}

		// Meeting Information
		$data = get_post_meta($MeetingsData->post_id, '__tfhb_meeting_opt', true);


		$meeting_type      = isset($data['meeting_type']) ? $data['meeting_type'] : 'one-to-single';
		$max_book_per_slot = isset($data['max_book_per_slot']) ? $data['max_book_per_slot'] : 1;
		$is_display_max_book_slot = isset($data['is_display_max_book_slot']) ? $data['is_display_max_book_slot'] : 0;

		$availability_data = $this->GetAvailabilityData($MeetingsData);

		// Meeting time zone
		$time_zone = isset($availability_data['time_zone']) && !empty($availability_data['time_zone']) ? $availability_data['time_zone'] : 'UTC';
		$time_zone = BookingTime::tz($time_zone, 'UTC')->getName();

		// Duration
		$duration = isset($data['duration']) && ! empty($data['duration']) ? $data['duration'] : 30;

		if ($duration == 'custom') {
			$duration = isset($data['custom_duration']) && ! empty($data['custom_duration']) ? $data['custom_duration'] : $duration;
		}
		// Buffer Time Before
		$buffer_time_before = isset($data['buffer_time_before']) && ! empty($data['buffer_time_before']) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset($data['buffer_time_after']) && ! empty($data['buffer_time_after']) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset($data['meeting_interval']) && ! empty($data['meeting_interval']) ? $data['meeting_interval'] : 0;

		// Weekly hours, date overrides and booking range for this host date
		$times = $this->getHostDayTimes($availability_data, $selected_date, $time_zone, $data);
		if (empty($times) || $this->isBookingFrequencyReached($meeting_id, $data, $selected_date, $exclude_booking_id)) {
			return array();
		}

		// Bookings that can overlap this host day: neighbouring host dates too, and any
		// occurrence of a recurring booking.
		$booking = new Booking();
		$where = [
			'OR' => [
				['host_id', '=', $MeetingsData->host_id],
				['meeting_id', '=', $meeting_id]
			],
			['meeting_dates', 'IN_DATES', BookingTime::date_range($selected_date, $selected_date, 1)],
			['status', '!=', 'canceled'],
		];
		if (! empty($exclude_booking_id)) {
			$where[] = ['id', '!=', $exclude_booking_id];
		}
		$bookings = $booking->getBookingWithAttendees(
			$where,
			null,
			'ASC'
		);
		$bookings = is_array($bookings) ? $bookings : array();

		$disabled_times = $this->getBookedTimes($bookings, $meeting_id, $meeting_type, $max_book_per_slot, $time_zone, $selected_time_zone, $selected_time_format);

		// Fetch two-way sync busy slots and merge them into disabled_times
		if (isset($MeetingsData->host_id)) {
			$google_calendar_service = new \HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar();
			$gc_busy_slots = $google_calendar_service->getBusyTimeSlots($MeetingsData->host_id, $selected_date, $time_zone, $selected_time_format);

			if (! empty($gc_busy_slots) && is_array($gc_busy_slots)) {
				$disabled_times = array_merge($disabled_times, $gc_busy_slots);
			}

			// Fetch two-way sync busy slots from Outlook Calendar
			if (class_exists('\HydraBookingPro\Services\Integrations\OutlookCalendar\OutlookCalendar') && method_exists('\HydraBookingPro\Services\Integrations\OutlookCalendar\OutlookCalendar', 'getBusyTimeSlots')) {
				$outlook_calendar_service = new \HydraBookingPro\Services\Integrations\OutlookCalendar\OutlookCalendar();
				$oc_busy_slots = $outlook_calendar_service->getBusyTimeSlots($MeetingsData->host_id, $selected_date, $time_zone, $selected_time_format);

				if (! empty($oc_busy_slots) && is_array($oc_busy_slots)) {
					$disabled_times = array_merge($disabled_times, $oc_busy_slots);
				}
			}
		}

		$time_slots_data = array();
		foreach ($times as $key => $value) {

			$start_time = $value['start'];

			$end_time = $value['end'];

			$generatedSlots = $this->generateTimeSlots($start_time, $end_time, $duration, $meeting_interval, $buffer_time_before, $buffer_time_after, $selected_date, $selected_time_format, $time_zone, $selected_time_zone, $bookings, $max_book_per_slot, $is_display_max_book_slot);

			$time_slots_data = array_merge($time_slots_data, $generatedSlots);
		}

		return array_values(self::removeOverlappingSlots($time_slots_data, $disabled_times, $buffer_time_before, $buffer_time_after));
	}

	/**
	 * Available slots grouped by date in the selected timezone.
	 *
	 * @param string[] $dates Dates in the selected timezone (Y-m-d).
	 * @return array Date => list of slots (see getAvailableTimeData()), one key per requested date.
	 */
	public function getAvailableSlotsByDate($meeting_id, $dates, $selected_time_zone, $selected_time_format, $exclude_booking_id = 0)
	{
		$grouped = array();
		foreach ($dates as $date) {
			$grouped[$date] = array();
		}
		if (empty($grouped)) {
			return $grouped;
		}

		$requested = array_keys($grouped);
		sort($requested);

		// One host date can produce slots on the previous or next selected-timezone date.
		foreach (BookingTime::date_range(reset($requested), end($requested), 1) as $host_date) {
			$slots = $this->getAvailableTimeData($meeting_id, $host_date, $selected_time_zone, $selected_time_format, 0, $exclude_booking_id);
			foreach ($slots as $slot) {
				if (isset($slot['date']) && isset($grouped[$slot['date']])) {
					$grouped[$slot['date']][] = $slot;
				}
			}
		}

		foreach ($grouped as $date => $slots) {
			usort($slots, array(__CLASS__, 'compareSlotStart'));
			$grouped[$date] = $slots;
		}

		return $grouped;
	}

	/**
	 * The available slot that starts at an absolute moment, or null.
	 */
	public function findAvailableSlot($meeting_id, \DateTimeInterface $start, $selected_time_zone, $selected_time_format = '12', $exclude_booking_id = 0)
	{
		$meeting      = new Meeting();
		$MeetingsData = $meeting->get($meeting_id);
		if ( empty( $MeetingsData ) || ! is_object( $MeetingsData ) ) {
			return null;
		}

		$availability_data = $this->GetAvailabilityData($MeetingsData);
		$time_zone         = BookingTime::tz(isset($availability_data['time_zone']) ? $availability_data['time_zone'] : '', 'UTC');
		$host_date         = BookingTime::format($start, $time_zone, 'Y-m-d');
		$start_utc         = BookingTime::iso_utc($start);

		foreach ($this->getAvailableTimeData($meeting_id, $host_date, $selected_time_zone, $selected_time_format, 0, $exclude_booking_id) as $slot) {
			if (isset($slot['start_utc']) && $slot['start_utc'] === $start_utc) {
				return $slot;
			}
		}

		return null;
	}

	public static function compareSlotStart($a, $b)
	{
		return strcmp(isset($a['start_utc']) ? $a['start_utc'] : '', isset($b['start_utc']) ? $b['start_utc'] : '');
	}

	/**
	 * Weekly hours (or the date override) that apply to a host date, empty when the host
	 * is unavailable that day or the date is outside the booking range.
	 */
	public function getHostDayTimes($availability_data, $host_date, $time_zone, $meeting_meta = array())
	{
		$host_day = BookingTime::instant($host_date, '', $time_zone);
		if (! $host_day) {
			return array();
		}

		$range = self::getBookingRange($meeting_meta, $time_zone);
		if ($range && ($host_date < $range['start'] || $host_date > $range['end'])) {
			return array();
		}

		$time_slots = isset($availability_data['time_slots']) && is_array($availability_data['time_slots']) ? $availability_data['time_slots'] : array();
		$date_slots = isset($availability_data['date_slots']) && is_array($availability_data['date_slots']) ? $availability_data['date_slots'] : array();

		$selected_day = $host_day->format('l');
		$times        = array();
		foreach ($time_slots as $single) {
			if (isset($single['day']) && $single['day'] == $selected_day) {
				$is_open = ! isset($single['status']) || ! empty($single['status']);
				$times   = $is_open && isset($single['times']) && is_array($single['times']) ? $single['times'] : array();
			}
		}

		// Date overrides: 'available' set means unavailable all day, otherwise its own hours
		foreach ($date_slots as $value) {
			if (empty($value['date']) || ! in_array($host_date, BookingTime::dates($value['date']), true)) {
				continue;
			}
			if (! empty($value['available'])) {
				return array();
			}
			$times = isset($value['times']) && is_array($value['times']) ? $value['times'] : array();
		}

		return $times;
	}

	/**
	 * Bookable host-date range of a meeting, or null when it is open-ended.
	 * A rolling "within N days" window starts at today in the host timezone.
	 *
	 * @return array|null array( 'start' => 'Y-m-d', 'end' => 'Y-m-d' )
	 */
	public static function getBookingRange($meeting_meta, $time_zone)
	{
		$meeting_meta = is_array($meeting_meta) ? $meeting_meta : array();
		$range_type   = isset($meeting_meta['availability_range_type']) ? $meeting_meta['availability_range_type'] : '';
		$range        = isset($meeting_meta['availability_range']) ? $meeting_meta['availability_range'] : array();
		$range        = is_string($range) ? json_decode($range, true) : $range;
		$range        = is_array($range) ? $range : array();

		if ('within_days' === $range_type) {
			$within       = isset($range['within_days'][0]) ? $range['within_days'][0] : array();
			$within_value = isset($within['limit']) ? (int) $within['limit'] : 30;
			$within_unit  = isset($within['times']) && in_array($within['times'], array('days', 'weeks', 'months'), true) ? $within['times'] : 'days';

			$today = BookingTime::to_tz(BookingTime::now(), $time_zone);
			return array(
				'start' => $today->format('Y-m-d'),
				'end'   => $today->modify('+' . $within_value . ' ' . $within_unit)->format('Y-m-d'),
			);
		}

		if ('range' === $range_type && ! empty($range['start']) && ! empty($range['end'])) {
			return array(
				'start' => substr(str_replace('/', '-', $range['start']), 0, 10),
				'end'   => substr(str_replace('/', '-', $range['end']), 0, 10),
			);
		}

		return null;
	}

	/**
	 * Whether the meeting's booking frequency limit ("N bookings per day / week / month")
	 * is already reached for the period that contains a host date.
	 */
	public function isBookingFrequencyReached($meeting_id, $meeting_meta, $host_date, $exclude_booking_id = 0)
	{
		$rules = isset($meeting_meta['booking_frequency']) ? $meeting_meta['booking_frequency'] : array();
		$rules = is_string($rules) ? json_decode($rules, true) : $rules;
		if (empty($rules) || ! is_array($rules)) {
			return false;
		}

		foreach ($rules as $rule) {
			$limit = isset($rule['limit']) ? (int) $rule['limit'] : 0;
			if ($limit < 1) {
				continue;
			}

			$period = self::getFrequencyPeriod($host_date, isset($rule['times']) ? $rule['times'] : 'days');
			if (! $period) {
				continue;
			}

			if ($this->countMeetingOccurrences($meeting_id, $period['start'], $period['end'], $exclude_booking_id) >= $limit) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Day, week (Monday based), month or year that contains a host date.
	 *
	 * @return array|null array( 'start' => 'Y-m-d', 'end' => 'Y-m-d' )
	 */
	public static function getFrequencyPeriod($host_date, $unit)
	{
		$day = \DateTimeImmutable::createFromFormat('!Y-m-d', $host_date, BookingTime::utc());
		if (! $day) {
			return null;
		}

		$unit = strtolower((string) $unit);
		if (false !== strpos($unit, 'week')) {
			$start = $day->modify('monday this week');
			$end   = $start->modify('+6 days');
		} elseif (false !== strpos($unit, 'month')) {
			$start = $day->modify('first day of this month');
			$end   = $day->modify('last day of this month');
		} elseif (false !== strpos($unit, 'year')) {
			$start = $day->setDate((int) $day->format('Y'), 1, 1);
			$end   = $day->setDate((int) $day->format('Y'), 12, 31);
		} else {
			$start = $day;
			$end   = $day;
		}

		return array(
			'start' => $start->format('Y-m-d'),
			'end'   => $end->format('Y-m-d'),
		);
	}

	/**
	 * Booked occurrences of a meeting between two host dates (inclusive).
	 */
	public function countMeetingOccurrences($meeting_id, $from, $to, $exclude_booking_id = 0)
	{
		static $cache = array();
		$key = $meeting_id . '|' . $from . '|' . $to . '|' . $exclude_booking_id;
		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$where = array(
			array('meeting_id', '=', $meeting_id),
			array('meeting_dates', 'IN_DATES', BookingTime::date_range($from, $to)),
			array('status', '!=', 'canceled'),
		);
		if (! empty($exclude_booking_id)) {
			$where[] = array('id', '!=', $exclude_booking_id);
		}

		$booking  = new Booking();
		$bookings = $booking->getBookingWithAttendees($where, null, 'ASC');

		$count = 0;
		foreach (is_array($bookings) ? $bookings : array() as $item) {
			foreach (BookingTime::dates($item->meeting_dates) as $date) {
				if ($date >= $from && $date <= $to) {
					$count++;
				}
			}
		}

		$cache[$key] = $count;
		return $count;
	}

	/**
	 * Busy intervals of existing bookings, each with absolute start/end objects.
	 */
	public function getBookedTimes($bookings, $meeting_id, $meeting_type, $max_book_per_slot, $time_zone, $selected_time_zone, $selected_time_format)
	{
		$disabled_times = array();
		foreach ($bookings as $booking) {

			// A group slot of this meeting stays open until it is full.
			if ('one-to-group' == $meeting_type && isset($booking->meeting_id) && $booking->meeting_id == $meeting_id) {
				$attendees = ! empty( $booking->attendees ) && ( is_array( $booking->attendees ) || $booking->attendees instanceof \Countable ) ? $booking->attendees : array();

				if (count($attendees) < $max_book_per_slot) {
					continue;
				}
			}

			// Each booking keeps the timezone it was booked in.
			foreach (BookingTime::booking_instants($booking, $time_zone) as $instant) {
				$start_time_obj = new \DateTime('@' . $instant['start']->getTimestamp());
				$end_time_obj   = new \DateTime('@' . $instant['end']->getTimestamp());

				$disabled_times[] = array(
					'start'        => $this->formatTime(clone $start_time_obj, $selected_time_format, $selected_time_zone),
					'end'          => $this->formatTime(clone $end_time_obj, $selected_time_format, $selected_time_zone),
					'start_object' => $start_time_obj,
					'end_object'   => $end_time_obj,
				);
			}
		}

		return $disabled_times;
	}

	/**
	 * Drop slots that overlap a busy interval widened by the buffers on both sides.
	 */
	public static function removeOverlappingSlots($slots, $disabled_times, $buffer_time_before, $buffer_time_after)
	{
		// A new meeting needs buffer_time_before prior to its start, and the existing meeting needs
		// buffer_time_after, so the total gap between them must be buffer_time_before + buffer_time_after.
		$total_buffer = ((int) $buffer_time_before + (int) $buffer_time_after) * 60;

		return array_filter(
			$slots,
			function ($slot) use ($disabled_times, $total_buffer) {
				$slot_start = isset($slot['start_utc']) ? BookingTime::from_iso($slot['start_utc']) : null;
				$slot_end   = isset($slot['end_utc']) ? BookingTime::from_iso($slot['end_utc']) : null;
				if (! $slot_start || ! $slot_end) {
					return true;
				}

				foreach ($disabled_times as $value) {
					if (empty($value['start_object']) || empty($value['end_object']) || ! $value['start_object'] instanceof \DateTimeInterface || ! $value['end_object'] instanceof \DateTimeInterface) {
						continue;
					}

					$booked_start = $value['start_object']->getTimestamp() - $total_buffer;
					$booked_end   = $value['end_object']->getTimestamp() + $total_buffer;

					if ($slot_start->getTimestamp() < $booked_end && $slot_end->getTimestamp() > $booked_start) {
						return false;
					}
				}

				return true;
			}
		);
	}


	public function generateTimeSlots($startTime, $endTime, $duration, $meeting_interval, $buffer_time_before, $buffer_time_after, $selected_date, $time_format, $time_zone, $selected_time_zone, $bookings, $max_book_per_slot, $is_display_max_book_slot)
	{
		$timeSlots = array();

		// Example value for buffer time before meeting start (replace with your actual setting)
		$_tfhb_general_settings = get_option('_tfhb_general_settings');
		$allowed_reschedule_before_meeting_start =   isset($_tfhb_general_settings['allowed_reschedule_before_meeting_start']) && !empty($_tfhb_general_settings['allowed_reschedule_before_meeting_start']) ? $_tfhb_general_settings['allowed_reschedule_before_meeting_start'] : 10;

		if (is_array($allowed_reschedule_before_meeting_start)) {
			$skip_before_meeting_start = $allowed_reschedule_before_meeting_start[0]['limit'];
			$skip_before_format = $allowed_reschedule_before_meeting_start[0]['times'];
		} else {
			$skip_before_meeting_start = $allowed_reschedule_before_meeting_start;
			$skip_before_format = 'minutes';
		}

		$time_zone          = BookingTime::tz($time_zone, 'UTC')->getName();
		$selected_time_zone = BookingTime::tz($selected_time_zone, 'UTC')->getName();

		// Convert start and end times based on the selected timezone
		$start = $this->convert_time_based_on_timezone($selected_date, $startTime, $time_zone, $selected_time_zone, '');
		$end = $this->convert_time_based_on_timezone($selected_date, $endTime, $time_zone, $selected_time_zone, '');

		// Clone the start time for manipulation
		$current = clone $start;

		// Convert to seconds for easier manipulation
		$diff = $duration * 60; // Convert to seconds
		$before_diff = $buffer_time_before * 60; // Convert to seconds
		$after_diff = $buffer_time_after * 60; // Convert to seconds
		$meeting_interval = $meeting_interval * 60; // Convert to seconds
		$total_diff = $diff + $before_diff + $after_diff;

		// Loop through the time range

		while ($current < $end) {

			$slot_end_obj = (clone $current)->modify("+$diff seconds");
			if ($slot_end_obj > $end) {
				break;
			}

			$start_time = $this->formatTime($current, $time_format, $selected_time_zone);
			$end_time   = $this->formatTime($slot_end_obj, $time_format, $selected_time_zone);

			// if current time is passed then skip skip_before_meeting_start
			$current_minus_skip = (clone $current)->modify("-$skip_before_meeting_start $skip_before_format");
			if (new \DateTime('now', new \DateTimeZone($time_zone)) > $current_minus_skip) {
				if ($meeting_interval == 0) {
					$current->modify("+$total_diff seconds");
				} else {
					$current->modify("+$meeting_interval seconds");
				}

				continue;
			}

			$slot = array(
				'start'      => $start_time,
				'end'        => $end_time,
				// Date of this slot in the selected timezone, which can differ from the host date
				'date'       => $current->format('Y-m-d'),
				'start_utc'  => BookingTime::iso_utc($current),
				'end_utc'    => BookingTime::iso_utc($slot_end_obj),
				'host_date'  => BookingTime::format($current, $time_zone, 'Y-m-d'),
				'host_start' => BookingTime::format($current, $time_zone, 'h:i A'),
				'host_end'   => BookingTime::format($slot_end_obj, $time_zone, 'h:i A'),
			);

			$arg = array(
				'bookings' => $bookings,
				'max_book_per_slot' => $max_book_per_slot,
				'is_display_max_book_slot' => $is_display_max_book_slot,
				'selected_date' => $slot['date'],
				'start_time' => $start_time,
				'end_time' => $end_time,
				'selected_time_zone' => $selected_time_zone,
				'start_utc' => $slot['start_utc'],
				'end_utc' => $slot['end_utc'],
				'time_zone' => $time_zone,
			);
			$timeSlots[] = apply_filters(
				'hydra_booking/generate_time_slots_with_remaining_slots',
				$slot,
				$arg
			);

			if ($meeting_interval == 0) {
				$current->modify("+$total_diff seconds");
			} else {
				$current->modify("+$meeting_interval seconds");
			}
		}

		return $timeSlots;
	}

	public function formatTime($dateTime, $timeFormat, $timeZone)
	{

		$dateTime->setTimezone(BookingTime::tz($timeZone, 'UTC'));

		$format = '';
		if ($timeFormat === '12') {
			$format = 'h:i A'; // 12-hour format with AM/PM
		} else {
			$format = 'H:i'; // 24-hour format
		}

		return $dateTime->format($format);
	}

	/* convert date time format */
	public function convertDateTimeFormat($date, $currentFormat, $newFormat)
	{
		$date = \DateTime::createFromFormat($currentFormat, $date);
		return $date->format($newFormat);
	}

	//Get availability_data
	public function GetAvailabilityData($MeetingsData)
	{
		// if $MeetingsData is object make it array
		$meeting_data =  !empty($MeetingsData) && is_array($MeetingsData) ? $MeetingsData : (array) $MeetingsData;
		$availability_data = isset($meeting_data['availability_custom']) ? $meeting_data['availability_custom'] : array();
		if (isset($meeting_data['availability_type']) && 'settings' === $meeting_data['availability_type']) {

			$host = new Host();
			$host_id = isset($meeting_data['host_id']) ? $meeting_data['host_id'] : 0;
			$host = $host_id ? $host->getHostById($host_id) : null;
			$host_user_id = ! empty($host) && isset($host->user_id) ? $host->user_id : 0;

			$_tfhb_availability_settings = $host_user_id ? get_user_meta($host_user_id, '_tfhb_host', true) : array();
			if (isset($_tfhb_availability_settings['availability_type']) && $_tfhb_availability_settings['availability_type'] == 'settings') {
				$host_settings_availability_id = $_tfhb_availability_settings['availability_id'];
				$_tfhb_availability_settings =  get_option('_tfhb_availability_settings');

				if (is_array($_tfhb_availability_settings)) {
					$key = array_search($host_settings_availability_id, array_column($_tfhb_availability_settings, 'id'));
					//  _tfhb_availability_settings index id wich is match with host settings availability id
					if (isset($_tfhb_availability_settings[$key])) {

						$availability_data = $_tfhb_availability_settings[$key];
					}
				}
			} elseif (isset($_tfhb_availability_settings['availability']) && is_array($_tfhb_availability_settings['availability']) && in_array($meeting_data['availability_id'], array_keys($_tfhb_availability_settings['availability']))) {

				$availability_data = $_tfhb_availability_settings['availability'][$meeting_data['availability_id']];
			}
		}

		$availability_data = !is_array($availability_data) ? json_decode($availability_data, true) : $availability_data;

		return $availability_data;
	}
}
