<script setup> 
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import axios from 'axios'  
import { toast } from "vue3-toastify";
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator'
const { errors } = useValidators();

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
const hostId = route.params.id;
 
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
            hostData.others_information = response.data.host.others_information != null && response.data.host.others_information.length != '[]' ? response.data.host.others_information : {};
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
    
</script>

<template>

  
    <div :class="{ 'tfhb-skeleton': skeleton }" class="tfhb-hydra-wrap tfhbb-host-profile-page ">    
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title"> 
                <h3>{{hostData.first_name}} {{ hostData.last_name }} {{ $tfhb_trans('Profile') }}  </h3>   
            </div> 
        </div>
        <nav class="tfhb-booking-tabs"> 
            <ul>
                <!-- to route example like hosts/profile/13/information -->
                
                <li><router-link :to="'/hosts/profile/'+ $route.params.id +'/information'" exact :class="{ 'active': $route.path === '/hosts/profile/'+ $route.params.id +'/information' }"> <Icon name="UserRound" /> {{ $tfhb_trans('Information') }}</router-link></li> 
                <li><router-link :to="'/hosts/profile/'+ $route.params.id +'/availability'" :class="{ 'active': $route.path === '/hosts/profile/'+ $route.params.id +'/availability' }"> <Icon name="Clock" /> {{ $tfhb_trans('Availability') }}</router-link></li>  
                <li v-if="true == $user.caps.tfhb_manage_integrations"><router-link :to="'/hosts/profile/'+ $route.params.id +'/calendars'" :class="{ 'active': $route.path === '/hosts/profile/'+ $route.params.id +'/calendars' }"> <Icon name="CalendarDays" /> {{ $tfhb_trans('Calendars') }}</router-link></li>  
                <li v-if="true == $user.caps.tfhb_manage_integrations"><router-link :to="'/hosts/profile/'+ $route.params.id +'/integrations'" :class="{ 'active': $route.path === '/hosts/profile/'+ $route.params.id +'/integrations' }"> <Icon name="Unplug" /> {{ $tfhb_trans('Integrations') }}</router-link></li>  

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
/* Your component styles go here */

.tfhb-hydra-dasboard {

    width: 100%;

    .tfhb-hydra-wrap {
        display: flex;
        // gap: 16px;
    }

    .tfhb-hydra-settings {

        width: 272px;

        ul {
            display: inline-block;
        }
    }

    .tfhb-hydra-dasboard-content {
        width: calc(100% - 272px);
        padding: 24px;
    }

    .tfhb-hydra-wrap {
        width: auto;
        margin-right: 10px;
    }
}
</style>
