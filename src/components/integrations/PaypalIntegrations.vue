<script setup>
import { __ } from '@wordpress/i18n';
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';

const props = defineProps([
    'class', 
    'display', 
    'paypal_data', 
    'ispopup',
    'from'

])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template> 
      <!-- paypal Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">  
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/paypal.png'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('PayPal', 'hydra-booking') }}</h3>
                <p>{{ __('Connect PayPal API for easy online payment solutions.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <span v-if="props.from == 'host' && paypal_data.secret_key == null && paypal_data.client_id  == null" class="tfhb-badge tfhb-badge-not-connected">{{ __('Not Configured', 'hydra-booking') }}  </span>
            
            <button v-else @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ paypal_data.secret_key ? 'Settings' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button>
                <!-- Checkbox swicher -->

                <HbSwitch 
                v-if="paypal_data.secret_key != '' &&  paypal_data.client_id  != '' && paypal_data.secret_key != null &&  paypal_data.client_id  != null" 
                @change="emit('update-integrations', 'paypal', paypal_data)" v-model="paypal_data.status"    />
            <!-- Swicher --> 
        </div>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ __('Connect Your Paypal Account', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Paypal Account', 'hydra-booking') }}
                </p>
                <HbDropdown 
                    v-model="paypal_data.environment"
                    required= "true"  
                    :label="__('Environment', 'hydra-booking')"   
                    selected = "1"
                    placeholder="Select Environment"  
                    :option = "[
                        {name: 'SandBox', value: 'sandbox'},  
                        {name: 'Production', value: 'live'},  
                    ]" 
                />
                <HbText  
                    v-model="paypal_data.client_id"  
                    required= "true"  
                    :label="__('Paypal Client ID', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Client ID', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="paypal_data.secret_key"  
                    required= "true"  
                    :label="__('Paypal Secret Key', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Paypal Secret', 'hydra-booking')"  
                />
                <button class="tfhb-btn boxed-btn" @click.stop="emit('update-integrations', 'paypal', paypal_data)">{{ __('Save & Validate', 'hydra-booking') }}</button>
            </template> 
        </HbPopup>

    </div>  


</template>

<style scoped>
</style> 