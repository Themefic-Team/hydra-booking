<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import HbQuestion from '@/components/widgets/HbQuestion.vue';
import Icon from '@/components/icon/LucideIcon.vue';
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue';

 
</script>

<template>   
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('General Settings') }} </h2> 
        <p>{{ $tfhb_trans('Manage the general settings and preferences for the frontend dashboard') }}</p>
    </div>
    <div class="tfhb-admin-card-box">   

        <div class="tfhb-single-form-field-wrap tfhb-flexbox">
            <div class="tfhb-field-image" >  
                <img class='avatar_display'  :src="$tfhb_url+'/assets/images/avator.png'" >
                <button class="tfhb-image-btn" @click="">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="avatar"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 >{{ $tfhb_trans('Dashboard Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p   class="tfhb-m-0">{{ $tfhb_trans('This logo serves as the main header image of the dashboard') }}</p>
            </div>
        </div> 
 
    </div>
    <div class="tfhb-admin-card-box">
        <div class="tfhb-single-form-field-wrap tfhb-flexbox">
            <div class="tfhb-field-image" >  
                <img class='avatar_display'  :src="$tfhb_url+'/assets/images/avator.png'" >
                <button class="tfhb-image-btn" @click="">{{ $tfhb_trans('Change') }}</button> 
                <input  type="text"  :v-model="avatar"   />  
            </div>
            <div class="tfhb-image-box-content">  
            <h4 >{{ $tfhb_trans('Mobile Logo') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p   class="tfhb-m-0">{{ $tfhb_trans('This logo is used for the mobile version of the dashboard') }}</p>
            </div>
        </div> 
    </div>
</template>

<style scoped>
</style> 