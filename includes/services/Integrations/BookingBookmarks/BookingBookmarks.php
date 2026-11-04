<?php

namespace HydraBooking\Services\Integrations\BookingBookmarks;
// exit
if (! defined('ABSPATH')) {
    exit;
}


use HydraBooking\DB\Attendees;
// use HydraBooking\Services\Integrations\BookingBookmarks\BookingBookmarks;

/**
 * 
 * BookingBookmarks
 */
class BookingBookmarks
{

    public function __construct() {}

    public function GetBookingConfirmationUrl($data)
    {

        $confirmation = add_query_arg(
            array(
                'hydra-booking' => 'booking',
                'hash'          => $data->hash,
                'meetingId'    => $data->meeting_id,
                'type'          => 'confirmation',
            ),
            home_url()
        );
        return $confirmation;
    }

    public function GetBookingIcsUrl($data)
    {
        $confirmation = add_query_arg(
            array(
                'hydra-booking' => 'booking',
                'hash'          => $data->hash,
                'meetingId'    => $data->meeting_id,
                'type'          => 'download_ics',
            ),
            home_url()
        );
        return $confirmation;
    }
    public function getMeetingBookmarks($data)
    {
        $bookingTitle = $data->meeting_title . ' Between ' . $data->host_first_name . ' ' . $data->host_last_name . ' and ' . $data->attendee_name;
        $location = '';
        if (!empty($data->meeting_locations)) {
            $location_data = json_decode($data->meeting_locations, true);

            foreach ($location_data as $key => $value) {
                $location .= $value['location'] . " - " . $value['address'];
                if ($key < count($location_data) - 1) {
                    $location .= ", ";
                }
            }
        }

        // Falls back to the host profile and site timezone for bookings saved without one
        $availability_time_zone = \HydraBooking\Admin\Controller\BookingTime::booking_tz($data)->getName(); // Example: "America/New_York"

        // meeting_dates can be a comma separated list; use the first date for the bookmark links.
        $meeting_dates = explode(',', $data->meeting_dates);
        $meeting_date  = trim($meeting_dates[0]);

        // Convert to required format with the correct timezone
        $dtStart = new \DateTime("{$meeting_date} {$data->start_time}", new \DateTimeZone($availability_time_zone));
        $dtEnd = new \DateTime("{$meeting_date} {$data->end_time}", new \DateTimeZone($availability_time_zone));
        $details = '<p>' . esc_html($data->meeting_title) . '</p>';

        // Format for Google Calendar (Including Timezone)
        $start_time_google = $dtStart->format("Ymd\THis");
        $end_time_google = $dtEnd->format("Ymd\THis");
        // Google Calendar Link with Timezone
        $bookmarks['google'] = [
            'title' => __('Google Calendar', 'hydra-booking'),
            'url'   => add_query_arg([
                'dates'    => $start_time_google . '/' . $end_time_google,
                'text'     => $bookingTitle,
                'details'  => $details,
                'location' => $location,
                'ctz'      => $availability_time_zone
            ], 'https://calendar.google.com/calendar/r/eventedit'),

            'icon' => esc_url(TFHB_URL . 'assets/app/images/google-calendar.svg'),
        ];

        // Format for Outlook (ISO 8601 format with time zone)
        // Outlook reads times without an offset in the viewer's own timezone, so send UTC
        $start_time_outlook = \HydraBooking\Admin\Controller\BookingTime::iso_utc($dtStart);
        $end_time_outlook = \HydraBooking\Admin\Controller\BookingTime::iso_utc($dtEnd);
        // Outlook Calendar Link
        $bookmarks['outlook'] = [
            'title' => __('Outlook', 'hydra-booking'),
            'url'   => add_query_arg([
                'startdt'  => $start_time_outlook,
                'enddt'    => $end_time_outlook,
                'subject'  => $bookingTitle,
                'path'     => '/calendar/action/compose',
                'body'     =>  $details,
                'rru'      => 'addevent',
                'location' => $location,
            ], 'https://outlook.live.com/calendar/0/deeplink/compose'),
            'icon'  => esc_url(TFHB_URL . 'assets/app/images/outlook-calendar.svg'),
        ];

        // // Microsoft Office 365 Calendar Link
        // $bookmarks['msoffice'] = [
        //     'title' => __('Microsoft Office', 'hydra-booking'),
        //     'url'   => add_query_arg([
        //         'startdt'  => $start_time_outlook,
        //         'enddt'    => $end_time_outlook,
        //         'subject'  => $bookingTitle,
        //         'path'     => '/calendar/action/compose',
        //         'body'     => $data->meeting_title,
        //         'rru'      => 'addevent',
        //         'location' => $location,
        //     ], 'https://outlook.office.com/calendar/0/deeplink/compose'),
        //     'icon'  => $assetsUrl . 'images/msoffice.svg'
        // ];


        // Format start time for Yahoo (UTC format with 'Z') - convert a clone so $dtStart stays in the local timezone for the calculations below
        $start_time_yahoo = (clone $dtStart)->setTimezone(new \DateTimeZone('UTC'))->format("Ymd\THis\Z");

        // Calculate duration in minutes
        $duration = $dtStart->diff($dtEnd);
        $duration_minutes = ($duration->h * 60) + $duration->i; // Convert hours to minutes
        // Format duration for Yahoo Calendar (HHMM format)
        $formatted_duration = str_pad(floor($duration_minutes / 60), 2, '0', STR_PAD_LEFT) . str_pad($duration_minutes % 60, 2, '0', STR_PAD_LEFT);



        // Yahoo Calendar Link with dynamic duration
        $bookmarks['yahoo'] = [
            'title' => __('Yahoo Calendar', 'hydra-booking'),
            'url'   => add_query_arg([
                'v'        => 60,
                'view'     => 'd',
                'type'     => 20,
                'title'    => $bookingTitle,
                'st'       => $start_time_yahoo,
                'dur'      => $formatted_duration,
                'desc'     => $details,
                'in_loc'   => $location,
            ], 'http://calendar.yahoo.com/'),
            'icon' => esc_url(TFHB_URL . 'assets/app/images/yahoo-calendar.svg'),
        ];
        $bookmarks['other']    = [
            'title' => __('Other Calendar', 'hydra-booking'),
            'url'   => $this->GetBookingIcsUrl($data),
            'icon' => esc_url(TFHB_URL . 'assets/app/images/other-calendar.svg'),
        ];

        return $bookmarks;
    }

