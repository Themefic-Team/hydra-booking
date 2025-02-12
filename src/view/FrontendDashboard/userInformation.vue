<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue' 
import HbButton from '@/components/form-fields/HbButton.vue' 
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();
import { toast } from "vue3-toastify";

import { FdDashboard } from '@/store/frontend-dashboard.js';

const disable_personal_info = ref(true);
const disable_password = ref(true);

const tfhbValidateInput = (validator_field) => {
    // Clear the errors object
     // Clear the errors object
     Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!FdDashboard.userAuth[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!FdDashboard.userAuth[fieldParts[0]][fieldParts[1]]){
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

    FdDashboard.updateUserProfile()
};
 

</script>

<template> 
    <div class="tfhb-full-width"> 
        <div class="tfhb-admin-title tfhb-flexbox tfhb-justify-between" >
            <div>
                <h2>{{ $tfhb_trans('Personal Details') }}    </h2>  
                <span>{{ $tfhb_trans('Set up your information') }}</span>
            </div>
            <div class="tfhb-flexbox tfhb-gap-8"> 
                <HbButton 
                    v-if=" FdDashboard.disable_personal_info == true"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="FdDashboard.disable_personal_info = false"
                    :buttonText="$tfhb_trans('Edit')"
                    icon="Pencil" 
                    icon_size = '15'
                />  
                <HbButton 
                    v-if="FdDashboard.disable_personal_info == false"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="FdDashboard.disable_personal_info = true"
                    :buttonText="$tfhb_trans('Cancel')"  
                />   
                <HbButton 
                    v-if=" FdDashboard.disable_personal_info == false"  
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="tfhbValidateInput(['first_name', 'last_name', 'time_zone', 'email'])"
                    :buttonText="$tfhb_trans('Save')"
                    icon="Save"
                    :pre_loader="FdDashboard.user_info_update_preloader"
                /> 
            </div>
        </div>
        <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">  
            <HbText  
                v-model="FdDashboard.userAuth.first_name"   
                :label="$tfhb_trans('First name')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your first name')" 
                width="50" 
                :disabled="FdDashboard.disable_personal_info"
                required= "true" 
                :errors="errors.first_name"
            /> 
            <HbText  
                v-model="FdDashboard.userAuth.last_name"   
                :label="$tfhb_trans('Last name')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your last name')" 
                width="50" 
                :disabled="FdDashboard.disable_personal_info"
                required= "true"  
                :errors="errors.last_name"
            />  
            <HbText  
                v-model="FdDashboard.userAuth.email"   
                :label="$tfhb_trans('Email')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your email')" 
                width="50"
                :disabled="FdDashboard.disable_personal_info"
                required= "true" 
                :errors="errors.email"
            /> 
            <HbDropdown 
                v-model="FdDashboard.userAuth.time_zone"  
                required= "true"  
                :label="$tfhb_trans('Time zone')"  
                selected = "1"
                :filter="true"
                :placeholder="$tfhb_trans('Select Time Zone')"  
                :option = "FdDashboard.time_zone" 
                width="50" 
                :errors="errors.time_zone"
            /> 
            <HbText  
                v-model="FdDashboard.userAuth.phone_number"   
                :label="$tfhb_trans('Mobile')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your mobile no')" 
                width="50" 
                :disabled="FdDashboard.disable_personal_info"
            />  
            
        <!-- Time Zone -->
        </div>
    </div>
    <div class="tfhb-full-width"> 
        <div class="tfhb-admin-title tfhb-flexbox tfhb-justify-between" >
            <div>
                <h2>{{ $tfhb_trans('Password') }}    </h2>  
                <span>{{ $tfhb_trans('Update your password and manage account') }} </span>
            </div>
            <div class="tfhb-flexbox tfhb-gap-8">
                <HbButton 
                    v-if=" FdDashboard.disable_password == true"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="FdDashboard.disable_password = false"
                    :buttonText="$tfhb_trans('Edit')"
                    icon="Pencil" 
                    icon_size = '15'
                />  
                <HbButton 
                    v-if="FdDashboard.disable_password == false"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="FdDashboard.disable_password = true"
                    :buttonText="$tfhb_trans('Cancel')"  
                />   
                <HbButton 
                    v-if=" FdDashboard.disable_password == false"  
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="FdDashboard.changePassword()"
                    :buttonText="$tfhb_trans('Save')"
                    icon="Save"
                    :pre_loader="FdDashboard.reset_password_preloader"
                /> 
            </div>
        </div>
        <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">  
            <HbText  
                v-model="FdDashboard.pass_data.old_password"  
                required= "true"  
                :label="$tfhb_trans('Old password')"  
                type="password"
                selected = "1"
                :placeholder="$tfhb_trans('Type your old password')" 
                width="100"
                :disabled="disable_password"
            /> 
            <HbText  
                v-model="FdDashboard.pass_data.new_password"  
                required= "true"  
                type="password"
                :label="$tfhb_trans('New password')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your new password')" 
                width="50"
                :disabled="disable_password"
            /> 
            <HbText  
                v-model="FdDashboard.pass_data.confirm_password"  
                required= "true" 
                type="password" 
                :label="$tfhb_trans('New password')"  
                selected = "1"
                :placeholder="$tfhb_trans('Re-type your new password')" 
                width="50"
                :disabled="disable_password"
            /> 
            
        <!-- Time Zone -->
        </div>
    </div>
         
</template>


 