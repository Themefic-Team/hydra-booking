<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, onMounted, computed } from 'vue';
import axios from 'axios'   
import { useRouter } from 'vue-router'
import Icon from '@/components/icon/LucideIcon.vue'
import HbSelect from '@/components/form-fields/HbSelect.vue';
import HbRadio from '@/components/form-fields/HbRadio.vue';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { toast } from "vue3-toastify"; 
import useDateFormat from '@/store/dateformat'
const { Tfhb_Date, Tfhb_Time } = useDateFormat();
import { Meeting } from '@/store/meetings'
import { Booking } from '@/store/booking'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

const router = useRouter()
const BookingDetailsPopup = ref(false);
const BookingEditPopup = ref(false);
const ExportAsCSV = ref(false);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const bookingView = ref('calendar');
const exportData = reactive({
    date_range: 'days',
    start_date: '',
    end_dates: ''
});
const select_all = ref(false);
const selected_items = ref([]);
const host_id = ref('');


// Export CSV
const ExportBookingAsCSV = async () => {
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/export-csv', exportData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_integrations'
            } 
        } );

        if (response.data.status) {  

            // Booking.bookings = response.data.booking; 
            // Booking.calendarbooking.events = response.data.booking_calendar;
            // BookingEditPopup.value = false;
            // export csv file data
            const url = window.URL.createObjectURL(new Blob([response.data.data]));
            const link = document.createElement('a');
            const file_name = response.data.file_name;

            link.href = url;

            link.setAttribute('download', file_name);

            // Append to the DOM
            document.body.appendChild(link);
            link.click();

            // Clean up
            link.remove();
            window.URL.revokeObjectURL(url);
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });   
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
        }
    } catch (error) {
        console.log(error);
    }   
}
const meeting_address = reactive({});
const TfhbFormatMeetingLocation = (address) => {
     meeting_address.value = JSON.parse(address) 
}

onBeforeMount(() => { 
    Booking.fetchBookings();
    Meeting.fetchMeetings();
});

// Booking Status Changed
const meeting_status = reactive({});
const UpdateMeetingStatus = async (id, host, status) => {    
    meeting_status.id = id
    meeting_status.host = host
    meeting_status.status = status
   try { 
        // axisos sent dataHeader Nonce Data
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/update', meeting_status, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            }  
        } );

        if (response.data.status) {  
            Booking.bookings = response.data.booking; 
            Booking.calendarbooking.events = response.data.booking_calendar;
            BookingEditPopup.value = false;

            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });   
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
        }
    } catch (error) {
        console.log(error);
    }   
}

const singleBookingData = ref('');
const Tfhb_Booking_View = async (data) => {   
    singleBookingData.value = data;
    BookingDetailsPopup.value = true;
    TfhbFormatMeetingLocation(data.meeting_locations);
}


const singleCalendarBookingData = reactive({
    title: '',
    booking_id: '',
    status: '',
    booking_date: '',
    booking_time: '',
    host_id: '',
});
const bookingCalendarPopup = (data) => {
    singleCalendarBookingData.title = data.title;
    singleCalendarBookingData.booking_id = data.extendedProps.booking_id;
    singleCalendarBookingData.status = data.extendedProps.status;
    singleCalendarBookingData.booking_date = data.extendedProps.booking_date;
    singleCalendarBookingData.booking_time = data.extendedProps.booking_time;
    singleCalendarBookingData.host_id = data.extendedProps.host_id;
    BookingEditPopup.value = true;
}

const deleteBooking = async ($id, $host) => { 
    let deleteBooking = {
        id: $id,
        host: $host
    }
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/delete', deleteBooking,{
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });

        if (response.data.status) { 
            Booking.bookings = response.data.bookings; 
            Booking.calendarbooking.events = response.data.booking_calendar;  
            BookingEditPopup.value = false; 
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        console.log(error);
    }
}
const Booking_Status_Callback = (value) => {
    UpdateMeetingStatus(singleCalendarBookingData.booking_id, singleCalendarBookingData.host_id, value);
}

const Bulk_Status_Callback = async (value) => { 


    let bookings = {
        items: selected_items.value,
        status: value
    } 
    try { 
        // axisos sent dataHeader Nonce Data
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/bulk-update', bookings, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
            
            Booking.bookings = response.data.booking; 
            Booking.calendarbooking.events = response.data.booking_calendar;
            BookingEditPopup.value = false;

            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });   
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
        }
    } catch (error) {
        console.log(error);
    } 
}


