<script setup>
import { __ } from '@wordpress/i18n';
import { reactive, ref, onBeforeMount } from 'vue';
import axios from 'axios'  
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue'  
import HbButton from '@/components/form-fields/HbButton.vue';

import Integrations from '@/components/meetings/Integrations.vue';
import { IntegrationsValue } from '@/store/meetings/integrations'; 

import Webhook from '@/components/meetings/Webhook.vue';
import { webhookData } from '@/store/meetings/webhook'; 

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
    integrations: {
        type: Object,
        required: true
    },
    update_preloader: {
        type: Boolean,
        required: true
    }

});
onBeforeMount(() => {  
    IntegrationsValue.integrationsData.meeting_id = props.meetingId;
    IntegrationsValue.meeting = props.meeting;
});
 

</script>

<template>

<!-- {{ integrationsData.bodys  }} -->
<div class="meeting-create-details tfhb-gap-24"> 
    <!-- Integrations  -->
    <div class="tfhb-meeting-integrations-wrap tfhb-full-width tfhb-flexbox tfhb-gap-16 ">

        <Integrations :IntegrationsValue="IntegrationsValue" :meeting="meeting" :integrations="integrations" />

    </div>

    <!-- WebHook -->
    <div class="tfhb-meeting-webhook-wrap  tfhb-full-width tfhb-flexbox tfhb-gap-16" v-if="$tfhb_is_pro == true  && $tfhb_license_status == true">
        <Webhook :meetingId="props.meetingId" :meeting="meeting" :integrations="integrations" />
    </div>
    <div class="tfhb-meeting-webhook-wrap  tfhb-full-width  tfhb-pro tfhb-flexbox tfhb-gap-16" v-else>
        <div class="tfhb-webhook-title tfhb-flexbox">
            <div class="tfhb-admin-title tfhb-m-0">
                <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    {{ __('Webhook Integration ', 'hydra-booking') }}
                    <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
                </h2> 
                <h2></h2> 
                <p>{{ __('Webhook integration enables automated data transfer between apps, allowing real-time communication and custom API interactions.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-admin-card-box tfhb-flexbox tfhb-align-baseline tfhb-m-0 tfhb-full-width tfhb-pro">
            <div class="tfhb-integration-box">
                <button class="tfhb-btn  tfhb-flexbox tfhb-gap-8">
                    <Icon name="PlusCircle" :width="20"/>
                    {{ __('Add New Integrations', 'hydra-booking') }}
                </button>  
            </div> 
        </div>
    </div>
    
     <!-- WebHook -->

     <div class="tfhb-submission-btn"> 
        <HbButton  
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="emit('update-meeting')"
            :buttonText="__('Save & Continue', 'hydra-booking')"
            icon="ChevronRight" 
            hover_icon="ArrowRight" 
            :hover_animation="true"
            :pre_loader="props.update_preloader"
        />   
    </div>
</div>
</template>

<style scoped>

</style>