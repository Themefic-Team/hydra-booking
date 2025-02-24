<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router'  
// import Form Field  
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import HbText from '@/components/form-fields/HbText.vue';

import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 


const props = defineProps([
    'FrontendDashboard', 
])

</script>

<template>   
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Signup Page Settings') }} </h2>
        <p>{{ $tfhb_trans('Customize the settings for the signup page') }}</p>
    </div>
     <HbInfoBox  name="first-modal">
        
        <template #content>
            <span>{{$tfhb_trans('Use shortcode [hydra_signup_form] to show Sign up form in post/page/widget.')}}</span> 
        </template>
    </HbInfoBox>
    <div class="tfhb-admin-card-box tfhb-flexbox"> 
        <HbText  
            v-model="props.FrontendDashboard.fd_dashboard.signup.signup_page_title"  
            required= "true"  
            :label="$tfhb_trans('Sign up Title')"   
            :subtitle="$tfhb_trans(`Type here custom Sign up title.`)"  
            selected = "1"
            :placeholder="$tfhb_trans('Type Sign up title')" 
            width="50" 
        /> 
        <HbText  
            v-model="props.FrontendDashboard.fd_dashboard.signup.signup_page_sub_title"  
            required= "true"  
            :label="$tfhb_trans('Sign up Sub-title')"   
            :subtitle="$tfhb_trans(`Type here custom Sign up sub-title.`)"  
            selected = "1"
            :placeholder="$tfhb_trans('Type Sign up sub-title')" 
            width="50" 
        />   
        <!-- Time format -->
        <HbDropdown  
            v-model="props.FrontendDashboard.fd_dashboard.signup.registration_page"  
            required= "true" 
            :filter="true"
            :subtitle="$tfhb_trans('Choose a page that will be used as the Sign up Page.')"  
            :label="$tfhb_trans('Registration Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Registration Page')"   
            :option = "props.FrontendDashboard.pages" 
        />
        <!-- Time format --> 
        <!-- Time format -->
        <HbDropdown  
            v-model="props.FrontendDashboard.fd_dashboard.signup.after_registration_redirect_type"  
            required= "true" 
            :subtitle="$tfhb_trans('Select the destination for users after they register.')"  
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
            :subtitle="$tfhb_trans('Select the destination page for users after they Sign up.')"  
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
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Signup Email Settings') }} </h2>
        <p>{{ $tfhb_trans('Customize the email settings for the signup page') }}</p>
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox"> 
        <HbSwitch 
            v-model="props.FrontendDashboard.fd_dashboard.signup.enable_email_verification"  
            width="100"
            :label="$tfhb_trans('Enable Email Verification')" 
        />
        
    </div>

   
    
</template>

<style scoped>
</style> 