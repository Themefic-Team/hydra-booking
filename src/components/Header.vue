<script setup>
import { __ } from '@wordpress/i18n';
import { ref, onBeforeMount } from 'vue'; 
import { onBeforeRouteLeave  } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
const props = defineProps([
    'title',
    'notifications',
    'total_unread'
     
])
const emit = defineEmits([ "MarkAsRead" ]); 

const displayNotification = ref(false);

// make a vue time 2024-08-20 16:15:39 to 6m example
const timeAgo = (date) => {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) {
        return Math.floor(interval) + " years";
    }
    interval = seconds / 2592000;
    if (interval > 1) {
        return Math.floor(interval) + " months";
    }
    interval = seconds / 86400;
    if (interval > 1) {
        return Math.floor(interval) + " days";
    }
    interval = seconds / 3600;
    if (interval > 1) {
        return Math.floor(interval) + " hours";
    }
    interval = seconds / 60;
    if (interval > 1) {
        return Math.floor(interval) + " minutes";
    }
    return Math.floor(seconds) + " seconds";
}


const MarkAsRead = () => {
    //  remove the unread class
    document.querySelectorAll('.tfhb-single-notification').forEach((el) => {
        el.classList.remove('unread');
    });
    emit('MarkAsRead')
}

// if click outside the dropdown
 
function hideDropdownOutsideClick(e) {
    if (!document.querySelector('.tfhb-header-notification').contains(e.target)) {
        displayNotification.value = false;
    }
}
onBeforeMount(() => {  
    window.addEventListener('click', hideDropdownOutsideClick); 
}); 
onBeforeRouteLeave((to, from, next) => {
    displayNotification.value = 0;
    window.removeEventListener('click', hideDropdownOutsideClick);
    next();
})
</script>


<template>
    <div class="thb-admin-header">
        <div class="thb-admin-header-icon tfhb-flexbox tfhb-gap-16">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="url(#paint0_radial_5202_17492)"/>
                <path d="M17.4872 24.1299L13.0694 19.712L12.0241 18.6668L11.4109 18.0536L8.99991 15.6426V25.7744H17.4872C17.9889 25.7744 18.4767 25.7047 18.9366 25.5793L17.4872 24.1299Z" fill="white"/>
                <path d="M13.0694 16.3818L8.99991 12.3124V6.25H9.71067L12.5676 9.10698L16.442 12.9953L13.0694 16.3818Z" fill="white"/>
                <path d="M23.1176 19.6575L18.1144 14.6543L17.4872 15.2814L15.5501 17.2186L14.7278 18.0409L17.4733 20.7863L21.1107 24.4238C22.3371 23.3925 23.1176 21.8316 23.1176 20.1035C23.1315 19.9641 23.1315 19.8108 23.1176 19.6575Z" fill="white"/>
                <path d="M22.797 9.97104C22.5183 9.20453 22.0723 8.50771 21.5009 7.93631C20.4835 6.89108 19.0481 6.25 17.4733 6.25H13.0415L14.1424 7.35098L22.0444 15.253C22.7412 14.3192 23.1454 13.1625 23.1454 11.9082C23.1315 11.2253 23.02 10.5703 22.797 9.97104Z" fill="white"/>
                <defs>
                <radialGradient id="paint0_radial_5202_17492" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(16 32) rotate(-90) scale(32)">
                <stop stop-color="#2E6B38"/>
                <stop offset="1" stop-color="#4C9959"/>
                </radialGradient>
                </defs>
            </svg>

            <h2 class="tfhb-admin-header-title">{{ props.title }}</h2>
        </div>
        <div v-if="props.notifications" class="tfhb-header-notification">
            <div class="tfhb-dropdown tfhb-mega-dropdown">
            <span @click="displayNotification = !displayNotification"> 
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.58333 17.4993C8.72282 17.753 8.92787 17.9646 9.17708 18.112C9.42628 18.2594 9.71048 18.3371 10 18.3371C10.2895 18.3371 10.5737 18.2594 10.8229 18.112C11.0721 17.9646 11.2772 17.753 11.4167 17.4993M5 6.66602C5 5.33993 5.52678 4.06816 6.46447 3.13048C7.40215 2.1928 8.67392 1.66602 10 1.66602C11.3261 1.66602 12.5979 2.1928 13.5355 3.13048C14.4732 4.06816 15 5.33993 15 6.66602C15 12.4993 17.5 14.166 17.5 14.166H2.5C2.5 14.166 5 12.4993 5 6.66602Z" stroke="#141915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>    
            </span>

            <span v-if="props.total_unread && props.total_unread != 0" @click="displayNotification = !displayNotification" class="tfhb-header-notification-count">
                {{props.total_unread}}
            </span>

            <transition name="tfhb-dropdown-transition">
                <div v-show="displayNotification" class="tfhb-dropdown-wrap " >   <!-- active class-->
                    <div class="tfhb-flexbox tfhb-justify-between">
                        <h3>{{ $tfhb_trans('Notifications') }}</h3>
                        <button @click="MarkAsRead" class="tfhb-btn">{{ $tfhb_trans('Mark as read') }}</button>
                        <!-- {{ notifications }} -->
                    </div>

                    <div class="tfhb-notification-wrap tfhb-scrollbar">
                        
                        <!-- Single Notifaction wrap -->
                        <div v-for=" notification in props.notifications" :key="notification.id" class="tfhb-single-notification tfhb-flexbox tfhb-gap-16"
                            :class="{ 'unread': notification.value.status == 'unread' }"  
                        > 
                            <div class="tfhb-single-notification-img">
                                <img :src="$tfhb_url+'/assets/images/avator.png'" alt="Notification Image">
                            </div> 
                            <div class="tfhb-single-notification-content"> 
                                <h4>{{notification.value.attendee_name}}</h4>
                                <p>{{ $tfhb_trans(notification.value.message) }}</p>

                            <span class="tfhb-notification-time">{{ timeAgo(notification.created_at) }} ago </span>
                            </div>
                            


                        </div> 
                        <div v-if="props.notifications.length == 0" class="tfhb-flexbox tfhb-gap-8 tfhb-flex-col tfhb-justify-center" style="text-align:center; padding:50px 12px ">
                              <img :src="$tfhb_url+'/assets/images/notification-not-found.svg'" alt="" >
                            <p > {{ $tfhb_trans('No new notifications') }} </p>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
        </div>
    </div>
</template> 

<style scoped>
/* Your component styles go here */
    
</style>

<style scoped>
 


</style>

