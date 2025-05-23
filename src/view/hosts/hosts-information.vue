<script setup>
import { ref } from 'vue';
import { __ } from '@wordpress/i18n';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbRadio from '@/components/form-fields/HbRadio.vue'
import Icon from '@/components/icon/LucideIcon.vue'
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
    activeProfileDropdown.value = false;
}
const UploadImage = () => {   
    wp.media.editor.send.attachment = (props, attachment) => { 
    // set the image url to the input field
    imageChange(attachment);
    };  
    wp.media.editor.open(); 
} 
const EmptyImage = () => {   
    props.host.avatar = ''; 
    activeProfileDropdown.value = false;
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


const EmptyImageFeatured  = () => {
    props.host.featured_image = '';
} 
// Profile Image and cover image dropdown
const activeCoverDropdown = ref(false);
const activeProfileDropdown = ref(false);

// hide activeCoverDropdown when clicked outside
document.addEventListener('click', (e) => { 
    if ( !e.target.closest('.edit-profile-image')) { 
        activeProfileDropdown.value = false;
    }
    if ( !e.target.closest('.edit-cover-image')) { 
        activeCoverDropdown.value = false;
    }
});
</script>

<template>   
    <div class="tfhb-admin-card-box tfhb-host-profile-image-wrap"   
        :style="{
            'background-image': props.host.featured_image != '' ? `url('${props.host.featured_image}')` : `url('${$tfhb_url}/assets/app/images/meeting-cover.png')`, 
        }"
    >
    <span class="tfhb-profile-overlay"></span>
       
        <div class="tfhb-single-form-field-wrap avatar_display-wrap tfhb-flexbox" >
            
            <div   class="tfhb-field-image" > 
                <div  class="tfhb-dropdown edit-profile-image">  
                    <span  @click="activeProfileDropdown = !activeProfileDropdown"> <Icon name="Edit" size=16 /></span> 
                    <transition  name="tfhb-dropdown-transition">
                        <div v-if="activeProfileDropdown" class="tfhb-dropdown-wrap"> 
                            <span class="tfhb-dropdown-single"  @click="UploadImage" > <Icon name="Upload" size=20 /> {{ $tfhb_trans('Upload image') }}</span>
                    
                            <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="EmptyImage" ><Icon name="Trash2" size=20 />{{ $tfhb_trans('Delete') }}</span>
                        </div>
                    </transition>
                </div>
                <img v-if="host.avatar != ''"  class='avatar_display'  :src="host.avatar">
                <img v-else  class='avatar_display'  :src="$tfhb_url+'/assets/images/avator.png'" >
                <input  type="text"  :v-model="host.avatar"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 v-if="label !=''" :for="name">{{ $tfhb_trans('Profile image') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p v-if="description !=''"  class="tfhb-m-0">{{ $tfhb_trans('Recommended Image Size: 120x120px') }}</p>
            </div>
        </div> 
        <div  class="tfhb-dropdown edit-cover-image"> 
            <HbButton 
                classValue="tfhb-btn secondary-btn flex-btn"  
                :buttonText="$tfhb_trans('Edit cover image')"
                icon="Edit"   
                @click="activeCoverDropdown =!activeCoverDropdown"
                icon_position="left"  
            /> 
            <transition  name="tfhb-dropdown-transition">
                <div v-if="activeCoverDropdown" class="tfhb-dropdown-wrap"> 
                     <span class="tfhb-dropdown-single" @click="UploadImageFeature" > <Icon name="Upload" size=20  /> {{ $tfhb_trans('Upload image') }}</span>
            
                    <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="EmptyImageFeatured"  ><Icon name="Trash2" size=20 />{{ $tfhb_trans('Delete') }}</span>
                </div>
            </transition>
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
            <div v-if="field.type == 'checkbox' && field.enable == 1" class="tfhb-hosts-single-information-wrap">
                
                <HbCheckbox 
                    v-model="props.host.others_information[field.name]" 
                    :names="props.others_information[field.name]"
                    :label="field.label"  
                    :placeholder="field.placeholder"  
                    :groups="true"
                    :options="field.options" 
                />
            </div>
            <div v-else-if="field.type == 'textarea' && field.enable == 1" class="tfhb-hosts-single-information-wrap">
                
                <HbTextarea 
                    v-model="props.host.others_information[field.name]" 
                    :names="props.host.others_information[field.name]"
                    :label="field.label"  
                    :placeholder="field.placeholder"  
                    :name="props.host.others_information[field.name]"
                />
            </div>
            <div v-else-if="field.type == 'radio' && field.enable == 1" class="tfhb-hosts-single-information-wrap">
                 
                <HbRadio 
                    v-model="props.host.others_information[field.name]" 
                    :names="props.host.others_information[field.name]"
                    :label="field.label"  
                    :placeholder="field.placeholder"  
                    :groups="true"
                    :options="field.options"   
                    :name="host.others_information[field.name]"
                />
            </div>
            <div v-else-if="field.type == 'select' && field.enable == 1" class="tfhb-hosts-single-information-wrap">
                
                <HbDropdown 
            
                    v-model="props.host.others_information[field.name]"  
                    required= "true"  
                    :label="field.label"  
                    :placeholder="field.placeholder"  
                    selected = "1" 
                    placeholder="Select Time Zone"  
                    :option = "field.options"  
                    optionType = "array"  
                />  
            </div>
            <div v-else-if="field.enable == 1" class="tfhb-hosts-single-information-wrap">
            
                <HbText  
                    v-model="props.host.others_information[field.name]"  
                    :required= "field.required == 1 ? 'true' : 'false'"  
                    :label="field.label"  
                    :placeholder="field.placeholder"  
                    :type="field.type"  
                    :errors="field.required == 1 ? errors['others_information___' + field.name] : ''"
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
.avatar_display-wrap, .featured_image_display-wrap {
  position: relative;

  .tfhb-image-field-close {
    position: absolute;
    top: 0;
    left: 54px;
    padding: 2px;
    border-radius: 50%;
    height: 20px;
    width: 20px;
    background-color: #FEECEE;
    z-index: 1;
    text-align: center;
    color: #AC0C22 !important;
    cursor: pointer;
    transition: 0.4s;
        &:hover {
            background-color: #AC0C22;
            color: #fff !important;
        }
    }

    .tfhb-field-image{
        position: relative;
        .tfhb-image-field-close{
            left: auto;
            right: 4px;
            top: 4px;
            margin: 0 !important;
            
        }
    }
}

.featured_image_display-wrap .tfhb-field-image { 
  max-width: 300px;
  width: auto;
  border-radius: 8px;
}
.featured_image_display-wrap .tfhb-field-image {
    .featured_image_display{ 
        border-radius: 8px;
    } 
}
 
</style>
