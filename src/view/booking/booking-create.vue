<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import HbText from '@/components/form-fields/HbText.vue'
import HbPreloader from '@/components/icon/HbPreloader.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 
import { useRouter } from 'vue-router' 
const router = useRouter();

// Fetch Pre booking Data
const booking = reactive({
    'name': '',
    'start_time': '',
    'end_time': '',
    'email': '',
    'time_zone': '',
    'meeting': '',
    'host': '',
    'location': '',
    'date': '',
    'time': '',
    'status': '',
})
const timeZone = reactive({});
const meetings = reactive({});
const meeting_locations = reactive({});
const meeting_hosts = reactive({});
const booking_time_data = reactive({
    value: {},
});
const flatpickr_date= reactive({
    dateFormat: 'Y-m-d',
    minDate : 'today',
    defaultDate: 'today',
    disable: [],
}); 
const fetchPreBookingData = async () => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/pre', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status) { 
            timeZone.value = response.data.time_zone; 
            meetings.value = response.data.meetings; 
        }
    } catch (error) {
        console.log(error);
    } 
}

const backToPrevLoader = ref(false);
// Back to Booking
const TfhbPrevNavigator = () => {

    backToPrevLoader.value = true;

    router.push({ name: 'BookingLists' });
    setTimeout(() => {

        backToPrevLoader.value = false;
    }, 8000);
}

// Meeting Change
const MeetingChangeCallback = async (e) => {
    let data = {
        meeting_id: e.value,
    };  
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/meeting', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );
      
        if (response.data.status) {    
            meeting_locations.value = response.data.locations; 
            meeting_hosts.value = response.data.hosts;
 
            let flatpickr_date_disable = response.data.flatpickr_date.disable;  
            let disable_days = response.data.flatpickr_date.disable_days;  
            //  disable specific day like sunday and saturday
            flatpickr_date.disable = [ 
                function(date) {
                    // return true to disable
                    return (
                        date.getDay() === disable_days.Sunday ||  
                        date.getDay() === disable_days.Saturday ||
                        date.getDay() === disable_days.Monday ||
                        date.getDay() === disable_days.Tuesday ||
                        date.getDay() === disable_days.Wednesday ||
                        date.getDay() === disable_days.Thursday ||
                        date.getDay() === disable_days.Friday

                    );
                },
            ];
            // merge with today disable date
            flatpickr_date.disable = flatpickr_date.disable.concat(flatpickr_date_disable);
            
            // also need to  flatpickr_date_disable include 
 

        }
    } catch (error) {
        
    }

}

// Check Available Times
const bookingSlot = async (date) => {
     
    let data = { 
        date: date,
        meeting_id: booking.meeting,
        selected_time_zone: booking.time_zone,
    };  

    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/availabletime', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );
      
        if (response.data.status) {    
            let time_slots_data = response.data.time_slots_data; 
            let time_slots = [];
            time_slots_data.forEach(element => {  
                time_slots.push({'name': element.start + ' - ' + element.end, 'value': element});
            });

            booking_time_data.value = time_slots; 
           
        }
    } catch (error) {
        
    }
}

// Add New Booking
const create_meeting_preLoader = ref(false);
const createBooking = async () => {
    create_meeting_preLoader.value = true;
    // Api Submission
    try { 

        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/create', booking, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        // Api Response
        if (response.data.status) {   
          
            create_meeting_preLoader.value = false;
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });  
            router.push({ name: 'BookingLists' });
        }else{
            create_meeting_preLoader.value = false;
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            
        }

    } catch (error) {
        console.log(error);
    } 
}

// Get End Time
const MeetingGetEndTime = (e) => {
    //  get selected value 
    let times = e.value;
    booking.start_time = times.start;
    booking.end_time = times.end;
}



onBeforeMount(() => { 
    fetchPreBookingData();
});
</script>

<template> 
    <div class="tfhb-booking-create">
        <div class="tfhb-booking-box tfhb-flexbox">
            <div class="tfhb-meeting-heading tfhb-flexbox tfhb-gap-8">
                <div class="prev-navigator tfhb-cursor-pointer" @click="TfhbPrevNavigator()">
                     
                    <Icon v-if="backToPrevLoader == false" name="ArrowLeft" size=20 /> 
                    <HbPreloader v-else color="#2E6B38" />
                </div>
                <h3>{{ $tfhb_trans('Back to Booking') }}</h3>
            </div>
            
            <HbText  
                v-model="booking.name"
                required= "true"  
                :label="$tfhb_trans('Customer name')"  
                name="name"
                selected = "1"
                :placeholder="$tfhb_trans('Jhon Deo')" 
            /> 
            <HbText  
                v-model="booking.email"
                required= "true"  
                :label="$tfhb_trans('Customer email')"  
                name="email"
                selected = "1"
                :placeholder="$tfhb_trans('name@yourmail.com')" 
            /> 

            <HbDropdown
                v-model="booking.time_zone"
                required= "true"  
                :label="$tfhb_trans('Client Time zone')" 
                :filter="true"
                selected = "1"
                :placeholder="$tfhb_trans('Select Time Zone')"  
                :option = "timeZone.value" 
            />  

            <HbDropdown
                v-model="booking.meeting"
                required= "true"  
                :label="$tfhb_trans('Select Meeting')" 
                :filter="true"
                selected = "1"
                :placeholder="$tfhb_trans('Select Your Meeting')"  
                :option = "meetings.value" 
                @tfhb-onchange="MeetingChangeCallback"
            />  

            <HbDropdown
                v-if="booking.meeting"
                v-model="booking.host"
                required= "true"  
                :label="$tfhb_trans('Select Team Member')" 
                :filter="true"
                selected = "1"
                :option = "meeting_hosts.value" 
            /> 

            <HbDropdown
                v-if="booking.meeting"
                v-model="booking.location"
                required= "true"  
                :label="$tfhb_trans('Select Location')" 
                :filter="true"
                selected = "1"
                :option = "meeting_locations.value" 
            /> 

            <HbDateTime   
                v-if="booking.meeting"
                v-model="booking.date"
                :label="$tfhb_trans('Select Date')" 
                selected = "1" 
                :config="flatpickr_date"
                :placeholder="$tfhb_trans('Enter schedule title')"   
                :change = true
                @dateChange="bookingSlot"
            />

            <HbDropdown  
                v-if="booking.date"
                v-model="booking.time"
                :label="$tfhb_trans('Select Time')" 
                required= "true" 
                :selected = "1"
                :placeholder="$tfhb_trans('Select Booking Time')"   
                :option = "booking_time_data.value" 
                @tfhb-onchange="MeetingGetEndTime"
            />   
            <HbDropdown  
                v-model="booking.status"
                :label="$tfhb_trans('Status')" 
                required= "true" 
                :selected = "1"
                :placeholder="$tfhb_trans('Select Booking status')"   
                :option = "[
                    {'name': 'Pending', 'value': 'pending'},  
                    {'name': 'Confirmed', 'value': 'confirmed'},   
                    {'name': 'Canceled', 'value': 'canceled'}
                ]" 
            />  

            <div class="tfhb-submission-btn">
                <HbButton 
                    classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                    @click="createBooking"
                    :buttonText="$tfhb_trans('Create Booking')"
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true"
                    :pre_loader="create_meeting_preLoader"
                />   
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>