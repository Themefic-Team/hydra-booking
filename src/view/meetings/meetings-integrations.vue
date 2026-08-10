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
 
    <div class="tfhb-meeting-integrations-wrap tfhb-full-width tfhb-flexbox tfhb-gap-16 ">

        <Integrations :IntegrationsValue="IntegrationsValue" :meeting="meeting" :integrations="integrations" />

    </div>

    <!-- WebHook -->
    <div class="tfhb-meeting-webhook-wrap  tfhb-full-width tfhb-flexbox tfhb-gap-16" v-if="$tfhb_is_pro == true  && $tfhb_license_status == true">
        <Webhook :meetingId="props.meetingId" :meeting="meeting" :integrations="integrations" />
    </div>
    <!-- WebHook -->

     <div class="tfhb-submission-btn"> 
        <HbButton  
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="emit('update-meeting')"
            :buttonText="$tfhb_trans('Save & Continue')"
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