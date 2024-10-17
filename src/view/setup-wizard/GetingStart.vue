<script setup>
import { __ } from '@wordpress/i18n';
import { RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import { setupWizard } from '@/store/setupWizard';
import HbButton from '@/components/form-fields/HbButton.vue'

// Toast
import { toast } from "vue3-toastify"; 

const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 

const GettingStart = () => {
    if(props.setupWizard.data.email == ''){
        toast.error('Email is required');
        return;

    }
    
    props.setupWizard.currentStep = 'step-one';
}

</script>

<template>
   
    <!-- Getting Start -->
    <div class="tfhb-setup-wizard-content-wrap tfhb-s-w-geting-start tfhb-flexbox">
        <div class="tfhb-s-w-icon-text">
            <img :src="$tfhb_url+'/assets/images/hydra-booking-logo.png'" alt="">
            <h2>{{ __('Welcome to HydraBooking!', 'hydra-booking') }}</h2>
            <p>{{ __('Thank you for choosing HydraBooking, the premier solution for effortless appointment and booking scheduling', 'hydra-booking') }}</p>
        </div>
        <div class="tfhb-s-w-getting-email">

             <!-- Custom Duration -->
             <HbText  
                v-model="props.setupWizard.data.email"  
                :label="__('Email', 'hydra-booking')"  
                name="title"
                type="email"
                selected = "1"
                :placeholder="__('Enter your email', 'hydra-booking')"  
            /> 
             <!-- Custom Duration -->
            <HbCheckbox 
                v-model="props.setupWizard.data.enable_recevie_updates"  
                type="checkbox" 
                required= "true"  
                :label="__('Receive updates and promotions', 'hydra-booking')"  
            />
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox">
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="GettingStart" 
                :buttonText="__('Get Started in a Minute', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
            />   
 
        </div>
     </div>
     <!-- Getting Start -->
</template>
 