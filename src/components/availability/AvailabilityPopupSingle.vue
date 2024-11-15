<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import HbText from '../form-fields/HbText.vue'; 
import HbDateTime from '../form-fields/HbDateTime.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbPopup from '@/components/widgets/HbPopup.vue';
   
import AvailabilityTime from '@/store/times'
import { toast } from "vue3-toastify"; 
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();
 

const props = defineProps({
  isOpen: Boolean,
  availabilityDataSingle: {},
  timeZone: {}, 
  is_host: Boolean,
  max_width: String,
  display_overwrite: Boolean,
});
const emit = defineEmits(["update:availabilityData", "modal-close", "update-availability"]); 


// Update Availability Settings
const UpdateAvailabilitySettings = async (validator_field) => {    
   
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!props.availabilityDataSingle[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!props.availabilityDataSingle[fieldParts[0]][fieldParts[1]]){
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
        }); 
        return
    }

    // Api Submission
    try { 
        if(props.is_host){
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/availability/update', props.availabilityDataSingle, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_custom_availability'
                } 
            } );
            if (response.data.status) {    
            
                // close the popup
                emit('modal-close');
                emit('update-availability', response.data.availability);
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                }); 
            }
        }else{
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability/update', props.availabilityDataSingle, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
            if (response.data.status) {    
            
                // close the popup
                emit('modal-close');
                emit('update-availability', response.data.availability);
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                }); 
            }
        }
        
    } catch (error) {
      
        toast.error(error.message, {
            position: 'bottom-right', // Set the desired position
        });
    }
}

 
// Remove time slot
const removeAvailabilityTime = (key, tkey = null) => {
    props.availabilityDataSingle.time_slots[key].times.splice(tkey, 1);
}
// Add new time slot
const addAvailabilityTime = (key) => {
    props.availabilityDataSingle.time_slots[key].times.push({
        start: '',
        end: '',
    });
}

// Overrides Calander Open
const OverridesOpen = ref(false);
const OverridesDates = reactive({
    
})

//add Overrides Time
const addOverridesTime = (key) => {
    OverridesDates.times.push({
        start: '09:00',
        end: '17:00',
    });
}

// Remove Overrides time slot
const removeOverridesTime = (key, tkey = null) => {
    OverridesDates.times.splice(tkey, 1);
}

const openOverridesCalendarDate = () => { 
    if(props.availabilityDataSingle.date_slots){
        props.availabilityDataSingle.date_slots.push({
            date: '',
            available: '',
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        });
    }else{
        props.availabilityDataSingle.date_slots = [{
            date: '',
            available: '',
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        }];
    }

    const lastIndexOfQuestion = props.availabilityDataSingle.date_slots.length - 1;
    OverridesDates.key = lastIndexOfQuestion;
    OverridesDates.date = '';
    OverridesDates.available = '';
    OverridesDates.times = [
        {
            start: '09:00',
            end: '17:00',
        }
    ];

    OverridesOpen.value = true;
}

// Remove date slot 
const removeAvailabilityTDate = (key) => {
    props.availabilityDataSingle.date_slots.splice(key, 1);
    OverridesOpen.value = false;
}

// Store to the reactive
const addAvailabilityDate = (key) => {

    props.availabilityDataSingle.date_slots[OverridesDates.key].date = OverridesDates.date
    props.availabilityDataSingle.date_slots[OverridesDates.key].available = OverridesDates.available
    props.availabilityDataSingle.date_slots[OverridesDates.key].times = OverridesDates.times

    OverridesOpen.value = false;
}

const editAvailabilityDate = (key) => {
    props.availabilityDataSingle.date_slots.forEach((available, qkey) => {
        if (qkey === key) {
            OverridesDates.key = key;
            OverridesDates.date = available.date;
            OverridesDates.available = available.available;
            OverridesDates.times = available.times;
            
            OverridesOpen.value = true;
        }
    });
}
// Date & Time Format
// Date & Time Format
const formatTime = (time) =>  {
    const [hour, minute] = time.split(':');
    const formattedHour = (parseInt(hour) % 12 || 12);
    const period = parseInt(hour) < 12 ? 'AM' : 'PM';
    return `${formattedHour}:${minute} ${period}`;
}
 
