<script setup>
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter, useRoute, RouterView } from 'vue-router' 
import axios from 'axios'  
import { toast } from "vue3-toastify"; 

// Get Current Route url
const currentRoute = useRouter().currentRoute.value.path;
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import ZoomIntregration from '@/components/integrations/ZoomIntegrations.vue';
import ZohoIntegrations from '@/components/hosts/ZohoIntegrations.vue';
import MailchimpIntegrations from '@/components/integrations/MailchimpIntegrations.vue'; 
import TelegramIntregration from '@/components/integrations/TelegramIntregrations.vue';
import TwilioIntegration from '@/components/integrations/TwilioIntegrations.vue';
import SlackIntegration from '@/components/integrations/SlackIntegrations.vue'

const route = useRoute();
const router = useRouter();
//  Load Time Zone 
const skeleton = ref(true);
const props = defineProps({
    hostId: {
        type: Number,
        required: true
    },
    host: {
        type: Object,
        required: true
    },
    time_zone:{},
    settings_zoho:{
        type: Object,
        required: true,
    }

});


const popup = ref(false);
const paypalpopup = ref(false);
const spopup = ref(false);
const zohopopup = ref(false);
const mailpopup = ref(false);
const telepopup = ref(false);
const twpopup = ref(false);
const skpopup = ref(false);
const isPopupOpen = () => {
    popup.value = true;
}
const isPopupClose = (data) => {
    popup.value = false;
}
const istwPopupOpen = () => {
    twpopup.value = true;
}
const istwPopupClose = (data) => {
    twpopup.value = false;
}
const isZohoPopupOpen = () => {
    zohopopup.value = true;
}
const isZohoPopupClose = (data) => {
    zohopopup.value = false;
}
const istelePopupOpen = () => {
    telepopup.value = true;
}
const istelePopupClose = (data) => {
    telepopup.value = false;
}
const isskPopupOpen = () => {
    skpopup.value = true;
}
const isskPopupClose = (data) => {
    skpopup.value = false;
}
const ismailchimpPopupOpen = () => {
    mailpopup.value = true;
}
const ismailchimpPopupClose = (data) => {
    mailpopup.value = false;
}


const Integration = reactive( {
    woo_payment : {
        type: 'payment', 
        status: 0, 
        connection_status: 0,  
    },
    zoom_meeting : {
        type: 'meeting', 
        status: 0, 
        connection_status: 0,
        account_id: '',
        app_client_id: '',
        app_secret_key: '',

    },
    google_calendar : {
        type: 'meeting', 
        status: 0, 
        connection_status: 0, 
        selected_calendar_id: '', 
        tfhb_google_calendar: {},

    },
    outlook_calendar : {
        type: 'meeting', 
        status: 0, 
        connection_status: 0, 
        selected_calendar_id: '', 
        tfhb_outlook_calendar: {},

    },
    apple_calendar : {
        type: 'calendar', 
        status: 0,
        connection_status: 0, 
        apple_id: '',
        app_password: '',

    },
    mailchimp : {
        type: 'mailchimp', 
        status: 0, 
        connection_status: 0, 
        key: ''
    },
    zoho : {
        type: 'zoho', 
        status: 0, 
        client_id: '',
        client_secret: '',
        redirect_url: '',
        access_token: '',
        refresh_token: '',
        modules: ''
    },
    telegram : {
        type: 'telegram', 
        status: 0, 
        connection_status: 0, 
        bot_token: '',
        chat_id: '',
    },
    twilio : {
        type: 'twilio', 
        status: 0, 
        connection_status: 0, 
        otp_type: 'whatsapp',
        receive_number: '',
        from_number: '',
        sid: '',
        token: '',
    },
    slack : {
        type: 'slack', 
        status: 0, 
        connection_status: 0, 
        endpoint: '',
    },
});
 

 // Fetch generalSettings
