import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const hostsSettings = reactive({
    skeleton: true,
    update_preloader: false,
    settings: {
        others_information: {
            enable_others_information: false, 
            fields : []
        }, 
        permission: {
            tfhb_manage_dashboard: true,
            tfhb_manage_meetings: true,
            tfhb_manage_booking: true,
            tfhb_manage_settings: false, 
            tfhb_manage_custom_availability: true,
            tfhb_manage_integrations: true,

        }
    }, 
    // Other Information 
    async fetchHostsSettings() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/hosts-settings', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) { 
                if(response.data.hosts_settings){

                    this.settings.others_information.enable_others_information = response.data.hosts_settings.others_information.enable_others_information;
                    this.settings.others_information.fields = response.data.hosts_settings.others_information.fields ? response.data.hosts_settings.others_information.fields :   this.settings.others_information.fields;
                    this.settings.permission = response.data.hosts_settings.permission;
                   
                }
                this.skeleton = false;
                 
            }
        } catch (error) {

            console.log(error);

        }  
    },
    async updateHostsSettings() { 
        this.update_preloader = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/hosts-settings/update', {
                hosts_settings: this.settings
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) {   
                
                // responsed message bottom
                toast.success(response.data.message, {
                    position: "bottom-right",
                });
                this.update_preloader = false;
            }
        } catch (error) {
            console.log(error);
            this.update_preloader = false;

        }  
    },
})

export { hostsSettings }