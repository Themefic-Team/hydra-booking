<script setup>
import { __ } from '@wordpress/i18n';
import { onBeforeMount, ref, reactive } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
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
            // fetchSingleAvailabilitySettings( Meeting.singleMeeting.MeetingData.host_id, props.meeting.availability_id);
        }
    });
});
 
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
</script>

<template>  
{{Meeting.singleMeeting.MeetingData}}
    <div  class="tfhb-meeting-create" :class="{ 'tfhb-skeleton': false }">
        <div class="tfhb-meeting-create-notice tfhb-flexbox tfhb-mb-32">
            <div class="tfhb-meeting-heading-wrap tfhb-flexbox tfhb-gap-8">
                <div class="prev-navigator" @click="TfhbPrevNavigator()">
                        <Icon name="ArrowLeft" size=20 /> 
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

                <HbDropdown 
                    v-model="Meeting.singleMeeting.MeetingData.availability_id"
                    required= "true" 
                    :label="__('Choose Schedule', 'hydra-booking')"  
                    :selected = "1"
                    :placeholder="__('Choose Schedule', 'hydra-booking')"   
                    :option="HostAvailabilities.value"
                    v-if="host_availble_type != 'settings'"
                    @add-change="tfhbValidateInput('availability_id')" 
                    @add-click="tfhbValidateInput('availability_id')" 
                    :errors="errors.availability_id" 
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


               
                <div class="tfhb-submission-btn">
                    
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="updateMeetingData(['title',  'duration'])"
                        :buttonText="__('Save & Continue', 'hydra-booking')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"
                    />  
                </div>
                <!--Bookings -->

            </div>
        </div> 
    </div>
    
</template>