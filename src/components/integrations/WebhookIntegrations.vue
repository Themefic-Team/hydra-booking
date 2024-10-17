<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 

const props = defineProps([
    'class', 
    'display', 
    'webhook_data', 
    'ispopup'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template>
      <!-- webhook Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class,{
            'tfhb-pro': !$tfhb_is_pro,
        }"
      >
        <span v-if="$tfhb_is_pro == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
          
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/webhooks.png'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ __('Webhook', 'hydra-booking') }}</h3>
                <p>{{ __('Set up webhook integration to send data via custom API.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <HbSwitch v-if="$tfhb_is_pro == true"    @change="emit('update-integrations', 'webhook', webhook_data)" v-model="webhook_data.status"    />
            <a v-else href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Upgrade to Pro', 'hydra-booking') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>

 
    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 