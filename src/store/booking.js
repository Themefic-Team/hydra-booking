import { ref, reactive } from 'vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import axios from 'axios'  

const Booking = reactive({
    bookings: [],
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
        dayMaxEventRows: true,
    },
    skeleton : ref(true),

    // booking List
    async fetchBookings() {

        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/lists', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) { 
            this.bookings = response.data.bookings;
            this.calendarbooking.events = response.data.booking_calendar;
            this.skeleton = false;
        }
    }
    
})

export { Booking }