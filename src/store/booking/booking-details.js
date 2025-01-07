import { ref, reactive } from 'vue'  
import axios from 'axios'  

const BookingDetails = reactive({
    booking : [],
    attendees : [],
    location : [],
    skeleton : ref(true),

    // booking List
    async fetchBookingsDetails(bookingId) {

        try { 
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/details/'+bookingId, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            if (response.data.status == true) { 
                // If any available this date 
                this.booking = response.data.booking;
                this.attendees = response.data.booking.attendees;
                this.location = JSON.parse(response.data.booking.meeting_locations);
     
            }
        } catch (error) {
            console.log(error);
        } 
    }
    
})

export { BookingDetails }