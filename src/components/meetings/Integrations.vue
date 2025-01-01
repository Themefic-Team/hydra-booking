<script setup>
import { __ } from '@wordpress/i18n';
import {ref} from 'vue'
import Icon from '@/components/icon/LucideIcon.vue'  
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbRadio from '@/components/form-fields/HbRadio.vue';
import axios from 'axios'  
const props = defineProps([
    'IntegrationsValue',  
    'meeting',  
    'integrations'
])
const router = useRouter();
const selecte_integrations = ref('')
// dropdown change 
const changeIntegrations = (value) => { 
    
    if(value == 'Mailchimp' && !props.meeting.mailchimp.status == true){
        return;
    }
    if(value == 'FluentCRM' && !props.meeting.fluentcrm.status == true){
        return;
    }
    if(value == 'ZohoCRM' && !props.meeting.zohocrm.status == true){
        return;
    }
    if(value == 'Pabbly' && !props.integrations.pabbly_status == true){
        return;
    }
    if(value == 'Zapier' && !props.integrations.zapier_status == true){
        return;
    }
    
    
    props.IntegrationsValue.addNewIntegrations(value)
}

// modules callback
const moduleFields = async (e) => {
    if(e){
        let data = {
            host_id: props.meeting.host_id,
            webhook: props.IntegrationsValue.integrationsData.webhook,
            module: e
        };  
        
        try { 
            const response = await axios.post(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/meetings/integration/fields', data, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                }
            } );
            if (response.data.status) { 
                props.IntegrationsValue.integrationsData.fields = response.data.fields;
            }
        } catch (error) {
            console.log(error);
        } 
    }
}

</script>

