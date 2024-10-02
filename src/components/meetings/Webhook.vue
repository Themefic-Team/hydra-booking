<script setup>
import {ref} from 'vue'
import Icon from '@/components/icon/LucideIcon.vue'  
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
const props = defineProps([
    'IntegrationsValue',  
    'meeting',  
])
 
 
</script>

<template> 
    <div class="tfhb-webhook-title tfhb-flexbox tfhb-full-width">
        <div class="tfhb-admin-title tfhb-m-0">
            <h2>{{ $tfhb_trans('Availability Range for this Booking') }}</h2> 
            <p>{{ $tfhb_trans('How many days can the invitee schedule?') }}</p>
        </div>
        <div class="tfhb-integration-box">
            <button class="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" v-if="props.IntegrationsValue.integrationsList" @click="props.IntegrationsValue.integrationsListopen=!props.IntegrationsValue.integrationsListopen">
                <Icon name="PlusCircle" :width="20"/>
                {{ $tfhb_trans('Add New Integrations') }}
            </button>
            <button class="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" v-if="props.IntegrationsValue.integrationscreate" @click="props.IntegrationsValue.backtointegrationsList">
                <Icon name="ArrowLeft" :width="20"/>
                {{ $tfhb_trans('Back') }}
            </button>

            <div class="tfhb-integrations-lists" v-if="props.IntegrationsValue.integrationsListopen">
                <ul>
                    <li @click="props.IntegrationsValue.addNewIntegrations('Mailchimp')" v-if="props.meeting.mailchimp.status">{{ $tfhb_trans('Mailchimp') }}</li>
                    <li @click="props.IntegrationsValue.addNewIntegrations('FluentCRM')" v-if="props.meeting.fluentcrm.status">{{ $tfhb_trans('FluentCRM') }}</li>
                    <li @click="props.IntegrationsValue.addNewIntegrations('ZohoCRM')" v-if="props.meeting.zohocrm.status">{{ $tfhb_trans('ZohoCRM') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="tfhb-webhook-content tfhb-full-width" v-if="props.IntegrationsValue.meeting.integrations && props.IntegrationsValue.integrationsList">
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
            <button class="tfhb-btn boxed-btn tfhb-flexbox" @click="props.IntegrationsValue.updateIntegrations">{{ $tfhb_trans('Save Webhook') }} </button>
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
        />  
    </div>
</template>

<style scoped>
</style> 