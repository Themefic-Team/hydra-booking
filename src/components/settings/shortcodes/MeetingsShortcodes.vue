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
                <h1 >{{ $tfhb_trans('Meetings Shortcode') }}</h1> 
                <p>{{ $tfhb_trans('Manage the settings and preferences for the hosts') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div> 
    </div>
 
</template>