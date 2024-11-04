<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { onBeforeMount } from 'vue';
import { RouterView } from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'

// Store 
import { hostsSettings } from '@/store/settings/hostsSettings';

 
onBeforeMount(() => {   
     hostsSettings.fetchHostsSettings();
});
const updateHosts = async () => {
     hostsSettings.updateHostsSettings();
}
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': hostsSettings.skeleton }" class="thb-host-dashboard "> 
        <div  class="tfhb-dashboard-heading tfhb-mb-16">
            <div class="tfhb-admin-title "> 
                <h1 >{{ __('Host Settings', 'hydra-booking') }}</h1> 
                <p>{{ __('Manage the settings and preferences for the hosts', 'hydra-booking') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="#" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ __('View Documentation', 'hydra-booking') }}<Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div>
        <div class="tfhb-content-wrap">
             <!-- {{ tfhbClass }} --> 
             <nav class="tfhb-booking-tabs"> 
                <ul>
                    <!-- to route example like hosts/profile/13/information -->
                    <li><router-link to="/settings/hosts-settings/information-builder" exact :class="{ 'active': $route.path === '/settings/hosts-settings/information-builder' }"> <Icon name="UserRound" /> {{ __('Information Builder', 'hydra-booking') }}</router-link></li> 
                    <li><router-link to="/settings/hosts-settings/permission" exact :class="{ 'active': $route.path === '/settings/hosts-settings/permission' }"> <Icon name="KeyRound" /> {{ __('Permission', 'hydra-booking') }}</router-link></li> 
                    
                </ul>  
            </nav>
            <div class="tfhb-hydra-content-wrap">      
             
                <router-view :hostsSettings="hostsSettings.settings" />

                <div class="tfhb-submission-btn tfhb-mt-16">
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="updateHosts" 
                        :buttonText="__('Update Host Settings', 'hydra-booking')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"
                        :pre_loader="hostsSettings.update_preloader"
                    />  
                </div> 

            </div> 

        </div>
    </div>
 
</template>