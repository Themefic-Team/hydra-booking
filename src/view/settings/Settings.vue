<script setup>
import { __ } from '@wordpress/i18n';
import { ref, onMounted  } from 'vue';
import { RouterView } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue';
import { Notification } from '@/store/notification';
import Header from '@/components/Header.vue';

onMounted(() => { 
    Notification.fetchNotifications();
}); 
</script>

<template>
    <!-- {{ tfhbClass }} -->
    <div class="tfhb-hydra-dasboard">
        <Header v-if="$front_end_dashboard == false"  :title="$tfhb_trans('Settings')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" />
        <div class="tfhb-hydra-wrap ">    
            <nav v-if="$front_end_dashboard == false"  class="tfhb-hydra-admin-tabs tfhb-hydra-settings">  
                <ul>
                    <li><router-link to="/settings/general" exact :class="{ 'active': $route.path === '/settings/general' }"> <Icon name="SlidersHorizontal" /> {{ $tfhb_trans('General') }}</router-link></li> 
                   
                    <li><router-link to="/settings/availability" :class="{ 'active': $route.path === '/settings/availability' }"> <Icon name="Clock" /> {{ $tfhb_trans('Availability') }}</router-link></li> 

                    <li  :class="{ 'expand': $route.path === '/settings/notifications' }" class="tfhb-notification-settings-menu"><router-link to="/settings/notifications#email" class="notification-submenu" data-filter="email" :class="{ 'active': $route.path.includes('notifications') }"> <Icon name="BellDot" /> {{ $tfhb_trans('Notifications') }}
                       <span class="setings-taps-dropdown-arrow"> <Icon name="ChevronDown" size=20 /> </span>
                    </router-link>
                        <ul class="dropdown" :style="$route.fullPath.includes('email') || $route.fullPath.includes('host') || $route.fullPath.includes('attendee') || $route.fullPath.includes('telegram') || $route.fullPath.includes('twilio') || $route.fullPath.includes('slack') ? {display: 'block !important', visibility: 'visible !important'} : ''">
                            <li><router-link to="/settings/notifications#email" :class="{ 'active': $route.fullPath.includes('email') || $route.fullPath.includes('host') || $route.fullPath.includes('attendee') }" class="notification-submenu" data-filter="email"> <Icon name="Mail" size=18 /> {{ $tfhb_trans('Email') }}</router-link></li>
                            
                            <li><router-link to="/settings/notifications#telegram" :class="{ 'active': $route.fullPath.includes('telegram') }" class="notification-submenu" data-filter="telegram"> <img :src="$tfhb_url+'/assets/images/Telegram.svg'" alt=""> {{ $tfhb_trans('Telegram') }}</router-link></li>

                            <li><router-link to="/settings/notifications#twilio" :class="{ 'active': $route.fullPath.includes('twilio') }" class="notification-submenu" data-filter="twilio"> <img :src="$tfhb_url+'/assets/images/Twilio.svg'" alt=""> {{ $tfhb_trans('Twilio') }}</router-link></li>

                            <li><router-link to="/settings/notifications#slack" :class="{ 'active': $route.fullPath.includes('slack') }" class="notification-submenu" data-filter="slack"> <img :src="$tfhb_url+'/assets/images/Slack.svg'" alt=""> {{ $tfhb_trans('Slack') }}</router-link></li>

                        </ul>
                    </li>
                 
                    <li  :class="{ 'expand': $route.path === '/settings/integrations' }" class="tfhb-integrations-settings-menu"><router-link to="/settings/integrations#all" class="integrations-submenu" data-filter="all" :class="{ 'active': $route.path === '/settings/integrations' }"> <Icon name="Unplug" /> {{ $tfhb_trans('Integrations') }}
                       <span class="setings-taps-dropdown-arrow"> <Icon name="ChevronDown" size=20 /> </span>
                    </router-link>
                        <ul class="dropdown">
                            <li><router-link to="/settings/integrations#all" :class="{ 'active': $route.hash === '#all' }" class="integrations-submenu" data-filter="all"> <Icon name="GalleryVerticalEnd" size=18 /> {{ $tfhb_trans('All') }}</router-link></li>
                            
                            <li><router-link to="/settings/integrations#conference" :class="{ 'active': $route.hash === '#conference' }" class="integrations-submenu" data-filter="conference"> <Icon name="Video" size=18 /> {{ $tfhb_trans('Conference') }}</router-link></li>

                            <li><router-link to="/settings/integrations#calendars" :class="{ 'active': $route.hash === '#calendars' }" class="integrations-submenu" data-filter="calendars"> <Icon name="CalendarDays" size=18 /> {{ $tfhb_trans('Calendars') }}</router-link></li>

                            <li><router-link to="/settings/integrations#payments" :class="{ 'active': $route.hash === '#payments' }" class="integrations-submenu" data-filter="payments"> <Icon name="HandCoins" size=18 /> {{ $tfhb_trans('Payments') }}</router-link></li> 

                            <li><router-link to="/settings/integrations#marketing-tools" :class="{ 'active': $route.hash === '#marketing-tools' }" class="integrations-submenu" data-filter="marketing-tools"> <Icon name="BadgePercent" size=18 /> {{ $tfhb_trans('Marketing Tools') }}</router-link></li> 
                            <li><router-link to="/settings/integrations#forms" :class="{ 'active': $route.hash === '#forms' }" class="integrations-submenu" data-filter="forms"> <Icon name="BookText" size=18 /> {{ $tfhb_trans('Forms') }}</router-link></li> 

                        </ul>
                    </li>
                    <li><router-link to="/settings/appearance" :class="{ 'active': $route.path === '/settings/appearance' }"> <Icon name="SwatchBook" /> {{ $tfhb_trans('Appearance') }}</router-link></li>

                    <li><router-link to="/settings/category" :class="{ 'active': $route.path === '/settings/category' }"> <Icon name="ClipboardList" /> {{ $tfhb_trans('Meeting Category') }}</router-link></li>
                    <li><router-link to="/settings/import-export" :class="{ 'active': $route.path === '/settings/import-export' }"> <Icon name="Import" /> {{ $tfhb_trans('Import/Export') }}</router-link></li>

                    <li><router-link to="/settings/hosts-settings" exact :class="{ 'active': $route.path.startsWith('/settings/hosts-settings') }"> <Icon name="UserCog" /> {{ $tfhb_trans('Host Settings') }}</router-link></li>  

                    <li><router-link to="/settings/shortcodes" exact :class="{ 'active': $route.path.startsWith('/settings/shortcodes') }"> <Icon name="Braces" /> {{ $tfhb_trans('Shortcodes') }}</router-link></li>  

                    <li><router-link to="/settings/fd-dashboard" exact :class="{ 'active': $route.path.startsWith('/settings/fd-dashboard') }"> <Icon name="LayoutDashboard" /> {{ $tfhb_trans('Frontend Dashboard') }}</router-link></li> 
 
                    <!-- Frontend Dashbaord -->
                    <li><router-link to="/settings/license" exact :class="{ 'active': $route.path.startsWith('/settings/license') }"> <Icon name="FileLock2" /> {{ $tfhb_trans('License') }}</router-link></li> 
                </ul>  
            </nav>  
            <div class="tfhb-hydra-dasboard-content"> 
                <router-view />
                
            </div> 
            
          
        </div>
    </div>
</template>
 