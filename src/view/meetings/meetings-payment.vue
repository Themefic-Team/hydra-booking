<script setup>
import { __ } from '@wordpress/i18n';
import {ref, onBeforeMount} from 'vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue'

// component
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { Meeting } from '@/store/meetings'


const emit = defineEmits(["update-meeting"]); 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },
    wcProduct: {
        type: Object,
        required: true
    },
    update_preloader: {
        type: Boolean,
        required: true
    }

});


const host = ref(true);
const attendee = ref(false);

onBeforeMount(() => {
    Meeting.fetchMeetingsPaymentIntegration();
})

</script>

<template>
    <div class="meeting-create-details tfhb-gap-24">
        <div class="tfhb-admin-title tfhb-m-0 tfhb-full-width">

            <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                {{ __('Payment for this Meeting ', 'hydra-booking') }}
                
                <HbSwitch 
                    v-model="meeting.payment_status"
                />
            </h2> 
            <p>{{ __('Securely process payment for this meeting using WooCommerce Payments, Stripe, or PayPal for a seamless transaction experience.', 'hydra-booking') }}</p>
        </div> 
        <div v-if="meeting.payment_status == 1"  class="tfhb-notification-wrap tfhb-admin-card-box tfhb-m-0 tfhb-gap-32 tfhb-full-width">
            
            <div  class="tfhb-content-wrap tfhb-full-width"> 
                <div class="tfhb-integrations-wrap tfhb-flexbox"> 

                    <HbDropdown 
                        v-model="meeting.payment_method" 
                        required= "true" 
                        :label="__('Payment Method', 'hydra-booking')"  
                        :selected = "1"
                        name="payment_method"
                        placeholder="Select Payment Method"  
                        :option = "[
                            {name: 'Woocommerce', value: 'woo_payment', disable: Meeting.meetingPaymentIntegration.woo_payment},  
                            {name: 'Paypal', value: 'paypal_payment', disable: Meeting.meetingPaymentIntegration.paypal}, 
                            {name: 'Stripe Pay', value: 'stripe_payment', disable: Meeting.meetingPaymentIntegration.stripe}, 
                        ]"   
                    /> 
                    <!-- Woo Integrations  -->
                </div> 
            </div>
            <div v-if="meeting.payment_status == 1 && meeting.payment_method=='woo_payment'" class="tfhb-single-form-field" style="width: 100%;" selected="1">
                <HbDropdown 
                    v-model="meeting.payment_meta.product_id" 
                    required= "true" 
                    :filter="true"
                    :label="__('Selecte Product', 'hydra-booking')"  
                    :selected = "1"
                    name="payment_meta"
                    placeholder="Select Product"  
                    :option = "props.wcProduct"   
                /> 
            </div>
            <div v-if="meeting.payment_status == 1 && meeting.payment_method=='stripe_payment' || meeting.payment_method=='paypal_payment'" class="tfhb-single-form-field" style="width: 100%;" selected="1">
                <div class="tfhb-single-form-field-wrap tfhb-field-input">
                    <label>{{ __('Price', 'hydra-booking') }} <span> *</span></label>
                    <div class="tfhb-meeting-currency tfhb-flexbox tfhb-justify-normal tfhb-gap-0">
                        <input v-model="meeting.meeting_price" required="" type="text" placeholder="00.000">
                        <!-- <select v-model="meeting.payment_currency" placeholder="USD">
                            <option value="USD">USD</option>
                            <option value="EUR">Euro</option>
                        </select> -->
                        <HbDropdown 
                            v-model="meeting.payment_currency"   
                            name="payment_method"
                            placeholder="Currency"  
                            :option = "[
                                {name: 'USD', value: 'USD'},  
                                {name: 'EUR', value: 'EUR'},   
                            ]"   
                        /> 
                        
                       
                    </div>
                </div>
            </div>
            

        </div> 
        <div class="tfhb-submission-btn">
            <HbButton  
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="emit('update-meeting')"
            :buttonText="__('Save & Preview', 'hydra-booking')"
            icon="ChevronRight" 
            hover_icon="ArrowRight" 
            :hover_animation="true"
            :pre_loader="props.update_preloader"
        />  
        </div>
        <!--Bookings -->
    </div>
</template>