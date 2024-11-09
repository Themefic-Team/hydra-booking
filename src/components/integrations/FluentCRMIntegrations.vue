<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue';

const props = defineProps([
    'class', 
    'display', 
    'fluent_crm_data', 
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
                <img :src="$tfhb_url+'/assets/images/fluent-crm.png'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ __('Fluent CRM', 'hydra-booking') }}</h3>
                <p>{{ __('Integrate FluentCRM to manage and track customer data easily.', 'hydra-booking') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- <HbSwitch v-if="$tfhb_is_pro == true" @change="emit('update-integrations', 'fluent_crm', fluent_crm_data)" v-model="fluent_crm_data.status"    /> -->
            <a v-if="$tfhb_is_pro == true"  class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Upcoming', 'hydra-booking') }}  <Icon name="ChevronRight" size=18 /></a>
            
            <a v-else href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Upgrade to Pro', 'hydra-booking') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>

 
    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 