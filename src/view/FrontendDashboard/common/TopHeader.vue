<script setup>
import { __ } from '@wordpress/i18n';
import { ref, onBeforeMount, computed,  watch } from 'vue'; 
import { onBeforeRouteLeave, useRoute  } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
import { FdDashboard } from '@/store/frontend-dashboard.js';
import axios from 'axios';

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';

// User Logout function 
async function logoutUser() {
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/logout-user', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            },
            withCredentials: true
        });
        if (response.data.success) {
            window.location.href = response.data.data.login_url;
        }
    } catch (e) {
        // handle error
    }
}
// if click outside the dropdown
 
function hideDropdownOutsideClick(e) {
    // if (!document.querySelector('.tfhb-header-notification').contains(e.target)) {
    //     displayNotification.value = false;
    // }
    if (!document.querySelector('.tfhb-header-profile-dropdown').contains(e.target)) {
        profileDropdown.value = false;
    }
}
onBeforeMount(async () => {
    await AddonsAuth.fetchLoggedInUser();
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
<!-- {{ AddonsAuth.loggedInUser }} -->
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
           
            <div class="tfhb-dropdown tfhb-header-profile-dropdown">
                <div @click.stop="profileDropdown = !profileDropdown"  class="tfhb-flexbox tfhb-gap-8">  
                    <img v-if="AddonsAuth.loggedInUser?.user_data?.avatar" :src="AddonsAuth.loggedInUser.user_data.avatar" alt="Hosts Avatar">
                    <img v-else :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                    <span class="tfhb-profile-name">  
                        <template v-if="AddonsAuth.skeleton">
                            {{ $tfhb_trans('Loading...') }}
                        </template>
                        <template v-else>
                            {{ $tfhb_trans('Hi,') }} 
                             <b v-if="AddonsAuth.loggedInUser?.user_role == 'exhibitors'" >{{ AddonsAuth.loggedInUser?.user_data?.name || 'User' }}</b>
                            <b v-else>{{ AddonsAuth.loggedInUser?.user_data?.name_of_participant || 'User' }}</b>
                        </template>
                    </span>
                    <span  class="tfhb-dropdown-single" >
                        <Icon  v-if="profileDropdown == false" name="ChevronDown" size=16 /> 
                        <Icon v-if="profileDropdown == true" name="ChevronUp" size=16 /> 
                    </span>
                </div>
 
                <transition  name="tfhb-dropdown-transition">
                    <div v-show="profileDropdown == true"  class="tfhb-dropdown-wrap"> 
                        <router-link v-if="AddonsAuth.loggedInUser?.user_role == 'buyers'"  class="tfhb-dropdown-single" to="/buyers/profile" exact >
                                <Icon name="User" size=16 /> 
                                {{ $tfhb_trans('My Account') }}
                            </router-link>
                        <router-link v-if="AddonsAuth.loggedInUser?.user_role == 'sellers'"  class="tfhb-dropdown-single" to="/sellers/profile" exact >
                                <Icon name="User" size=16 /> 
                                {{ $tfhb_trans('My Account') }}
                            </router-link>
                        <router-link v-if="AddonsAuth.loggedInUser?.user_role == 'exhibitors'"  class="tfhb-dropdown-single" to="/exhibitors/profile" exact >
                                <Icon name="User" size=16 /> 
                                {{ $tfhb_trans('My Account') }}
                            </router-link>
                        <span class="tfhb-dropdown-single tfhb-dropdown-error"@click="logoutUser"><Icon name="LogOut" size=16 />{{ $tfhb_trans('Logout') }}</span>
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

