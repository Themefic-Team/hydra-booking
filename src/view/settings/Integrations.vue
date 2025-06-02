<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import { toast } from "vue3-toastify"; 
import { useRouter, useRoute, RouterView } from 'vue-router' 

// component
import ZoomIntregration from '@/components/integrations/ZoomIntegrations.vue';
import WooIntegrations from '@/components/integrations/WooIntegrations.vue';
import GoogleCalendarIntegrations from '@/components/integrations/GoogleCalendarIntegrations.vue'; 
import OutlookCalendarIntegrations from '@/components/integrations/OutlookCalendarIntegrations.vue'; 
import AppleCalendarIntegrations from '@/components/integrations/AppleCalendarIntegrations.vue'; 
import StripeIntegrations from '@/components/integrations/StripeIntegrations.vue'; 
import MailchimpIntegrations from '@/components/integrations/MailchimpIntegrations.vue'; 
import PaypalIntegrations from '@/components/integrations/PaypalIntegrations.vue'; 
import CF7Integrations from '@/components/integrations/CF7Integrations.vue'; 
import FluentFormsIntegrations from '@/components/integrations/FluentFormsIntegrations.vue'; 
import ForminatorIntegrations from '@/components/integrations/ForminatorIntegrations.vue'; 
import GravityFormsIntegrations from '@/components/integrations/GravityFormsIntegrations.vue'; 
import WebhookIntegrations from '@/components/integrations/WebhookIntegrations.vue'; 
import FluentCRMIntegrations from '@/components/integrations/FluentCRMIntegrations.vue'; 
import ZohoCRMIntegrations from '@/components/integrations/ZohoCRMIntegrations.vue'; 
import PabblyIntegrations from '@/components/integrations/PabblyIntegrations.vue'; 
import ZapierIntegrations from '@/components/integrations/ZapierIntegrations.vue';
import TelegramIntregration from '@/components/integrations/TelegramIntregrations.vue';
import TwilioIntegration from '@/components/integrations/TwilioIntegrations.vue';
import SlackIntegration from '@/components/integrations/SlackIntegrations.vue';

// import Form Field 
import Icon from '@/components/icon/LucideIcon.vue' 

//  Load Time Zone 
const skeleton = ref(true);
 
 

const popup = ref(false);
const gpopup = ref(false);
const spopup = ref(false);
const mailpopup = ref(false);
const outlookpopup = ref(false);
const paypalpopup = ref(false);
const tpopup = ref(false);
const wpopup = ref(false);
const twpopup = ref(false);
const slpopup = ref(false);

const currentHash = ref('all'); 
 
// tfhb-hydra-admin-tabs a clicked using javascript event
document.addEventListener('click', function (event) {
    if (event.target.matches('.integrations-submenu')) {
        // .tfhb-integrations-settings-menu add class expand
        document.querySelector('.tfhb-integrations-settings-menu').classList.add('expand');

        currentHash.value = event.target.getAttribute('data-filter');
        // this add class active to the clicked element
        document.querySelectorAll('.dropdown a').forEach(function (el) {
            el.classList.remove('active');
            // 
        });
        event.target.classList.add('active');
    }
}, false);

 

 
const isPopupOpen = () => {
    popup.value = true;
}
const isPopupClose = (data) => {
    popup.value = false;
}
const isgPopupOpen = () => {
    gpopup.value = true;
}
const isgPopupClose = (data) => {
    gpopup.value = false;
}
const isOutlookPopupOpen = () => {
    outlookpopup.value = true;
}
const isOutlookPopupClose = (data) => {
    outlookpopup.value = false;
}
const isstripePopupOpen = () => {
    spopup.value = true;
}
const isstripePopupClose = (data) => {
    spopup.value = false;
}

const ismailchimpPopupOpen = () => {
    mailpopup.value = true;
}
const ismailchimpPopupClose = (data) => {
    mailpopup.value = false;
}

