<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import { useRouter, useRoute, RouterView } from 'vue-router' 
import axios from 'axios'  
import { toast } from "vue3-toastify"; 

import HbPreloader from '@/components/icon/HbPreloader.vue'
import { Availability } from '@/store/availability';
import ShareMeeting from '@/components/meetings/ShareMeeting.vue';
import useValidators from '@/store/validator'
const { errors } = useValidators();

const route = useRoute();
const router = useRouter();
const skeleton = ref(true);
const timeZone = reactive({});
const meetingCategory = reactive({});
const wcProduct = reactive({});
const integrations = reactive({
    google_calendar_status : 1,
    zoom_meeting_status : 1,
    cf7_status : 1,
    fluent_status : 1,
    forminator_status : 1,
    gravity_status : 1,
    fluent_crm_status : 1,
    zoho_status : 1,
});
const formsList = reactive({});

const local_time_zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
const meetingData = reactive({
    id: 0,
    user_id: 0,
    slug: '',
    host_id: '',
    post_id: '',
    title: '',
    description: '',
    meeting_type: '',
    duration: '',
    custom_duration: 5,
    meeting_locations: [
        {
        location:'',
        address:''
        }
    ],
    meeting_category: '',
    availability_range_type: 'indefinitely',
    availability_range: {
        start: '',
        end: ''
    },
    availability_type: 'settings',
    availability_id : '',
    availability_custom: 
        {
        title: '',
        time_zone: local_time_zone,
        date_status: 0,
        time_slots: [
            { 
                day: 'Monday',
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    },   
                ]
            },
            { 
                day: 'Tuesday', 
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    }
                ]
            },
            { 
                day: 'Wednesday', 
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    }
                ]
            },
            { 
                day: 'Thursday', 
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    }
                ]
            },
            { 
                day: 'Friday', 
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    }
                ]
            },
            { 
                day: 'Saturday', 
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    }
                ]
            },
            { 
                day: 'Sunday', 
                status: 1,
                times: [
                    {
                        start: '09:00',
                        end: '17:00',
                    }
                ]
            }
        ],
        date_slots: [
        ]
    },
    buffer_time_before: '5',
    buffer_time_after: '5',
    booking_frequency: [
        {
            limit: 5,
            times:'days'
        }
    ],
    meeting_interval: '10',
    recurring_status: 1,
    recurring_repeat:[
        {
            limit: 1,
            times:'Year'
        }
    ],
    recurring_maximum: '',
    attendee_can_cancel: 1,
    attendee_can_reschedule: 1, 
    questions_type: 'custom',
    form_type: '',
    form_id: '',
    questions: [
        {
            label: 'name',
            type:'Text',
            placeholder:'Name',
            options: [],
            required: 1
        },
        {
            label: 'email',
            type:'Email',
            options: [],
            placeholder:'Email',
            options: [],
            required: 1
        },
        {
            label: 'address',
            type:'Text', 
            placeholder:'Address',
            options: [],
            required: 1
        }
    ],
    notification: {
        host: {
            booking_confirmation: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
            booking_pending: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
            booking_cancel: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
            booking_reschedule: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
            booking_reminder: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
        },
        attendee : {
            booking_confirmation: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',
            },
            booking_pending: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',
            },
            booking_cancel: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
            booking_reschedule: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
            booking_reminder: {
                status : 1,
                template : 'default',
                form : '',
                subject : '',
                body : '',

            },
        }
    },
    payment_status: 1,
    meeting_price: '',
    payment_currency: '',
    payment_method: '',
    permalink	: '',
    payment_meta: {
        product_id: '',
    },
    webhook: '',
    integrations: '',
    max_book_per_slot: 1,
    is_display_max_book_slot: 0,
    mailchimp: '',
    fluentcrm: '',
    zohocrm: '',
    setting_webhook: ''
});


// Add more Location
function addMoreLocations(){
    meetingData.meeting_locations.push({
    location:'',
    address:'',
  })
}
// Remove Location
const removeLocations = (key) => {
    meetingData.meeting_locations.splice(key, 1);
}

