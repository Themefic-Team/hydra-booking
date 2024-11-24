<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';
import HbPreloader from '@/components/icon/HbPreloader.vue';
// import { Copy } from 'lucide-vue-next';
import { toast } from "vue3-toastify"; 
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

const props = defineProps([
    'google_calendar', 
    'class', 
    'pre_loader', 
    'display', 
    'pre_loader', 
    'ispopup'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
} 

const copyRedirectionURL = () => {
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = props.google_calendar.redirect_url;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action 
    // success mess into bottom right
    toast.success( props.google_calendar.redirect_url + ' is Copied' , {
        position: 'bottom-right', // Set the desired position
        duration: 2000 // Set the desired duration
    });
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
                <h3>{{ $tfhb_trans('Google Calendar/Meet') }}</h3> 
                <p>{{ $tfhb_trans('Connect Google Calendar/Meet API to add events and create video calls.') }}</p>

            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- <button @click="emit('popup-open-control')" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ props.google_calendar.connection_status == 1 ? 'Connected' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button> -->
            <HbButton  
                @click="emit('popup-open-control')" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="props.google_calendar.connection_status == 1 ? 'Connected' : 'Connect' " 
                :hover_animation="false"    
            />     
            <!-- Checkbox swicher -->
                
                <HbSwitch v-if="props.google_calendar.connection_status" @change="emit('update-integrations', 'google_calendar', props.google_calendar)" v-model="props.google_calendar.status"    />
            <!-- Swicher --> 
        </div>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <!-- {{ google_calendar }} -->
                <h2>{{ $tfhb_trans('Add Google Calendar') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Google Calendar') }}
                    <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                    
                </p>
                <HbText  
                    v-model="props.google_calendar.client_id"  
                    required= "true"
                    name="client_id"
                    :errors="errors.client_id"  
                    :label="$tfhb_trans('Client ID')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Client ID')"  
                /> 
                <HbText  
                    v-model="props.google_calendar.secret_key"  
                    required= "true"  
                    name="secret_key"
                    :errors="errors.secret_key"  
                    :label="$tfhb_trans('Secret Key')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Secret Key')"  
                /> 
                <div class="tfhb-google-calender-redirection-url tfhb-full-width"  >
                    <HbText  
                        v-model="props.google_calendar.redirect_url"  
                        required= "true"   
                        name="redirect_url"
                        :readonly="true"
                        :errors="errors.redirect_url"  
                        :label="$tfhb_trans('Redirect Url')"   
                        selected = "1" 
                        :placeholder="$tfhb_trans('Enter Redirect Url')"  
                    /> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                        @click="copyRedirectionURL()" 
                        :buttonText="$tfhb_trans('Copy URL')" 
                    /> 
                </div>
                

                <HbButton  
                    @click.stop="emit('update-integrations', 'google_calendar', props.google_calendar, ['client_id', 'secret_key', 'redirect_url'])"
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"  
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
 

<style lang="scss" scoped>

</style> 