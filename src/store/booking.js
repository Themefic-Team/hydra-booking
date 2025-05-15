import { ref, reactive } from 'vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { toast } from "vue3-toastify"; 
import axios from 'axios'  

const Booking = reactive({
    time_zone: {},
    time_slot: {},
    bookings: [], 
    FilterPreview: false,
    filter_data: {
        filter_type: 'upcoming',
        filter_search: '',
        host_ids: [],
        meeting_ids: [],
        status: [],
        date_range: {
            from: '',
            to: ''
        }

    },
    calendarbooking: {
        plugins: [ 
            dayGridPlugin,
            timeGridPlugin,
            interactionPlugin
        ],
        initialView: 'dayGridMonth',
        events: '',
        headerToolbar: {
            left: '',
            center: 'prev,title,next',
            right: 'timeGridDay,timeGridWeek,dayGridMonth'
        },
        dayMaxEvents: 3,
        allDaySlot: false,
    },
    reBookPopup: false,
    reBookPreLoader: false,
    disabled_days: [],
    disabled_dates: [], 
    reBook: { 
        booking_id: 0,
        meeting_id: 0,
        availability_time_zone: '',
        select_date: '',
        select_time_slot: {},
        select_status: '',
    },
    skeleton : ref(true),
    filter_skeleton : ref(false),

    // booking List
    async fetchBookings() {

        this.filter_skeleton = true;
        let data = { 
            filter_data: this.filter_data, 
        }
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/lists', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) { 
            this.bookings = response.data.bookings;
            this.time_zone = response.data.time_zone;
            this.calendarbooking.events = response.data.booking_calendar;
            this.filter_skeleton = false; 
            this.skeleton = false;
        }
    },

    async getAvailabilityDates() {   
        let data = {  
            meeting_id: this.reBook.meeting_id,  
            selected_time_zone: this.reBook.availability_time_zone, 
        }
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/get-availability-dates', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
            this.disabled_days = response.data.disabled_days;
            this.disabled_dates = response.data.disabled_dates; 
        }
    },
    async getAvailabilityTimeSlot(value) {   
        let data = { 
            select_date: value, 
            meeting_id: this.reBook.meeting_id, 
            selected_time_zone: this.reBook.availability_time_zone, 
        }
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/get-availability-time-slot', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
            this.time_slot = response.data.time_slot;
        }
    },
    // Reb
    async reBookMeeting() {   
        this.reBookPreLoader = true;
        let data = this.reBook
        if(this.reBook.availability_time_zone == '' || this.reBook.select_date == '' || this.reBook.select_time_slot == '' || this.reBook.select_status == ''  ){
            // error message 
            this.reBookPreLoader = false;
            toast.error('Please fill all the fields', {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            })
            return;
        }
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/re-book-meeting', data, {
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
            this.fetchBookings();
            this.reBookPreLoader = false;
            this.reBookPopup = false;
        }
    },
    
})

export { Booking }