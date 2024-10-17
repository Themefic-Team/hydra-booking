<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
// import { Copy } from 'lucide-vue-next';
import { toast } from "vue3-toastify"; 

const props = defineProps([
    'google_calendar', 
    'class', 
    'display', 
    'ispopup'
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
                <img :src="$tfhb_url+'/assets/images/google-calendar.png'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('Google Calendar/Meet', 'hydra-booking') }}</h3> 
                <p>{{ __('Connect Google Calendar/Meet API to add events and create video calls.', 'hydra-booking') }}</p>

            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <button @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ props.google_calendar.connection_status == 1 ? 'Connected' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button>
                <!-- Checkbox swicher -->

                <HbSwitch v-if="props.google_calendar.connection_status" @change="emit('update-integrations', 'google_calendar', props.google_calendar)" v-model="props.google_calendar.status"    />
            <!-- Swicher --> 
        </div>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <!-- {{ google_calendar }} -->
                <h2>{{ __('Add Google Calendar', 'hydra-booking') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ __('Please read the documentation here for step by step guide to know how you can get api credentials from Google Calendar', 'hydra-booking') }}
                    
                </p>
                <HbText  
                    v-model="props.google_calendar.client_id"  
                    required= "true"  
                    :label="__('Client ID', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Client ID', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="props.google_calendar.secret_key"  
                    required= "true"  
                    :label="__('Secret Key', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter Secret Key', 'hydra-booking')"  
                /> 
                <HbText  
                    v-model="props.google_calendar.redirect_url"  
                    required= "true"   
                    :label="__('Redirect Url', 'hydra-booking')"   
                    selected = "1" 
                    :placeholder="__('Enter Redirect Url', 'hydra-booking')"  
                /> 
                <button class="tfhb-btn boxed-btn" @click.stop="emit('update-integrations', 'google_calendar', props.google_calendar)">{{ __('Save & Validate', 'hydra-booking') }}</button>
            </template> 
        </HbPopup>

    </div>  
    <!-- Single Integrations  -->

</template>
 

<style scoped>
</style> 