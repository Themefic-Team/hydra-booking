<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import { useRouter, useRoute, RouterView } from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import { setupWizard } from '@/store/setupWizard';
import useValidators from '@/store/validator';
const router = useRouter();

const { errors } = useValidators();

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
import HbButton from '@/components/form-fields/HbButton.vue';
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';  

// Toast
import { toast } from "vue3-toastify"; 
//  Load Time Zone 
const skeleton = ref(true);
const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 



const popup = ref(false);
const gpopup = ref(false);
const spopup = ref(false);
const mailpopup = ref(false);
const outlookpopup = ref(false);
const paypalpopup = ref(false);

const currentHash = ref('all'); 
 
const selectedFilterIntegrations = (e) => {
    // remove all display none css 
    document.querySelectorAll('.tfhb-integrations-single-block').forEach((integration) => {
        integration.style.display = '';
    });
    currentHash.value = e.target.getAttribute('data-filter');

    // change the label
    document.querySelector('.tfhb-s-w-integrations-dropdown span').innerText = e.target.innerText;
}

const FilterBySearch = (e) => {
    // show only input thext mathc besd on .tfhb-integrations-single-block h3 text
    let search = e.target.value;
    currentHash.value = 'all';
    document.querySelector('.tfhb-s-w-integrations-dropdown span').innerText = 'All Integrations';
    //
    let integrations = document.querySelectorAll('.tfhb-integrations-single-block');
    integrations.forEach((integration) => {
        let title = integration.querySelector('h3').innerText;
        if (title.toLowerCase().indexOf(search.toLowerCase()) > -1) {
            integration.style.display = '';
        } else {
            integration.style.display = 'none';
        }
    });
    //

 
}
 
 
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
const submit_preloader = ref(false);
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
        type: 'others', 
        status: 0, 
    },
    fluent_crm : {
        type: 'others', 
        status: 0, 
    },
    zoho_crm : {
        type: 'others', 
        status: 0, 
    }
});

//  update Integration

// Fetch generalSettings
const fetchIntegration = async () => {

    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/integration',
        {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );
        if (response.data.status) { 
            
            // console.log(response.data.integration_settings);
            Integration.zoom_meeting= response.data.integration_settings.zoom_meeting ? response.data.integration_settings.zoom_meeting : Integration.zoom_meeting;
            Integration.woo_payment= response.data.integration_settings.woo_payment ? response.data.integration_settings.woo_payment : Integration.woo_payment;
            Integration.google_calendar= response.data.integration_settings.google_calendar ? response.data.integration_settings.google_calendar : Integration.google_calendar;
            Integration.outlook_calendar= response.data.integration_settings.outlook_calendar ? response.data.integration_settings.outlook_calendar : Integration.outlook_calendar;
            Integration.apple_calendar= response.data.integration_settings.apple_calendar ? response.data.integration_settings.apple_calendar : Integration.apple_calendar;

            Integration.stripe= response.data.integration_settings.stripe ? response.data.integration_settings.stripe : Integration.stripe;
            Integration.mailchimp= response.data.integration_settings.mailchimp ? response.data.integration_settings.mailchimp : Integration.mailchimp;
            Integration.paypal= response.data.integration_settings.paypal ? response.data.integration_settings.paypal : Integration.paypal;

            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}

const UpdateIntegration = async (key, value, validator_field) => { 
     // Clear the errors object
     Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
   
   
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!Integration[key][fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!Integration[key][fieldParts[0]][fieldParts[1]]){
                errors[fieldParts[0]+'___'+[fieldParts[1]]] = 'Required this field';
            }
        }
            
        });
    }

    // Errors Checked
    const isEmpty = Object.keys(errors).length === 0;
    if(!isEmpty){ 
        toast.error('Fill Up The Required Fields', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return
    }
    submit_preloader.value = true;
    let data = {
        key: key,
        value: value
    }; 
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/integration/update', data, {
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

            popup.value = false;
            gpopup.value = false;
            spopup.value = false;
            spopup.value = false;
            mailpopup.value = false;
            paypalpopup.value = false;
            
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });

            popup.value = false;
            gpopup.value = false;
            outlookpopup.value = false;
        }
        submit_preloader.value = false;
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}
onBeforeMount(() => {   
    fetchIntegration();
    // if currentHash == all 
   
});

