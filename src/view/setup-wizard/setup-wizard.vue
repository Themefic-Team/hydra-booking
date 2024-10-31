<script setup>
import { ref, reactive, onBeforeMount } from 'vue'; 
import { setupWizard } from '@/store/setupWizard';

// Component
import GettingStart from './GetingStart.vue';
import StepOne from './StepOne.vue';
import StepTwo from './StepTwo.vue';
import StepThree from './StepThree.vue';
import StepFour from './StepFour.vue';
import StepEnd from './StepEnd.vue';


// add class into body
// route is #/setup-wizard add class into body
onBeforeMount(() => { 
    setupWizard.fetchSetupWizard();  
   
});
 

 
</script>

<template> 
   <div class="tfhb-setup-wizard tfhb-flexbox tfhb-hydra-wrap" 
        :style="'background-image: url(' + $tfhb_url+'/assets/images/setup-wizard.jpg' + ')'" 
        alt=""
    >
    
     <div class="tfhb-setup-wizard-header tfhb-flexbox tfhb-justify-between">
        <div class="tfhb-s-w-logo">
            <img :src="$tfhb_url+'/assets/images/hydra-booking-logo.png'" alt="">
        </div>
        <div class="tfhb-s-w-cta tfhb-flexbox tfhb-gap-16">
            <a href="#">Need Help</a>
            <span></span>
            <a href="#">FAQ</a>
        </div>
     </div>

     <!-- Getting Start Component -->
     <transition name="tfhb-fade-right">
     <GettingStart 
        v-if="setupWizard.currentStep  == 'getting-start'"
        :setupWizard="setupWizard"

     />
    </transition>
     <!-- Step One -->
     <transition name="tfhb-fade-right">
     <StepOne 
        v-if="setupWizard.currentStep  == 'step-one'"
        :setupWizard="setupWizard"
    />
    </transition>

    <!-- // Step two -->
    <transition name="tfhb-fade-right">
    <StepTwo 
        v-if="setupWizard.currentStep  == 'step-two'"
        :setupWizard="setupWizard"
     />
    </transition>
     <!-- Step Three -->
     <transition name="tfhb-fade-right">
    <StepThree 
        v-if="setupWizard.currentStep  == 'step-three'"
        :setupWizard="setupWizard"
    />
    </transition>
    <!-- Step Four -->
    <transition name="tfhb-fade-right"> 
    <StepFour 
        v-if="setupWizard.currentStep  == 'step-four'"
        :setupWizard="setupWizard"
    />
    </transition>

    <!-- Step End -->
    <transition name="tfhb-fade-right"> 
    <StepEnd 
        v-if="setupWizard.currentStep  == 'step-end'"
        :setupWizard="setupWizard"
    />
</transition>
   </div>
</template>
 