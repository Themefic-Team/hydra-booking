<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import HbQuestion from '@/components/widgets/HbQuestion.vue';
import Icon from '@/components/icon/LucideIcon.vue';
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
// import Form Field  
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
</script>

<template>   
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Login Page Settings') }} </h2>
        <p>{{ $tfhb_trans('Customize the settings for the login page') }}</p>
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox">   
        <!--  Login Page  -->
        <HbDropdown  
            v-model="time_format"  
            required= "true" 
            :subtitle="$tfhb_trans('Choose a page to serve as the Login Page.')"  
            :label="$tfhb_trans('Login Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Login Page')"   
            :option = "[
                {'name': '12 Hours', 'value': '12'}, 
                {'name': '24 Hours', 'value': '24'}
            ]" 
        />
        <!--  Login Page  --> 
        <!-- Login Redirect Option -->
        <HbDropdown  
            v-model="time_format"  
            required= "true" 
            :subtitle="$tfhb_trans('Select the destination for users after they log in.')"  
            :label="$tfhb_trans('Login Redirect Option')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Login Redirect Option')"   
            :option = "[
                {'name': '12 Hours', 'value': '12'}, 
                {'name': '24 Hours', 'value': '24'}
            ]" 
        />
        <!-- Time format --> 
        <!-- Time format -->
        <HbDropdown  
            v-model="time_format"  
            required= "true" 
            :subtitle="$tfhb_trans('Select the destination page for users after they log in.')"  
            :label="$tfhb_trans('Choose your Page')"  
            width="50"
            :selected = "1"
            :placeholder="$tfhb_trans('Select Time Format')"   
            :option = "[
                {'name': '12 Hours', 'value': '12'}, 
                {'name': '24 Hours', 'value': '24'}
            ]" 
        />
        <!-- Time format --> 
    </div>

    <HbInfoBox  name="first-modal">
        
        <template #content>
            <span>{{$tfhb_trans('Use shortcode [tf_login_form] to show login form in post/page/widget.')}}</span> 
        </template>
    </HbInfoBox>
    
</template>

<style scoped>
</style> 