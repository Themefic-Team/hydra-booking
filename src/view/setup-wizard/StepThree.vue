<script setup>
import { __ } from '@wordpress/i18n';
import { RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import { setupWizard } from '@/store/setupWizard';

// Toast
import { toast } from "vue3-toastify"; 

const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 


</script>

<template>
 

     <!-- Step Three -->
     <div class="tfhb-setup-wizard-content-wrap tfhb-s-w-step-three tfhb-flexbox">
        
        <div class="tfhb-s-w-icon-text">
            <div class="tfhb-step-wizard-steper tfhb-flexbox tfhb-gap-16" >
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 "></span>
            </div>
            <h2>{{ __('Experience HydraBooking in Action (Instantly!)', 'hydra-booking') }}</h2>
            <p>{{ __('It helps you customize the booking process to fit your workflow and make things run more smoothly.', 'hydra-booking') }}</p>
        </div>
        <div class="tfhb-s-w-import-data">
            <img :src="$tfhb_url+'/assets/images/import.gif'" style="height: 96px;" alt="">
        </div>
        <div class="tfhb-s-w-getting-email">

            <button class="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" @click="props.setupWizard.importDemoMeeting" >Import demo data
               
                <Icon v-if="props.setupWizard.pre_loader == 'false'"  name="ChevronRight" size=20 />   
                <svg class="tfhb-s-w-preloader" v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle fill="none" stroke-opacity="1" stroke="#FFFFFF" stroke-width=".5" cx="100" cy="100" r="0"><animate attributeName="r" calcMode="spline" dur="2" values="1;80" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate><animate attributeName="stroke-width" calcMode="spline" dur="2" values="0;25" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate><animate attributeName="stroke-opacity" calcMode="spline" dur="2" values="1;0" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate></circle></svg>
            
            </button>
             
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox tfhb-gap-8"> 
            <HbButton 
                classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8 icon-left" 
                @click="props.setupWizard.currentStep = 'step-two'" 
                :buttonText="__('Back', 'hydra-booking')"
                icon="ChevronLeft" 
                hover_icon="ArrowLeft" 
                :hover_animation="true" 
                icon_position="left"
            /> 
            <button  @click="props.setupWizard.currentStep = 'step-end'"  class="tfhb-btn  tfhb-flexbox tfhb-gap-8" >Skip </button>
        </div>
     </div>
     <!-- Step Three -->

</template>
 