// Pagination
const totalPages = computed(() => {
  return Math.ceil(Booking.bookings.length / itemsPerPage.value);
});

const paginatedBooking = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return Booking.bookings.slice(start, end);
});

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value += 1;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value -= 1;
  }
};


// Select All
const toggleSelectAll = (e) => {
    if(e.target.checked){
        select_all.value = true;
    }else{
        select_all.value = false;
    } 

    if (select_all.value) {
        // If 'select_all' is true, select all items
        selected_items.value = paginatedBooking.value.map(item => item.id);
    } else {
        // If 'select_all' is false, deselect all items
        selected_items.value = [];
    }
}

const truncateString = (str, num) => {
    if (str.length <= num) {
        return str
    }
    return str.slice(0, num) + '...'
}

const deletePopup = ref(false);

const deleteItemConfirm = () => {
    Bulk_Status_Callback('delete'); 
    deletePopup.value = false;
    // empty selected_items
    selected_items.value = [];
}

</script>
<template>
<!-- {{ tfhbClass }} -->
<!-- :class="{ 'tfhb-skeleton': Booking.skeleton }" -->
<div class="tfhb-dashboard-heading tfhb-flexbox tfhb-justify-between">
    <div class="tfhb-filter-box tfhb-flexbox">
        <div class="tfhb-booking-view">
            <div class="tfhb-list-calendar">
                <ul class="tfhb-flexbox tfhb-gap-8">
                    <li :class="'list'==bookingView ? 'active' : ''" @click="bookingView='list'">
                        <Icon name="List" size=20 />
                    </li>
                    <li :class="'calendar'==bookingView ? 'active' : ''" @click="bookingView='calendar'">
                        <Icon name="CalendarDays" size=20 />
                    </li>
                </ul>
            </div>
        </div>
        <div class="tfhb-header-filters">
            <input type="text" placeholder="Host name or meeting title" /> 
            <span><Icon name="Search" size=20 /></span>
        </div>
    </div>
    <div class="thb-admin-btn right tfhb-flexbox tfhb-action-filter-button"> 
       
        <HbDropdown   
            v-if="selected_items.length > 0"
            placeholder="Status"   
            :option = "[
                {'name': 'Pending', 'value': 'pending'},  
                {'name': 'Confirmed', 'value': 'confirmed'},   
                {'name': 'Canceled', 'value': 'canceled'}
            ]"
            @tfhb-onchange="Bulk_Status_Callback" 
        />  
        
        <HbButton 
            v-if="selected_items.length > 0" 
            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
            @click="deletePopup = true"
            :buttonText="__('Delete', 'hydra-booking')"
            icon="Trash2"   
            :hover_animation="false" 
            icon_position = 'left'
        /> 
  
        <HbButton 
            classValue="tfhb-btn boxed-secondary-btn tfhb-flexbox tfhb-gap-8" 
            @click="ExportAsCSV"
            :buttonText="__('Export as CSV', 'hydra-booking')"
            icon="FileDown"   
            :hover_animation="false" 
            icon_position = 'left'
        /> 
        <HbButton 
            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
            @click="router.push({ name: 'BookingCreate' })"
            :buttonText="__('Add New Booking', 'hydra-booking')"
            icon="PlusCircle"   
            :hover_animation="false" 
            icon_position = 'left'
        /> 
    </div> 
</div>

<HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="400px" name="first-modal"> 
    <template #header> 
        <!-- {{ google_calendar }} -->
        <h2>{{ __('Confirmation', 'hydra-booking') }}</h2>
        
    </template>  
    <template #content>  
        <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
            <div class="tfhb-close-icon">
                <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
            </div>
            <div class="tfhb-close-content">
                <h3>{{ __('Are you absolutely sure?', 'hydra-booking') }}  </h3>  
                <p>{{ __('Data and bookings associated with this meeting will be deleted. It will not affect previously scheduled meetings.', 'hydra-booking') }}</p>
            </div>
            <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16">  
                <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click=" deletePopup = !deletePopup"
                    :buttonText="__('Cancel', 'hydra-booking')" 
                />  
                <HbButton  
                    classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                    @click="deleteItemConfirm"
                    :buttonText="__('Delete', 'hydra-booking')"
                    icon="Trash2"   
                    :hover_animation="false" 
                    icon_position = 'left'
                />
            </div>
        </div> 
    </template> 
