<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 

const props = defineProps([
    'class', 
    'display', 
    'pabbly_data', 
    'ispopup'
])
const emit = defineEmits([ "update-integrations" ]); 

</script>

<template>
      <!-- webhook Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class,{
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >
    <span v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ __('Pro', 'hydra-booking') }}</span>
           
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/pabbly.svg'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ __('Pabbly', 'hydra-booking') }}</h3>
                <p>{{ __('Implement Pabbly for comprehensive sales and marketing automation.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <HbSwitch v-if="$tfhb_is_pro == true &&  $tfhb_license_status == true"  @change="emit('update-integrations', 'pabbly', pabbly_data)" v-model="pabbly_data.status"    />
            <a v-else href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Upgrade to Pro', 'hydra-booking') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>

 
    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 