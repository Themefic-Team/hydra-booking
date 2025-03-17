import { reactive } from 'vue';
import axios from 'axios';
import { toast } from "vue3-toastify"; 
const LicenseBase = reactive({
    license_key: '',
    skeleton: true,
    License_loader: false,
    license_email: '',
    license_active: false,
    license_type: 'free',
    LicenseData: {
        is_valid : false,
    }, 

    async GetLicense() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/license', {
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

                const licenseTitle = response.data.data.data.license_title || "";
                this.license_type = licenseTitle.toLowerCase().includes("free") ? 'free' : 'pro';
                this.license_active = true;
            }else{
                this.skeleton = false;
            }
        } catch (error) {
            this.skeleton = false;
            console.log(error);

        } 

  
    }, 
    async UpdateLicense() {  
        this.skeleton = true;
        this.License_loader = true;
        const data = {
            license_key: this.license_key,
            license_email: this.license_email
        }

        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/license/update', data, {
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
                
                const licenseTitle = response.data.data.data.license_title || "";
                this.license_type = licenseTitle.toLowerCase().includes("free") ? 'free' : 'pro';
                this.license_active = true;
       
                toast.success(response.data.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                // window.location.reload();
                // this.skeleton = false;


            }else{
                toast.error(response.data.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                 this.skeleton = false;
            }
            this.License_loader = false;

        } catch (error) {
            this.License_loader = false;
            console.log(error);

        } 

  
    }, 

    async DeactivateLicense() {
        this.skeleton = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/license/deactivate', {}, {
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