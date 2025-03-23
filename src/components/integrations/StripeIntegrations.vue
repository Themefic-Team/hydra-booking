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
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >
        <span v-if="$tfhb_is_pro == false  || $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
         
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/stripe.png'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Stripe') }}</h3>
                <p>{{ $tfhb_trans('ntegrate Stripe API for secure payment processing.') }}</p>
            </div>
        </div>
        <div v-if="$tfhb_is_pro == false  || $tfhb_license_status == false"  class="tfhb-integrations-single-block-btn tfhb-flexbox"> 
            <span   v-if=" props.from == 'host' && stripe_data.connection_status != '1'" class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
           
            <a v-else href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upgrade to Pro') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>

        <div v-if="$tfhb_is_pro == true &&  $tfhb_license_status == true" class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
             
            <HbButton  
                @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                :buttonText="stripe_data.secret_key != '' &&  stripe_data.public_key  != '' && stripe_data.secret_key != 'null' &&  stripe_data.public_key  != 'null' ? 'Connected' : 'Connect' "
                :hover_animation="true"   
                width="80px"
            /> 

            <HbSwitch 
                v-if="stripe_data.secret_key != '' &&  stripe_data.public_key  != '' && stripe_data.secret_key != 'null' &&  stripe_data.public_key  != 'null'" 
                @change="emit('update-integrations', 'stripe', stripe_data)" v-model="stripe_data.status"    
             /> 
        </div>

        <HbPopup  v-if="$tfhb_is_pro == true  || $tfhb_license_status == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ $tfhb_trans('Connect Your Stripe Account') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Stripe Account') }}
                </p>
                <HbText  
                    v-model="stripe_data.public_key"  
                    required= "true"  
                    name="public_key"
                    :errors="errors.public_key"
                    :label="$tfhb_trans('Stripe Public Key')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Public Key')"  
                /> 
                <HbText  
                    v-model="stripe_data.secret_key"  
                    required= "true"  
                    name="secret_key"
                    :errors="errors.secret_key"
                    :label="$tfhb_trans('Stripe Secret Key')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your Stripe Secret')"  
                />
                <HbButton  
                    @click.stop="emit('update-integrations', 'stripe', stripe_data, ['public_key', 'secret_key'])"
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
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 