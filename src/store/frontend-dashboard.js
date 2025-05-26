import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

export const FdDashboard = reactive({
    skeleton: true,
    update_preloader: true, 
    user_info_update_preloader: false, 
    reset_password_preloader: false,
    disable_personal_info: true,
    disable_password: true,
    userAuth: {
        others_information: {},
    },
    site_settings: {},
    time_zone: {},
    hosts_settings: {},
    pass_data: {
        old_password: '',
        new_password: '',
        confirm_password: ''
    },

    // Other Information 
    async FetchUserAuth() {   
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/fd-dashboard/user-auth', {
               userAuthData: tfhb_core_apps.user 
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) {  
                this.userAuth = response.data.userAuth; 
                this.site_settings = response.data.site_settings; 
                this.time_zone = response.data.time_zone;  
                this.userAuth.others_information = response.data.userAuth.others_information != '[]' && response.data.userAuth.others_information != null ? JSON.parse(response.data.userAuth.others_information) : {};
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

    // update user profile
    async updateUserProfile() {  
        this.user_info_update_preloader = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/fd-dashboard/update-profile', {
                userAuth: this.userAuth, 
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) {   
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                }); 
                this.skeleton = false;
                this.user_info_update_preloader = false;
                this.disable_personal_info = true;
                 
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                this.user_info_update_preloader = false;
                this.skeleton = false;
            }

        } catch (error) { 
            console.log(error);
            this.skeleton = false;
        }
    },

    // Change Password
    async changePassword() {  
        this.user_info_update_preloader = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/fd-dashboard/change-password', {
                userAuth: this.userAuth, 
                pass_data: this.pass_data
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) {   
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                }); 
                this.skeleton = false;
                this.user_info_update_preloader = false;
                this.disable_personal_info = true;
                 
            }else{

                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                this.user_info_update_preloader = false;
                this.skeleton = false;
            }

        } catch (error) {

            console.log(error);
            this.skeleton = false;
        }
    },
})
 