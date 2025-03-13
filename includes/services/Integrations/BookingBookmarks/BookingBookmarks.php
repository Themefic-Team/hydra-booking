<?php
namespace HydraBooking\Services\Integrations\BookingBookmarks;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 
 * BookingBookmarks
 */
class BookingBookmarks { 

	public function __construct( ) { 

	}

    public function getMeetingBookmarks($assetsUrl = '', $data){
        //  $bookmarks = [];
        $bookingTitle = $data->meeting_title . ' Between ' . $data->host_first_name . ' ' . $data->host_last_name . ' and ' . $data->attendee_name;
        $location = '';
        $availability_time_zone = $data->availability_time_zone; // Example: "America/New_York"

        // Convert to required format with the correct timezone
        $dtStart = new \DateTime($data->start_time, new \DateTimeZone($availability_time_zone));
        $dtEnd = new \DateTime($data->end_time, new \DateTimeZone($availability_time_zone));

        // Format for Google Calendar (Including Timezone)
        $start_time_google = $dtStart->format("Ymd\THis");
        $end_time_google = $dtEnd->format("Ymd\THis");

        // Google Calendar Link with Timezone
        $bookmarks['google'] = [
            'title' => __('Google Calendar', 'fluent-booking'),
            'url'   => add_query_arg([
                'dates'    => $start_time_google . '/' . $end_time_google,
                'text'     => $bookingTitle,
                'details'  => $data->meeting_title,
                'location' => $location,
                'ctz'      => $availability_time_zone // Time Zone Parameter for Google
            ], 'https://calendar.google.com/calendar/r/eventedit'),
           
            'icon' => esc_url(TFHB_URL . 'assets/app/images/google-calendar.svg'), 
        ];

        // Format for Outlook (ISO 8601 format with time zone)
        $start_time_outlook = $dtStart->format("Y-m-d\TH:i:s"); 
        $end_time_outlook = $dtEnd->format("Y-m-d\TH:i:s");
        // Outlook Calendar Link
        $bookmarks['outlook'] = [
            'title' => __('Outlook', 'fluent-booking'),
            'url'   => add_query_arg([
                'startdt'  => $start_time_outlook,
                'enddt'    => $end_time_outlook,
                'subject'  => $bookingTitle,
                'path'     => '/calendar/action/compose',
                'body'     =>  $data->meeting_title,
                'rru'      => 'addevent',
                'location' => $location,
            ], 'https://outlook.live.com/calendar/0/deeplink/compose'),
            'icon'  => esc_url(TFHB_URL . 'assets/app/images/outlook-calendar.svg'), 
        ];

        // // Microsoft Office 365 Calendar Link
        // $bookmarks['msoffice'] = [
        //     'title' => __('Microsoft Office', 'fluent-booking'),
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


        // Format start time for Yahoo (UTC format with 'Z')
        $start_time_yahoo = $dtStart->format("Ymd\THis\Z");

        // Calculate duration in minutes
        $duration = $dtStart->diff($dtEnd);
        $duration_minutes = ($duration->h * 60) + $duration->i; // Convert hours to minutes
        // Format duration for Yahoo Calendar (HHMM format)
        $formatted_duration = str_pad(floor($duration_minutes / 60), 2, '0', STR_PAD_LEFT) . str_pad($duration_minutes % 60, 2, '0', STR_PAD_LEFT);

     

        // Yahoo Calendar Link with dynamic duration
        $bookmarks['yahoo'] = [
            'title' => __('Yahoo Calendar', 'fluent-booking'),
            'url'   => add_query_arg([
                'v'        => 60,
                'view'     => 'd',
                'type'     => 20,
                'title'    => $bookingTitle,
                'st'       => $start_time_yahoo,
                'dur'      => $formatted_duration,
                'desc'     => $data->meeting_title,
                'in_loc'   => $location,
            ], 'http://calendar.yahoo.com/'),
            'icon' => esc_url(TFHB_URL . 'assets/app/images/yahoo-calendar.svg'), 
        ];
        $bookmarks['other']    = [
            'title' => __('Other Calendar', 'fluent-booking'),
            'url'   => '',
            // 'url'   => $this->getIcsDownloadUrl(), 
            'icon' => esc_url(TFHB_URL . 'assets/app/images/other-calendar.svg'), 
        ];
        
        return $bookmarks;

    }

    // public static function generateBookingICS(Booking $booking)
    // {
    //     $author = $booking->getHostDetails(false);

    //     // Initialize the ICS content
    //     $icsContent = "BEGIN:VCALENDAR\r\n";
    //     $icsContent .= "VERSION:2.0\r\n";
    //     $icsContent .= "PRODID:-//Google Inc//Fluent Booking//EN\r\n";
    //     $icsContent .= "METHOD:REQUEST\r\n";
    //     $icsContent .= "STATUS:CONFIRMED\r\n";

    //     $icsContent .= "BEGIN:VEVENT\r\n";
    //     $icsContent .= "UID:" . md5($booking->hash) . "\r\n"; // Unique ID for the event

    //     // Event details
    //     $icsContent .= "SUMMARY:" . $booking->getBookingTitle() . "\r\n";
    //     $icsContent .= "DESCRIPTION:" . $booking->getIcsBookingDescription() . "\r\n";

    //     // Date and time formatting (assuming eventStart and eventEnd are DateTime objects)
    //     $icsContent .= "DTSTART:" . gmdate('Ymd\THis\Z', strtotime($booking->start_time)) . "\r\n";
    //     $icsContent .= "DTEND:" . gmdate('Ymd\THis\Z', strtotime($booking->end_time)) . "\r\n";

    //     $icsContent .= "LOCATION:" . $booking->getLocationAsText() . "\r\n";

    //     $icsContent .= "ORGANIZER;CN=\"" . $author['name'] . "\":mailto:" . $author['email'] . "\r\n";

    //     $icsContent .= "ATTENDEE;CN=\"" . $booking->email . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE;PARTSTAT=ACCEPTED:mailto:" . $booking->email . "\r\n";

    //     $icsContent .= "END:VEVENT\r\n";

    //     // Close the VCALENDAR component
    //     $icsContent .= "END:VCALENDAR\r\n";

    //     return $icsContent;
    // }
 
}