</HbPopup>

<!-- Export CSV POPup -->
<HbPopup  :isOpen="ExportAsCSV" @modal-close="ExportAsCSV = false" max_width="500px" name="first-modal" gap="32px">
    <template #header>  
        <h3>Export Bookings as CSV</h3>
    </template>

    <template #content> 
        
        <HbRadio  
            required= "true"
            v-model="exportData.date_range"
            name="request_header"
            :label="__('Date Range', 'hydra-booking')"
            :groups="true" 
            :options="[
                {'label': 'Today', 'value': 'days'},  
                {'label': 'Last 7 Days', 'value': 'weeks'},
                {'label': 'Current Month', 'value': 'months'},
                {'label': 'Last Year', 'value': 'years'}, 
                {'label': 'All', 'value': 'all'}, 
                {'label': 'Custom', 'value': 'custom'} 
            ]" 
        />
      <div v-if="exportData.date_range == 'custom'" class="custom-date-range" >
        <label for="">{{ __('Select Date Range', 'hydra-booking') }}</label>
        <div class="tfhb-filter-dates tfhb-flexbox">
            
            <div class="tfhb-filter-start-end-date">
                <HbDateTime 
                    v-model="exportData.start_date"
                    :label="''" 
                    enableTime='true'
                    icon="CalendarDays"
                    placeholder="From"   
                />  
            </div>
            <div class="tfhb-calender-move-icon">
                <Icon name="MoveRight" size="20px" /> 
            </div>
            <div class="tfhb-filter-start-end-date">
                <HbDateTime 
                    v-model="exportData.end_date"
                    :label="''" 
                    icon="CalendarDays"
                    enableTime='true'
                    placeholder="To"   
                />  
            </div>
        </div> 
      </div>

      <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width"> 
        <button @click="ExportBookingAsCSV" class="tfhb-btn boxed-btn flex-btn"><Icon name="Download" size=20 /> {{ __('Export Meeting', 'hydra-booking') }}</button> 
      </div>
    </template> 
</HbPopup>
<!-- Export CSV POPup -->

<!-- Booking Quick View Start -->

<HbPopup :isOpen="BookingDetailsPopup" @modal-close="BookingDetailsPopup = false" max_width="750px" name="first-modal" gap="32px">
    <template #header> 
        <h3>{{ singleBookingData.title }}</h3>
    </template>

    <template #content>  
        <div class="tfhb-booking-info tfhb-full-width  tfhb-flexbox tfhb-gap-16">
            <!-- Host Info -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ __('Host', 'hydra-booking') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-16">
                    <div v-if="singleBookingData.host_first_name" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33.33% - 16px)">
                        <Icon name="User" size=20 /> 
                        {{ singleBookingData.host_first_name }}
                    </div>  
                    <div v-if="singleBookingData.host_email"  class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33.33%  - 16px)">
                        <Icon name="Mail" size=20 /> 
                        {{ singleBookingData.host_email }}
                    </div>  
                    <div v-if="singleBookingData.host_time_zone"  class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33.33%  - 16px)">
                        <Icon name="Globe" size=20 /> 
                        {{singleBookingData.host_time_zone}}
                    </div>  
                </div>
            </div>

            <!-- Attendee Info -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ __('Attendee', 'hydra-booking') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-16">
                    <div v-if="singleBookingData.attendee_name" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33.33% - 16px)">
                        <Icon name="User" size=20 /> 
                        {{ singleBookingData.attendee_name }}
                    </div>  
                    <div v-if="singleBookingData.attendee_email"  class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33.33%  - 16px)">
                        <Icon name="Mail" size=20 /> 
                        {{ singleBookingData.attendee_email }}
                    </div>  
                    <div v-if="singleBookingData.attendee_time_zone"  class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33.33%  - 16px)">
                        <Icon name="Globe" size=20 /> 
                        {{singleBookingData.attendee_time_zone}}
                    </div>  
                    <div v-if="singleBookingData.address" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(100% - 16px)">
                        <Icon name="MapPin" size=20 /> 
                        {{singleBookingData.address}}
                    </div>   
                </div>
            </div>

            <!-- Meeting -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ __('Meeting', 'hydra-booking') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-16">
                    <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33% - 16px)">
                        <Icon name="Clock" size=20 /> 
                        {{singleBookingData.start_time }} - {{singleBookingData.end_time }}
                    </div>  
                    <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(33% - 16px)">
                        <Icon name="CalendarDays" size=20 /> 
                        {{singleBookingData.meeting_dates }}
                    </div>   
                </div>
            </div>

            <!-- Location -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ __('Meeting Location', 'hydra-booking') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-16">  
                    <!-- [ { "location": "In Person (Organizer Address)", "address": "Address" }, { "location": "Organizer Phone Number", "address": "9098080" } ]  -->
                    <div v-for=" (address, index) in meeting_address.value" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(100% - 16px)">
                        <Icon name="MapPin" size=20 /> 
                        Location : {{address.location}} - {{address.address}}
                    </div>    
                </div>
            </div>
        </div>
 
  

    </template> 