const formatTimeSlots = (timeSlots) =>  {
    return timeSlots.map(slot => {
    return `${formatTime(slot.start)} - ${formatTime(slot.end)}`
    }).join(', ');
}

const getLatestEndTime = (day) => {
    let latestEndTime = day.times[0].end;
    for (let i = 1; i < day.times.length; i++) {
        const endTime = day.times[i].end;
        if (endTime > latestEndTime) {
            latestEndTime = endTime;
        }
    }
    return latestEndTime;
}

const TfhbStartDataEvent = (key, skey, startTime) => {
     
    if(skey == 0){
        return;
    }
    const day = props.availabilityDataSingle.time_slots[key];
    const latestEndTime = getLatestEndTime(day);

    if (startTime <= latestEndTime) {
        toast.error("Your start time will be over the: " + latestEndTime);
        return latestEndTime;
    }
}

const TfhbEndDataEvent = (key, skey, endTime) => {
    const day = props.availabilityDataSingle.time_slots[key];
    const nextDate = skey+1;
    const NextdayData = day.times[nextDate] ? day.times[nextDate].start : '';

    if(NextdayData){
        if ( day.times[skey].start >= endTime || NextdayData <= endTime) {
            toast.error("Your End time will be over the: " + day.times[[skey]].start +" And Less than " + NextdayData);
            return;
        }
    }else{
        if (day.times[skey].start >= endTime) {
            toast.error("Your End time will be over the: " + day.times[[skey]].start);
            return;
        }
    }
    
}

const tfhbValidateInput = (fieldName) => {
    const fieldParts = fieldName.split('.');
    if(fieldParts[0] && !fieldParts[1]){
        isEmpty(fieldParts[0], props.availabilityDataSingle[fieldParts[0]]);
    }
    if(fieldParts[0] && fieldParts[1]){
        isEmpty(fieldParts[0]+'___'+[fieldParts[1]], props.availabilityDataSingle[fieldParts[0]][fieldParts[1]]);
    }
};

</script>
 

