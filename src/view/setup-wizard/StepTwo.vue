<script setup>
import { ref } from 'vue';
import { RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import { setupWizard } from '@/store/setupWizard';
import AvailabilityPopupSingle from '@/components/availability/AvailabilityPopupSingle.vue';
// Toast
import { toast } from "vue3-toastify"; 


const isModalOpened = ref(true);
const timeZone = ref({});
const availabilityDataSingle = ref({});
const fetchAvailabilitySettingsUpdate = () => {
    console.log('fetchAvailabilitySettingsUpdate');
}
const closeModal = () => {
    isModalOpened.value = false;
}
const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 

const StepTwo = () => {
    if(props.setupWizard.data.availabilityDataSingle.title == ''){ 
        toast.error('Title is required');
        return;

    }
    if(props.setupWizard.data.availabilityDataSingle.time_zone == ''){ 
        toast.error('Timezone is required');
        return;

    }
    
    props.setupWizard.currentStep = 'step-three';
}


</script>

<template>

     <!-- Step Two -->
     <div class="tfhb-setup-wizard-content-wrap tfhb-s-w-step-two tfhb-flexbox">
      
        <div class="tfhb-s-w-icon-text">
            <div class="tfhb-step-wizard-steper tfhb-flexbox tfhb-gap-16" >
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 "></span>
                <span class="tfhb-step-bar step-1 "></span>
            </div>
            <h2>{{$tfhb_trans['Take control of your schedule']}}</h2>
            <p>{{$tfhb_trans['You can streamline your appointment booking process by using your availability.']}}</p> 
        </div>
        <div class="tfhb-content-wrap tfhb-s-w-availability-wrap tfhb-flexbox tfhb-gap-tb-24">

           
            <AvailabilityPopupSingle max_width="1000px" v-if="isModalOpened" :timeZone="props.setupWizard.time_zone" :availabilityDataSingle="props.setupWizard.data.availabilityDataSingle" :isOpen="isModalOpened" @modal-close="closeModal"  @update-availability="fetchAvailabilitySettingsUpdate" />
    
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox">
            <HbButton 
                classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8 icon-left" 
                @click="props.setupWizard.currentStep = 'step-one'" 
                :buttonText="$tfhb_trans['Back']"
                icon="ChevronLeft" 
                hover_icon="ArrowLeft" 
                :hover_animation="true"
                icon_position="left"
            /> 
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                @click="StepTwo" 
                :buttonText="$tfhb_trans['Next']"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
            /> 
            
            <!-- <button @click="props.setupWizard.currentStep = 'step-one'" class="tfhb-btn tfhb-btn tfhb-flexbox tfhb-gap-8" >Skip<Icon name="ChevronRight" size="20" />  </button> -->
        </div>
     </div>
     <!-- Step Two -->

</template>
 