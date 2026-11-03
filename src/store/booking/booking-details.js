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

            toast.error( (tfhb_core_apps.trans['Please enter cancellation reason'] || 'Please enter cancellation reason'), {
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
        
        // Decode meeting locations
        let locations = JSON.parse(this.booking.meeting_locations || '{}');
        let locationString = Object.values(locations).map(loc => `${loc.location} - ${loc.address}`).join(', ');

        // One event per occurrence; stored times are in the booking's availability timezone
        let meetingDates = String(this.booking.meeting_dates || '').split(',').map(date => date.trim()).filter(Boolean);
        meetingDates.forEach((meetingDate, index) => {
            let start = this.wallClockToDate(meetingDate, this.booking.start_time, this.booking.availability_time_zone);
            let end = this.wallClockToDate(meetingDate, this.booking.end_time, this.booking.availability_time_zone);
            if (!start || !end) {
                return;
            }
            // A meeting that ends at or after midnight ends on the next day
            if (end < start) {
                end = new Date(end.getTime() + 86400000);
            }

            ical += "BEGIN:VEVENT\r\n";
            ical += "UID:" + Date.now().toString() + '-' + index + "\r\n";
            ical += "DTSTAMP:" + this.toIcsUtc(new Date()) + "\r\n";
            ical += "DTSTART:" + this.toIcsUtc(start) + "\r\n";
            ical += "DTEND:" + this.toIcsUtc(end) + "\r\n";
            ical += "SUMMARY:" + this.booking.title + "\r\n";
            ical += "STATUS:" + this.booking.status.toUpperCase() + "\r\n";

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
        });
        ical += "END:VCALENDAR\r\n";
        // make booking event file name as title and date time 
     
        this.downloadICS(ical, 'booking_event_'+this.booking.id+'.ics');
    }, 
    formatToUTC(date, time, timezone) {
        let dateTime = this.wallClockToDate(date, time, timezone);
        return dateTime ? this.toIcsUtc(dateTime) : '';
    },
    toIcsUtc(dateTime) {
        return dateTime.toISOString().replace(/[-:]/g, "").split('.')[0] + "Z";
    },
    // A Y-m-d date and 'h:i A' (or 'H:i') time on the wall clock of an IANA timezone, as an
    // absolute Date. Parsing "date time" with new Date() would use the browser's timezone.
    wallClockToDate(date, time, timeZone) {
        let dateParts = String(date || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
        let timeParts = String(time || '').trim().match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?\s*([AaPp][Mm])?$/);
        if (!dateParts || !timeParts) {
            return null;
        }
        let hour = parseInt(timeParts[1], 10);
        if (timeParts[4]) {
            hour = hour % 12 + (timeParts[4].toUpperCase() === 'PM' ? 12 : 0);
        }
        let wallClock = Date.UTC(+dateParts[1], +dateParts[2] - 1, +dateParts[3], hour, +timeParts[2], +(timeParts[3] || 0));

        // Offset of the timezone at a moment, from what its wall clock shows then
        let offsetAt = (timestamp) => {
            try {
                let values = {};
                new Intl.DateTimeFormat('en-US', {
                    timeZone: timeZone || 'UTC', hourCycle: 'h23',
                    year: 'numeric', month: '2-digit', day: '2-digit',
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                }).formatToParts(new Date(timestamp)).forEach(part => { values[part.type] = part.value; });
                return Date.UTC(+values.year, +values.month - 1, +values.day, +values.hour % 24, +values.minute, +values.second) - timestamp;
            } catch (e) {
                return 0; // unknown timezone: treat as UTC
            }
        };
        // Applied twice so a daylight saving change between the guess and the answer settles
        let timestamp = wallClock - offsetAt(wallClock);
        timestamp = wallClock - offsetAt(timestamp);
        return new Date(timestamp);
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