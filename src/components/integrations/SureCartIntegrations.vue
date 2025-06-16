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
    'sure_cart', 
    'display',
])
const emit = defineEmits([ "update-integrations", ]); 
const plugin_url = tfhb_core_apps.admin_url+'/wp-admin/plugin-install.php?s=surecart&tab=search';

const installWooPlugin = () => {
    // redirect to plugin_url in new devs
    window.open(plugin_url, '_blank');
}
</script>

<template>
    <div class="tfhb-integrations-single-block tfhb-admin-card-box ">
        <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/surecart-logo.svg'" style="height:20px" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('SureCart Payment') }}</h3>
                <p>{{ $tfhb_trans('Connect SureCart for manage booking payment.') }}</p>
            </div>
        </div>
       

<!-- Aadmin -->

        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
           
             
            <!-- <button v-if="sure_cart.connection_status == 1"  class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Connected') }} <Icon name="ChevronRight" size=18 /></button> -->
            <HbButton 
                 v-if="sure_cart.connection_status == 1" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="$tfhb_trans('Connected')" 
                :hover_animation="false"
            /> 
      
              
            <HbButton 
                v-else
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8" 
                @click="installWooPlugin"
                :buttonText="$tfhb_trans('Connect')"  
                :hover_animation="false"
            /> 
                
            <HbSwitch v-if="sure_cart.connection_status" @change="emit('update-integrations', 'sure_cart', sure_cart)"  v-model="sure_cart.status"    />
            
        </div>
 
    </div>  

</template>

<style scoped>
</style> 