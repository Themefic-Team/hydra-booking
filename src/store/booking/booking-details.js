import { ref, reactive } from 'vue'  
import axios from 'axios'  
import { toast } from "vue3-toastify"; 
const BookingDetails = reactive({
    booking : [],
    attendees : [],
    location : [],
    attendeeCencelPopup : ref(false),
    attendeeCancelPreloader : ref(false),
    deletePopup : ref(false),
    deletePreloader : ref(false),
    cancelAttendee : {
        id: 0,
        booking_id: 0,
        attendee_name: '',
        status: 'canceled',
        cancel_reason: ''

    },
    skeleton : ref(true),

    // booking List
    async fetchBookingsDetails(bookingId, router) {

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
     
            }else{
               
                router.push({ name: 'BookingLists' });
            }
        } catch (error) {
            console.log(error);
        } 
    },
    // Change Booking Status
    async ChangeBookingStatus(  status) { 

        let data = {
            booking_id: this.booking.id,
            status: status
        }
        try { 
            // axisos sent dataHeader Nonce Data
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/change-booking-status', data, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {   
                this.booking = response.data.booking;
                this.attendees = response.data.booking.attendees; 
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
    }, 
     // cancel Booking Status
    async cancelBookingAttendee( ) { 
        
        if(this.cancelAttendee.cancel_reason == ''){

            toast.error( 'Please enter cancellation reason', {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            return false;
        }
        this.attendeeCancelPreloader = true; 
        try { 
            // axisos sent dataHeader Nonce Data
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/cancel-booking-attendee', this.cancelAttendee, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {   
                this.booking = response.data.booking;
                this.attendees = response.data.booking.attendees; 
    
                this.attendeeCencelPopup = false;
                this.attendeeCancelPreloader = false;
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
    },

    // Delete Booking 

    async deleteBooking () { 
        let deleteBooking = {
            id: this.booking.id,
            host: this.booking.host_id
        }
        try { 
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/delete', deleteBooking,{
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
    
            if (response.data.status) { 
                // return to booking list page after goign list page showing toast message
                this.deletePopup = false;
                this.deletePreloader = false;
                // toast.success(response.data.message, {
                //     position: 'bottom-right', // Set the desired position
                //     "autoClose": 1500,
                // });
                router.push({ name: 'BookingLists' });
            }
        } catch (error) {
            console.log(error);
        }
    }

    // async ChangeAttendeeStatus(attendee_id, booking_id, status) {

    //     let data = {
    //         attendee_id: attendee_id,
    //         status: status
    //     }
    //     try { 
    //         // axisos sent dataHeader Nonce Data
    //         const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/change-attendee-status', data, {
    //             headers: {
    //                 'X-WP-Nonce': tfhb_core_apps.rest_nonce,
    //                 'capability': 'tfhb_manage_options'
    //             } 
    //         } );
    
    //         if (response.data.status) {  
    //             //   update booking data using booking id and attendee id form paginatedBooking
    //             let index = paginatedBooking.value.findIndex(booking => booking.id == booking_id);
    //             let attendeeIndex = paginatedBooking.value[index].attendees.findIndex(attendee => attendee.id == attendee_id); 
    //             paginatedBooking.value[index].attendees[attendeeIndex].status = status;
    
    
    //             toast.success(response.data.message, {
    //                 position: 'bottom-right', // Set the desired position
    //                 "autoClose": 1500,
    //             });
    
    //         }else{
    
    //             toast.error(response.data.message, {
    //                 position: 'bottom-right', // Set the desired position
    //                 "autoClose": 1500,
    //             });
    
    //         }
    //     } catch (error) {
    //         console.log(error);
    //     } 
    // }
    
})

export { BookingDetails }