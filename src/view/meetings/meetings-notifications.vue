<script setup>
import { __ } from '@wordpress/i18n';
import {ref} from 'vue'
import Icon from '@/components/icon/LucideIcon.vue'
import MailNotifications from '@/components/notifications/MailNotifications.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';

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


const currentTabs = ref('host');
const ntskeleton = ref(false);  
const currentIntegrationTabs = ref('telegram');
const smsskeleton = ref(false);  

// Update Notification 
const changeTab = (e) => {  
    ntskeleton.value = true;
    // get data-tab attribute value of clicked button
    const tab = e.target.getAttribute('data-tab'); 
    currentTabs.value = tab;
    setTimeout(() => {
        ntskeleton.value = false;
    }, 1000);

}
const changeIntegrationTab = (e) => {
    smsskeleton.value = true;
    // get data-tab attribute value of clicked button
    const tab = e.target.getAttribute('data-tab'); 
    currentIntegrationTabs.value = tab;
    setTimeout(() => {
        smsskeleton.value = false;
    }, 1000);
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
                <button @click="changeTab" data-tab="host" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='host' ? 'active' : ''" ><Icon name="UserRound" size=15 /> {{ $tfhb_trans('To Host') }}</button>
                <button @click="changeTab"  data-tab="attendee" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='attendee' ? 'active' : ''"><Icon name="UsersRound" size=15 /> {{ $tfhb_trans('To Attendee') }} </button>
            </div>
            
            <!-- Mail Notification -->
            <div class="tfhb-notification-box tfhb-full-width">
                <div class="tfhb-notification-header tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox  tfhb-gap-12">
                        <Icon name="Mail" size=20 /> <span> {{ $tfhb_trans('Email') }}</span>
                    </div>
                    <div class="tfhb-arrow-collapse">
                        <Icon name="ChevronDown" size=20 />
                    </div>
                </div>

                <div class="tfhb-integration-notification-box">
                    <div v-if="currentTabs=='host'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{ 'tfhb-skeleton': ntskeleton }"> 
                        <!-- Single Notification  -->
                        <MailNotifications 
                            title="Send Email to Host" 
                            :label="$tfhb_trans('Booking Confirmation')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.host.booking_confirmation"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingConfirmPopUp"
                            @popup-open-control="hostBookingConfirmPopUp = true"
                            @popup-close-control="hostBookingConfirmPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Pending')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.host.booking_pending"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingPendingPopUp"
                            @popup-open-control="hostBookingPendingPopUp = true"
                            @popup-close-control="hostBookingPendingPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Cancel')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.host.booking_cancel"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingCencelPopUp"
                            @popup-open-control="hostBookingCencelPopUp = true"
                            @popup-close-control="hostBookingCencelPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Reschedule')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.host.booking_reschedule" 
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingReschedulePopUp"
                            @popup-open-control="hostBookingReschedulePopUp = true"
                            @popup-close-control="hostBookingReschedulePopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Reminder')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.host.booking_reminder"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingReminderPopUp"
                            @popup-open-control="hostBookingReminderPopUp = true"
                            @popup-close-control="hostBookingReminderPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->
        
        
                    </div> 
                    <div v-if="currentTabs=='attendee'"  class="tfhb-notification-wrap tfhb-notification-host tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{ 'tfhb-skeleton': ntskeleton }"> 

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Send Email to Attendee')" 
                            :label="$tfhb_trans('Booking Confirmation')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.attendee.booking_confirmation"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="attendeeBookingConfirmPopUp"
                            @popup-open-control="attendeeBookingConfirmPopUp = true"
                            @popup-close-control="attendeeBookingConfirmPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Send Email to Attendee')"
                            :label="$tfhb_trans('Booking Pending')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.attendee.booking_pending"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="attendeeBookingPendingPopUp"
                            @popup-open-control="attendeeBookingPendingPopUp = true"
                            @popup-close-control="attendeeBookingPendingPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Attendee')"
                            :label="$tfhb_trans('Booking Cancel')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.attendee.booking_cancel"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="attendeeBookingCancelPopUp"
                            @popup-open-control="attendeeBookingCancelPopUp = true"
                            @popup-close-control="attendeeBookingCancelPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Attendee')"
                            :label="$tfhb_trans('Booking Reschedule')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.attendee.booking_reschedule"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="attendeeBookingReschedulePopUp"
                            @popup-open-control="attendeeBookingReschedulePopUp = true"
                            @popup-close-control="attendeeBookingReschedulePopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Attendee')"
                            :label="$tfhb_trans('Booking Reminder')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.attendee.booking_reminder"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="attendeeBookingReminderPopUp"
                            @popup-open-control="attendeeBookingReminderPopUp = true"
                            @popup-close-control="attendeeBookingReminderPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->
        
        
                    </div> 
                </div>
            </div>
            

            <!-- SMS Notification -->
            <div class="tfhb-notification-box tfhb-full-width tfhb-mt-24" v-if="currentTabs=='host'" :class="{ 'tfhb-skeleton': ntskeleton }">
                <div class="tfhb-notification-header tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox  tfhb-gap-12">
                        <Icon name="TextSelect" size=20 /> <span> {{ $tfhb_trans('SMS') }}</span>
                    </div>
                    <div class="tfhb-arrow-collapse">
                        <Icon name="ChevronDown" size=20 />
                    </div>
                </div>

                <div class="tfhb-integration-notification-box">
                    <div class="tfhb-notification-button-tabs tfhb-flexbox">
                        <button @click="changeIntegrationTab" data-tab="telegram" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentIntegrationTabs=='telegram' ? 'active' : ''" ><img :src="$tfhb_url+'/assets/images/telegram.svg'" alt=""> {{ $tfhb_trans('Telegram') }}</button>
                        <button @click="changeIntegrationTab" data-tab="slack" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentIntegrationTabs=='slack' ? 'active' : ''" ><img :src="$tfhb_url+'/assets/images/Slack.svg'" alt=""> {{ $tfhb_trans('Slack') }}</button>
                        <button @click="changeIntegrationTab" data-tab="twilio" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentIntegrationTabs=='twilio' ? 'active' : ''" ><img :src="$tfhb_url+'/assets/images/Twilio.svg'" alt=""> {{ $tfhb_trans('Twilio') }}</button>
                    </div>

                    <HbInfoBox name="first-modal">
                        <template #content>
                            <span>{{$tfhb_trans('Your aren’t connected with WhatsApp. Please go to Setting > Integration and connect.')}}  
                            </span>
                        </template>
                    </HbInfoBox>

                    <div v-if="currentIntegrationTabs=='telegram'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{ 'tfhb-skeleton': smsskeleton }"> 
                        <!-- Single Notification  -->
                        <MailNotifications 
                            title="Send Email to Host" 
                            :label="$tfhb_trans('Booking Confirmation')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_confirmation"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingConfirmPopUp"
                            @popup-open-control="hostBookingConfirmPopUp = true"
                            @popup-close-control="hostBookingConfirmPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Pending')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_pending"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingPendingPopUp"
                            @popup-open-control="hostBookingPendingPopUp = true"
                            @popup-close-control="hostBookingPendingPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Cancel')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_cancel"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingCencelPopUp"
                            @popup-open-control="hostBookingCencelPopUp = true"
                            @popup-close-control="hostBookingCencelPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Reschedule')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_reschedule" 
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingReschedulePopUp"
                            @popup-open-control="hostBookingReschedulePopUp = true"
                            @popup-close-control="hostBookingReschedulePopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                        :title="$tfhb_trans('Send Email to Host')" 
                            :label="$tfhb_trans('Booking Reminder')"
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_reminder"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="hostBookingReminderPopUp"
                            @popup-open-control="hostBookingReminderPopUp = true"
                            @popup-close-control="hostBookingReminderPopUp = false"
                            :mediaurl="$tfhb_url"
                        /> 
                        <!-- Single Integrations  -->
        
        
                    </div> 

                </div>
                
            </div>

        </div> 

        <div class="tfhb-submission-btn"> 
            <HbButton  
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting')"
                :buttonText="$tfhb_trans('Save & Continue')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                :pre_loader="props.update_preloader"
            />  
        </div>
        <!--Bookings -->
    </div>
</template>