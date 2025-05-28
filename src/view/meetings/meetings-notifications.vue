<script setup>
import { __ } from '@wordpress/i18n';
import {ref} from 'vue'
import Icon from '@/components/icon/LucideIcon.vue'
import MailNotifications from '@/components/notifications/MailNotifications.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import { useRouter } from 'vue-router' 

const router = useRouter();

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
const SmsPreview = ref(true);
const EmailPreview = ref(true);


// Update Notification 
const changeTab = (tab) => {  
    ntskeleton.value = true;
    currentTabs.value = tab;
    setTimeout(() => {
        ntskeleton.value = false;
    }, 1000);

}
const changeIntegrationTab = (tab) => {
  smsskeleton.value = true;
  currentIntegrationTabs.value = tab;
  setTimeout(() => {
    smsskeleton.value = false;
  }, 1000);
};



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

const telegramBookingConfirmPopUp = ref(false);
const telegramBookingPendingPopUp = ref(false);
const telegramBookingCencelPopUp = ref(false);
const telegramBookingReschedulePopUp = ref(false);
const telegramBookingReminderPopUp = ref(false);

const slackBookingConfirmPopUp = ref(false);
const slackBookingPendingPopUp = ref(false);
const slackBookingCencelPopUp = ref(false);
const slackBookingReschedulePopUp = ref(false);
const slackBookingReminderPopUp = ref(false);

const twilioBookingConfirmPopUp = ref(false);
const twilioBookingPendingPopUp = ref(false);
const twilioBookingCencelPopUp = ref(false);
const twilioBookingReschedulePopUp = ref(false);
const twilioBookingReminderPopUp = ref(false);

const UpdateNotification = async () => {  

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

        telegramBookingConfirmPopUp.value = false;
        telegramBookingPendingPopUp.value = false;
        telegramBookingCencelPopUp.value = false;
        telegramBookingReschedulePopUp.value = false;
        telegramBookingReminderPopUp.value = false;

        slackBookingConfirmPopUp.value = false;
        slackBookingPendingPopUp.value = false;
        slackBookingCencelPopUp.value = false;
        slackBookingReschedulePopUp.value = false;
        slackBookingReminderPopUp.value = false;

        twilioBookingConfirmPopUp.value = false;
        twilioBookingPendingPopUp.value = false;
        twilioBookingCencelPopUp.value = false;
        twilioBookingReschedulePopUp.value = false;
        twilioBookingReminderPopUp.value = false;
    }, 800);
}
</script>

