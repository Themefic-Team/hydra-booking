<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 


// import Form Field 
import MailNotifications from '@/components/notifications/MailNotifications.vue'



//  Load Time Zone 
const skeleton = ref(true);   
const host = ref(true);
const attendee = ref(false);
const popup = ref(false);
const isPopupOpen = () => {
    popup.value = true;
}
const isPopupClose = (data) => {
    popup.value = false;
}

const Notification = reactive(  { 
     host : {
        booking_confirmation: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_pending: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_cancel: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_reschedule: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_reminder: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
    
     },
     attendee : {
        booking_confirmation: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_pending: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_cancel: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_reschedule: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
        booking_reminder: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',

        },
    
     }
});

// Host Booking Confirm PopUp
const hostBookingConfirmPopUp = ref(false);
// Host Booking Pending PopUp
const hostBookingPendingPopUp = ref(false);
// Host Booking Cencel PopUp
const hostBookingCencelPopUp = ref(false);
// Host Booking Reschedule PopUp
const hostBookingReschedulePopUp = ref(false);
// Host Booking Reminder PopUp
const hostBookingReminderPopUp = ref(false);

// Attendee Booking Confirm PopUp
const attendeeBookingConfirmPopUp = ref(false);
// Attendee Booking Pending PopUp
const attendeeBookingPendingPopUp = ref(false);
// Attendee Booking Cancel PopUp
const attendeeBookingCancelPopUp = ref(false);
// Attendee Booking Reschedule PopUp
const attendeeBookingReschedulePopUp = ref(false);
// Attendee Booking Reminder PopUp
const attendeeBookingReminderPopUp = ref(false);


// Update Notification 

const changeTab = (e) => {  
    // get data-tab attribute value of clicked button
    const tab = e.target.getAttribute('data-tab'); 
    if(tab == 'host') {  
        host.value = true;
        attendee.value = false;  
    } else { 
        host.value = false;
        attendee.value = true; 
    }

}


// Fetch Notification
const fetchNotification = async () => {

    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status) { 
            // console.log(response.data.integration_settings);
            Notification.host = response.data.notification_settings.host ? response.data.notification_settings.host : Notification.host; 
            Notification.attendee = response.data.notification_settings.attendee ? response.data.notification_settings.attendee : Notification.attendee;
            
            
            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}
const UpdateNotification = async () => {   

    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification/update', Notification, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {    
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 

            popup.value = false;
            
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });

            popup.value = false;
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}
onBeforeMount(() => {  
    fetchNotification();
});

