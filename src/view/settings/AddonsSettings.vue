<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { onBeforeMount } from 'vue';
import { RouterView } from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
 
import { AddonsSettings } from '@/store/settings/addons-settings';

// on before mount 
onBeforeMount(() => {
    AddonsSettings.FetchAddonsSettings();
})
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': AddonsSettings.skeleton }" class="thb-host-dashboard "> 
        <div  class="tfhb-dashboard-heading tfhb-mb-16">
            <div class="tfhb-admin-title "> 
                <h1 >{{ $tfhb_trans('Addons Settings') }}</h1> 
                <p>{{ $tfhb_trans('Manage extra features for your meetings') }}</p> 
            </div> 
        </div>
        <div class="tfhb-content-wrap">
             <!-- {{ tfhbClass }} --> 
             <nav class="tfhb-booking-tabs"> 
                <ul>
                    <li><router-link to="/settings/addons-settings/event-settings" exact :class="{ 'active': $route.path === '/settings/shortcodes/event-settings' }"> <Icon name="SlidersHorizontal" /> {{ $tfhb_trans('Event Settings') }}</router-link></li>  
                    
                    <li><router-link to="/settings/addons-settings/sellers" exact :class="{ 'active': $route.path === '/settings/addons-settings/sellers' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Sellers') }}</router-link></li>  
                    <li><router-link to="/settings/addons-settings/buyers" exact :class="{ 'active': $route.path === '/settings/addons-settings/buyers' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Buyers') }}</router-link></li>  
                   
                </ul>  
            </nav>
            <div class="tfhb-hydra-content-wrap">      
             
                <router-view /> 
            </div> 

        </div>
    </div>
 
</template>