</HbPopup>

<!-- Booking Quick View End -->

<!-- Booking Calendar View -->
<div    class="tfhb-booking-calendar tfhb-mt-72" v-if="bookingView=='calendar'"> 
    <FullCalendar class='demo-app-calendar ' :options='Booking.calendarbooking'>
        <template v-slot:eventContent='arg'>
            <!-- {{ arg.event.booking_date }}
            <b>{{ arg.timeText }}</b>
            {{ arg.event.extendedProps.booking_time }} -->
            <b class="tfhb-calendar-popup" :class="arg.event.extendedProps.status" @click="bookingCalendarPopup(arg.event)">{{ truncateString(arg.event.title, 50) }}  ( {{  arg.event.extendedProps.booking_time }} )</b>
        </template>
    </FullCalendar>
</div>
<!-- Booking Calendar View -->

<!-- Booking Calendar Edit -->

<HbPopup :isOpen="BookingEditPopup" @modal-close="BookingEditPopup = false" max_width="300px" name="first-modal" gap="24px" class="tfhb-booking-calendar-popup">
    <template #header> 
        <h3>{{ truncateString( singleCalendarBookingData.title , 50) }}</h3>
    </template>

    <template #content> 

        <HbDropdown  
            v-model="singleCalendarBookingData.status"
            :label="__('Status', 'hydra-booking')" 
            :selected = "1"
            placeholder="Select Booking status"   
            :option = "[
                {'name': 'Pending', 'value': 'pending'},  
                {'name': 'Confirmed', 'value': 'confirmed'},   
                {'name': 'Re-schedule', 'value': 'schedule'},   
                {'name': 'Canceled', 'value': 'canceled'}
            ]"
            @tfhb-onchange="Booking_Status_Callback" 
        />  

        <div class="tfhb-single-form-field" style="width: 100%;">
            <div class="tfhb-single-form-field-wrap tfhb-field-input">
                <label>Date</label>
                <div class="tfhb-time-date-view tfhb-flexbox">
                    <Icon name="CalendarDays" size=20 />
                    <input type="text" readonly :value="singleCalendarBookingData.booking_date">
                </div>
            </div>
        </div>

        <div class="tfhb-single-form-field" style="width: 100%;">
            <div class="tfhb-single-form-field-wrap tfhb-field-input">
                <label>Time</label>
                <div class="tfhb-time-date-view tfhb-flexbox">
                    <Icon name="Clock4" size=20 />
                    <input type="text" readonly :value="singleCalendarBookingData.booking_time">
                </div>
            </div>
        </div>

        <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width">
            <!-- <a href="#" class="tfhb-btn boxed-btn flex-btn"><Icon name="Video" size=20 /> {{ __('Join Meet', 'hydra-booking') }}</a> -->

            <HbButton  
                classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                @click="deleteBooking(singleCalendarBookingData.booking_id, singleCalendarBookingData.host_id)"
                :buttonText="__('Delete', 'hydra-booking')"
                icon="Trash2"   
                :hover_animation="false" 
                icon_position = 'left'
            /> 
 
        </div>
    </template> 
</HbPopup>