// Add new time slot
const addAvailabilityTime = (key) => {
    meetingData.availability_custom.time_slots[key].times.push({
        start: '09:00',
        end: '17:00',
    });
}

// Remove time slot
const removeAvailabilityTime = (key, tkey = null) => {
    meetingData.availability_custom.time_slots[key].times.splice(tkey, 1);
}

// Add new date slot
const addAvailabilityDate = (key) => {
    meetingData.availability_custom.date_slots.push({
        date: '',
        available: '',
        times: [
            {
                start: '09:00',
                end: '17:00',
            }
        ]
    });
}

// Remove date slot 
const removeAvailabilityTDate = (key) => {
    meetingData.availability_custom.date_slots.splice(key, 1);
}

//add Overrides Time
const addOverridesTime = (key) => {
    meetingData.availability_custom.date_slots[key].times.push({
        start: '09:00',
        end: '17:00',
    });
}

// Remove Overrides time slot
const removeOverridesTime = (key, tkey = null) => {
    meetingData.availability_custom.date_slots[key].times.splice(tkey, 1);
}

// Meeting Type
const AvailabilityTabs = (type) => {
    meetingData.availability_type = type
}

const meetingId = route.params.id;

const fetchMeeting = async () => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/'+meetingId, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status == true) { 
            // Time Zone = 
            timeZone.value = response.data.time_zone;   
            integrations.google_calendar_status = response.data.integrations.google_calendar_status && response.data.integrations.google_calendar_status == 1 ? false : true;  
            integrations.zoom_meeting_status = response.data.integrations.zoom_meeting_status && response.data.integrations.zoom_meeting_status == 1  ? false : true;  
            integrations.cf7_status = response.data.integrations.cf7_status && response.data.integrations.cf7_status == 1  ? false : true;  
            integrations.fluent_status = response.data.integrations.fluent_status && response.data.integrations.fluent_status == 1  ? false : true;  
            integrations.forminator_status = response.data.integrations.forminator_status && response.data.integrations.forminator_status == 1  ? false : true;  
            integrations.gravity_status = response.data.integrations.gravity_status && response.data.integrations.gravity_status == 1  ? false : true;  
            integrations.webhook_status = response.data.integrations.webhook_status && response.data.integrations.webhook_status == 1  ? false : true;  
            integrations.fluent_crm_status = response.data.integrations.fluent_crm_status && response.data.integrations.fluent_crm_status == 1  ? false : true;  
            integrations.zoho_crm_status = response.data.integrations.zoho_crm_status && response.data.integrations.zoho_crm_status == 1  ? false : true;  

            wcProduct.value = response.data.wc_product;  
            formsList.value = response.data.formsList;  
            meetingCategory.value = response.data.meeting_category;

            meetingData.id = response.data.meeting.id
            meetingData.user_id = response.data.meeting.user_id
            meetingData.host_id = response.data.meeting.host_id && response.data.meeting.host_id!=0 ? response.data.meeting.host_id : '';
            meetingData.slug = response.data.meeting.slug  ? response.data.meeting.slug : response.data.meeting.title;
            meetingData.post_id = response.data.meeting.post_id
            meetingData.title = response.data.meeting.title
            meetingData.description = response.data.meeting.description
            meetingData.meeting_type = response.data.meeting.meeting_type
            meetingData.duration = response.data.meeting.duration
            meetingData.custom_duration = response.data.meeting.custom_duration
            meetingData.meeting_category = response.data.meeting.meeting_category
            meetingData.payment_method = response.data.meeting.payment_method
            meetingData.max_book_per_slot = response.data.meeting.max_book_per_slot
            meetingData.is_display_max_book_slot = response.data.meeting.is_display_max_book_slot

            if(response.data.meeting.meeting_locations){
                meetingData.meeting_locations = JSON.parse(response.data.meeting.meeting_locations)
            }

            meetingData.availability_range_type = response.data.meeting.availability_range_type ? response.data.meeting.availability_range_type : 'indefinitely'

            meetingData.availability_range = response.data.meeting.availability_range ? JSON.parse(response.data.meeting.availability_range) : {}
           
            if(response.data.meeting.availability_custom){
                 
                meetingData.availability_custom = JSON.parse(response.data.meeting.availability_custom)
             
                
            } 
            // meetingData.availability_custom.time_zone = Availability.GeneralSettings.time_zone ? Availability.GeneralSettings.time_zone : '';

            meetingData.availability_custom.time_slots = Availability.GeneralSettings.week_start_from ?  Availability.RearraingeWeekStart(Availability.GeneralSettings.week_start_from, meetingData.availability_custom.time_slots) : meetingData.availability_custom.time_slots;
            
            if(response.data.meeting.availability_type){
                meetingData.availability_type = response.data.meeting.availability_type
            }
            meetingData.availability_id = response.data.meeting.availability_id
            meetingData.buffer_time_before = response.data.meeting.buffer_time_before
            meetingData.buffer_time_after = response.data.meeting.buffer_time_after
            meetingData.meeting_interval = response.data.meeting.meeting_interval
            if(response.data.meeting.recurring_status){
                meetingData.recurring_status = response.data.meeting.recurring_status
            }
            meetingData.recurring_maximum = response.data.meeting.recurring_maximum

            if(response.data.meeting.recurring_repeat){
                meetingData.recurring_repeat = JSON.parse(response.data.meeting.recurring_repeat)
            }
            if(response.data.meeting.booking_frequency){
                meetingData.booking_frequency = JSON.parse(response.data.meeting.booking_frequency)
            }

            meetingData.attendee_can_cancel = response.data.meeting.attendee_can_cancel
            meetingData.attendee_can_reschedule = response.data.meeting.attendee_can_reschedule

            if(response.data.meeting.questions_type){
                meetingData.questions_type = response.data.meeting.questions_type
            }
            if(response.data.meeting.questions_form_type){
                meetingData.questions_form_type = response.data.meeting.questions_form_type
            }

            if(response.data.meeting.questions){
                meetingData.questions = JSON.parse(response.data.meeting.questions)
            }
            if(response.data.meeting.questions_form){
                meetingData.questions_form = response.data.meeting.questions_form
            }
            if(response.data.meeting.notification && "string" == typeof response.data.meeting.notification){
                meetingData.notification = JSON.parse(response.data.meeting.notification)
            }
            if(response.data.meeting.notification && "object" == typeof response.data.meeting.notification){
                meetingData.notification = response.data.meeting.notification
            }
            if(response.data.meeting.payment_status){
                meetingData.payment_status = response.data.meeting.payment_status
            }
            meetingData.meeting_price = response.data.meeting.meeting_price
            meetingData.payment_currency = response.data.meeting.payment_currency

            if(response.data.meeting.payment_meta && "string" == typeof response.data.meeting.payment_meta){
                meetingData.payment_meta = JSON.parse(response.data.meeting.payment_meta)
            }
            if(response.data.meeting.payment_meta && "object" == typeof response.data.meeting.payment_meta){
                meetingData.payment_meta = response.data.meeting.payment_meta
            }

            meetingData.webhook = response.data.meeting.webhook ? JSON.parse(response.data.meeting.webhook) : '';
            meetingData.integrations = response.data.meeting.integrations ? JSON.parse(response.data.meeting.integrations) : '';
            meetingData.mailchimp = response.data.mailchimp ? response.data.mailchimp : '';
            meetingData.fluentcrm = response.data.fluentcrm ? response.data.fluentcrm : '';
            meetingData.zohocrm = response.data.zohocrm ? response.data.zohocrm : '';
            meetingData.setting_webhook = response.data.setting_webhook ? response.data.setting_webhook : '';
            meetingData.permalink	= response.data.meeting.permalink ? response.data.meeting.permalink : '';

            skeleton.value = false
        }else{ 
            router.push({ name: 'MeetingsLists' });
        }
    } catch (error) {
        console.log(error);
    } 
} 

