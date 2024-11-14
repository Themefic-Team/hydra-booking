<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue';  
import HbButton from '@/components/form-fields/HbButton.vue';
// import Form Field 
import HbSwitch from '@/components/form-fields/HbSwitch.vue';

const zoomPopup = ref(false);

const zoom_integration = reactive(  { 
    status: 0, 
    connection_status: 0,   
});

const props = defineProps([
    'woo_payment', 
    'display',
])
const emit = defineEmits([ "update-integrations", ]); 
const plugin_url = tfhb_core_apps.admin_url+'/wp-admin/plugin-install.php?s=WooCommerce&tab=search';

const installWooPlugin = () => {
    // redirect to plugin_url in new devs
    window.open(plugin_url, '_blank');
}
</script>

<template>
    <div class="tfhb-integrations-single-block tfhb-admin-card-box ">
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Woo.png'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ __('Woo Payment', 'hydra-booking') }}</h3>
                <p>{{ __('Connect WooCommerce for manage booking payment.', 'hydra-booking') }}</p>
            </div>
        </div>
       

<!-- Aadmin -->

        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
           
             
            <!-- <button v-if="woo_payment.connection_status == 1"  class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ __('Connected', 'hydra-booking') }} <Icon name="ChevronRight" size=18 /></button> -->
            <HbButton 
                 v-if="woo_payment.connection_status == 1" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="__('Connected', 'hydra-booking')" 
                :hover_animation="false"
            /> 
      
              
            <HbButton 
                v-else
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8" 
                @click="installWooPlugin"
                :buttonText="__('Connect', 'hydra-booking')"  
                :hover_animation="false"
            /> 
                
            <HbSwitch v-if="woo_payment.connection_status" @change="emit('update-integrations', 'woo_payment', woo_payment)"  v-model="woo_payment.status"    />
            
        </div>
 
    </div>  

</template>

<style scoped>
</style> 