<script setup>
import { ref, reactive, computed, onBeforeMount } from 'vue';
import { useRoute } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbText from '@/components/form-fields/HbText.vue'; 
import { toast } from "vue3-toastify";
import { LicenseBase } from '@/store/license'; 
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();
const route = useRoute();

const props = defineProps({
  title: String,
  desc: String,
  icon: String,
  isblocked: Boolean,
  btntext: String
});

const licenseing = reactive({
  isOpen: false
});

const UnlockPopup = () => {
  licenseing.isOpen = false;
}
const License_pre_loader = ref(false);
const licenseingData = reactive({
  email: ''
});
const isValidEmail = computed(() => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(licenseingData.email);
});

const LicenseMessage = reactive({
  status: '',
  message: '',
  exits: false
});

const GenaratePasswordLink = async () => {
  const formData = new FormData();
  formData.append("action", "tfhb_user_registration_license");
  formData.append("email", licenseingData.email);
  formData.append("nonce", tfhb_core_apps.rest_nonce);

  License_pre_loader.value = true;
  try {
    const response = await fetch(tfhb_core_apps.ajax_url, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    if (result.success) {
      License_pre_loader.value = false;
      LicenseMessage.status = true;
      LicenseMessage.message = result.data.message;
      LicenseMessage.exits = false;
      LicenseBase.license_email = licenseingData.email;
    } else {
      License_pre_loader.value = false;
      LicenseMessage.status = false;
      LicenseMessage.exits = result.data.exits;
      LicenseMessage.message = result.data.message || "Something went wrong.";
      licenseingData.email = ''
    }
  } catch (error) {
    License_pre_loader.value = false;
    console.error("AJAX Error:", error);
    LicenseMessage.value = "Failed to connect to the server.";
  }
};
 
onBeforeMount(async () => {
  LicenseBase.GetLicense();
});

const updateLicense = async (validator_field) => {

    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added 
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!LicenseBase[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!LicenseBase[fieldParts[0]][fieldParts[1]]){
                errors[fieldParts[0]+'___'+[fieldParts[1]]] = 'Required this field';
            }
        }
            
        });
    }

    // Errors Checked
    const isEmpty = Object.keys(errors).length === 0;
    if(!isEmpty){ 
        toast.error('Fill Up The Required Fields', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        }); 
        return
    }

    // if LicenseBase.license_email is not email address then return
    if(LicenseBase.license_email && !LicenseBase.license_email.includes('@')){
        toast.error(' Please enter a valid email address', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return
    }
   
    LicenseBase.path = route.fullPath;

    LicenseBase.UpdateLicense();
}

</script>

<template>
  <HbPopup :isOpen="licenseing.isOpen" max_width="450px" name="first-modal" @modal-close="UnlockPopup()">
      <template #header> 
        <h3 v-if="!LicenseMessage.exits && !LicenseMessage.status">{{ $tfhb_trans('Let’s Get Started!') }}</h3>
        <h3 v-if="LicenseMessage.exits">{{ $tfhb_trans('Already used!') }}</h3>
        <h3 v-if="!LicenseMessage.exits && LicenseMessage.status">{{ $tfhb_trans('Sent successfully!') }}</h3>
      </template>

      <template #content> 
          <p v-if="!LicenseMessage.exits && !LicenseMessage.status">
            {{ $tfhb_trans('Please provide your email address. We’ll send your key directly to your inbox.') }}
          </p> 
          <p v-if="LicenseMessage.exits">
            {{ $tfhb_trans('You already used this email. Use another email address or click on the following link to obtain the previous license key.') }}
          </p> 
          <p v-if="!LicenseMessage.exits && LicenseMessage.status">
            {{ $tfhb_trans('Check your inbox and set a password for your security then paste the license key below.') }}
          </p> 
          
          <a href="https://portal.themefic.com/my-account/licenses/" target="_blank" v-if="LicenseMessage.exits" class="tfhb-license-url">https://portal.themefic.com/my-account/licenses/</a>

          <div class="tfhb-license-email-field" v-if="!LicenseMessage.status">
            <HbText  
              type="email"
              v-model="licenseingData.email"
              required= "true"  
              selected = "1"
              placeholder="Enter your email address" 
            />
            <div class="tfhb-validate-notice empty" v-if="!licenseingData.email">
              user@example.com
            </div>
            <div class="tfhb-validate-notice invalid" v-if="licenseingData.email && !isValidEmail">
              {{ $tfhb_trans('Please enter a valid email address.') }}
            </div>
            <div class="tfhb-validate-notice valid tfhb-flexbox tfhb-gap-4" v-if="licenseingData.email && isValidEmail">
              <Icon name="Check" :size="20" />{{ $tfhb_trans('Perfect') }}
            </div>
          </div>
          <div class="tfhb-submission-btn tfhb-gap-8" :class="!isValidEmail ? 'tfhb-disable-btn' : ''" v-if="!LicenseMessage.status">
            <HbButton 
              @click="GenaratePasswordLink()"
              classValue="tfhb-btn boxed-btn flex-btn" 
              :buttonText="$tfhb_trans('Send me the License key')"
              icon="ChevronRight" 
              hover_icon="ArrowRight" 
              :hover_animation="true" 
              :pre_loader="License_pre_loader"
            />  
          </div> 
          
          <div class="tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between" v-if="LicenseMessage.status">  
            <HbText  
                v-model="LicenseBase.license_key"  
                required= "true"  
                type="password"
                name="license_key"
                :label="$tfhb_trans('License Key')"  
                selected = "1"
                :placeholder="$tfhb_trans('Enter your License key')"  
                :errors="errors.license_key"
            /> 
            <HbText  
                v-model="LicenseBase.license_email"  
                required= "true"  
                type="email"
                :label="$tfhb_trans('License Email')"  
                name="license_email"
                selected = "1"
                :placeholder="$tfhb_trans('Type your Admin Email')"  
                :errors="errors.license_email"
            /> 
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click.stop="updateLicense(['license_key', 'license_email'])" 
                :buttonText="$tfhb_trans('Activate')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true" 
                :pre_loader="LicenseBase.License_loader"
            /> 
          </div>  

      </template> 
  </HbPopup>
  
  <div class="tfhb-info-box tfhb-flexbox tfhb-gap-16 tfhb-p-24 tfhb-full-width" :class="isblocked ? 'tfhb-blue-border' : ''">
    <div class="tfhb-info-box-icon" v-if="!isblocked">
        <Icon :name="icon ?? 'Info'" :size="20" />
    </div>
    <div class="tfhb-info-box-content" :class="isblocked ? 'tfhb-flexbox tfhb-unlock-box' : 'tfhb-full-width'">
        <div class="tfhb-info-box-desc">
          <h3 v-if="props.title">{{ props.title }}</h3>
          <slot name="content"> {{$tfhb_trans('default content')}}</slot>
        </div>
        <HbButton 
          v-if="isblocked"
          @click="licenseing.isOpen=true"
          classValue="tfhb-btn boxed-btn flex-btn" 
          :buttonText="props.btntext"
        />  
    </div>
  </div>
</template>