<template>
    <div class="meeting-create-details tfhb-gap-24">
        <div class="tfhb-notification-wrap tfhb-admin-card-box tfhb-m-0 tfhb-gap-0 tfhb-full-width">

            <!-- Gmail -->
            <div class="tfhb-notification-button-tabs tfhb-flexbox tfhb-mb-16">
                <button @click="changeTab('host')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='host' ? 'active' : ''" ><Icon name="UserRound" size=15 /> {{ $tfhb_trans('To Host') }}</button>
                <button @click="changeTab('attendee')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='attendee' ? 'active' : ''"><Icon name="UsersRound" size=15 /> {{ $tfhb_trans('To Attendee') }} </button>
            </div>
            
            <!-- Mail Notification -->
            <div class="tfhb-notification-box tfhb-full-width">
                <div class="tfhb-notification-header tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox  tfhb-gap-12">
                        <Icon name="Mail" size=20 /> <span> {{ $tfhb_trans('Email') }}</span>
                    </div>
                    <div class="tfhb-arrow-collapse" @click="EmailPreview=!EmailPreview">
                        <Icon v-if="EmailPreview" name="ChevronUp" size=20 />
                        <Icon v-else name="ChevronDown" size=20 />
                    </div>
                </div>

                <div class="tfhb-integration-notification-box" v-show="EmailPreview">
                    <div v-if="currentTabs=='host'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{ 'tfhb-skeleton': ntskeleton }"> 
                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Confirmation to Host')"  
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
                            :title="$tfhb_trans('Booking Pending to Host')" 
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
                            :title="$tfhb_trans('Booking Cancel to Host')" 
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
                            :title="$tfhb_trans('Booking Reschedule to Host')" 
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
                            :title="$tfhb_trans('Booking Reminder to Host')" 
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
                            :title="$tfhb_trans('Booking Confirmation to Attendee')" 
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
                            :title="$tfhb_trans('Booking Pending to Attendee')"
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
                            :title="$tfhb_trans('Booking Cancel to Attendee')"
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
                            :title="$tfhb_trans('Booking Reschedule to Attendee')"
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
                            :title="$tfhb_trans('Booking Reminder to Attendee')"
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
                    <div class="tfhb-arrow-collapse" @click="SmsPreview=!SmsPreview">
                        <Icon v-if="SmsPreview" name="ChevronUp" size=20 />
                        <Icon v-else name="ChevronDown" size=20 />
                    </div>
                </div>

                <div class="tfhb-integration-notification-box" v-show="SmsPreview">
                    <div class="tfhb-notification-button-tabs tfhb-flexbox">
                        <button @click="changeIntegrationTab('telegram')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentIntegrationTabs=='telegram' ? 'active' : ''" ><img :src="$tfhb_url+'/assets/images/telegram.svg'" alt=""> {{ $tfhb_trans('Telegram') }}</button>
                        <button @click="changeIntegrationTab('slack')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentIntegrationTabs=='slack' ? 'active' : ''" ><img :src="$tfhb_url+'/assets/images/Slack.svg'" alt=""> {{ $tfhb_trans('Slack') }}</button>
                        <button @click="changeIntegrationTab('twilio')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentIntegrationTabs=='twilio' ? 'active' : ''" ><img :src="$tfhb_url+'/assets/images/Twilio.svg'" alt=""> {{ $tfhb_trans('Twilio') }}</button>
                    </div>

                    <!-- Telegram -->
                    <HbInfoBox name="first-modal" v-if="currentIntegrationTabs=='telegram' && meeting.telegram==''">
                        <template #content>
                            <span>{{$tfhb_trans('Your aren’t connected with Telegram. Please go to ')}}  
                                <HbButton 
                                    classValue="tfhb-btn" 
                                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                                    buttonText="Setting > Integration"
                                />  
                                {{$tfhb_trans('and connect.')}}  
                            </span>
                        </template>
                    </HbInfoBox>

                    <div v-if="currentIntegrationTabs=='telegram'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{
                        'tfhb-skeleton': smsskeleton,
                        'tfhb-pro': !meeting.telegram
                    }"> 
                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Confirmation to Host')"
                            :label="$tfhb_trans('Booking Confirmation')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_confirmation"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="telegramBookingConfirmPopUp"
                            @popup-open-control="telegramBookingConfirmPopUp = true"
                            @popup-close-control="telegramBookingConfirmPopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="telegram"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Cancel to Host')"
                            :label="$tfhb_trans('Booking Cancel')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_cancel"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="telegramBookingCencelPopUp"
                            @popup-open-control="telegramBookingCencelPopUp = true"
                            @popup-close-control="telegramBookingCencelPopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="telegram"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Reschedule to Host')"
                            :label="$tfhb_trans('Booking Reschedule')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.telegram.booking_reschedule" 
                            :update_preloader="props.update_preloader"  
                            :ispopup="telegramBookingReschedulePopUp"
                            @popup-open-control="telegramBookingReschedulePopUp = true"
                            @popup-close-control="telegramBookingReschedulePopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="telegram"
                        /> 
                        <!-- Single Integrations  -->
        
        
                    </div> 

                    <!-- Slack -->
                    <HbInfoBox name="first-modal" v-if="currentIntegrationTabs=='slack' && meeting.slack==''">
                        <template #content>
                            <span>{{$tfhb_trans('Your aren’t connected with Slack. Please go to ')}}  
                                <HbButton 
                                    classValue="tfhb-btn" 
                                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                                    buttonText="Setting > Integration"
                                />  
                                {{$tfhb_trans('and connect.')}}  
                            </span>
                        </template>
                    </HbInfoBox>
                    <div v-if="currentIntegrationTabs=='slack'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{
                        'tfhb-skeleton': smsskeleton,
                        'tfhb-pro': !meeting.slack
                    }"> 
                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Confirmation to Host')" 
                            :label="$tfhb_trans('Booking Confirmation')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.slack.booking_confirmation"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="slackBookingConfirmPopUp"
                            @popup-open-control="slackBookingConfirmPopUp = true"
                            @popup-close-control="slackBookingConfirmPopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="slack"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Cancel to Host')"
                            :label="$tfhb_trans('Booking Cancel')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.slack.booking_cancel"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="slackBookingCencelPopUp"
                            @popup-open-control="slackBookingCencelPopUp = true"
                            @popup-close-control="slackBookingCencelPopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="slack"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Reschedule to Host')"
                            :label="$tfhb_trans('Booking Reschedule')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.slack.booking_reschedule" 
                            :update_preloader="props.update_preloader"  
                            :ispopup="slackBookingReschedulePopUp"
                            @popup-open-control="slackBookingReschedulePopUp = true"
                            @popup-close-control="slackBookingReschedulePopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="slack"
                        /> 
                        <!-- Single Integrations  -->
        
        
                    </div> 

                    <!-- Twilio -->
                    <HbInfoBox name="first-modal" v-if="currentIntegrationTabs=='twilio' && meeting.twilio==''">
                        <template #content>
                            <span>{{$tfhb_trans('Your aren’t connected with Twilio. Please go to ')}}  
                                <HbButton 
                                    classValue="tfhb-btn" 
                                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                                    buttonText="Setting > Integration"
                                />  
                                {{$tfhb_trans('and connect.')}}  
                            </span>
                        </template>
                    </HbInfoBox>
                    <div v-if="currentIntegrationTabs=='twilio'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box tfhb-m-0 tfhb-full-width" :class="{
                        'tfhb-skeleton': smsskeleton,
                        'tfhb-pro': !meeting.twilio
                    }"> 
                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Confirmation to Host')"
                            :label="$tfhb_trans('Booking Confirmation')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.twilio.booking_confirmation"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="twilioBookingConfirmPopUp"
                            @popup-open-control="twilioBookingConfirmPopUp = true"
                            @popup-close-control="twilioBookingConfirmPopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="twilio"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Cancel to Host')"
                            :label="$tfhb_trans('Booking Cancel')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.twilio.booking_cancel"  
                            :update_preloader="props.update_preloader"  
                            :ispopup="twilioBookingCencelPopUp"
                            @popup-open-control="twilioBookingCencelPopUp = true"
                            @popup-close-control="twilioBookingCencelPopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="twilio"
                        /> 
                        <!-- Single Integrations  -->

                        <!-- Single Notification  -->
                        <MailNotifications 
                            :title="$tfhb_trans('Booking Reschedule to Host')"
                            :label="$tfhb_trans('Booking Reschedule')" 
                            @update-notification="UpdateNotification"
                            :data="meeting.notification.twilio.booking_reschedule" 
                            :update_preloader="props.update_preloader"  
                            :ispopup="twilioBookingReschedulePopUp"
                            @popup-open-control="twilioBookingReschedulePopUp = true"
                            @popup-close-control="twilioBookingReschedulePopUp = false"
                            :mediaurl="$tfhb_url"
                            categoryKey="twilio"
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