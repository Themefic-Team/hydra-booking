<script setup>
import { __ } from '@wordpress/i18n';
import Icon from '@/components/icon/LucideIcon.vue'
import { ref } from 'vue';
import { RouterView } from 'vue-router' 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';

import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

const props = defineProps([
    'class', 
    'display', 
    'paypal_data', 
    'pre_loader', 
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
                <h3>{{ $tfhb_trans('PayPal') }}</h3>
                <p>{{ $tfhb_trans('Connect PayPal API for easy online payment solutions.') }}</p>
                
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- <span v-if="props.from == 'host' && paypal_data.secret_key == null && paypal_data.client_id  == null" class="tfhb-badge tfhb-badge-not-connected">{{ $tfhb_trans('Not Configured') }}  </span> -->
            <router-link  v-if="props.from == 'host' && paypal_data.secret_key == null && paypal_data.client_id  == null"  to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>
            <button v-else @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ paypal_data.secret_key ? 'Settings' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button>
            <HbButton  
                 v-else @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="props.paypal_data.secret_key ? 'Settings' : 'Connect'" 
                :hover_animation="false"    
            /> 

                <HbSwitch 
                v-if="paypal_data.secret_key != '' &&  paypal_data.client_id  != '' && paypal_data.secret_key != null &&  paypal_data.client_id  != null" 
                @change="emit('update-integrations', 'paypal', paypal_data, [])" v-model="paypal_data.status"    />
            
        </div>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ $tfhb_trans('Connect Your Paypal Account') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Paypal Account') }}
                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/paypal-integration/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                </p>
                <HbDropdown 
                    v-model="paypal_data.environment"
                    required= "true"  
                    name="environment"
                    :errors="errors.environment"
                    :label="$tfhb_trans('Environment')"   
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
                    name="client_id"
                    :errors="errors.client_id"
                    :label="$tfhb_trans('Paypal Client ID')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Client ID')"  
                /> 
                <HbText  
                    v-model="paypal_data.secret_key"  
                    name="secret_key"
                    :errors="errors.secret_key"
                    required= "true"  
                    :label="$tfhb_trans('Paypal Secret Key')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Paypal Secret')"  
                />

                <HbButton  
                    @click.stop="emit('update-integrations', 'paypal', props.paypal_data, ['environment', 'client_id', 'secret_key'])"
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                    :buttonText="'Save & Validate' "
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true" 
                    :pre_loader="props.pre_loader"
                />  
            </template> 
        </HbPopup>

    </div>  


</template>

<style scoped>
</style> 