<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, watch } from 'vue'; 
import axios from 'axios';
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';

const props = defineProps([
    'class', 
    'display', 
    'stripe_data', 
    'pre_loader', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

// Ensure default test_mode and sync keys
onBeforeMount(() => {
    // Determine default mode based on existing keys if test_mode not explicitly set
    if (typeof props.stripe_data.test_mode === 'undefined' || props.stripe_data.test_mode === null) {
        if (props.stripe_data.public_key && props.stripe_data.public_key.startsWith('pk_live_')) {
            props.stripe_data.test_mode = 0; // Live mode
        } else {
            props.stripe_data.test_mode = 1; // Test mode default
        }
    }

    // Initialize cached keys for test and live
    if (props.stripe_data.public_key) {
        if (props.stripe_data.public_key.startsWith('pk_live_')) {
            props.stripe_data.live_public_key = props.stripe_data.public_key;
        } else if (props.stripe_data.public_key.startsWith('pk_test_')) {
            props.stripe_data.test_public_key = props.stripe_data.public_key;
        }
    }
    if (props.stripe_data.secret_key) {
        if (props.stripe_data.secret_key.startsWith('sk_live_')) {
            props.stripe_data.live_secret_key = props.stripe_data.secret_key;
        } else if (props.stripe_data.secret_key.startsWith('sk_test_')) {
            props.stripe_data.test_secret_key = props.stripe_data.secret_key;
        }
    }

    // Populate active fields if empty but cached mode keys exist
    if (!props.stripe_data.public_key) {
        props.stripe_data.public_key = (props.stripe_data.test_mode == 1) 
            ? (props.stripe_data.test_public_key || '') 
            : (props.stripe_data.live_public_key || '');
    }
    if (!props.stripe_data.secret_key) {
        props.stripe_data.secret_key = (props.stripe_data.test_mode == 1) 
            ? (props.stripe_data.test_secret_key || '') 
            : (props.stripe_data.live_secret_key || '');
    }
});

// Watch mode switch to swap active fields between Test and Live caches
watch(() => props.stripe_data.test_mode, (newMode, oldMode) => {
    if (typeof oldMode === 'undefined') return;

    // Cache current active values
    if (oldMode == 1) {
        props.stripe_data.test_public_key = props.stripe_data.public_key;
        props.stripe_data.test_secret_key = props.stripe_data.secret_key;
    } else {
        props.stripe_data.live_public_key = props.stripe_data.public_key;
        props.stripe_data.live_secret_key = props.stripe_data.secret_key;
    }

    // Restore new mode's values
    if (newMode == 1) {
        props.stripe_data.public_key = props.stripe_data.test_public_key || '';
        props.stripe_data.secret_key = props.stripe_data.test_secret_key || '';
    } else {
        props.stripe_data.public_key = props.stripe_data.live_public_key || '';
        props.stripe_data.secret_key = props.stripe_data.live_secret_key || '';
    }

    testConnStatus.value = null;
    testConnMessage.value = '';
});

// Keep mode-specific caches synchronized as user types
watch(() => props.stripe_data.public_key, (newVal) => {
    if (props.stripe_data.test_mode == 1) {
        props.stripe_data.test_public_key = newVal;
    } else {
        props.stripe_data.live_public_key = newVal;
    }
});
watch(() => props.stripe_data.secret_key, (newVal) => {
    if (props.stripe_data.test_mode == 1) {
        props.stripe_data.test_secret_key = newVal;
    } else {
        props.stripe_data.live_secret_key = newVal;
    }
});

const isConnected = () => {
    const data = props.stripe_data;
    return !!(data.public_key && data.secret_key && data.public_key !== 'null');
}

const handleSave = () => {
    // Ensure active keys are synced into both active and mode cache
    if (props.stripe_data.test_mode == 1) {
        props.stripe_data.test_public_key = props.stripe_data.public_key;
        props.stripe_data.test_secret_key = props.stripe_data.secret_key;
    } else {
        props.stripe_data.live_public_key = props.stripe_data.public_key;
        props.stripe_data.live_secret_key = props.stripe_data.secret_key;
    }
    emit('update-integrations', 'stripe', props.stripe_data, ['public_key', 'secret_key']);
}

// Test Connection Action
const testConnLoading = ref(false);
const testConnStatus = ref(null); // 'success' | 'error' | null
const testConnMessage = ref('');

const runTestConnection = async () => {
    testConnLoading.value = true;
    testConnStatus.value = null;
    testConnMessage.value = '';

    const secretKey = props.stripe_data.secret_key;

    if (!secretKey) {
        testConnStatus.value = 'error';
        testConnMessage.value = 'Please enter a Stripe Secret Key before testing.';
        testConnLoading.value = false;
        return;
    }

    try {
        const formData = new URLSearchParams();
        formData.append('action', 'tfhb_stripe_test_connection');
        formData.append('nonce', typeof tfhb_core_apps !== 'undefined' && tfhb_core_apps.rest_nonce ? tfhb_core_apps.rest_nonce : '');
        formData.append('secret_key', secretKey);
        formData.append('test_mode', props.stripe_data.test_mode);

        const ajaxUrl = (typeof window.ajaxurl !== 'undefined' && window.ajaxurl) 
            ? window.ajaxurl 
            : ((typeof tfhb_core_apps !== 'undefined' && tfhb_core_apps.ajax_url) ? tfhb_core_apps.ajax_url : '/wp-admin/admin-ajax.php');

        const response = await axios.post(ajaxUrl, formData, {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        if (response.data && response.data.success) {
            testConnStatus.value = 'success';
            testConnMessage.value = response.data.data.message || 'Connection successful! Stripe API keys are valid.';
        } else {
            testConnStatus.value = 'error';
            testConnMessage.value = (response.data && response.data.data && response.data.data.message) 
                ? response.data.data.message 
                : 'Stripe API validation failed. Check your Secret Key.';
        }
    } catch (err) {
        testConnStatus.value = 'error';
        testConnMessage.value = err?.response?.data?.data?.message || err.message || 'Failed to connect to Stripe API.';
    } finally {
        testConnLoading.value = false;
    }
};

</script>

<template>
      <!-- Stripe Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class,{
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >
        <span v-if="$tfhb_is_pro == false  || $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
         
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/stripe.png'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Stripe') }}</h3>
                <p>{{ $tfhb_trans('Integrate Stripe Elements API for secure payment processing (WooCommerce standard).') }}</p>
            </div>
        </div>
        <div v-if="$tfhb_is_pro == false  || $tfhb_license_status == false"  class="tfhb-integrations-single-block-btn tfhb-flexbox"> 
            <span   v-if=" props.from == 'host' && stripe_data.connection_status != '1'" class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
           
            <router-link v-else to="/settings/license" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upgrade to Pro') }}  <Icon name="ChevronRight" size=18 /></router-link>
 
        </div>

        <div v-if="$tfhb_is_pro == true &&  $tfhb_license_status == true" class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
             
            <HbButton  
                @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                :buttonText="isConnected() ? 'Connected' : 'Connect' "
                :hover_animation="true"   
                width="80px"
            /> 

            <HbSwitch 
                v-if="isConnected()" 
                @change="emit('update-integrations', 'stripe', stripe_data)" v-model="stripe_data.status"    
             /> 
        </div>

        <HbPopup  v-if="$tfhb_is_pro == true  || $tfhb_license_status == true"  :isOpen="ispopup" @modal-close="closePopup" max_width="650px" name="first-modal">
            <template #header> 
                <h2>{{ $tfhb_trans('Connect Your Stripe Account') }}</h2>
            </template>

            <template #content>  
                <p class="tfhb-full-width">
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get API credentials from Stripe Account') }}
                    
                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/stripe/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                </p>

                <!-- Environment Selector -->
                <HbDropdown 
                    v-model="stripe_data.test_mode"
                    required="true"  
                    name="test_mode"
                    :label="$tfhb_trans('Environment / Mode')"   
                    selected="1"
                    placeholder="Select Environment"  
                    :option="[
                        {name: 'Test Mode (Sandbox)', value: 1},  
                        {name: 'Live Mode (Production)', value: 0},  
                    ]" 
                />

                <!-- Stripe Publishable Key -->
                <HbText  
                    v-model="stripe_data.public_key"  
                    required="true"  
                    name="public_key"
                    :errors="errors.public_key"
                    :label="$tfhb_trans('Stripe Publishable Key')"  
                    selected="1"
                    :placeholder="stripe_data.test_mode == 1 ? 'pk_test_...' : 'pk_live_...'"  
                /> 

                <!-- Stripe Secret Key (Password Protected) -->
                <HbText  
                    v-model="stripe_data.secret_key"  
                    required="true"  
                    type="password"
                    name="secret_key"
                    :errors="errors.secret_key"
                    :label="$tfhb_trans('Stripe Secret Key')"  
                    selected="1"
                    :placeholder="stripe_data.test_mode == 1 ? 'sk_test_...' : 'sk_live_...'"  
                />

                <!-- Test Mode Info Notice -->
                <div v-if="stripe_data.test_mode == 1" class="tfhb-notice tfhb-info tfhb-mt-8 tfhb-full-width" style="padding: 10px 14px; background: #f0f7ff; border-left: 4px solid #0073aa; border-radius: 4px; font-size: 13px; color: #1e3a5f; width: 100%;">
                    <strong>{{ $tfhb_trans('Test Mode Info:') }}</strong>
                    {{ $tfhb_trans('Transactions will not charge real cards. Use test card 4242 4242 4242 4242 with any future expiry date and CVC to simulate successful payments.') }}
                </div>

                <div class="tfhb-notice tfhb-warning tfhb-mt-8 tfhb-full-width" style="padding: 10px 14px; background: #fff8e5; border-left: 4px solid #f0ad4e; border-radius: 4px; font-size: 12px; color: #8a6d3b; margin-bottom: 8px; width: 100%;">
                    <strong>{{ $tfhb_trans('Important Notice:') }}</strong>
                    {{ $tfhb_trans('Stripe requires every charge to meet minimum currency amounts (equivalent to approx $0.50 USD). Meeting prices set below this threshold will be rejected by Stripe.') }}
                </div>

                <!-- Test Connection Feedback Message -->
                <div v-if="testConnStatus" class="tfhb-mb-16 tfhb-full-width" style="padding: 10px 14px; border-radius: 6px; font-size: 13px; display: flex; align-items: center; gap: 8px; width: 100%;"
                     :style="testConnStatus === 'success' 
                        ? 'background: #e6f4ea; color: #137333; border: 1px solid #ceead6;' 
                        : 'background: #fce8e6; color: #c5221f; border: 1px solid #fad2cf;'">
                    <Icon :name="testConnStatus === 'success' ? 'CheckCircle2' : 'AlertCircle'" size="18" />
                    <span>{{ testConnMessage }}</span>
                </div>

                <div class="tfhb-flexbox tfhb-justify-between tfhb-align-center tfhb-mt-16 tfhb-full-width" style="width: 100%;">
                    <button type="button" 
                            class="tfhb-btn tfhb-stripe-test-btn" 
                            @click.stop="runTestConnection" 
                            :disabled="testConnLoading"
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; font-size: 13px; font-weight: 500; border: 1px solid #dcdfe6; border-radius: 6px; background: #ffffff; cursor: pointer; color: #374151; transition: all 0.2s ease;">
                        <Icon v-if="!testConnLoading" name="Activity" size="14" />
                        <span v-if="testConnLoading" class="tfhb-spinner-mini" style="display: inline-block; width: 14px; height: 14px; border: 2px solid #ccc; border-top-color: #2E6B38; border-radius: 50%; animation: tfhb-spin 0.8s linear infinite;"></span>
                        <span>{{ testConnLoading ? $tfhb_trans('Testing...') : $tfhb_trans('Test API Connection') }}</span>
                    </button>

                    <HbButton  
                        @click.stop="handleSave"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                        :buttonText="'Save & Validate'"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        :pre_loader="props.pre_loader"
                    />   
                </div>
            </template> 
        </HbPopup>

    </div>  
    <!-- Single Integrations  -->
</template>

<style scoped>
@keyframes tfhb-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.tfhb-stripe-test-btn:hover:not(:disabled) {
    background-color: #f9fafb !important;
    border-color: #c0c4cc !important;
}
</style>