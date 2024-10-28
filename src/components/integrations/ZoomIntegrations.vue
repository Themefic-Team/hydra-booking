<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import { RouterView } from 'vue-router' 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

const zoomPopup = ref(false);

const zoom_integration = reactive(  { 
    status: 0, 
    connection_status: 0,   
});

const props = defineProps([
    'class', 
    'display', 
    'zoom_meeting', 
    'Integration', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template>
      <!-- Zoom Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Zoom.png'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('Zoom', 'hydra-booking') }}</h3>
                <p>{{ __('Connect Zoom API to configure virtual meetings.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <!-- <span  v-if=" props.from == 'host' && zoom_meeting.connection_status != '1'" class="tfhb-badge tfhb-badge-not-connected">{{ __('Not Configured', 'hydra-booking') }}  </span> -->
            
            <router-link  v-if=" props.from == 'host' && zoom_meeting.connection_status != '1'" to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ __('Go To Settings', 'hydra-booking') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>
           

            <button v-else @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ zoom_meeting.connection_status == 1 ? 'Connected' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button>
            <!-- Checkbox swicher --> 
            <HbSwitch v-if="zoom_meeting.connection_status" @change="emit('update-integrations', 'zoom_meeting', zoom_meeting)" v-model="zoom_meeting.status"    /> 
            <!-- Swicher --> 
        </div>
        <!-- <Transition name="zoom-in"> -->
            <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ __('Add New Zoom User Account', 'hydra-booking') }}</h2>
                    
                </template>

                <template #content>  
                    <p>
                        {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Zoom Account', 'hydra-booking') }}
                    </p>
                    <HbText  
                        v-model="zoom_meeting.account_id"  
                        required= "true"  
                        name="account_id"
                        :errors="errors.account_id"
                        :label="__('Zoom Account ID', 'hydra-booking')"  
                        selected = "1"
                        :placeholder="__('Enter Your Account ID', 'hydra-booking')"  
                    /> 
                    <HbText  
                        v-model="zoom_meeting.app_client_id"  
                        required= "true"  
                        name="app_client_id"
                        :errors="errors.app_client_id"
                        :label="__('Zoom App Client ID', 'hydra-booking')"  
                        selected = "1"
                        :placeholder="__('Enter Your App Client ID', 'hydra-booking')"  
                    /> 
                    <HbText  
                        v-model="zoom_meeting.app_secret_key"  
                        required= "true"  
                        name="app_secret_key"
                        :errors="errors.app_secret_key"
                        :label="__('Zoom App Secret Key', 'hydra-booking')"  
                        selected = "1"
                        type = "password"
                        :placeholder="__('Enter Your App Secret Key', 'hydra-booking')"  
                    /> 
                    <button class="tfhb-btn boxed-btn" @click.stop="emit('update-integrations', 'zoom_meeting', zoom_meeting, ['account_id', 'app_client_id', 'app_secret_key'])">{{ __('Save & Validate', 'hydra-booking') }}</button>
                </template> 
            </HbPopup>
        <!-- </Transition > -->

    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 