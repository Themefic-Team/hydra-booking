<script setup>
import { __ } from '@wordpress/i18n';
import {ref, onBeforeMount} from 'vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue'
import { useRouter} from 'vue-router' 
// component
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbText from '@/components/form-fields/HbText.vue';
import { Meeting } from '@/store/meetings'


const router = useRouter();

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
                {{ $tfhb_trans('Payment for this Meeting') }}
                
                <HbSwitch 
                    v-model="meeting.payment_status"
                />
            </h2> 
            <p>{{ $tfhb_trans('Securely process payment for this meeting using WooCommerce Payments, Stripe, or PayPal for a seamless transaction experience.') }}</p>
        </div> 
        <div v-if="meeting.payment_status == 1"  class="tfhb-notification-wrap tfhb-admin-card-box tfhb-m-0 tfhb-gap-32 tfhb-full-width">
            
            <div  class="tfhb-content-wrap tfhb-full-width"> 
                <div class="tfhb-integrations-wrap tfhb-flexbox tfhb-gap-4"> 

                    <HbDropdown 
                        v-model="meeting.payment_method" 
                        required= "true" 
                        :label="$tfhb_trans('Payment Method')"  
                        :selected = "1"
                        name="payment_method"
                        :placeholder="$tfhb_trans('Select Payment Method')"  
                        :option = "[
                            {name: 'Woocommerce', value: 'woo_payment', icon: $tfhb_url+'/assets/images/Woo.png',  },  
                            {name: 'Paypal', value: 'paypal_payment', icon: $tfhb_url+'/assets/images/paypal.svg',}, 
                            {name: 'Stripe Pay', value: 'stripe_payment', icon: $tfhb_url+'/assets/images/stripe-small.svg',}, 
                        ]"   
                    /> 

                    <div  v-if="meeting.payment_method == 'woo_payment' && Meeting.meetingPaymentIntegration.woo_payment == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4"> {{ $tfhb_trans('Woocommerce is not connected.') }} 
                        <HbButton 
                            v-if="$user.role != 'tfhb_host'"
                            classValue="tfhb-btn flex-btn" 
                            @click="() => router.push({ name: 'SettingsAntegrations' })" 
                            :buttonText="$tfhb_trans('Please Configure')"
                        />  
                    </div>
                    <div  v-if="meeting.payment_method == 'paypal_payment' && Meeting.meetingPaymentIntegration.paypal == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4">{{ $tfhb_trans('Paypal is not connected.') }}  
                        <HbButton 
                            v-if="$user.role != 'tfhb_host'"
                            classValue="tfhb-btn flex-btn" 
                            @click="() => router.push({ name: 'SettingsAntegrations' })" 
                            :buttonText="$tfhb_trans('Please Configure')"
                        />  
                    </div>
                    <div  v-if="meeting.payment_method == 'stripe_payment' && Meeting.meetingPaymentIntegration.stripe == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4 tfhb-mt-4"> {{ $tfhb_trans('Stripe is not connected.') }}  
                        <HbButton  
                            v-if="$user.role != 'tfhb_host'"
                            classValue="tfhb-btn flex-btn" 
                            @click="() => router.push({ name: 'SettingsAntegrations' })" 
                            :buttonText="$tfhb_trans('Please Configure')"
                        />  
                    </div>
                    <!-- Woo Integrations  -->
                </div> 
            </div>

            <div v-if="meeting.payment_status == 1 && meeting.payment_method=='woo_payment' && Meeting.meetingPaymentIntegration.woo_payment == false" class="tfhb-single-form-field" style="width: 100%;" selected="1">
                <HbDropdown 
                    v-model="meeting.payment_meta.product_id" 
                    required= "true" 
                    :filter="true"
                    :label="$tfhb_trans('Selecte Product')"  
                    :selected = "1"
                    name="payment_meta"
                    :placeholder="$tfhb_trans('Selecte Product')"  
                    :option = "props.wcProduct"   
                /> 
            </div>
            <div v-if="meeting.payment_status == 1 && meeting.payment_method=='stripe_payment' || meeting.payment_method=='paypal_payment'" class="tfhb-single-form-field tfhb-flexbox" style="width: 100%;" selected="1">
                <HbText  
                    v-model="meeting.meeting_price"  
                    type= "number"
                    required= "true" 
                    :label="$tfhb_trans('Price')"   
                    :placeholder="'00.000'" 
                    :width= "60"
                
                /> 
                <HbDropdown 
                    v-model="meeting.payment_currency"   
                    name="payment_method"
                    required= "true" 
                    :label="$tfhb_trans('Currency')"  
                    placeholder="Currency"  
                    :width= "40"
                    :filter="true"
                    :option = "Meeting.currency_list"   
                />  
            </div>
            

        </div> 
        <div class="tfhb-submission-btn">
            <HbButton  
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="emit('update-meeting')"
            :buttonText="$tfhb_trans('Save & Finish')"
            icon="ChevronRight" 
            hover_icon="ArrowRight" 
            :hover_animation="true"
            :pre_loader="props.update_preloader"
        />  
        </div>
        <!--Bookings -->
    </div>
</template>