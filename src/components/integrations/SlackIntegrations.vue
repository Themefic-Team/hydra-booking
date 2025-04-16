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
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
const { errors, isEmpty } = useValidators();

const props = defineProps([
    'class', 
    'display', 
    'slack_data', 
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
    <!-- {{ slack_data }} -->
      
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Slack.png'" alt="">
            </span>  
            <div class="cartbox-text"> 
                <h3>{{ $tfhb_trans('Slack') }}</h3>
                <p>{{ $tfhb_trans('Connect Slack API to configure virtual meetings.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">

            <button  v-if=" props.from == 'host' && slack_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Configured') }} </button>
            <router-link  v-else-if=" props.from == 'host' && slack_data.connection_status != '1'" to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>
           
            <HbButton 
                v-else @click="emit('popup-open-control')" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="slack_data.status == 1 && slack_data.endpoint != '' ? 'Connected' : 'Connect'" 
                hover_icon="ArrowRight" 
                :hover_animation="false"    
            />  
            <!-- Checkbox swicher --> 
            <HbSwitch v-if="slack_data.status && slack_data.endpoint !='' && display != 'list'" @change="emit('update-integrations', 'slack_data', slack_data)" v-model="slack_data.status"    /> 
            <HbSwitch v-if="slack_data.endpoint && display == 'list' && slack_data.connection_status == '1'" @change="emit('update-integrations', 'slack_data', slack_data)" v-model="slack_data.status"    /> 
            <!-- Swicher --> 
        </div>
        <!-- <Transition name="zoom-in"> -->
            <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ $tfhb_trans('Add Slack Option') }}</h2>
                    
                </template>

                <template #content>  
                    <p>
                        {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get Access Token from Slack Account') }} <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                    </p>
                    <HbText  
                        v-model="slack_data.endpoint"  
                        required= "true"  
                        name="endpoint"
                        :errors="errors.endpoint"
                        :label="$tfhb_trans('Endpoint Name')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Endpoint Name')"  
                    /> 
                    <!-- <HbText  
                        v-model="slack_data.token"  
                        required= "true"  
                        name="token"
                        :errors="errors.token"
                        :label="$tfhb_trans('Access Token')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Access Token')"  
                    />  -->

                    
                    <HbButton  
                         @click.stop="emit('update-integrations', 'slack_data', slack_data, ['endpoint'])"
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