<script setup>
import { __ } from '@wordpress/i18n';
import { ref, toRef, watch, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import { RouterView } from 'vue-router' 
import { toast } from "vue3-toastify"; 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbInfoBox from '../widgets/HbInfoBox.vue';
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

const props = defineProps([
    'class', 
    'display', 
    'hubspot_data', 
    'pre_loader', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const hubspot_data = toRef( props, 'hubspot_data' );
const shouldRedirectAfterUpdate = ref(false);
const pendingClientId = ref('');
const previousAuthorizeUrl = ref('');

watch(
    () => [hubspot_data.value?.client_id, hubspot_data.value?.authorize_url],
    () => {
        const newData = hubspot_data.value;
        if (!shouldRedirectAfterUpdate.value || !newData) {
            return;
        }

        // Only redirect for the same client id that triggered the update.
        if (newData.client_id != pendingClientId.value) {
            return;
        }

        if (newData.authorize_url == '' || newData.authorize_url == null) {
            return;
        }

        // Prevent redirecting with the stale URL that existed before the update.
        if (newData.authorize_url == previousAuthorizeUrl.value) {
            return;
        }

        shouldRedirectAfterUpdate.value = false;
        pendingClientId.value = '';
        previousAuthorizeUrl.value = '';

        if (newData.authorize_url != '' && newData.authorize_url != null) {
            RedirectToHubspotAuthUrl(newData.authorize_url);
            return;
        }

        toast.error((tfhb_core_apps.trans['Unable to generate HubSpot authorize URL. Please try again.'] || 'Unable to generate HubSpot authorize URL. Please try again.'), {
            position: 'bottom-right',
            duration: 2000
        });
    },
    { deep: false }
);

const closePopup = () => { 
    shouldRedirectAfterUpdate.value = false;
    pendingClientId.value = '';
    previousAuthorizeUrl.value = '';
    emit('popup-close-control', false);
}

const RedirectToHubspotAuthUrl = (url) => {
    window.open(url, '_blank');
}

const RemoveIntegration = (type) => { 
    shouldRedirectAfterUpdate.value = false;
    pendingClientId.value = '';
    previousAuthorizeUrl.value = '';
    emit('update-integrations', type, {
        status: 0,
        connection_status: 0,
        auth_data: null,
        authorize_url: hubspot_data.value.authorize_url
    })
}
const UpdateHubspotData = () => { 
    // if client id is empty then show error toaster message
    if(hubspot_data.value.client_id == '' || hubspot_data.value.client_id == null){
        toast.error( (tfhb_core_apps.trans['Client ID is required'] || 'Client ID is required') , {
            position: 'bottom-right',
            duration: 2000
        });
        return;
    }
    if(hubspot_data.value.client_secret == '' || hubspot_data.value.client_secret == null){
        toast.error( (tfhb_core_apps.trans['Client Secret is required'] || 'Client Secret is required') , {
            position: 'bottom-right',
            duration: 2000
        });
        return;
    }
 
    shouldRedirectAfterUpdate.value = true;
    pendingClientId.value = hubspot_data.value.client_id;
    previousAuthorizeUrl.value = hubspot_data.value.authorize_url ? String(hubspot_data.value.authorize_url) : '';
    const payload = {
        ...hubspot_data.value,
    };

    emit('update-integrations', 'hubspot', payload);
    // after 1 sec
    setTimeout(() => {
        closePopup();
        // redirect ot auth url
        RedirectToHubspotAuthUrl(props.hubspot_data.authorize_url);
    }, 1000);
}

const copyRedirectionURL = () => {
    const textarea = document.createElement('textarea');
    textarea.value = props.hubspot_data.redirect_url;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    toast.success( props.hubspot_data.redirect_url + ' is Copied' , {
        position: 'bottom-right',
        duration: 2000
    });
}

</script>

<template> 
      <!-- HubSpot Integrations  -->
      <div   class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class, {
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >  
        <span v-if="$tfhb_is_pro == false ||  $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
         
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/hubspot-icon.svg'" alt="">
            </span>  
            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('HubSpot') }}</h3>
                <p>{{ $tfhb_trans('Integrate HubSpot API to collect attendee emails and info.') }}</p>
            </div>
        </div>
        <div v-if="!$tfhb_is_pro || !$tfhb_license_status" class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <router-link to="/settings/license" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upgrade to Pro') }}  <Icon name="ChevronRight" size=18 /></router-link>
        </div>
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <button  v-if=" props.from == 'host' && hubspot_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Connected') }} </button>
            <router-link v-else-if="props.from == 'host' && hubspot_data.connection_status != 1"  to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <HbButton  
                v-else @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="props.hubspot_data.status == 1 && Number(hubspot_data.connection_status) == 1 ? 'Connected' : 'Connect' " 
                :hover_animation="false"    
            /> 

            <HbSwitch
            v-if="hubspot_data.authorize_url != '' &&  hubspot_data.authorize_url  != null " 
                @change="emit('update-integrations', 'hubspot', hubspot_data)" v-model="hubspot_data.status"   
            />
        </div>
 

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header>
                <h2>{{ $tfhb_trans('Connect Your HubSpot API') }}</h2> 
            </template>
           
            <template #content>   
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from HubSpot Account') }}

                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/hubspot-integration/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                </p>  
                <div v-if="Number(hubspot_data.connection_status) != 1 &&  props.from != 'host'"  style="width: 100%;">
                    <div clas="tfhb-flexbox tfhb-gap-12 tfhb-flex-direction-colum">
                        <HbText  
                            v-model="hubspot_data.client_id"  
                            required= "true"  
                            name="client_id"
                            :errors="errors.client_id"  
                            :label="$tfhb_trans('Client ID')"  
                            selected = "1"
                            :placeholder="$tfhb_trans('Enter Client ID')"  
                        />  
                        <HbText  
                            v-model="hubspot_data.client_secret"  
                            required= "true"  
                            name="client_secret"
                            :errors="errors.client_secret"  
                            :label="$tfhb_trans('Client Secret')"  
                            selected = "1"
                            :placeholder="$tfhb_trans('Enter Client Secret')"  
                        />  
                        <div class="tfhb-google-calender-redirection-url tfhb-full-width"  >
                            <HbText  
                                v-model="hubspot_data.redirect_url"  
                                required= "true"
                                :readonly="true"
                                name="redirect_url"
                                :errors="errors.redirect_url"  
                                :label="$tfhb_trans('Redirect Url')"  
                                selected = "1" 
                                :placeholder="$tfhb_trans('Enter Redirect Url')"  
                            /> 
                            <HbButton 
                                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                                @click="copyRedirectionURL()" 
                                :buttonText="$tfhb_trans('Copy URL')" 
                            /> 
                        </div>
                    </div>
                    <div style="margin-top: 20px; display: inline-block;" class="tfhb-alert tfhb-alert-success" > 
                        <a @click.prevent="UpdateHubspotData" class="connect-with-hubspot tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Connect with HubSpot') }}</a> 
                    </div>
                </div>
                <div v-else-if="props.from == 'host' && (hubspot_data.auth_data == null || hubspot_data.auth_data.length === 0)  " class="tfhb-alert tfhb-alert-success">
                    <a :href="hubspot_data.authorize_url" target="_blank" class="connect-with-hubspot tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Connect with HubSpot') }}</a> 
                </div>

                <div v-else class="tfhb-flexbox tfhb-gap-12 tfhb-flex-direction-column"> 
                     <HbInfoBox  name="first-modal">
                        <template #content>
                            <div class="tfhb-flexbox tfhb-justify-between tfhb-align-center">
                                <span>
                                    {{ $tfhb_trans('HubSpot Configuration is complete.') }}
                                </span>  
                            </div>
                        </template>
                    </HbInfoBox>

                    <HbButton   
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8"  
                        :buttonText="'Disconnect HubSpot'"
                        @click="RemoveIntegration('hubspot')"
                        icon="ChevronRight"  
                    />   
                </div>
            </template> 
        </HbPopup>
    </div>  
</template>

<style scoped>
.connect-with-hubspot:hover {
  color: #fff !important;
}
</style> 