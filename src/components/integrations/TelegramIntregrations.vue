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
    'telegram_data', 
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
    <!-- {{ telegram_data }} -->
      <!-- Telegram Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/Zoom.png'" alt="">
            </span>  
            <div class="cartbox-text"> 
                <h3>{{ $tfhb_trans('Telegram') }}</h3>
                <p>{{ $tfhb_trans('Connect Telegram API to configure virtual meetings.') }}</p>
            </div>
        </div>
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">

            <button  v-if=" props.from == 'host' && telegram_data.connection_status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Configured') }} </button>
            <router-link  v-else-if=" props.from == 'host' && telegram_data.connection_status != '1'" to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>
           
            <HbButton 
                v-else @click="emit('popup-open-control')" 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8"  
                :buttonText="telegram_data.status == 1 ? 'Connected' : 'Connect'" 
                hover_icon="ArrowRight" 
                :hover_animation="false"    
            />  
            <!-- Checkbox swicher --> 
            <HbSwitch v-if="telegram_data.status && telegram_data.bot_token !='' && display != 'list'" @change="emit('update-integrations', 'telegram_data', telegram_data)" v-model="telegram_data.status"    /> 
            <HbSwitch v-if="telegram_data.bot_token && display == 'list' && telegram_data.connection_status == '1'" @change="emit('update-integrations', 'telegram_data', telegram_data)" v-model="telegram_data.status"    /> 
            <!-- Swicher --> 
        </div>
        <!-- <Transition name="zoom-in"> -->
            <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="600px" name="first-modal">
                <template #header> 
                    <h2>{{ $tfhb_trans('Add New Telegram Option') }}</h2>
                    
                </template>

                <template #content>  
                    <p>
                        {{ $tfhb_trans('Please read the documentation here for step by step guide to know how you can get BOT Token and Chat ID from Telegram Account') }} <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Read Documentation') }}</a>
                    </p>
                    <HbText  
                        v-model="telegram_data.bot_token"  
                        required= "true"  
                        name="bot_token"
                        :errors="errors.bot_token"
                        :label="$tfhb_trans('Telegram BOT Token')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Telegram BOT Token')"  
                    /> 
                    <HbText  
                        v-model="telegram_data.chat_id"  
                        required= "true"  
                        name="chat_id"
                        :errors="errors.chat_id"
                        :label="$tfhb_trans('Telegram Chat ID')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Enter Your Telegram Chat ID')"  
                    /> 

                    <HbButton  
                         @click.stop="emit('update-integrations', 'telegram_data', telegram_data, ['bot_token', 'chat_id'])"
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