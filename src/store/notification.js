import { reactive } from 'vue';
import axios from 'axios';
const Notification = reactive({
    Data: {}, 

    async fetchNotifications() { 

        try {  
            const response = await axios.get(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/notifaction', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.Data = response.data.notifications;
            }
        } catch (error) {

            console.log(error);

        } 

        // try {
        //     const response = await fetch(apiUrl, {
        //         method: 'GET',
        //         credentials: 'include', // Include cookies for authentication
        //     });
        //     if (!response.ok) {
        //         throw new Error('Network response was not ok');
        //     }
        //     const data = await response.json(); 
        //     this.Auth =  data;
        // } catch (error) {
        //     console.error('Error fetching Hosts:', error);
        // }
    }, 
})

export { Notification }