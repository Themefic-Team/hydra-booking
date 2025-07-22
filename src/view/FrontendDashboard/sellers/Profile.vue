<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { __ } from '@wordpress/i18n';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbRadio from '@/components/form-fields/HbRadio.vue'
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator'
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const { errors, isEmpty } = useValidators();

// --- API-driven user public info ---
const userPublicInformation = reactive({
    cover_image : '',
    avatar : '',
    description : '',
});
const loading = ref(false);
const skeleton = ref(true);
const saveSuccess = ref(false);
const saveError = ref('');

async function fetchUserPublicInfo() {
    loading.value = true;
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/user-public-info', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            },
            params: {
                user_id: tfhb_core_apps.user.id
            },
            withCredentials: true
        });
        if (response.data.success && response.data.data) {
            Object.assign(userPublicInformation, response.data.data);
            skeleton.value = false;
        }
    } catch (e) {
        // handle error
    } finally {
        loading.value = false;
    }
}

async function saveUserPublicInfo() {
    loading.value = true;
    saveSuccess.value = false;
    saveError.value = '';
    try {
        const response = await axios.post(
            tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/user-public-info',
            { ...userPublicInformation, user_id: tfhb_core_apps.user.id },
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            }
        );
        if (response.data.success) {
            saveSuccess.value = true;
            toast.success('Profile updated successfully!', { position: "bottom-right" });
        } else {
            saveError.value = response.data.message || 'Failed to save.';
            toast.error(saveError.value, { position: "bottom-right" });
        }
    } catch (e) {
        saveError.value = 'Failed to save.';
        toast.error(saveError.value, { position: "bottom-right" });
    } finally {
        loading.value = false;
        setTimeout(() => { saveSuccess.value = false; }, 2000);
    }
}

// --- Password Change State ---
const passwordForm = reactive({
    current_password: '',
    new_password: '',
    confirm_password: ''
});
const passwordLoading = ref(false);
const passwordSuccess = ref('');
const passwordError = ref('');

async function changePassword() {
    passwordLoading.value = true;
    passwordSuccess.value = '';
    passwordError.value = '';
    try {
        const response = await axios.post(
            tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/change-password',
            { ...passwordForm },
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            }
        );
        if (response.data.success) {
            passwordSuccess.value = response.data.message || 'Password changed successfully!';
            passwordForm.current_password = '';
            passwordForm.new_password = '';
            passwordForm.confirm_password = '';
            toast.success(passwordSuccess.value, { position: "bottom-right" });
        } else {
            passwordError.value = response.data.message || 'Failed to change password.';
            toast.error(passwordError.value, { position: "bottom-right" });
        }
    } catch (e) {
        passwordError.value = 'Failed to change password.';
        toast.error(passwordError.value, { position: "bottom-right" });
    } finally {
        passwordLoading.value = false;
        setTimeout(() => { passwordSuccess.value = ''; }, 3000);
    }
}

onMounted(fetchUserPublicInfo);

const imageChange = (attachment) => {   
    userPublicInformation.avatar = attachment.url; 
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
    userPublicInformation.avatar = ''; 
    activeProfileDropdown.value = false;
}
    

