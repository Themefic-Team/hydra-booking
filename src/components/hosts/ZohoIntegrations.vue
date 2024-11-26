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
    'zoho_crm_status',
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
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >
        
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Zoho.svg'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Zoho') }}</h3>
                <p>{{ $tfhb_trans('New standard in online payment') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <span v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>

            <a v-if="zoho_data.client_id && !zoho_data.access_token && $tfhb_is_pro == true && $tfhb_license_status == true" :href="' https://accounts.zoho.com/oauth/v2/auth?response_type=code&client_id='+zoho_data.client_id+'&scope=ZohoCRM.modules.ALL%20ZohoCRM.settings.ALL&redirect_uri='+zoho_data.redirect_url+'&state='+host_id+'&access_type=offline'" target="_blank"class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Get Access Token') }}</a>

            <button v-else-if="zoho_data.client_id && zoho_data.access_token && $tfhb_is_pro == true && $tfhb_license_status == true" @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Settings') }}<Icon name="ChevronRight" size=18 /></button>

            <button v-else-if="$tfhb_is_pro == true && $tfhb_license_status == true && zoho_crm_status==1" @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Connect') }}<Icon name="ChevronRight" size=18 /></button>

            <router-link v-else-if="$tfhb_is_pro == true && $tfhb_license_status == true && zoho_crm_status==0"  to="/settings/integrations#marketing-tools" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <!-- Checkbox swicher -->

            <HbSwitch v-else
                v-if="zoho_data.access_token != '' &&  zoho_data.access_token  != null " 
                @change="emit('update-integrations', 'zoho', zoho_data)" v-model="zoho_data.status"  
              />
            <!-- Swicher --> 
        </div>

        <HbPopup  v-if="$tfhb_is_pro == true && $tfhb_license_status == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ $tfhb_trans('Connect Your Zoho Account') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Zoho Account') }}
                </p>
                <HbText  
                    v-model="zoho_data.client_id"  
                    required= "true"  
                    :label="$tfhb_trans('Zoho Client ID')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Client ID')"  
                /> 
                <HbText  
                    v-model="zoho_data.client_secret"  
                    required= "true"  
                    :label="$tfhb_trans('Zoho Client Secret')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Client Secret')"  
                />
                <HbText  
                    v-model="zoho_data.redirect_url"  
                    required= "true"  
                    :readonly="true"
                    :label="$tfhb_trans('Zoho Redirect URL')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Redirect URL')"  
                />
                <button class="tfhb-btn boxed-btn tfhb-hover-effect" @click.stop="emit('update-integrations', 'zoho', zoho_data)">{{ $tfhb_trans('Save & Validate') }}</button>
            </template> 
        </HbPopup>

    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 