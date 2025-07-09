import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const AddonsSettings = reactive({
    skeleton: true, 
    update_preloader: false, 
    Sellers: {
        registration_froms_fields : [],
        registration_start_date : '',
        registration_end_date : '',
        enable_registration : 0,
    },
    // Other Information 
    async FetchAddonsSettings() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/settings', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) { 
                // response.data.settings not empty then set value
                this.fd_dashboard.general =  response.data.settings.general ? response.data.settings.general : this.fd_dashboard.general;
                this.fd_dashboard.signup =  response.data.settings.signup ? response.data.settings.signup : this.fd_dashboard.signup;
                this.fd_dashboard.login =  response.data.settings.login ? response.data.settings.login : this.fd_dashboard.login;
                this.pages = response.data.pages; 
                this.skeleton = false;
                 
            }
        } catch (error) {

            console.log(error);

        }  
    }, 
    async UpdateSellersSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/sellers-settings/update', this.Sellers, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );

            if (response.data.status) {   
                

                this.skeleton = false;
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

export { AddonsSettings }