onBeforeMount(() => {  
    fetchMeeting();
    Availability.getGeneralSettings(); 
   
});

const update_preloader = ref(false);
const UpdateMeetingData = async (validator_field) => {
    
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!meetingData[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!meetingData[fieldParts[0]][fieldParts[1]]){
                errors[fieldParts[0]+'___'+[fieldParts[1]]] = 'Required this field';
            }
        }
            
        });
    }

    // Errors Checked
    const isEmpty = Object.keys(errors).length === 0;
    if(!isEmpty){ 
        toast.error('Fill Up The Required Fields', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return
    }

    update_preloader.value = true;
    // Api Submission
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/details/update', meetingData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status == true) { 
            meetingData.slug = response.data.meeting.slug; 
            // toast.success(response.data.message, {
            //         position: 'bottom-right', // Set the Fdesired position
            //         "autoClose": 1500,
            //     });
            let nextRouteName = '';
            if("MeetingsCreateDetails"==route.name){ 
                nextRouteName = 'MeetingsCreateAvailability';
            }
            if("MeetingsCreateAvailability"==route.name){ 
                nextRouteName = 'MeetingsCreateLimits';
            }
            if("MeetingsCreateLimits"==route.name){ 
                nextRouteName = 'MeetingsCreateQuestions';
            }
            if("MeetingsCreateQuestions"==route.name){ 
                nextRouteName = 'MeetingsCreateNotifications';
            }
            if("MeetingsCreateNotifications"==route.name){
                // router.push({ name: 'MeetingsCreateIntegrations' });
                nextRouteName = 'MeetingsCreateIntegrations';
            }
            if("MeetingsCreateIntegrations"==route.name){ 
                nextRouteName = 'MeetingsCreatePayment';
            }
            if("MeetingsCreatePayment"==route.name){ 
                sharePopupData();
                // window.open(meetingData.permalink, '_blank'); 


            }
            if (nextRouteName) {
                router.push({ name: nextRouteName }).then(() => {
                    nextTick(() => {
                        toast.success(response.data.message, {
                            position: 'bottom-right',
                            autoClose: 1500,
                        });
                    });
                });
            }else{
                toast.success(response.data.message, {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
            }
            update_preloader.value = false;
            // if("MeetingsCreateIntegrations"==route.name){
            //     router.push({ name: 'MeetingsCreatePayment' });
            // }
        }else{ 
            toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
        }
    } catch (error) {
        console.log(error);
    } 
}
const beck_to_prev = ref(false);
const TfhbPrevNavigator = () => { 
    beck_to_prev.value = true;
   

    setTimeout(() => {
        if("MeetingsCreateDetails"==route.name){
        router.push({ name: 'MeetingsLists' });
        }
        if("MeetingsCreateAvailability"==route.name){
            router.push({ name: 'MeetingsCreateDetails' });
        }
        if("MeetingsCreateLimits"==route.name){
            router.push({ name: 'MeetingsCreateAvailability' });
        }
        if("MeetingsCreateQuestions"==route.name){
            router.push({ name: 'MeetingsCreateLimits' });
        }
        if("MeetingsCreateNotifications"==route.name){
            router.push({ name: 'MeetingsCreateQuestions' });
        }
        if("MeetingsCreatePayment"==route.name){
            router.push({ name: 'MeetingsCreateNotifications' });
        }
        if("MeetingsCreateWebhook"==route.name){
            router.push({ name: 'MeetingsCreatePayment' });
        }
        beck_to_prev.value = false;
    }, 500);
}

