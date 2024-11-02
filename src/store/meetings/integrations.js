import { reactive } from 'vue'
import { toast } from "vue3-toastify"; 
import axios from 'axios'  

const IntegrationsValue = reactive({
    integrationsList: true,
    integrationsListopen: false,
    integrationscreate: false,
    dataFields: false,
    meeting: {},
    integrationsData: {
        meeting_id: '',
        title: '',  
        events: '',  
        audience: '',  
        modules: '',  
        tags: '',  
        fields: '',  
        bodys: [
            {
                name: '',
                type: 'Settings',
                value: '',

            }
        ],
        status: '',  
        
    },


    // Meeting List
    async updateIntegrations() {

        // Api Submission
        try { 
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/integration/update', this.integrationsData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            });
            if (response.data.status == true) { 
                toast.success(response.data.message); 
                this.meeting.integrations = response.data.integrations ? JSON.parse(response.data.integrations) : '';

                this.integrationscreate = false;
                this.integrationsList = true;
            }else{
                toast.error(response.data.message); 
            }
        } catch (error) {
            console.log(error);
        } 

    },

     // Meeting List
     async deleteIntegrations(key) {

        const data = {
            key: key,
            meeting_id: this.meeting.id
        };
    
        try { 
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/integration/delete', data, {
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
    
                this.meeting.integrations = response.data.integrations ? JSON.parse(response.data.integrations) : '';
            }
        } catch (error) {
            console.log(error);
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
    async addNewIntegrations (integration){    
        alert(integration);
        this.integrationsList = false;
        this.integrationscreate = true;
        this.integrationsListopen = false;
    
        this.integrationsData.key = '';
        this.integrationsData.title = '';
        this.integrationsData.webhook = integration;
        this.integrationsData.events = '';
        this.integrationsData.audience = '';
        this.integrationsData.tags = '';
        this.integrationsData.lists = '';
        this.integrationsData.modules = '';
        this.integrationsData.fields = '';
        this.integrationsData.status = '';
        this.integrationsData.bodys = [
            {
                'name': '',
                'type': 'Settings',
                'value': ''
            }
        ];
    },
    
    // Filter By Meeting Title
    async backtointegrationsList (filterData){
        this.integrationscreate = false;
        this.integrationsList = true;
    },

    async editIntegrations (data, key){
        this.integrationsData.key = key;
        this.integrationsData.meeting_id = this.meeting.id;
        this.integrationsData.webhook = data.webhook;
        this.integrationsData.title = data.title;
        this.integrationsData.audience = data.audience;
        this.integrationsData.tags = data.tags;
        this.integrationsData.lists = data.lists;
        this.integrationsData.modules = data.modules;
        this.integrationsData.fields = data.fields;
        this.integrationsData.events = data.events;
        this.integrationsData.status = data.status;
        this.integrationsData.bodys = data.bodys;
    
        this.integrationsList = false;
        this.integrationsListopen = false;
        this.integrationscreate = true;
    },

    // Meeting Category

    async updateHookStatus (e, data, key){
        this.integrationsData.key = key;
        this.integrationsData.meeting_id = this.meeting.meetingId;
        this.integrationsData.webhook = data.webhook;
        this.integrationsData.title = data.title;
        this.integrationsData.events = data.events;
        this.integrationsData.audience = data.audience;
        this.integrationsData.tags = data.tags;
        this.integrationsData.lists = data.lists;
        this.integrationsData.modules = data.modules;
        this.integrationsData.fields = data.fields;
        this.integrationsData.status = e.target.checked ? 1 : 0;
        this.integrationsData.bodys = data.bodys;

        this.updateIntegrations();
    },

    async addBodyField (){
        this.integrationsData.bodys.push({
            name: '',
            type: 'Settings',
            value: '',
        });
    },

    async deleteBodyField (key){
        this.integrationsData.bodys.splice(key, 1)
    },

    async BodyValues (key, value){
        if(value!='tfhb_ct'){
            this.integrationsData.bodys[key] = value
        }
    },
    
})

export { IntegrationsValue }