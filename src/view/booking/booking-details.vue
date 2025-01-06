<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import HbText from '@/components/form-fields/HbText.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
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



onBeforeMount(() => { 

    BookingDetails.fetchBookingsDetails(bookingId);
});
</script>

<template> 

    <div class="tfhb-booking-single-details">
       <div class="tfhb-booking-heading tfhb-flexbox tfhb-gap-4 tfhb-full-width tfhb-justify-between">
            <div class="tfhb-booking-heading-left tfhb-flexbox tfhb-gap-8">
                <div class="prev-navigator tfhb-cursor-pointer" @click="TfhbPrevNavigator()">
                    
                    <Icon name="ArrowLeft" size=15 /> 
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
                    <div class="status completed"  > 
                        completed
                    </div>
                </div>
            </div>
            <div class="tfhb-booking-heading-right tfhb-flexbox tfhb-gap-8">
                <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="alert(1)"
                    :buttonText="$tfhb_trans('More')" 
                    icon="ChevronRight" 
                /> 
                <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="alert(1)"
                    :buttonText="$tfhb_trans('Print')" 
                    icon="Printer" 
                    icon_position="left"
                /> 
            </div>
            
       </div>

       <div class="tfhb-booking-details-wrap tfhb-flexbox tfhb-gap-16 tfhb-justify-between tfhb-align-normal">
            <div class="tfhb-booking-details-content tfhb-flexbox tfhb-gap-16">
                <div class="tfhb-b-d-wrap">
                    <h4>{{ $tfhb_trans('Meeting Details') }}</h4> 
                    <div class="tfhb-b-d-icon-box-wrap tfhb-flexbox tfhb-gap-32 tfhb-justify-between">
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
                                <p> 
                                    {{ Tfhb_Date(BookingDetails.booking.meeting_dates) }}
                                </p>
                            </div>
                        </div>     
                          <!-- Booking Details Icon Box -->
                          <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                            <Icon name="NotepadText" size=20 /> 
                            <div class="tfhb-b-d-icon-content">
                                <h5>{{ $tfhb_trans('Note') }}</h5> 
                                <p> 
                                    N/A
                                </p>
                            </div>
                        </div>     
                    </div>
                </div>
                <div class="tfhb-b-d-wrap tfhb-flexbox tfhb-gap-16 tfhb-full-width" v-if="'one-to-one' == BookingDetails.booking.meeting_type">
                     
                    <div class="tfhb-b-d-inner tfhb-full-width">
                        <h4>{{ $tfhb_trans('Attendee Details') }}</h4>
                        <div class="tfhb-b-d-icon-box-wrap bg-wrap tfhb-flexbox tfhb-gap-32 tfhb-justify-between tfhb-align-normal">
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
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Banknote" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Payment') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].payment_method}}  
                                    </p>
                                </div>
                            </div>      
                                    
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="NotepadText" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Note') }}</h5> 
                                    <p> 
                                        N/A
                                    </p>
                                </div>
                            </div>     
                        </div>
                    </div>
                    <div class="tfhb-b-d-inner tfhb-full-width" v-if="'free' != BookingDetails.attendees[0].payment_method">
                        <h4>{{ $tfhb_trans('Payment Details') }}</h4>
                        <div class="tfhb-b-d-icon-box-wrap bg-wrap  tfhb-flexbox tfhb-gap-32 tfhb-justify-between tfhb-align-normal">
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
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="Banknote" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Payment') }}</h5>
                                    <p> 
                                        {{BookingDetails.attendees[0].payment_method}}  
                                    </p>
                                </div>
                            </div>      
                                    
                            <!-- Booking Details Icon Box -->
                            <div class="tfhb-b-d-icon-box tfhb-flexbox tfhb-gap-8 tfhb-align-normal">
                                <Icon name="NotepadText" size=20 /> 
                                <div class="tfhb-b-d-icon-content">
                                    <h5>{{ $tfhb_trans('Note') }}</h5> 
                                    <p> 
                                        N/A
                                    </p>
                                </div>
                            </div>     
                        </div>
                    </div>
                </div> 
            </div>
            <div class="tfhb-booking-details-activity">
                <div class="tfhb-b-d-wrap">
                    <h4>Activity Details</h4>

                    <div class="tfhb-activity-timeline tfhb-flexbox tfhb-gap-16">
                        <div class="tfhb-activity-single-timeline">
                            <div class="tfhb-a-s-t-icon">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.99992 14.6673C11.6818 14.6673 14.6666 11.6825 14.6666 8.00065C14.6666 4.31875 11.6818 1.33398 7.99992 1.33398C4.31802 1.33398 1.33325 4.31875 1.33325 8.00065C1.33325 11.6825 4.31802 14.6673 7.99992 14.6673Z" fill="#56765B"/>
                                    <path d="M6 7.99935L7.33333 9.33268L10 6.66602" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>  
                            </div>
                            <div class="tfhb-a-s-t-content">
                                <span>Jan 24, 2025, 09:01 PM</span>
                                <h5>Reminder Email Sent</h5>
                                <p>Reminder email sent to host hosts Reminder email sent to host.</p>
                            </div>
                        </div>
                        <div class="tfhb-activity-single-timeline">
                            <div class="tfhb-a-s-t-icon">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.99992 14.6673C11.6818 14.6673 14.6666 11.6825 14.6666 8.00065C14.6666 4.31875 11.6818 1.33398 7.99992 1.33398C4.31802 1.33398 1.33325 4.31875 1.33325 8.00065C1.33325 11.6825 4.31802 14.6673 7.99992 14.6673Z" fill="#56765B"/>
                                    <path d="M6 7.99935L7.33333 9.33268L10 6.66602" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>  
                            </div>
                            <div class="tfhb-a-s-t-content">
                                <span>Jan 24, 2025, 09:01 PM</span>
                                <h5>Reminder Email Sent</h5>
                                <p>Reminder email sent to host hosts Reminder email sent to host.</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
       </div> 

    </div>
</template>

<style scoped>

</style>