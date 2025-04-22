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
            'body' =>  '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with Host Name was confirmed.</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/calendar-days.png" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Meeting.png" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/file-text.png" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Location.png" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/mail.png" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/phone.png" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
                
            ',
            'builder' => ''
            
        );
        $notification['host']['booking_cancel'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'A booking was cancelled with {{attendee.name}}',
            'body' =>  '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A booking with Host Name was cancelled.</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/calendar-days.png" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Meeting.png" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/file-text.png" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Location.png" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/mail.png" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/phone.png" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => ''
        );
        
        $notification['host']['booking_pending'] = array(
            'status' => 1,
            'template' => 'default',
            'from' =>  '{{wp.admin_email}}',
            'subject' => 'Pending Booking between {{host.name}} & {{attendee.name}}',
            'body' =>  ' 
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with Host Name was pending.</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/calendar-days.png" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Meeting.png" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/file-text.png" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Location.png" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/mail.png" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/phone.png" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => ''
            
        );
        $notification['host']['booking_reschedule'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'A booking was rescheduled with {{attendee.name}}',
            'body' =>  '
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A booking with Host Name was rescheduled.</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/calendar-days.png" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Meeting.png" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/file-text.png" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/Location.png" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/user.png" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/mail.png" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="http://tourfic-development-site.local/wp-content/plugins/hydra-booking/assets/images/phone.png" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => ''
        );
        $notification['host']['booking_reminder'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_host}}', 
            'body' =>  ' 
            ',
            'builder' => ''
        );

        // Attendee Notification
        $notification['attendee']['booking_confirmation'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Booking Confirmation between {{host.name}} & {{attendee.name}}',
            'body' =>  ' 
            ',
            'builder' => ''

        );
        // Attendee Notification
        $notification['attendee']['booking_pending'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Pending Booking with {{host.name}}',
            'body' =>  '
 
            ',
            'builder' => ''

        );
        $notification['attendee']['booking_cancel'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'A booking was cancelled with {{host.name}}',
            'body' =>  ' 
            ',
            'builder' => ''


        );
        $notification['attendee']['booking_reschedule'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'Your booking was rescheduled with {{host.name}}',
            'body' =>  ' 
 
            ',
        );
        $notification['attendee']['booking_reminder'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_attendee}}', 
            'body' =>  ' 
            ',
        );


        return $notification;
        
    }
 
 

 
}
