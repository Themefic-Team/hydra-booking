import { reactive } from 'vue';
import axios from 'axios';
const Notification = reactive({
    Data: {}, 
    total_unread: 0, 

    async fetchNotifications() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/notifaction', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.Data = response.data.notifications;
                this.total_unread = response.data.total_unread;
            }
        } catch (error) {

            console.log(error);

        } 

       
    }, 

    async MarkAsRead() {
        alert(1);
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/notifaction/markasread', this.Data, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.total_unread = 0;
            }
        } catch (error) {

            console.log(error);

        }
    }, 
})

export { Notification }