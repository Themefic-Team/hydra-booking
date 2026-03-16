<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import { RouterView } from 'vue-router' 
// import Form Field 
import HbText from '@/components/form-fields/HbText.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbSwitch from '@/components/form-fields/HbSwitch.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';

import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

const props = defineProps([
    'class', 
    'display', 
    'aweber_data', 
    'pre_loader', 
    'ispopup',
    'from'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}

</script>

<template>
      <!-- Mailchimp Integrations  -->
      <div   class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class, {
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      > 
        <span v-if="$tfhb_is_pro == false ||  $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
         
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Awever.svg'" alt="">
            </span>  
            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('AWeber') }}</h3>
                <p>{{ $tfhb_trans('Integrate AWeber API to collect attendee emails and info.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- {{ aweber_data }} -->
            
            <button  v-if=" props.from == 'host' && aweber_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Connected') }} </button>
            <router-link v-else-if="props.from == 'host' && aweber_data.connection_status != 1"  to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <HbButton  
                v-else @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="props.aweber_data.status == 1 && aweber_data.key != '' &&  aweber_data.key  != null ? 'Connected' : 'Connect' " 
                :hover_animation="false"    
            /> 

            <HbSwitch
            v-if="aweber_data.client_id != '' &&  aweber_data.client_id  != null " 
                @change="emit('update-integrations', 'aweber', aweber_data)" v-model="aweber_data.status"   
            />
            <!-- Swicher --> 
        </div>
 

        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
            <template #header> 
                <!-- {{ google_calendar }} -->
                <h2>{{ $tfhb_trans('Connect Your AWeber API') }}</h2>
                
            </template>

            <template #content>  
                
               <p>
                    {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get api credentials from AWeber Account') }}

                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/aweber/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                </p> 

                <div v-if="aweber_data.connection_status == 0" class="tfhb-alert tfhb-alert-success">
                    <!-- add button connect with api -->
                    <a :href="aweber_data.authorize_url" target="_blank" class="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Configure with AWeber') }}</a>
            
                
                </div>
                <div v-if="aweber_data.connection_status == 0" class="tfhb-alert tfhb-alert-success">
                    <!-- add button connect with api -->
                    <a :href="aweber_data.authorize_url" target="_blank" class="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Configure with AWeber') }}</a>
            
                
                </div>
                <div v-else class="tfhb-flexbox tfhb-gap-12 tfhb-flex-direction-column">
                    {{ props.aweber_data.selected_subscriber_list }}
                    <!-- Time Zone -->
                    <HbDropdown 
                            
                        v-model="props.aweber_data.selected_subscriber_list"  
                        required= "true"  
                        :label="$tfhb_trans('Subscriber List')"  
                        selected = "1"
                        :filter="true"
                        :placeholder="$tfhb_trans('Select Subscriber List')"   
                        :option = "props.aweber_data.lists" 
                        :errors="errors.selected_subscriber_list"
                    /> 
                    <!-- Time Zone --> 

                    <HbButton  
                        @click.stop="emit('update-integrations', 'aweber', props.aweber_data, ['selected_subscriber_list', ])"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"  
                        :buttonText="'Save & Validate' "
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        :pre_loader="props.pre_loader"
                    />   

                </div>

            </template> 
        </HbPopup>


    </div>  
    <!-- Single Integrations  -->

</template>

<style scoped>
</style> 