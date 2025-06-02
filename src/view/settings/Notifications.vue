<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
const router = useRouter();

// import Form Field 
import MailNotifications from '@/components/notifications/MailNotifications.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbButton from '@/components/form-fields/HbButton.vue'


//  Load Time Zone 
const skeleton = ref(true);   
const ntskeleton = ref(false);   
const currentTabs = ref('host');
const popup = ref(false);
const update_preloader = ref(false);
const currentHash = ref('email'); 
 
// tfhb-hydra-admin-tabs a clicked using javascript event
document.addEventListener('click', function (event) {
    if (event.target.matches('.notification-submenu')) {
        // .tfhb-notification-settings-menu add class expand
        document.querySelector('.tfhb-notification-settings-menu').classList.add('expand');

        currentHash.value = event.target.getAttribute('data-filter');
        // this add class active to the clicked element
        document.querySelectorAll('.dropdown a').forEach(function (el) {
            el.classList.remove('active');
            // 
        });
        event.target.classList.add('active');
    }
}, false);

const Notification = reactive(  { 
     host : {
        booking_confirmation: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_pending: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reminder: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
    
     },
     attendee : {
        booking_confirmation: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_pending: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reminder: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
    },
    telegram : {
        booking_confirmation: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            body : '',
            builder: ''
        },
    },
    twilio : {
        booking_confirmation: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            body : '',
            builder: ''
        },
    },
    slack : {
        booking_confirmation: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            body : '',
            builder: ''
        },
    }
});

// Integration Data
const Integration = reactive( {
    telegram: '',
    slack: '',
    twilio: ''
});

// Update Notification 
const changeTab = (tab) => {  
    ntskeleton.value = true;
    currentTabs.value = tab;
    setTimeout(() => {
        ntskeleton.value = false;
    }, 1000);
}


// Fetch Notification
const fetchNotification = async () => {

    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        });
        if (response.data.status) { 
            Notification.host = response.data.notification_settings.host ? response.data.notification_settings.host : Notification.host; 
            Notification.attendee = response.data.notification_settings.attendee ? response.data.notification_settings.attendee : Notification.attendee;
            Notification.telegram = response.data.notification_settings.telegram ? response.data.notification_settings.telegram : Notification.telegram;
            Notification.twilio = response.data.notification_settings.twilio ? response.data.notification_settings.twilio : Notification.twilio;
            Notification.slack = response.data.notification_settings.slack ? response.data.notification_settings.slack : Notification.slack;

            Integration.telegram = response.data.telegram.status ? response.data.telegram.status : '';
            Integration.slack = response.data.slack.status ? response.data.slack.status : '';
            Integration.twilio = response.data.twilio.status ? response.data.twilio.status : '';

            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}
const UpdateNotification = async () => {   

    update_preloader.value = true;
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification/update', Notification, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );

        if (response.data.status) {    
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 

            popup.value = false;
            update_preloader.value = false;
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });

            popup.value = false;
            update_preloader.value = false;
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}

// image uploader
const imageChangeMobileDashboardLogo = (attachment) => {   
    Notification.email.email_logo = attachment.url; 
    const image = document.querySelector('.notification_email_logo_display'); 
    image.src = attachment.url; 
}
const UploadChangeMobileDashboardLogo = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChangeMobileDashboardLogo(attachment);
    };  
    wp.media.editor.open(); 
}

onBeforeMount(() => {  
    fetchNotification();
});

