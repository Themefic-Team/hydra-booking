import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

export const FdDashboard = reactive({
    skeleton: true,
    update_preloader: true, 
    userAuth: {},

    // Other Information 
    async FetchUserAuth() {   
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/fd-dashboard/user-auth', {
               userAuthData: tfhb_core_apps.user 
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                this.userAuth = response.data.userAuth; 
                this.skeleton = false;
                 
            }
        } catch (error) { 
            console.log(error);
            this.skeleton = false;
        }  
    }, 

    // Log out User
    async logoutUser() {  
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/fd-dashboard/logout', {
                userAuth: this.userAuth 
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {   
                // redirect to login page
                window.location.href = response.data.redirect;
                 
            }
        } catch (error) { 
            console.log(error);
        }
    },
})
 