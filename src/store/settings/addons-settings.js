import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const AddonsSettings = reactive({
    skeleton: true, 
    update_preloader: false, 
    meeting_list : {},
    event_settings: {
        meeting_id : 0, 
        live_chat_url : '', 
        guest_booking : false, 
    },
    Sellers: {
        registration_froms_fields : [],
        registration_start_date : '',
        registration_end_date : '',
        enable_registration : 0,
        default_account_status : 'active',
        badge_pdf_image : '',
    },
    buyers: {
        registration_froms_fields : [],
        registration_start_date : '',
        registration_end_date : '',
        enable_registration : 0,
        default_account_status : 'active',
        badge_pdf_image : '',
    },
    Exhibitors: {
        registration_froms_fields : [],
        registration_start_date : '',
        registration_end_date : '',
        enable_registration : 0,
        default_account_status : 'active',
        badge_pdf_image : '',
    },
    // Matching Settings
    matching_settings: {
        matching_start_date : '',
        matching_rules: [
            {
                buyer_field: '',
                seller_field: '',
                priority: 1
            }
        ]
    },
    // Other Information 
    async FetchAddonsSettings() {  
        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/settings', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } ); 
            if (response.data.status) {  
                this.meeting_list = response.data.meeting_list ? response.data.meeting_list : this.meeting_list; 
                this.Sellers = response.data.sellers_settings ? response.data.sellers_settings : this.Sellers; 
                this.buyers = response.data.buyers_settings ? response.data.buyers_settings : this.buyers; 
                this.Exhibitors = response.data.exhibitors_settings ? response.data.exhibitors_settings : this.Exhibitors; 
                this.event_settings = response.data.event_settings ? response.data.event_settings : this.event_settings; 
                this.matching_settings = response.data.matching_settings ? response.data.matching_settings : this.matching_settings; 
                this.skeleton = false;
            }
        } catch (error) { 
            console.log(error); 
        }  
    }, 
    async UpdateSellersSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/sellers-settings/update', this.Sellers, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );

            if (response.data.status) {   
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
    async UpdateBuyersSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/buyers-settings/update', this.buyers, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );

            if (response.data.status) {   
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
    async UpdateExhibitorsSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/exhibitors-settings/update', this.Exhibitors, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );

            if (response.data.status) {   
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
    async UpdateEventsSettings() { 
        this.update_preloader = true; 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/events-settings/update', this.event_settings, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );

            if (response.data.status) {   
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
    // Matching Settings API Methods
    async FetchMatchingSettings() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/matching-settings', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            });
            
            if (response.data.status) {
                this.matching_settings = response.data.settings ? response.data.settings : this.matching_settings;
                return response.data;
            }
        } catch (error) {
            console.log(error);
            throw error;
        }
    },
    async UpdateMatchingSettings() {
        this.update_preloader = true;
        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/matching-settings/update', this.matching_settings, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            });

            if (response.data.status) {
                toast.success(response.data.message || 'Matching rules saved successfully', {
                    position: "bottom-right",
                });
                this.update_preloader = false;
                return response.data;
            }
        } catch (error) {
            console.log(error);
            this.update_preloader = false;
            toast.error(error.response?.data?.message || 'Failed to save matching settings', {
                position: "bottom-right",
            });
            throw error;
        }
    },
    // Helper methods for matching rules
    addMatchingRule() {
        this.matching_settings.matching_rules.push({
            buyer_field: '',
            seller_field: '',
            priority: 1
        });
    },
    removeMatchingRule(index) {
        this.matching_settings.matching_rules.splice(index, 1);
    },
    updateMatchingRule(index, rule) {
        this.matching_settings.matching_rules[index] = rule;
    },
    getBuyerFields() {
        return this.buyers.registration_froms_fields
            .filter(field => field.enable !== 0)
            .map(field => ({
                name: field.name,
                label: field.label,
                type: field.type,
                options: field.options || []
            }));
    },
    getSellerFields() {
        return this.Sellers.registration_froms_fields
            .filter(field => field.enable !== 0)
            .map(field => ({
                name: field.name,
                label: field.label,
                type: field.type,
                options: field.options || []
            }));
    },
    // Format meeting list for dropdown
    formatMeetingList(meetings) {
        if (!Array.isArray(meetings)) {
            return [];
        }
        return meetings.map(meeting => ({
            name: `Title: ${meeting.title || meeting.name || 'Untitled'} - ID: ${meeting.id || meeting.ID}`,
            value: meeting.id || meeting.ID
        }));
    },
})

export { AddonsSettings }