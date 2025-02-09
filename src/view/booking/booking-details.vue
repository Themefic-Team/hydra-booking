<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import HbText from '@/components/form-fields/HbText.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbTextarea from '@/components/form-fields/HbTextarea.vue';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 
import { useRouter, useRoute } from 'vue-router' 
const router = useRouter();
const route = useRoute();
import { BookingDetails } from '@/store/booking/booking-details';
import useDateFormat from '@/store/dateformat'
// Fetch Pre booking Data


// get booking id from route
const bookingId = route.params.id;

const { Tfhb_Date, Tfhb_Time, Tfhb_DateTime } = useDateFormat();

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
        // skip empty link
         if(link == '' || link == ' '){
              return;
            }
       let linkParts = link.split(','); 
       linkHtml += '<a href="'+linkParts+'" target="_blank">Join Now</a> <br>';
   });
   return linkHtml; 

}
 



// Active Action Dropdwon
const activeBookingAction = ref(0);

// Single Attendee Details able to show multiple items at a time
const activeAttendeeDetails = reactive([]);

const activeSingleAttendeeDetails = (attendee_id) => {  
    if (activeAttendeeDetails.includes(attendee_id)) {
        // Update the contents of activeAttendeeDetails without reassigning it
        const index = activeAttendeeDetails.indexOf(attendee_id);
        if (index !== -1) {
            activeAttendeeDetails.splice(index, 1); // Removes the attendee_id
        }
        return;
    }
    // remove first all the items 
    activeAttendeeDetails.push(attendee_id); 
};

// Single Attendee Details able to show multiple items at a time
const activeAttendeeAction = reactive([]);

const activeSingleAttendeeAction = (attendee_id) => {  
    if (activeAttendeeAction.includes(attendee_id)) {
        // Update the contents of activeAttendeeAction without reassigning it
        const index = activeAttendeeAction.indexOf(attendee_id);
        if (index !== -1) {
            activeAttendeeAction.splice(index, 1); // Removes the attendee_id
        }
        return;
    }
    // remove first all the items
    activeAttendeeAction.splice(0, activeAttendeeAction.length);
    activeAttendeeAction.push(attendee_id);
};
 

// Hide Single Attendee Details
const goForReschedule = (attendee) => {
  
  let url = tfhb_core_apps.admin_url+'/?hydra-booking=booking&hash='+attendee.hash+'&meetingId='+attendee.meeting_id+'&type=reschedule';
  window.open(url, '_blank');
}

