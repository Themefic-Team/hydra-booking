<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router'  
// import Form Field  
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import HbText from '@/components/form-fields/HbText.vue';
import { toast } from "vue3-toastify"; 
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 

import Icon from '@/components/icon/LucideIcon.vue';


const props = defineProps([
    'FrontendDashboard', 
]) 
 
const copyShortcodeCode = () => {
    const link = '[hydra_signup_form]';
    const textarea = document.createElement('textarea');
    textarea.value = link;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action 
    // success mess into bottom right
    toast.success( 'Copied' , {
        position: 'bottom-right', // Set the desired position
        duration: 2000 // Set the desired duration
    });
}
</script>

<template>   
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Signup Page Settings') }} </h2>
        <p>{{ $tfhb_trans('Customize the settings for the signup page') }}</p>
    </div>
     <HbInfoBox  name="first-modal">
        
        <template #content>
            <div class="tfhb-flexbox tfhb-justify-between tfhb-align-center">
                
                <span>{{$tfhb_trans('Use shortcode [hydra_signup_form] to show Sign up form in post/page/widget.')}}</span> 
                <span  class="copy-shortcode" @click="copyShortcodeCode()"> 
                    <!-- Copy -->
                    <Icon name="Copy" size ="16" /> 
                </span> 
            </div>
        </template>
    </HbInfoBox>
    <div class="tfhb-admin-card-box tfhb-flexbox"> 
        <HbText  
            v-model="props.FrontendDashboard.fd_dashboard.signup.signup_page_title"  
            required= "true"  
            :label="$tfhb_trans('Sign up Title')"   
            tooltip="true"
            :tooltipText="$tfhb_trans(`Type here custom Sign up title.`)"  
            selected = "1"
            :placeholder="$tfhb_trans('Type Sign up title')" 
            width="50" 
        /> 
        <HbText  
            v-model="props.FrontendDashboard.fd_dashboard.signup.signup_page_sub_title"  
            required= "true"  
            :label="$tfhb_trans('Sign up Sub-title')"   
            tooltip="true"
            :tooltipText="$tfhb_trans(`Type here custom Sign up sub-title.`)"  
            selected = "1"
            :placeholder="$tfhb_trans('Type Sign up sub-title')" 
            width="50" 
        />   
        <!-- Time format -->
        <HbDropdown  
            v-model="props.FrontendDashboard.fd_dashboard.signup.registration_page"  
            required= "true" 
            :filter="true"
            tooltip="true"
            :tooltipText="$tfhb_trans('Choose a page that will be used as the Sign up Page.')"  
            :label="$tfhb_trans('Sign up Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Sign up Page')"   
            :option = "props.FrontendDashboard.pages" 
        />
        <!-- Time format --> 
        <!-- Time format -->
        <HbDropdown  
            v-model="props.FrontendDashboard.fd_dashboard.signup.after_registration_redirect_type"  
            required= "true" 
            tooltip="true"
            :tooltipText="$tfhb_trans('Select the destination for users after they register.')"  
            :label="$tfhb_trans('Choose your Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Signup Redirect Option')"   
            :option = "[
                {'name': 'Custom URL', 'value': 'custom_url'}, 
                {'name': 'Pages', 'value': 'page'} 
            ]" 
        />
        <!-- Time format --> 
        <!-- Time format -->
        <HbDropdown  
            v-if="props.FrontendDashboard.fd_dashboard.signup.after_registration_redirect_type == 'page'"
            v-model="props.FrontendDashboard.fd_dashboard.signup.after_registration_redirect"  
            required= "true" 
            :filter="true"
            tooltip="true"
            :tooltipText="$tfhb_trans('Select the destination page for users after they Sign up.')"  
            :label="$tfhb_trans('Choose your Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Choose your Page')"   
            :option = "props.FrontendDashboard.pages" 
        />
        <HbText  
             v-if="props.FrontendDashboard.fd_dashboard.signup.after_registration_redirect_type == 'custom_url'"
            v-model="props.FrontendDashboard.fd_dashboard.signup.after_registration_redirect_custom"  
            required= "true"  
            :subtitle="$tfhb_trans('Select the destination page for users after they log in.')"
            :label="$tfhb_trans('Type your URL')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your URL')" 
            width="50" 
        /> 
     
        
    </div> 
    <div class="tfhb-admin-card-box tfhb-flexbox email_verifaction"> 
        <HbSwitch 
            v-model="props.FrontendDashboard.fd_dashboard.signup.enable_email_verification"  
            width="100"
            :label="$tfhb_trans('Enable Email Verification')" 
        />
        
    </div> 
    
</template>

<style scoped>
.copy-shortcode{
    cursor:pointer;
}
.email_verifaction{
    background-color: #F9FBF9;
}
</style> 