const fetchIntegration = async () => { 
    let host_id = route.params.id != undefined? route.params.id : props.hostId; 
    let data = {
        id: host_id,
        user_id: props.host.user_id,
    };  
    try { 

        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/integration', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );

        if (response.data.status) {   
            Integration.zoom_meeting= response.data.zoom_meeting ? response.data.zoom_meeting : Integration.zoom_meeting;
            Integration.google_calendar= response.data.google_calendar ? response.data.google_calendar : Integration.google_calendar;  
            Integration.outlook_calendar = response.data.outlook_calendar  ? response.data.outlook_calendar  : Integration.outlook_calendar ;  
            Integration.apple_calendar = response.data.apple_calendar  ? response.data.apple_calendar  : Integration.apple_calendar ;  
            Integration.mailchimp = response.data.mailchimp  ? response.data.mailchimp  : Integration.mailchimp ;  
            Integration.zoho = response.data.zoho  ? response.data.zoho  : Integration.zoho ; 
            Integration.telegram = response.data.telegram  ? response.data.telegram  : Integration.telegram ; 
            Integration.twilio = response.data.twilio  ? response.data.twilio  : Integration.twilio ; 
            Integration.slack = response.data.slack  ? response.data.slack  : Integration.slack ; 
            

            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}
const UpdateIntegration = async (key, value) => { 
    let host_id = route.params.id != undefined? route.params.id : props.hostId; 
    let data = {
        key: key,
        value: value,
        id: host_id,
        user_id: props.host.user_id,
    };  
  
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/integration/update', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );

        if (response.data.status) {  
              
            Integration.zoom_meeting = response.data.host_integration_settings.zoom_meeting  ? response.data.host_integration_settings.zoom_meeting : Integration.zoom_meeting; 
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
            
            popup.value = false;
            paypalpopup.value = false;
            spopup.value = false;
            telepopup.value = false;
            twpopup.value = false;
            mailpopup.value = false;
            zohopopup.value = false;
            skpopup.value = false;
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });
        }
    } catch (error) {
        toast.error(error.message, {
            position: 'bottom-right', // Set the desired position
        });
    }
}
onBeforeMount(() => {  
    fetchIntegration();
});
</script>

<template>
 
    <HbInfoBox v-if="$user.role != 'tfhb_host'" name="first-modal">
        
        <template #content>
            <span>{{$tfhb_trans('Before connecting make sure you provide the necessary credentials to')}} 
                <HbButton 
                        classValue="tfhb-btn" 
                        @click="() => router.push({ name: 'SettingsIntegrations' })" 
                        buttonText="Settings  Integrations"
                    />  
            </span>
            
        </template>
    </HbInfoBox>
            
    <div class="tfhb-admin-card-box tfhb-m-0">    
        <!-- Woo  Integrations  --> 
        <ZoomIntregration display="list" class="tfhb-flexbox tfhb-host-integrations tfhb-justify-between" 
        :zoom_meeting="Integration.zoom_meeting"  
        @update-integrations="UpdateIntegration"
        from="host"
        :ispopup="popup"
        @popup-open-control="isPopupOpen"
        @popup-close-control="isPopupClose" 
        />


 

        <!-- Mailchimp intrigation -->
        <MailchimpIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations tfhb-justify-between"  
        :mail_data="Integration.mailchimp" 
        @update-integrations="UpdateIntegration" 
        from="host"
        :ispopup="mailpopup"
        @popup-open-control="ismailchimpPopupOpen"
        @popup-close-control="ismailchimpPopupClose" 
        />
        <!-- Mailchimp intrigation -->

        <!-- Zoho intrigation -->
        <ZohoIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations tfhb-justify-between"  
        :zoho_data="Integration.zoho"  
        :zoho_crm_status="settings_zoho.zoho_crm_status"  
        @update-integrations="UpdateIntegration" 
        from="host"
        :ispopup="zohopopup"
        :host_id="props.hostId"
        @popup-open-control="isZohoPopupOpen"
        @popup-close-control="isZohoPopupClose" 
        />
        <!-- Zoho intrigation -->

        <!-- telegram intrigation -->
        <TelegramIntregration display="list" class="tfhb-flexbox tfhb-host-integrations tfhb-justify-between"  
        :telegram_data="Integration.telegram" 
        @update-integrations="UpdateIntegration" 
        from="host"
        :ispopup="telepopup"
        @popup-open-control="istelePopupOpen"
        @popup-close-control="istelePopupClose" 
        />
        <!-- telegram intrigation -->

        <!-- twilio intrigation -->
        <TwilioIntegration display="list" class="tfhb-flexbox tfhb-host-integrations tfhb-justify-between"  
        :twilio_data="Integration.twilio" 
        @update-integrations="UpdateIntegration" 
        from="host"
        :ispopup="twpopup"
        @popup-open-control="istwPopupOpen"
        @popup-close-control="istwPopupClose" 
        />
        <!-- twilio intrigation -->

        <!-- slack intrigation -->
        <SlackIntegration display="list" class="tfhb-flexbox tfhb-host-integrations tfhb-justify-between"  
        :slack_data="Integration.slack" 
        @update-integrations="UpdateIntegration" 
        from="host"
        :ispopup="skpopup"
        @popup-open-control="isskPopupOpen"
        @popup-close-control="isskPopupClose" 
        />
        <!-- slack intrigation -->

    </div> 
</template>


 