const activeDropdown = ref(false);

const toggleDropdown = () => {
    
    activeDropdown.value = !activeDropdown.value;
}

const pre_loader = ref(false);
const gotoDashboard = () => {
    
    // weit for 2 sec
    pre_loader.value = true;
    setTimeout(() => {
        pre_loader.value = false;
        router.push({ name: 'dashboard' });
        props.setupWizard.currentStep = 'getting-start'
    }, 500);  
    
}

window.addEventListener('click', function(e) { 
    if( props.setupWizard.currentStep == 'step-end' ){
        if (!document.querySelector('.tfhb-s-w-integrations-dropdown').contains(e.target)) {
            
            activeDropdown.value = 0;
        }
    }
    
});
</script>

<template>
 

    <!-- Step End -->
    <div  class="tfhb-setup-wizard-content-wrap tfhb-hydra-dasboard-content tfhb-s-w-step-end tfhb-flexbox">
        <div class="tfhb-s-w-icon-text">
            <img :src="$tfhb_url+'/assets/images/hydra-booking-logo.png'" alt="">
            <h2>{{ $tfhb_trans('Congratulations! You are All Set Up!') }}</h2>
            <p>{{ $tfhb_trans('You have successfully installed and activated Hydrabooking, configured your settings, connected your calendar, customized your booking forms, and embedded them on your website.') }}</p> 
       
        </div>
        <HbInfoBox :isblocked="true">
            <template #content>
                {{ $tfhb_trans('You’re currently using HydraBooking in limited mode. To access advanced features, provide your license key now!') }} 
            </template>
        </HbInfoBox>
        <div class="tfhb-s-w-step-end tfhb-flexbox tfhb-full-width">

            <div class="tfhb-s-w-integrations-bar tfhb-flexbox  tfhb-justify-between">
                <div  @click.stop="toggleDropdown"  class="tfhb-s-w-integrations-dropdown tfhb-dropdown tfhb-flexbox tfhb-gap-8 ">
                    <span class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('All Integrations') }} 
                        <Icon v-if="activeDropdown == false" @click.stop="toggleDropdown"  name="ChevronDown" size=20 /> 
                        <Icon v-else name="ChevronUp"  @click.stop="toggleDropdown"  size=20 /> 
                    </span> 
                     
                    <transition name="tfhb-dropdown-transition">
                        <div v-show="activeDropdown == true" class="tfhb-dropdown-wrap"> 
                            <span @click="selectedFilterIntegrations" class="tfhb-dropdown-single" data-filter="all"> {{ $tfhb_trans('All Integrations') }} </span>
                            <span @click="selectedFilterIntegrations"  class="tfhb-dropdown-single" data-filter="conference"> {{ $tfhb_trans('Conference') }}</span>
                            <span @click="selectedFilterIntegrations" class="tfhb-dropdown-single" data-filter="calendars"> {{ $tfhb_trans('Calendars') }}</span>
                            <span @click="selectedFilterIntegrations" class="tfhb-dropdown-single" data-filter="payments"> {{ $tfhb_trans('Payments') }}</span>
                            <span @click="selectedFilterIntegrations" class="tfhb-dropdown-single" data-filter="forms">{{ $tfhb_trans('Forms') }} </span>
                        </div>
                    </transition>
                </div>
                <div class="tfhb-integrations-searchbar">
                    <input @keyup="FilterBySearch" type="text" :placeholder="$tfhb_trans('Search Integrations')">
                    <Icon name="Search" size=20 /> 
                </div>
            </div>

            <div class="tfhb-flexbox tfhb-s-w-integrations-wrap">

                <!-- Woo  Integrations  -->

                <WooIntegrations
                display="list" class="tfhb-flexbox tfhb-host-integrations"
                :woo_payment="Integration.woo_payment" :pre_loader="submit_preloader" @update-integrations="UpdateIntegration" v-if="currentHash === 'all' || currentHash === 'payments'"/>

                <!-- Woo Integrations  -->

                <!-- zoom intrigation -->
                <ZoomIntregration display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :zoom_meeting="Integration.zoom_meeting" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration" 
                :ispopup="popup"
                @popup-open-control="isPopupOpen"
                @popup-close-control="isPopupClose"
                v-if="currentHash === 'all' || currentHash === 'conference'"
                />
                <!-- zoom intrigation -->

                <!-- zoom intrigation -->
                <GoogleCalendarIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :google_calendar="Integration.google_calendar"
                :pre_loader="submit_preloader"  
                @update-integrations="UpdateIntegration"
                :ispopup="gpopup"
                @popup-open-control="isgPopupOpen"
                @popup-close-control="isgPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'calendars'"
                />
                <!-- zoom intrigation -->
                
                <!-- Outlook intrigation -->
                <OutlookCalendarIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :outlook_calendar="Integration.outlook_calendar" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"
                :ispopup="outlookpopup"
                @popup-open-control="isOutlookPopupOpen"
                @popup-close-control="isOutlookPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'calendars'"
                />
                <!-- Outlook intrigation -->

                <!-- Apple intrigation -->  
                <!-- Apple intrigation -->

                <!-- stripe intrigation -->
                <StripeIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :stripe_data="Integration.stripe" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration" 
                :ispopup="spopup"
                @popup-open-control="isstripePopupOpen"
                @popup-close-control="isstripePopupClose" 
                v-if="currentHash === 'all' || currentHash === 'payments'"
                />
                <!-- stripe intrigation -->
 

                <!-- paypal intrigation -->
                <PaypalIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :paypal_data="Integration.paypal" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration" 
                :ispopup="paypalpopup"
                @popup-open-control="ispaypalPopupOpen"
                @popup-close-control="ispaypalPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'payments'"
                />
                <!-- paypal intrigation -->

               <!-- CF7 -->
               <CF7Integrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :cf7_data="Integration.cf7" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                />
                <!-- CF7 -->

                <!-- Fluent -->
                <FluentFormsIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :fluent_data="Integration.fluent" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                />
                <!-- CF7 -->

                
                <!-- CF7 -->

                <!-- gravity -->
                <GravityFormsIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :gravity_data="Integration.gravity" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'forms'"
                />
                <!-- gravity -->

                <!-- webhook -->
                <WebhookIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :webhook_data="Integration.webhook" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'others'"
                />
                <!-- webhook -->
          
                <!-- Mailchimp intrigation -->
                <MailchimpIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :mail_data="Integration.mailchimp" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration" 
                :ispopup="mailpopup"
                @popup-open-control="ismailchimpPopupOpen"
                @popup-close-control="ismailchimpPopupClose" 
                v-if="currentHash === 'all' || currentHash === 'all'"
                />
                <!-- Mailchimp intrigation -->

                <!-- Fluent CRM -->
                <FluentCRMIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :fluent_crm_data="Integration.fluent_crm" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'others'"
                />
                <!-- Fluent CRM -->
                
                <!-- Zoho CRM -->
                <ZohoCRMIntegrations display="list" class="tfhb-flexbox tfhb-host-integrations  tfhb-justify-between"
                :zoho_crm_data="Integration.zoho_crm" 
                :pre_loader="submit_preloader" 
                @update-integrations="UpdateIntegration"   
                v-if="currentHash === 'all' || currentHash === 'others'"
                />
                <!-- Zoho CRM -->
                
                

            </div> 

            
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox">
             
             <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 icon-left tfhb-icon-hover-animation" 
                @click="gotoDashboard" 
                :buttonText="$tfhb_trans('Visit Dashboard')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"  
                :pre_loader="pre_loader" 
            /> 
        </div>
     </div>
     <!-- Step End-->


</template>
 