<template> 
<HbPopup :isOpen="props.isOpen"   :max_width="max_width" :enableAvailabilityClass="true" @modal-close="emit('modal-close')"  name="first-modal">
        <template #header> 
            <h2> {{ __('Add New Availability', 'hydra-booking') }} </h2>   
        </template>

        <template #content>   
            <div class=" tfhb-availability-popup-wrap">
                <div class="tfhb-content-wrap "> 
                    <div class="tfhb-admin-card-box tfhb-flexbox">  
                        <!-- Title -->
                        <HbText  
                            v-model="props.availabilityDataSingle.title"  
                            required= "true"  
                            :label="__(' Availability title', 'hydra-booking')"  
                            selected = "1"
                            placeholder="Enter schedule title"   
                            @keyup="() => tfhbValidateInput('title')"
                            :errors="errors.title"
                        /> 
                        <!-- Title -->
                        <!-- Time Zone -->
                        <HbDropdown 
                                
                            v-model="props.availabilityDataSingle.time_zone"  
                            required= "true"  
                            :label="__('Time zone', 'hydra-booking')"  
                            selected = "1"
                            :filter="true"
                            placeholder="Select Time Zone"  
                            :option = "props.timeZone" 
                            :errors="errors.time_zone"
                        /> 
                        <!-- Time Zone --> 
                    </div>
                </div>
                <div class="tfhb-content-wrap tfhb-availability-content-wrap">  

                    <div class="tfhb-admin-card-box ">  
                        <div  class="tfhb-dashboard-heading ">
                            <div class="tfhb-availability-title"> 
                                <h3> {{ __('Weekly hours', 'hydra-booking') }} </h3>  
                            </div>
                            <div class="thb-admin-btn right"> 
                                <span>{{props.availabilityDataSingle.time_zone}}</span> 
                            </div> 
                        </div>
                        <div v-for="(time_slot, key) in props.availabilityDataSingle.time_slots" :key="key" class="tfhb-availability-schedule-single tfhb-flexbox tfhb-align-baseline tfhb-justify-between">
                            <div class="tfhb-swicher-wrap  tfhb-flexbox tfhb-gap-8">
                                <!-- Checkbox swicher -->
                                <label class="switch">
                                    <input :id="'swicher-'+key" v-model="time_slot.status" true-value="1"    type="checkbox">
                                    <span class="slider"></span>
                                </label>
                                <label class="tfhb-schedule-swicher" :for="'swicher-'+key"> {{time_slot.day}}</label>
                                <!-- Swicher -->
                            </div>
                            <div v-if="time_slot.status == 1" class="tfhb-availability-schedule-wrap "> 
                                <div v-for="(time, tkey) in time_slot.times" :key="tkey"  class="tfhb-availability-schedule-inner tfhb-flexbox">
                                    <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-no-wrap">
                                        <HbDropdown 
                                            v-model="time.start"  
                                            required= "true" 
                                            width="50"
                                            :selected = "1"
                                            :filter="true"
                                            icon="Clock"
                                            placeholder="Start"   
                                            :option = "AvailabilityTime.AvailabilityTime.timeSchedule"
                                            @tfhb_start_change="TfhbStartDataEvent"
                                            :parent_key = "key"
                                            :single_key = "tkey"
                                        />                
                                        <Icon name="MoveRight" size=20 /> 
                                        <HbDropdown 
                                            v-model="time.end"  
                                            required= "true" 
                                            :filter="true"
                                            width="50"
                                            :selected = "1"
                                            icon="Clock"
                                            placeholder="End"   
                                            :option = "AvailabilityTime.AvailabilityTime.timeSchedule"
                                            @tfhb_start_change="TfhbEndDataEvent"
                                            :parent_key = "key"
                                            :single_key = "tkey"
                                        />     

                                    </div>
                                    <div v-if="tkey == 0" class="tfhb-availability-schedule-clone-single">
                                        <button class="tfhb-availability-schedule-btn" @click="addAvailabilityTime(key)"><Icon name="Plus" size=20 /> </button> 
                                    </div>
                                    <div v-else class="tfhb-availability-schedule-clone-single">
                                        <button class="tfhb-availability-schedule-btn" @click="removeAvailabilityTime(key, tkey)"><Icon name="X" size=20 /> </button> 
                                    </div>
                                </div>
                                
                            </div>
                        </div> 
                    </div>  
                    <!-- Date Overrides -->
                    <div v-if="props.display_overwrite == true" class="tfhb-admin-card-box  tfhb-flexbox">  

                        <div  class="tfhb-dashboard-heading tfhb-overrides-heading tfhb-full-width" :style="{margin: '0 !important'}">
                            <div class="tfhb-admin-title"> 
                                <h3>{{ __('Add date overrides', 'hydra-booking') }}</h3>  
                                <p>{{ __('Add dates when your availability changes from your daily hours', 'hydra-booking') }}</p>
                            </div> 
                        </div>

                        <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" v-for="(date_slot, key) in props.availabilityDataSingle.date_slots" :key="key">
                            <div class="tfhb-flexbox">
                                <div class="tfhb-overrides-date">
                                    <h4>{{ date_slot.date }}</h4>
                                    <p class="tfhb-m-0">{{ date_slot.available!=1 ? formatTimeSlots(date_slot.times) : 'Unavailable' }}</p>
                                </div>
                                <div class="tfhb-overrides-action tfhb-flexbox tfhb-gap-16 tfhb-justify-normal">
                                    <button class="question-edit-btn" @click="editAvailabilityDate(key)">
                                        <Icon name="PencilLine" :width="16" />
                                    </button>
                                    <button class="question-edit-btn" @click="removeAvailabilityTDate(key)">
                                        <Icon name="Trash" :width="16"/>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Overrides Calendar Form -->

                        <div class="tfhb-overrides-add-form tfhb-flexbox tfhb-full-width" v-if="OverridesOpen">
                            <div class="tfhb-flexbox tfhb-align-normal">
                                <div class="tfhb-override-calendar">
                                    <HbDateTime  
                                        v-model="OverridesDates.date"
                                        selected = "1" 
                                        :config="{
                                            inline: true,
                                            monthSelectorType: 'static',
                                            yearSelectorType: 'static',
                                            mode: 'multiple',
                                            nextArrow: `<svg width='19' height='20' viewBox='0 0 19 20' fill='none' xmlns='http://www.w3.org/2000/svg'><g id='chevron-right'><path id='Vector' d='M7.5 15L12.5 10L7.5 5' stroke='$primary-default' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/></g></svg>`,
                                            prevArrow: `<svg width='19' height='20' viewBox='0 0 19 20' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M11.5 15L6.5 10L11.5 5' stroke='$primary-default' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/></svg>`
                                        }"
                                        placeholder="Enter schedule title"   
                                    /> 
                                </div>
                                <div class="tfhb-override-times">
                                    <h3>{{ __('Which hours are you free?', 'hydra-booking') }}</h3>

                                    <div class="tfhb-availability-schedule-inner tfhb-flexbox tfhb-gap-16 tfhb-mt-16" v-for="(time, tkey) in OverridesDates.times" :key="tkey" v-if="OverridesDates.available!=1">
                                        <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-gap-8">
                                            
                                            <HbDropdown 
                                                v-model="time.start"  
                                                required= "true" 
                                                width="45"
                                                :selected = "1"
                                                placeholder="Start"   
                                                :option = "AvailabilityTime.AvailabilityTime.timeSchedule"
                                            />  
                                            <Icon name="MoveRight" size=20 /> 
                                            <HbDropdown 
                                                v-model="time.end"  
                                                required= "true" 
                                                width="45"
                                                :selected = "1"
                                                placeholder="End"   
                                                :option = "AvailabilityTime.AvailabilityTime.timeSchedule"
                                            />  

                                        </div>
                                        
                                        <div v-if="tkey == 0" class="tfhb-availability-schedule-clone-single">
                                            <button class="tfhb-availability-schedule-btn" @click="addOverridesTime(key)"><Icon name="Plus" size=20 /> </button> 
                                        </div>
                                        <div v-else class="tfhb-availability-schedule-clone-single">
                                            <button class="tfhb-availability-schedule-btn" @click="removeOverridesTime(key, tkey)"><Icon name="X" size=20 /> </button> 
                                        </div>
                                    </div>

                                    <div class="tfhb-mark-unavailable tfhb-full-width tfhb-mt-16">
                                        <HbCheckbox 
                                            v-model="OverridesDates.available"
                                            :label="__('Mark unavailable (All day)', 'hydra-booking')"
                                            :name="'mark_unavailable'+key"
                                        />
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="tfhb-overrides-store tfhb-flexbox tfhb-gap-16 tfhb-justify-end tfhb-full-width">
                                <!-- <button class="tfhb-btn secondary-btn" @click="OverridesOpen=false">{{ __('Cancel', 'hydra-booking') }}</button> --> 
                                <HbButton 
                                    classValue="tfhb-btn boxed-btn flex-btn" 
                                    @click="addAvailabilityDate(key)"
                                    :buttonText="__('Add override', 'hydra-booking')"
                                    icon="ChevronRight" 
                                    hover_icon="ArrowRight" 
                                    :hover_animation="true" 
                                />  
                            </div>
                        </div>


                        <HbButton 
                            v-if="!OverridesOpen"
                            classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-p-0 tfhb-height-auto" 
                            @click="openOverridesCalendarDate()"
                            :buttonText="__('Add an override', 'hydra-booking')"
                            icon="PlusCircle"  
                            icon_position="left"
                        />  
                  

                    </div>  
 
                    <!-- Create Or Update Availability -->
                    <HbButton 
                            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                            @click="UpdateAvailabilitySettings(['title', 'time_zone'])" 
                            :buttonText="is_host ? __('Save Availability', 'hydra-booking'): __('Update Availability', 'hydra-booking')"
                            icon="ChevronRight" 
                            hover_icon="ArrowRight" 
                            :hover_animation="true"
                        />  
                    <!-- <button class="tfhb-btn boxed-btn" @click="UpdateAvailabilitySettings(['title', 'time_zone'])">{{ is_host ? __('Save Availability', 'hydra-booking'): __('Update Availability', 'hydra-booking')}}</button> -->
                </div>
            </div>
        </template> 
    </HbPopup>
   
   
</template> 
<style scoped>

</style> 
