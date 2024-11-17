<script setup>
import { __ } from '@wordpress/i18n';
import {ref} from 'vue'
import Icon from '@/components/icon/LucideIcon.vue'
import MailNotifications from '@/components/notifications/MailNotifications.vue'
import HbButton from '@/components/form-fields/HbButton.vue'

const emit = defineEmits(["update-meeting"]); 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },
    update_preloader: {
        type: Boolean,
        required: true
    }

});


const host = ref(true);
const attendee = ref(false);

const popup = ref(false);
const isPopupOpen = () => {
    popup.value = true;
}
const isPopupClose = (data) => {
    popup.value = false;
}


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


// Host Booking Confirm PopUp
const hostBookingConfirmPopUp = ref(false);
// Host booking Pending PopUp
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

const UpdateNotification = async () => {  



    // emit('update-meeting', []) 

    setTimeout(() => {
        hostBookingConfirmPopUp.value = false;
        hostBookingPendingPopUp.value = false;
        hostBookingCencelPopUp.value = false;
        hostBookingReschedulePopUp.value = false;
        hostBookingReminderPopUp.value = false;
        attendeeBookingConfirmPopUp.value = false;
        attendeeBookingPendingPopUp.value = false;
        attendeeBookingCancelPopUp.value = false;
        attendeeBookingReschedulePopUp.value = false;
        attendeeBookingReminderPopUp.value = false;
    }, 800);
}
</script>

<template>
    <div class="meeting-create-details tfhb-gap-24">
        <div class="tfhb-notification-wrap tfhb-admin-card-box tfhb-m-0 tfhb-gap-0 tfhb-full-width">

            <!-- Gmail -->
            <div class="tfhb-notification-button-tabs tfhb-flexbox tfhb-mb-16">
                <button @click="changeTab" data-tab="host" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn"  :class="host ? 'active' : ''" ><Icon name="UserRound" size=15 /> {{ __('To Host', 'hydra-booking') }}</button>
                <button @click="changeTab"  data-tab="attendee" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="attendee ? 'active' : ''"><Icon name="UsersRound" size=15 /> {{ __('To Attendee', 'hydra-booking') }} </button>
            </div>
 
            <div v-if="host" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width"> 
 
                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="__('Booking Confirmation', 'hydra-booking')" 
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.host.booking_confirmation"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="hostBookingConfirmPopUp"
                    @popup-open-control="hostBookingConfirmPopUp = true"
                    @popup-close-control="hostBookingConfirmPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="__('Booking Pending', 'hydra-booking')" 
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.host.booking_pending"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="hostBookingPendingPopUp"
                    @popup-open-control="hostBookingPendingPopUp = true"
                    @popup-close-control="hostBookingPendingPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="__('Booking Cancel', 'hydra-booking')" 
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.host.booking_cancel"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="hostBookingCencelPopUp"
                    @popup-open-control="hostBookingCencelPopUp = true"
                    @popup-close-control="hostBookingCencelPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="__('Booking Reschedule', 'hydra-booking')" 
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.host.booking_reschedule" 
                    :update_preloader="props.update_preloader"  
                    :ispopup="hostBookingReschedulePopUp"
                    @popup-open-control="hostBookingReschedulePopUp = true"
                    @popup-close-control="hostBookingReschedulePopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host" 
                    :label="__('Booking Reminder', 'hydra-booking')"
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.host.booking_reminder"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="hostBookingReminderPopUp"
                    @popup-open-control="hostBookingReminderPopUp = true"
                    @popup-close-control="hostBookingReminderPopUp = false"
                /> 
                <!-- Single Integrations  -->
 
 
            </div> 
            <div v-if="attendee"  class="tfhb-notification-wrap tfhb-notification-host tfhb-admin-card-box tfhb-m-0 tfhb-full-width"> 

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="__('Booking Confirmation', 'hydra-booking')"
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.attendee.booking_confirmation"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="attendeeBookingConfirmPopUp"
                    @popup-open-control="attendeeBookingConfirmPopUp = true"
                    @popup-close-control="attendeeBookingConfirmPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                title="Send Email to Attendee" 
                :label="__('Booking Pending', 'hydra-booking')"
                @update-notification="UpdateNotification"
                :data="meeting.notification.attendee.booking_pending"  
                :update_preloader="props.update_preloader"  
                :ispopup="attendeeBookingPendingPopUp"
                @popup-open-control="attendeeBookingPendingPopUp = true"
                @popup-close-control="attendeeBookingPendingPopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="__('Booking Cancel', 'hydra-booking')"
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.attendee.booking_cancel"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="attendeeBookingCancelPopUp"
                    @popup-open-control="attendeeBookingCancelPopUp = true"
                    @popup-close-control="attendeeBookingCancelPopUp = false"
                /> 
                 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="__('Booking Reschedule', 'hydra-booking')"
                    :data="meeting.notification.attendee.booking_reschedule"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="attendeeBookingReschedulePopUp"
                    @popup-open-control="attendeeBookingReschedulePopUp = true"
                    @popup-close-control="attendeeBookingReschedulePopUp = false"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="__('Booking Reminder', 'hydra-booking')"
                    @update-notification="UpdateNotification"
                    :data="meeting.notification.attendee.booking_reminder"  
                    :update_preloader="props.update_preloader"  
                    :ispopup="attendeeBookingReminderPopUp"
                    @popup-open-control="attendeeBookingReminderPopUp = true"
                    @popup-close-control="attendeeBookingReminderPopUp = false"
                /> 
                <!-- Single Integrations  -->
 
 
            </div> 

        </div> 

        <div class="tfhb-submission-btn"> 
            <HbButton  
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting')"
                :buttonText="__('Save & Continue', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                :pre_loader="props.update_preloader"
            />  
        </div>
        <!--Bookings -->
    </div>
</template>