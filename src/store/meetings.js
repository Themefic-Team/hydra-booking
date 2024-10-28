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
    singleMeeting: {
        MeetingData: {
            id: 0,
            user_id: 0,
            slug: '',
            host_id: '',
            post_id: '',
            title: '',
            description: '',
            meeting_type: '',
            duration: '',
            custom_duration: 5,
            meeting_locations: [
                {
                location:'',
                address:''
                }
            ],
            meeting_category: '',
            availability_range_type: 'indefinitely',
            availability_range: {
                start: '',
                end: ''
            },
            availability_type: 'settings',
            availability_id : '',
            availability_custom: 
                {
                title: '',
                time_zone: '',
                date_status: 0,
                time_slots: [
                    { 
                        day: 'Monday',
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            },   
                        ]
                    },
                    { 
                        day: 'Tuesday', 
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            }
                        ]
                    },
                    { 
                        day: 'Wednesday', 
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            }
                        ]
                    },
                    { 
                        day: 'Thursday', 
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            }
                        ]
                    },
                    { 
                        day: 'Friday', 
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            }
                        ]
                    },
                    { 
                        day: 'Saturday', 
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            }
                        ]
                    },
                    { 
                        day: 'Sunday', 
                        status: 1,
                        times: [
                            {
                                start: '09:00',
                                end: '17:00',
                            }
                        ]
                    }
                ],
                date_slots: [
                ]
            },
            buffer_time_before: '5',
            buffer_time_after: '5',
            booking_frequency: [
                {
                    limit: 5,
                    times:'days'
                }
            ],
            meeting_interval: '10',
            recurring_status: 1,
            recurring_repeat:[
                {
                    limit: 1,
                    times:'Year'
                }
            ],
            recurring_maximum: '',
            attendee_can_cancel: 1,
            attendee_can_reschedule: 1, 
            questions_type: 'custom',
            form_type: '',
            form_id: '',
            questions: [
                {
                    label: 'name',
                    type:'Text',
                    placeholder:'Name',
                    options: [],
                    required: 1
                },
                {
                    label: 'email',
                    type:'Email',
                    options: [],
                    placeholder:'Email',
                    options: [],
                    required: 1
                },
                {
                    label: 'address',
                    type:'Text', 
                    placeholder:'Address',
                    options: [],
                    required: 1
                }
            ],
            notification: {
                host: {
                    booking_confirmation: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                    booking_cancel: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                    booking_reschedule: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                    booking_reminder: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                },
                attendee : {
                    booking_confirmation: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
                    },
                    booking_cancel: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                    booking_reschedule: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                    booking_reminder: {
                        status : 0,
                        template : 'default',
                        form : '',
                        subject : '',
                        body : '',
        
                    },
                }
            },
            payment_status: 1,
            meeting_price: '',
            payment_currency: '',
            payment_method: '',
            permalink	: '',
            payment_meta: {
                product_id: '',
            },
            webhook: '',
            integrations: '',
            max_book_per_slot: 1,
            is_display_max_book_slot: 0,
            mailchimp: '',
            fluentcrm: '',
            zohocrm: ''
        },
        integrations: {},
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
    async fetchSingleMeeting(meetingId){
        try { 
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/'+meetingId, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            if (response.data.status == true) { 
                // this.singleMeeting.MeetingData = response.data.meeting;


                // if(response.data.meeting.meeting_locations == null){
                //     this.singleMeeting.MeetingData.meeting_locations = [];
                //     this.singleMeeting.MeetingData.meeting_locations.push({
                //         location: '',
                //         address: ''
                //     });


                // }else{
                //     this.singleMeeting.MeetingData.meeting_locations = JSON.parse(response.data.meeting.meeting_locations)
                // }

                this.singleMeeting.MeetingData.id = response.data.meeting.id
                this.singleMeeting.MeetingData.meeting_type = response.data.meeting.meeting_type
             
                this.singleMeeting.MeetingData.webhook = response.data.meeting.webhook ? JSON.parse(response.data.meeting.webhook) : '';
                this.singleMeeting.MeetingData.integrations = response.data.meeting.integrations ? JSON.parse(response.data.meeting.integrations) : '';
                this.singleMeeting.MeetingData.mailchimp = response.data.mailchimp ? response.data.mailchimp : '';
                this.singleMeeting.MeetingData.fluentcrm = response.data.fluentcrm ? response.data.fluentcrm : '';
                this.singleMeeting.MeetingData.zohocrm = response.data.zohocrm ? response.data.zohocrm : '';
                this.singleMeeting.MeetingData.permalink	= response.data.meeting.permalink ? response.data.meeting.permalink : '';

                this.singleMeeting.integrations.google_calendar_status = response.data.integrations.google_calendar_status && response.data.integrations.google_calendar_status == 1 ? false : true;  
                this.singleMeeting.integrations.zoom_meeting_status = response.data.integrations.zoom_meeting_status && response.data.integrations.zoom_meeting_status == 1  ? false : true;  
                this.singleMeeting.integrations.cf7_status = response.data.integrations.cf7_status && response.data.integrations.cf7_status == 1  ? false : true;  
                this.singleMeeting.integrations.fluent_status = response.data.integrations.fluent_status && response.data.integrations.fluent_status == 1  ? false : true;  
                this.singleMeeting.integrations.forminator_status = response.data.integrations.forminator_status && response.data.integrations.forminator_status == 1  ? false : true;  
                this.singleMeeting.integrations.gravity_status = response.data.integrations.gravity_status && response.data.integrations.gravity_status == 1  ? false : true;  
                this.singleMeeting.integrations.webhook_status = response.data.integrations.webhook_status && response.data.integrations.webhook_status == 1  ? false : true;  
                this.singleMeeting.integrations.fluent_crm_status = response.data.integrations.fluent_crm_status && response.data.integrations.fluent_crm_status == 1  ? false : true;  
                this.singleMeeting.integrations.zoho_crm_status = response.data.integrations.zoho_crm_status && response.data.integrations.zoho_crm_status == 1  ? false : true;  
     
                // skeleton.value = false
            }else{ 
                router.push({ name: 'MeetingsLists' });
            }
        } catch (error) {
            console.log(error);
        } 
    },

    async updateSingleMeetingData(routes){
         // Api Submission
        try { 
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/details/update', this.singleMeeting.MeetingData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            if (response.data.status == true) { 
                this.singleMeeting.MeetingData.slug = response.data.meeting.slug; 
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                routes.push({ name: 'MeetingsCreate', params: { id: response.data.meeting.id} });
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
                routes.push({ name: 'MeetingsCreateSingle', params: { id: response.data.id} });
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