<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';

const props = defineProps([
    'class', 
    'display', 
    'stripe_data', 
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
      <!-- Stripe Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class,{
            'tfhb-pro': !$tfhb_is_pro,
        }"
      >
        <span v-if="$tfhb_is_pro == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
         
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/stripe.png'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ __('Stripe', 'hydra-booking') }}</h3>
                <p>{{ __('ntegrate Stripe API for secure payment processing.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div v-if="$tfhb_is_pro == false"  class="tfhb-integrations-single-block-btn tfhb-flexbox"> 
            <span   v-if=" props.from == 'host' && stripe_data.connection_status != '1'" class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
           
            <a v-else href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Upgrade to Pro', 'hydra-booking') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>

        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <button @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ stripe_data.secret_key ? 'Settings' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button>
             
            <HbButton  
                @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                :buttonText="props.stripe_data.secret_key == 1 ? 'Connected' : 'Connect' "
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"   
                width="80px"
            /> 

            <HbSwitch 
                v-if="stripe_data.secret_key != '' &&  stripe_data.public_key  != '' && stripe_data.secret_key != 'null' &&  stripe_data.public_key  != 'null'" 
                @change="emit('update-integrations', 'stripe', stripe_data)" v-model="stripe_data.status"    
             />
            <!-- Swicher -->
        </div>

        <HbPopup  v-if="$tfhb_is_pro == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ __('Connect Your Stripe Account', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Stripe Account', 'hydra-booking') }}
                </p>
                <HbText  
                    v-model="stripe_data.public_key"  
                    required= "true"  
                    name="public_key"
                    :errors="errors.public_key"
                    :label="__('Stripe Public Key', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Public Key', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="stripe_data.secret_key"  
                    required= "true"  
                    name="secret_key"
                    :errors="errors.secret_key"
                    :label="__('Stripe Secret Key', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Your Stripe Secret', 'hydra-booking')"  
                />
                <HbButton  
                    @click.stop="emit('update-integrations', 'stripe', stripe_data, ['public_key', 'secret_key'])"
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                    :buttonText="'Save & Validate' "
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true" 
                    :pre_loader="props.pre_loader"
                    width="150px"
                />   
            </template> 
        </HbPopup>


 
    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 