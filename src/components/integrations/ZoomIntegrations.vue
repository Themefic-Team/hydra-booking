<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import { RouterView } from 'vue-router' 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import useValidators from '@/store/validator';
import HbButton from '@/components/form-fields/HbButton.vue';
const { errors, isEmpty } = useValidators();

const zoomPopup = ref(false);

const zoom_integration = reactive(  { 
    status: 0, 
    connection_status: 0,   
});

const props = defineProps([
    'class', 
    'display', 
    'zoom_meeting', 
    'pre_loader', 
    'Integration', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template>
      <!-- Zoom Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Zoom.png'" alt="">
            </span>  
            <div class="cartbox-text"> 
                <h3>{{ $tfhb_trans('Zoom') }}</h3>
                <p>{{ $tfhb_trans('Connect Zoom API to configure virtual meetings.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- <span  v-if=" props.from == 'host' && zoom_meeting.connection_status != '1'" class="tfhb-badge tfhb-badge-not-connected">{{ $tfhb_trans('Not Configured') }}  </span> -->
            
            <button  v-if=" props.from == 'host' && zoom_meeting.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Configured') }} </button>
            <router-link  v-else-if=" props.from == 'host' && zoom_meeting.connection_status != '1'" to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>
           

            <HbButton 
                v-else @click="emit('popup-open-control')" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="zoom_meeting.status == 1 ? 'Connected' : 'Connect'" 
                hover_icon="ArrowRight" 
                :hover_animation="false"    
            />  
            <!-- Checkbox swicher --> 
            <HbSwitch v-if="zoom_meeting.connection_status && zoom_meeting.account_id !='' && display != 'list'" @change="emit('update-integrations', 'zoom_meeting', zoom_meeting)" v-model="zoom_meeting.status"    /> 
            <HbSwitch v-if="zoom_meeting.account_id && display == 'list'" @change="emit('update-integrations', 'zoom_meeting', zoom_meeting)" v-model="zoom_meeting.status"    /> 
            <!-- Swicher --> 
        </div>
        <!-- <Transition name="zoom-in"> -->
            <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ $tfhb_trans('Add New Zoom User Account') }}</h2>
                    
                </template>

                <template #content>  
                    <p>
                        {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from Zoom Account') }} <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/zoom-integration/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                    </p>
                    <HbText  
                        v-model="zoom_meeting.account_id"  
                        required= "true"  
                        name="account_id"
                        :errors="errors.account_id"
                        :label="$tfhb_trans('Zoom Account ID')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Account ID')"  
                    /> 
                    <HbText  
                        v-model="zoom_meeting.app_client_id"  
                        required= "true"  
                        name="app_client_id"
                        :errors="errors.app_client_id"
                        :label="$tfhb_trans('Zoom App Client ID')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your App Client ID')"  
                    /> 
                    <HbText  
                        v-model="zoom_meeting.app_secret_key"  
                        required= "true"  
                        name="app_secret_key"
                        :errors="errors.app_secret_key"
                        :label="$tfhb_trans('Zoom App Secret Key')"  
                        selected = "1"
                        type = "password"
                        :placeholder="$tfhb_trans('Enter Your App Secret Key')"  
                    /> 

                    <HbButton  
                         @click.stop="emit('update-integrations', 'zoom_meeting', zoom_meeting, ['account_id', 'app_client_id', 'app_secret_key'])"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"  
                        :buttonText="'Save & Validate' "
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        :pre_loader="props.pre_loader"
                    />   
                </template> 
            </HbPopup>
        <!-- </Transition > -->

    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 