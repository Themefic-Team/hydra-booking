<script setup> 
import { __ } from '@wordpress/i18n'; 
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount, } from 'vue';
import { useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 


// import Form Field 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';   
import HbText from '@/components/form-fields/HbText.vue' 
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbEditor from '@/components/form-fields/HbEditor.vue';
import HbButton from '@/components/form-fields/HbButton.vue'
import Editor from 'primevue/editor';

//  Load Time Zone 
const skeleton = ref(false);


const props = defineProps([
    'title', 
    'label', 
    'data',
    'ispopup'
])
const emit = defineEmits(['update-notification', 'popup-open-control', 'popup-close-control']);

const meetingShortcode = ref([
    '{{meeting.title}}',
    '{{meeting.date}}', 
    '{{meeting.location}}', 
    '{{meeting.duration}}', 
    '{{meeting.time}}', 
    '{{host.name}}', 
    '{{host.email}}',  
    '{{attendee.name}}', 
    '{{attendee.email}}', 
    '{{attendee.additional_data}}',
    '{{wp.admin_email}}',
    "{{booking.cancel_reason}}",
    "{{booking.full_start_end_host_timezone}}",
    "{{booking.start_date_time_for_host}}",
    "{{booking.start_date_time_for_attendee}}",
    "{{booking.full_start_end_attendee_timezone}}",
    "{{booking.rescheduled_reason}}",
    "{{booking.location_details_html}}",
])
const copyShortcode = (value) => { 
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action
    toast.success(value + ' is Copied');
}

const closePopup = () => { 
    emit('popup-close-control', false)
}
</script>
<template>
    <!-- Single Notification  -->
    <div class="tfhb-notification-single tfhb-flexbox tfhb-justify-between">
        <div class="tfhb-swicher-wrap  tfhb-flexbox"> 

            <!-- Checkbox swicher -->
            <HbSwitch v-model="props.data.status"  @change="emit('update-notification')"  :label="props.label "  /> 

        </div>

        <button class="tfhb-btn tfhb-edit flex-btn" @click="emit('popup-open-control')" ><Icon name="PencilLine" size=15 /> {{ __('Edit', 'hydra-booking') }} </button>

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="700px" name="first-modal">
            <template #header> 
                <h3>{{ title }}</h3>
                
            </template>

            <template #content>
 
                <!-- Time format -->
                <HbDropdown 
                    
                    v-model="data.template"  
                    required= "true" 
                    :label="__('Select Template', 'hydra-booking')"  
                    width="50"
                    :selected = "1"
                    placeholder="Select Template"   
                    :option = "[
                        {'name': 'Default', 'value': 'default'},  
                    ]"  
                /> 
                <!-- Time format --> 
                <HbText  
                    v-model="props.data.from"   
                    type="email"
                    width="50"
                    :label="__('From', 'hydra-booking')"  
                    selected = "1"
                    :placeholder="__('Enter From Email', 'hydra-booking')"  
                /> 

                <HbText  
                    v-model="props.data.subject"  
                    required= "true"  
                    :label="__('Subject', 'hydra-booking')"  
                    selected = "1"
                    type = "text"
                    :placeholder="__('Enter Mail Subject', 'hydra-booking')"  
                /> 
 
                
                <div class="tfhb-single-form-field" style="width: 100%;"  >
                    <div class="tfhb-single-form-field-wrap tfhb-field-input">
                        <!--if has label show label with tag else remove tags  --> 
                        <label for="">{{ __('Mail Body', 'hydra-booking') }}</label>  
                        <Editor 
                            v-model="props.data.body"  
                            :placeholder="__('Mail Body', 'hydra-booking')"    
                            editorStyle="height: 250px" 
                        />
                    </div> 
                </div> 
                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8"> 
                    <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>

                </div>

                <HbButton  
                    classValue="tfhb-btn boxed-btn" 
                    @click="emit('update-notification')"
                    :buttonText="__('Update', 'hydra-booking')" 
                />  
             </template> 
        </HbPopup>
    </div>
    <!-- Single Integrations  -->

 
</template>