<script setup>
import { __ } from '@wordpress/i18n';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbRadio from '@/components/form-fields/HbRadio.vue'
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();

const emit = defineEmits(["save-host-info"]); 

// Get Current Route url 
const props = defineProps({
    hostId: {
        type: Number,
        required: true
    },
    host: {
        type: Object,
        required: true
    },
    time_zone:{},
    hosts_settings: {
        type: Object,
        default: {
            others_information:{
                enable_others_information: false,
                fields: {},
            }
        },
        required: true
    }, 
    update_host_preloader: {
        type: Boolean,
        required: true
    }

}); 
 

const imageChange = (attachment) => {   
    props.host.avatar = attachment.url; 
    const image = document.querySelector('.avatar_display'); 
    image.src = attachment.url; 
}
const UploadImage = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChange(attachment);
    };  
    wp.media.editor.open(); 
}

const imageChangeFeature = (attachment) => {   
    props.host.featured_image = attachment.url; 
    const image = document.querySelector('.featured_image_display'); 
    image.src = attachment.url; 
}
const UploadImageFeature  = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChangeFeature(attachment);
    };  
    wp.media.editor.open(); 
}

const tfhbValidateInput = (fieldName) => {
    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    const fieldParts = fieldName.split('.');
    if(fieldParts[0] && !fieldParts[1]){
        isEmpty(fieldParts[0], props.host[fieldParts[0]]);
    }
    if(fieldParts[0] && fieldParts[1]){
        isEmpty(fieldParts[0]+'___'+[fieldParts[1]], props.host[fieldParts[0]][fieldParts[1]]);
    }
};

</script>

<template>  
    <div class="tfhb-admin-card-box">   

        <div class="tfhb-single-form-field-wrap tfhb-flexbox">
            <div class="tfhb-field-image" > 
                <img v-if="host.avatar != ''"  class='avatar_display'  :src="host.avatar">
                <img v-else  class='avatar_display'  :src="$tfhb_url+'/assets/images/avator.png'" >
                <button class="tfhb-image-btn" @click="UploadImage">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="host.avatar"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 v-if="label !=''" :for="name">{{ $tfhb_trans('Profile image') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p v-if="description !=''"  class="tfhb-m-0">{{ $tfhb_trans('Recommended Image Size: 400x400px') }}</p>
            </div>
        </div> 
    </div>
    <div class="tfhb-admin-card-box">   

        <div class="tfhb-single-form-field-wrap tfhb-flexbox featured_image_display-wrap">
            <div class="tfhb-field-image" > 
                <img v-if="host.featured_image != ''"  class='featured_image_display'  :src="host.featured_image">
                <img v-else  class='featured_image_display'  :src="$tfhb_url+'/assets/images/images-icon.png'" >
                <button class="tfhb-image-btn" @click="UploadImageFeature">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="host.featured_image"   />  
            </div>
            <div class="tfhb-image-box-content">  
                <h4 v-if="label !=''" :for="name">{{ $tfhb_trans('Featured image') }} <span  v-if="required == 'true'"> *</span> </h4>
                <p v-if="description !=''"  class="tfhb-m-0">{{ $tfhb_trans('Feature image appearing as the host background image.') }}</p>
            </div>
        </div> 
    </div>
    <div class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('General Information') }}    </h2>  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">  
        <HbText  
            v-model="host.first_name"  
            required= "true"  
            :label="$tfhb_trans('First name')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your first name')" 
            width="50"
            :errors="errors.first_name"
        /> 
        <HbText  
            v-model="host.last_name"  
            required= "true"  
            :label="$tfhb_trans('Last name')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your last name')" 
            width="50"
            :errors="errors.last_name"
        />  
        <HbText  
            v-model="host.email"  
            required= "true"  
            :label="$tfhb_trans('Email')"  
            selected = "1"
             :placeholder="$tfhb_trans('Type your email')" 
            width="50"
            disabled="true"
        /> 
        <!-- Time Zone -->
        <HbDropdown 
            v-model="host.time_zone"  
            required= "true"  
            :label="$tfhb_trans('Time zone')"  
            selected = "1"
            :filter="true"
            :placeholder="$tfhb_trans('Select Time Zone')"  
            :option = "time_zone" 
            width="50" 
            :errors="errors.time_zone"
        /> 
        <HbText  
            v-model="host.phone_number"   
            :label="$tfhb_trans('Mobile')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your mobile no')" 
            width="50"  
        />  
         
    <!-- Time Zone -->
    </div>   
    <div v-if="hosts_settings.others_information && hosts_settings.others_information.enable_others_information == true"  class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('Others Information') }}    </h2>  
    </div>
    <div v-if="hosts_settings.others_information && hosts_settings.others_information.enable_others_information == true && hosts_settings.others_information.fields" class="tfhb-admin-card-box tfhb-flexbox">  
       <div class="tfhb-host-single-information" v-for="(field, index) in hosts_settings.others_information.fields" :key="index">  
            <!--  --> 
            <div v-if="field.type == 'checkbox'" class="tfhb-hosts-single-information-wrap">
                
                <HbCheckbox 
                    v-model="host.others_information[field.label]" 
                    :names="host.others_information[field.label]"
                    :label="field.placeholder"
                    :groups="true"
                    :options="field.options" 
                />
            </div>
            <div v-else-if="field.type == 'textarea'" class="tfhb-hosts-single-information-wrap">
                
                <HbTextarea 
                    v-model="host.others_information[field.label]" 
                    :names="host.others_information[field.label]"
                    :label="field.placeholder"  
                    :name="host.others_information[field.label]"
                />
            </div>
            <div v-else-if="field.type == 'radio'" class="tfhb-hosts-single-information-wrap">
                 
                <HbRadio 
                    v-model="host.others_information[field.label]" 
                    :names="host.others_information[field.label]"
                    :label="field.placeholder"
                    :groups="true"
                    :options="field.options"   
                    :name="host.others_information[field.label]"
                />
            </div>
            <div v-else-if="field.type == 'select'" class="tfhb-hosts-single-information-wrap">
                
                <HbDropdown 
            
                    v-model="host.others_information[field.label]"  
                    required= "true"  
                    label="field.placeholder"
                    selected = "1" 
                    placeholder="Select Time Zone"  
                    :option = "field.options"  
                    optionType = "array"  
                />  
            </div>
            <div v-else class="tfhb-hosts-single-information-wrap">
                <HbText  
                    v-model="host.others_information[field.label]"  
                    :required= "field.required == 1 ? 'true' : 'false'"  
                    :label="field.placeholder"   
                    :placeholder="field.placeholder"  
                    :type="field.type"  
                    :errors="field.required == 1 ? errors['others_information___' + field.label] : ''"
                />
            </div>
            

       </div>
    </div>  


    <!--  Update Hosts Information -->
    <HbButton 
        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
        @click="emit('save-host-info', ['first_name', 'last_name', 'time_zone'])"
        :buttonText="$tfhb_trans('Save & Continue')"
        icon="ChevronRight" 
        hover_icon="ArrowRight" 
        :hover_animation="true"
        :pre_loader="props.update_host_preloader"
    />  
</template>


 
<style scoped lang="scss">
/* Your component styles go here */

.featured_image_display-wrap .tfhb-field-image { 
  max-width: 300px;
  width: auto;
  border-radius: 8px;
}
.featured_image_display-wrap .tfhb-field-image .featured_image_display {
  border-radius: 8px;
  padding: 4px;
}
 
</style>
