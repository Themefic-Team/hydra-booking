import { reactive } from 'vue';
import axios from 'axios';
import { toast } from "vue3-toastify"; 
const LicenseBase = reactive({
    license_key: '',
    skeleton: true,
    license_email: '',
    LicenseData: {
        is_valid : false,
    }, 

    async GetLicense() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking-pro/v1/settings/license', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.data.status) {  

                this.LicenseData = response.data.data.data;
                this.license_key = response.data.data.license_key;
                this.license_email = response.data.data.license_email;
                this.skeleton = false;

            }else{
                this.skeleton = false;
            }
        } catch (error) {

            console.log(error);

        } 

  
    }, 
    async UpdateLicense() {  
        this.skeleton = true;
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
    
            console.log(response.data.data.message);
            if (response.data.data.status) {  
                this.LicenseData = response.data.data.data;
                this.license_key = response.data.data.license_key;
                this.license_email = response.data.data.license_email; 
                // possition bottom
       
                toast.success(response.data.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                window.location.reload();
                // this.skeleton = false;


            }else{
                toast.error(response.data.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {

            console.log(error);

        } 

  
    }, 

    async DeactivateLicense() {
        this.skeleton = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking-pro/v1/settings/license/deactivate', {}, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.data.status) {  
                this.LicenseData =  response.data.data.data;
                this.license_key = '';
                this.license_email = ''; 
                // possition bottom
       
                toast.success(response.data.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                // this.skeleton = false;
                window.location.reload();

            }else{
                toast.error(response.data.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {

            console.log(error);

        } 
    }
})

export { LicenseBase }