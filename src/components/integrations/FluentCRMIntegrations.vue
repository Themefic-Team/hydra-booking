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
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      >
    <span v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
           
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/fluent-crm.png'" alt="">
            </span> 


            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Fluent CRM') }}</h3>
                <p>{{ $tfhb_trans('Integrate FluentCRM to manage and track customer data easily.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <HbSwitch v-if="$tfhb_is_pro == true &&  $tfhb_license_status == true" @change="emit('update-integrations', 'fluent_crm', fluent_crm_data)" v-model="fluent_crm_data.status"    />
            
            <a v-else href="#" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Upgrade to Pro') }}  <Icon name="ChevronRight" size=18 /></a>
 
        </div>

 
    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 