    public  function generateBookingICS($data)
    {
        // meeting_dates can be a comma separated list; use the first date for the ICS event.
        $meeting_dates = explode(',', $data->meeting_dates);
        $meeting_date  = trim($meeting_dates[0]);

        // Convert time to UTC format for ICS
        $booking_time_zone = \HydraBooking\Admin\Controller\BookingTime::booking_tz($data);
        $start = new \DateTime("{$meeting_date} {$data->start_time}", $booking_time_zone);
        $end = new \DateTime("{$meeting_date} {$data->end_time}", $booking_time_zone);
        $start->setTimezone(new \DateTimeZone('UTC'));
        $end->setTimezone(new \DateTimeZone('UTC'));

        // ICS File Content
        $ics_content = "BEGIN:VCALENDAR\r\n";
        $ics_content .= "VERSION:2.0\r\n";
        $ics_content .= "PRODID:-//YourPlugin//BookingSystem//EN\r\n";
        $ics_content .= "METHOD:REQUEST\r\n";
        $ics_content .= "BEGIN:VEVENT\r\n";
        $ics_content .= "UID:" . md5($data->hash) . "\r\n";
        $ics_content .= "SUMMARY:" . $data->meeting_title . "\r\n";
        $ics_content .= "DESCRIPTION:Meeting with " . $data->attendee_name . "\r\n";
        $ics_content .= "DTSTART:" . $start->format('Ymd\THis\Z') . "\r\n";
        $ics_content .= "DTEND:" . $end->format('Ymd\THis\Z') . "\r\n";
        // Decode meeting locations
        $locations = json_decode($data->meeting_locations, true);
        if (!empty($locations)) {
            $locationString = [];
            foreach ($locations as $key => $location) {
                $locationString[] = "{$location['location']} - {$location['address']}";
            }
            $ics_content .= "LOCATION:" . implode(", ", $locationString) . "\r\n";
        }
        // $ics_content .= "LOCATION:" . $location . "\r\n";
        $ics_content .= "ORGANIZER;CN=\"" . $data->host_first_name . "\":mailto:" . $data->host_email . "\r\n";
        $ics_content .= "ATTENDEE;CN=\"" . $data->attendee_name . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE;PARTSTAT=ACCEPTED:mailto:" . $data->email . "\r\n";
        $ics_content .= "END:VEVENT\r\n";
        $ics_content .= "END:VCALENDAR\r\n";

        // Send Headers
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="booking-event.ics"');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $ics_content;
        exit;
    }


    // generate full booking ics 
    public function generateFullBookingICS($data)
    {
        // iCal header
        // Start iCal file
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Your Company//Meeting Scheduler//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        foreach ($data as $meeting) {

            if ($meeting->meeting_dates == '' || $meeting->start_time == '') {
                continue;
            }
            $meeting_dates = explode(',', $meeting->meeting_dates);
            foreach ($meeting_dates as $meeting_date) {
                $uid = uniqid();
                $time_zone = !empty($meeting->availability_time_zone) ? $meeting->availability_time_zone : $meeting->host_time_zone;
                $dtStart = $this->formatToUTC($meeting_date, $meeting->start_time, $time_zone);
                $dtEnd = $this->formatToUTC($meeting_date, $meeting->end_time, $time_zone);

                $ical .= "BEGIN:VEVENT\r\n";
                $ical .= "UID:$uid\r\n";
                $ical .= "DTSTAMP:" . gmdate("Ymd\THis\Z") . "\r\n";
                $ical .= "DTSTART:$dtStart\r\n";
                $ical .= "DTEND:$dtEnd\r\n";
                $ical .= "SUMMARY:" . $meeting->title . "\r\n";
                $status = ! empty( $meeting->status ) ? strtoupper( (string) $meeting->status ) : 'CONFIRMED';
                $ical .= "STATUS:" . $status . "\r\n";

                // Decode meeting locations
                $locations = json_decode($meeting->meeting_locations, true);
                if (!empty($locations)) {
                    $locationString = [];
                    foreach ($locations as $key => $location) {
                        $locationString[] = "{$location['location']} - {$location['address']}";
                    }
                    $ical .= "LOCATION:" . implode(", ", $locationString) . "\r\n";
                }

                // Add attendees
                if (!empty($meeting->attendees)) {
                    foreach ($meeting->attendees as $attendee) {
                        $ical .= "ATTENDEE;CN={$attendee->attendee_name}:mailto:{$attendee->email}\r\n";
                    }
                }

                $ical .= "END:VEVENT\r\n";
            }
        }

