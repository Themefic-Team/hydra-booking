import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const AddonsSettings = reactive({
    skeleton: true, 
    update_preloader: false, 
    event_settings: {
        guest_booking : false, 
    },
    Sellers: {
        registration_froms_fields : [],
        registration_start_date : '',
        registration_end_date : '',
        enable_registration : 0,
        default_account_status : 'active',
    },
    buyers: {
        registration_froms_fields : [],
        registration_start_date : '',
        registration_end_date : '',
        enable_registration : 0,
        default_account_status : 'active',
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
                this.Sellers = response.data.sellers_settings ? response.data.sellers_settings : this.Sellers; 
                this.buyers = response.data.buyers_settings ? response.data.buyers_settings : this.buyers; 
                this.event_settings = response.data.event_settings ? response.data.event_settings : this.event_settings; 
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
    async UpdateBuyersSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/buyers-settings/update', this.buyers, {
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
    async UpdateEventsSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/events-settings/update', this.event_settings, {
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