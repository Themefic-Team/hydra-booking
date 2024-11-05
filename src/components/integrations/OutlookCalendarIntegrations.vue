<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';

const props = defineProps([
    'outlook_calendar', 
    'class', 
    'display', 
    'pre_loader', 
    'ispopup'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}
</script>
 
<template>
      <!-- Zoom Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
      :class="props.class,{
        'tfhb-pro': !$tfhb_is_pro,
      }"
      >
        <span v-if="$tfhb_is_pro == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
         
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/outlook-calendar.png'" alt="" >
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('Outlook Calendar', 'hydra-booking') }}</h3> 
                <p>{{ __('Connect Outlook Calendar/Teams API for event creation.', 'hydra-booking') }}</p>

            </div>
        </div>
        <div v-if="$tfhb_is_pro == false" class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <a  href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Upgrade to Pro', 'hydra-booking') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <HbButton  
                @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                :buttonText="props.outlook_calendar.connection_status == 1 ? 'Connected' : 'Connect' "
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"   
                width="80px"
            />   
            <HbSwitch v-if="outlook_calendar.connection_status" @change="emit('update-integrations', 'outlook_calendar', outlook_calendar)" v-model="outlook_calendar.status"    />
             
 
        </div>


        <HbPopup  v-if="$tfhb_is_pro == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <!-- {{ outlook_calendar }} -->
                <h2>{{ __('Add Outlook Calendar', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Outlook Calendar', 'hydra-booking') }}
                    
                </p>
                <HbText  
                    v-model="outlook_calendar.client_id"  
                    required= "true"  
                    name="client_id"
                    :errors="errors.client_id"  
                    :label="__('Client ID', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Client ID', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="outlook_calendar.secret_key"  
                    required= "true"  
                    name="secret_key"
                    :errors="errors.secret_key"  
                    :label="__('Secret Key', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Secret Key', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="outlook_calendar.redirect_url"  
                    required= "true"  
                    name="redirect_url"
                    :errors="errors.redirect_url"  
                    :label="__('Redirect Url', 'hydra-booking')"  
                    selected = "1" 
                    :placeholder="__('Enter Redirect Url', 'hydra-booking')"  
                /> 
                <HbButton  
                    @click.stop="emit('update-integrations', 'outlook_calendar', outlook_calendar, ['client_id', 'secret_key', 'redirect_url'])"
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