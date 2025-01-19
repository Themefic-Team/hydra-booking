<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import HbQuestion from '@/components/widgets/HbQuestion.vue'; 

const props = defineProps([
    'FrontendDashboard', 
])

const imageChangeDashboardLogo = (attachment) => {   
    props.FrontendDashboard.fd_dashboard.general.dashboard_logo = attachment.url; 
    const image = document.querySelector('.dashboard_logo_display'); 
    image.src = attachment.url; 
}
const UploadChangeDashboardLogo = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChangeDashboardLogo(attachment);
    };  
    wp.media.editor.open(); 
}
const imageChangeMobileDashboardLogo = (attachment) => {   
    props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo = attachment.url; 
    const image = document.querySelector('.mobile_dashboard_logo_display'); 
    image.src = attachment.url; 
}
const UploadChangeMobileDashboardLogo = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChangeMobileDashboardLogo(attachment);
    };  
    wp.media.editor.open(); 
}
 
</script>

<template>   
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('General Settings') }} </h2> 
        <p>{{ $tfhb_trans('Manage the general settings and preferences for the frontend dashboard') }}</p>
    </div> 
    <div class="tfhb-admin-card-box">   

        <div class="tfhb-single-form-field-wrap tfhb-flexbox">
            <div class="tfhb-field-image" >  
                <img v-if="props.FrontendDashboard.fd_dashboard.general.dashboard_logo != ''"  class='dashboard_logo_display'  :src="props.FrontendDashboard.fd_dashboard.general.dashboard_logo">
                <img v-else class='dashboard_logo_display'  :src="$tfhb_url+'/assets/images/150x50.png'" >
                <button class="tfhb-image-btn" @click="UploadChangeDashboardLogo">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="props.FrontendDashboard.fd_dashboard.general.dashboard_logo"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 >{{ $tfhb_trans('Dashboard Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p   class="tfhb-m-0">{{ $tfhb_trans('This logo serves as the main header image of the dashboard') }}</p>
            </div>
        </div> 
 
    </div>
    <div class="tfhb-admin-card-box">
        <div class="tfhb-single-form-field-wrap tfhb-flexbox">
            <div class="tfhb-field-image" >  
                <img v-if="props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo != ''"  class='mobile_dashboard_logo_display'  :src="props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo">
                
                <img v-else class='mobile_dashboard_logo_display'  :src="$tfhb_url+'/assets/images/150x50.png'" >
                <button class="tfhb-image-btn" @click="UploadChangeMobileDashboardLogo">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 >{{ $tfhb_trans('Mobile Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p class="tfhb-m-0">{{ $tfhb_trans('This logo is used for the mobile version of the dashboard') }}</p>
            </div>
        </div> 
    </div>
</template>

<style scoped>
.tfhb-field-image {
	width: 100px !important;
	border-radius: 18px !important;
}
.tfhb-single-form-field-wrap .tfhb-field-image img {
	height: 100%;
	width: 100%;
	border-radius: 18px;
}
</style> 