const ispaypalPopupOpen = () => {
    paypalpopup.value = true;
}
const ispaypalPopupClose = (data) => {
    paypalpopup.value = false;
}

const istPopupOpen = () => {
    tpopup.value = true;
}
const istPopupClose = (data) => {
    tpopup.value = false;
}

const iswPopupOpen = () => {
    wpopup.value = true;
}
const iswPopupClose = (data) => {
    wpopup.value = false;
}

const istwPopupOpen = () => {
    twpopup.value = true;
}
const istwPopupClose = (data) => {
    twpopup.value = false;
}

const isslPopupOpen = () => {
    slpopup.value = true;
}
const isslPopupClose = (data) => {
    slpopup.value = false;
}

const preloader = ref(false);
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
    telegram : {
        type: 'meeting', 
        status: 1, 
        bot_token: '',
        chat_id: '',
    },
    twilio : {
        type: 'meeting', 
        status: 1, 
        otp_type: 'whatsapp',
        receive_number: '',
        from_number: '',
        sid: '',
        token: '',
    },
    slack : {
        type: 'meeting', 
        status: 1, 
        endpoint: ''
    },
    google_calendar : {
        type: 'calendar', 
        status: 0, 
        connection_status: 0,
        client_id: '',
        secret_key: '',
        redirect_url: '',

    },
    outlook_calendar : {
        type: 'calendar', 
        status: 0, 
        connection_status: 0,
        client_id: '',
        secret_key: '',
        redirect_url: '',

    },
    apple_calendar : {
        type: 'calendar', 
        status: 0,
        connection_status: 0,
    },
    stripe : {
        type: 'stripe', 
        status: 0, 
        public_key: '',
        secret_key: '',
    },
    mailchimp : {
        type: 'mailchimp', 
        status: 0, 
        key: ''
    },
    paypal : {
        type: 'paypal', 
        environment: '',
        status: 0, 
        client_id: '',
        secret_key: '',
    },
    cf7 : {
        type: 'forms', 
        status: 0, 
    },
    fluent : {
        type: 'forms', 
        status: 0, 
    },
    forminator : {
        type: 'forms', 
        status: 0, 
    },
    gravity : {
        type: 'forms', 
        status: 0, 
    },
    webhook : {
        type: 'webhook', 
        status: 0, 
    },
    fluent_crm : {
        type: 'marketing-tools', 
        status: 0, 
    },
    zoho_crm : {
        type: 'marketing-tools', 
        status: 0, 
    },
    pabbly : {
        type: 'marketing-tools', 
        status: 0, 
    },
    zapier : {
        type: 'marketing-tools', 
        status: 0, 
    }
});

//  update Integration

