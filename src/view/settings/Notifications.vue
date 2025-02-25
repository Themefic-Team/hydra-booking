<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 
const route = useRoute();
const router = useRouter();

// import Form Field 
import MailNotifications from '@/components/notifications/MailNotifications.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbButton from '@/components/form-fields/HbButton.vue'


//  Load Time Zone 
const skeleton = ref(true);   
const currentTabs = ref('host');
const popup = ref(false);
const update_preloader = ref(false);

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
    
    },
    email: {
        site_title: 'HydraBooking',
        email_logo: '',
        facebook: '',
        twitter: '',
        youtube: ''
    }
});

// Update Notification 
const changeTab = (e) => {  
    // get data-tab attribute value of clicked button
    const tab = e.target.getAttribute('data-tab'); 
    currentTabs.value = tab;
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
            Notification.email = response.data.notification_settings.email ? response.data.notification_settings.email : Notification.email;
            
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
                'capability': 'tfhb_manage_options'
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
                <h1 class="tfhb-capitalize">{{ $route.params.type }}</h1> 
                <p class="tfhb-capitalize">{{ $route.params.id.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') }}</p>
            </div>
            <div class="tfhb-admin-title tfhb-m-0" v-else> 
                <h1 >{{ $tfhb_trans('Notifications') }}</h1> 
                <p>{{ $tfhb_trans('Organize booking confirmation/cancel/reschedule/reminder notification for host and attendee') }}</p>
            </div>
            <div class="thb-admin-btn"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=15 /></a>
            </div> 
        </div>
        <div class="tfhb-content-wrap">
            <!-- Gmail -->
            <div class="tfhb-notification-button-tabs tfhb-flexbox tfhb-mb-16" v-if="!$route.params.id">
                <button @click="changeTab" data-tab="host" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn"  :class="currentTabs=='host' ? 'active' : ''" ><Icon name="UserRound" size=15 /> {{ $tfhb_trans('To Host') }} </button>
                <button @click="changeTab"  data-tab="attendee" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='attendee' ? 'active' : ''"><Icon name="UsersRound" size=15 /> {{ $tfhb_trans('To Attendee') }} </button>
                <button @click="changeTab"  data-tab="email" class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn" :class="currentTabs=='email' ? 'active' : ''"><Icon name="Mail" size=15 /> {{ $tfhb_trans('Email Settings') }} </button>
            </div>
 
            <div v-if="currentTabs=='host' && !$route.params.id" class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box "> 
 
                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Host for Booking Confirmation" 
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
                    title="Send Email to Host for Booking Pending" 
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
                    title="Send Email to Host for Booking Cancels" 
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
                    title="Send Email to Host" 
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
                    title="Send Email to Host" 
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
            <div v-if="currentTabs=='attendee' && !$route.params.id"  class="tfhb-notification-wrap tfhb-notification-host tfhb-admin-card-box "> 

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
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
                    title="Send Email to Attendee" 
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
                    title="Send Email to Attendee" 
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
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Reschedule')"
                    :data="Notification.attendee.booking_reschedule"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_reschedule"
                /> 
                <!-- Single Integrations  -->

                <!-- Single Notification  -->
                <MailNotifications 
                    title="Send Email to Attendee" 
                    :label="$tfhb_trans('Booking Reminder')" 
                    @update-notification="UpdateNotification"
                    :data="Notification.attendee.booking_reminder"  
                    :isSingle="true"
                    categoryKey="attendee"
                    emailKey="booking_reminder"
                /> 
                <!-- Single Integrations  -->
 
 
            </div> 
            
            <div v-if="currentTabs=='email' && !$route.params.id"  class="tfhb-notification-wrap tfhb-notification-email tfhb-admin-card-box tfhb-flexbox"> 

                <HbText  
                    v-model="Notification.email.site_title"  
                    required= "true"  
                    :label="$tfhb_trans('Site Title')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Type your Site Title')" 
                /> 

                <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" style="border-color: #273F2B;"> 
                    <div class="tfhb-single-form-field-wrap tfhb-flexbox ">
                        <div class="tfhb-field-image" >  
                            <img v-if="Notification.email.email_logo != ''"  class='notification_email_logo_display'  :src="Notification.email.email_logo">
                            
                            <img v-else class='notification_email_logo_display'  :src="$tfhb_url+'/assets/images/images-icon.png'" >
                            <button class="tfhb-image-btn" @click="UploadChangeMobileDashboardLogo">{{ $tfhb_trans('Change') }}</button> 
                            <input  type="text"  :v-model="Notification.email.email_logo"   />  
                        </div>
                        <div class="tfhb-image-box-content">  
                        <h4 >{{ $tfhb_trans('Email Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
                        
                        <p class="tfhb-m-0">{{ $tfhb_trans('This logo will used for the email template.') }}</p>
                        </div>
                    </div>
                </div>

                <HbText  
                    v-model="Notification.email.facebook"  
                    required= "true"  
                    :label="$tfhb_trans('Facebook Profile')"  
                    selected = "1"
                /> 
                <HbText  
                    v-model="Notification.email.twitter"  
                    required= "true"  
                    :label="$tfhb_trans('X (previously twitter)')"  
                    selected = "1"
                /> 
                <HbText  
                    v-model="Notification.email.youtube"  
                    required= "true"  
                    :label="$tfhb_trans('Youtube')"  
                    selected = "1"
                /> 
                <HbButton  
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="UpdateNotification"
                    :buttonText="$tfhb_trans('Update')" 
                    icon="ChevronRight"
                    hover_icon="ArrowRight"
                    :hover_animation="true"
                    :pre_loader="update_preloader"
                />  
            </div> 

            <router-link class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-mb-24" :to="{ name: 'SettingsNotifications' }" v-if="$route.params.id">
                <Icon name="ArrowLeft" :width="20"/>
                {{ $tfhb_trans('Back') }}
            </router-link>
           
            <router-view 
            v-if="$route.params" 
            @update-notification="UpdateNotification" 
            />

        </div>
    </div>
 
</template>