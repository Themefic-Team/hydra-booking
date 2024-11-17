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
import HbPopup from '@/components/widgets/HbPopup.vue';


const deletePopup = ref(false)
 

const upgradeToPro = () => {
    // https://themefic.com/docs/hydrabooking redirecto this url in new tab

    window.open('https://hydrabooking.com/#pricing', '_blank');
}

onBeforeMount(async () => {
    LicenseBase.GetLicense();
});

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

    // if LicenseBase.license_email is not email address then return
    if(LicenseBase.license_email && !LicenseBase.license_email.includes('@')){
        toast.error(' Please enter a valid email address', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return
    }
   

    LicenseBase.UpdateLicense();
}

const deactivateLicense = async () => {
 

    LicenseBase.DeactivateLicense();
    deletePopup.value = false;
}

</script>
<template>
    
    <div :class="{ 'tfhb-skeleton': LicenseBase.skeleton }" class="thb-event-dashboard">
  
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
 
            <!-- Date And Time --> 
                <div  v-if="$tfhb_is_pro == true && $tfhb_license_status == false"  class="tfhb-admin-title" >
                    <h2>{{ __(' License Info', 'hydra-booking') }}</h2> 
                    <p>{{ __('Explore licensing options and benefits for advanced features.', 'hydra-booking') }}</p>
                </div>
                <div  v-if="$tfhb_is_pro == true && $tfhb_license_status == true && LicenseBase.LicenseData.is_valid == true" class="tfhb-admin-card-box tfhb-general-card  ">  

                    <ul class="el-license-info">
                        <li>
                            <div>
                                <span class="el-license-info-title">Status</span>
    
                                    <span v-if="LicenseBase.LicenseData.is_valid == true " class="el-license-valid">Valid</span> 
                                    <span v-else class="el-license-valid">Invalid</span> 
                            </div>
                        </li>

                        <li>
                            <div>
                                <span class="el-license-info-title">License Type</span>
                                {{ LicenseBase.LicenseData.license_title }} 
                            </div>
                        </li>

                    <li>
                        <div>
                            <span class="el-license-info-title">License Expired on</span>
                            {{ LicenseBase.LicenseData.expire_date }} 
                        
                                <a v-if="LicenseBase.LicenseData.expire_renew_link" target="_blank" class="el-blue-btn" href="{{ LicenseBase.LicenseData.expire_renew_link }}">Renew</a>
                            
                        </div>
                    </li>

                    <li>
                        <div>
                            <span class="el-license-info-title">Support Expired on</span>
                            {{ LicenseBase.LicenseData.support_end }}
                            <a v-if="LicenseBase.LicenseData.expire_renew_link" target="_blank" class="el-blue-btn" href="{{ LicenseBase.LicenseData.expire_renew_link }}">Renew</a> 
                        </div>
                    </li>
                        <li>
                            <div>
                                <span class="el-license-info-title">Your License Key</span>
                                <span class="el-license-key">{{LicenseBase.LicenseData.license_key }}</span>
                                <!-- <span class="el-license-key"><?php echo esc_attr( substr($this->response_obj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->response_obj->license_key,-9) ); ?></span> -->


                            </div>
                        </li>
                    </ul>
  
                    <HbButton 
                        classValue="tfhb-btn boxed-btn-danger flex-btn tfhb-icon-hover-animation tfhb-mt-16" 
                        @click.stop="deletePopup = true " 
                        :buttonText="__('Deactivate', 'hydra-booking')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                    /> 


                    <HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="400px" name="first-modal">
                            <template #header> 
                                
                            </template>

                            <template #content>  
                                <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                                    <div class="tfhb-close-icon">
                                        <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                                    </div>
                                    <div class="tfhb-close-content">
                                        <h3>{{ __('Confirm License Deactivation!', 'hydra-booking') }}  </h3>  
                                        <p>{{ __('Deactivating the license will remove access to all premium features.', 'hydra-booking') }}</p>
                                    </div>
                                    <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                                        <HbButton 
                                            classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                                            @click=" deletePopup = !deletePopup"
                                            :buttonText="__('Cancel', 'hydra-booking')" 
                                        />  
                                        <HbButton  
                                            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                                            @click="deactivateLicense()"
                                            :buttonText="__('Deactivate', 'hydra-booking')"
                                            icon="Trash2"   
                                            :hover_animation="false" 
                                            icon_position = 'left'
                                        />
                                    
                                    </div>
                                </div> 
                            </template> 
                        </HbPopup>

            </div>  

            <div  v-if="$tfhb_is_pro == true && $tfhb_license_status == false && LicenseBase.LicenseData.is_valid == false" class="tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between">  

                <!-- Time Zone -->
                <HbText  
                    v-model="LicenseBase.license_key"  
                    required= "true"  
                    type="password"
                    name="license_key"
                    :label="__(' License Key', 'hydra-booking')"  
                    :description="__('Insert your license key here. You can get it from our Client Portal -> Support -> License keys.', 'hydra-booking')"
                    selected = "1"
                    :placeholder="__('Enter your License key', 'hydra-booking')"  
                    :errors="errors.license_key"
                /> 

                <HbText  
                    v-model="LicenseBase.license_email"  
                    required= "true"  
                    type="email"
                    :label="__(' License Email ', 'hydra-booking')"  
                    :description="__('Please enter the email address you used for purchasing the plugin.', 'hydra-booking')"
                    name="license_email"
                    selected = "1"
                    :placeholder="__('Type your Admin Email', 'hydra-booking')"  
                    :errors="errors.license_email"
                /> 

                    <HbButton 
                        
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click.stop="updateLicense(['license_key', 'license_email'])" 
                        :buttonText="__('Activate', 'hydra-booking')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                    /> 
                </div>  
            <!-- Date And Time -->

            

           
        </div>
    </div>
 
</template>

<style scoped>

 
.el-license-info-title {
  width: 150px;
  display: inline-block;
  position: relative;
  padding-right: 10px;
  margin-right: 20px !important;
}
.el-license-info-title:after {
  content: ":";
  position: absolute;
  right: 2px;
}
.el-license-valid {
  padding: 0 5px 2px;
  color: #fff;
  background-color: #8fcc77;
  border-radius: 3px;
  color: #fff !important;
  padding: 6px 12px;
}
</style>