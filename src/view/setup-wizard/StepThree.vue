<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
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

const pre_loader = ref(false);
const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 


const StepThree = () => {
    
    if(props.setupWizard.pre_loader == true){
        return;
    }
    props.setupWizard.pre_loader = true;
   


    props.setupWizard.importDemoMeeting(); 
}

 
const sikpStepThree = () => { 
    if(props.setupWizard.skip_preloader == true){
        return;
    } 
    props.setupWizard.skip_import = true;
    props.setupWizard.skip_preloader = true; 
    props.setupWizard.importDemoMeeting();
}
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
            
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-import-demo" 
                @click="StepThree"
                :buttonText="__('Create demo meeting', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :pre_loader="props.setupWizard.pre_loader"
                :hover_animation="true" 
            />  
             
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox"> 
            <HbButton 
                classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8 left" 
                @click="props.setupWizard.currentStep = 'step-two'" 
                :buttonText="__('Back', 'hydra-booking')"
                icon="ChevronLeft"  
                hover_icon="ArrowLeft" 
                :hover_animation="true" 
                icon_position="left" 
            /> 
            <HbButton 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 " 
                @click="sikpStepThree" 
                :buttonText="__('Skip', 'hydra-booking')"  
                :hover_animation="true"  
                :pre_loader="props.setupWizard.skip_preloader"
                icon="ChevronRight"  
                hover_icon="ArrowRight"  
            />  
        </div>
     </div>
     <!-- Step Three -->

</template>
 