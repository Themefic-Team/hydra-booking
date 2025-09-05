<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import HbQuestion from '@/components/widgets/HbQuestion.vue';
import Icon from '@/components/icon/LucideIcon.vue';
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue'; 
import HbPopup from '@/components/widgets/HbPopup.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';

import HbDropdown from '@/components/form-fields/HbDropdown.vue'

import HbText from '@/components/form-fields/HbText.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';


import HbInfoBox from '@/components/widgets/HbInfoBox.vue';

const informationPopup = ref(false);
import { AddonsSettings } from '@/store/settings/addons-settings';

  
 
</script>

<template>    
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Events Settings') }} </h2>  
        <p>{{ $tfhb_trans('Manage the settings for your events') }}</p>
    </div> 
 
    <div class="tfhb-admin-card-box tfhb-flexbox  tfhb-gap-24"  >    
        <!-- <HbSwitch  
            v-model="AddonsSettings.event_settings.guest_booking" 
            :label="'Enable Guest Booking'"   
            width="100"
        />  -->
        <HbDropdown 
            v-if="Array.isArray(AddonsSettings.meeting_list) && AddonsSettings.meeting_list.length > 0"
            v-model="AddonsSettings.event_settings.meeting_id" 
            :label="'Select Default Meeting'"   
            width="100"
            :selected = "1"
            :placeholder="$tfhb_trans('Select')"
            :option="AddonsSettings.meeting_list"
        /> 
        <div v-else-if="AddonsSettings.skeleton" class="tfhb-skeleton-placeholder">
            <div class="tfhb-skeleton-line"></div>
        </div>
        <div v-else class="tfhb-no-meetings">
            <p>{{ $tfhb_trans('No meetings available') }}</p>
        </div>
        <HbText  
            v-model="AddonsSettings.event_settings.live_chat_url"  
            required= "true"  
            :label="$tfhb_trans('Live Chat URL')"  
            selected = "1"
            :placeholder="$tfhb_trans('Live Chat URL')" 
            width="100" 
        /> 
    </div>  
    <HbButton 
        classValue="tfhb-btn boxed-btn flex-btn tfhb-mt-16" 
        @click="AddonsSettings.UpdateEventsSettings()" 
        :buttonText="$tfhb_trans('Save Changes')"
        icon="ChevronRight" 
        hover_icon="ArrowRight" 
        :pre_loader="AddonsSettings.update_preloader"
        :hover_animation="true"
    />    
        
</template>

<style scoped>
.tfhb-skeleton-placeholder {
    width: 100%;
    padding: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #f9f9f9;
}

.tfhb-skeleton-line {
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
}

.tfhb-no-meetings {
    width: 100%;
    padding: 16px;
    text-align: center;
    color: #666;
    border: 1px dashed #ccc;
    border-radius: 8px;
    background: #fafafa;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style> 