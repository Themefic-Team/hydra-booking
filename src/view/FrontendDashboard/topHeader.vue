<script setup>
import { __ } from '@wordpress/i18n';
import { ref, onBeforeMount, computed,  watch } from 'vue'; 
import { onBeforeRouteLeave, useRoute  } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
import { FdDashboard } from '@/store/frontend-dashboard.js';
const props = defineProps([
    'title',
    'notifications',
    'userAuth',
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
    if (!document.querySelector('.tfhb-header-profile-dropdown').contains(e.target)) {
        profileDropdown.value = false;
    }
}
onBeforeMount(() => {  
    window.addEventListener('click', hideDropdownOutsideClick); 
}); 
onBeforeRouteLeave((to, from, next) => {
    displayNotification.value = 0;
    window.removeEventListener('click', hideDropdownOutsideClick);
    next();
});

const profileDropdown = ref(false);

const route = useRoute();
const pageTitle = computed(() => {
  if (route.path === '/') {
    return 'Dashboard';
  } 
  else if (route.path.includes('/meetings')) {
    return 'Meetings';
  }
  else if (route.path.includes('/hosts')) {
    return 'Hosts';
  }
  else if (route.path.includes('/settings')) {
    return 'Settings';
  }
  else if (route.path.includes('/profile')) {
    return 'Profile';
  }
  else {
    return '';
  }
});

// Update document title whenever pageTitle changes
watch(pageTitle, (newTitle) => {
  document.title = newTitle;
}, { immediate: true });

// if tfhb-responsive-menu-trigger click add a active class into tfhb-frontend-sidebar  div  using add class not toggle
const toggleSidebar = () => {
    document.querySelector('.tfhb-frontend-sidebar').classList.toggle('responsive-active');
}
</script>


<template>    
    <div :class="{ 'tfhb-skeleton': FdDashboard.skeleton }" class="thb-admin-header tfhb-frontend-top-header">
        <div class="tfhb-flexbox">
            <div class="tfhb-admin-header-icon tfhb-flexbox tfhb-gap-16" >

                <a  class="responsive-header-icon"  v-if="'' != FdDashboard.site_settings.mobile_dashboard_logo"   :href="FdDashboard.site_settings.dashboard_url" >
                    <img :src="FdDashboard.site_settings.mobile_dashboard_logo" :alt="FdDashboard.site_settings.blog_title">
                </a>
                <span class="tfhb-responsive-menu-trigger" @click="toggleSidebar()">
                    <Icon name="Menu" size=20 /> 
                </span>
               
                <a  v-if="'' == FdDashboard.site_settings.dashboard_logo" :href="FdDashboard.site_settings.dashboard_url" class="desktop-header-icon" >{{ FdDashboard.site_settings.blog_title }}</a>
                <a  v-else  :href="FdDashboard.site_settings.dashboard_url" >
                    <img class="desktop-header-icon" :src="FdDashboard.site_settings.dashboard_logo" :alt="FdDashboard.site_settings.blog_title">
                </a>
             

            </div>
            <div class="tfhb-admin-header-icon tfhb-flexbox tfhb-gap-16">

                <h2 class="tfhb-admin-header-title">{{ $tfhb_trans(pageTitle) }}</h2>
            </div>
        </div>
        
        <div  class="tfhb-flexbox tfhb-gap-24 ">
            <div v-if="props.notifications" class="tfhb-header-notification  tfhb-dropdown tfhb-mega-dropdown">
                <span @click="displayNotification = !displayNotification"> <Icon name="Bell" size=24 /> </span>

                <span  v-if="props.total_unread && props.total_unread != 0"  @click="displayNotification = !displayNotification" class="tfhb-header-notification-count">
                    {{props.total_unread}}
                </span>

                <transition name="tfhb-dropdown-transition">
                    <div v-show="displayNotification" class="tfhb-dropdown-wrap " >   <!-- active class-->
                        <div class="tfhb-flexbox tfhb-justify-between">
                            <h3>{{ $tfhb_trans('Notifications') }}</h3>
                            <button @click="MarkAsRead" class="tfhb-btn">{{ $tfhb_trans('Mark as read') }}</button>
                            <!-- {{ notifications }} -->
                        </div>

                        <div  v-if="props.notifications.length > 0" class="tfhb-notification-wrap tfhb-scrollbar">
                            
                            <!-- Single Notifaction wrap -->
                            <div v-for=" notification in props.notifications" :key="notification.id" class="tfhb-single-notification tfhb-flexbox tfhb-gap-16"
                                :class="{ 'unread': notification.value.status == 'unread' }"  
                            > 
                                <div class="tfhb-single-notification-img">
                                    <img :src="$tfhb_url+'/assets/images/avator.png'" alt="Notification Image">
                                </div> 
                                <div class="tfhb-single-notification-content"> 
                                    <h4>{{notification.value.attendee_name}}</h4>
                                    <p> {{notification.value.message}}</p>

                                <span class="tfhb-notification-time">{{ timeAgo(notification.created_at) }} ago </span>
                                </div> 
                            </div> 
                        <!-- emti then display on notifcation here --> 
                        </div>
                        <p v-if="props.notifications.length == 0" style="text-align:center; padding:12px"> {{ $tfhb_trans('No notifications found') }} </p>
                    </div>
                </transition>
            </div>
            <div class="tfhb-dropdown tfhb-header-profile-dropdown">
                <div @click.stop="profileDropdown = !profileDropdown"  class="tfhb-flexbox tfhb-gap-8">  
                    <img :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                    <span class="tfhb-profile-name">  {{ $tfhb_trans('Hi,') }} <b>{{props.userAuth.first_name}}</b> </span>
                    <span  class="tfhb-dropdown-single" >
                        <Icon  v-if="profileDropdown == false" name="ChevronDown" size=16 /> 
                        <Icon v-if="profileDropdown == true" name="ChevronUp" size=16 /> 
                    </span>
                </div>
 
                <transition  name="tfhb-dropdown-transition">
                    <div v-show="profileDropdown == true"  class="tfhb-dropdown-wrap"> 
                        <router-link  class="tfhb-dropdown-single" to="/frontend-dashboard/profile" exact >
                                <Icon name="User" size=16 /> 
                                {{ $tfhb_trans('My Account') }}
                            </router-link>
                      
                        <span class="tfhb-dropdown-single tfhb-dropdown-error"@click="FdDashboard.logoutUser"><Icon name="LogOut" size=16 />{{ $tfhb_trans('Logout') }}</span>
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

