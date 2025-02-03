<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbRadio from '@/components/form-fields/HbRadio.vue'
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator'
const { errors } = useValidators();

import { FdDashboard } from '@/store/frontend-dashboard.js';

const disable_personal_info = ref(true);
const disable_password = ref(true);

const imageChange = (attachment) => {   
    FdDashboard.userAuth.featured_image = attachment.url; 
    const image = document.querySelector('.avatar_display'); 
    image.src = attachment.url; 
    FdDashboard.updateUserProfile();

}
const UploadImage = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChange(attachment);
    };  
    wp.media.editor.open(); 
}
    
</script>

<template>

  
    <div :class="{ 'tfhb-skeleton': FdDashboard.skeleton }" class="tfhbb-fd-user-profile-page tfhb-flexbox tfh-gap-32">   
        <div class="tfhb-single-form-field-wrap tfhb-user-profile-image tfhb-flexbox  tfhb-full-width">
            <div class="tfhb-field-image" >  
                <img v-if="FdDashboard.userAuth.featured_image ==''" class='avatar_display'  :src="$tfhb_url+'/assets/images/avator.png'" >
                <img v-else class='avatar_display'  :src="FdDashboard.userAuth.featured_image" >
                <button class="tfhb-image-btn" @click="UploadImage"><Icon name="ImagePlus" size=20 /> </button> 
                <input  type="text"  :v-model="FdDashboard.userAuth.featured_image"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 v-if="label !=''" :for="name">{{ FdDashboard.userAuth.first_name }}  {{ FdDashboard.userAuth.last_name }} <span  v-if="required == 'true'"> *</span> </h4>
            <p v-if="description !=''"  class="tfhb-m-0">{{ FdDashboard.userAuth.email }}</p>
            </div>
        </div> 
 

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
        
    </div> 
</template>



<style scoped lang="scss">
 
</style>
