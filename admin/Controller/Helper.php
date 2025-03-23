<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; } 
 
class Helper {


	// constaract
	public function __construct() {
 
	}

    // Get Default Notification
    public function get_default_notification_template(){
        $notification = array(
            'host' => array(),
            'attendee' => array(),
        );

        //  Host Notification
         //  Host Notification
         $notification['host']['booking_confirmation'] = array(
            'status' => 1,
            'template' => 'default',
            'from' =>  '{{wp.admin_email}}',
            'subject' => 'New Booking between {{host.name}} & {{attendee.name}}',
            'body' =>  ' <h2>A new Booking has been scheduled</h2> <hr> 
            
                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_host_timezone}} </p>

                <h3>Who</h3> 

                <p> Host: {{host.name}}  </p>
                <p> Attendee: {{attendee.name}}  </p>

                <h3>Where</h3> 
                <p>{{booking.location_details_html}}  </p>

                <h3>Additional Data</h3>
                <p> {{attendee.additional_data}} </p>
            '
            
        );
        $notification['host']['booking_cancel'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'A booking was cancelled with {{attendee.name}}',
            'body' =>  ' <h2>Booking Cancellation</h2> <hr> 
            
                <h3>Cancellation Reason</h3>
                <p>{{booking.cancel_reason}}</p>  

                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_host_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p>

                <p> Attendee: {{attendee.name}}  </p>
                <h3>Where</h3>
                <p>{{booking.location_details_html}}  </p>

                <h3>Additional Data</h3>
                <p> {{attendee.additional_data}} </p>
            '
        );
        
        $notification['host']['booking_pending'] = array(
            'status' => 1,
            'template' => 'default',
            'from' =>  '{{wp.admin_email}}',
            'subject' => 'Pending Booking between {{host.name}} & {{attendee.name}}',
            'body' =>  ' <h2>A Booking is Pending Approval</h2> <hr> 
            
                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_host_timezone}} </p>

                <h3>Who</h3> 

                <p> Host: {{host.name}}  </p>
                <p> Attendee: {{attendee.name}}  </p>
 
                <h3>Additional Data</h3>
                <p> {{attendee.additional_data}} </p>
            '
            
        );
        $notification['host']['booking_reschedule'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'A booking was rescheduled with {{attendee.name}}',
            'body' =>  ' <h2>Booking Rescheduled</h2> <hr> 
            
                <h3>Rescheduling Reason</h3>
                <p>{{booking.cancel_reason}}</p>  

                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_host_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p>

                <p> Attendee: {{attendee.name}}  </p>
                <h3>Where</h3>
                <p>{{booking.location_details_html}}  </p>

                <h3>Additional Data</h3>
                <p> {{attendee.additional_data}} </p>
            '
        );
        $notification['host']['booking_reminder'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_host}}', 
            'body' =>  ' <h2>Reminder: Your meeting will start in {{booking.start_date_time_for_host}}</h2> <hr> 
                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_attendee_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p> 
                <p> Attendee: {{attendee.name}}  </p>

                <h3>Where</h3>
                <p>{{booking.location_details_html}}  </p>

                <h3>Additional Data</h3>
                <p> {{attendee.additional_data}} </p>
            '
        );

        // Attendee Notification
        $notification['attendee']['booking_confirmation'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Booking Confirmation between {{host.name}} & {{attendee.name}}',
            'body' =>  ' <h2>Your booking has been scheduled</h2> <hr> 
                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_attendee_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p>

                <p> Attendee: {{attendee.name}}  </p>

                <h3>Where</h3>
                <p>{{booking.location_details_html}}  </p>
            '

        );
        // Attendee Notification
        $notification['attendee']['booking_pending'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Pending Booking with {{host.name}}',
            'body' =>  ' <h2>Your Booking is Pending Approval</h2> <hr> 
                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_attendee_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p>

                <p> Attendee: {{attendee.name}}  </p>
 
            '

        );
        $notification['attendee']['booking_cancel'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'A booking was cancelled with {{host.name}}',
            'body' =>  ' <h2>Booking Cancellation</h2> <hr> 
                <h3>Cancellation Reason</h3>
                <p>{{booking.cancel_reason}}</p>  

                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>


                <h3>When</h3>
                <p> {{booking.full_start_end_attendee_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p> 
                <p> Attendee: {{attendee.name}}  </p>

                <h3>Where</h3> 
               <p>{{booking.location_details_html}}  </p>
            '


        );
        $notification['attendee']['booking_reschedule'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'Your booking was rescheduled with {{host.name}}',
            'body' =>  ' <h2>Booking Rescheduled</h2> <hr> 
            
                <h3>Rescheduling Reason</h3>
                <p>{{booking.cancel_reason}}</p>  

                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_host_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p> 
                <p> Attendee: {{attendee.name}}  </p>

                <h3>Where</h3> 
                <p>{{booking.location_details_html}}  </p>
 
            '
        );
        $notification['attendee']['booking_reminder'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_attendee}}', 
            'body' =>  ' <h2>Reminder: Your meeting will start in {{booking.start_date_time_for_attendee}}</h2> <hr> 
                <h3>Meeting Details</h3>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>

                <h3>When</h3>
                <p> {{booking.full_start_end_attendee_timezone}} </p>

                <h3>Who</h3>
                <p> Host: {{host.name}}  </p> 
                <p> Attendee: {{attendee.name}}  </p>

                <h3>Where</h3>
                <p>{{booking.location_details_html}}  </p>
            '
        );


        return $notification;
        
    }
 
 

 
}
