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

const props = defineProps([
    'class', 
    'display', 
    'whatsapp_data', 
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
    <!-- {{ whatsapp_data }} -->
      <!-- Telegram Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Zoom.png'" alt="">
            </span>  
            <div class="cartbox-text"> 
                <h3>{{ $tfhb_trans('Whatsapp') }}</h3>
                <p>{{ $tfhb_trans('Connect Whatsapp API to configure virtual meetings.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">

            <button  v-if=" props.from == 'host' && whatsapp_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Configured') }} </button>
            <router-link  v-else-if=" props.from == 'host' && whatsapp_data.connection_status != '1'" to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>
           
            <HbButton 
                v-else @click="emit('popup-open-control')" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="whatsapp_data.status == 1 ? 'Connected' : 'Connect'" 
                hover_icon="ArrowRight" 
                :hover_animation="false"    
            />  
            <!-- Checkbox swicher --> 
            <HbSwitch v-if="whatsapp_data.status && whatsapp_data.bot_token !='' && display != 'list'" @change="emit('update-integrations', 'whatsapp_data', whatsapp_data)" v-model="whatsapp_data.status"    /> 
            <HbSwitch v-if="whatsapp_data.bot_token && display == 'list' && whatsapp_data.connection_status == '1'" @change="emit('update-integrations', 'whatsapp_data', whatsapp_data)" v-model="whatsapp_data.status"    /> 
            <!-- Swicher --> 
        </div>
        <!-- <Transition name="zoom-in"> -->
            <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ $tfhb_trans('Add Whatsapp Option') }}</h2>
                    
                </template>

                <template #content>  
                    <p>
                        {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get Access Token from Whatsapp Account') }} <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                    </p>
                    <HbText  
                        v-model="whatsapp_data.number"  
                        required= "true"  
                        name="number"
                        :errors="errors.number"
                        :label="$tfhb_trans('Whatsapp Number')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Whatsapp Number')"  
                    /> 
                    <HbText  
                        v-model="whatsapp_data.access_token"  
                        required= "true"  
                        name="access_token"
                        :errors="errors.access_token"
                        :label="$tfhb_trans('Access Token')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Access Token')"  
                    /> 

                    <HbButton  
                         @click.stop="emit('update-integrations', 'whatsapp_data', whatsapp_data, ['number', 'access_token'])"
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