        // iCal footer
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    // Get booking ICS URL for the current user
    public function generateSingleBookingICS($meeting)
    {
        // Start iCal file
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Your Company//Meeting Scheduler//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $meeting_dates = explode(',', $meeting->meeting_dates);
        foreach ($meeting_dates as $meeting_date) {
            $uid = uniqid();

            $dtStart = $this->formatToUTC($meeting_date, $meeting->start_time, $meeting->availability_time_zone);
            $dtEnd = $this->formatToUTC($meeting_date, $meeting->end_time, $meeting->availability_time_zone);

            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:$uid\r\n";
            $ical .= "DTSTAMP:" . gmdate("Ymd\THis\Z") . "\r\n";
            $ical .= "DTSTART:$dtStart\r\n";
            $ical .= "DTEND:$dtEnd\r\n";
            $ical .= "SUMMARY:" . $meeting->title . "\r\n";
            $status = ! empty( $meeting->status ) ? strtoupper( (string) $meeting->status ) : 'CONFIRMED';
                $ical .= "STATUS:" . $status . "\r\n";

            // Decode meeting locations
            $locations = json_decode($meeting->meeting_locations, true);
            if (!empty($locations)) {
                $locationString = [];
                foreach ($locations as $key => $location) {
                    $locationString[] = "{$location['location']} - {$location['address']}";
                }
                $ical .= "LOCATION:" . implode(", ", $locationString) . "\r\n";
            }

            // Add attendees
            if (!empty($meeting->attendees)) {
                foreach ($meeting->attendees as $attendee) {
                    $ical .= "ATTENDEE;CN={$attendee->attendee_name}:mailto:{$attendee->email}\r\n";
                }
            }

            $ical .= "END:VEVENT\r\n";
        }

        // iCal footer
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    // Convert date and time to UTC format
    public function formatToUTC($date, $time, $timezone)
    {
        // An empty or invalid timezone falls back to the site timezone, same as every other export
        $datetime = \HydraBooking\Admin\Controller\BookingTime::instant(trim($date), $time, $timezone);

        if (! $datetime) {
            return false; // Return false if date parsing fails
        }

        return \HydraBooking\Admin\Controller\BookingTime::format($datetime, 'UTC', "Ymd\THis\Z");
    }

    // sent bookmark add to calender link in email notification
    public function sendBookmarkFormEmail($hash)
    {
        // decode hash to get booking data
        // encoded format: $hash = base64_encode( wp_json_encode( ['hash' => $booking_hash, 'type' => $type] ) );
        $decoded_hash = base64_decode($hash, true);
        if ($decoded_hash === false) {
            return false; // Invalid base64 string
        }
        $booking_data = json_decode($decoded_hash, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($booking_data)) {
            return false; // Invalid JSON
        }

        $type = $booking_data['type'] ?? '';
        $allowed_types = ['google', 'outlook', 'yahoo', 'other'];
        if (!in_array($type, $allowed_types, true)) {
            return false;
        }

        // Strictly require secret booking hash; reject guessable sequential attendee_id
        $booking_hash = isset($booking_data['hash']) ? sanitize_text_field(wp_unslash($booking_data['hash'])) : '';
        if (empty($booking_hash) || !ctype_xdigit($booking_hash)) {
            return false;
        }

        $Attendee = new Attendees();
        $attendeeBooking = $Attendee->getAttendeeWithBooking(
            array(
                array('hash', '=', $booking_hash),
            ),
            1,
            'DESC'
        );
        if (!$attendeeBooking || !hash_equals($attendeeBooking->hash, $booking_hash)) {
            return false;
        }

        // For "other", directly stream the ICS file without a 302 redirect to prevent hash disclosure in headers
        if ($type === 'other') {
            $this->generateBookingICS($attendeeBooking);
            exit;
        }

        // Get bookmarks for external calendar providers
        $bookmarks = $this->getMeetingBookmarks($attendeeBooking);

        if (empty($bookmarks[$type]['url'])) {
            return false;
        }

        // Allow redirection to trusted calendar providers
        add_filter('allowed_redirect_hosts', function ($hosts) {
            $calendar_hosts = ['calendar.google.com', 'outlook.live.com', 'calendar.yahoo.com'];
            return array_merge((array) $hosts, $calendar_hosts);
        });

        wp_safe_redirect($bookmarks[$type]['url']);
        exit;
    }
}