<!-- Booking Calendar Edit -->
<div :class="{ 'tfhb-skeleton': Booking.skeleton }"  class="tfhb-booking-details tfhb-mt-32" v-if="bookingView=='list' && paginatedBooking.length > 0 ">
  
    <table class="table" cellpadding="0" :cellspacing="0">
        <thead>
            <tr>
                <th> 
                    <div class="select-checkbox-lists">
                        <label>
                            <input type="checkbox" v-model="select_all" @change="toggleSelectAll">   
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </th>
                <th>{{ __('Date & Time', 'hydra-booking') }}</th>
                <th>{{ __('Title', 'hydra-booking') }}</th>
                <th>{{ __('Host', 'hydra-booking') }}</th>
                <th>{{ __('Attendee', 'hydra-booking') }}</th>
                <th>{{ __('Duration', 'hydra-booking') }}</th>
                <th>{{ __('Status', 'hydra-booking') }}</th>
                <th>{{ __('Action', 'hydra-booking') }}</th>
            </tr>
        </thead>

        <tbody v-if="paginatedBooking">
            <tr v-for="(book, key) in paginatedBooking" :key="key">
                <td>
                    <div class="checkbox-lists">
                        <label>
                            <input type="checkbox" v-model="selected_items" :value="book.id" :checked="select_all == true ? true : false">   
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </td>
                <td>
                    {{ Tfhb_Date(book.meeting_dates) }}
                    <span>{{ book.start_time }} - {{ book.end_time }}</span>
                </td>
                <td>
                    {{ truncateString(book.title, 50) }}
                </td>
                <td>
                    {{ book.host_first_name }} {{ book.host_last_name }}
                    <span>{{ book.host_email }}</span>
                </td>
                <td>
                    {{ book.attendee_name }}
                    <span>{{ book.attendee_email }}</span>
                </td>
                <td>
                    {{ book.duration }} {{ __('minutes', 'hydra-booking') }}
                </td>
                <td>
                    <div class="tfhb-details-status tfhb-flexbox tfhb-justify-normal tfhb-gap-0">
                        <div class="status" :class="book.booking_status">
                            {{ book.booking_status }}
                        </div>
                        <div class="tfhb-status-bar">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 13.334L5 8.33398H15L10 13.334Z" fill="#765664"/>
                            </svg>
                            <div class="tfhb-status-popup">
                                <ul class="tfhb-flexbox tfhb-gap-2">
                                    <li @click="UpdateMeetingStatus(book.id, book.host_id, 'confirmed')">{{ __('Confirmed', 'hydra-booking') }}</li>
                                    <li class="pending" @click="UpdateMeetingStatus(book.id, book.host_id, 'pending')">{{ __('Pending', 'hydra-booking') }}</li>
                                    <li class="schedule" @click="UpdateMeetingStatus(book.id, book.host_id, 'schedule')">{{ __('Re-schedule', 'hydra-booking') }}</li>
                                    <li class="canceled" @click="UpdateMeetingStatus(book.id, book.host_id, 'canceled')">{{ __('Canceled', 'hydra-booking') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="tfhb-details-action tfhb-flexbox tfhb-justify-normal tfhb-gap-16">
                        <span @click.stop="Tfhb_Booking_View(book)">
                            <Icon name="Eye" width="20" />
                        </span>
                        <router-link :to="{ name: 'bookingUpdate', params: { id: book.id } }" class="tfhb-dropdown-single">
                            <Icon name="FilePenLine" width="20" />
                        </router-link>
                    </div>
                </td>
            </tr>
            
        </tbody>
    </table>

    <div class="tfhb-booking-details-pagination tfhb-flexbox tfhb-mt-32" v-if="totalPages > 1">
        <div class="tfhb-prev-next-button">
            <a href="#" @click.prevent="prevPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="currentPage === 1"><Icon name="ArrowLeft" width="20" />Previous</a>
        </div>
        <div class="tfhb-pagination">
            <ul class="tfhb-flexbox tfhb-gap-0 tfhb-justify-normal">
                <li v-for="page in totalPages" :key="page" :class="{ active: page === currentPage }">
                    <a href="#" @click.prevent="changePage(page)" :class="{ 'active-link': page === currentPage }">{{ page }}</a>
                </li>
            </ul>
        </div>
        <div class="tfhb-prev-next-button">
            <a href="#" @click.prevent="nextPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="currentPage === totalPages">Next<Icon name="ArrowRight" width="20" /></a>
        </div>
    </div>
</div>
<div  v-else-if="bookingView=='list' && paginatedBooking.length == 0" class="tfhb-empty-notice-box-wrap tfhb-flexbox tfhb-gap-16 tfhb-full-width">  
    <img :src="$tfhb_url+'/assets/images/icon-calendar.svg'" alt="" >
    <p>{{ __('No Booking Created', 'hydra-booking') }}</p> 
</div>
</template>

<style scoped>

</style>