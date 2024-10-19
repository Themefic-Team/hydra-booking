<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import { RouterView } from 'vue-router' 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue';  

const props = defineProps([
    'class', 
    'display', 
    'mail_data', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template>
      <!-- Mailchimp Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box "> 
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Mailchimp.svg'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('Mailchimp', 'hydra-booking') }}</h3>
                <p>{{ __('Integrate Mailchimp API to collect attendee emails and info.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <!-- <span v-if="props.from == 'host' && mail_data.connection_status != 1" class="tfhb-badge tfhb-badge-not-connected">{{ __('Not Configured', 'hydra-booking') }}  </span> -->
            
            <router-link v-if="props.from == 'host' && mail_data.connection_status != 1"  to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ __('Go To Settings', 'hydra-booking') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <button v-else @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ mail_data.key ? 'Settings' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button>
                <!-- Checkbox swicher -->

                <HbSwitch
                v-if="mail_data.key != '' &&  mail_data.key  != null " 
                 @change="emit('update-integrations', 'mailchimp', mail_data)" v-model="mail_data.status"   
                />
            <!-- Swicher --> 
        </div>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ __('Connect Your Mailchimp API', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Mailchimp Account', 'hydra-booking') }}
                </p>
                <HbText  
                    v-model="mail_data.key"  
                    required= "true"  
                    :label="__('Mailchimp API Key', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your API Key', 'hydra-booking')"  
                /> 
                <button class="tfhb-btn boxed-btn" @click.stop="emit('update-integrations', 'mailchimp', mail_data)">{{ __('Save & Validate', 'hydra-booking') }}</button>
            </template> 
        </HbPopup>

    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 