<template> 
    <div class="tfhb-webhook-title tfhb-flexbox tfhb-full-width">
        <div class="tfhb-admin-title tfhb-m-0">
            <h2>{{ $tfhb_trans('Integration') }}</h2> 
            <p>{{ $tfhb_trans('Integrate Mailchimp, FluentCRM, and Zoho for managing emails, tracking leads, and enhancing customer engagement.') }}</p> 
        </div>
    </div>

    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-align-baseline tfhb-m-0 tfhb-full-width">

        <button class="tfhb-btn tfhb-flexbox tfhb-gap-8" v-if="props.IntegrationsValue.integrationscreate" @click="props.IntegrationsValue.backtointegrationsList">
            <Icon name="ArrowLeft" :width="20"/>
            {{ $tfhb_trans('Back') }}
        </button> 

        <div class="tfhb-webhook-content tfhb-full-width" v-if="props.IntegrationsValue.meeting.integrations.length > 0 && props.IntegrationsValue.integrationsList ">
           
            
            <div class="tfhb-admin-card-box tfhb-full-width tfhb-justify-between tfhb-mb-16" v-for="(hook, key)  in props.IntegrationsValue.meeting.integrations" :key="key">
                <div class="tfhb-webhook-info">
                    <h4>{{ hook.webhook }}</h4>
                    <p>{{ hook.title }}</p>
                    <ul class="webhook-event" v-if="hook.events">
                        <li v-for="event in hook.events">
                            {{ event }}
                        </li>
                    </ul>
                </div>
                <div class="tfhb-webhook-action tfhb-flexbox tfhb-gap-8">

                    <HbSwitch 
                    v-model="hook.status"
                    @change="(e) => props.IntegrationsValue.updateHookStatus(e, hook, key)"
                    />
                    <button class="question-edit-btn" >
                        <Icon name="PencilLine" :width="16" @click="props.IntegrationsValue.editIntegrations(hook, key)" />
                    </button>
                    <button class="question-edit-btn">
                        <Icon name="X" :width="16" @click="props.IntegrationsValue.deleteIntegrations(key)" />
                    </button>
                </div>
            </div>
        </div>

        <div class="tfhb-admin-card-box tfhb-webhook-box tfhb-full-width tfhb-gap-24" v-if="props.IntegrationsValue.integrationscreate">

            <HbText  
                v-model="props.IntegrationsValue.integrationsData.title"
                required= "true"  
                :label="$tfhb_trans('Integrations Title')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your Integrations Title')" 
                width="50"
            /> 

            <HbText  
                v-if="props.IntegrationsValue.integrationsData.webhook=='Pabbly' || props.IntegrationsValue.integrationsData.webhook=='Zapier'"
                v-model="props.IntegrationsValue.integrationsData.url"
                required= "true"  
                :label="__('URL', 'hydra-booking')"  
                :placeholder="__('Type your URL', 'hydra-booking')" 
                width="50"
            /> 

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='Mailchimp'"
                v-model="props.IntegrationsValue.integrationsData.audience"
                required= "true"  
                :label="$tfhb_trans('Select Audience')"   
                width="50"
                selected = "1"
                placeholder="Select Audience"  
                :option = "meeting.mailchimp.audience"
                @tfhb-onchange="moduleFields" 
            />

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='FluentCRM'"
                v-model="props.IntegrationsValue.integrationsData.lists"
                required= "true"  
                :label="$tfhb_trans('FluentCRM Lists')"   
                width="50"
                selected = "1"
                :placeholder="$tfhb_trans('Select FluentCRM List')"  
                :option = "meeting.fluentcrm.lists"
                @tfhb-onchange="moduleFields"
            />

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='FluentCRM'"
                v-model="props.IntegrationsValue.integrationsData.tags"
                required= "true"  
                :label="$tfhb_trans('Contact Tags')"   
                width="50"
                selected = "1"
                :placeholder="$tfhb_trans('Select Contact Tag')" 
                :option = "meeting.fluentcrm.tags"
            />

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='ZohoCRM'"
                v-model="props.IntegrationsValue.integrationsData.modules"
                required= "true"  
                :label="$tfhb_trans('Modules')"   
                width="50"
                selected = "1"
                :placeholder="$tfhb_trans('Select Modules')" 
                :option = "meeting.zohocrm.modules"
                @tfhb-onchange="moduleFields"
            />

            <HbCheckbox 
                required= "true"
                v-model="props.IntegrationsValue.integrationsData.events"
                name="webhook_events"
                :label="$tfhb_trans('Event Triggers')"
                :groups="true"
                :options="['Booking Confirmed', 'Booking Canceled', 'Booking Completed']" 
            />

            <div class="tfhb-headers tfhb-full-width">
                <p>{{ $tfhb_trans('Other Fields') }}</p>
                <HbRadio 
                    v-if="props.IntegrationsValue.integrationsData.webhook=='Pabbly' || props.IntegrationsValue.integrationsData.webhook=='Zapier'"
                    required= "true"
                    v-model="props.IntegrationsValue.integrationsData.request_body"
                    name="request_body"
                    :label="__('Request Body', 'hydra-booking')"
                    :groups="true"
                    :options="[
                        {'label': 'All Data', 'value': 'all'}, 
                        {'label': 'Selected Data', 'value': 'selected'}
                    ]" 
                />
            </div>
            <div class="tfhb-headers tfhb-full-width" v-if="'selected'==props.IntegrationsValue.integrationsData.request_body && (props.IntegrationsValue.integrationsData.webhook=='Pabbly' || props.IntegrationsValue.integrationsData.webhook=='Zapier') ">
                <p>{{ __('Other Fields', 'hydra-booking') }}</p>
                <div class="tfhb-flexbox" v-for="(body, key) in props.IntegrationsValue.integrationsData.bodys">
                    <div class="tfhb-request-header-fields tfhb-flexbox">
                        <HbText  
                            v-model="body.name"
                            required= "true"  
                            selected = "1"
                            :placeholder="__('Enter Name', 'hydra-booking')" 
                            width="50"
                        />
                        <HbDropdown  
                            v-show="body.type!='tfhb_ct'"
                            v-model="body.type"
                            required= "true"  
                            width="50"
                            selected = "1"
                            :placeholder="__('Enter Value', 'hydra-booking')" 
                            :option = "[
                                {'name': '{{attendee.full_name}}', 'value': 'attendee_name'}, 
                                {'name': '{{attendee.email}}', 'value': 'email'},
                                {'name': '{{attendee.timezone}}', 'value': 'timezone'},
                                {'name': '{{attendee.address}}', 'value': 'address'},
                                {'name': '{{booking.meeting_date}}', 'value': 'meeting_date'},
                                {'name': '{{booking.start_time}}', 'value': 'start_time'},
                                {'name': '{{booking.end_time}}', 'value': 'end_time'},
                                {'name': '{{booking.duration}}', 'value': 'duration'},
                                {'name': '{{booking.hash}}', 'value': 'hash'},
                                {'name': '{{host.name}}', 'value': 'host_name'},
                                {'name': '{{host.email}}', 'value': 'host_email'},
                                {'name': '{{host.timezone}}', 'value': 'host_timezone'},
                                {'name': 'Custom', 'value': 'tfhb_ct'},
                            ]"
                            @tfhb_body_value_change="BodyValues"
                            :single_key = "key"
                        />
                        <HbText  
                            v-show="body.type=='tfhb_ct'"
                            v-model="body.value"
                            required= "true"   
                            selected = "1"
                            :placeholder="__('Enter Value', 'hydra-booking')" 
                            width="50"
                        /> 
                    </div>
                    <div class="request-actions">
                        <button class="tfhb-availability-schedule-btn" @click="props.IntegrationsValue.addBodyField" v-if="key == 0">
                            <Icon name="Plus" size=20 /> 
                        </button> 
                        <button class="tfhb-availability-schedule-btn" @click="props.IntegrationsValue.deleteBodyField(key)" v-else>
                            <Icon name="X" size=20 /> 
                        </button> 
                    </div>
                </div>
            </div>

            <div class="tfhb-headers tfhb-full-width" v-if="props.IntegrationsValue.integrationsData.webhook!='Pabbly' && props.IntegrationsValue.integrationsData.webhook!='Zapier'">
                <p>{{ __('Other Fields', 'hydra-booking') }}</p>
                <div class="tfhb-flexbox" v-for="(body, key) in props.IntegrationsValue.integrationsData.bodys">
                    <div class="tfhb-request-header-fields tfhb-flexbox">

                        <HbDropdown  
                            v-model="body.name"
                            required= "true"    
                            width="50"
                            selected = "1"
                            placeholder="Select Tag"  
                            :option = "props.IntegrationsValue.integrationsData.fields"
                        />
                        <HbDropdown  
                            v-show="body.type!='tfhb_ct'"
                            v-model="body.type"
                            required= "true"  
                            width="50"
                            selected = "1"
                            :placeholder="$tfhb_trans('Enter Value')" 
                            :option = "[
                                {'name': '{{attendee.full_name}}', 'value': 'attendee_name'}, 
                                {'name': '{{attendee.email}}', 'value': 'email'},
                                {'name': '{{attendee.timezone}}', 'value': 'timezone'},
                                {'name': '{{attendee.address}}', 'value': 'address'},
                                {'name': '{{booking.meeting_date}}', 'value': 'meeting_date'},
                                {'name': '{{booking.start_time}}', 'value': 'start_time'},
                                {'name': '{{booking.end_time}}', 'value': 'end_time'},
                                {'name': '{{booking.duration}}', 'value': 'duration'},
                                {'name': '{{booking.hash}}', 'value': 'hash'},
                                {'name': '{{host.name}}', 'value': 'host_name'},
                                {'name': '{{host.email}}', 'value': 'host_email'},
                                {'name': '{{host.timezone}}', 'value': 'host_timezone'},
                                {'name': 'Custom', 'value': 'tfhb_ct'},
                            ]"
                            @tfhb_body_value_change="BodyValues"
                            :single_key = "key"
                        />
                        <HbText  
                            v-show="body.type=='tfhb_ct'"
                            v-model="body.value"
                            required= "true"   
                            selected = "1"
                            :placeholder="$tfhb_trans('Enter Value')" 
                            width="50"
                        /> 
                    </div>
                    <div class="request-actions">
                        <button class="tfhb-availability-schedule-btn" @click="props.IntegrationsValue.addBodyField" v-if="key == 0">
                            <Icon name="Plus" size=20 /> 
                        </button> 
                        <button class="tfhb-availability-schedule-btn" @click="props.IntegrationsValue.deleteBodyField(key)" v-else>
                            <Icon name="X" size=20 /> 
                        </button> 
                    </div>
                </div>
            </div>

            <HbCheckbox 
                v-model="props.IntegrationsValue.integrationsData.status"
                :label="$tfhb_trans('Enable this Webhook')"
                name="enable_webhook"
            />

            <div class="tfhb-submission-btn">
                <button class="tfhb-btn boxed-btn tfhb-flexbox tfhb-hover-effect" @click="props.IntegrationsValue.updateIntegrations">{{ $tfhb_trans('Save Webhook') }} </button>
            </div>
        </div>
      
        <div class="tfhb-integration-box tfhb-full-width">
            <button class="tfhb-btn  tfhb-flexbox tfhb-gap-8" v-if="props.IntegrationsValue.integrationsList" @click="props.IntegrationsValue.integrationsListopen=!props.IntegrationsValue.integrationsListopen">
                <Icon name="PlusCircle" :width="20"/>
                {{ $tfhb_trans('Add New Integrations') }}
            </button>

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsListopen"
                v-model="selecte_integrations"
                required= "true"  
                class="tfhb-mt-16" 
                :label="$tfhb_trans('Select integrations')"    
                selected = "1"
                :placeholder="$tfhb_trans('Select integrations')"
                :option = "[
                    {name: 'Mailchimp', value: 'Mailchimp', icon: $tfhb_url+'/assets/images/Mailchimp-small.svg',},  
                    {name: 'FluentCRM', value: 'FluentCRM', icon: $tfhb_url+'/assets/images/fluent-crm-small.svg',},  
                    {name: 'ZohoCRM', value: 'ZohoCRM', icon: $tfhb_url+'/assets/images/Zoho.svg',},
                    {name: 'Pabbly', value: 'Pabbly', icon: $tfhb_url+'/assets/images/pabbly-small.svg',},
                    {name: 'Zapier', value: 'Zapier', icon: $tfhb_url+'/assets/images/zapier-small.png',},
                ]"
                @tfhb-onchange="changeIntegrations"
            /> 
            <div  v-if="selecte_integrations == 'Mailchimp' && !props.meeting.mailchimp.status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4"> {{ $tfhb_trans('Mailchimp is not connected.') }}  
                <HbButton 
                    classValue="tfhb-btn flex-btn" 
                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                    :buttonText="$tfhb_trans('Please Configure')"
                />  
            </div>
            <div  v-if="selecte_integrations == 'FluentCRM' && !props.meeting.fluentcrm.status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4">
                <span>{{ props.meeting.fluentcrm.error_msg ? props.meeting.fluentcrm.error_msg : 'FluentCRM is not connected. ' }}</span>
                <HbButton 
                    v-if="props.meeting.fluentcrm.error_msg==''"
                    classValue="tfhb-btn flex-btn" 
                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                    :buttonText="$tfhb_trans('Please Configure')"
                />  
            </div>
            <div  v-if="selecte_integrations == 'ZohoCRM' && !props.meeting.zohocrm.status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4"> {{ $tfhb_trans('ZohoCRM is not connected.') }}   
                <HbButton 
                    classValue="tfhb-btn flex-btn" 
                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                    :buttonText="$tfhb_trans('Please Configure')"
                />  
            </div>
           
            <div  v-if="selecte_integrations == 'Pabbly' && !props.integrations.pabbly_status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4">Pabbly is not connected. 
                <HbButton 
                    classValue="tfhb-btn flex-btn" 
                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                    :buttonText="__('Please Configure', 'hydra-booking')"
                />  
            </div>
            <div  v-if="selecte_integrations == 'Zapier' && !props.integrations.zapier_status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4">Zapier is not connected. 
                <HbButton 
                    classValue="tfhb-btn flex-btn" 
                    @click="() => router.push({ name: 'SettingsIntegrations' })" 
                    :buttonText="__('Please Configure', 'hydra-booking')"
                />  
            </div>
        </div> 

    </div>
    


</template>

<style scoped>
</style> 