<script setup>
import { __ } from '@wordpress/i18n';
import { onBeforeMount, ref, reactive } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbPreloader from '@/components/icon/HbPreloader.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import AvailabilityTime from '@/store/times';
import Icon from '@/components/icon/LucideIcon.vue'

import HbPopup from '@/components/widgets/HbPopup.vue';
import useValidators from '@/store/validator';
import { toast } from "vue3-toastify"; 
import axios from 'axios' 
const { errors, isEmpty } = useValidators();
import { Meeting } from '@/store/meetings';
import { Host } from '@/store/hosts';

const router = useRouter();
const route = useRoute();
const user = tfhb_core_apps.user || '';
const user_id = user.id || '';
const user_role = user.role[0] || '';
const HostAvailabilities = reactive([]);
const host_availble_type = ref('settings');
const Settings_avalibility = ref();

const tfhbValidateInput = (fieldName) => {
    
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });

    const fieldParts = fieldName.split('.');
    if(fieldParts[0] && !fieldParts[1]){
        isEmpty(fieldParts[0], Meeting.singleMeeting[fieldParts[0]]);
    }
    if(fieldParts[0] && fieldParts[1]){
        isEmpty(fieldParts[0]+'___'+[fieldParts[1]], Meeting.singleMeeting[fieldParts[0]][fieldParts[1]]);
    }
}; 
const meetingId = route.params.id;

