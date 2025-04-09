<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import { RouterView } from 'vue-router' 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';

import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

const props = defineProps([
    'class', 
    'display', 
    'mail_data', 
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
      <!-- Mailchimp Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box "> 
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Mailchimp.svg'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Mailchimp') }}</h3>
                <p>{{ $tfhb_trans('Integrate Mailchimp API to collect attendee emails and info.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- <span v-if="props.from == 'host' && mail_data.connection_status != 1" class="tfhb-badge tfhb-badge-not-connected">{{ $tfhb_trans('Not Configured') }}  </span> -->
            <button  v-if=" props.from == 'host' && mail_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Connected') }} </button>
            <router-link v-else-if="props.from == 'host' && mail_data.connection_status != 1"  to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <HbButton  
                v-else @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="props.mail_data.status == 1 && mail_data.key != '' &&  mail_data.key  != null ? 'Connected' : 'Connect' " 
                :hover_animation="false"    
            /> 

                <HbSwitch
                v-if="mail_data.key != '' &&  mail_data.key  != null " 
                 @change="emit('update-integrations', 'mailchimp', mail_data)" v-model="mail_data.status"   
                />
            <!-- Swicher --> 
        </div>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <h2>{{ $tfhb_trans('Connect Your Mailchimp API') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Mailchimp Account') }}

                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/mailchimp/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                </p>
                <HbText  
                    v-model="mail_data.key"  
                    required= "true"  
                    name="key"
                    :errors="errors.key"
                    :label="$tfhb_trans('Mailchimp API Key')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Your API Key')"  
                /> 
                <HbButton  
                    @click.stop="emit('update-integrations', 'mailchimp', mail_data, ['key'])"
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                    :buttonText="'Save & Validate' "
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true"   
                    :pre_loader="props.pre_loader"
                /> 
                <!-- <button class="tfhb-btn boxed-btn" @click.stop="emit('update-integrations', 'mailchimp', mail_data, ['mailchimp_api_key'])">{{ $tfhb_trans('Save & Validate') }}</button> -->
            </template> 
        </HbPopup>

    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 