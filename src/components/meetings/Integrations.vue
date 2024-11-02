<script setup>
import { __ } from '@wordpress/i18n';
import {ref} from 'vue'
import Icon from '@/components/icon/LucideIcon.vue'  
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
const props = defineProps([
    'IntegrationsValue',  
    'meeting',  
])

// dropdown change 
const changeIntegrations = (value) => { 
    props.IntegrationsValue.addNewIntegrations(value)
}
 
</script>

<template> 
    <div class="tfhb-webhook-title tfhb-flexbox tfhb-full-width">
        <div class="tfhb-admin-title tfhb-m-0">
            <h2>{{ __('Mailchimp, FluentCRM & Zoho Integration ', 'hydra-booking') }}</h2> 
            <p>{{ __('Integrate Mailchimp, FluentCRM, and Zoho for managing emails, tracking leads, and enhancing customer engagement.', 'hydra-booking') }}</p>
        </div>
    </div>

    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-align-baseline tfhb-m-0 tfhb-full-width">

        <button class="tfhb-btn tfhb-flexbox tfhb-gap-8" v-if="props.IntegrationsValue.integrationscreate" @click="props.IntegrationsValue.backtointegrationsList">
            <Icon name="ArrowLeft" :width="20"/>
            {{ __('Back', 'hydra-booking') }}
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
                :label="__('Integrations Title', 'hydra-booking')"  
                selected = "1"
                :placeholder="__('Type your Integrations Title', 'hydra-booking')" 
                width="50"
            /> 

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='Mailchimp'"
                v-model="props.IntegrationsValue.integrationsData.audience"
                required= "true"  
                :label="__('Select Audience', 'hydra-booking')"   
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
                :label="__('FluentCRM Lists', 'hydra-booking')"   
                width="50"
                selected = "1"
                :placeholder="__('Select FluentCRM List', 'hydra-booking')"  
                :option = "meeting.fluentcrm.lists"
                @tfhb-onchange="moduleFields"
            />

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='FluentCRM'"
                v-model="props.IntegrationsValue.integrationsData.tags"
                required= "true"  
                :label="__('Contact Tags', 'hydra-booking')"   
                width="50"
                selected = "1"
                :placeholder="__('Select Contact Tag', 'hydra-booking')" 
                :option = "meeting.fluentcrm.tags"
            />

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsData.webhook=='ZohoCRM'"
                v-model="props.IntegrationsValue.integrationsData.modules"
                required= "true"  
                :label="__('Modules', 'hydra-booking')"   
                width="50"
                selected = "1"
                :placeholder="__('Select Modules', 'hydra-booking')" 
                :option = "meeting.zohocrm.modules"
                @tfhb-onchange="moduleFields"
            />

            <HbCheckbox 
                required= "true"
                v-model="props.IntegrationsValue.integrationsData.events"
                name="webhook_events"
                :label="__('Event Triggers', 'hydra-booking')"
                :groups="true"
                :options="['Booking Confirmed', 'Booking Canceled', 'Booking Completed']" 
            />

            <div class="tfhb-headers tfhb-full-width">
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
                            :placeholder="__('Enter Value', 'hydra-booking')" 
                            :option = "[
                                {'name': '{{attendee.full_name}}', 'value': '{{attendee.full_name}}'}, 
                                {'name': '{{attendee.email}}', 'value': '{{attendee.email}}'},
                                {'name': '{{attendee.phone}}', 'value': '{{attendee.phone}}'},
                                {'name': '{{attendee.timezone}}', 'value': '{{attendee.timezone}}'},
                                {'name': '{{attendee.address}}', 'value': '{{attendee.address}}'},
                                {'name': '{{booking.meeting_date}}', 'value': '{{booking.meeting_date}}'},
                                {'name': '{{booking.start_time}}', 'value': '{{booking.start_time}}'},
                                {'name': '{{booking.end_time}}', 'value': '{{booking.end_time}}'},
                                {'name': '{{booking.duration}}', 'value': '{{booking.duration}}'},
                                {'name': '{{booking.hash}}', 'value': '{{booking.hash}}'},
                                {'name': '{{host.name}}', 'value': '{{host.name}}'},
                                {'name': '{{host.email}}', 'value': '{{host.email}}'},
                                {'name': '{{host.timezone}}', 'value': '{{host.timezone}}'},
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

            <HbCheckbox 
                v-model="props.IntegrationsValue.integrationsData.status"
                :label="__('Enable this Webhook', 'hydra-booking')"
                name="enable_webhook"
            />

            <div class="tfhb-submission-btn">
                <button class="tfhb-btn boxed-btn tfhb-flexbox" @click="props.IntegrationsValue.updateIntegrations">{{ __('Save Webhook', 'hydra-booking') }} </button>
            </div>
        </div>
      
        <div class="tfhb-integration-box tfhb-full-width">
            <button class="tfhb-btn  tfhb-flexbox tfhb-gap-8" v-if="props.IntegrationsValue.integrationsList" @click="props.IntegrationsValue.integrationsListopen=!props.IntegrationsValue.integrationsListopen">
                <Icon name="PlusCircle" :width="20"/>
                {{ __('Add New Integrations', 'hydra-booking') }}
            </button>

            <HbDropdown  
                v-if="props.IntegrationsValue.integrationsListopen"
                v-model="selecte_integrations"
                required= "true"  
                class="tfhb-mt-16" 
                :label="__('Select integrations', 'hydra-booking')"    
                selected = "1"
                placeholder="Select integrations"
                :option = "[
                    {name: 'Mailchimp', value: 'Mailchimp', disable: !props.meeting.mailchimp.status},  
                    {name: 'FluentCRM', value: 'FluentCRM', disable: !props.meeting.mailchimp.status},  
                    {name: 'ZohoCRM', value: 'ZohoCRM', disable: !props.meeting.mailchimp.status},
                ]"
                @tfhb-onchange="changeIntegrations"
            /> 
        </div> 

    </div>


</template>

<style scoped>
</style> 