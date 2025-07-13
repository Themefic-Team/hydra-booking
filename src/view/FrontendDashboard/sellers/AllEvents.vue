<script setup> 
import { ref, onBeforeMount, defineProps  } from 'vue'; 
 
 
</script>

<template > 
    <div class="container">
        <div  class="tfhb-booking-details tfhb-mt-32" >
 
            <table class="table" cellpadding="0" :cellspacing="0">
                <thead>
                    <tr> 
                        <th>{{ $tfhb_trans('Date & Time') }}</th>
                        <th>{{ $tfhb_trans('Events') }}</th> 
                        <!-- <th>{{ $tfhb_trans('Attendee') }}</th> --> 
                        <th>{{ $tfhb_trans('Status') }}</th>
                        <th>{{ $tfhb_trans('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr  >
                        <td>Date and time</td>
                        <td> Events</td>
                        <!-- <td>{{ booking.attendee }}</td> -->
                        <td>Status</td>
                        <td>
                            <a href="#" class="btn btn-primary">{{ $tfhb_trans('View') }}</a>
                        </td>
                    </tr>
                    <tr  >
                        <td>Date and time</td>
                        <td> Events</td>
                        <!-- <td>{{ booking.attendee }}</td> -->
                        <td>Status</td>
                        <td>
                            <a href="#" class="btn btn-primary">{{ $tfhb_trans('View') }}</a>
                        </td>
                    </tr>
                    <tr  >
                        <td>Date and time</td>
                        <td> Events</td>
                        <!-- <td>{{ booking.attendee }}</td> -->
                        <td>Status</td>
                        <td>
                            <a href="#" class="btn btn-primary">{{ $tfhb_trans('View') }}</a>
                        </td>
                    </tr>
                    <tr  >
                        <td>Date and time</td>
                        <td> Events</td>
                        <!-- <td>{{ booking.attendee }}</td> -->
                        <td>Status</td>
                        <td>
                            <a href="#" class="btn btn-primary">{{ $tfhb_trans('View') }}</a>
                        </td>
                    </tr>
                    <tr  >
                        <td>Date and time</td>
                        <td> Events</td>
                        <!-- <td>{{ booking.attendee }}</td> -->
                        <td>Status</td>
                        <td>
                            <a href="#" class="btn btn-primary">{{ $tfhb_trans('View') }}</a>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
    
</template>  
<style scoped> 
</style>