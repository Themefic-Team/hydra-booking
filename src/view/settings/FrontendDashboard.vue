<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { onBeforeMount } from 'vue';
import { RouterView } from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'

 
 
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': false }" class="thb-host-dashboard "> 
        <div  class="tfhb-dashboard-heading tfhb-mb-16">
            <div class="tfhb-admin-title "> 
                <h1 >{{ $tfhb_trans('Frontend Dashboard Settings') }}</h1> 
                <p>{{ $tfhb_trans('Manage the settings and preferences for the frontend dashboard') }} </p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div>
        <div class="tfhb-content-wrap"> 
            
             <nav class="tfhb-booking-tabs"> 
                <ul>
                    <!-- to route example like hosts/profile/13/information -->
                    <li><router-link to="/settings/fd-dashboard/general" exact :class="{ 'active': $route.path === '/settings/fd-dashboard/general' }"> <Icon name="SlidersHorizontal" /> {{ $tfhb_trans('General') }}</router-link></li>
                    <li><router-link to="/settings/fd-dashboard/signup" exact :class="{ 'active': $route.path === '/settings/fd-dashboard/signup' }"> <Icon name="CircleUser" /> {{ $tfhb_trans('Signup') }}</router-link></li>
                    <li><router-link to="/settings/fd-dashboard/login" exact :class="{ 'active': $route.path === '/settings/fd-dashboard/login' }"> <Icon name="LogIn" /> {{ $tfhb_trans('LogIn') }}</router-link></li>
                    
                </ul>  
            </nav>
            <div class="tfhb-hydra-content-wrap">      
             
                <router-view  />

                <div class="tfhb-submission-btn tfhb-mt-16">
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="alert(1)" 
                        :buttonText="$tfhb_trans('Update Host Settings')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"
                        :pre_loader="false"
                    />  
                </div> 

            </div> 

        </div>
    </div>
 
</template>