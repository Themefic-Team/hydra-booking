import { reactive } from 'vue'
import { toast } from "vue3-toastify"; 
import axios from 'axios'  

const Meeting = reactive({
    meetings: [],
    meetingCategory: [],
    meetingPaymentIntegration: {
        woo_payment: true,
        paypal: true, 
        stripe: true,
    },

    // Meeting List
    async fetchMeetings() {

        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/lists', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) { 
            this.meetings = response.data.meetings;
        }

    },

     // Meeting List
     async fetchMeetingsPaymentIntegration() {

        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/payment/payment-method', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {  
            this.meetingPaymentIntegration = response.data.integrations;
        }

    },

    // Delete Meeting
    async deleteMeeting ($id, $post_id){ 
        if($id == '' || $post_id == ''){
            toast.error('Something went wrong. Please try again', {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            return;
        }
        let deleteMeeting = {
            id: $id,
            post_id: $post_id
        }
        try { 
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/delete', deleteMeeting, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
            if (response.data.status) { 
                this.meetings = response.data.meetings;    
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {
            console.log(error);
        }
    },

    // Popup Meeting Creation
    async CreatePopupMeeting (type, routes){   
        console.log(routes);
        let TypeData = {
            data: type
        }
        try { 
             // axisos sent dataHeader Nonce Data
             const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/create', TypeData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
             } );
     
             if (response.data.status) {  
                 toast.success(response.data.message, {
                     position: 'bottom-right', // Set the desired position
                     "autoClose": 1500,
                 });   
                routes.push({ name: 'MeetingsCreate', params: { id: response.data.id} });
             }else{
                 toast.error(response.data.message, {
                     position: 'bottom-right', // Set the desired position
                     "autoClose": 1500,
                 });
             }
         } catch (error) {
             console.log(error);
         }   
    },
    
    // Filter By Meeting Title
    async Tfhb_Meeting_Filter (filterData){
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/filter', {
                params: {
                    filterData
                },
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            
            if (response.data.status) { 
                this.meetings = response.data.meetings;  
            }
    
        } catch (error) {
            console.error('Error:', error);
        }
    },

    async Tfhb_Meeting_Select_Filter (filterData){
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/filter', {
                params: {
                    filterData
                },
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            
            if (response.data.status) { 
                this.meetings = response.data.meetings;  
            }
    
        } catch (error) {
            console.error('Error:', error);
        }
    },

    // Meeting Category

    async fetchMeetingCategory (){
        try { 
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/categories', {
                    headers: {
                        'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                        'capability': 'tfhb_manage_options'
                    } 
                 }
            );
            if (response.data.status) { 
                this.meetingCategory = response.data.category;  
            }
        } catch (error) {
            console.log(error);
        } 
    } 
    
})

export { Meeting }