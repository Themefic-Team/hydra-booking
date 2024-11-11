<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify";
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();

import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
// import Form Field  
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue'; 

const licenseData = reactive({
    license_key: '',
    license_email: '', 
});

const upgradeToPro = () => {
    // https://themefic.com/docs/hydrabooking redirecto this url in new tab

    window.open('https://hydrabooking.com/#pricing', '_blank');
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
          
            <!-- Date And Time --> 
         
            <!-- <div class="tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between ">   -->
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
            <!-- </div>   -->
            <!-- Date And Time --> 
            <div  v-if="$tfhb_is_pro == true"  class="tfhb-admin-title" >
                <h2>{{ __(' License Info', 'hydra-booking') }}</h2> 
                <p>{{ __('Explore licensing options and benefits for advanced features.', 'hydra-booking') }}</p>
            </div>
            <div  v-if="$tfhb_is_pro == true" class="tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between">  

                <!-- Time Zone -->
                <HbText  
                    v-model="licenseData.license_key"  
                    required= "true"  
                    :label="__(' License Key', 'hydra-booking')"  
                    :description="__('Insert your license key here. You can get it from our Client Portal -> Support -> License keys.', 'hydra-booking')"
                    selected = "1"
                    :placeholder="__('Type your Admin Email', 'hydra-booking')"  
                    :errors="errors.admin_email"
                /> 

                <HbText  
                    v-model="licenseData.license_email"  
                    required= "true"  
                    :label="__(' License Email ', 'hydra-booking')"  
                    :description="__('Please enter the email address you used for purchasing the plugin.', 'hydra-booking')"
                    
                    selected = "1"
                    :placeholder="__('Type your Admin Email', 'hydra-booking')"  
                    :errors="errors.admin_email"
                /> 
  
                
            </div>  
            <!-- Date And Time -->

            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="UpdateGeneralSettings" 
                :buttonText="__('Update General Settings', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                :pre_loader="generalSettings_pre_loader"
            /> 

        </div>
    </div>
 
</template>