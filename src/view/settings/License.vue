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
    if( typeof tfhb_core_apps_pro !== 'undefined'){

        LicenseBase.GetLicense();
    }else{
        LicenseBase.skeleton = false;
    }
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

const encryptLicense = (license_key) => {
    // BDF2D6F8-XXXXXXXX-XXXXXXXX-7DC7E8FC
    return license_key.substr(0,9) + 'XXXXXXXX-XXXXXXXX' + license_key.substr(-9); 
}


</script>
<template>
    
    <div :class="{ 'tfhb-skeleton': LicenseBase.skeleton }" class="thb-event-dashboard">
  
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('License Management') }}</h1>  
                <p>{{ $tfhb_trans('Manage your HydraBooking Pro license') }}</p>
            </div>
            <div class="thb-admin-btn right">  

            </div> 
        </div>
        <div class="tfhb-content-wrap">
           
            <HbInfoBox v-if="$tfhb_is_pro == false"  icon="Lock" name="first-modal">
                
                <template #content>
                    <div  class="tfhb-license-heading  tfhb-flexbox tfhb-full-width tfhb-flexbox-nowrap tfhb-justify-between">
                        <div class="tfhb-admin-title tfhb-m-0"> 
                            <h2 >{{ $tfhb_trans('Unlock Advanced Capabilities with HydraBooking Pro !') }}</h2>  
                            <p>{{ $tfhb_trans('Please upgrade to get all the advanced features.') }}</p>
                        </div>
                        
                        <div class="thb-admin-btn right"> 
                            <HbButton 
                                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                                @click="upgradeToPro" 
                                :buttonText="$tfhb_trans('Upgrade to Pro')"
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
                    <h2>{{ $tfhb_trans('License Info') }}</h2> 
                    <p>{{ $tfhb_trans('Explore licensing options and benefits for advanced features.') }}</p>
                </div>
                <div  v-if="$tfhb_is_pro == true && $tfhb_license_status == true && LicenseBase.LicenseData.is_valid == true" class="tfhb-admin-card-box tfhb-general-card  ">  

                    <ul class="el-license-info">
                        <li>
                            <div>
                                <span class="el-license-info-title">{{ $tfhb_trans('Status') }}</span>
    
                                    <span v-if="LicenseBase.LicenseData.is_valid == true " class="el-license-valid">{{ $tfhb_trans('Valid') }}</span> 
                                    <span v-else class="el-license-valid">{{ $tfhb_trans('Invalid') }}</span> 
                            </div>
                        </li>

                        <li>
                            <div>
                                <span class="el-license-info-title">{{ $tfhb_trans('License Type') }}</span>
                                {{ LicenseBase.LicenseData.license_title }} 
                            </div>
                        </li>

                    <li>
                        <div>
                            <span class="el-license-info-title">{{ $tfhb_trans('License Expired on') }}</span>
                            {{ LicenseBase.LicenseData.expire_date }} 
                        
                                <a v-if="LicenseBase.LicenseData.expire_renew_link" target="_blank" class="el-blue-btn" href="{{ LicenseBase.LicenseData.expire_renew_link }}">{{ $tfhb_trans('Renew') }}</a>
                            
                        </div>
                    </li>

                    <li>
                        <div>
                            <span class="el-license-info-title">{{ $tfhb_trans('Support Expired on') }}</span>
                            {{ LicenseBase.LicenseData.support_end }}
                            <a v-if="LicenseBase.LicenseData.expire_renew_link" target="_blank" class="el-blue-btn" href="{{ LicenseBase.LicenseData.expire_renew_link }}">{{ $tfhb_trans('Renew') }}</a> 
                        </div>
                    </li>
                        <li>
                            <div>
                                <span class="el-license-info-title">{{ $tfhb_trans('Your License Key') }}</span>
                                <span class="el-license-key">{{ encryptLicense(LicenseBase.LicenseData.license_key) }}</span>
                                <!-- <span class="el-license-key"><?php echo esc_attr( substr($this->response_obj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->response_obj->license_key,-9) ); ?></span> -->


                            </div>
                        </li>
                    </ul>
  
                    <HbButton 
                        classValue="tfhb-btn boxed-btn-danger flex-btn tfhb-icon-hover-animation tfhb-mt-16" 
                        @click.stop="deletePopup = true " 
                        :buttonText="$tfhb_trans('Deactivate')"
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
                                        <h3>{{ $tfhb_trans('Confirm License Deactivation!') }}  </h3>  
                                        <p>{{ $tfhb_trans('Deactivating the license will remove access to all premium features.') }}</p>
                                    </div>
                                    <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                                        <HbButton 
                                            classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                                            @click=" deletePopup = !deletePopup"
                                            :buttonText="$tfhb_trans('Cancel')" 
                                        />  
                                        <HbButton  
                                            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                                            @click="deactivateLicense()"
                                            :buttonText="$tfhb_trans('Deactivate')"
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
                    :label="$tfhb_trans('License Key')"  
                    :description="$tfhb_trans('Insert your license key here. You can get it from our Client Portal -> Support -> License keys.')"
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter your License key')"  
                    :errors="errors.license_key"
                /> 

                <HbText  
                    v-model="LicenseBase.license_email"  
                    required= "true"  
                    type="email"
                    :label="$tfhb_trans('License Email')"  
                    :description="$tfhb_trans('Please enter the email address you used for purchasing the plugin.')"
                    name="license_email"
                    selected = "1"
                    :placeholder="$tfhb_trans('Type your Admin Email')"  
                    :errors="errors.license_email"
                /> 

                    <HbButton 
                        
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click.stop="updateLicense(['license_key', 'license_email'])" 
                        :buttonText="$tfhb_trans('Activate')"
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