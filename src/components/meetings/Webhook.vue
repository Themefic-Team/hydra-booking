<script setup>
import { __ } from '@wordpress/i18n';
import {ref, reactive} from 'vue'
import axios from 'axios'  
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue'  
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbButton from '@/components/form-fields/HbButton.vue';

 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },

});

const webhookList = ref(true);
const webhookcreate = ref(false);
const webhookData = reactive({
    'meeting_id' : props.meetingId,
    'key': '',
    'webhook': '',
    'url': '',
    'request_method': '',
    'request_format': '',
    'events': '',
    'request_body': 'all',
    'request_header': 'no',
    'headers': [
        {
            'key': '',
            'value': ''
        }
    ],
    'bodys': [
        {
            'name': '',
            'value': ''
        }
    ],
    'status': '',
});


const updateWebHook = async () => {
    // Api Submission
    try { 
        const response = await axios.post(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/meetings/webhook/update', webhookData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status == true) { 
            toast.success(response.data.message); 
            props.meeting.webhook = response.data.webhook ? JSON.parse(response.data.webhook) : '';

            webhookcreate.value = false;
            webhookList.value = true;
        }else{
            toast.error(response.data.message); 
        }
    } catch (error) {
        console.log(error);
    } 
}

const deleteWebHook = async (key) => {
    const data = {
        key: key,
        meeting_id: props.meetingId
    };

    try { 
        const response = await axios.post(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/meetings/webhook/delete', data, {
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

            props.meeting.webhook = response.data.webhook ? JSON.parse(response.data.webhook) : '';
        }
    } catch (error) {
        console.log(error);
    }
}

const addNewWebHook = () => {
    webhookList.value = false;
    webhookcreate.value = true;

    webhookData.key = '';
    webhookData.webhook = '';
    webhookData.url = '';
    webhookData.request_method = '';
    webhookData.request_format = '';
    webhookData.events = '';
    webhookData.request_body = 'all';
    webhookData.request_header = 'no';
    webhookData.status = '';
    webhookData.headers = [
        {
            'key': '',
            'value': ''
        }
    ];
    webhookData.bodys = [
        {
            'name': '',
            'value': ''
        }
    ];
}

const backtoWebHookList = () => {
    webhookcreate.value = false;
    webhookList.value = true;
}

// edit webhook
const editWebHook = (data, key) => {
    webhookData.key = key;
    webhookData.meeting_id = props.meetingId;
    webhookData.webhook = data.webhook;
    webhookData.url = data.url;
    webhookData.request_method = data.request_method;
    webhookData.request_format = data.request_format;
    webhookData.events = data.events;
    webhookData.request_body = data.request_body;
    webhookData.request_header = data.request_header;
    webhookData.status = data.status;
    webhookData.headers = data.headers;
    webhookData.bodys = data.bodys;

    webhookList.value = false;
    webhookcreate.value = true;
}

// update webhook status
const updateHookStatus = (e, data, key) => {

    webhookData.key = key;
    webhookData.meeting_id = props.meetingId;
    webhookData.webhook = data.webhook;
    webhookData.url = data.url;
    webhookData.request_method = data.request_method;
    webhookData.request_format = data.request_format;
    webhookData.events = data.events;
    webhookData.request_body = data.request_body;
    webhookData.request_header = data.request_header;
    webhookData.status = e.target.checked ? 1 : 0;
    webhookData.headers = data.headers;
    webhookData.bodys = data.bodys;

    updateWebHook();
}

// Add header
const addHeadersField = () => {
    webhookData.headers.push({
        key: '',
        value: '',
    });
}
// Delete header
const deleteHeadersField = (key) => {
    webhookData.headers.splice(key, 1)
}

// Add body
const addBodyField = () => {
    webhookData.bodys.push({
        name: '',
        value: '',
    });
}
// Delete body
const deleteBodyField = (key) => {
    webhookData.bodys.splice(key, 1)
}


</script>

