<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, onBeforeMount, ref, nextTick } from 'vue';
import { useRouter, RouterView } from 'vue-router' 
import axios from 'axios'  
import Icon from '@/components/icon/LucideIcon.vue'
import Header from '@/components/Header.vue'; 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';
import HbSelect from  '@/components/form-fields/HbSelect.vue';
import HbText from  '@/components/form-fields/HbText.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
// Import for redirect route   
import { Notification } from '@/store/notification'; 
import { toast } from "vue3-toastify"; 

import useValidators from '@/store/validator'
const { errors } = useValidators();

const router = useRouter();
const isModalOpened = ref(false);
const skeleton = ref(true);
const closeModal = () => { 
  isModalOpened.value = false;
};
const openModal = () => {
  isModalOpened.value = true;
};
const hosts = reactive({});
const host  = reactive({});
const usersData = reactive({});
 // Fetch generalSettings
const fetchHosts = async () => {

    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/lists',{
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status) { 
            usersData.data = response.data.users; 
            hosts.data = response.data.hosts; 
            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
} 
// Hosts
const create_host_preloader = ref(false);
const CreateHosts = async (validator_field) => {    

    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!host[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!host[fieldParts[0]][fieldParts[1]]){
                errors[fieldParts[0]+'___'+[fieldParts[1]]] = 'Required this field';
            }
        }
            
        });
    }
    // Errors Checked
    const isEmpty = Object.keys(errors).length === 0;
    if(!isEmpty && host.id == 0){ 
        toast.error('Fill Up The Required Fields', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return
    }
    // redirect with router argument
   create_host_preloader.value = true;
    try { 
        // axisos sent dataHeader Nonce Data
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/create', host, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
            // skeleton.value = false;
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });  
            create_host_preloader.value = false;
            closeModal(); 
            hosts.data = response.data.hosts;   
            router.push({ name: 'HostsProfile', params: { id: response.data.id} }).then(() => {
                    nextTick(() => {
                        toast.success(response.data.message, {
                            position: 'bottom-right',
                            autoClose: 1500,
                        });
                    });
                }); 
            // Redirecto to Other Route
            // router.push({ name: 'HostsProfile' });
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            create_host_preloader.value = false;
        }
    } catch (error) {
        console.log(error);
    }   
}

// Delete Hosts 
const deleteHost = async ($id, $user_id) => { 
    let deleteHost = {
        id: $id,
        user_id: $user_id
    }
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/delete', deleteHost, { 
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            }
        }  );
        if (response.data.status) { 
            hosts.data = response.data.hosts;  
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        console.log(error);
    }
}

// Delete Hosts 
const updateHostStatus = async ($id, $user_id, $status) => { 
    let HostData = {
        id: $id,
        user_id: $user_id,
        status: $status
    }
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/update-status', HostData, { 
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            }
        } );
        if (response.data.status) { 
            hosts.data = response.data.hosts;  
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        console.log(error);
    }
}

onBeforeMount(() => { 
    fetchHosts();

    Notification.fetchNotifications();
});

// Filtering
const filterData = reactive({
    name: '',
})
const Tfhb_Host_Filter = async (e) =>{
    filterData.name=e.target.value;
    skeleton.value = true;
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/filter', {
            params: {
                filterData
            },
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_integrations'
            }
        });
        
        if (response.data.status) { 
            hosts.data = response.data.hosts;  
            skeleton.value = false;
        }

    } catch (error) {
        console.error('Error:', error);
    }
}


</script>

<template>

    <!-- {{ tfhbClass }} -->
    <div :class="{ 'tfhb-skeleton': skeleton }"  class="tfhb-admin-hosts">
        <Header title="Hosts" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" />
        <div class="tfhb-dashboard-heading tfhb-flexbox tfhb-justify-between">
           <div class="tfhb-header-filters">
                <input type="text" @keyup="Tfhb_Host_Filter" placeholder="Search by host name" /> 
                <span><Icon name="Search" size=20 /></span>
           </div>
            <div class="thb-admin-btn right"> 
               <HbButton 
                    classValue="tfhb-btn boxed-btn flex-btn " 
                    @click="openModal"
                    :buttonText="$tfhb_trans('Add New Host')"
                    icon="PlusCircle"  
                    icon_position="left"

                />  
            </div> 
        </div>
        <div class="tfhb-hosts-content">  
            <HbPopup :isOpen="isModalOpened" @modal-close="closeModal" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ $tfhb_trans('Add New Host') }}</h2>   
                </template>

                <template #content>  
                    <!-- Select User --> 
                    <HbDropdown    
                        v-model="host.id"  
                        required= "true"  
                        :label="$tfhb_trans('Select User')"  
                        name="id"
                        selected = "1"
                        :placeholder="$tfhb_trans('Select User')" 
                        :option = "usersData.data" 
                        :errors="errors.id"
                    /> 
                    <!-- Select User --> 
                    <!-- UsernName -->
                    <HbText  
                        v-if="host.id == 0"
                        v-model="host.username"  
                        required= "true"  
                        :label="$tfhb_trans('Username')"  
                        name="username"
                        selected = "1"
                        :placeholder="$tfhb_trans('Type Username')"  
                        :errors="errors.username"
                    /> 
                    <!-- UsernName -->
                    <!-- Email -->
                    <HbText  
                    v-if="host.id == 0"
                        v-model="host.email"  
                        required= "true"  
                        type= "email"  
                        name="email"
                        :label="$tfhb_trans('Email')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Type User Email')"  
                        :errors="errors.email"
                    /> 
                    <!-- Email -->

                    <!-- Password -->
                    
                    <HbText  
                        v-if="host.id == 0"
                        v-model="host.password"  
                        required= "true"  
                        name="password"
                        type= "password"  
                        :label="$tfhb_trans('Password')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Type User Password')"  
                        :errors="errors.password"
                    /> 
                    <!-- Password -->
                    

                    <!-- Create Or Update Availability --> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn " 
                        @click="CreateHosts( ['username', 'email', 'password'] )"
                        :buttonText="$tfhb_trans('Create Hosts')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"
                        :pre_loader="create_host_preloader"
                    />   
                </template> 
            </HbPopup>
             <router-view :host_list="hosts.data" @update-host-status="updateHostStatus" @delete-host="deleteHost" :host_skeleton="skeleton" /> 
        </div> 
    </div>
</template>



<style scoped lang="scss">
/* Your component styles go here */

 
</style>
