<script setup> 
import { __ } from '@wordpress/i18n'; 
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount, } from 'vue';
import { useRoute, useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 


// import Form Field 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';   
import HbText from '@/components/form-fields/HbText.vue' 
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbEditor from '@/components/form-fields/HbEditor.vue';
import HbButton from '@/components/form-fields/HbButton.vue'
import Editor from 'primevue/editor';
const route = useRoute();
//  Load Time Zone 
const skeleton = ref(false);
const emit = defineEmits(["update-notification"]); 

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

const meetingShortcode = ref([
    '{{meeting.title}}',
    '{{meeting.date}}', 
    '{{meeting.location}}', 
    '{{meeting.duration}}', 
    '{{meeting.time}}', 
    '{{host.name}}', 
    '{{host.email}}',  
    '{{attendee.name}}', 
    '{{attendee.email}}', 
    '{{attendee.additional_data}}',
    '{{wp.admin_email}}',
    "{{booking.cancel_reason}}",
    "{{booking.cancel_link}}",
    "{{booking.full_start_end_host_timezone}}",
    "{{booking.start_date_time_for_host}}",
    "{{booking.start_date_time_for_attendee}}",
    "{{booking.full_start_end_attendee_timezone}}",
    "{{booking.rescheduled_reason}}",
    "{{booking.rescheduled_link}}",
    "{{booking.location_details_html}}",
])
const copyShortcode = (value) => { 
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action
    toast.success(value + ' is Copied');
}

const preloader = ref(false);

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
    preloader.value = true;
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

            preloader.value = false;
            
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });

            preloader.value = false;
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
    <!-- Single Notification  -->
    <div class="tfhb-notification-single tfhb-flexbox tfhb-justify-between">
        <div class="tfhb-swicher-wrap  tfhb-flexbox"> 
            
            <!-- Time format -->
            <HbDropdown 
                    
                v-model="Notification[$route.params.type][$route.params.id].template"  
                required= "true" 
                :label="$tfhb_trans('Select Template')"  
                width="50"
                :selected = "1"
                :placeholder="$tfhb_trans('Select Template')"   
                :option = "[
                    {'name': 'Default', 'value': 'default'},  
                ]"  
            /> 
            <!-- Time format --> 
            <HbText  
                v-model="Notification[$route.params.type][$route.params.id].from"   
                type="email"
                width="50"
                :label="$tfhb_trans('From')"  
                selected = "1"
                :placeholder="$tfhb_trans('Enter From Email')"  
            /> 

            <HbText  
                v-model="Notification[$route.params.type][$route.params.id].subject"  
                required= "true"  
                :label="$tfhb_trans('Subject')"  
                selected = "1"
                type = "text"
                :placeholder="$tfhb_trans('Enter Mail Subject')"  
            /> 

            
            <div class="tfhb-single-form-field" style="width: 100%;"  >
                <div class="tfhb-single-form-field-wrap tfhb-field-input">
                    <!--if has label show label with tag else remove tags  --> 
                    <label for="">{{ $tfhb_trans('Mail Body') }}</label>  
                    <Editor 
                        v-model="Notification[$route.params.type][$route.params.id].body"  
                        :placeholder="$tfhb_trans('Mail Body')"    
                        editorStyle="height: 250px" 
                    />
                </div> 
            </div> 
            <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8"> 
                <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>

            </div>

            <HbButton  
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                @click="UpdateNotification"
                :buttonText="$tfhb_trans('Update')" 
                icon="ChevronRight"
                hover_icon="ArrowRight"
                :hover_animation="true"
                :pre_loader="preloader"
            />  

        </div>

    </div>
    <!-- Single Integrations  -->

 
</template>