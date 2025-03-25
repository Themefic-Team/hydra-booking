import { ref, reactive } from 'vue'  
import axios from 'axios'  
import { toast } from "vue3-toastify"; 
const BookingDetails = reactive({
    booking : [],
    attendees : [],
    location : [],
    booking_activity : {},
    internal_note : '',
    showinternalNoteEdit : ref(false),
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
        this.skeleton = true;
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
                this.booking_activity = response.data.booking_activity; 
                this.internal_note =  response.data.internal_note == '' || response.data.internal_note == null ? '' : response.data.internal_note;
                this.location = JSON.parse(response.data.booking.meeting_locations);
                this.skeleton = false;
     
            }else{ 
                router.push({ name: 'BookingLists'}).then(() => {
                    nextTick(() => {
                         toast.success(response.data.message, {
                            position: 'bottom-right', // Set the desired position
                            "autoClose": 1500,
                        });
                        // add scrool to top with smooth 
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }); 
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

    // Update internal note
    async updateInternalNote() {
        let data = {
            booking_id: this.booking.id,
            internal_note: this.internal_note
        }
        try { 
            // axisos sent dataHeader Nonce Data
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/update-internal-note', data, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {    
                this.showinternalNoteEdit = false;
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

    async deleteBooking (router) { 
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
                
                router.push({ name: 'BookingLists'}).then(() => {
                    nextTick(() => {
                         toast.success(response.data.message, {
                            position: 'bottom-right', // Set the desired position
                            "autoClose": 1500,
                        });
                        // add scrool to top with smooth 
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }); 
            }
        } catch (error) {
            console.log(error);
        }
    },
    // Change Booking Status
    DownloadAsIcs() { 

         
        let ical = "BEGIN:VCALENDAR\r\n";
        ical += "VERSION:2.0\r\n";
        ical += "PRODID:-//Your Company//Meeting Scheduler//EN\r\n";
        ical += "CALSCALE:GREGORIAN\r\n";
        ical += "METHOD:PUBLISH\r\n";
        
        let uid = Date.now().toString();
        let dtStart = this.formatToUTC(this.booking.meeting_dates, this.booking.start_time, this.booking.availability_time_zone); 
        let dtEnd = this.formatToUTC(this.booking.meeting_dates, this.booking.end_time, this.booking.availability_time_zone);
        
        ical += "BEGIN:VEVENT\r\n";
        ical += "UID:" + uid + "\r\n";
        ical += "DTSTAMP:" + new Date().toISOString().replace(/[-:]/g, "").split('.')[0] + "Z\r\n";
        ical += "DTSTART:" + dtStart + "\r\n";
        ical += "DTEND:" + dtEnd + "\r\n";
        ical += "SUMMARY:" + this.booking.title + "\r\n";
        ical += "STATUS:" + this.booking.status.toUpperCase() + "\r\n";
        
        // Decode and add meeting locations
        let locations = JSON.parse(this.booking.meeting_locations || '{}');
        let locationString = Object.values(locations).map(loc => `${loc.location} - ${loc.address}`).join(', ');
        if (locationString) {
            ical += "LOCATION:" + locationString + "\r\n";
        }
        
        // Add attendees
        if (this.booking.attendees && this.booking.attendees.length > 0) {
            this.booking.attendees.forEach(attendee => {
                ical += `ATTENDEE;CN=${attendee.attendee_name}:mailto:${attendee.email}\r\n`;
            });
        }
        
        ical += "END:VEVENT\r\n";
        ical += "END:VCALENDAR\r\n";
        // make booking event file name as title and date time 
     
        this.downloadICS(ical, 'booking_event_'+this.booking.id+'.ics');
    }, 
    formatToUTC(date, time, timezone) {
        let dateTimeString = `${date} ${time}`;
        let dateTime = new Date(dateTimeString);
        return dateTime.toISOString().replace(/[-:]/g, "").split('.')[0] + "Z";
    },
    downloadICS(content, filename) {
        let blob = new Blob([content], { type: 'text/calendar' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
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