import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const AddonsAuth = reactive({
    skeleton: true, 
    update_preloader: false, 
    loggedInUser: {},
    event_settings: {},
    event: {},
    // Other Information 
    async fetchLoggedInUser() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/logged-in-user', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                params: {
                    user_id: tfhb_core_apps.user.id
                },
                withCredentials: true
            });
            if (response.data.success && response.data.data) {
                this.loggedInUser = response.data.data;
            }
        } catch (e) {
            // handle error
        } finally {
            this.skeleton = false;
        }
    },
    async FetchSettings() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/all-settings', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                }, 
                withCredentials: true
            });
            if (response.data.success && response.data.data) { 
                this.event_settings = response.data.data.event_settings;
                this.event = response.data.data.event;
            }
        } catch (e) {
            // handle error
        } finally {
            this.skeleton = false;
        }
    }
    
    
})

export { AddonsAuth };