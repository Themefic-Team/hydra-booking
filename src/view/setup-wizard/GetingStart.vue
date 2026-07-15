<script setup>
import { __ } from '@wordpress/i18n'; 
import HbText from '@/components/form-fields/HbText.vue' 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
// import { setupWizard } from '@/store/setupWizard';
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
    props.setupWizard.currentStep = 'step-one';
}

</script>

<template>
   
    <!-- Getting Start -->
    <div class="tfhb-setup-wizard-content-wrap tfhb-s-w-geting-start tfhb-flexbox">
        <div class="tfhb-s-w-icon-text">
            <img :src="$tfhb_url+'/assets/images/hydra-booking-logo.png'" alt="">
            <h2>{{ $tfhb_trans('Welcome to HydraBooking!') }}</h2>
            <p>{{ $tfhb_trans('Thank you for choosing HydraBooking, the premier solution for effortless appointment and booking scheduling') }}</p>
        </div>
        <div class="tfhb-s-w-getting-email">
 
             <HbText  
                v-model="props.setupWizard.data.email"  
                :label="$tfhb_trans('Email')"  
                name="title"
                type="email"
                selected = "1"
                :placeholder="$tfhb_trans('Enter your email')"  
            />  
            <div class="tfhb-recevie-updates">
                    <HbCheckbox 
                    v-model="props.setupWizard.data.enable_recevie_updates"  
                    type="checkbox"  
                    :label="$tfhb_trans('Receive updates and promotions')"  
                />
            </div>
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox">
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="GettingStart" 
                :buttonText="$tfhb_trans('Get Started in a Minute')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                icon_position="right"  
            />   
 
        </div>
     </div>
     <!-- Getting Start -->
</template>
 