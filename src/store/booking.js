import { ref, reactive } from 'vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import axios from 'axios'  

const Booking = reactive({
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
            this.calendarbooking.events = response.data.booking_calendar;
            this.filter_skeleton = false;
            this.FilterPreview  = false;
            this.skeleton = false;
        }
    }
    
})

export { Booking }