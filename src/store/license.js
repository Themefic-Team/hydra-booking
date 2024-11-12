import { reactive } from 'vue';
import axios from 'axios';
const LicenseBase = reactive({
    license_key: '',
    license_email: '',
    LicenseData: [], 

    async fetchAuth() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking-pro/v1/settings/license', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.Auth = response.data.user;
            }
        } catch (error) {

            console.log(error);

        } 

  
    }, 
    async UpdateLicense() {  
        const data = {
            license_key: this.license_key,
            license_email: this.license_email
        }

        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking-pro/v1/settings/license/update', data, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.Auth = response.data.user;
            }
        } catch (error) {

            console.log(error);

        } 

  
    }, 
})

export { LicenseBase }