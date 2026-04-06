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
    'aweber_data', 
    'pre_loader', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const aweber_data = toRef( props, 'aweber_data' );
const shouldRedirectAfterUpdate = ref(false);
const pendingClientId = ref('');

watch(
    () => aweber_data.value,
    (newData) => {
        if (!shouldRedirectAfterUpdate.value || !newData) {
            return;
        }

        // Only redirect for the same client id that triggered the update.
        if (newData.client_id != pendingClientId.value) {
            return;
        }

        shouldRedirectAfterUpdate.value = false;
        pendingClientId.value = '';

        if (newData.authorize_url != '' && newData.authorize_url != null) {
            RedirectToAweberAuthUrl(newData.authorize_url);
            return;
        }

        toast.error('Unable to generate AWeber authorize URL. Please try again.', {
            position: 'bottom-right',
            duration: 2000
        });
    },
    { deep: false }
);

const closePopup = () => { 
    shouldRedirectAfterUpdate.value = false;
    pendingClientId.value = '';
    emit('popup-close-control', false);
}

const RedirectToAweberAuthUrl = (url) => {
    window.open(url, '_blank');
}

const RemoveIntegration = (type) => { 
    shouldRedirectAfterUpdate.value = false;
    pendingClientId.value = '';
    emit('update-integrations', type, {
        status: 0,
        connection_status: 0,
        auth_data: null,
        authorize_url: aweber_data.value.authorize_url
    })
}
const UpdateAweberData = () => { 
    // if client id is empty then show error toster message
    if(aweber_data.value.client_id == '' || aweber_data.value.client_id == null){
        toast.error( 'Client ID is required' , {
            position: 'bottom-right', // Set the desired position
            duration: 2000 // Set the desired duration
        });
        return;
    }

    shouldRedirectAfterUpdate.value = true;
    pendingClientId.value = aweber_data.value.client_id;

    emit('update-integrations', 'aweber', aweber_data.value);
}

const copyRedirectionURL = () => {
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = props.aweber_data.redirect_url;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action 
    // success mess into bottom right
    toast.success( props.aweber_data.redirect_url + ' is Copied' , {
        position: 'bottom-right', // Set the desired position
        duration: 2000 // Set the desired duration
    });
}

</script>

<template> 
      <!-- Mailchimp Integrations  -->
      <div   class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class, {
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >  
        <span v-if="$tfhb_is_pro == false ||  $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
         
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Awever.svg'" alt="">
            </span>  
            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('AWeber') }}</h3>
                <p>{{ $tfhb_trans('Integrate AWeber API to collect attendee emails and info.') }}</p>
            </div>
        </div>
        <div v-if="!$tfhb_is_pro || !$tfhb_license_status" class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <router-link to="/settings/license" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upgrade to Pro') }}  <Icon name="ChevronRight" size=18 /></router-link>
 
            <!-- Swicher --> 
        </div>
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <button  v-if=" props.from == 'host' && aweber_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Connected') }} </button>
            <router-link v-else-if="props.from == 'host' && aweber_data.connection_status != 1"  to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <HbButton  
                v-else @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="props.aweber_data.status == 1 && Number(aweber_data.connection_status) == 1 ? 'Connected' : 'Connect' " 
                :hover_animation="false"    
            /> 

            <HbSwitch
            v-if="aweber_data.authorize_url != '' &&  aweber_data.authorize_url  != null " 
                @change="emit('update-integrations', 'aweber', aweber_data)" v-model="aweber_data.status"   
            />
            <!-- Swicher --> 
        </div>
 

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header>
                <h2>{{ $tfhb_trans('Connect Your AWeber API') }}</h2> 
            </template>
           
            <template #content>   
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from AWeber Account') }}

                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/aweber/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                </p>  
                <div v-if="Number(aweber_data.connection_status) != 1 &&  props.from != 'host'"  style="width: 100%;">
                    <div clas="tfhb-flexbox tfhb-gap-12 tfhb-flex-direction-colum">
                        <HbText  
                            v-model="aweber_data.client_id"  
                            required= "true"  
                            name="client_id"
                            :errors="errors.client_id"  
                            :label="$tfhb_trans('Client ID')"  
                            selected = "1"
                            :placeholder="$tfhb_trans('Enter Client ID')"  
                        />  
                        <div class="tfhb-google-calender-redirection-url tfhb-full-width"  >
                            <HbText  
                                v-model="aweber_data.redirect_url"  
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
                          
                        <!-- add button connect with api -->
                        <a @click="UpdateAweberData" target="_blank" class="connect-with-aweber tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Connect with AWeber') }}</a> 
                
                    </div>
                </div>
                <div v-else-if="props.from == 'host' && (aweber_data.auth_data == null || aweber_data.auth_data.length === 0)  " class="tfhb-alert tfhb-alert-success">
                    <!-- add button connect with api -->
                    <a :href="aweber_data.authorize_url" target="_blank" class="connect-with-aweber tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Connect with AWeber') }}</a> 
                
                </div>

                <div v-else class="tfhb-flexbox tfhb-gap-12 tfhb-flex-direction-column"> 
                     
                     <HbInfoBox  name="first-modal">
        
                        <template #content>
                            <div class="tfhb-flexbox tfhb-justify-between tfhb-align-center">
                                <span>
                                    {{ $tfhb_trans('AWeber Configuration is complete.') }}
                                </span>  
                            </div>
                        </template>
                    </HbInfoBox>

                    <HbButton   
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8"  
                        :buttonText="'Disconnect AWeber'"
                        @click="RemoveIntegration('aweber')"
                        icon="ChevronRight"  
                    />   

                </div>

            </template> 
        </HbPopup>


    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
.connect-with-aweber:hover {
  color: #fff !important;
}
</style> 