<script setup> 
import { __ } from '@wordpress/i18n';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbCounter from '@/components/meetings/HbCounter.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import Icon from '@/components/icon/LucideIcon.vue'
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
                    :label="__('Buffer time before meeting', 'hydra-booking')"  
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
                    :label="__('Buffer time after meeting', 'hydra-booking')"   
                    width="50"
                    selected = "1"
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
                <!-- Buffer time after meeting -->
              
            
                <!-- Booking Frequency -->
            
                <HbCounter
                    :label="__('Booking frequency', 'hydra-booking')" 
                    :description="__('Limit how many times this meeting can be booked', 'hydra-booking')"
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
                    :label="__('Meeting interval', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Default interval', 'hydra-booking')"
                    :option = "[
                        {name: 'No interval', value: '0'}, 
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

       <div class="tfhb-meeting-limit tfhb-flexbox tfhb-gap-16">
            <div v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-admin-title tfhb-full-width tfhb-m-0  tfhb-pro" >
                <div class=" tfhb-pro">
                    <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    Recurring Event
                        <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
                    </h2> 
                    <!-- <p>Set up a repeating schedule</p>  -->
                </div> 
            
            </div>
            <div v-else class="tfhb-admin-title tfhb-full-width tfhb-m-0">
                <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    Recurring Event
                    <HbSwitch 
                        v-model="meeting.recurring_status"
                    />
                </h2> 
                <p>Set up a repeating schedule</p>
            </div> 
            <div
            :class="{'tfhb-pro': $tfhb_is_pro == false || $tfhb_license_status == false}"
            class="tfhb-admin-card-box tfhb-meeting-limits tfhb-flexbox tfhb-m-0 tfhb-full-width" v-if="meeting.recurring_status == true">  

                <!-- Meeting interval -->

                <HbCounter
                    :label="__('Repeats every', 'hydra-booking')"
                    width="100" 
                    :repater="false"
                    :counter_value="meeting.recurring_repeat"
                    limit="1"
                />
                
                <!-- For a maximum of --> 
                <HbText  
                        v-model="meeting.recurring_maximum"   
                        type="number"
                        :label="__('Maximum number of bookings', 'hydra-booking')"   
                        selected = "1"
                        :placeholder="__('Use meeting length (default)', 'hydra-booking')" 
                        width="50"  
                        limit="1"
                    /> 


            </div>  
       </div>
        
     

        <div class="tfhb-meeting-schedule tfhb-full-width tfhb-flexbox tfhb-gap-16">
            <HbCheckbox 
                v-model="meeting.attendee_can_cancel"
                :label="__('Attendee can cancel this meeting', 'hydra-booking')"
                name="attendee_can_cancel"
            />
            <HbCheckbox 
                v-model="meeting.attendee_can_reschedule"
                :label="__('Attendee can reschedule this meeting', 'hydra-booking')"
                name="attendee_can_reschedule"
            />
        </div>
        
        <div class="tfhb-submission-btn"> 
            <HbButton  
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting')"
                :buttonText="__('Save & Continue', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                :pre_loader="props.update_preloader"
            />   
        </div>
        <!--Bookings -->
    </div>
</template>