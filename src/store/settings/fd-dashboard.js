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
            colors_palette : 'default',
            primery_default: '#2E6B38',
            primery_hover: '#4C9959',
            secondary_default: '#273F2B',
            secondary_hover: '#E1F2E4',
            text_title: '#141915',
            surface_primary: '#F9FBF9',
            surface_background: '#C0D8C4', 
            surface_border: '#C0D8C4', 
            surface_border_hover: '#211319', 
            surface_input_field: '#56765B',
        }, 
        signup: {
            registration_page: '',
            signup_page_title: '',
            signup_page_sub_title: '',
            after_registration_redirect_type: '',
            after_registration_redirect: '',
            after_registration_redirect_custom: '',
        },
        login: {
            login_page_title: '',
            login_page_sub_title: '',
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