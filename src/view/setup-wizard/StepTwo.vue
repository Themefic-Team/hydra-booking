<script setup>
import { __ } from '@wordpress/i18n';
import { ref } from 'vue';
import { RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import { setupWizard } from '@/store/setupWizard';
import useValidators from '@/store/validator'
import AvailabilityPopupSingle from '@/components/availability/AvailabilityPopupSingle.vue';
// Toast
import { toast } from "vue3-toastify"; 
const { errors, isEmpty } = useValidators();

const isModalOpened = ref(true);
const timeZone = ref({});
const availabilityDataSingle = ref({});
const fetchAvailabilitySettingsUpdate = () => {
    // console.log('fetchAvailabilitySettingsUpdate');
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

const StepTwo = (validator_field) => {
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!props.setupWizard.data.availabilityDataSingle[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!props.setupWizard.data.availabilityDataSingle[fieldParts[0]][fieldParts[1]]){
                errors[fieldParts[0]+'___'+[fieldParts[1]]] = 'Required this field';
            }
        }
            
        });
    }

    // Errors Checked
    const isEmpty = Object.keys(errors).length === 0;
    if(!isEmpty){ 
        toast.error('Fill Up The Required Fields', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        }); 
        return
    }
   
    
    if(props.setupWizard.skip_preloader == true){
        return;
    } 

    // props.setupWizard.skip_import = true;
    props.setupWizard.skip_preloader = true;  
    props.setupWizard.importDemoMeeting(); 
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
            <h2>{{ $tfhb_trans('Take control of your schedule') }}</h2>
            <p>{{ $tfhb_trans('Make your appointment booking process even easier by simply setting your availability.') }}</p> 
        </div>
        <div class="tfhb-content-wrap tfhb-s-w-availability-wrap tfhb-flexbox tfhb-gap-tb-24">

           
            <AvailabilityPopupSingle max_width="800px" v-if="isModalOpened" :timeZone="props.setupWizard.time_zone" :display_overwrite="false"  :availabilityDataSingle="props.setupWizard.data.availabilityDataSingle" :isOpen="isModalOpened" @modal-close="closeModal"  @update-availability="fetchAvailabilitySettingsUpdate" />
    
        </div>
        <div class="tfhb-flexbox tfhb-justify-center tfhb-gap-16">
            <div class="tfhb-submission-btn tfhb-flexbox">
                <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8 left" 
                    @click="props.setupWizard.currentStep = 'step-one'" 
                    :buttonText="$tfhb_trans('Back')"
                    icon="ChevronLeft" 
                    hover_icon="ArrowLeft" 
                    :hover_animation="true"
                    icon_position="left" 
                /> 
                <HbButton 
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="StepTwo(['title', 'time_zone'])" 
                    :buttonText="$tfhb_trans('Next')"
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true" 
                    :pre_loader="props.setupWizard.skip_preloader"
                /> 
            
                <!-- <button @click="props.setupWizard.currentStep = 'step-one'" class="tfhb-btn tfhb-btn tfhb-flexbox tfhb-gap-8" >Skip<Icon name="ChevronRight" size=20 />  </button> -->
            </div>
                
            <HbButton 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-p-0" 
                @click="props.setupWizard.currentStep = 'step-three';" 
                :buttonText="$tfhb_trans('Skip')"   
            
            />  
        </div>
     
            
     </div>
     <!-- Step Two -->

</template>
 