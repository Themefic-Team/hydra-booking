<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, onBeforeMount, ref } from 'vue';
import { useRouter, RouterView } from 'vue-router' 
import axios from 'axios'  
import Icon from '@/components/icon/LucideIcon.vue'
import Header from '@/components/Header.vue'; 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSelect from  '@/components/form-fields/HbSelect.vue';
import HbText from  '@/components/form-fields/HbText.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
// Import for redirect route   
import { Notification } from '@/store/notification'; 
import { toast } from "vue3-toastify"; 
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
const CreateHosts = async () => {    
    // redirect with router argument
   
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
            closeModal(); 
            hosts.data = response.data.hosts;  
            router.push({ name: 'HostsProfile', params: { id: response.data.id} });
            // Redirecto to Other Route
            // router.push({ name: 'HostsProfile' });
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
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
        <div class="tfhb-dashboard-heading tfhb-flexbox">
           <div class="tfhb-header-filters">
                <input type="text" @keyup="Tfhb_Host_Filter" placeholder="Search by host name" /> 
                <span><Icon name="Search" size=20 /></span>
           </div>
            <div class="thb-admin-btn right">
               <button class="tfhb-btn boxed-btn flex-btn" @click="openModal"><Icon name="PlusCircle" size=20 /> {{ __('Add New Host', 'hydra-booking') }}</button> 
            </div> 
        </div>
        <div class="tfhb-hosts-content">  
            <HbPopup :isOpen="isModalOpened" @modal-close="closeModal" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ __('Add New Host', 'hydra-booking') }}</h2>   
                </template>

                <template #content>  
                    <!-- Select User --> 
                    <HbDropdown    
                        v-model="host.id"  
                        required= "true"  
                        :label="__('Select User', 'hydra-booking')"  
                        selected = "1"
                        :placeholder="__('Select User', 'hydra-booking')" 
                        :option = "usersData.data" 
                    /> 
                    <!-- Select User --> 
                    <!-- UsernName -->
                    <HbText  
                        v-if="host.id == 0"
                        v-model="host.username"  
                        required= "true"  
                        :label="__('Username', 'hydra-booking')"  
                        selected = "1"
                        :placeholder="__('Type Username', 'hydra-booking')"  
                    /> 
                    <!-- UsernName -->
                    <!-- Email -->
                    <HbText  
                    v-if="host.id == 0"
                        v-model="host.email"  
                        required= "true"  
                        type= "email"  
                        :label="__('Email', 'hydra-booking')"  
                        selected = "1"
                        :placeholder="__('Type User Email', 'hydra-booking')"  
                    /> 
                    <!-- Email -->

                    <!-- Password -->
                    
                    <HbText  
                        v-if="host.id == 0"
                        v-model="host.password"  
                        required= "true"  
                        type= "password"  
                        :label="__('Password', 'hydra-booking')"  
                        selected = "1"
                        :placeholder="__('Type User Password', 'hydra-booking')"  
                    /> 
                    <!-- Password -->
                    

                    <!-- Create Or Update Availability -->
                    <button class="tfhb-btn boxed-btn" @click="CreateHosts">{{ __('Create Hosts', 'hydra-booking') }}</button>
                </template> 
            </HbPopup>
             <router-view :host_list="hosts.data" @update-host-status="updateHostStatus" @delete-host="deleteHost" :host_skeleton="skeleton" /> 
        </div> 
    </div>
</template>



<style scoped lang="scss">
/* Your component styles go here */

 
</style>
