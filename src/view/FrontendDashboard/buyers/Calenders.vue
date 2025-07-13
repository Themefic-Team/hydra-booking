
<script setup>
import { ref, reactive, onBeforeMount, onMounted, computed } from 'vue';
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
 
 
 const calendarbooking = reactive ({
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
});
</script>

<template>
<!-- Booking Calendar View -->
 <div class="tfhb-admin-booking">
    <div  class="tfhb-booking-calendar  " >  
        <FullCalendar class='demo-app-calendar ' :options='calendarbooking'>
            <template v-slot:eventContent='arg'>
                <!-- {{ arg.event.booking_date }}
                <b>{{ arg.timeText }}</b>
                {{ arg.event.extendedProps.booking_time }} --> 
            </template>
        </FullCalendar>
    </div>
 </div>

<!-- Booking Calendar View -->
</template>

<style scoped>
/* @import '@fullcalendar/daygrid/main.css';
@import '@fullcalendar/timegrid/main.css'; */

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
}
</style>
