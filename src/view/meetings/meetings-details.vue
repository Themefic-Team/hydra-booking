<script setup>
import { __ } from '@wordpress/i18n';
import { defineProps, defineEmits, ref } from 'vue';
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

const emit = defineEmits(["add-more-location", "remove-meeting-location", "update-meeting"]); 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },
    integrations: {
        type: Object,
        required: true
    },
    meetingCategory: {
        type: Object,
        required: true
    },
    timeZone: {
        type: Object,
        required: true
    },

});
const createMeetingPopup = ref(false);

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
const CategoryData = {
    id: '',
    title: '',
    description: '',
};
// Create and Update Category
const UpdateCategory = async () => { 
    console.log(CategoryData);
    try { 
        const response = await axios.post(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/meetings/categories/create-update', CategoryData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );
      
        if (response.data.status) {  
   
            let category = response.data.category;
            let categoryList = [];
            category.forEach(element => {
                categoryList.push({name: element.name, value: element.id});
            });
            props.meetingCategory.value = categoryList; 

            // Set Default
            CategoryData.id = '';
            CategoryData.title = '';
            CategoryData.description = '';
            createMeetingPopup.value = false;
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}

// close popup
const closePopup = () => {
    createMeetingPopup.value = false;
}
 

</script>

<template> 
    <div class="meeting-create-details tfhb-gap-24">
        <HbText  
            v-model="meeting.title" 
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
            v-model="meeting.description" 
            required= "false"  
            name="description"
            :label="__('Description', 'hydra-booking')"  
            :placeholder="__('Describe about meeting', 'hydra-booking')" 
        /> 

        <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-16 tfhb-m-0 tfhb-full-width"> 
            <!-- Duration -->
            <HbDropdown 
                v-model="meeting.duration" 
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
                v-model="meeting.custom_duration"  
                :label="__('Custom Duration', 'hydra-booking')"  
                name="title"
                type="number"
                selected = "1"
                :placeholder="__('Type Custom Duration', 'hydra-booking')"  
                v-if="'custom'==meeting.duration"
            /> 
             <!-- Custom Duration -->
            <!-- <HbSwitch 
                type="checkbox" 
                required= "true" 
                :label="__('Allow attendee to select duration', 'hydra-booking')" 
            /> -->
        </div>

        <div class="tfhb-admin-card-box tfhb-no-flexbox tfhb-m-0 tfhb-full-width"> 
            <div class="tfhb-flexbox tfhb-gap-16 tfhb-mb-24" v-for="(slocation, index) in meeting.meeting_locations" :key="index">
                <div class="tfhb-meeting-location tfhb-flexbox tfhb-gap-16" :style="meeting.meeting_locations.length<2 ?'width:100%' : '' ">
                    <!-- Location --> 
                    <HbDropdown 
                        v-model="slocation.location" 
                        required= "true" 
                        :label="__('Location', 'hydra-booking')"  
                        :selected = "1"
                        :placeholder="__('Location', 'hydra-booking')" 
                        :option = "[
                            {name: 'Zoom', value: 'zoom', disable:  props.integrations.zoom_meeting_status, icon: 'Fullscreen'}, 
                            {name: 'Google Meet', value: 'meet', disable: props.integrations.google_calendar_status}, 
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
                <div class="tfhb-meeting-location-removed" v-if="meeting.meeting_locations.length>1" @click="emit('remove-meeting-location', index)">
                    <Icon name="Trash" :width="16" />
                </div>
            </div>
            <div class="tfhb-add-new-question">
             
                <button @click="emit('add-more-location')" class="tfhb-btn tfhb-inline-flex tfhb-gap-8 tfhb-justify-normal tfhb-height-auto">
                    <Icon name="PlusCircle" :width="20"/>
                    {{ __('Add Another Location', 'hydra-booking') }}
                </button> 
            </div>
        </div>

        <div v-if="meeting.meeting_type == 'one-to-group'" class="tfhb-admin-card-box tfhb-no-flexbox tfhb-m-0 tfhb-full-width">  
            <div class="tfhb-meeting-location tfhb-flexbox tfhb-gap-16" > 
                <HbText  
                        v-model="meeting.max_book_per_slot"  
                        type= "number"
                        :label="__('Max invitees in a spot', 'hydra-booking')"   
                        :placeholder="'Max invitees in a spot'" 
                        :width= "100"
                       
                    /> 

                    <HbSwitch 
                        v-model="meeting.is_display_max_book_slot" 
                        type="checkbox" 
                        required= "true" 
                        :label="__('Display remaining spots on booking page', 'hydra-booking')" 
                    />
            </div>  
        </div>


        <!-- Category --> 
        <HbDropdown 
            v-model="meeting.meeting_category" 
            required= "true" 
            :label="__('Select Category', 'hydra-booking')"  
            :selected = "meeting.meeting_category"
            :placeholder="__('Select Category', 'hydra-booking')" 
            :option = "props.meetingCategory.value" 
        />
        <div class="tfhb-add-moreinfo tfhb-full-width" >
            

            <button @click="createMeetingPopup = !createMeetingPopup" class="tfhb-btn tfhb-inline-flex tfhb-gap-8 tfhb-justify-normal tfhb-height-auto">
                <Icon name="PlusCircle" :width="20"/>
                {{ __('Create Category', 'hydra-booking') }}
            </button> 
        </div>
        <div class="tfhb-submission-btn">
            
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting', ['title',  'duration'])"
                :buttonText="__('Save & Continue', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
            />  
        </div>
        <!--Bookings -->

        <HbPopup :isOpen="createMeetingPopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ __('Create Meeting Category', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>   
                <HbText  
                    v-model="CategoryData.title"
                    required= "true"  
                    :label="__('Category Title', 'hydra-booking')"  
                    name="ctg-title"
                /> 
                <HbTextarea  
                    v-model="CategoryData.description"
                    required= "true"  
                    name="ctg-description"
                    :label="__('Description', 'hydra-booking')"  
                /> 
                <button class="tfhb-btn boxed-btn" @click="UpdateCategory">{{ __('Save Category', 'hydra-booking') }}</button> 
            </template> 
        </HbPopup>
    </div>
</template>