onBeforeMount(() => { 
    
    Meeting.fetchSingleMeeting(meetingId);
    // Meeting.singleMeeting.MeetingData.id = meetingId; 

    Host.fetchHosts().then(() => {
        if('tfhb_host' == user_role && Meeting.singleMeeting.MeetingData.host_id == ''){
           
            Meeting.singleMeeting.MeetingData.host_id = user_id 
        } 
        if(Meeting.singleMeeting.MeetingData.host_id!=0){
            
            fetchHostAvailability(Meeting.singleMeeting.MeetingData.host_id);
            fetchSingleAvailabilitySettings( Meeting.singleMeeting.MeetingData.host_id, Meeting.singleMeeting.MeetingData.availability_id);
        }
    });
 
    // if previuse page is MeetingsLists then show the toast message
    // if(route.query && route.query.success){
    //     toast.success('Meeting Updated Successfully', {
    //         position: 'bottom-right', // Set the desired position
    //         "autoClose": 1500,
    //     });
    // }

  
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
   Meeting.singleMeeting.MeetingData.availability_custom.date_slots.push({
        date: '',
        available: '',
        times: [
            {
                start: '09:00',
                end: '17:00',
            }
        ]
    });

    const lastIndexOfQuestion = Meeting.singleMeeting.MeetingData.availability_custom.date_slots.length - 1;
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
    Meeting.singleMeeting.MeetingData.availability_custom.date_slots.splice(key, 1);
}


function updateMeetingData(validator_field){
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!Meeting.singleMeeting.MeetingData[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!Meeting.singleMeeting.MeetingData[fieldParts[0]][fieldParts[1]]){
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
   

    Meeting.updateSingleMeetingData(router);

}

function addMoreLocations(){
    Meeting.singleMeeting.MeetingData.meeting_locations.push({
        location: '',
        address: ''
    });
    
}
// Remove Location
const removeLocations = (key) => {
    Meeting.singleMeeting.MeetingData.meeting_locations.splice(key, 1);
}
// trunkate string 
const truncateString = (str, num) => {
    if (str.length <= num) {
        return str
    }
    return str.slice(0, num) + '...'
}
const Host_Avalibility_Callback = (value) => {
    
    if(value){
        fetchHostAvailability(value);
    }
}

const fetchAvailabilitySettings = async (availability_id) => {
    
    if('tfhb_host' == user_role &&Meeting.singleMeeting.MeetingData.host_id == ''){
       Meeting.singleMeeting.MeetingData.host_id = user_id
    }
    

    let data = {
        host_id:Meeting.singleMeeting.MeetingData.host_id,
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
            Meeting.singleMeeting.MeetingData.availability_id = response.data.host.availability.id; 
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

const beck_to_prev = ref(false);
const TfhbPrevNavigator = () => {
    beck_to_prev.value = true;
    router.push({ name: 'MeetingsLists' });

    // wait for 1 second and then set the value to false
    setTimeout(() => {
        beck_to_prev.value = false;
    }, 1000);
    
}

// Meeting Type
const AvailabilityTabs = (type) => {
    Meeting.singleMeeting.MeetingData.availability_type = type
}
</script>

<template>  
 
    <div class="tfhb-admin-meetings" >
        <div  class="tfhb-meeting-create" :class="{ 'tfhb-skeleton': Meeting.skeleton }">
            <div class="tfhb-meeting-create-notice tfhb-flexbox tfhb-mb-32">
                <div class="tfhb-meeting-heading-wrap tfhb-flexbox tfhb-gap-8">
                    <div class="prev-navigator" @click="TfhbPrevNavigator">
                            <Icon v-if="beck_to_prev == false" name="ArrowLeft" size=20 /> 
                            <HbPreloader v-else color="#2E6B38" />
                    </div>
                    <div class="tfhb-meeting-heading tfhb-flexbox">
                    
                        <h3 v-if="Meeting.singleMeeting.MeetingData.title != '' && Meeting.singleMeeting.MeetingData.title != null">{{ truncateString(Meeting.singleMeeting.MeetingData.title, 110) }}</h3>
                        <h3 v-else-if="Meeting.singleMeeting.MeetingData.type == 'one-to-one'" >{{ __('Create One-to-One booking', 'hydra-booking') }}</h3>
                        <h3 v-else >{{ __('Create One-to-Group booking', 'hydra-booking') }}</h3>
                    </div> 
                    <!-- <div  class="tfhb-meeting-subtitle">
                        {{ __('Create and manage booking/appointment form', 'hydra-booking') }}
                    </div> -->
                </div>
            
            </div>

            <div class="tfhb-hydra-dasboard-content">   
                <div class="meeting-create-details tfhb-gap-24">
                    <HbText  
                        v-model="Meeting.singleMeeting.MeetingData.title" 
                        required= "true"  
                        :label="__('Meeting title', 'hydra-booking')"  
                        name="title"
                        selected = "1"
                        :placeholder="__('Type meeting title', 'hydra-booking')" 
                        @keyup="() => tfhbValidateInput('title')"
                        @click="() => tfhbValidateInput('title')"
                        :errors="errors.title"
                    /> 
                    <HbTextarea  
                        v-model="Meeting.singleMeeting.MeetingData.description" 
                        required= "false"  
                        name="description"
                        :label="__('Description', 'hydra-booking')"  
                        :placeholder="__('Describe about meeting', 'hydra-booking')" 
                    /> 
 
                    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-16 tfhb-m-0 tfhb-full-width"> 
                        <!-- Duration -->
                        <HbDropdown 
                            v-model="Meeting.singleMeeting.MeetingData.duration" 
                            required= "true" 
                            :label="__('Duration', 'hydra-booking')"  
                            :selected = "1"
                            name="duration"
                            placeholder="Select Meetings Duration"  
                            :option = "[
                                {name: '15 minutes', value: '15'}, 
                                {name: '30 minutes', value: '30'},
                                {name: '45 minutes', value: '45'},
                                {name: '60 minutes', value: '60'},
                                {name: 'Custom', value: 'custom'} 
                            ]" 
                            @add-change="tfhbValidateInput('duration')" 
                            @add-click="tfhbValidateInput('duration')" 
                            :errors="errors.duration"
                        />
                        <!-- Duration -->
                        <!-- Custom Duration -->
                        <HbText  
                            v-model="Meeting.singleMeeting.MeetingData.custom_duration"  
                            :label="__('Custom Duration', 'hydra-booking')"  
                            name="title"
                            type="number"
                            selected = "1"
                            :placeholder="__('Type Custom Duration', 'hydra-booking')"  
                            v-if="'custom'==Meeting.singleMeeting.MeetingData.duration"
                        /> 
                        <!-- Custom Duration -->
                        <!-- <HbSwitch 
                            type="checkbox" 
                            required= "true" 
                            :label="__('Allow attendee to select duration', 'hydra-booking')" 
                        /> -->
                    </div>

                    <div class="tfhb-admin-card-box tfhb-no-flexbox tfhb-m-0 tfhb-full-width"> 
                        <div class="tfhb-flexbox tfhb-gap-16 tfhb-mb-24" v-for="(slocation, index) in Meeting.singleMeeting.MeetingData.meeting_locations" :key="index">
                            <div class="tfhb-meeting-location tfhb-flexbox tfhb-gap-16" :style="Meeting.singleMeeting.MeetingData.meeting_locations.length<2 ?'width:100%' : '' ">
                                <!-- Location --> 
                                <HbDropdown 
                                    v-model="slocation.location" 
                                    required= "true" 
                                    :label="__('Location', 'hydra-booking')"  
                                    :selected = "1"
                                    :placeholder="__('Location', 'hydra-booking')" 
                                    :option = "[
                                        {name: 'Zoom', value: 'zoom', disable:  Meeting.singleMeeting.integrations.zoom_meeting_status, icon: 'Fullscreen'}, 
                                        {name: 'Google Meet', value: 'meet', disable: Meeting.singleMeeting.integrations.google_calendar_status}, 
                                        {name: 'In Person (Attendee Address)', value: 'In Person (Attendee Address)',},
                                        {name: 'In Person (Organizer Address)', value: 'In Person (Organizer Address)'},
                                        {name: 'Attendee Phone Number', value: 'Attendee Phone Number'},
                                        {name: 'Organizer Phone Number', value: 'Organizer Phone Number'},
                                        {name: 'Add Custom', value: 'Add Custom'}
                                    ]" 
                                    :width= "50"
                                />
                                <!-- Address -->
                                <HbText  
                                    v-model="slocation.address" 
                                    required= "true"  
                                    :label="__('Address', 'hydra-booking')"  
                                    selected = "1"
                                    :placeholder="__('Enter Address', 'hydra-booking')" 
                                    :width= "50"
                                    v-if="'In Person (Organizer Address)'== slocation.location "
                                /> 
                                <HbText  
                                    v-model="slocation.address" 
                                    required= "true"  
                                    :label="__('Add Custom Location', 'hydra-booking')"  
                                    selected = "1"
                                    :placeholder="__('Enter Address', 'hydra-booking')" 
                                    :width= "50"
                                    v-if="'Add Custom'==slocation.location"
                                /> 
                                <HbText  
                                    v-model="slocation.address" 
                                    type="number"
                                    required= "true"  
                                    :label="__('Phone Number', 'hydra-booking')"  
                                    selected = "1"
                                    :placeholder="__('Enter Phone Number', 'hydra-booking')" 
                                    :width= "50"
                                    v-if="'Organizer Phone Number'==slocation.location"
                                /> 
                            </div>
                            <div class="tfhb-meeting-location-removed" v-if="Meeting.singleMeeting.MeetingData.meeting_locations.length>1" @click="removeLocations(index)">
                                <Icon name="Trash" :width="16" />
                            </div>
                        </div>
                        <div class="tfhb-add-new-question">
                        
                            <button @click="addMoreLocations" class="tfhb-btn tfhb-inline-flex tfhb-gap-8 tfhb-justify-normal tfhb-height-auto">
                                <Icon name="PlusCircle" :width="20"/>
                                {{ __('Add Another Location', 'hydra-booking') }}
                            </button> 
                        </div>
                    </div>

                    <div v-if="Meeting.singleMeeting.MeetingData.meeting_type == 'one-to-group'" class="tfhb-admin-card-box tfhb-no-flexbox tfhb-m-0 tfhb-full-width">  
                        <div class="tfhb-meeting-location tfhb-flexbox tfhb-gap-16" > 
                            <HbText  
                                    v-model="Meeting.singleMeeting.MeetingData.max_book_per_slot"  
                                    type= "number"
                                    :label="__('Max invitees in a spot', 'hydra-booking')"   
                                    :placeholder="'Max invitees in a spot'" 
                                    :width= "100"
                                
                                /> 

                                <HbSwitch 
                                    v-model="Meeting.singleMeeting.MeetingData.is_display_max_book_slot" 
                                    type="checkbox" 
                                    required= "true" 
                                    :label="__('Display remaining spots on booking page', 'hydra-booking')" 
                                />
                        </div>  
                    </div>

                    <!-- Select Host -->
                    <HbDropdown 
                        v-if="'tfhb_host' != user_role"
                        v-model="Meeting.singleMeeting.MeetingData.host_id"
                        required= "true" 
                        :label="__('Select Host', 'hydra-booking')"  
                        name="host_id"
                        :placeholder="__('Select Host', 'hydra-booking')"  
                        :option = "Host.hosts" 
                        @add-change="tfhbValidateInput('host_id')" 
                        @add-click="tfhbValidateInput('host_id')" 
                        :errors="errors.host_id"
                        @tfhb-onchange="Host_Avalibility_Callback"
                    />

                    <!-- Choose Schedule -->

                 

                    <div v-if="Meeting.singleMeeting.MeetingData.host_id != 0" class="tfhb-availaility-tabs">
                        <ul class="tfhb-flexbox tfhb-gap-16">
                            <li class="tfhb-flexbox tfhb-gap-8" :class="'settings'==Meeting.singleMeeting.MeetingData.availability_type ? 'active' : ''" @click="AvailabilityTabs('settings')"><Icon name="Heart" :width="20" /> {{ __('Use existing availability', 'hydra-booking') }}</li>
                            <li class="tfhb-flexbox tfhb-gap-8" :class="'custom'==Meeting.singleMeeting.MeetingData.availability_type ? 'active' : ''" @click="AvailabilityTabs('custom') "><Icon name="PencilLine" :width="20" /> {{ __('Custom availability', 'hydra-booking') }}</li>
                        </ul>
                    </div>
                    <!-- Choose Schedule -->

                    <HbDropdown 
                        v-model="Meeting.singleMeeting.MeetingData.availability_id"
                        required= "true" 
                        :label="__('Choose Schedule', 'hydra-booking')"  
                        :selected = "1"
                        :placeholder="__('Choose Schedule', 'hydra-booking')"   
                        :option="HostAvailabilities.value"
                        v-if="host_availble_type != 'settings'" 
                        :errors="errors.availability_id" 
                    />
                
                    <HbText 
                        v-model="Meeting.singleMeeting.MeetingData.availability_custom.title"
                        required= "true" 
                        :label="__('Choose Schedule', 'hydra-booking')"  
                        :placeholder="__('Availability title', 'hydra-booking')"   
                        v-if="'custom'==Meeting.singleMeeting.MeetingData.availability_type" 
                        :errors="errors.availability_custom___title"
                    /> 
                    <!-- Time Zone --> 
                    <HbDropdown 
                        
                        v-model="Meeting.singleMeeting.MeetingData.availability_custom.time_zone"  
                        required= "true"  
                        :label="__('Time zone', 'hydra-booking')"  
                        :filter="true"
                        selected = "1"
                        placeholder="Select Time Zone"  
                        :option = "Meeting.time_zone" 
                        v-if="'custom'==Meeting.singleMeeting.MeetingData.availability_type"
                        @add-change="tfhbValidateInput('availability_custom.time_zone')" 
                        @add-click="tfhbValidateInput('availability_custom.time_zone')" 
                        :errors="errors.availability_custom___time_zone"
                    /> 
                    <!-- Time Zone --> 
                    <!-- Settings Data -->
                    
                    <div class="tfhb-admin-card-box tfhb-gap-24 tfhb-full-width tfhb-availability-details-wrap tfhb-m-0" v-if="Settings_avalibility && 'settings'==Meeting.singleMeeting.MeetingData.availability_type">  
                        <div  class="tfhb-availability-schedule-single tfhb-schedule-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-admin-title"> 
                                <h3> {{ __('Schedule Preview', 'hydra-booking') }} </h3>  
                            </div>
                            <div class="thb-admin-btn right"> 
                                <span>{{ Settings_avalibility.availability.time_zone }}</span> 
                            </div> 
                        </div>
                        
                        <div v-for="(time_slot, key) in Settings_avalibility.availability.time_slots" :key="key" class="tfhb-availability-schedule-single tfhb-flexbox tfhb-align-baseline tfhb-justify-between">
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
                            <h4 class="tfhb-availability-schedule">Unavailable</h4>
                            </div>
                        </div>  
                    </div>  
                    <!-- Date Overrides --> 
                    <div class="tfhb-admin-card-box tfhb-m-0 tfhb-flexbox tfhb-full-width tfhb-justify-between" v-if=" Settings_avalibility && 'settings'==Meeting.singleMeeting.MeetingData.availability_type && Settings_avalibility.availability.date_slots.length > 0">  
                        <div  class="tfhb-dashboard-heading tfhb-full-width" :style="{margin: '0 !important'}">
                            <div class="tfhb-admin-title tfhb-m-0"> 
                                <h3>{{ __('Add date overrides', 'hydra-booking') }} </h3>  
                                <p>{{ __('Add dates when your availability changes from your daily hours', 'hydra-booking') }}</p>
                            </div> 
                        </div>

                        <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" v-for="(date_slot, key) in Settings_avalibility.availability.date_slots" :key="key">
                            <div class="tfhb-flexbox tfhb-full-width">
                                <div class="tfhb-overrides-date">
                                    <h4>{{ date_slot.date }}</h4>
                                    <p class="tfhb-m-0">{{ date_slot.available!=1 ? date_slot.times : 'Unavailable' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Custom Data -->
                    <div class="tfhb-admin-card-box tfhb-gap-24  tfhb-m-0" v-if="'custom'==Meeting.singleMeeting.MeetingData.availability_type">  
                        <div  class="tfhb-availability-schedule-single tfhb-schedule-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-admin-title"> 
                                <h3> {{ __('Weekly hours', 'hydra-booking') }} </h3>  
                            </div>
                            <div class="thb-admin-btn right"> 
                                <span>{{ Meeting.singleMeeting.MeetingData.availability_custom.time_zone }}</span> 
                            </div> 
                        </div>
                        
                        <div v-for="(time_slot, key) in Meeting.singleMeeting.MeetingData.availability_custom.time_slots" :key="key" class="tfhb-availability-schedule-single tfhb-flexbox tfhb-align-baseline tfhb-justify-between">
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
                                    <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-no-wrap tfhb-gap-8 tfhb-justify-between">

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

                    <!-- custom Date Overrides -->
                    <div v-if="'custom'==Meeting.singleMeeting.MeetingData.availability_type" class="tfhb-admin-card-box tfhb-m-0 tfhb-flexbox tfhb-full-width">  

                        <div  class="tfhb-dashboard-heading tfhb-full-width" :style="{margin: '0 !important'}">
                            <div class="tfhb-admin-title"> 
                                <h3>{{ __('Add date overrides', 'hydra-booking') }} </h3>  
                                <p>{{ __('Add dates when your availability changes from your daily hours', 'hydra-booking') }}</p>
                            </div> 
                        </div>

                        <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" v-for="(date_slot, key) in Meeting.singleMeeting.MeetingData.availability_custom.date_slots" :key="key">
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
                                <button class="tfhb-btn secondary-btn" @click="OverridesOpen=false">{{ __('Cancel', 'hydra-booking') }}</button>
                                <button class="tfhb-btn boxed-btn" @click="addAvailabilityDate(key)">{{ __('Add override', 'hydra-booking') }}</button>
                            </div>
                        </div>


                        <button class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-p-0 tfhb-height-auto" @click="openOverridesCalendarDate()">
                            <Icon name="PlusCircle" :width="20"/>
                            {{ __('Add an override', 'hydra-booking') }}
                        </button>

                    </div>  



                    <div class="tfhb-submission-btn">
                        
                        <HbButton 
                            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                            @click="updateMeetingData(['title',  'duration'])"
                            :buttonText="__('Save & Continue', 'hydra-booking')"
                            icon="ChevronRight" 
                            hover_icon="ArrowRight" 
                            :hover_animation="true"
                            :pre_loader="Meeting.pre_loader"
                        />  
                    </div>
                    <!--Bookings -->

                </div>
            </div> 
        </div>
    </div>
    
    
</template>

<style scoped>
.prev-navigator{
                        
    svg{
        transition: 0.2s;
        cursor: pointer;
    }
    &:hover{
        svg{
            color:  #2E6B38;
        }
    }
}
</style>