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
        <Header title="Meetings" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" />
        <div class="tfhb-hydra-wrap ">    
            <nav class="tfhb-hydra-admin-tabs tfhb-hydra-settings">  
                <ul>
                    <li><router-link to="/settings/general" exact :class="{ 'active': $route.path === '/settings/general' }"> <Icon name="SlidersHorizontal" /> {{ __('General', 'hydra-booking') }}</router-link></li> 
                   
                    <li><router-link to="/settings/availability" :class="{ 'active': $route.path === '/settings/availability' }"> <Icon name="Clock" /> {{ __('Availability', 'hydra-booking') }}</router-link></li> 
                    <li><router-link to="/settings/notifications" :class="{ 'active': $route.path === '/settings/notifications' }"> <Icon name="BellDot" /> {{ __('Notifications', 'hydra-booking') }}</router-link></li>
                    <li  :class="{ 'expand': $route.path === '/settings/integrations' }" class="tfhb-integrations-settings-menu"><router-link to="/settings/integrations#all" class="integrations-submenu" data-filter="all" :class="{ 'active': $route.path === '/settings/integrations' }"> <Icon name="Unplug" /> {{ __('Integrations', 'hydra-booking') }}
                       <span class="setings-taps-dropdown-arrow"> <Icon name="ChevronDown" size=20 /> </span>
                    </router-link>
                        <ul class="dropdown">
                            <li><router-link to="/settings/integrations#all" :class="{ 'active': $route.hash === '#all' }" class="integrations-submenu" data-filter="all"> <Icon name="GalleryVerticalEnd" /> {{ __('All', 'hydra-booking') }}</router-link></li>
                            
                            <li><router-link to="/settings/integrations#conference" :class="{ 'active': $route.hash === '#conference' }" class="integrations-submenu" data-filter="conference"> <Icon name="Video" /> {{ __('Conference', 'hydra-booking') }}</router-link></li>

                            <li><router-link to="/settings/integrations#calendars" :class="{ 'active': $route.hash === '#calendars' }" class="integrations-submenu" data-filter="calendars"> <Icon name="CalendarDays" /> {{ __('Calendars', 'hydra-booking') }}</router-link></li>

                            <li><router-link to="/settings/integrations#payments" :class="{ 'active': $route.hash === '#payments' }" class="integrations-submenu" data-filter="payments"> <Icon name="HandCoins" /> {{ __('Payments', 'hydra-booking') }}</router-link></li> 

                            <li><router-link to="/settings/integrations#marketing-tools" :class="{ 'active': $route.hash === '#marketing-tools' }" class="integrations-submenu" data-filter="marketing-tools"> <Icon name="BadgePercent" /> {{ __('Marketing Tools', 'hydra-booking') }}</router-link></li> 
                            <li><router-link to="/settings/integrations#forms" :class="{ 'active': $route.hash === '#forms' }" class="integrations-submenu" data-filter="forms"> <Icon name="BookText" /> {{ __('Forms', 'hydra-booking') }}</router-link></li> 

                        </ul>
                    </li>
                    <li><router-link to="/settings/appearance" :class="{ 'active': $route.path === '/settings/appearance' }"> <Icon name="SwatchBook" /> {{ __('Appearance', 'hydra-booking') }}</router-link></li>

                    <li><router-link to="/settings/category" :class="{ 'active': $route.path === '/settings/category' }"> <Icon name="ClipboardList" /> {{ __('Meeting Category', 'hydra-booking') }}</router-link></li>
                    <li><router-link to="/settings/hosts-settings" exact :class="{ 'active': $route.path.startsWith('/settings/hosts-settings') }"> <Icon name="UserCog" /> {{ __('Host Settings', 'hydra-booking') }}</router-link></li> 
                    <li><router-link to="/settings/license" exact :class="{ 'active': $route.path.startsWith('/settings/license') }"> <Icon name="FileLock2" /> {{ __('License', 'hydra-booking') }}</router-link></li> 
                </ul>  
            </nav>  
            <div class="tfhb-hydra-dasboard-content"> 
                <router-view />
                
            </div> 
            
          
            </div>
    </div>
</template>
 