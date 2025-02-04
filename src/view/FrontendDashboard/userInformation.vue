<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue' 
import HbButton from '@/components/form-fields/HbButton.vue' 
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator' 

import { FdDashboard } from '@/store/frontend-dashboard.js';

const disable_personal_info = ref(true);
const disable_password = ref(true);

 

</script>

<template>

    <div class="tfhb-full-width"> 
        <div class="tfhb-admin-title tfhb-flexbox tfhb-justify-between" >
            <div>
                <h2>{{ $tfhb_trans('Personal Details') }}    </h2>  
                <span>Set up your information</span>
            </div>
            <div class="tfhb-flexbox tfhb-gap-8">
                    
                <HbButton 
                    v-if=" disable_personal_info == true"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="disable_personal_info = false"
                    :buttonText="$tfhb_trans('Edit')"
                    icon="Pencil" 
                />  
                <HbButton 
                    v-if="disable_personal_info == false"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="disable_personal_info = true"
                    :buttonText="$tfhb_trans('Cancel')"  
                />   
                <HbButton 
                    v-if=" disable_personal_info == false"  
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="FdDashboard.updateUserProfile()"
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
                :disabled="disable_personal_info"
            /> 
            <HbText  
                v-model="FdDashboard.userAuth.last_name"   
                :label="$tfhb_trans('Last name')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your last name')" 
                width="50" 
                :disabled="disable_personal_info"
            />  
            <HbText  
                v-model="FdDashboard.userAuth.email"   
                :label="$tfhb_trans('Email')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your email')" 
                width="50"
                :disabled="disable_personal_info"
            /> 
                
            <HbText  
                v-model="FdDashboard.userAuth.phone_number"   
                :label="$tfhb_trans('Mobile')"  
                selected = "1"
                :placeholder="$tfhb_trans('Type your mobile no')" 
                width="50" 
                :disabled="disable_personal_info"
            />  
            
        <!-- Time Zone -->
        </div>
    </div>
    <div class="tfhb-full-width"> 
        <div class="tfhb-admin-title tfhb-flexbox tfhb-justify-between" >
            <div>
                <h2>{{ $tfhb_trans('Password') }}    </h2>  
                <span>Update your password and manage account</span>
            </div>
            <div class="tfhb-flexbox tfhb-gap-8">
                <HbButton 
                    v-if=" disable_password == true"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="disable_password = false"
                    :buttonText="$tfhb_trans('Edit')"
                    icon="Pencil" 
                />  
                <HbButton 
                    v-if="disable_password == false"  
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="disable_password = true"
                    :buttonText="$tfhb_trans('Cancel')"  
                />   
                <HbButton 
                    v-if=" disable_password == false"  
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


 