// Fetch generalSettings
const fetchIntegration = async () => {

    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/integration', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        });
        if (response.data.status) { 
            
            // console.log(response.data.integration_settings);
            Integration.zoom_meeting= response.data.integration_settings.zoom_meeting ? response.data.integration_settings.zoom_meeting : Integration.zoom_meeting;
            Integration.woo_payment= response.data.integration_settings.woo_payment ? response.data.integration_settings.woo_payment : Integration.woo_payment;
            Integration.google_calendar= response.data.integration_settings.google_calendar ? response.data.integration_settings.google_calendar : Integration.google_calendar;
            Integration.outlook_calendar= response.data.integration_settings.outlook_calendar ? response.data.integration_settings.outlook_calendar : Integration.outlook_calendar;
            Integration.apple_calendar= response.data.integration_settings.apple_calendar ? response.data.integration_settings.apple_calendar : Integration.apple_calendar;
            Integration.webhook= response.data.integration_settings.webhook ? response.data.integration_settings.webhook : Integration.webhook;
            Integration.fluent_crm= response.data.integration_settings.fluent_crm ? response.data.integration_settings.fluent_crm : Integration.fluent_crm;
            Integration.zoho_crm= response.data.integration_settings.zoho_crm ? response.data.integration_settings.zoho_crm : Integration.zoho_crm;
            Integration.pabbly= response.data.integration_settings.pabbly ? response.data.integration_settings.pabbly : Integration.pabbly;
            Integration.zapier= response.data.integration_settings.zapier ? response.data.integration_settings.zapier : Integration.zapier;

            Integration.stripe= response.data.integration_settings.stripe ? response.data.integration_settings.stripe : Integration.stripe;
            Integration.mailchimp= response.data.integration_settings.mailchimp ? response.data.integration_settings.mailchimp : Integration.mailchimp;
            Integration.paypal= response.data.integration_settings.paypal ? response.data.integration_settings.paypal : Integration.paypal;
            Integration.cf7= response.data.integration_settings.cf7 ? response.data.integration_settings.cf7 : Integration.cf7;
            Integration.fluent= response.data.integration_settings.fluent ? response.data.integration_settings.fluent : Integration.fluent;
            Integration.gravity= response.data.integration_settings.gravity ? response.data.integration_settings.gravity : Integration.gravity;
            Integration.telegram= response.data.integration_settings.telegram ? response.data.integration_settings.telegram : Integration.telegram;
            Integration.twilio= response.data.integration_settings.twilio ? response.data.integration_settings.twilio : Integration.twilio;
            Integration.slack= response.data.integration_settings.slack ? response.data.integration_settings.slack : Integration.slack;

            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}
const UpdateIntegration = async (key, value) => { 
    let data = {
        key: key,
        value: value
    }; 
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/integration/update', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );
    
        if (response.data.status) {    
            
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 

            popup.value = false;
            gpopup.value = false;
            spopup.value = false;
            spopup.value = false;
            tpopup.value = false;
            wpopup.value = false;
            twpopup.value = false;
            mailpopup.value = false;
            paypalpopup.value = false;
            slpopup.value = false;
            
            Integration.zoom_meeting= response.data.integration_settings.zoom_meeting ? response.data.integration_settings.zoom_meeting : Integration.zoom_meeting;
            Integration.woo_payment= response.data.integration_settings.woo_payment ? response.data.integration_settings.woo_payment : Integration.woo_payment;
            Integration.google_calendar= response.data.integration_settings.google_calendar ? response.data.integration_settings.google_calendar : Integration.google_calendar;
            Integration.outlook_calendar= response.data.integration_settings.outlook_calendar ? response.data.integration_settings.outlook_calendar : Integration.outlook_calendar;
            Integration.apple_calendar= response.data.integration_settings.apple_calendar ? response.data.integration_settings.apple_calendar : Integration.apple_calendar;
            Integration.webhook= response.data.integration_settings.webhook ? response.data.integration_settings.webhook : Integration.webhook;
            Integration.fluent_crm= response.data.integration_settings.fluent_crm ? response.data.integration_settings.fluent_crm : Integration.fluent_crm;

            Integration.stripe= response.data.integration_settings.stripe ? response.data.integration_settings.stripe : Integration.stripe;
            Integration.mailchimp= response.data.integration_settings.mailchimp ? response.data.integration_settings.mailchimp : Integration.mailchimp;
            Integration.paypal= response.data.integration_settings.paypal ? response.data.integration_settings.paypal : Integration.paypal;
            Integration.telegram= response.data.integration_settings.telegram ? response.data.integration_settings.telegram : Integration.telegram;
            Integration.twilio= response.data.integration_settings.twilio ? response.data.integration_settings.twilio : Integration.twilio;
            Integration.slack= response.data.integration_settings.slack ? response.data.integration_settings.slack : Integration.slack;
            
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });

            popup.value = false;
            gpopup.value = false;
            outlookpopup.value = false;
            tpopup.value = false;
            wpopup.value = false;
            twpopup.value = false;
            slpopup.value = false;
        }
    } catch (error) {
        // toast.error('Action successful', {
        //     position: 'bottom-right', // Set the desired position
        // });
    }
}
onBeforeMount(() => {  
    fetchIntegration();
    // if currentHash == all 
   
});