</script>
<template> 

    <!DOCTYPE html>
    <html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Booking Confirmation</title>
    </head>
    <body style="margin: 0; padding: 0; background-color: #F4F4F4; font-family: Arial, sans-serif;">

        <!-- Main Container -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#F4F4F4">
            <tr>
                <td align="center">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" bgcolor="#FFFFFF" style="border-radius: 8px; overflow: hidden; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);">
                        
                        <!-- Header -->
                        <tr>
                            <td bgcolor="#215732" style="padding: 16px 32px; text-align: left;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <img :src="$tfhb_url+'/assets/images/hydra-booking-logo.png'" alt="HydraBooking"  style="max-height: 36px;display: block;">
                                        </td>

                                        <td style="vertical-align: middle; padding-left: 8px;">
                                            <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                        </tr>

                        <!-- Content -->
                        <tr>
                            <td style="padding: 32px;">
                                <p style="font-weight: bold;margin: 0; font-size: 17px;">Hey Sydur,</p>
                                <p style="font-weight: bold; margin: 8px 0 32px 0; font-size: 17px;">A new booking with Host Name was confirmed.</p>

                                <!-- Meeting Details -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 15px; background: #FAFAFA;">
                                    <tr>
                                        <td style="font-weight: bold; font-size: 16px; padding-bottom: 10px;">Meeting Details</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td style="vertical-align: middle;">
                                                        Date & Time:
                                                    </td>

                                                    <td style="vertical-align: middle; padding-left: 8px;">
                                                        Sunday, November 11, 2024 at 12:00 PM (UTC+06:00) Asia - Dhaka
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr><td><strong>Host:</strong> Kamrul Hasan Shovo</td></tr>
                                    <tr><td><strong>About:</strong> Discussion about design system</td></tr>
                                    <tr><td><strong>Description:</strong> Hydra reads your availability from all your existing calendars ensuring you never get double booked.</td></tr>
                                    <tr><td><strong>Location:</strong> Host will initiate the meeting by Google Meet</td></tr>
                                    <tr><td><strong>Meeting link:</strong> <a href="https://google.meet/asdfkjasdflk">https://google.meet/asdfkjasdflk</a></td></tr>
                                    <tr>
                                        <td style="padding-top: 10px;">
                                            <a href="https://google.meet/asdfkjasdflk" style="background-color: #137333; color: #FFFFFF; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block; font-weight: bold;">Join Google Meet</a>
                                        </td>
                                    </tr>
                                </table>

                                <br>

                                <!-- Host Details -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; background: #FAFAFA;">
                                    <tr>
                                        <td style="font-weight: bold; font-size: 16px; padding-bottom: 10px;">Host Details</td>
                                    </tr>
                                    <tr><td><strong>Name:</strong> Kamrul Hasan Shovo</td></tr>
                                    <tr><td><strong>Email:</strong> jack.spar@gmail.com</td></tr>
                                    <tr><td><strong>Phone:</strong> +89 000 123 456</td></tr>
                                </table>

                                <br>

                                <!-- Instructions -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; background: #FAFAFA;">
                                    <tr>
                                        <td style="font-weight: bold; font-size: 16px; padding-bottom: 10px;">Instructions</td>
                                    </tr>
                                    <tr><td>• Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</td></tr>
                                    <tr><td>• Ensure you have a good internet connection, a quality camera, and a quiet space.</td></tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td align="center" style="font-size: 12px; padding: 15px; background: #F9F9F9; color: #777;">
                                © 2025 HydraBooking. All rights reserved.
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>

    </body>
    </html>

    <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard ">
  
        <div  class="tfhb-dashboard-heading  ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('Notifications') }}</h1> 
                <p>{{ $tfhb_trans('Organize booking confirmation/cancel/reschedule/reminder notification for host and attendee') }}</p>
            </div>
            <div class="thb-admin-btn"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=15 /></a>
            </div> 
        </div>
        <div class="tfhb-content-wrap">
            <!-- Gmail -->
            <div class="tfhb-notification-button-tabs tfhb-flexbox tfhb-mb-16">
                <button @click="changeTab" data-tab="host" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn"  :class="host ? 'active' : ''" ><Icon name="UserRound" size=15 /> {{ $tfhb_trans('To Host') }} </button>
                <button @click="changeTab"  data-tab="attendee" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="attendee ? 'active' : ''"><Icon name="UsersRound" size=15 /> {{ $tfhb_trans('To Attendee') }} </button>
            </div>
 
            <div v-if="host" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box "> 
 
                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host for Booking Confirmation" 
                   :label="$tfhb_trans('Booking Confirmation')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_confirmation"  
                    :ispopup="hostBookingConfirmPopUp"
                    @popup-open-control="hostBookingConfirmPopUp = true"
                    @popup-close-control="hostBookingConfirmPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host for Booking Pending" 
                   :label="$tfhb_trans('Booking Pending')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_pending"  
                    :ispopup="hostBookingPendingPopUp"
                    @popup-open-control="hostBookingPendingPopUp = true"
                    @popup-close-control="hostBookingPendingPopUp = false"
                /> 
                <!-- Single Integrations  -->


                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host for Booking Cancels" 
                    :label="$tfhb_trans('Booking Cancel')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_cancel"  
                    :ispopup="hostBookingCencelPopUp"
                    @popup-open-control="hostBookingCencelPopUp = true"
                    @popup-close-control="hostBookingCencelPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="$tfhb_trans('Booking Reschedule')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_reschedule"  
                    :ispopup="hostBookingReschedulePopUp"
                    @popup-open-control="hostBookingReschedulePopUp = true"
                    @popup-close-control="hostBookingReschedulePopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="$tfhb_trans('Booking Reminder')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_reminder"  
                    :ispopup="hostBookingReminderPopUp"
                    @popup-open-control="hostBookingReminderPopUp = true"
                    @popup-close-control="hostBookingReminderPopUp = false"
                /> 
                <!-- Single Integrations  -->
 
 
            </div> 
            <div v-if="attendee"  class="tfhb-notification-wrap tfhb-notification-host tfhb-admin-card-box "> 

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Confirmation')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_confirmation"  
                    :ispopup="attendeeBookingConfirmPopUp"
                    @popup-open-control="attendeeBookingConfirmPopUp = true"
                    @popup-close-control="attendeeBookingConfirmPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Pending')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_pending"  
                    :ispopup="attendeeBookingPendingPopUp"
                    @popup-open-control="attendeeBookingPendingPopUp = true"
                    @popup-close-control="attendeeBookingPendingPopUp = false"
                /> 
                <!-- Single Integrations  -->


                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Cancel')"  
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_cancel"  
                    :ispopup="attendeeBookingCancelPopUp"
                    @popup-open-control="attendeeBookingCancelPopUp = true"
                    @popup-close-control="attendeeBookingCancelPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Reschedule')"
                    :data="Notification.attendee.booking_reschedule"  
                    :ispopup="attendeeBookingReschedulePopUp"
                    @popup-open-control="attendeeBookingReschedulePopUp = true"
                    @popup-close-control="attendeeBookingReschedulePopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Reminder')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_reminder"  
                    :ispopup="attendeeBookingReminderPopUp"
                    @popup-open-control="attendeeBookingReminderPopUp = true"
                    @popup-close-control="attendeeBookingReminderPopUp = false"
                /> 
                <!-- Single Integrations  -->
 
 
            </div> 


        </div>
    </div>
 
</template>