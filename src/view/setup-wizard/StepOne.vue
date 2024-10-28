<script setup>
import { __ } from '@wordpress/i18n';
import { RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { setupWizard } from '@/store/setupWizard';

// Toast
import { toast } from "vue3-toastify"; 

const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 

const StepOne = () => {
    if(props.setupWizard.data.business_type == ''){
        toast.error('Please select your business type');
        return;

    }
    
    props.setupWizard.currentStep = 'step-two';
}


</script>

<template>
    <!-- Step One -->
    
    <div class="tfhb-setup-wizard-content-wrap tfhb-s-w-step-one tfhb-flexbox">
        
        <div class="tfhb-s-w-icon-text">
            <div class="tfhb-step-wizard-steper tfhb-flexbox tfhb-gap-16" >
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 "></span>
                <span class="tfhb-step-bar step-1 "></span>
                <span class="tfhb-step-bar step-1 "></span>
            </div>
            <h2>{{ __('Simplify Your Work with HydraBooking', 'hydra-booking') }}</h2>
            <p>{{ __('Our intuitive setup process makes HydraBooking a breeze to use, even for non-technical users.', 'hydra-booking') }}</p>
        </div>
        <div class="tfhb-s-w-getting-email">

           
             <HbDropdown 
                    
                    v-model="props.setupWizard.data.business_type"  
                    required= "true"   
                    :label="__('Your business type?', 'hydra-booking')"  
                    selected = "1"
                    placeholder="Select Your Business Type"  
                    :option = "[
                        {'name': 'Consultant', 'value': 'Consultant'}, 
                        {'name': 'Doctor', 'value': 'Doctor'}, 
                        {'name': 'Teacher', 'value': 'Teacher'},
                        {'name': 'Others', 'value': 'Others'}
                    ]"
                    
                />
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox"> 
            <HbButton 
                classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation left" 
                @click="props.setupWizard.currentStep = 'getting-start'" 
                :buttonText="__('Back', 'hydra-booking')"
                icon="ChevronLeft" 
                hover_icon="ArrowLeft" 
                :hover_animation="true"
                icon_position="left"
                width="84px"
            /> 
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation" 
                @click="StepOne" 
                :buttonText="__('Next', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                width="84px"
            />  
            <HbButton 
                classValue="tfhb-btn  secondary-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation" 
                @click="props.setupWizard.currentStep = 'step-end'" 
                :buttonText="__('Skip', 'hydra-booking')"  
                :hover_animation="true" 
                icon="ChevronRight"   
                width="80px"
            />   
        </div>
     </div> 
     <!-- Step One -->
</template>
 