<template> 
    <!-- {{ props.meetingId  }} -->
    <div class="tfhb-webhook-title tfhb-flexbox tfhb-full-width">
        <div class="tfhb-admin-title tfhb-m-0">
            <h2>{{ __('Webhook Integration', 'hydra-booking') }}</h2> 
            <p>{{ __('Webhook integration enables automated data transfer between apps, allowing real-time communication and custom API interactions.', 'hydra-booking') }}</p>
        </div>
    </div>

    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-align-baseline tfhb-m-0 tfhb-full-width">

        <button class="tfhb-btn tfhb-flexbox tfhb-gap-8" v-if="webhookcreate" @click="backtoWebHookList">
            <Icon name="ArrowLeft" :width="20"/>
            {{ __('Back', 'hydra-booking') }}
        </button> 

        <div class="tfhb-webhook-content tfhb-full-width" v-if="meeting.webhook && webhookList">
        
            
            <div class="tfhb-admin-card-box tfhb-full-width tfhb-justify-between tfhb-mb-16" v-for="(hook, key)  in meeting.webhook" :key="key">
                <div class="tfhb-webhook-info">
                    <h4>{{ hook.webhook }}</h4>
                    <p>{{ hook.url }}</p>
                    <ul class="webhook-event" v-if="hook.events">
                        <li v-for="event in hook.events">
                            {{ event }}
                        </li>
                    </ul>
                </div>
                <div class="tfhb-webhook-action tfhb-flexbox tfhb-gap-8">
                    <HbSwitch 
                    v-model="hook.status"
                    @change="(e) => updateHookStatus(e, hook, key)"
                    />
                    <button class="question-edit-btn" >
                        <Icon name="PencilLine" :width="16" @click="editWebHook(hook, key)" />
                    </button>
                    <button class="question-edit-btn">
                        <Icon name="X" :width="16" @click="deleteWebHook(key)" />
                    </button>
                </div>
            </div>
        </div>

        <div class="tfhb-admin-card-box tfhb-webhook-box tfhb-full-width tfhb-gap-24" v-if="webhookcreate">

            <HbText  
                v-model="webhookData.url"
                required= "true"  
                :label="__('Webhook URL', 'hydra-booking')"  
                selected = "1"
                :placeholder="__('Type your Webhook URL', 'hydra-booking')" 
            /> 

            <HbDropdown  
                v-model="webhookData.request_method"
                :label="__('Request Method', 'hydra-booking')"   
                width="50"
                selected = "1"
                placeholder="Request Method"  
                :option = "[
                    {'name': 'GET', 'value': 'GET'}, 
                    {'name': 'POST', 'value': 'POST'},
                    {'name': 'PUT', 'value': 'PUT'},
                    {'name': 'PATCH', 'value': 'PATCH'}
                ]"
            />

            <HbDropdown  
                v-model="webhookData.request_format"
                :label="__('Request Format', 'hydra-booking')"   
                width="50"
                selected = "1"
                placeholder="Request Format"  
                :option = "[
                    {'name': 'FORM', 'value': 'form'}, 
                    {'name': 'JSON', 'value': 'json'}
                ]"
            />

            <HbCheckbox 
                required= "true"
                v-model="webhookData.events"
                name="webhook_events"
                :label="__('Event Triggers', 'hydra-booking')"
                :groups="true"
                :options="['Booking Confirmed', 'Booking Canceled', 'Booking Completed']" 
            />

            <HbRadio 
                v-if="'Pabbly'!=webhookData.webhook && 'Zapier'!=webhookData.webhook"
                required= "true"
                v-model="webhookData.request_header"
                name="request_header"
                :label="__('Request Header', 'hydra-booking')"
                :groups="true"
                :options="[
                    {'label': 'No Headers', 'value': 'no'}, 
                    {'label': 'With Headers', 'value': 'with'}
                ]" 
            />
            
            <div class="tfhb-headers tfhb-full-width" v-if="'with'==webhookData.request_header && 'Pabbly'!=webhookData.webhook && 'Zapier'!=webhookData.webhook">
                <p>{{ __('Request Headers', 'hydra-booking') }}'</p>
                <div class="tfhb-flexbox" v-for="(header, key) in webhookData.headers">
                    <div class="tfhb-request-header-fields tfhb-flexbox">
                        <HbText  
                            v-model="header.key"
                            required= "true"  
                            selected = "1"
                            :placeholder="__('Header Key', 'hydra-booking')" 
                            width="50"
                        /> 
                        <HbText  
                            v-model="header.value"
                            required= "true"   
                            selected = "1"
                            :placeholder="__('Header Value', 'hydra-booking')" 
                            width="50"
                        /> 
                    </div>
                    <div class="request-actions">
                        <button class="tfhb-availability-schedule-btn" @click="addHeadersField" v-if="key == 0">
                            <Icon name="Plus" size="20px" /> 
                        </button> 
                        <button class="tfhb-availability-schedule-btn" @click="deleteHeadersField(key)" v-else>
                            <Icon name="X" size="20px" /> 
                        </button> 
                    </div>
                </div>
            </div>

            <HbRadio 
                required= "true"
                v-model="webhookData.request_body"
                name="request_body"
                :label="__('Request Body', 'hydra-booking')"
                :groups="true"
                :options="[
                    {'label': 'All Data', 'value': 'all'}, 
                    {'label': 'Selected Data', 'value': 'selected'}
                ]" 
            />

            <div class="tfhb-headers tfhb-full-width" v-if="'selected'==webhookData.request_body">
                <p>{{ __('Request Fields', 'hydra-booking') }}'</p>
                <div class="tfhb-flexbox" v-for="(body, key) in webhookData.bodys">
                    <div class="tfhb-request-header-fields tfhb-flexbox">
                        <HbText  
                            v-model="body.name"
                            required= "true"  
                            selected = "1"
                            :placeholder="__('Enter Name', 'hydra-booking')" 
                            width="50"
                        /> 
                        <HbText  
                            v-model="body.value"
                            required= "true"   
                            selected = "1"
                            :placeholder="__('Enter Value', 'hydra-booking')" 
                            width="50"
                        /> 
                    </div>
                    <div class="request-actions">
                        <button class="tfhb-availability-schedule-btn" @click="addBodyField" v-if="key == 0">
                            <Icon name="Plus" size="20px" /> 
                        </button> 
                        <button class="tfhb-availability-schedule-btn" @click="deleteBodyField(key)" v-else>
                            <Icon name="X" size="20px" /> 
                        </button> 
                    </div>
                </div>
            </div>

            <HbCheckbox 
                v-model="webhookData.status"
                :label="__('Enable this Webhook', 'hydra-booking')"
                name="enable_webhook"
            />

            <div class="tfhb-submission-btn">
                <button class="tfhb-btn boxed-btn tfhb-flexbox tfhb-hover-effect" @click="updateWebHook">{{ __('Save Webhook', 'hydra-booking') }} </button>
            </div>
        </div>

        <div class="tfhb-integration-box tfhb-full-width">
            <button class="tfhb-btn  tfhb-flexbox tfhb-gap-8" v-if="webhookList" @click="addNewWebHook">
                <Icon name="PlusCircle" :width="20"/>
                {{ __('Add New Webhook', 'hydra-booking') }}
            </button>
            
        </div> 

    </div>
    
</template>

<style scoped>
</style> 