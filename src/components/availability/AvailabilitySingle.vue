<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
const props = defineProps([
    'availability', 
]) 
const emit = defineEmits(["delete-availability", "edit-availability"]); 

const deleteAvailability = () => {
    emit('delete-availability');
}
const editAvailability = () => { 
    emit('edit-availability');
}
const markAsDefault = () => {
    emit('mark-as-default');
}

const activeItemDropdown = ref(0);
// on click add class active
const activeSingleMeetingDropdown = (id) => {
    
    if(activeItemDropdown.value == id) {
        activeItemDropdown.value = 0;
        return;
    }
    activeItemDropdown.value = id;  
}
// outside click
window.addEventListener('click', function(e) {
    if (!document.querySelector('.tfhb-content-wrap').contains(e.target)) {
        activeItemDropdown.value = 0;
    }
});
</script>

<template>
  <div class="tfhb-availability-single-box">
    <div class="tfhb-availability-single-box-wrap">
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-flexbox tfhb-gap-16"> 
                <h3 >{{availability.title}}  </h3>   
                <!-- {{ availability }} -->
                <span  v-if="availability.default_status == true"  class="tfhb-availability-default tfhb-flexbox tfhb-gap-4"><Icon name="Heart" size=15 /> {{ __('Default', 'hydra-booking') }}</span>
            </div>
            <div class="thb-admin-btn right"> 
                <div @click="activeSingleMeetingDropdown(availability.id)"  class="tfhb-availability-action tfhb-dropdown">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.0001 10.8337C10.4603 10.8337 10.8334 10.4606 10.8334 10.0003C10.8334 9.54009 10.4603 9.16699 10.0001 9.16699C9.53984 9.16699 9.16675 9.54009 9.16675 10.0003C9.16675 10.4606 9.53984 10.8337 10.0001 10.8337Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.0001 4.99967C10.4603 4.99967 10.8334 4.62658 10.8334 4.16634C10.8334 3.7061 10.4603 3.33301 10.0001 3.33301C9.53984 3.33301 9.16675 3.7061 9.16675 4.16634C9.16675 4.62658 9.53984 4.99967 10.0001 4.99967Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.0001 16.6667C10.4603 16.6667 10.8334 16.2936 10.8334 15.8333C10.8334 15.3731 10.4603 15 10.0001 15C9.53984 15 9.16675 15.3731 9.16675 15.8333C9.16675 16.2936 9.53984 16.6667 10.0001 16.6667Z" stroke="#765664" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div v-show="availability.id == activeItemDropdown"  class="tfhb-dropdown-wrap active">
                        <span class="tfhb-dropdown-single" @click="editAvailability">{{ __('Edit', 'hydra-booking') }}</span>
                        <span  v-if="availability.default_status != true"  class="tfhb-dropdown-single" @click="markAsDefault">{{ __('Default', 'hydra-booking') }}</span>
                        <!-- <span class="tfhb-dropdown-single">Duplicate</span> -->
                        <span v-if="availability.default_status != true"  class="tfhb-dropdown-single tfhb-dropdown-error" @click="deleteAvailability">{{ __('Delete', 'hydra-booking') }}</span>
                    </div>
                </div>
            </div> 
        </div>
        <div class="tfhb-availability-single-box-info  tfhb-flexbox">
            <Icon name="Clock" size=20 />  
            <span class="fthb-availability-timeslots"><p v-for="(day, key)  in availability.time_slots" :key="key"  v-show = "day.status == 1"  >    
                {{ day.status == 1 ? day.day + ' (' + day.times[0].start + ' - ' + day.times[0].end + ') ,' : '' }} 
            </p></span>
        </div>
        <div class="tfhb-availability-single-box-info tfhb-flexbox" v-if="availability.time_zone">
            <Icon name="MapPin" size=20 /> 
            <span >{{availability.time_zone}}</span>
        </div>
    </div>
  </div>
</template>

<style scoped>
</style> 