// Cencel Attendee  
const cancelAttendee = (attendee) => {    
    if(attendee.status == 'canceled'){ 
        toast.error('Already Canceled', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return;
    } 
    BookingDetails.cancelAttendee.id = attendee.id;
    BookingDetails.cancelAttendee.booking_id = attendee.booking_id; 
    BookingDetails.cancelAttendee.attendee_name = attendee.attendee_name; 
    BookingDetails.cancelAttendee.cancel_reason = attendee.reason ?? attendee.reason != null ? attendee.reason : ''; 
    BookingDetails.attendeeCencelPopup = true; 
}
// attendeeCencelPopup close
const attendeeCencelPopupClose = () => {
    BookingDetails.attendeeCencelPopup = false;
    BookingDetails.attendeeCancelPreloader = false;
}

// Get booking Activity 
const getSingleAttendeeActivity = ($value) => {   
    $value = JSON.parse($value);
    let activityHtml = ''; 
    activityHtml += '<span>'+Tfhb_DateTime($value.datetime)+'</span> <h5>'+$value.title+'</h5> <p>'+$value.description+'</p>';
 
    return activityHtml;

}
// edit internal note  

function hideDropdownOutsideClick(e) {  
    // const multiSelectPanel = document.querySelector('.p-multiselect-panel'); // Dynamically check for p-multiselect-panel
    if (!document.querySelector('.tfhb-booking-details-action').contains(e.target)) { 
        activeBookingAction.value = false;
    }
    // if (!document.querySelector('.tfhb-b-d-icon-cta').contains(e.target)) { 
        
    //     // activeAttendeeDetails make empty
    //     console.log(activeAttendeeDetails);

    // }
    
    
}
onBeforeMount(() => {  
    BookingDetails.fetchBookingsDetails(bookingId, router);
    window.addEventListener('click', hideDropdownOutsideClick);
});

const TfhbPrevNavigator = () => {
    router.push({ name: 'BookingLists' });
}
</script>

<template> 
<!-- Cencel  POPUP-->
    <HbPopup   :isOpen="BookingDetails.attendeeCencelPopup" @modal-close="attendeeCencelPopupClose()" max_width="400px" name="first-modal">
        <template #header> 
            <h3>{{$tfhb_trans('Cancel Meeting')}}</h3>   
        </template>  

        <template #content>  
            <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24"> 
                <div class="tfhb-close-content">
                    <h3> {{BookingDetails.booking.host_first_name}} <br> <span> With <b>{{  BookingDetails.cancelAttendee.attendee_name }}</b> </span> </h3>
                    <b>{{ Tfhb_Date(BookingDetails.booking.meeting_dates) }}, {{ BookingDetails.booking.start_time }} - {{ BookingDetails.booking.end_time }}</b>
                    <br>    
                    <br>    
                    <HbTextarea  
                        v-model="BookingDetails.cancelAttendee.cancel_reason" 
                        required= "true"  
                        name="description"
                        :label="$tfhb_trans('Please confirm that you would like to cancel this event. A cancellation email will also go out to the invitee')"  
                        :placeholder="$tfhb_trans('Reason for cancellation')" 
                    /> 
                    
                </div>
                <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click.stop="attendeeCencelPopupClose()"
                        :buttonText="$tfhb_trans( `No, Don't cancel`)" 
                    />
                    <HbButton  
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                        @click=" BookingDetails.cancelBookingAttendee()"
                        :buttonText="$tfhb_trans('Yes, Cancel')" 
                        icon="X" 
                        :pre_loader="BookingDetails.attendeeCancelPreloader"
                        :hover_animation="false" 
                        icon_position = 'left'
                    />
                    
                </div>
            </div> 
        </template> 
    </HbPopup>
<!-- Cencel  POPUP-->

<!-- Delete Popup -->
<HbPopup :isOpen="BookingDetails.deletePopup" @modal-close="BookingDetails.deletePopup = !BookingDetails.deletePopup" max_width="400px" name="first-modal"> 
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
                    @click="BookingDetails.deleteBooking(router)"
                    :buttonText="$tfhb_trans('Delete')"
                    icon="Trash2"   
                    :hover_animation="false" 
                    icon_position = 'left'
                />
            </div>
        </div> 
    </template> 
</HbPopup>
<!-- Delete Popup -->

    <div :class="{   'tfhb-skeleton': BookingDetails.skeleton } "  class="tfhb-booking-single-details">
       <div class="tfhb-booking-heading tfhb-flexbox tfhb-gap-4 tfhb-full-width tfhb-justify-between">
            <div class="tfhb-booking-heading-left tfhb-flexbox tfhb-gap-8">
                <div class="prev-navigator tfhb-cursor-pointer" @click="TfhbPrevNavigator()">
                    
                    <svg width="18" height="22" viewBox="0 0 18 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.80008 18.3331L3.66675 13.1997L8.80008 8.06641" stroke="#2E6B38" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.9334 13.1992H3.66675" stroke="#2E6B38" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- <HbPreloader v-else color="#2E6B38" /> -->
                </div>
                <h4> {{ BookingDetails.booking.title }}</h4>
                <div class="booking-details-timeframe tfhb-flexbox tfhb-gap-8">
                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.26676 1.66602V4.99935M13.9334 1.66602V4.99935M3.1001 8.33268H18.1001M7.26676 11.666H7.2751M10.6001 11.666H10.6084M13.9334 11.666H13.9418M7.26676 14.9993H7.2751M10.6001 14.9993H10.6084M13.9334 14.9993H13.9418M4.76676 3.33268H16.4334C17.3539 3.33268 18.1001 4.07887 18.1001 4.99935V16.666C18.1001 17.5865 17.3539 18.3327 16.4334 18.3327H4.76676C3.84629 18.3327 3.1001 17.5865 3.1001 16.666V4.99935C3.1001 4.07887 3.84629 3.33268 4.76676 3.33268Z" stroke="#002AB3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>{{$tfhb_trans('Timeframe:')}} <b>{{ Tfhb_Date(BookingDetails.booking.meeting_dates) }}, {{ BookingDetails.booking.start_time }} - {{ BookingDetails.booking.end_time }}</b> </span>
                </div> 
                <div class="tfhb-details-status" >
                    <div v-if="'one-to-one' == BookingDetails.booking.meeting_type" class="status" :class="BookingDetails.attendees[0].status "  > 
                        {{BookingDetails.attendees[0].status}} 
                    </div>
                    <div v-else class="status" :class="BookingDetails.booking.status "  > 
                        {{BookingDetails.booking.status}} 
                    </div>
                </div>
            </div> 
            <div class="tfhb-booking-heading-right tfhb-flexbox tfhb-gap-8"> 
                <div class="tfhb-booking-details-action tfhb-dropdown ">
                    <button  @click="activeBookingAction = !activeBookingAction"  class="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" style="padding-right: 16px;">
                        {{ $tfhb_trans('More') }} 
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt="">
                    </button>
                    
                    <transition name="tfhb-dropdown-transition">
                        <div v-show="activeBookingAction == true" class="tfhb-dropdown-wrap "> 
                             
                            <span class="tfhb-dropdown-single " @click="BookingDetails.ChangeBookingStatus('completed')"><Icon name="FileCheck" size=16 />{{ $tfhb_trans('Mark as Complete') }}</span>
                            <span class="tfhb-dropdown-single "  v-if="'one-to-one' == BookingDetails.booking.meeting_type" @click.stop="goForReschedule(BookingDetails.attendees[0])"><Icon name="RefreshCw" size=16 />{{ $tfhb_trans('Re-Schedule') }}</span> 
                            <span class="tfhb-dropdown-single " v-if="'one-to-one' == BookingDetails.booking.meeting_type" @click.stop="cancelAttendee(BookingDetails.attendees[0])"><Icon name="X" size=16 />{{ $tfhb_trans('Cancel') }}</span>
                            <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="BookingDetails.deletePopup = true"><Icon name="Trash" size=16 />{{ $tfhb_trans('Delete') }}</span>
                            
                        </div>
                    </transition>
                </div>
                <!-- <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="alert(1)"
                    :buttonText="$tfhb_trans('Print')" 
                    icon="Printer" 
                    icon_position="left"
                />  -->
            </div>
            
       </div>
       <div class="tfhb-booking-details-wrap tfhb-flexbox tfhb-gap-16 tfhb-justify-between tfhb-align-normal">
            <div class="tfhb-booking-details-content tfhb-flexbox tfhb-gap-16">
                <div class="tfhb-b-d-wrap">
                    <h3>{{ $tfhb_trans('Meeting Details') }}</h3> 
                    
                    <!-- {{BookingDetails.booking}} -->
                    <div class="tfhb-b-d-icon-box-wrap tfhb-flexbox tfhb-gap-24 tfhb-justify-between tfhb-align-normal">
                        <!-- Booking Details Icon Box -->
                        <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="User" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <h5>{{ $tfhb_trans('Host') }}</h5>
                                <p> 
                                    {{BookingDetails.booking.host_first_name}} {{BookingDetails.booking.host_last_name}}
                                </p>
                            </div>
                        </div>     
                        <!-- Booking Details Icon Box -->
                        <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="Globe" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <h5>{{ $tfhb_trans('Timezone') }}</h5>
                                <p> 
                                    {{BookingDetails.booking.availability_time_zone}}
                                </p>
                            </div>
                        </div>      
                        <!-- Booking Details Icon Box -->
                        <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="Clock5" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <h5>{{ $tfhb_trans('Durations') }}</h5>
                                <p> 
                                    {{BookingDetails.booking.duration}} {{ $tfhb_trans('Minutes') }}
                                </p>
                            </div>
                        </div>      
                        <!-- Booking Details Icon Box -->
                        <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="CalendarDays" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <h5>{{ $tfhb_trans('Date') }}</h5>
                                <p> 
                                    {{ Tfhb_Date(BookingDetails.booking.meeting_dates) }}
                                </p>
                            </div>
                        </div>     
                        <!-- Booking Details Icon Box -->
                        <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="MapPin" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <h5>{{ $tfhb_trans('Location') }}</h5>  
                                <div v-for=" (address, index) in BookingDetails.location" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: calc(100% - 16px)">
                                    
                                    <div class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal" v-if="address.location == 'zoom'" >
                                     
                                        <span>{{ $tfhb_trans('Zoom') }} </span> <span v-html="MakeMeetingLink(address.address.link)"></span>  
                                    </div>
                                    <div class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal meet" v-else-if="address.location == 'meet'" >
                                        
                                        <span>{{ $tfhb_trans('Google Meet') }} </span>  <span v-html="MakeMeetingLink(address.address)"></span> 
                                    </div>
                                    <div class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal" v-else  > 
                                        <span>{{address.location}} - {{address.address}}</span>   
                                    </div>
                                </div>  
                            </div>
                        </div>     
                          <!-- Booking Details Icon Box -->
                          <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="NotepadText" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <div class="tfhb-flexbox tfhb-gap-8 tfhb-align-normal">

                                    <h5>{{ $tfhb_trans('Internal Note') }}    </h5> 
                                    <span style="margin-top:0; cursor: pointer" @click="BookingDetails.showinternalNoteEdit = !BookingDetails.showinternalNoteEdit "><Icon name="PencilLine" size=15 /></span> 
                                </div>
                                <transition name="accordion">  
                                    <div v-show="BookingDetails.showinternalNoteEdit == true" class="tfhb-internal-note-input tfhb-flexbox tfhb-gap-8" >
                                        <HbTextarea  
                                            v-model="BookingDetails.internal_note" 
                                            required= "true"  
                                            name="description" 
                                        />
                                        
                                        <div class="tfhb-flexbox tfhb-gap-8">
                                            <HbButton 
                                                classValue="tfhb-btn boxed-btn "  
                                                @click="BookingDetails.updateInternalNote()"
                                                :buttonText="$tfhb_trans('Update')" 
                                            /> 
                                            <HbButton 
                                                classValue="tfhb-btn boxed-btn-danger"  
                                                @click="BookingDetails.showinternalNoteEdit = !BookingDetails.showinternalNoteEdit"
                                                :buttonText="$tfhb_trans('Cancel')" 
                                            /> 
                                        </div>

                                    </div>
                                </transition> 
                                <p v-if="BookingDetails.internal_note == '' && BookingDetails.showinternalNoteEdit != true">  
                                    {{ $tfhb_trans('N/A') }}
                                </p>
                                <p v-if="BookingDetails.internal_note != '' && BookingDetails.showinternalNoteEdit != true"> 
                                    {{BookingDetails.internal_note}}
                                </p>
                            </div>
                        </div>     
                    </div>
                </div>
                <!-- For Single Booking -->
                <div class="tfhb-b-d-wrap tfhb-flexbox tfhb-gap-16 tfhb-full-width" v-if="'one-to-one' == BookingDetails.booking.meeting_type">
 
                    <div class="tfhb-b-d-inner tfhb-full-width">
                        <h3>{{ $tfhb_trans('Attendee Details') }}</h3>
                        <div class="tfhb-b-d-icon-box-wrap bg-wrap tfhb-flexbox tfhb-gap-24  tfhb-align-normal">
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="User" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Name') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].attendee_name}}  
                                    </p>
                                </div>
                            </div>     
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Mail" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Email') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].email}}  
                                    </p>
                                </div>
                            </div>  
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Globe" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Timezone') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].attendee_time_zone}}  
                                    </p>
                                </div>
                            </div>
                             <!-- Booking Details Icon Box -->
                             <div v-if="BookingDetails.attendees[0].address !=''" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="MapPin" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Address') }}</h5>
                                    <p> 
                                        {{ BookingDetails.attendees[0].address }}  
                                    </p>
                                </div>
                            </div>  
                            <!-- Booking Details Icon Box -->
                            <div v-if="BookingDetails.attendees[0].status == 'canceled' || BookingDetails.attendees[0].status == 'rescheduled'" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="FileQuestion" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Reason') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].reason}}  
                                    </p>
                                </div>
                            </div> 
                                     
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Banknote" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Payment') }}</h5>
                                    <p v-if="BookingDetails.attendees[0].payment_method == 'free'"> 
                                        {{BookingDetails.attendees[0].payment_method}}  
                                    </p>
                                    <p v-else> 
                                        {{BookingDetails.attendees[0].payment_status}}  
                                    </p>
                                </div>
                            </div>       
                            <div v-show = "Object.keys(BookingDetails.attendees[0].others_info).length > 0" v-for=" (others_info, index) in BookingDetails.attendees[0].others_info" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Disc" size=20 /> 
                               
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{index}}</h5>
                                    <p> 
                                        {{ others_info }}  
                                    </p>
                                </div>
                            </div>
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Clock5" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Booked At') }}</h5>
                                    <p> 
                                        {{ Tfhb_DateTime( BookingDetails.attendees[0].created_at ) }}  
                                    </p>
                                </div>
                            </div> 
                            <!-- Booking Details Icon Box -->
                            <!-- <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="NotepadText" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Note') }}</h5> 
                                    <p> 
                                        N/A
                                    </p>
                                </div>
                            </div>      -->
                        </div>
                    </div>
                    <div class="tfhb-b-d-inner tfhb-full-width" v-if="'free' != BookingDetails.attendees[0].payment_method">
                        <div class="tfhb-flexbox tfhb-gap-8 tfhb-b-d-inner-title">
                            <h4>{{ $tfhb_trans('Payment Details') }}</h4>
                            <span class="status" :class="BookingDetails.attendees[0].payment_status"> {{BookingDetails.attendees[0].payment_status}} </span> 
                        </div>
                        
                        <div class="tfhb-b-d-icon-box-wrap bg-wrap  tfhb-flexbox tfhb-gap-24 tfhb-align-normal">
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="User" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Name') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].attendee_name}}  
                                    </p>
                                </div>
                            </div>     
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Mail" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Email') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].email}}  
                                    </p>
                                </div>
                            </div>  
                             
                            <!-- Booking Details Icon Box -->
                            <div v-if="BookingDetails.attendees[0].transaction" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="DollarSign" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Total Payment') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].transaction.total}}  
                                    </p>
                                </div>
                            </div>        
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Banknote" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Payment Method') }}</h5>
                                    <img v-if="BookingDetails.attendees[0].payment_method == 'paypal_payment'" :src="$tfhb_url+'/assets/images/paypal.svg'" alt="" style="height: 20px;">
                                    <img v-if="BookingDetails.attendees[0].payment_method == 'stripe_payment'" :src="$tfhb_url+'/assets/images/stripe-small.svg'" alt="" style="height: 20px;">
                                    <img v-if="BookingDetails.attendees[0].payment_method == 'woo_payment'" :src="$tfhb_url+'/assets/images/Woo.png'" alt="" style="height: 20px;">
                                </div>
                            </div>      
                                    
                            <!-- Booking Details Icon Box -->
                            <div v-if="BookingDetails.attendees[0].transaction"  class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3334 8.33268H15.0001M13.3334 11.666H15.0001M5.14175 12.4993C5.31353 12.011 5.63267 11.5881 6.0551 11.2889C6.47753 10.9897 6.98242 10.829 7.50008 10.829C8.01774 10.829 8.52263 10.9897 8.94507 11.2889C9.3675 11.5881 9.68663 12.011 9.85841 12.4993M9.16675 9.16602C9.16675 10.0865 8.42056 10.8327 7.50008 10.8327C6.57961 10.8327 5.83341 10.0865 5.83341 9.16602C5.83341 8.24554 6.57961 7.49935 7.50008 7.49935C8.42056 7.49935 9.16675 8.24554 9.16675 9.16602ZM3.33341 4.16602H16.6667C17.5872 4.16602 18.3334 4.91221 18.3334 5.83268V14.166C18.3334 15.0865 17.5872 15.8327 16.6667 15.8327H3.33341C2.41294 15.8327 1.66675 15.0865 1.66675 14.166V5.83268C1.66675 4.91221 2.41294 4.16602 3.33341 4.16602Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Payment ID') }}</h5> 
                                    <p> 
                                        {{BookingDetails.attendees[0].transaction.transation_history.payment_id}}  
                                    </p>
                                </div>
                            </div>     
                        </div>
                    </div>
                </div> 
                 <!-- For Single Booking --> 
                 <!-- For Group Booking -->
                 <div class="tfhb-b-d-wrap tfhb-flexbox tfhb-gap-0 tfhb-full-width" v-if="'one-to-group' == BookingDetails.booking.meeting_type">
                     
                    <h3 class="tfhb-mb-8">{{ $tfhb_trans('Attendee Details') }}</h3>
                    <!-- Single Attendee Content -->
                     <div v-for=" (attendees, index) in BookingDetails.attendees" class="tfhb-b-d-inner tfhb-singlee-attendees tfhb-full-width"
                        :class="activeAttendeeDetails.includes(attendees.id) ? 'active' : ''"
                     >
                         <div class="tfhb-b-d-icon-box-wrap tfhb-group-accordion-bar bg-wrap no-border tfhb-flexbox tfhb-gap-24  tfhb-justify-between">
                             <!-- Booking Details Icon Box -->
                             
                             <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal"> 
                                 <div class="tfhb-b-d-icon-content"> 
                                    <p> 
                                        {{attendees.attendee_name}}  
                                    </p>
                                 </div>
                             </div>  
                             
                             <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal"> 
                                 <div class="tfhb-b-d-icon-content"> 
                                     <p> 
                                         {{attendees.email}}  
                                     </p>
                                 </div>
                             </div>  
                             <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-justify-between"> 

                                <div class="tfhb-details-status tfhb-flexbox tfhb-justify-normal tfhb-gap-0"> 
                                    <div class="status" :class="attendees.status">
                                        {{ attendees.status }}
                                    </div> 
                                </div>
                                 
                             </div> 
                             <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-16 tfhb-justify-end"> 
                                <div  @click.stop="activeSingleAttendeeDetails(attendees.id )"  class=" tfhb-flexbox tfhb-gap-4 accordion-content"  > 
                                    <span>Details</span>
                                    <span class="accordion-icons">
                                        <!-- activeAttendeeDetails is reactive array -->
                                        <Icon v-if="!activeAttendeeDetails.includes(attendees.id)" name="ChevronRight" size=20 />
                                        <Icon v-if="activeAttendeeDetails.includes(attendees.id)" name="ChevronDown" size=20 />
                                    </span>
                                
                                </div> 
                                <div class="tfhb-b-d-icon-cta ">  
                                    <div  @click="activeSingleAttendeeAction(attendees.id)"  class="tfhb-booking-details-action tfhb-dropdown "> 
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.99984 10.8327C10.4601 10.8327 10.8332 10.4596 10.8332 9.99935C10.8332 9.53911 10.4601 9.16602 9.99984 9.16602C9.5396 9.16602 9.1665 9.53911 9.1665 9.99935C9.1665 10.4596 9.5396 10.8327 9.99984 10.8327Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.99984 5.00065C10.4601 5.00065 10.8332 4.62756 10.8332 4.16732C10.8332 3.70708 10.4601 3.33398 9.99984 3.33398C9.5396 3.33398 9.1665 3.70708 9.1665 4.16732C9.1665 4.62756 9.5396 5.00065 9.99984 5.00065Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.99984 16.6667C10.4601 16.6667 10.8332 16.2936 10.8332 15.8333C10.8332 15.3731 10.4601 15 9.99984 15C9.5396 15 9.1665 15.3731 9.1665 15.8333C9.1665 16.2936 9.5396 16.6667 9.99984 16.6667Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <transition name="tfhb-dropdown-transition">
                                            <div v-show="activeAttendeeAction.includes(attendees.id)" class="tfhb-dropdown-wrap ">  
                                                <span class="tfhb-dropdown-single"  @click.stop="goForReschedule(attendees)"><Icon name="RefreshCw" size=16 />{{ $tfhb_trans('Re-Schedule') }}</span> 
                                                <span class="tfhb-dropdown-single " @click="cancelAttendee(attendees)"><Icon name="X" size=16 />{{ $tfhb_trans('Cancel') }}</span>  
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                                </div>       
                         </div>
                         <transition name="accordion">
                            <div v-show="activeAttendeeDetails.includes(attendees.id)"  class="tfhb-singlee-attendees-inner tfhb-full-width" >
                                <div class="tfhb-b-d-icon-box-wrap no-border tfhb-flexbox tfhb-gap-24  tfhb-align-normal">
                                    <!-- Booking Details Icon Box -->
                                      
                                    <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="Clock5" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Booked At') }}</h5>
                                            <p> 
                                                {{ Tfhb_DateTime( attendees.created_at ) }}  
                                            </p>
                                        </div>
                                    </div> 
                                    <!-- Booking Details Icon Box -->
                                    <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="Globe" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Timezone') }}</h5>
                                            <p> 
                                                {{attendees.attendee_time_zone}}  
                                            </p>
                                        </div>
                                    </div> 
                                     <!-- Booking Details Icon Box -->
                                    <div v-if="attendees.address !=''" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="MapPin" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Address') }}</h5>
                                            <p> 
                                                {{ attendees.address }}  
                                            </p>
                                        </div>
                                    </div> 
                                    <div v-show = "Object.keys(attendees.others_info).length > 0" v-for=" (others_info, index) in attendees.others_info" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="Disc" size=20 /> 
                                    
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{index}}</h5>
                                            <p> 
                                                {{ others_info }}  
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Booking Details Icon Box -->
                                    <div v-if="attendees.status == 'canceled' || attendees.status == 'rescheduled'" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="FileQuestion" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Reason') }}</h5>
                                            <p> 
                                                {{attendees.reason}}  
                                            </p>
                                        </div>
                                    </div> 
                                </div>

                                <div class="tfhb-flexbox tfhb-gap-8 tfhb-b-d-inner-title tfhb-pt-16 tfhb-pb-8">
                                    <h4>{{ $tfhb_trans('Payment Details') }}</h4>
                                    <span class="status" :class="attendees.payment_status"> {{attendees.payment_status}} </span> 
                                </div>

                                <div class="tfhb-b-d-icon-box-wrap tfhb-flexbox no-border tfhb-gap-24 tfhb-align-normal">
                                    <!-- Booking Details Icon Box -->
                                    <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="User" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Name') }}</h5>
                                            <p> 
                                                {{attendees.attendee_name}}  
                                            </p>
                                        </div>
                                    </div>     
                                    <!-- Booking Details Icon Box -->
                                    <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="Mail" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Email') }}</h5>
                                            <p> 
                                                {{attendees.email}}  
                                            </p>
                                        </div>
                                    </div>  
                                    <!-- Booking Details Icon Box -->
                                    <div v-if="attendees.transaction" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="DollarSign" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Total Payment') }}</h5>
                                            <p> 
                                                {{attendees.transaction.total}}  
                                            </p>
                                        </div>
                                    </div>        
                                    <!-- Booking Details Icon Box -->
                                    <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <Icon name="Banknote" size=20 /> 
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Payment Method') }}</h5>
                                            <img v-if="attendees.payment_method == 'paypal_payment'" :src="$tfhb_url+'/assets/images/paypal.svg'" alt="" style="height: 20px;">
                                            <img v-if="attendees.payment_method == 'stripe_payment'" :src="$tfhb_url+'/assets/images/stripe-small.svg'" alt="" style="height: 20px;">
                                            <img v-if="attendees.payment_method == 'woo_payment'" :src="$tfhb_url+'/assets/images/Woo.png'" alt="" style="height: 20px;">
                                        </div>
                                    </div>      
                                            
                                    <!-- Booking Details Icon Box -->
                                    <div  v-if="attendees.transaction" class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13.3334 8.33268H15.0001M13.3334 11.666H15.0001M5.14175 12.4993C5.31353 12.011 5.63267 11.5881 6.0551 11.2889C6.47753 10.9897 6.98242 10.829 7.50008 10.829C8.01774 10.829 8.52263 10.9897 8.94507 11.2889C9.3675 11.5881 9.68663 12.011 9.85841 12.4993M9.16675 9.16602C9.16675 10.0865 8.42056 10.8327 7.50008 10.8327C6.57961 10.8327 5.83341 10.0865 5.83341 9.16602C5.83341 8.24554 6.57961 7.49935 7.50008 7.49935C8.42056 7.49935 9.16675 8.24554 9.16675 9.16602ZM3.33341 4.16602H16.6667C17.5872 4.16602 18.3334 4.91221 18.3334 5.83268V14.166C18.3334 15.0865 17.5872 15.8327 16.6667 15.8327H3.33341C2.41294 15.8327 1.66675 15.0865 1.66675 14.166V5.83268C1.66675 4.91221 2.41294 4.16602 3.33341 4.16602Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="tfhb-b-d-icon-content">
                                            <h5>{{ $tfhb_trans('Payment ID') }}</h5> 
                                            <p> 
                                                {{attendees.transaction.transation_history.payment_id}}  
                                            </p>
                                        </div>
                                    </div>     
                                </div>
                            </div>
                        </transition> 
                     </div> 
                     <!-- Single Attendee Content -->
                 </div> 
                 <!-- For Group Booking -->
            </div>
            <div class="tfhb-booking-details-activity">
                <div class="tfhb-b-d-wrap">  
                    <h3>{{ $tfhb_trans('Activity Details') }}</h3> 
                    <div v-if="BookingDetails.booking_activity"  class="tfhb-activity-timeline tfhb-flexbox tfhb-gap-16">
                        <div v-for=" (activity, index) in BookingDetails.booking_activity" class="tfhb-activity-single-timeline">
                            <div class="tfhb-a-s-t-icon">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.99992 14.6673C11.6818 14.6673 14.6666 11.6825 14.6666 8.00065C14.6666 4.31875 11.6818 1.33398 7.99992 1.33398C4.31802 1.33398 1.33325 4.31875 1.33325 8.00065C1.33325 11.6825 4.31802 14.6673 7.99992 14.6673Z" fill="#56765B"/>
                                    <path d="M6 7.99935L7.33333 9.33268L10 6.66602" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>  
                            </div>
                            <div class="tfhb-a-s-t-content" v-html="getSingleAttendeeActivity(activity.value)"></div>
                        </div> 
                    </div>
                    <div v-if="BookingDetails.booking_activity.length === 0" class=" " style="text-align:center;">
                        <div class="tfhb-activity-single-timeline">
                          <h5  > {{ $tfhb_trans('No activity found') }}</h5>
                        </div> 
                    </div>
 
                </div>
            </div>
       </div> 

    </div>
</template>

<style scoped>

</style>