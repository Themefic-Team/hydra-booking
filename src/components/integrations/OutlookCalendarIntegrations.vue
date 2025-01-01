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
import { toast } from "vue3-toastify"; 

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

const copyRedirectionURL = () => {
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = props.outlook_calendar.redirect_url;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action 
    // success mess into bottom right
    toast.success( props.outlook_calendar.redirect_url + ' is Copied' , {
        position: 'bottom-right', // Set the desired position
        duration: 2000 // Set the desired duration
    });
}
</script>
 
<template>
      <!-- Zoom Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
      :class="props.class,{
        'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
      }"
      >
        <span v-if="$tfhb_is_pro == false ||  $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
         
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/outlook-calendar.png'" alt="" >
            </span> 

            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Outlook Calendar') }}</h3> 
                <p>{{ $tfhb_trans('Connect Outlook Calendar/Teams API for event creation.') }}</p>

            </div>
        </div>
        <div v-if="$tfhb_is_pro == false ||  $tfhb_license_status == false" class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <a  href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upgrade to Pro') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>
        <!-- <div v-if="$tfhb_is_pro == true &&  $tfhb_license_status == true" class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <a  href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upcoming') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div> --> 
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            
            <HbButton  
                @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                :buttonText="props.outlook_calendar.connection_status == 1 ? 'Connected' : 'Connect' " 
                :hover_animation="true"    
            />
            
            <HbSwitch v-if="outlook_calendar.connection_status" @change="emit('update-integrations', 'outlook_calendar', outlook_calendar)" v-model="outlook_calendar.status" />

             
 
        </div>


        <HbPopup  v-if="$tfhb_is_pro == true ||  $tfhb_license_status == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <!-- {{ outlook_calendar }} -->
                <h2>{{ $tfhb_trans('Add Outlook Calendar') }}</h2>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Outlook Calendar') }}
                    
                </p>
                <HbText  
                    v-model="outlook_calendar.client_id"  
                    required= "true"  
                    name="client_id"
                    :errors="errors.client_id"  
                    :label="$tfhb_trans('Client ID')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Client ID')"  
                /> 
                <HbText  
                    v-model="outlook_calendar.secret_key"  
                    required= "true"  
                    name="secret_key"
                    :errors="errors.secret_key"  
                    :label="$tfhb_trans('Secret Key')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter Secret Key')"  
                /> 
                <div class="tfhb-google-calender-redirection-url tfhb-full-width"  >
                    <HbText  
                        v-model="outlook_calendar.redirect_url"  
                        required= "true"  
                        name="redirect_url"
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