</script>
<template>
    
    <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard "> 
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('Integrations') }}</h1> 
                <p>{{ $tfhb_trans('Configure integration for conferencing, calendar and payment') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div>
         <nav v-if="$front_end_dashboard == true" class="tfhb-booking-tabs tfhb-integrations-settings-menu"> 
            <ul>
                <!-- to route example like hosts/profile/13/information -->
                
                <li><router-link to="/settings/integrations#all" :class="{ 'active': $route.hash === '#all' }" class="integrations-submenu" data-filter="all"> <Icon name="GalleryVerticalEnd" /> {{ $tfhb_trans('All') }}</router-link></li>
                            
                <li><router-link to="/settings/integrations#conference" :class="{ 'active': $route.hash === '#conference' }" class="integrations-submenu" data-filter="conference"> <Icon name="Video" /> {{ $tfhb_trans('Conference') }}</router-link></li>

                <li><router-link to="/settings/integrations#calendars" :class="{ 'active': $route.hash === '#calendars' }" class="integrations-submenu" data-filter="calendars"> <Icon name="CalendarDays" /> {{ $tfhb_trans('Calendars') }}</router-link></li>

                <li><router-link to="/settings/integrations#payments" :class="{ 'active': $route.hash === '#payments' }" class="integrations-submenu" data-filter="payments"> <Icon name="HandCoins" /> {{ $tfhb_trans('Payments') }}</router-link></li> 

                <li><router-link to="/settings/integrations#marketing-tools" :class="{ 'active': $route.hash === '#marketing-tools' }" class="integrations-submenu" data-filter="marketing-tools"> <Icon name="BadgePercent" /> {{ $tfhb_trans('Marketing Tools') }}</router-link></li> 
                <li><router-link to="/settings/integrations#forms" :class="{ 'active': $route.hash === '#forms' }" class="integrations-submenu" data-filter="forms"> <Icon name="BookText" /> {{ $tfhb_trans('Forms') }}</router-link></li> 
               
            </ul>  
        </nav>
        <div class="tfhb-content-wrap"> 
            <!-- {{ Integration }} -->
            <div class="tfhb-integrations-wrap tfhb-flexbox">

                <!-- Woo  Integrations  -->
                
                <WooIntegrations :woo_payment="Integration.woo_payment" @update-integrations="UpdateIntegration" v-if="currentHash === 'all' || currentHash === 'payments'"/>

                <!-- Woo Integrations  -->

                <!-- zoom intrigation -->
                <ZoomIntregration 
                :zoom_meeting="Integration.zoom_meeting"  
                @update-integrations="UpdateIntegration" 
                :ispopup="popup"
                @popup-open-control="isPopupOpen"
                @popup-close-control="isPopupClose"
                v-if="currentHash === 'all' || currentHash === 'conference'"
                />
                <!-- zoom intrigation -->

                <!-- Telegram intrigation -->
                <TelegramIntregration 
                :telegram_data="Integration.telegram"  
                @update-integrations="UpdateIntegration" 
                :ispopup="tpopup"
                @popup-open-control="istPopupOpen"
                @popup-close-control="istPopupClose"
                v-if="currentHash === 'all' || currentHash === 'conference'"
                />
                <!-- Telegram intrigation -->
                
                <!-- TwilioIntegration intrigation -->
                <TwilioIntegration 
                :twilio_data="Integration.twilio"  
                @update-integrations="UpdateIntegration" 
                :ispopup="twpopup"
                @popup-open-control="istwPopupOpen"
                @popup-close-control="istwPopupClose"
                v-if="currentHash === 'all' || currentHash === 'conference'"
                />
                <!-- TwilioIntegration intrigation -->

                <!-- SlackIntegration intrigation -->
                <SlackIntegration 
                :slack_data="Integration.slack"  
                @update-integrations="UpdateIntegration" 
                :ispopup="slpopup"
                @popup-open-control="isslPopupOpen"
                @popup-close-control="isslPopupClose"
                v-if="currentHash === 'all' || currentHash === 'conference'"
                />
                <!-- SlackIntegration intrigation -->

                <!-- Google Calendar intrigation -->
                <GoogleCalendarIntegrations 
                :google_calendar="Integration.google_calendar" 
                @update-integrations="UpdateIntegration"
                :ispopup="gpopup"
                @popup-open-control="isgPopupOpen"
                @popup-close-control="isgPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'calendars'"
                />
                <!-- Google Calendar intrigation -->
                 
                <!-- Outlook intrigation -->
                <OutlookCalendarIntegrations 
                :outlook_calendar="Integration.outlook_calendar" 
                @update-integrations="UpdateIntegration"
                :ispopup="outlookpopup"
                @popup-open-control="isOutlookPopupOpen"
                @popup-close-control="isOutlookPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'calendars'"
                />
                <!-- Outlook intrigation -->

                <!-- Apple intrigation -->
                <!-- <AppleCalendarIntegrations 
                :apple_calendar="Integration.apple_calendar" 
                @update-integrations="UpdateIntegration"
                :ispopup="outlookpopup" 
                v-if="currentHash === 'all' || currentHash === 'calendars'"
                /> -->
                <!-- Apple intrigation -->

                <!-- stripe intrigation -->
                <StripeIntegrations 
                :stripe_data="Integration.stripe" 
                @update-integrations="UpdateIntegration" 
                :ispopup="spopup"
                @popup-open-control="isstripePopupOpen"
                @popup-close-control="isstripePopupClose" 
                v-if="currentHash === 'all' || currentHash === 'payments'"
                />
                <!-- stripe intrigation -->

              

                <!-- paypal intrigation -->
                <PaypalIntegrations 
                :paypal_data="Integration.paypal" 
                @update-integrations="UpdateIntegration" 
                :ispopup="paypalpopup"
                @popup-open-control="ispaypalPopupOpen"
                @popup-close-control="ispaypalPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'payments'"
                />
                <!-- paypal intrigation -->

                
                <!-- CF7 -->
                <CF7Integrations 
                :cf7_data="Integration.cf7" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                />
                <!-- CF7 -->

                <!-- Fluent -->
                <FluentFormsIntegrations 
                :fluent_data="Integration.fluent" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                />
                <!-- CF7 -->

                <!-- Forminator -->
                <!-- <ForminatorIntegrations 
                :forminator_data="Integration.forminator" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                /> -->
                <!-- CF7 -->

                <!-- gravity -->
                <GravityFormsIntegrations 
                :gravity_data="Integration.gravity" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                />
                <!-- gravity -->

                <!-- webhook -->
                <WebhookIntegrations 
                :webhook_data="Integration.webhook" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'marketing-tools'"
                />
                <!-- webhook -->
          
                <!-- Mailchimp intrigation -->
                <MailchimpIntegrations 
                :mail_data="Integration.mailchimp" 
                @update-integrations="UpdateIntegration" 
                :ispopup="mailpopup"
                @popup-open-control="ismailchimpPopupOpen"
                @popup-close-control="ismailchimpPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'marketing-tools'"
                />
                <!-- Mailchimp intrigation -->

                <!-- Fluent CRM -->
                <FluentCRMIntegrations 
                :fluent_crm_data="Integration.fluent_crm" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'marketing-tools'"
                />
                <!-- Fluent CRM -->
                
                <!-- Zoho CRM -->
                <ZohoCRMIntegrations 
                :zoho_crm_data="Integration.zoho_crm" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'marketing-tools'"
                />
                <!-- Zoho CRM -->

                <!-- Pabbly -->
                <PabblyIntegrations 
                :pabbly_data="Integration.pabbly" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'marketing-tools'"
                />
                <!-- Pabbly -->

                <!-- Zapier -->
                <ZapierIntegrations 
                :zapier_data="Integration.zapier" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'marketing-tools'"
                />
                <!-- Zapier -->
          

            </div> 


        </div>
    </div>
 
</template>