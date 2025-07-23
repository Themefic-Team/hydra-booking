import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const AddonsAuth = reactive({
    skeleton: true, 
    update_preloader: false, 
    loggedInUser: {},
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
    }
    
})

export { AddonsAuth };