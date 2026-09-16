<script setup> 
import { __ } from '@wordpress/i18n';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbCounter from '@/components/meetings/HbCounter.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import { applyFilters } from '@/utils/hooks.js';
import { ref } from 'vue';

const componentMap = {
    HbSwitch,
    HbCounter,
    HbText
};

const defaultLimitSections = [];

const limitSections = ref(applyFilters('tfhb_meeting_limit_sections', defaultLimitSections));

const emit = defineEmits(["update-meeting", "limits-frequency-add"]); 

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


// Add more addExtraFrequency
function addExtraFrequency(){
    props.meeting.booking_frequency.push({
    limit: 1,
    times:'Month',
  })
}

// Remove removeExtraFrequency
const removeExtraFrequency = (key) => {
    props.meeting.booking_frequency.splice(key, 1);
}

</script>

<template>
    <div class="meeting-create-details tfhb-gap-24">

        <!-- Date And Time --> 
       <div class="tfhb-meeting-limit tfhb-flexbox tfhb-gap-16">
            <div class="tfhb-admin-title tfhb-full-width tfhb-m-0" >
                <h2>Meeting Limits</h2> 
                <p>How often attendee can be book</p>
            </div>
            <div class="tfhb-admin-card-box tfhb-meeting-limits tfhb-flexbox tfhb-align-baseline tfhb-m-0 tfhb-full-width">  

                <!-- Buffer time before meeting -->
                <HbDropdown 
                    v-model="meeting.buffer_time_before"
                    required= "true" 
                    :label="$tfhb_trans('Buffer time before meeting')"  
                    width="50"
                    :selected = "1"
                    placeholder="No buffer time"  
                    :option = "[
                        {name: 'No buffer time', value: '0'}, 
                        {name: '5 Minutes', value: '5'},  
                        {name: '10 Minutes', value: '10'},  
                        {name: '15 Minutes', value: '15'},  
                        {name: '20 Minutes', value: '20'},  
                        {name: '30 Minutes', value: '30'},  
                        {name: '45 Minutes', value: '45'},  
                        {name: '60 Minutes', value: '60'},  
                        {name: '90 Minutes', value: '90'},  
                        {name: '120 Minutes', value: '120'},   
                    ]" 
                />
                <!-- Buffer time before meeting --> 
                <!-- Buffer time after meeting -->
                <HbDropdown 
                    v-model="meeting.buffer_time_after"
                    required= "true"  
                    :label="$tfhb_trans('Buffer time after meeting')"   
                    width="50"
                    selected = "1"
                    :placeholder="$tfhb_trans('No buffer time')"  
                    :option = "[
                        {name: 'No buffer time', value: '0'},  
                        {name: '5 Minutes', value: '5'},  
                        {name: '10 Minutes', value: '10'},  
                        {name: '15 Minutes', value: '15'},  
                        {name: '20 Minutes', value: '20'},  
                        {name: '30 Minutes', value: '30'},  
                        {name: '45 Minutes', value: '45'},  
                        {name: '60 Minutes', value: '60'},  
                        {name: '90 Minutes', value: '90'},  
                        {name: '120 Minutes', value: '120'},   
                    ]" 
                />
                <!-- Buffer time after meeting -->
              
            
                <!-- Booking Frequency -->
            
                <HbCounter
                    :label="$tfhb_trans('Booking frequency')" 
                    :description="$tfhb_trans('Limit how many times this meeting can be booked')"
                    :repater="true" 
                    counterLabel="Bookings"
                    :counter_value="meeting.booking_frequency"
                    @limits-frequency-add="addExtraFrequency"
                    @limits-frequency-remove="removeExtraFrequency"
                    limit="1"
                />

                  <!-- Meeting interval -->
                  <HbDropdown 
                    v-model="meeting.meeting_interval"
                    required= "true"  
                    :label="$tfhb_trans('Meeting interval')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Default interval')"
                    :option = "[
                        {name: 'Use meeting length (default)', value: '0'}, 
                        {name: '5 Minutes', value: '5'},  
                        {name: '10 Minutes', value: '10'},  
                        {name: '15 Minutes', value: '15'},  
                        {name: '20 Minutes', value: '20'},  
                        {name: '30 Minutes', value: '30'},  
                        {name: '45 Minutes', value: '45'},  
                        {name: '60 Minutes', value: '60'},  
                        {name: '90 Minutes', value: '90'},  
                        {name: '120 Minutes', value: '120'},   
                    ]"
                />
            
            </div>  
       </div>

        <div v-for="section in limitSections" :key="section.id" class="tfhb-meeting-limit tfhb-flexbox tfhb-gap-16">
            <div v-if="section.isPro && (!$tfhb_is_pro || !$tfhb_license_status)" class="tfhb-admin-title tfhb-full-width tfhb-m-0" >
                <div class=" tfhb-pro">
                    <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    {{$tfhb_trans(section.title)}}
                        <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size="20" /> {{ $tfhb_trans('Pro') }}</span>
                    </h2> 
                    <p> {{$tfhb_trans(section.description)}}</p>
                </div> 
            </div>
            <div v-else class="tfhb-admin-title tfhb-full-width tfhb-m-0">
                <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    {{$tfhb_trans(section.title)}}
                    <HbSwitch 
                        v-if="section.switchModel"
                        v-model="meeting[section.switchModel]"
                    />
                </h2> 
                <p> {{$tfhb_trans(section.description)}}</p>
            </div> 
            
            <div
                :class="{'tfhb-pro': section.isPro && (!$tfhb_is_pro || !$tfhb_license_status)}"
                class="tfhb-admin-card-box tfhb-meeting-limits tfhb-flexbox tfhb-m-0 tfhb-full-width" 
                v-if="(section.isPro && (!$tfhb_is_pro || !$tfhb_license_status)) || (section.switchModel && meeting[section.switchModel] == true) || !section.switchModel"
            >  
                <template v-for="(field, fIndex) in section.fields" :key="fIndex">
                    <component 
                        :is="componentMap[field.type]"
                        v-bind="field.props"
                        v-model="meeting[field.model]"
                        :counter_value="field.counterModel ? meeting[field.counterModel] : undefined"
                    />
                </template>
            </div>  
        </div>

        <div class="tfhb-meeting-schedule tfhb-full-width tfhb-flexbox tfhb-gap-16">
            <HbCheckbox 
                v-model="meeting.attendee_can_cancel"
                :label="$tfhb_trans('Attendee can cancel this meeting')"
                name="attendee_can_cancel"
            />
            <HbCheckbox 
                v-model="meeting.attendee_can_reschedule"
                :label="$tfhb_trans('Attendee can reschedule this meeting')"
                name="attendee_can_reschedule"
            />
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