const sharePopup = reactive({
    value: false
})
// Share Popup Data
const shareData = reactive({
    title: '',
    time: '',
    meeting_type: '',
    share_type: 'link',
    link: '',
    shortcode: '',
    embed: ''
})
const sharePopupData = () => { 
    shareData.share_type = 'link'
    shareData.title = meetingData.title
    shareData.time = meetingData.duration
    shareData.meeting_type = meetingData.meeting_type
    shareData.shortcode = '[hydra_booking id="'+meetingData.id+'"]'
    shareData.link = meetingData.permalink
    shareData.embed = '<iframe src="'+ tfhb_core_apps.admin_url +'/?hydra-booking=meeting&meeting-id='+meetingData.id+'&type=iframe" title="description"  height="600" width="100%" ></iframe>'

    // Popup open
    sharePopup.value = true; 
}

// trunkate string 
const truncateString = (str, num) => {
    if (str.length <= num) {
        return str
    }
    return str.slice(0, num) + '...'
}
</script>

<template> 
    <div class="tfhb-meeting-create" :class="{ 'tfhb-skeleton': skeleton }">
        <div class="tfhb-meeting-create-notice tfhb-flexbox tfhb-mb-32 tfhb-justify-between">
            <div class="tfhb-meeting-heading-wrap tfhb-flexbox tfhb-gap-8">
                <div class="prev-navigator" @click="TfhbPrevNavigator()">
                    <Icon v-if="beck_to_prev == false" name="ArrowLeft" size=20 /> 
                    <HbPreloader v-else color="#2E6B38" />
                </div>
                <div class="tfhb-meeting-heading tfhb-flexbox">
                  
                    <h3 v-if="meetingData.title != '' && meetingData.title != null">{{ truncateString(meetingData.title, 110) }}</h3>
                    <h3 v-else-if="meetingData.type == 'one-to-one'" >{{ __('Create One-to-One booking', 'hydra-booking') }}</h3>
                    <h3 v-else >{{ __('Create One-to-Group booking', 'hydra-booking') }}</h3>
                </div> 
                <!-- <div  class="tfhb-meeting-subtitle">
                    {{ __('Create and manage booking/appointment form', 'hydra-booking') }}
                </div> -->
            </div>
           
            <div class="thb-admin-btn right"> 
                <button  @click="sharePopupData()" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ __('Share', 'hydra-booking') }}  <Icon name="ArrowUpRight" size=20 /></button>
            </div> 
        </div>
        <nav class="tfhb-booking-tabs tfhb-meeting-tabs tfhb-mb-32"> 
            <ul>
                <!-- to route example like meetings/create/13/details -->
                
                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/details'" exact :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/details' }">{{ __('Details', 'hydra-booking') }}</router-link></li> 
                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/availability'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/availability' }">{{ __('Availability', 'hydra-booking') }}</router-link></li>  
                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/limits'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/limits' }">{{ __('Limits', 'hydra-booking') }}</router-link></li>  
                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/questions'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/questions' }"> {{ __('Questions', 'hydra-booking') }}</router-link></li>  
                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/notifications'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/notifications' }"> {{ __('Notifications', 'hydra-booking') }}</router-link></li>   
                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/integrations'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/integrations' }">{{ __('Integrations', 'hydra-booking') }}</router-link></li> 
                <!-- <li><router-link :to="'/meetings/single/'+ $route.params.id +'/webhook'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/webhook' }">{{ __('Webhook', 'hydra-booking') }}</router-link></li>   -->

                <li><router-link :to="'/meetings/single/'+ $route.params.id +'/payment'" :class="{ 'active': $route.path === '/meetings/single/'+ $route.params.id +'/payment' }">{{ __('Payment', 'hydra-booking') }}</router-link></li> 

            </ul>  
        </nav>

        <div class="tfhb-hydra-dasboard-content">   
            <router-view 
            :meetingId ="meetingId" 
            :meeting="meetingData" 
            :integrations="integrations" 
            :timeZone="timeZone.value" 
            :wcProduct="wcProduct.value" 
            :formsList="formsList" 
            :update_preloader="update_preloader" 
            :meetingCategory="meetingCategory" 
            @add-more-location="addMoreLocations" 
            @remove-meeting-location="removeLocations" 
            @update-meeting="UpdateMeetingData" 
            @availability-time="addAvailabilityTime"
            @availability-time-del="removeAvailabilityTime"
            @availability-date="addAvailabilityDate"
            @availability-date-del="removeAvailabilityTDate"
            @availability-tabs="AvailabilityTabs"
            @add-overrides-time="addOverridesTime"
            @remove-overrides-time="removeOverridesTime"
            />
            <!-- Share Meeting -->
            <ShareMeeting :shareData="shareData" :sharePopup="sharePopup"  />
        </div> 
    </div>
</template>

<style scoped>

</style>