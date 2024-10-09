<script setup>
import { reactive, ref, onBeforeMount } from 'vue';
import axios from 'axios'  
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue'  
import HbButton from '@/components/form-fields/HbButton.vue';

import Integrations from '@/components/meetings/Integrations.vue';
import { IntegrationsValue } from '@/store/meetings/integrations'; 
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
    <div class="tfhb-meeting-integrations-wrap tfhb-full-width ">

        <Integrations :IntegrationsValue="IntegrationsValue" :meeting="meeting" />

    </div>

     <!-- WebHook -->
     <div class="tfhb-meeting-webhook-wrap  tfhb-full-width  tfhb-pro">
        <div class="tfhb-webhook-title tfhb-flexbox">
            <div class="tfhb-admin-title tfhb-m-0">
                <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    {{ $tfhb_trans('Webhook Integration ') }}
                    <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
                </h2> 
                <h2></h2> 
                <p>{{ $tfhb_trans('How many days can the invitee schedule?') }}</p>
            </div>
        </div>
        <div class="tfhb-admin-card-box tfhb-flexbox tfhb-align-baseline tfhb-m-0 tfhb-full-width tfhb-pro">
            <div class="tfhb-integration-box">
                <button class="tfhb-btn  tfhb-flexbox tfhb-gap-8">
                    <Icon name="PlusCircle" :width="20"/>
                    {{ $tfhb_trans('Add New Integrations') }}
                </button>  
            </div> 
        </div>
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
        />   
    </div>
</div>
</template>

<style scoped>

</style>