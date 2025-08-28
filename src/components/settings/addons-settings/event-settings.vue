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
            v-model="AddonsSettings.event_settings.meeting_id" 
            :label="'Select Default Meeting'"   
            width="100"
            :selected = "1"
            :placeholder="$tfhb_trans('Select')"
            :option="AddonsSettings.meeting_list"
        />
        <!-- Time Zone -->
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
</style> 