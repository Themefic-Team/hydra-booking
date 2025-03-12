<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router'  
// import Form Field  
import Icon from '@/components/icon/LucideIcon.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import HbText from '@/components/form-fields/HbText.vue';
import { toast } from "vue3-toastify"; 
const props = defineProps([
    'FrontendDashboard', 
]);
const copyShortcodeCode = () => {
    const link = '[hydra_login_form]';
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
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Login Page Settings') }} </h2>
        <p>{{ $tfhb_trans('Customize the settings for the login page') }}</p>
    </div>
    <HbInfoBox  name="first-modal">
        
        <template #content>
            <div class="tfhb-flexbox tfhb-justify-between tfhb-align-center">
                <span>
                    {{ $tfhb_trans('Use shortcode [hydra_login_form] to show login form in post/page/widget.') }}
                </span> 
                <span  class="copy-shortcode" @click="copyShortcodeCode()"> 
                    <!-- Copy -->
                    <Icon name="Copy" size ="16" /> 
                </span> 
            </div>
        </template>
    </HbInfoBox>
    <div class="tfhb-admin-card-box tfhb-flexbox">   
        <HbText  
            v-model="props.FrontendDashboard.fd_dashboard.login.login_page_title"  
            required= "true"  
            :label="$tfhb_trans('Login Title')"   
            tooltip="true"
            :tooltipText="$tfhb_trans(`Type here custom login title.`)"  
            selected = "1"
            :placeholder="$tfhb_trans('Type login title')" 
            width="50" 
        /> 
        <HbText  
            v-model="props.FrontendDashboard.fd_dashboard.login.login_page_sub_title"  
            required= "true"  
            :label="$tfhb_trans('Login Sub-title')"   
            tooltip="true"
            :tooltipText="$tfhb_trans(`Type here custom login sub-title.`)"  
            selected = "1"
            :placeholder="$tfhb_trans('Type login sub-title')" 
            width="50" 
        /> 
        <!--  Login Page  -->
        <HbDropdown  
            v-model="props.FrontendDashboard.fd_dashboard.login.login_page"  
            required= "true" 
            tooltip="true"
            :tooltipText="$tfhb_trans('Choose a page to serve as the Login Page.')"  
            :label="$tfhb_trans('Login Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Login Page')"   
            :option = "props.FrontendDashboard.pages" 
        />
        <!--  Login Page  --> 
        <!-- Login Redirect Option -->
        <HbDropdown  
            v-model="props.FrontendDashboard.fd_dashboard.login.after_login_redirect_type"  
            required= "true" 
            tooltip="true"
            :tooltipText="$tfhb_trans('Select the destination for users after they log in.')"  
            :label="$tfhb_trans('Login Redirect Option')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Login Redirect Option')"   
            :option = "[
                {'name': 'Custom URL', 'value': 'custom_url'}, 
                {'name': 'Pages', 'value': 'page'} 
            ]" 
        /> 
        <!-- Time format -->
        <HbDropdown  

        v-if="props.FrontendDashboard.fd_dashboard.login.after_login_redirect_type == 'page'"
            v-model="props.FrontendDashboard.fd_dashboard.login.after_login_redirect"  
            required= "true" 
            tooltip="true"
            :tooltipText="$tfhb_trans('Select the destination page for users after they log in.')"  
            :label="$tfhb_trans('Choose your Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Time Format')"   
            :option = "props.FrontendDashboard.pages" 
        />
        <HbText  
             v-if="props.FrontendDashboard.fd_dashboard.login.after_login_redirect_type == 'custom_url'"
            v-model="props.FrontendDashboard.fd_dashboard.login.after_login_redirect_custom"  
            required= "true"  
            tooltip="true"
            :tooltipText="$tfhb_trans('Select the destination page for users after they log in.')"
            :label="$tfhb_trans('Type your URL')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your URL')" 
            width="50" 
        />  
    </div>

    
    
</template>

<style scoped>
.copy-shortcode{
    cursor:pointer;
}
</style> 