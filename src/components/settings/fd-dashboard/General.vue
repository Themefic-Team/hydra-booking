<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import HbQuestion from '@/components/widgets/HbQuestion.vue'; 
import HbColor from '@/components/form-fields/HbColor.vue'; 
import HbColorPalette from '@/components/form-fields/HbColorPalette.vue'; 
import LvColorpicker from 'lightvue/color-picker';
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
const ChangeColors = (value,  colors) => {

    props.FrontendDashboard.fd_dashboard.general.colors_palette = value; 
    console.log(colors);
    if('custom' != props.FrontendDashboard.fd_dashboard.general.colors_palette){  
        props.FrontendDashboard.fd_dashboard.general.primery_default = colors.primary; 
        props.FrontendDashboard.fd_dashboard.general.primery_hover = colors.primery_hover; 
        props.FrontendDashboard.fd_dashboard.general.secondary_default = colors.secondary; 
        props.FrontendDashboard.fd_dashboard.general.secondary_hover = colors.secondary_hover; 
        props.FrontendDashboard.fd_dashboard.general.text_title = colors.text_title; 
        props.FrontendDashboard.fd_dashboard.general.text_paragraph = colors.text_paragraph; 
        props.FrontendDashboard.fd_dashboard.general.surface_primary = colors.surface_primary; 
        props.FrontendDashboard.fd_dashboard.general.surface_background = colors.surface_background; 
        props.FrontendDashboard.fd_dashboard.general.surface_border = colors.surface_border; 
        props.FrontendDashboard.fd_dashboard.general.surface_border_hover = colors.surface_border_hover; 
        props.FrontendDashboard.fd_dashboard.general.surface_input_field = colors.surface_input_field;  
    }
    
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
                <img v-else class='dashboard_logo_display'  :src="$tfhb_url+'/assets/images/images-icon.png'" >
                <button class="tfhb-image-btn" @click="UploadChangeDashboardLogo">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="props.FrontendDashboard.fd_dashboard.general.dashboard_logo"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 >{{ $tfhb_trans('Dashboard Header Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p   class="tfhb-m-0">{{ $tfhb_trans('This logo serves as the main header image of the dashboard') }}</p>
            </div>
        </div> 
 
    </div>
    <div class="tfhb-admin-card-box">  
        
        <div class="tfhb-single-form-field-wrap tfhb-flexbox ">
            <div class="tfhb-field-image" >  
                <img v-if="props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo != ''"  class='mobile_dashboard_logo_display'  :src="props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo">
                
                <img v-else class='mobile_dashboard_logo_display'  :src="$tfhb_url+'/assets/images/images-icon.png'" >
                <button class="tfhb-image-btn" @click="UploadChangeMobileDashboardLogo">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="props.FrontendDashboard.fd_dashboard.general.mobile_dashboard_logo"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 >{{ $tfhb_trans('Responsive Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
               
            <p class="tfhb-m-0">{{ $tfhb_trans('This logo is used for the mobile version of the dashboard') }}</p>

            
            </div>
        </div>
        
    </div>

    <div class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('Frontend dashboard custom brand colors') }}</h2> 
        <p>{{ $tfhb_trans('Customize your own brand color into Frontend Dashboard') }}</p>
    </div>

    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-tb-24 tfhb-gap-16"> 
        <HbColorPalette  
            v-model="props.FrontendDashboard.fd_dashboard.general.colors_palette"   
            :label="$tfhb_trans('Default Palette')"  
            selected = "1" 
            name="default-palellte"
            width="33" 
            :class="{ 'active': props.FrontendDashboard.fd_dashboard.general.colors_palette == 'default' }"
            value="default"
            @click="ChangeColors('default',{  primary: '#2E6B38', secondary: '#273F2B', text_title: '#141915', text_paragraph: '#273F2B', primery_hover: '#4C9959', secondary_hover: '#E1F2E4', surface_primary: '#F9FBF9', surface_background: '#C0D8C4', surface_border: '#C0D8C4', surface_border_hover: '#211319', surface_input_field: '#56765B', })"
            :colors ="{  primary: '#2E6B38', secondary: '#273F2B', text_title: '#141915', text_paragraph: '#273F2B', primary_hover: '#4C9959', secondary_hover: '#E1F2E4', surface_primary: '#F9FBF9', surface_background: '#C0D8C4', surface_border: '#C0D8C4', surface_border_hover: '#211319', surface_input_field: '#56765B', }"
        />  
        <HbColorPalette  
            v-model="props.FrontendDashboard.fd_dashboard.general.colors_palette"   
            :label="$tfhb_trans('Custom Palette')"  
            selected = "1" 
            name="custom-palellte"
            value="custom"
            :class="{ 'active': props.FrontendDashboard.fd_dashboard.general.colors_palette == 'custom' }"
            width="33"
            @click="ChangeColors('custom',{  primary: '', secondary: '', text_title: '', text_paragraph: '', primary_hover: '', secondary_hover: '', surface_primary: '', surface_background: '', surface_border: '', surface_border_hover: '', surface_input_field: '', })"
            :colors ="{  primary: '', secondary: '', text_title: '', text_paragraph: '', primary_hover: '', secondary_hover: '', surface_primary: '', surface_background: '', surface_border: '', surface_border_hover: '', surface_input_field: '', }"
        />  
        
    </div>
    <div v-if="props.FrontendDashboard.fd_dashboard.general.colors_palette == 'custom'" class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-tb-24 tfhb-gap-16">
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.primery_default"   
            :label="$tfhb_trans('Primary Color (Default)')"
            name="Primary"
            selected = "1" 
            width="50"  
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.primery_hover"   
            :label="$tfhb_trans('Primary Color (Hover)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.secondary_default"   
            :label="$tfhb_trans('Secondary Color (Default)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.secondary_hover"   
            :label="$tfhb_trans('Secondary Color (Hover)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.text_title"   
            :label="$tfhb_trans('Text Color (Title)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.text_paragraph"   
            :label="$tfhb_trans('Text Color (Paragraph)')"  
            selected = "1" 
            width="50" 
        />  

        <!--  -->
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.surface_primary"   
            :label="$tfhb_trans('Surface Color (Primary)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.surface_background"   
            :label="$tfhb_trans('Surface Color (Background)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.surface_border"   
            :label="$tfhb_trans('Surface Color (Border)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.surface_border_hover"   
            :label="$tfhb_trans('Surface Color (Border Hover)')"  
            selected = "1" 
            width="50" 
        />  
        <HbColor  
            v-model="props.FrontendDashboard.fd_dashboard.general.surface_input_field"   
            :label="$tfhb_trans('Surface Color (Input Field)')"  
            selected = "1" 
            width="50" 
        />  
        
    </div>

</template>

<style scoped>
.tfhb-field-image {
	max-width: 300px !important;
    width: auto !important;
	border-radius: 18px !important;
}
.tfhb-single-form-field-wrap .tfhb-field-image img {
	height: 100%;
	width: 100%;
    padding: 4px;
	border-radius: 18px;
}
</style> 