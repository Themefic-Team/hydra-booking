<script setup> 
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router'
import axios from 'axios'  
import { toast } from "vue3-toastify";  
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator'
const { errors } = useValidators();


import { FdDashboard } from '@/store/frontend-dashboard.js';
 
// Get Current Route url 
const route = useRoute();
const skeleton = ref(true);
const router = useRouter();
const hostData = reactive({
    id: 0,
    user_id: 0,
    first_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    about: '',
    avatar: '',
    featured_image: '',
    time_zone: '',
    availability_type: 'settings',
    availability_id: '',
    availability: [],
    others_information: {},
    status: '',

});
const time_zones = reactive({});
const hosts_settings = reactive({});
const settingsAvailabilityData = reactive({});
const hostId = FdDashboard.userAuth.id;
 
const integration = reactive({
    zoho_crm_status : 0,
});

// availability type
const AvailabilityTabs = (type) => {
    hostData.availability_type = type
}

const update_host_preloader = ref(false);
// Save and Update Host Info
const UpdateHostsInformation = async (validator_field) => {

    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!hostData[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!hostData[fieldParts[0]][fieldParts[1]]){
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

    update_host_preloader.value = true;

    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/information/update', hostData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_integrations'
            } 
        });
        if (response.data.status == true) {  
            

            let nextRouteName = ''; 
            if("HostsProfileInformation"==route.name){ 
                nextRouteName = 'HostsAvailability';
            }
            if("HostsAvailability"==route.name){ 
                nextRouteName = 'HostsProfileCalendars';
            }
            if("HostsProfileCalendars"==route.name){ 
                nextRouteName = 'HostsProfileIntegrations';
            }
            if (nextRouteName) {
                router.push({ name: nextRouteName }).then(() => {
                    nextTick(() => {
                        toast.success(response.data.message, {
                            position: 'bottom-right', // Set the desired position
                            "autoClose": 1500,
                        });
                    });
                });
            }else{
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
            update_host_preloader.value = false;
        }else{ 
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            update_host_preloader.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}


 // Fetch generalSettings
 const fetchHost = async () => { 
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/'+hostId , {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_integrations'
            } 
        } );
        if (response.data.status == true) { 
            
            hostData.id = response.data.host.id;
            hostData.user_id = response.data.host.user_id;
            hostData.first_name = response.data.host.first_name;
            hostData.last_name = response.data.host.last_name;
            hostData.email = response.data.host.email;
            hostData.phone_number = response.data.host.phone_number;
            hostData.about = response.data.host.about;
            hostData.avatar = response.data.host.avatar;
            hostData.featured_image = response.data.host.featured_image;
            hostData.status = response.data.host.status;
            hostData.time_zone = response.data.host.time_zone;
            hostData.availability = response.data.host.availability;
            hostData.availability_type = response.data.host.availability_type ? response.data.host.availability_type : 'settings';
            hostData.availability_id = response.data.host.availability_id;
            hostData.others_information = response.data.host.others_information != null ? response.data.host.others_information : {};
            skeleton.value = false;
            time_zones.data = response.data.time_zone; 
            hosts_settings.data = response.data.hosts_settings; 
            settingsAvailabilityData.data = response.data.settingsAvailabilityData; 
            integration.zoho_crm_status = response.data.integrations.zoho_crm_status;
        }else{ 
            // return to redirect back route 
            router.push({ name: 'HostsLists' });
        }
    } catch (error) {
        
        console.log(error);
    } 
} 
onBeforeMount(() => { 
    fetchHost();
});
    
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
    <div :class="{ 'tfhb-skeleton': FdDashboard.skeleton }" class="tfhbb-fd-user-profile-page tfhb-hydra-wrap tfhb-flexbox tfh-gap-32">   
        
        <div class="tfhb-single-form-field-wrap tfhb-user-profile-image tfhb-flexbox  tfhb-full-width">
            <div class="tfhb-field-image" >  
                <img v-if="FdDashboard.userAuth.featured_image ==''" class='avatar_display'  :src="$tfhb_url+'/assets/images/avator.png'" >
                <img v-else class='avatar_display'  :src="FdDashboard.userAuth.featured_image" >
                <button class="tfhb-image-btn" @click="UploadImage"><Icon name="ImagePlus" size=20 /> </button> 
                <input  type="text"  v-model="FdDashboard.userAuth.featured_image"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 v-if="label !=''" :for="name">{{ FdDashboard.userAuth.first_name }}  {{ FdDashboard.userAuth.last_name }} <span  v-if="required == 'true'"> *</span> </h4>
            <p v-if="description !=''"  class="tfhb-m-0">{{ FdDashboard.userAuth.email }}</p>
            </div>
        </div> 
 
        <nav class="tfhb-booking-tabs tfhb-full-width"> 
            <ul>
                <!-- to route example like hosts/profile/13/information -->
                
                <li><router-link :to="'/frontend-dashboard/profile/information'" exact :class="{ 'active': $route.path === '/frontend-dashboard/profile/information' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Information') }}</router-link></li> 
                <li><router-link :to="'/frontend-dashboard/profile/availability'" :class="{ 'active': $route.path === '/frontend-dashboard/profile/availability' }"> <Icon name="Clock" /> {{ $tfhb_trans('Availability') }}</router-link></li>  
                <li v-if="true == $user.caps.tfhb_manage_integrations"><router-link :to="'/frontend-dashboard/profile/calendars'" :class="{ 'active': $route.path === '/frontend-dashboard/profile/calendars' }"> <Icon name="CalendarDays" /> {{ $tfhb_trans('Calendars') }}</router-link></li>  
                <li v-if="true == $user.caps.tfhb_manage_integrations"><router-link :to="'/frontend-dashboard/profile/integrations'" :class="{ 'active': $route.path === '/frontend-dashboard/profile/integrations' }"> <Icon name="Unplug" /> {{ $tfhb_trans('Integrations') }}</router-link></li>  

            </ul>  
        </nav>
        <div class="tfhb-hydra-dasboard-content">       
            <router-view 
            :hostId ="hostId" 
            :host="hostData" 
            :update_host_preloader="update_host_preloader" 
            :time_zone="time_zones.data" 
            :hosts_settings="hosts_settings.data" 
            :settings_zoho="integration" 
            :settingsAvailabilityData="settingsAvailabilityData.data" 
            @availability-tabs="AvailabilityTabs"
            @save-host-info="UpdateHostsInformation"
            />
            
        </div> 
        
    </div> 
</template>



<style scoped lang="scss">
 
</style>