const imageChangeFeature = (attachment) => {   
    userPublicInformation.cover_image = attachment.url; 
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
    userPublicInformation.cover_image = '';
} 
// Profile Image and cover image dropdown
const activeCoverDropdown = ref(false);
const activeProfileDropdown = ref(false);

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
    <div :class="{ 'tfhb-skeleton': skeleton }" class="tfhb-admin-card-box tfhb-host-profile-image-wrap">
    
    <div class="tfhb-admin-card-box tfhb-host-profile-image-wrap"   
        :style="{
            'background-image': `url('${userPublicInformation.cover_image || $tfhb_url + '/assets/app/images/meeting-cover.png'}')`, 
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
                <img   class='avatar_display'  :src="userPublicInformation.avatar || $tfhb_url+'/assets/images/avator.png'" >
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
        <h2>{{ $tfhb_trans('Public Information') }}    </h2>  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">  
        <HbText  
            v-model="userPublicInformation.name_of_participant"  
            required= "true"  
            :label="$tfhb_trans('Name')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your name')" 
            width="50"
        /> 
        <HbText  
            v-model="userPublicInformation.job_title"  
            :label="$tfhb_trans('Job Title')"  
            selected = "1"
            :placeholder="$tfhb_trans('Job Title')" 
            width="50"
        />  
        <HbText  
            v-model="userPublicInformation.email"  
            required= "true"  
            :label="$tfhb_trans('Email')"  
            selected = "1"
             :placeholder="$tfhb_trans('Type your email')" 
            width="50"
            disabled="true"
        /> 
        <HbText  
            v-model="userPublicInformation.mobile_no"   
            :label="$tfhb_trans('Mobile')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your mobile no')" 
            width="50"  
        />  
        
        <HbText  
            v-model="userPublicInformation.address"   
            :label="$tfhb_trans('Address')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your Address')" 
            width="50"  
        />    
        <HbText  
            v-model="userPublicInformation.state"   
            :label="$tfhb_trans('State')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your State')" 
            width="50"  
        />
        <HbText  
            v-model="userPublicInformation.company_website"   
            :label="$tfhb_trans('Website')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your website')" 
            width="50"  
        />  
        <HbTextarea  
            v-model="userPublicInformation.description"   
            :label="$tfhb_trans('Description')"  
            :placeholder="$tfhb_trans('Type your description')" 
            width="100"  
        />  
         <HbButton 
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="saveUserPublicInfo"
            :buttonText="$tfhb_trans('Save & Continue')"
            icon="ChevronRight" 
            hover_icon="ArrowRight" 
            :hover_animation="true"
            :pre_loader="loading"
        />  
    </div>   

    <div class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('More Details') }}    </h2>  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox" style="flex-direction:column;">
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Travel Agent Name') }}:</strong>
        <span>{{ userPublicInformation['travel-agent-name'] }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Family Name of Participant') }}:</strong>
        <span>{{ userPublicInformation.family_name_of_participant }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Office Direct Phone') }}:</strong>
        <span>{{ userPublicInformation.office_direct_phone }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Passport Number') }}:</strong>
        <span>{{ userPublicInformation.passport_number }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Nation') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.nation)">
            {{ userPublicInformation.nation.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.nation }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Invitation Received From') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.invitation_received_from)">
            {{ userPublicInformation.invitation_received_from.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.invitation_received_from }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Areas of Activity') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.areas_of_activity)">
            {{ userPublicInformation.areas_of_activity.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.areas_of_activity }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Preferred Workshop Meetings') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.preferred_workshop_meetings)">
            {{ userPublicInformation.preferred_workshop_meetings.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.preferred_workshop_meetings }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Participation to Basilicata') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.participation_to_basilicata)">
            {{ userPublicInformation.participation_to_basilicata.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.participation_to_basilicata }}
          </template>
        </span>
      </div>
    </div>
   

    <div class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('Change Password') }}    </h2>  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">
        <HbText
            v-model="passwordForm.current_password"
            :label="$tfhb_trans('Current Password')"
            type="password"
            required="true"
            width="32"
        />
        <HbText
            v-model="passwordForm.new_password"
            :label="$tfhb_trans('New Password')"
            type="password"
            required="true"
            width="32"
        />
        <HbText
            v-model="passwordForm.confirm_password"
            :label="$tfhb_trans('Confirm New Password')"
            type="password"
            required="true"
            width="32"
        />
    </div>
    <HbButton
        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation"
        @click="changePassword"
        :buttonText="$tfhb_trans('Change Password')"
        icon="Lock"
        :pre_loader="passwordLoading"
    />
   
    </div>
</template>

<style scoped lang="scss">
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
.tfhb-info-row {
  display: flex;
  align-items: flex-start;
  width: 100%;
  padding: 12px 18px;
  background: #f8f9fb;
  border-radius: 6px;
  border: 1px solid #e3e6ed;
  box-shadow: 0 1px 2px rgba(30,40,90,0.04);
  transition: background 0.2s;
  box-sizing: border-box;
}
.tfhb-info-row strong {
  min-width: 240px;
  font-weight: 600;
  color: #2d3748;
  margin-right: 12px;
  line-height: 1.5;
}
.tfhb-info-row span {
  flex: 1;
  color: #4a5568;
  word-break: break-word;
  line-height: 1.5;
}
.tfhb-info-row:hover {
  background: #eef2f7;
}
</style>