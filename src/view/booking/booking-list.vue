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
import HbMultiSelect from '@/components/form-fields/HbMultiSelect.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { toast } from "vue3-toastify"; 
import useDateFormat from '@/store/dateformat'
const { Tfhb_Date, Tfhb_Time } = useDateFormat();
import { Meeting } from '@/store/meetings'
import { Booking } from '@/store/booking'
import { Host } from '@/store/hosts'

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
const bookingView = ref('list');
const exportData = reactive({
    date_range: 'days',
    start_date: '',
    end_dates: ''
}); 
const selected_items = ref([]); 

const exportAsPreloader = ref(false);
// Export CSV
const ExportBookingAsCSV = async () => {
    exportAsPreloader.value = true;
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
            exportAsPreloader.value = false;
            ExportAsCSV.value = false;
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            exportAsPreloader.value = false;
            
        }
    } catch (error) {
        console.log(error);
    }   
}
const meeting_address = reactive({});
const TfhbFormatMeetingLocation = (address) => {
     meeting_address.value = JSON.parse(address) 
}



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
            Booking.fetchBookings();

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
const Tfhb_Booking_View = async (id) => {  
    //  go to booking details page
    router.push({ name: 'bookingDetails', params: { id: id } });

    // singleBookingData.value = data;
    // BookingDetailsPopup.value = true;
    // TfhbFormatMeetingLocation(data.meeting_locations);
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
 
const Tfhb_Booking_Filter = async (e) =>{
    Booking.filter_data.filter_search =e.target.value;
    Booking.filter_data.filter_type ='search';
    Booking.fetchBookings();
}

// Pagination
const totalPages = computed(() => {
  return Math.ceil(Booking.bookings.length / itemsPerPage.value);
});

const paginatedBooking = computed(() => {
  
    const bookingsArray = Booking.bookings;  
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return bookingsArray.slice(start, end);
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

const MakeMeetingLink = (link) => {
   
    // link like https://meet.google.com/jpu-oxfr-uuc | https://zoom.us/j/1234567890 
    // mamake <a href="https://meet.google.com/jpu-oxfr-uuc">https://meet.google.com/jpu-oxfr-uuc</a> | <a href="https://zoom.us/j/1234567890">https://zoom.us/j/1234567890</a>
    if(link == ''){
        return link;
    } 
    if (typeof link !== 'string') { 
        return link;
    }
    // make array based on | 
    let links = link.split('|');
   
    let linkHtml = '';
    links.forEach((link) => {
        
        let linkParts = link.split(','); 
        linkHtml += '<a href="'+linkParts+'" target="_blank">'+linkParts+'</a> <br>';
    });
    return linkHtml;




}
const bookingReminder  = async (booking) => { 

    let data = {
        booking_id: booking.id
    } 
    try { 
        // axisos sent dataHeader Nonce Data
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/send-reminder', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
              
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
const SendAttendeeReminder  = async (attendee) => { 

    let data = {
        attendee_id: attendee.id
    } 
    try { 
        // axisos sent dataHeader Nonce Data
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/send-attendee-reminder', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
              
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
const ChangeAttendeeStatus  = async (attendee_id, booking_id, status) => { 
    
    let data = {
        attendee_id: attendee_id,
        status: status
    }
    try { 
        // axisos sent dataHeader Nonce Data
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/change-attendee-status', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
            //   update booking data using booking id and attendee id form paginatedBooking
            let index = paginatedBooking.value.findIndex(booking => booking.id == booking_id);
            let attendeeIndex = paginatedBooking.value[index].attendees.findIndex(attendee => attendee.id == attendee_id); 
            paginatedBooking.value[index].attendees[attendeeIndex].status = status;


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

// const


const activeAttendeeAction = ref(0);
// on click add class active
const activeSingleAttendeeAction = (id) => {
    if(activeAttendeeAction.value == id) {
        activeAttendeeAction.value = 0;
        return;
    }
    activeAttendeeAction.value = id; 

}
// 
const displayTotalAttendeesWithCount = (attendees) => {
    let totalAttendees = attendees.length;
    let displayAttendees = ''; 

    if (totalAttendees > 0) {
        // Limit to first two attendees
        displayAttendees = attendees
            .slice(0, 2) // Only take the first two attendees
            .map(attendee => attendee.attendee_name) // Map to names
            .join(', '); // Join with a comma and space

        // Add "+X More" if there are more than 2 attendees
        if (totalAttendees > 2) {
            displayAttendees += ` <b>+${totalAttendees - 2} More</b>`;
        }
    }

    return displayAttendees;
};

// Filter  
const goForReschedule = (attendee) => {
  
    let url = tfhb_core_apps.admin_url+'/?hydra-booking=booking&hash='+attendee.hash+'&meetingId='+attendee.meeting_id+'&type=reschedule';
    window.open(url, '_blank');
}

const getMeetingList = (meeting) => { 

    let meetingList = [];

    meeting.forEach((item) => {
        meetingList.push({
            'name': item.title,
            'value': item.id
        });
    });

    return meetingList;
}

// Filter Booking By Type
const filterBookingByType = (type) => {
    Booking.filter_data.filter_type = type;

    Booking.fetchBookings();
}
// reset filter
const resetFilter = () => {
    Booking.filter_data.date_range = { from: '', to: '' };
    Booking.filter_data.host_ids = [];
    Booking.filter_data.meeting_ids = [];
    Booking.filter_data.status = [];
    Booking.fetchBookings();
}

// Hide Booking Details Popup

function hideDropdownOutsideClick(e) { 
    const filterContentWrap = document.querySelector('.tfhb-filter-content-wrap');
    const multiSelectPanel = document.querySelector('.p-multiselect-panel'); // Dynamically check for p-multiselect-panel

    if (!filterContentWrap.contains(e.target) &&
        (!multiSelectPanel || !multiSelectPanel.contains(e.target)) ) { 
        Booking.FilterPreview = false;
        
    }
    
}


onBeforeMount(() => { 
    Booking.fetchBookings('upcoming');
    Meeting.fetchMeetings();
    Host.fetchHosts();
    window.addEventListener('click', hideDropdownOutsideClick);
});
const ToDateMin = ref(null);
const getMinDate = (value) => {      
    ToDateMin.value = new Date(value);  
    Booking.filter_data.date_range.to ='';
    
} 
const changeToDate = (value) => {  
    if( Booking.filter_data.date_range.from == ''){
 
        toast.error('Please select from date first', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
    } 
    if( Booking.filter_data.date_range.from > value){
        toast.error('To date should be greater than from date', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
    }

    if(value){
        // after value updateinto te v model 
        Booking.filter_data.date_range.to = value;
        Booking.fetchBookings();
    }
}
</script>
<template>

<div class="tfhb-booking-heading tfhb-flexbox tfhb-justify-between tfhb-gap-24">
    
    <!-- Dashboard Heading Wrap -->
    <div class="tfhb-dashboard-heading-wrap tfhb-flexbox tfhb-justify-between">
        <div class="tfhb-filter-box tfhb-flexbox"> 
            <div class="tfhb-header-filters">
                <input type="text"  placeholder="Host name or meeting title" @keyup="Tfhb_Booking_Filter" /> 
                <span><Icon name="Search" size=20 /></span>
            </div>
           
        </div> 
        <HbButton 
            classValue="tfhb-btn  boxed-btn tfhb-flexbox tfhb-gap-8" 
            @click="ExportAsCSV = true"
            :buttonText="$tfhb_trans('Export as CSV')"
            icon="FileDown"   
            :hover_animation="false" 
            icon_position = 'left'
        />
    </div> 
    <!-- Dashboard Heading Wrap -->
    <div class="tfhb-dashboard-heading-wrap tfhb-flexbox tfhb-justify-between">   
        <div class="tfhb-filter-box tfhb-flexbox tfhb-gap-8">  
            
            <div class="tfhb-filter-content-wrap " :class="Booking.FilterPreview ? 'active' : ''"> 
                <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8"  
                    :class="Booking.filter_data.filter_type == 'filter' ? 'active' : ''"
                    @click="Booking.FilterPreview =!Booking.FilterPreview , Booking.filter_data.filter_type='filter'"
                    :buttonText="$tfhb_trans('Filter')"
                    icon="Filter"   
                    width="100"
                    :hover_animation="false" 
                    icon_position = 'left'
                /> 
                <transition name="tfhb-dropdown-transition">
                    <div class="tfhb-filter-box-content" v-show="Booking.FilterPreview "> 
                        <div class="tfhb-filter-form tfhb-justify-center">
                            
                            <!-- <HbButton 
                                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                                @click="Booking.fetchBookings()"
                                :buttonText="$tfhb_trans('Apply Filter')"
                                icon="Search"   
                                width="100"
                                :hover_animation="false" 
                                icon_position = 'left'
                            />  -->
                            <div class="tfhb-filter-dates tfhb-flexbox tfhb-gap-8">
                                <HbDateTime 
                                    v-model="Booking.filter_data.date_range.from"
                                    selected = "1"  
                                    @dateChange =  "getMinDate" 
                                    width="48.5"
                                    enableTime='true'
                                    :placeholder="$tfhb_trans('From')"  
                                    icon="CalendarDays"   
                                />
                                 
                                <!-- {{new Date()}} -->
                                <div class="tfhb-calender-move-icon" >
                                    <Icon name="MoveRight" size=15 /> 
                                </div>
                                <HbDateTime 
                                    v-model="Booking.filter_data.date_range.to"
                                    selected = "1" 
                                    width="48.5"
                                    @dateChange =  "changeToDate" 
                                    :config="{ minDate: ToDateMin }"
                                    enableTime='true'
                                    :placeholder="$tfhb_trans('To')"   
                                    icon="CalendarDays"   
                                />
                            </div> 

                            
                            <HbMultiSelect  
                                v-model="Booking.filter_data.host_ids" 
                                :selected = "1"
                                @add-change="Booking.fetchBookings()"
                                :placeholder="$tfhb_trans('Select Host : All')"   
                                :option = "Host.hosts" 
                            /> 
                            <HbMultiSelect  
                                v-model="Booking.filter_data.meeting_ids"  
                                :selected = "1"
                                @add-change="Booking.fetchBookings()"
                                :placeholder="$tfhb_trans('Select Meeting : All')"   
                                :option = "getMeetingList(Meeting.meetings)" 
                            /> 
                            <HbMultiSelect  
                                v-model="Booking.filter_data.status" 
                                :selected = "1"
                                @add-change="Booking.fetchBookings()"
                                :placeholder="$tfhb_trans('Select Status : All')"   
                                :option = "[
                                    {'name': 'Pending', 'value': 'pending'},  
                                    {'name': 'Confirmed', 'value': 'confirmed'},    
                                    {'name': 'Canceled', 'value': 'canceled'}
                                ]" 
                            /> 

                            <div class="tfhb-flexbox tfhb-justify-center">
                                <HbButton 
                                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                                    @click="resetFilter"
                                    :buttonText="$tfhb_trans('Reset Filter')"
                                    icon="RefreshCw"
                                    width="100"
                                    :hover_animation="false" 
                                    icon_position = 'left'
                                />
                            </div>   
                            
                        </div> 
                    </div>
                </transition>
            </div> 
            <HbButton 
                classValue="tfhb-btn secondary-btn " 
                :class="Booking.filter_data.filter_type == 'upcoming' ? 'active' : ''"
                @click="filterBookingByType('upcoming')"
                :buttonText="$tfhb_trans('Upcoming')" 
            />  
            <HbButton 
                classValue="tfhb-btn secondary-btn " 
                :class="Booking.filter_data.filter_type == 'completed' ? 'active' : ''"
                @click="filterBookingByType('completed')"
                :buttonText="$tfhb_trans('Completed')" 
            />  
            <HbButton 
                classValue="tfhb-btn secondary-btn " 
                :class="Booking.filter_data.filter_type == 'latest' ? 'active' : ''"
                @click="filterBookingByType('latest')"
                :buttonText="$tfhb_trans('Latest')" 
            /> 
            <HbButton 
                classValue="tfhb-btn secondary-btn " 
                :class="Booking.filter_data.filter_type == 'all' ? 'active' : ''"
                @click="filterBookingByType('all')"
                :buttonText="$tfhb_trans('All')" 
            /> 
        </div>
        <div class="thb-admin-btn right tfhb-flexbox tfhb-action-filter-button"> 
        
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
        </div> 
    </div> 

</div>

<HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="400px" name="first-modal"> 
    <template #header> 
        
    </template>  
    <template #content>  
        <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
            <div class="tfhb-close-icon">
                <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
            </div>
            <div class="tfhb-close-content">
                <h3>{{ $tfhb_trans('Are you absolutely sure?') }}  </h3>  
                <p>{{ $tfhb_trans('Data and bookings associated with this meeting will be deleted. It will not affect previously scheduled meetings.') }}</p>
            </div>
            <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16">  
                <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click=" deletePopup = !deletePopup"
                    :buttonText="$tfhb_trans('Cancel')" 
                />  
                <HbButton  
                    classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                    @click="deleteItemConfirm"
                    :buttonText="$tfhb_trans('Delete')"
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
        <h3>{{$tfhb_trans('Export Bookings as CSV')}}</h3>
    </template>

    <template #content> 
        
        <HbRadio  
            required= "true"
            v-model="exportData.date_range"
            name="request_header"
            :label="$tfhb_trans('Date Range')"
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
        <label for="">{{ $tfhb_trans('Select Date Range') }}</label>
        <div class="tfhb-filter-dates tfhb-flexbox">
            
            <div class="tfhb-filter-start-end-date">
                <HbDateTime 
                    v-model="exportData.start_date"
                    :label="''" 
                    enableTime='true'
                    icon="CalendarDays"
                    :placeholder="$tfhb_trans('From')"   
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
                    :placeholder="$tfhb_trans('To')"  
                />  
            </div>
        </div> 
      </div>

      <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width"> 
        <HbButton 
            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
            @click="ExportBookingAsCSV"
            :buttonText="$tfhb_trans('Export Booking')"
            icon="ChevronRight"   
            hover_icon="ArrowRight"
            :pre_loader="exportAsPreloader"
            :hover_animation="true" 
            icon_position = 'right'
        /> 
      
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
                <h3>{{ $tfhb_trans('Host') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-12">
                    <div v-if="singleBookingData.host_first_name" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                        <Icon name="User" size=20 /> 
                        {{ singleBookingData.host_first_name }}
                    </div>  
                    <div v-if="singleBookingData.host_email"  class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                        <Icon name="Mail" size=20 /> 
                        {{ singleBookingData.host_email }}
                    </div>  
                    <div v-if="singleBookingData.host_time_zone"  class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                        <Icon name="Globe" size=20 /> 
                        {{singleBookingData.host_time_zone}}
                    </div>  
                </div>
            </div>

            <!-- Attendee Info -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ $tfhb_trans('Attendee') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-attendee-details tfhb-flexbox tfhb-gap-12">
                    <div   class="tfhb-booking-details tfhb-full-width" >
   
                        <table class="table" cellpadding="0" :cellspacing="0">
                            <thead>
                                <tr> 
                                    <th>{{ $tfhb_trans('Name & Email') }}</th>
                                    <th>{{ $tfhb_trans('Time Zone') }}</th> 
                                    <th>{{ $tfhb_trans('Status') }}</th> 
                                    <th>{{ $tfhb_trans('Action') }}</th>
                                </tr>
                            </thead>

                            <tbody v-if="singleBookingData.attendees">
                                <tr v-for="(attendee, key) in singleBookingData.attendees" :key="key"> 
                                   <td> 
                                        {{ attendee.attendee_name }} 
                                        <span>{{attendee.email}}</span>
                                    </td>
                                   
                                   <td>
                                        {{attendee.attendee_time_zone }}
                                    </td> 
                                    <td>
                                        {{attendee.status }}
                                    </td> 
                                    <td> 
                                        <div class="tfhb-details-action tfhb-flexbox tfhb-justify-normal tfhb-gap-16">
                                            <span @click.stop="SendAttendeeReminder(attendee)">
                                                <Icon name="AlarmClock" width="20" />
                                            </span>
                                            <span @click.stop="goForReschedule(attendee)">
                                                <Icon name="RefreshCcw" width="20" />
                                            </span>
                                            <div  @click="activeSingleAttendeeAction(attendee.id)" class="tfhb-single-hosts-action tfhb-dropdown">
                                                <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt="">
                                                <transition name="tfhb-dropdown-transition">
                                                    <div v-show="attendee.id == activeAttendeeAction" class="tfhb-dropdown-wrap ">  
                                                        
                                                        <span class="tfhb-dropdown-single" @click="ChangeAttendeeStatus(attendee.id, singleBookingData.id, 'confirmed')"><Icon name="CalendarCheck2" size=16   /> {{ $tfhb_trans('Confirm') }} </span>
                                                        <span class="tfhb-dropdown-single " @click="ChangeAttendeeStatus(attendee.id, singleBookingData.id, 'pending')" ><Icon name="CalendarClock" size=16  /> {{ $tfhb_trans('Pending') }} </span>
                                                        <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="ChangeAttendeeStatus(attendee.id, singleBookingData.id, 'canceled')" ><Icon name="X"  size=16 /> {{ $tfhb_trans('Cancel') }} </span>
                                                      
                                                    </div>
                                                </transition>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
 
                    </div>   
                </div>
            </div>

            <!-- Meeting -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ $tfhb_trans('Meeting') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-12">
                    <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                        <Icon name="Clock" size=20 /> 
                        {{singleBookingData.start_time }} - {{singleBookingData.end_time }}
                    </div>  
                    <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                        <Icon name="CalendarDays" size=20 /> 
                        {{singleBookingData.meeting_dates }}
                    </div>   
                </div>
            </div>

            <!-- Location -->
            <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width ">
                <h3>{{ $tfhb_trans('Meeting Location') }}  </h3>
                <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-16">  
                    <!-- [ { "location": "In Person (Organizer Address)", "address": "Address" }, { "location": "Organizer Phone Number", "address": "9098080" } ]  -->
                    <div v-for=" (address, index) in meeting_address.value" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(100% - 16px)">
                       <span class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal" v-if="address.location == 'zoom'" >
                        <Icon name="MapPin" size=20 /> 
                        Location : {{address.location}} - <div v-html="MakeMeetingLink(address.address.link)"></div>  
                       </span>
                       <span class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal" v-else-if="address.location == 'meet'" >
                        <Icon name="MapPin" size=20 /> 
                        Location : {{address.location}} - <div v-html="MakeMeetingLink(address.address)"></div> 
                       </span>
                       <span class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal" v-else  >
                        <Icon name="MapPin" size=20 /> 
                        Location : {{address.location}} - {{address.address}}
                       </span>
                    </div>    
                </div>
            </div>
        </div>
 
  

    </template> 
</HbPopup>

<!-- Booking Quick View End -->

<!-- Booking Calendar View -->
<div :class="{   'tfhb-skeleton': Booking.skeleton, 'tfhb-skeleton': Booking.filter_skeleton } " class="tfhb-booking-calendar tfhb-mt-24" v-if="bookingView=='calendar'"> 
     
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
            :label="$tfhb_trans('Status')" 
            :selected = "1"
            :placeholder="$tfhb_trans('Select Booking status')"   
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
                <label>{{$tfhb_trans('Date')}}</label>
                <div class="tfhb-time-date-view tfhb-flexbox">
                    <Icon name="CalendarDays" size=20 />
                    <input type="text" readonly :value="singleCalendarBookingData.booking_date">
                </div>
            </div>
        </div>

        <div class="tfhb-single-form-field" style="width: 100%;">
            <div class="tfhb-single-form-field-wrap tfhb-field-input">
                <label>{{$tfhb_trans('Time')}}</label>
                <div class="tfhb-time-date-view tfhb-flexbox">
                    <Icon name="Clock4" size=20 />
                    <input type="text" readonly :value="singleCalendarBookingData.booking_time">
                </div>
            </div>
        </div>

        <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width">
            <!-- <a href="#" class="tfhb-btn boxed-btn flex-btn"><Icon name="Video" size=20 /> {{ $tfhb_trans('Join Meet') }}</a> -->

            <HbButton  
                classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                @click="deleteBooking(singleCalendarBookingData.booking_id, singleCalendarBookingData.host_id)"
                :buttonText="$tfhb_trans('Delete')"
                icon="Trash2"   
                :hover_animation="false" 
                icon_position = 'left'
            /> 
 
        </div>
    </template> 
</HbPopup>

<!-- Booking Calendar Edit -->
<div :class=" {   'tfhb-skeleton': Booking.skeleton, 'tfhb-skeleton': Booking.filter_skeleton } "  class="tfhb-booking-details tfhb-mt-32" v-if="bookingView=='list' && paginatedBooking.length > 0 ">
  
    <table class="table" cellpadding="0" :cellspacing="0">
        <thead>
            <tr> 
                <th>{{ $tfhb_trans('Date & Time') }}</th>
                <th>{{ $tfhb_trans('Events') }}</th>
                <th>{{ $tfhb_trans('Attendees') }}</th>
                <!-- <th>{{ $tfhb_trans('Attendee') }}</th> -->
                <th>{{ $tfhb_trans('Type') }}</th>
                <th>{{ $tfhb_trans('Status') }}</th>
                <th>{{ $tfhb_trans('Action') }}</th>
            </tr>
        </thead>

        <tbody v-if="paginatedBooking">
            <template v-for="(bookData, key) in paginatedBooking" :key="key">
                <tr  class="tfhb-list-date-tr">
                    <td colspan="6" >
                        <div class="tfhb-date-title" v-if="bookData.date == 'Tomorrow' ||  bookData.date == 'Today'">{{ bookData.date }}</div>
                        <div class="tfhb-date-title" v-else>{{ Tfhb_Date(bookData.date) }}</div>
                    </td>
                </tr>
                <tr v-for="(book, key) in bookData.bookings" :key="key" :class="{ last: key === bookData.bookings.length - 1 }">
                     
                    <td> 
                        <span>{{ book.start_time }} - {{ book.end_time }}</span>
                    </td>
                    <td>
                        <span class="tfhb-list-data-event-title">{{ book.title }}</span>
                    </td>
                    <td style="width: 25%;">  
                        <span class="tfhb-list-data-event-title" v-html="displayTotalAttendeesWithCount(book.attendees)"></span>
                    </td>
                
                    <td>
                        
                        <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-one'==book.meeting_type">
                            <div class="user-info-icon">
                                <Icon name="UserRound" size=16 /> 
                                <Icon name="ArrowRight" size=16 /> 
                                <Icon name="UserRound" size=16 /> 
                            </div>
                            <div class="user-info-title">
                                {{ $tfhb_trans('One to One') }}
                            </div>
                        </div>
                        <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-group'==book.meeting_type">
                            <div class="user-info-icon">
                                <Icon name="UserRound" size=16 /> 
                                <Icon name="ArrowRight" size=16 /> 
                                <Icon name="UsersRound" size=16 /> 
                            </div>
                            <div class="user-info-title">
                                {{ $tfhb_trans('One to Group') }}
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="tfhb-details-status tfhb-flexbox tfhb-justify-normal tfhb-gap-0">
                            <div class="status" :class="book.status">
                                {{ book.status }}
                            </div>
                            <div class="tfhb-status-bar">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 13.334L5 8.33398H15L10 13.334Z" fill="#765664"/>
                                </svg>
                                <div class="tfhb-status-popup">
                                    <ul class="tfhb-flexbox tfhb-gap-2">
                                        <li @click="UpdateMeetingStatus(book.id, book.host_id, 'confirmed')">{{ $tfhb_trans('Confirmed') }}</li>
                                        <li class="pending" @click="UpdateMeetingStatus(book.id, book.host_id, 'pending')">{{ $tfhb_trans('Pending') }}</li> 
                                        <li class="canceled" @click="UpdateMeetingStatus(book.id, book.host_id, 'canceled')">{{ $tfhb_trans('Canceled') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="tfhb-details-action tfhb-flexbox tfhb-justify-normal tfhb-gap-16">
                            <span @click.stop="Tfhb_Booking_View(book.id)">
                                <Icon name="Eye" width="20" />
                            </span>
                            <span @click.stop="bookingReminder(book)">
                                <Icon name="AlarmClock" width="20" />
                            </span>
                            <!-- <router-link :to="{ name: 'bookingUpdate', params: { id: book.id } }" class="tfhb-dropdown-single">
                                <Icon name="FilePenLine" width="20" />
                            </router-link>  -->
                        </div>
                    </td>
                </tr>

            </template>  
            
        </tbody>
    </table>

    <div class="tfhb-booking-details-pagination tfhb-flexbox tfhb-mt-32" v-if="totalPages > 1">
        <div class="tfhb-prev-next-button">
            <a href="#" @click.prevent="prevPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="currentPage === 1"><Icon name="ArrowLeft" width="20" />{{ $tfhb_trans('Previous') }}</a>
        </div>
        <div class="tfhb-pagination">
            <ul class="tfhb-flexbox tfhb-gap-0 tfhb-justify-normal">
                <li v-for="page in totalPages" :key="page" :class="{ active: page === currentPage }">
                    <a href="#" @click.prevent="changePage(page)" :class="{ 'active-link': page === currentPage }">{{ page }}</a>
                </li>
            </ul>
        </div>
        <div class="tfhb-prev-next-button">
            <a href="#" @click.prevent="nextPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="currentPage === totalPages">{{ $tfhb_trans('Next') }}<Icon name="ArrowRight" width="20" /></a>
        </div>
    </div>
</div>
<div  v-else-if="bookingView=='list' && paginatedBooking.length == 0" class="tfhb-empty-notice-box-wrap tfhb-flexbox tfhb-gap-16 tfhb-full-width">  
    <img :src="$tfhb_url+'/assets/images/icon-calendar.svg'" alt="" >
    <p>{{ $tfhb_trans('No Booking Found') }}</p>
</div>
</template>

<style scoped>

</style>