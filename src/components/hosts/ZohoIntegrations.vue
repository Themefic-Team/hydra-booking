<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 

const props = defineProps([
    'class', 
    'display', 
    'zoho_data', 
    'ispopup',
    'host_id',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template>
      <!-- Zoho Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class,{
            'tfhb-pro': !$tfhb_is_pro,
        }"
      >
        
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Zoho.svg'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('Zoho', 'hydra-booking') }}</h3>
                <p>{{ __('New standard in online payment', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <span v-if="$tfhb_is_pro == false" class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>

            <a v-if="zoho_data.client_id && !zoho_data.access_token && $tfhb_is_pro == true" :href="' https://accounts.zoho.com/oauth/v2/auth?response_type=code&client_id='+zoho_data.client_id+'&scope=ZohoCRM.modules.ALL%20ZohoCRM.settings.ALL&redirect_uri='+zoho_data.redirect_url+'&state='+host_id+'&access_type=offline'" target="_blank"class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Get Access Token', 'hydra-booking') }}</a>

            <button v-else-if="zoho_data.client_id && zoho_data.access_token && $tfhb_is_pro == true" @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">Settings<Icon name="ChevronRight" size=18 /></button>

            <button v-else-if="$tfhb_is_pro == true" @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">Connect<Icon name="ChevronRight" size=18 /></button>

            <!-- Checkbox swicher -->

            <HbSwitch v-else
                v-if="zoho_data.access_token != '' &&  zoho_data.access_token  != null " 
                @change="emit('update-integrations', 'zoho', zoho_data)" v-model="zoho_data.status"  
              />
            <!-- Swicher --> 
        </div>

        <HbPopup  v-if="$tfhb_is_pro == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ __('Connect Your Zoho Account', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Zoho Account', 'hydra-booking') }}
                </p>
                <HbText  
                    v-model="zoho_data.client_id"  
                    required= "true"  
                    :label="__('Zoho Client ID', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Client ID', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="zoho_data.client_secret"  
                    required= "true"  
                    :label="__('Zoho Client Secret', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Client Secret', 'hydra-booking')"  
                />
                <HbText  
                    v-model="zoho_data.redirect_url"  
                    required= "true"  
                    :readonly="true"
                    :label="__('Zoho Redirect URL', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Redirect URL', 'hydra-booking')"  
                />
                <button class="tfhb-btn boxed-btn tfhb-hover-effect" @click.stop="emit('update-integrations', 'zoho', zoho_data)">{{ __('Save & Validate', 'hydra-booking') }}</button>
            </template> 
        </HbPopup>

    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 