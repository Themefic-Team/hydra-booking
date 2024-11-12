<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify";
import { LicenseBase } from '@/store/license'; 
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();

import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
// import Form Field   
import HbText from '@/components/form-fields/HbText.vue' 
import HbButton from '@/components/form-fields/HbButton.vue'; 

 

const upgradeToPro = () => {
    // https://themefic.com/docs/hydrabooking redirecto this url in new tab

    window.open('https://hydrabooking.com/#pricing', '_blank');
}

const updateLicense = async (validator_field) => {

      // Clear the errors object
      Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added 
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!LicenseBase[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!LicenseBase[fieldParts[0]][fieldParts[1]]){
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


    LicenseBase.UpdateLicense();
}

</script>
<template>
    
    <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard">
  
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ __('License Management', 'hydra-booking') }}</h1>  
                <p>{{ __('Manage your HydraBooking Pro license', 'hydra-booking') }}</p>
            </div>
            <div class="thb-admin-btn right">  

            </div> 
        </div>
        <div class="tfhb-content-wrap">
           
            <HbInfoBox v-if="$tfhb_is_pro == false"  icon="Lock" name="first-modal">
                
                <template #content>
                    <div  class="tfhb-license-heading  tfhb-flexbox tfhb-full-width tfhb-flexbox-nowrap tfhb-justify-between">
                        <div class="tfhb-admin-title tfhb-m-0"> 
                            <h2 >{{ __('Unlock Advanced Capabilities with HydraBooking Pro !', 'hydra-booking') }}</h2>  
                            <p>{{ __('Please upgrade to get all the advanced features. ', 'hydra-booking') }}</p>
                        </div>
                        
                        <div class="thb-admin-btn right"> 
                            <HbButton 
                                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                                @click="upgradeToPro" 
                                :buttonText="__('Upgrade to Pro', 'hydra-booking')"
                                icon="ChevronRight" 
                                hover_icon="ArrowRight" 
                                :hover_animation="true" 
                            />  
                        </div> 
                    </div> 
                </template>
            </HbInfoBox> 

            {{ LicenseBase }}
            <!-- Date And Time --> 
            <div  v-if="$tfhb_is_pro == true"  class="tfhb-admin-title" >
                <h2>{{ __(' License Info', 'hydra-booking') }}</h2> 
                <p>{{ __('Explore licensing options and benefits for advanced features.', 'hydra-booking') }}</p>
            </div>
            <div  v-if="$tfhb_is_pro == true" class="tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between">  

                <!-- Time Zone -->
                <HbText  
                    v-model="LicenseBase.license_key"  
                    required= "true"  
                    type="password"
                    name="license_key"
                    :label="__(' License Key', 'hydra-booking')"  
                    :description="__('Insert your license key here. You can get it from our Client Portal -> Support -> License keys.', 'hydra-booking')"
                    selected = "1"
                    :placeholder="__('Type your Admin Email', 'hydra-booking')"  
                    :errors="errors.license_key"
                /> 

                <HbText  
                    v-model="LicenseBase.license_email"  
                    required= "true"  
                    :label="__(' License Email ', 'hydra-booking')"  
                    :description="__('Please enter the email address you used for purchasing the plugin.', 'hydra-booking')"
                    name="license_email"
                    selected = "1"
                    :placeholder="__('Type your Admin Email', 'hydra-booking')"  
                    :errors="errors.license_email"
                /> 
  
                
            </div>  
            <!-- Date And Time -->

            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="updateLicense(['license_key', 'license_email'])" 
                :buttonText="__('Update General Settings', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true" 
            /> 

        </div>
    </div>
 
</template>