<script setup>
import { ref, reactive, computed } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbText from '@/components/form-fields/HbText.vue'; 

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
  message: ''
});

const GenaratePasswordLink = async () => {
  const formData = new FormData();
  formData.append("action", "tfhb_user_registration_license");
  formData.append("email", licenseingData.email);
  formData.append("nonce", tfhb_core_apps.rest_nonce);

  License_pre_loader.value = true;
  try {
    const response = await fetch("/wp-admin/admin-ajax.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    if (result.success) {
      License_pre_loader.value = false;
      LicenseMessage.status = true;
      LicenseMessage.message = result.data.message;
    } else {
      License_pre_loader.value = false;
      LicenseMessage.status = false;
      LicenseMessage.message = result.data.message || "Something went wrong.";
    }
  } catch (error) {
    License_pre_loader.value = false;
    console.error("AJAX Error:", error);
    LicenseMessage.value = "Failed to connect to the server.";
  }
};

</script>

<template>
  <HbPopup :isOpen="licenseing.isOpen" max_width="450px" name="first-modal" @modal-close="UnlockPopup()">
      <template #header> 
        <h3>{{ $tfhb_trans('Let’s Get Started!') }}</h3>
      </template>

      <template #content>  
          <p>
            {{ $tfhb_trans('Please provide your email address. We’ll send your key directly to your inbox.') }}
          </p> 
          <div class="tfhb-license-message" v-if="LicenseMessage.message">
            <div :class="LicenseMessage.status ? 'success' : 'error'">
              {{ LicenseMessage.message }}
            </div>
          </div>
          <div class="tfhb-license-email-field" v-if="!LicenseMessage.message">
            <HbText  
              type="email"
              v-model="licenseingData.email"
              required= "true"  
              selected = "1"
              placeholder="Enter your email address" 
            />
            <div class="tfhb-validate-notice invalid" v-if="licenseingData.email && !isValidEmail">
              {{ $tfhb_trans('Please enter a valid email address.') }}
            </div>
            <div class="tfhb-validate-notice valid tfhb-flexbox tfhb-gap-4" v-if="licenseingData.email && isValidEmail">
              <Icon name="Check" :size="20" />{{ $tfhb_trans('Perfect') }}
            </div>
          </div>
          <div class="tfhb-submission-btn tfhb-gap-8" :class="!isValidEmail ? 'tfhb-disable-btn' : ''" v-if="!LicenseMessage.message">
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
      </template> 
  </HbPopup>
  
  <div class="tfhb-info-box tfhb-flexbox tfhb-gap-16 tfhb-p-24 tfhb-full-width">
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