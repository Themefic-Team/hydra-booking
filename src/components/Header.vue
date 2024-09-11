<script setup>
import Icon from '@/components/icon/LucideIcon.vue'
const props = defineProps([
    'title',
    'notifications'
     
])

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

</script>


<template>
    <div class="thb-admin-header">
        <div class="thb-admin-header-icon tfhb-flexbox tfhb-gap-16">
            <svg width="40" height="40" viewBox="0 0 128 128" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="128" height="128" rx="32" fill="url(#paint0_radial_1_31)"/>
                <path d="M69.9489 96.522L52.2774 78.8506L48.0965 74.6696L45.6437 72.2168L35.9996 62.5728V103.1H69.9489C71.9557 103.1 73.9068 102.821 75.7465 102.32L69.9489 96.522Z" fill="white"/>
                <path d="M52.2774 65.5273L35.9996 49.2495V25H38.8427L50.2706 36.4279L65.7679 51.981L52.2774 65.5273Z" fill="white"/>
                <path d="M92.4703 78.6275L72.4576 58.6147L69.949 61.1233L62.2003 68.872L58.9113 72.161L69.8933 83.1429L84.4429 97.6926C89.3486 93.5674 92.4703 87.3239 92.4703 80.4114C92.5261 79.8539 92.5261 79.2407 92.4703 78.6275Z" fill="white"/>
                <path d="M91.188 39.8842C90.0731 36.8181 88.2892 34.0308 86.0036 31.7453C81.9342 27.5643 76.1923 25 69.8931 25H52.1659L56.5698 29.4039L88.1777 61.0119C90.965 57.2769 92.5816 52.65 92.5816 47.6328C92.5259 44.9013 92.0799 42.2812 91.188 39.8842Z" fill="white"/>
                <defs>
                <radialGradient id="paint0_radial_1_31" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(64 128) rotate(-90) scale(128)">
                <stop stop-color="#FA056F"/>
                <stop offset="1" stop-color="#ED85B2"/>
                </radialGradient>
                </defs>
            </svg> 
            <h2 class="tfhb-admin-header-title">{{ props.title }}</h2>
        </div>
        <div v-if="props.notifications" class="tfhb-header-notification">
            <div class="tfhb-dropdown tfhb-mega-dropdown">
            <span> <Icon name="Bell" size="24" /> </span>
           
            <div class="tfhb-dropdown-wrap ">   <!-- active class-->
                <div class="tfhb-flexbox">
                    <h3>{{ $tfhb_trans('Notifications') }}</h3>
                    <!-- <a href="#" class="tfhb-btn">{{ $tfhb_trans('Mark as read') }}</a> -->
                    <!-- {{ notifications }} -->
                </div>

                <div class="tfhb-notification-wrap">
                    
                    <!-- Single Notifaction wrap -->
                    <div v-for=" notification in props.notifications" :key="notification.id" class="tfhb-single-notification tfhb-flexbox tfhb-gap-16">
                        <div class="tfhb-single-notification-img">
                            <img :src="$tfhb_url+'/assets/images/avator.png'" alt="Notification Image">
                        </div> 
                        <div class="tfhb-single-notification-content"> 
                            <h4>{{notification.value.attendee_name}}</h4>
                            <p> {{notification.value.message}}</p>

                        <span class="tfhb-notification-time">{{ timeAgo(notification.created_at) }} ago</span>
                        </div>
                        


                    </div> 
                </div>
            </div>
        </div>
        </div>
    </div>
</template> 

<style scoped>
/* Your component styles go here */
    
</style>

<style scoped>
/* Your component styles go here */
</style>

