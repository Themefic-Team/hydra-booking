
<script setup>
import { ref, reactive, onBeforeMount, onMounted, computed } from 'vue'; 
//  Load Time Zone 

import axios from 'axios' 
// const router = useRouter(); 
const skeleton = ref(true);
const meetings= ref({});

// Fetch generalSettings
const FetchEvents = async () => {

    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/sellers/events', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        });
        
    
        if (response.data.status) {  
            skeleton.value = false;
            meetings.value = response.data.meetings;

        }
    } catch (error) {
        console.log(error);
    } 
}

onBeforeMount(() => { 
    FetchEvents();
});
</script>

<template>
<!-- Booking Calendar View -->
 <div :class="{ 'tfhb-skeleton': skeleton }"  class="tfhb-admin-booking">
    <div  class="tfhb-booking-calendar  " >   
        <div class="tfhb-meeting-list" style="margin: 0; max-width: 100% !important; padding: 0;"> 
            <div class="tfhb-meeting-list__wrap tfhb-flexbox tfhb-justify-between" style="width: 100%;"> 
                <div  v-if="meetings.length > 0"  v-for="(items, key) in meetings" class="tfhb-meeting-list__wrap__items" style="width: 48%;"> 
                    <div class="tfhb-meeting-list__wrap__items__wrap">
                        <!-- <?php if($meeting->host_featured_image != ''): ?> -->
                        <div class="tfhb-meeting-list__wrap__items__wrap__img">
                            <img src="#" alt="">
                        </div>
                        <!-- <?php endif; ?> -->
                        <div class="tfhb-meeting-list__wrap__items__wrap__content">
                            <h3>
                                <a href="#">
                                   {{items.title}}
                                </a>
                            </h3>
                             {{items.description}} 
                            <div class="tfhb-meeting-list__wrap__items__wrap__content__tags"> 
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                     {{items.host_first_name}}  
                                </span> 
                                
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 
                                    {{items.duration}} minutes   
                                </span>
                               
                                <span v-if="items.meeting_price">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg> 
                                   {{items.meeting_price}}
                                </span>
                                <span v-els>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg> 
                                   Free
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                        <a :href="items.permalink" target="_blank" class="tfhb-btn secondary-btn">
                            Select
                        </a>
                    </div>
                </div>
 
                <div v-else class="tfhb-meeting-list__wrap__no-found">
                    <p>No Event found.</p>
                </div> 

            </div>
        </div> 
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
