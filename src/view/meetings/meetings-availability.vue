<script setup>
import { __ } from '@wordpress/i18n';
import {ref, onBeforeMount, onMounted, reactive, computed} from 'vue'
import axios from 'axios'  
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbText from '@/components/form-fields/HbText.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import useValidators from '@/store/validator'
import { Availability } from '@/store/availability';
import AvailabilityTime from '@/store/times'
import { toast } from "vue3-toastify"; 
import { Host } from '@/store/hosts';
const { errors, isEmpty } = useValidators();

const user = tfhb_core_apps.user || '';
const user_id = user.id || '';
const host_id = user.host_id || '';
const user_role = user.role[0] || '';

const emit = defineEmits(["availability-time", "availability-time-del", "availability-date", "availability-date-del", "availability-tabs", "update-meeting", "add-overrides-time", "remove-overrides-time"]); 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },
    timeZone: {
        type: Object,
        required: true
    },
    update_preloader: {
        type: Boolean,
        required: true
    }

});


// Fetch Single Availability while Schdeule on change 
const Settings_avalibility = ref();
const fetchAvailabilitySettings = async (availability_id) => {
    
    if('tfhb_host' == user_role && props.meeting.host_id == ''){
        props.meeting.host_id = user_id
    }
    

    let data = {
        host_id: props.meeting.host_id,
        availability_id: availability_id
    };  
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/availability/single', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_integrations'
            } 
        } );
        if (response.data.status && response.data.availability) { 
            Settings_avalibility.value = response.data;
            Settings_avalibility.value.availability.time_slots = Availability.GeneralSettings.week_start_from ?  Availability.RearraingeWeekStart(Availability.GeneralSettings.week_start_from, Settings_avalibility.value.availability.time_slots) : Settings_avalibility.value.availability.time_slots;
            
        }
    } catch (error) {
        console.log(error);
    } 
}
const Settings_Avalibility_Callback = (value) => {
    if(value){
        fetchAvailabilitySettings(value);
    }
}

const tfhbValidateInput = (fieldName) => {
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    const fieldParts = fieldName.split('.');
    if(fieldParts[0] && !fieldParts[1]){
        isEmpty(fieldParts[0], props.meeting[fieldParts[0]]);
    }
    if(fieldParts[0] && fieldParts[1]){
        isEmpty(fieldParts[0]+'___'+[fieldParts[1]], props.meeting[fieldParts[0]][fieldParts[1]]);
    }
};

// Host Wise Availability
const HostAvailabilities = reactive([]);
const host_availble_type = ref('settings');
const fetchHostAvailability = async (host) => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/single-host-availability/'+host, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_integrations'
            } 
        }); 
        
        // Clear existing data before updating
        for (const key in HostAvailabilities) {
            delete HostAvailabilities[key];
        }
        host_availble_type.value = response.data.host_availble;
        if("settings"==response.data.host_availble){ 
            Settings_avalibility.value = response.data.host;
            props.meeting.availability_id = response.data.host.availability.id; 
            
        }else{
            Settings_avalibility.value = '';
            if(response.data.host.availability){

                let HostAvailabilitiesData = [];
                // use Each Loop
                for (const key in response.data.host.availability) {
                    HostAvailabilitiesData.push({
                        name: response.data.host.availability[key].title,
                        value: key // Adjust 'someValue' as per your data structure
                    });
                } 
                HostAvailabilities.value = HostAvailabilitiesData;
            }
        }
    } catch (error) {
        console.log(error);
    } 
}

const Host_Avalibility_Callback = (value) => {
    
    if(value){
        fetchHostAvailability(value);
    }
}

// Host Default Availability
const fetchSingleAvailabilitySettings = async (host_id, availability_id) => {
    let data = {
        host_id: host_id,
        availability_id: availability_id
    };  
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/availability/single', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );
        if (response.data.status && response.data.availability) {    
            Settings_avalibility.value = response.data;
        }
    } catch (error) {
        console.log(error);
    } 
}

// Mount
onBeforeMount(() => { 
    Availability.getGeneralSettings();
    Host.fetchHosts().then(() => {
        setTimeout(() => {
            if('tfhb_host' == user_role && props.meeting.host_id == ''){
           
                props.meeting.host_id = host_id 
            } 
            if(props.meeting.host_id!=0){
                fetchHostAvailability(props.meeting.host_id);
                fetchSingleAvailabilitySettings(props.meeting.host_id, props.meeting.availability_id);
            }
        }, 2000);
       
    });  
});
 

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

