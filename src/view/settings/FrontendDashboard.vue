<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { onBeforeMount } from 'vue';
import { RouterView } from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import FrontendDashboard from '@/store/settings/fd-dashboard.js' 
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import FdDashboardGeneral from '@/components/settings/fd-dashboard/FdDashboardGeneral.vue';
import FdDashboardSignup from '@/components/settings/fd-dashboard/FdDashboardSignup.vue';
import FdDashboardlogin from '@/components/settings/fd-dashboard/FdDashboardlogin.vue';

import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 

// Get Current Route url
onBeforeMount(() => { 
    FrontendDashboard.fetchFrontendDashboardSettings(); 
});

 
 
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': FrontendDashboard.skeleton }" class="thb-host-dashboard "> 
        <div  class="tfhb-dashboard-heading tfhb-mb-16">
         
            <div class="tfhb-admin-title "> 
                <h1 class="tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Frontend Dashboard') }} 
                    <HbSwitch 
                        v-if="$tfhb_is_valid == true"
                        v-model="FrontendDashboard.fd_dashboard.general.enable_fd_dashboard" 
                        :label="''"  
                        @change="FrontendDashboard.updateFrontendDashboardSettings()" 
                    />
                </h1> 
                <p>{{ $tfhb_trans('Manage the settings and preferences for the frontend dashboard') }} </p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div> 
        <HbInfoBox :isblocked="true" :btntext="$tfhb_trans('Create a Free License Key')" v-if="$tfhb_is_valid != true">
            <template #content>
                {{ $tfhb_trans('The Frontend Dashboard feature requires a free license key to activate.') }} 
            </template>
        </HbInfoBox>

        <div class="tfhb-content-wrap" :class="$tfhb_is_valid != true  ? 'tfhb-pro' : ''">    
            
            <div class="tfhb-hydra-content-wrap">       
                <!-- <router-view 
                :FrontendDashboard="FrontendDashboard"
                /> -->
                <FdDashboardGeneral :FrontendDashboard="FrontendDashboard"   /> 
                <FdDashboardSignup :FrontendDashboard="FrontendDashboard"   /> 
                <FdDashboardlogin :FrontendDashboard="FrontendDashboard"   /> 

                <div class="tfhb-submission-btn tfhb-mt-16">
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click=" FrontendDashboard.updateFrontendDashboardSettings()" 
                        :buttonText="$tfhb_trans('Update Host Settings')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"
                        :pre_loader="FrontendDashboard.update_preloader"
                    />  
                </div> 

            </div> 

        </div>
    </div>
 
</template>