</script>
<template> 
    <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard ">

        <div  class="tfhb-dashboard-heading  ">
            <div class="tfhb-admin-title tfhb-m-0" v-if="$route.params.id"> 
                <div class="tfhb-flexbox tfhb-gap-4">
                    <router-link class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-inline-flexbox" to="/settings/notifications#email" v-if="$route.params.id && $route.params.type!='telegram' && $route.params.type!='twilio' && $route.params.type!='slack'">
                        <Icon name="ArrowLeft" :width="20"/>
                    </router-link>
                    <router-link class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-inline-flexbox" to="/settings/notifications#telegram" v-if="$route.params.id && $route.params.type=='telegram'">
                        <Icon name="ArrowLeft" :width="20"/>
                    </router-link>
                    <router-link class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-inline-flexbox" to="/settings/notifications#twilio" v-if="$route.params.id && $route.params.type=='twilio'">
                        <Icon name="ArrowLeft" :width="20"/>
                    </router-link>
                    <router-link class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-inline-flexbox" to="/settings/notifications#slack" v-if="$route.params.id && $route.params.type=='slack'">
                        <Icon name="ArrowLeft" :width="20"/>
                    </router-link>
                    <h1 class="tfhb-capitalize">{{ $route.params.type }}</h1> 
                </div>
                <p class="tfhb-capitalize">{{ $route.params.id.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') }}</p>
            </div>
            <div class="tfhb-admin-title tfhb-m-0" v-else> 
                <h1 >{{ $tfhb_trans('Notifications') }}</h1> 
                <p v-if="$route.hash!='#telegram' && $route.hash!='#twilio' && $route.hash!='#slack'">{{ $tfhb_trans('Organize booking confirmation/cancel/reschedule/reminder notification for host and attendee') }}</p>
                <p v-else>{{ $tfhb_trans('Organize booking confirmation/cancel/reschedule/reminder notification for host') }}</p>
            </div>
            <div class="thb-admin-btn"> 
                <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/notifications/" target="_blank" class="tfhb-btn"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=15 /></a>
            </div> 
        </div>
         <nav v-if="$front_end_dashboard == true && !$route.params.id" class="tfhb-booking-tabs tfhb-integrations-settings-menu"> 
            <ul>
                <!-- to route example like hosts/profile/13/information -->
                   <li><router-link to="/settings/notifications#email" :class="{ 'active': $route.fullPath.includes('email') || $route.fullPath.includes('host') || $route.fullPath.includes('attendee') }" class="notification-submenu" data-filter="email"> <Icon name="Mail" size=18 /> {{ $tfhb_trans('Email') }}</router-link></li>
                    
                    <li><router-link to="/settings/notifications#telegram" :class="{ 'active': $route.fullPath.includes('telegram') }" class="notification-submenu" data-filter="telegram"> <img :src="$tfhb_url+'/assets/images/Telegram.svg'" alt=""> {{ $tfhb_trans('Telegram') }}</router-link></li>

                    <li><router-link to="/settings/notifications#twilio" :class="{ 'active': $route.fullPath.includes('twilio') }" class="notification-submenu" data-filter="twilio"> <img :src="$tfhb_url+'/assets/images/Twilio.svg'" alt=""> {{ $tfhb_trans('Twilio') }}</router-link></li>

                    <li><router-link to="/settings/notifications#slack" :class="{ 'active': $route.fullPath.includes('slack') }" class="notification-submenu" data-filter="slack"> <img :src="$tfhb_url+'/assets/images/Slack.svg'" alt=""> {{ $tfhb_trans('Slack') }}</router-link></li>

            </ul>  
        </nav>
        <div class="tfhb-content-wrap">
            <!-- Gmail -->
            <div class="tfhb-notification-button-tabs tfhb-flexbox tfhb-mb-16" v-if="!$route.params.id && $route.hash === '#email'">
                <button @click="changeTab('host')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='host' ? 'active' : ''" ><Icon name="UserRound" size=15 /> {{ $tfhb_trans('To Host') }} </button>
                <button @click="changeTab('attendee')" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='attendee' ? 'active' : ''"><Icon name="UsersRound" size=15 /> {{ $tfhb_trans('To Attendee') }} </button>
            </div>

            <div v-if="currentTabs=='host' && !$route.params.id && $route.hash === '#email'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box" :class="{ 'tfhb-skeleton': ntskeleton }"> 
 
 
                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Confirmation to Host')"   
                    :label="$tfhb_trans('Booking Confirmation')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_confirmation"  
                    :isSingle="true"
                    categoryKey="host"
                    emailKey="booking_confirmation"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Pending to Host')"  
                    :label="$tfhb_trans('Booking Pending')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_pending"  
                    :isSingle="true"
                    categoryKey="host"
                    emailKey="booking_pending"
                /> 
                <!-- Single Integrations  -->


                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Cancel to Host')"  
                    :label="$tfhb_trans('Booking Cancel')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_cancel"  
                    :isSingle="true"
                    categoryKey="host"
                    emailKey="booking_cancel"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Reschedule to Host')"  
                    :label="$tfhb_trans('Booking Reschedule')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_reschedule"  
                    :isSingle="true"
                    categoryKey="host"
                    emailKey="booking_reschedule"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Reminder to Host')"  
                    :label="$tfhb_trans('Booking Reminder')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.host.booking_reminder"  
                    :isSingle="true"
                    categoryKey="host"
                    emailKey="booking_reminder"
                /> 
                <!-- Single Integrations  -->
                <!-- <router-view :notifications="Notification" /> -->
 
            </div> 

            <div v-if="currentTabs=='attendee' && !$route.params.id && $route.hash === '#email'"  class="tfhb-notification-wrap tfhb-notification-host tfhb-admin-card-box" :class="{ 'tfhb-skeleton': ntskeleton }"> 

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Confirmation to Attendee')"  
                    :label="$tfhb_trans('Booking Confirmation')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_confirmation"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_confirmation"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Pending to Attendee')"  
                    :label="$tfhb_trans('Booking Pending')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_pending"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_pending"
                /> 
                <!-- Single Integrations  -->


                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Cancel to Attendee')"  
                    :label="$tfhb_trans('Booking Cancel')"  
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_cancel"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_cancel"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Reschedule to Attendee')"  
                    :label="$tfhb_trans('Booking Reschedule')"
                    :data="Notification.attendee.booking_reschedule"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_reschedule"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Reminder to Attendee')"   
                    :label="$tfhb_trans('Booking Reminder')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_reminder"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_reminder"
                /> 
                <!-- Single Integrations  -->
 
 
            </div> 

            <!-- Telegram Notification -->
            <HbInfoBox name="first-modal" v-if="!$route.params.id && $route.hash === '#telegram' && Integration.telegram==''">
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
            <div v-if="!$route.params.id && $route.hash === '#telegram'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box" :class="!Integration.telegram ? 'tfhb-pro' : ''"> 
 
            <!-- Single Notification  -->
            <MailNotifications 
                :title="$tfhb_trans('Booking Confirmation to Host')"  
                :label="$tfhb_trans('Booking Confirmation')" 
                @update-notification="UpdateNotification"
                :data="Notification.telegram.booking_confirmation"  
                :isSingle="true"
                categoryKey="telegram"
                emailKey="booking_confirmation"
            /> 
            <!-- Single Integrations  -->


            <!-- Single Notification  -->
            <MailNotifications 
                :title="$tfhb_trans('Booking Cancel to Host')"  
                :label="$tfhb_trans('Booking Cancel')" 
                @update-notification="UpdateNotification"
                :data="Notification.telegram.booking_cancel"  
                :isSingle="true"
                categoryKey="telegram"
                emailKey="booking_cancel"
            /> 
            <!-- Single Integrations  -->

            <!-- Single Notification  -->
            <MailNotifications 
                :title="$tfhb_trans('Booking Reschedule to Host')"
                :label="$tfhb_trans('Booking Reschedule')" 
                @update-notification="UpdateNotification"
                :data="Notification.telegram.booking_reschedule"  
                :isSingle="true"
                categoryKey="telegram"
                emailKey="booking_reschedule"
            /> 
            <!-- Single Integrations  -->

            </div> 

            <!-- Twilio Notification -->
            <HbInfoBox name="first-modal" v-if="!$route.params.id && $route.hash === '#twilio' && Integration.twilio==''">
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
            <div v-if="!$route.params.id && $route.hash === '#twilio'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box" :class="!Integration.twilio ? 'tfhb-pro' : ''"> 
 
                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Confirmation to Host')"  
                    :label="$tfhb_trans('Booking Confirmation')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.twilio.booking_confirmation"  
                    :isSingle="true"
                    categoryKey="twilio"
                    emailKey="booking_confirmation"
                /> 
                <!-- Single Integrations  -->


                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Cancel to Host')" 
                    :label="$tfhb_trans('Booking Cancel')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.twilio.booking_cancel"  
                    :isSingle="true"
                    categoryKey="twilio"
                    emailKey="booking_cancel"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    :title="$tfhb_trans('Booking Reschedule to Host')"
                    :label="$tfhb_trans('Booking Reschedule')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.twilio.booking_reschedule"  
                    :isSingle="true"
                    categoryKey="twilio"
                    emailKey="booking_reschedule"
                /> 
                <!-- Single Integrations  -->

            </div> 

            <!-- Slack Notification -->
            <HbInfoBox name="first-modal" v-if="!$route.params.id && $route.hash === '#slack' && Integration.slack==''">
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
            <div v-if="!$route.params.id && $route.hash === '#slack'" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box" :class="!Integration.slack ? 'tfhb-pro' : ''"> 
 
            <!-- Single Notification  -->
            <MailNotifications 
                :title="$tfhb_trans('Booking Confirmation to Host')"  
                :label="$tfhb_trans('Booking Confirmation')" 
                @update-notification="UpdateNotification"
                :data="Notification.slack.booking_confirmation"  
                :isSingle="true"
                categoryKey="slack"
                emailKey="booking_confirmation"
            /> 
            <!-- Single Integrations  -->


            <!-- Single Notification  -->
            <MailNotifications 
                :title="$tfhb_trans('Booking Cancel to Host')" 
                :label="$tfhb_trans('Booking Cancel')" 
                @update-notification="UpdateNotification"
                :data="Notification.slack.booking_cancel"  
                :isSingle="true"
                categoryKey="slack"
                emailKey="booking_cancel"
            /> 
            <!-- Single Integrations  -->

            <!-- Single Notification  -->
            <MailNotifications 
                :title="$tfhb_trans('Booking Reschedule to Host')"
                :label="$tfhb_trans('Booking Reschedule')" 
                @update-notification="UpdateNotification"
                :data="Notification.slack.booking_reschedule"  
                :isSingle="true"
                categoryKey="slack"
                emailKey="booking_reschedule"
            /> 
            <!-- Single Integrations  -->

            </div> 

            <router-view 
            v-if="$route.params" 
            :mediaurl="$tfhb_url"
            />

        </div>
    </div>
 
</template>