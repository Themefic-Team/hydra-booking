<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router'  
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';


const informationPopup = ref(false);
const emit = defineEmits(["update-meeting", "limits-frequency-add"]); 
const props = defineProps({
    hostsSettings : {
        type: Object,
        required: true
    }
}); 
 
 

// Extra Qestion Data
const questions_data =  reactive({});
 
</script>

<template>  
        <div class="tfhb-admin-card-box tfhb-flexbox  tfhb-gap-16  tfhb-m-0"  >   

            <HbCheckbox 
                v-model="hostsSettings.permission.tfhb_manage_dashboard"
                :label="__('Allow host to manage the dashboard.', 'hydra-booking')"
                name="tfhb_manage_dashboard"
            /> 
            <HbCheckbox 
                v-model="hostsSettings.permission.tfhb_manage_meetings"
                :label="__('Allow host to create and manage meetings.', 'hydra-booking')"
                name="tfhb_manage_meetings"
            /> 
            <HbCheckbox 
                v-model="hostsSettings.permission.tfhb_manage_booking"
                :label="__('Allow host to manage bookings and reservations.', 'hydra-booking')"
                name="tfhb_manage_booking"
            /> 
            <HbCheckbox 
                v-model="hostsSettings.permission.tfhb_manage_settings"
                :label="__('Allow host to adjust system and personal settings.', 'hydra-booking')"
                name="tfhb_manage_settings"
            /> 
            <HbCheckbox 
                v-model="hostsSettings.permission.tfhb_manage_custom_availability"
                :label="__('Allow host to manage custom availability settings.', 'hydra-booking')"
                name="tfhb_manage_custom_availability"
            /> 
            <HbCheckbox 
                v-model="hostsSettings.permission.tfhb_manage_integrations"
                :label="__('Allow host to manage integrations and external services.', 'hydra-booking')"
                name="tfhb_manage_integrations"
            /> 
        </div>
        
</template>

<style scoped>
</style> 