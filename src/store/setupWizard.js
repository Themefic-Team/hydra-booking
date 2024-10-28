import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const setupWizard = reactive({
    skeleton: true,
    // currentStep: 'step-end',
    time_zone: {},
    pre_loader: 'false',
    currentStep: 'getting-start',
    data: {
        email: '',
        enable_recevie_updates: 1,
        business_type : '',
        meeting : {},
        availabilityDataSingle: { 
            id: 0,
            title: '',
            time_zone: '',
            date_status: 0,
            default_status: true,
            time_slots: [

                { 
                    day: 'Sunday', 
                    status: 1,
                    times: [
                        {
                            start: '09:00',
                            end: '17:00',
                        }
                    ]
                },
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
                }
            ],
            date_slots: [
            ]
        }
    }, 

    // Other Information 
    async fetchSetupWizard() {   
        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/setup-wizard/fetch', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.time_zone = response.data.time_zone;
                this.data.email = response.data.user_email;
                 
            }
        } catch (error) {

            console.log(error);

        }  
    },


    // Other Information 
    async importDemoMeeting() {   
        alert
        let import_demo_class = document.querySelector('.tfhb-import-demo');
        // this.pre_loader = 'true';
        {}
        // add class disable
        import_demo_class.classList.add('disable');
        return;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/setup-wizard/import-meeting', this.data, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                this.pre_loader = 'false';
                this.data.meeting = response.data.meeting;
                this.currentStep = 'step-four'; 
            }
        } catch (error) {

            console.log(error);

        }  
    }
})

export { setupWizard }