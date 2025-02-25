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
    //  hostsSettings.fetchHostsSettings();
});
 
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': false }" class="thb-host-dashboard "> 
        <div  class="tfhb-dashboard-heading tfhb-mb-16">
            <div class="tfhb-admin-title "> 
                <h1 >{{ $tfhb_trans('Host Settings') }}</h1> 
                <p>{{ $tfhb_trans('Manage the settings and preferences for the hosts') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div>
        <div class="tfhb-content-wrap">
             <!-- {{ tfhbClass }} --> 
             <nav class="tfhb-booking-tabs"> 
                <ul>
                    <!-- to route example like hosts/profile/13/information -->
                    <li><router-link to="/settings/shortcodes/meetings" exact :class="{ 'active': $route.path === '/settings/shortcodes/meetings' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Meetings') }}</router-link></li>  
                    <li><router-link to="/settings/shortcodes/hosts" exact :class="{ 'active': $route.path === '/settings/shortcodes/hosts' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Hosts') }}</router-link></li>  
                    <li><router-link to="/settings/shortcodes/meeting-categories" exact :class="{ 'active': $route.path === '/settings/shortcodes/meeting-categories' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Meeting Categories') }}</router-link></li>  
                    
                </ul>  
            </nav>
            <div class="tfhb-hydra-content-wrap">      
             
                <router-view />

                <div class="tfhb-submission-btn tfhb-mt-16"> 
                </div> 

            </div> 

        </div>
    </div>
 
</template>