import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const FrontendDashboard = reactive({
    skeleton: true,
    update_preloader: false, 
    pages: [],
    fd_dashboard: {
        general: {
            dashboard_logo: '',  
            mobile_dashboard_logo: '', 
            primery_default: '#2E6B38',
            primery_hover: '#17030C',
            secondary_default: '#273F2B',
            secondary_hover: '#E1F2E4',
            text_title: '#141915',
            text_paragraph: '#273F2B',
        }, 
        signup: {
            registration_page: '',
            after_registration_redirect_type: '',
            after_registration_redirect: '',
            after_registration_redirect_custom: '',
        },
        login: {
            login_page: '',
            after_login_redirect_type: '',
            after_login_redirect: '',
            after_login_redirect_custom: '',
        },
    }, 
    // Other Information 
    async fetchFrontendDashboardSettings() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/fd-dashboard', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                // response.data.settings not empty then set value
                this.fd_dashboard.general =  response.data.settings.general ? response.data.settings.general : this.fd_dashboard.general;
                this.fd_dashboard.signup =  response.data.settings.signup ? response.data.settings.signup : this.fd_dashboard.signup;
                this.fd_dashboard.login =  response.data.settings.login ? response.data.settings.login : this.fd_dashboard.login;
                this.pages = response.data.pages; 
                this.skeleton = false;
                 
            }
        } catch (error) {

            console.log(error);

        }  
    },
    async updateFrontendDashboardSettings() { 
        this.update_preloader = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/fd-dashboard/update', {
                fd_dashboard: this.fd_dashboard
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {   
                

                this.skeleton = false;
                // responsed message bottom
                toast.success(response.data.message, {
                    position: "bottom-right",
                });
                this.update_preloader = false;
            }
        } catch (error) {
            console.log(error);
            this.update_preloader = false;

        }  
    },
})

export default FrontendDashboard