// Open Overrides Form
const openOverridesCalendarDate = () => {
    props.meeting.availability_custom.date_slots.push({
        date: '',
        available: '',
        times: [
            {
                start: '09:00',
                end: '17:00',
            }
        ]
    });

    const lastIndexOfQuestion = props.meeting.availability_custom.date_slots.length - 1;
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


// Remove Single Availability
const removeAvailabilityTDate = (key) => {
    props.meeting.availability_custom.date_slots.splice(key, 1);
}


// Store to the reactive
const addAvailabilityDate = (key) => {

    props.meeting.availability_custom.date_slots[OverridesDates.key].date = OverridesDates.date
    props.meeting.availability_custom.date_slots[OverridesDates.key].available = OverridesDates.available
    props.meeting.availability_custom.date_slots[OverridesDates.key].times = OverridesDates.times

    OverridesOpen.value = false;
}

const editAvailabilityDate = (key) => {
    props.meeting.availability_custom.date_slots.forEach((available, qkey) => {
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
    const day = props.meeting.availability_custom.time_slots[key];
    const latestEndTime = getLatestEndTime(day);

    if (startTime <= latestEndTime) {
        toast.error("Your start time will be over the: " + latestEndTime, {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });  
        return latestEndTime;
    }
}

const TfhbEndDataEvent = (key, skey, endTime) => {
    const day = props.meeting.availability_custom.time_slots[key];
    const nextDate = skey+1;
    const NextdayData = day.times[nextDate] ? day.times[nextDate].start : '';

    if(NextdayData){
        if ( day.times[skey].start >= endTime || NextdayData <= endTime) {
            toast.error("Your End time will be over the: " + day.times[[skey]].start +" And Less than " + NextdayDatas, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });   
            return;
        }
    }else{
        if (day.times[skey].start >= endTime) {
            toast.error("Your End time will be over the: " + day.times[[skey]].start, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });  
            return;
        }
    }
}

const isobjectempty = (data) => {
    return Object.keys(data).length === 0;
}
const CheckDateRangeStart = (date) => { 
    // if end date below of the sart date then set a alert and empty the end date
    if(props.meeting.availability_range.end!= '' && date > props.meeting.availability_range.end){
         
         toast.error("End date should be greater than or equal to Start dates", {
             position: 'bottom-right', // Set the desired position
             "autoClose": 1500,
         });   
     }
}
const CheckDateRangeEnd = (date) => { 
    // if end date below of the sart date then set a alert and empty the end date
    if(props.meeting.availability_range.start!= '' && date < props.meeting.availability_range.start){
         
        toast.error("End date should be greater than or equal to Start dates", {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });   
    }
}

const filteredDateSlots = computed(() => {
    return props.meeting?.availability_custom?.date_slots?.filter(slot => slot.date) ?? [];
});

</script>

<template>
    
    <div class="meeting-create-details tfhb-gap-24"> 
        <div class="tfhb-meeting-range tfhb-full-width">
            <div class="tfhb-admin-title" >
                {{ props.meeting.availability_range.start }}
                <h2>{{ $tfhb_trans('Availability Range for this Booking') }}</h2> 
                <p>{{ $tfhb_trans('How many days can the invitee schedule?') }}</p>
            </div>

            <div class="tfhb-flexbox tfhb-gap-0 tfhb-align-normal tfhb-justify-between">
                <div class="tfhb-single-meeting-range tfhb-admin-card-box tfhb-border-box tfhb-m-0 tfhb-align-baseline">
                    <label for="tfhb_continuos_date" class="tfhb-m-0 tfhb-flexbox tfhb-gap-16 tfhb-align-normal">
                        <div class="tfhb-range-checkbox">
                            <input id="tfhb_continuos_date" name="tfhb_range_date" value="indefinitely" type="radio" v-model="meeting.availability_range_type" :checked="meeting.availability_range_type == 'indefinitely' ? true : false">
                            <span class="checkmark"></span> 
                        </div>
                        <div class="tfhb-range-title">
                            <h4 class="tfhb-m-0">{{ $tfhb_trans('Indefinitely into the future') }}</h4> 
                            <p class="tfhb-m-0">{{ $tfhb_trans('Meeting will be go for indefinitely into the future') }}</p>
                        </div>
                    </label>
                </div>
                <div class="tfhb-single-meeting-range tfhb-admin-card-box tfhb-border-box tfhb-m-0 tfhb-align-baseline"> 
                    <label for="tfhb_specific_date" class="tfhb-m-0 tfhb-flexbox tfhb-gap-16 tfhb-align-normal">
                        <div class="tfhb-range-checkbox">
                            <input id="tfhb_specific_date" name="tfhb_range_date" type="radio" value="range" v-model="meeting.availability_range_type" :checked="meeting.availability_range_type == 'range' ? true : false">
                            <span class="checkmark"></span> 
                        </div>
                        <div class="tfhb-range-title">
                            <h4 class="tfhb-m-0">{{ $tfhb_trans('Specific date range') }}</h4> 
                            <p class="tfhb-m-0">{{ $tfhb_trans('Meeting will be only available on specific dates') }}</p>
                        </div>
                    </label>
                    <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-gap-4 tfhb-justify-between" v-if="meeting.availability_range_type == 'range'">
                        <HbDateTime   
                            v-model="meeting.availability_range.start"
                            icon="CalendarDays"
                            @dateChange="CheckDateRangeStart"
                            selected = "1" 
                            :config="{
                            }"
                            width="48"
                            :placeholder="$tfhb_trans('Start')"
                        /> 
                        <Icon name="MoveRight" size=15 /> 
                        <HbDateTime  
                            v-model="meeting.availability_range.end" 
                            @dateChange="CheckDateRangeEnd"
                            icon="CalendarDays" 
                            selected = "1"
                            :config="{
                            }"
                            width="48"
                            :placeholder="$tfhb_trans('End')"   
                        /> 

                    </div>
                </div>
            </div>
        </div>
        <!-- Select Host --> 
        <HbDropdown 
            v-if="'tfhb_host' != user_role"
            v-model="meeting.host_id"
            required= "true" 
            :label="$tfhb_trans('Select Host')"  
            name="host_id"
            :placeholder="$tfhb_trans('Select Host')"  
            :option = "Host.hosts" 
            :errors="errors.host_id"
            @tfhb-onchange="Host_Avalibility_Callback"
        />

        <div class="tfhb-add-moreinfo tfhb-full-width" v-if="isobjectempty(Host.hosts) && 'tfhb_host' != user_role">
            <router-link :to="'/hosts/list'" exact :class="'tfhb-btn tfhb-inline-flex tfhb-gap-8 tfhb-justify-normal tfhb-height-auto'">
                <Icon name="PlusCircle" :width="20"/>
                {{$tfhb_trans('Create Host')}}
            </router-link>
        </div>

        <div class="tfhb-availaility-tabs">
            <ul class="tfhb-flexbox tfhb-gap-16">
                <li class="tfhb-flexbox tfhb-gap-8" :class="'settings'==meeting.availability_type ? 'active' : ''" @click="emit('availability-tabs', 'settings')"><Icon name="Heart" :width="20" /> {{ $tfhb_trans('Use existing availability') }}</li>
                <li class="tfhb-flexbox tfhb-gap-8" :class="'custom'==meeting.availability_type ? 'active' : ''" @click="emit('availability-tabs', 'custom')"><Icon name="PencilLine" :width="20" /> {{ $tfhb_trans('Custom availability') }}</li>
            </ul>
        </div>
        <!-- Choose Schedule -->

        <HbDropdown 
            v-model="meeting.availability_id"
            required= "true" 
            :label="$tfhb_trans('Choose Schedule')"  
            :selected = "1"
            :placeholder="$tfhb_trans('Choose Schedule')"   
            :option="HostAvailabilities.value"
            v-if="'settings'==meeting.availability_type && host_availble_type != 'settings'"
            :errors="errors.availability_id"
            @tfhb-onchange="Settings_Avalibility_Callback"
        />

        <HbText 
            v-model="meeting.availability_custom.title"
            required= "true" 
            :label="$tfhb_trans('Choose Schedule')"  
            :placeholder="$tfhb_trans('Availability title')"   
            v-if="'custom'==meeting.availability_type"
            :errors="errors.availability_custom___title"
        /> 
        <!-- Time Zone --> 
        <HbDropdown 
            
            v-model="meeting.availability_custom.time_zone"  
            required= "true"  
            :label="$tfhb_trans('Time zone')"  
            :filter="true"
            selected = "1"
            :placeholder="$tfhb_trans('Select Time Zone')"  
            :option = "props.timeZone" 
            v-if="'custom'==meeting.availability_type"
            :errors="errors.availability_custom___time_zone"
        /> 
        <!-- Time Zone --> 
        <!-- Settings Data -->
        
        <div class="tfhb-admin-card-box tfhb-gap-24 tfhb-full-width tfhb-availability-details-wrap tfhb-m-0" v-if="Settings_avalibility && 'settings'==meeting.availability_type">  
            <div  class="tfhb-availability-schedule-single tfhb-schedule-heading tfhb-flexbox tfhb-justify-between">
                <div class="tfhb-admin-title"> 
                    <h3> {{ $tfhb_trans('Schedule Preview') }} </h3>  
                </div>
                <div class="thb-admin-btn right"> 
                    <span>{{ Settings_avalibility.availability.time_zone }}</span> 
                </div> 
            </div>
            
            <div v-for="(time_slot, key) in Settings_avalibility.availability.time_slots" :key="key" class="tfhb-availability-schedule-single tfhb-flexbox  tfhb-justify-between">
                <div class="tfhb-swicher-wrap tfhb-gap-8  tfhb-flexbox">
                    <!-- Checkbox swicher -->
                    <label class="switch">
                        <input id="swicher" disabled v-model="time_slot.status" true-value="1" type="checkbox">
                        <span class="slider"></span>
                    </label>
                    <label class="tfhb-schedule-swicher" for="swicher"> {{time_slot.day}}</label>
                    <!-- Swicher -->
                </div>
                <div v-if="time_slot.status == 1" class="tfhb-availability-schedule-wrap"> 
                    <div v-for="(time, tkey) in time_slot.times" :key="tkey" class="tfhb-availability-schedule-inner tfhb-flexbox">
                        <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-gap-8 tfhb-justify-between">
                            
                            <div class="tfhb-single-form-field" style="width: calc(45% - 12px);" selected="1">
                                <div class="tfhb-single-form-field-wrap tfhb-field-date">
                                    <input type="text" data-input="true" class="flatpickr-input" :value="time.start" readonly="readonly">
                                    <span class="tfhb-flat-icon">
                                        <Icon name="Clock4" size=20 />
                                    </span>
                                </div>
                            </div>

                            <Icon name="MoveRight" size=20 /> 

                            <div class="tfhb-single-form-field" style="width: calc(45% - 12px);" selected="1">
                                <div class="tfhb-single-form-field-wrap tfhb-field-date">
                                    <input type="text" data-input="true" class="flatpickr-input" :value="time.end" readonly="readonly">
                                    <span class="tfhb-flat-icon">
                                        <Icon name="Clock4" size=20 />
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                </div>
                <div v-else class="tfhb-availability-schedule-wrap"> 
                   <h6 class="tfhb-availability-schedule">{{ $tfhb_trans('Unavailable') }}</h6>
                </div>
            </div> 
        </div>  
        <!-- Date Overrides --> 
        <div class="tfhb-admin-card-box tfhb-m-0 tfhb-flexbox tfhb-full-width" v-if="Settings_avalibility?.availability?.date_slots?.length > 0 && meeting.availability_type === 'settings'">  
            <div  class="tfhb-dashboard-heading tfhb-full-width" :style="{margin: '0 !important'}">
                <div class="tfhb-admin-title tfhb-m-0"> 
                    <h3>{{ $tfhb_trans('Overrides preview') }} </h3>  
                    <p>{{ $tfhb_trans('Overrides preview for this avalibility') }}</p>
                </div> 
            </div>

            <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" v-for="(date_slot, key) in Settings_avalibility.availability.date_slots" :key="key">
                <div class="tfhb-flexbox tfhb-full-width">
                    <div class="tfhb-overrides-date">
                        <h4>{{ date_slot.date }}</h4>
                        <p class="tfhb-m-0">{{ date_slot.available!=1 ? formatTimeSlots(date_slot.times) : 'Unavailable' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Data -->
        <div class="tfhb-admin-card-box tfhb-gap-24 tfhb-m-0" v-if="'custom'==meeting.availability_type">  
            <div  class="tfhb-availability-schedule-single tfhb-schedule-heading tfhb-flexbox tfhb-justify-between">
                <div class="tfhb-admin-title"> 
                    <h3> {{ $tfhb_trans('Weekly hours') }} </h3>  
                </div>
                <div class="thb-admin-btn right"> 
                    <span>{{ meeting.availability_custom.time_zone }}</span> 
                </div> 
            </div>
            
            <div v-for="(time_slot, key) in meeting.availability_custom.time_slots" :key="key" class="tfhb-availability-schedule-single tfhb-flexbox tfhb-align-baseline tfhb-justify-between">
                <div class="tfhb-swicher-wrap tfhb-gap-8 tfhb-flexbox">
                    <!-- Checkbox swicher -->
                    <label class="switch">
                        <input id="swicher" v-model="time_slot.status" true-value="1" type="checkbox">
                        <span class="slider"></span>
                    </label>
                    <label class="tfhb-schedule-swicher" for="swicher"> {{time_slot.day}}</label>
                    <!-- Swicher -->
                </div>
                <div v-if="time_slot.status == 1" class="tfhb-availability-schedule-wrap"> 
                    <div v-for="(time, tkey) in time_slot.times" :key="tkey" class="tfhb-availability-schedule-inner tfhb-flexbox tfhb-gap-8 tfhb-justify-between">
                        <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-no-wrap tfhb-gap-8">

                            <HbDropdown 
                                v-model="time.start"  
                                required= "true" 
                                width="60"
                                :selected = "1"
                                placeholder="Start"   
                                :option = "AvailabilityTime.AvailabilityTime.timeSchedule"
                                @tfhb_start_change="TfhbStartDataEvent"
                                icon="Clock"
                                :parent_key = "key"
                                :single_key = "tkey"
                            />                
                            <Icon name="MoveRight" size=20 /> 
                            <HbDropdown 
                                v-model="time.end"  
                                required= "true" 
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
                            <button class="tfhb-availability-schedule-btn" @click="emit('availability-time',key)"><Icon name="Plus" size=20 /> </button> 
                        </div>
                        <div v-else class="tfhb-availability-schedule-clone-single">
                            <button class="tfhb-availability-schedule-btn" @click="emit('availability-time-del',key, tkey)"><Icon name="X" size=20 /> </button> 
                        </div>
                    </div>
                    
                </div>
            </div> 
        </div>  

         <!-- Date Overrides -->
         <div v-if="'custom'==meeting.availability_type" class="tfhb-admin-card-box tfhb-m-0 tfhb-flexbox tfhb-full-width">  

            <div  class="tfhb-dashboard-heading tfhb-full-width" :style="{margin: '0 !important'}">
                <div class="tfhb-admin-title"> 
                    <h3>{{ $tfhb_trans('Add date overrides') }} </h3>  
                    <p>{{ $tfhb_trans('Add dates when your availability changes from your daily hours') }}</p>
                </div> 
            </div>

            <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" v-for="(date_slot, key) in filteredDateSlots" :key="key">
                <div class="tfhb-flexbox tfhb-full-width">
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
                        <h3>{{ $tfhb_trans('Which hours are you free?') }}</h3>

                        <div class="tfhb-availability-schedule-inner tfhb-flexbox tfhb-gap-16 tfhb-mt-16" v-for="(time, tkey) in OverridesDates.times" :key="tkey" v-if="OverridesDates.available!=1">
                            <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-gap-8 tfhb-justify-between"> 
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
                                :label="$tfhb_trans('Mark unavailable (All day)')"
                                :name="'mark_unavailable'+key"
                            />
                        </div>
                        
                    </div>
                </div>

                <div class="tfhb-overrides-store tfhb-flexbox tfhb-gap-16 tfhb-justify-end tfhb-full-width">
                    <button class="tfhb-btn secondary-btn" @click="OverridesOpen=false">{{ $tfhb_trans('Cancel') }}</button>
                    <button class="tfhb-btn boxed-btn" @click="addAvailabilityDate(key)">{{ $tfhb_trans('Add override') }}</button>
                </div>
            </div>


            <button class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-p-0 tfhb-height-auto" @click="openOverridesCalendarDate()" v-if="!OverridesOpen">
                <Icon name="PlusCircle" :width="20"/>
                {{ $tfhb_trans('Add an override') }}
            </button>

        </div>  


        <div class="tfhb-submission-btn"> 
            <HbButton 
                v-if="'settings'==meeting.availability_type"
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting', ['host_id'])"
                :buttonText="$tfhb_trans('Save & Continue')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
            />  
            <HbButton 
                v-if="'custom'==meeting.availability_type"
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting', ['host_id', 'availability_custom___title', 'availability_custom___time_zone'])"
                :buttonText="$tfhb_trans('Save & Continue')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :pre_loader="props.update_preloader"
                :hover_animation="true"
            />   
        </div>
        <!--Bookings -->
    </div>
</template>