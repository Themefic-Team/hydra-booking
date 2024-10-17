<script setup> 
import { __ } from '@wordpress/i18n';
import {ref, reactive, onBeforeMount} from 'vue'
import { useRouter, RouterView,} from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbImageSelect from '@/components/form-fields/HbImageSelect.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import axios from 'axios' 
import { toast } from "vue3-toastify";
import LvColorpicker from 'lightvue/color-picker';
const router = useRouter();


const appearanceSettings = reactive({
  themes: 'System default',
  primary_color: '$primary-default',
  secondary_color: '$text-paragraph',
  paragraph_color: '#765664',
  titleTypo: '',
  desTypo: '',
});

const skeleton = ref(true);
// Fetch Appearance
const fetchAppearanceSettings = async () => {

try { 
    const response = await axios.get(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/settings/appearance-settings', {
        headers: {
            'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            'capability': 'tfhb_manage_options'
        } 
    });
    if (response.data.status) { 
  
        // Set Appearance Settings
        appearanceSettings.themes = response.data.appearance_settings.themes ? response.data.appearance_settings.themes : 'System default';
        appearanceSettings.primary_color = response.data.appearance_settings.primary_color ? response.data.appearance_settings.primary_color : '$primary-default';
        appearanceSettings.secondary_color = response.data.appearance_settings.secondary_color ? response.data.appearance_settings.secondary_color : '$text-paragraph';
        appearanceSettings.paragraph_color = response.data.appearance_settings.paragraph_color ? response.data.appearance_settings.paragraph_color : '#765664';
        appearanceSettings.titleTypo = response.data.appearance_settings.titleTypo ? response.data.appearance_settings.titleTypo : '';
        appearanceSettings.desTypo = response.data.appearance_settings.desTypo ? response.data.appearance_settings.desTypo : '';
        skeleton.value = false;
    }
} catch (error) {
    console.log(error);
} 
}

const UpdateAppearanceSettings = async () => { 
    try { 
        const response = await axios.post(tfhb_core_apps.admin_url + '/wp-json/hydra-booking/v1/settings/appearance-settings/update', appearanceSettings, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );
      
        if (response.data.status) {    
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
            
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}

onBeforeMount(() => { 
    fetchAppearanceSettings();
});

</script>
<template>
    <div :class="{ 'tfhb-skeleton': skeleton }"  class="thb-event-dashboard">

        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ __('Appearance', 'hydra-booking') }}</h1> 
                <p>{{ __('Set up your appearance settings for bookings', 'hydra-booking') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="#" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ __('View Documentation', 'hydra-booking') }}<Icon name="ArrowUpRight" size=15 /></a>
            </div> 
        </div>
        
        <div class="thb-content-wrap">
            
            <!-- <div class="tfhb-admin-title" >
                <h2>{{ __('Theme', 'hydra-booking') }}</h2> 
                <p>{{ __('This only applies to your attendee booking pages', 'hydra-booking') }}</p>
            </div>

            <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-tb-24">
                <div class="tfhb-imageselect-box">

                    <HbImageSelect 
                        v-model="appearanceSettings.themes"
                        width="50"
                        name="themes"
                        selected = "1"
                        :option = "[
                            {'name': 'System default', 'value': '#765664'}, 
                            {'name': 'Light', 'value': '$text-input'},
                            {'name': 'Dark', 'value': '$text-paragraph'},
                        ]"
                    />
                </div>
            </div> -->

            <div class="tfhb-admin-title" >
                <h2>{{ __('Custom brand colors', 'hydra-booking') }}</h2> 
                <p>{{ __('Customize your own brand color into your booking page', 'hydra-booking') }}</p>
            </div>

            <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-tb-24">
                <div class="tfhb-colorbox tfhb-full-width">
                    <div class="tfhb-single-colorbox tfhb-flexbox tfhb-mb-16">
                        <label>
                            {{ __('Primary Color', 'hydra-booking') }}
                        </label>
                        <div class="color-select">
                            <LvColorpicker :value="appearanceSettings.primary_color" v-model="appearanceSettings.primary_color" :withoutInput="true"/>
                            <span>{{ __('Select Color', 'hydra-booking') }}</span>
                        </div>
                    </div>
                    <div class="tfhb-single-colorbox tfhb-flexbox tfhb-mb-16">
                        <label>
                            {{ __('Secondary Color', 'hydra-booking') }}
                        </label>
                        <div class="color-select">
                            <LvColorpicker :value="appearanceSettings.secondary_color" v-model="appearanceSettings.secondary_color" :withoutInput="true"/>
                            <span>{{ __('Select Color', 'hydra-booking') }}</span>
                        </div>
                    </div>
                    <div class="tfhb-single-colorbox tfhb-flexbox">
                        <label>
                            {{ __('Paragraph Color', 'hydra-booking') }}
                        </label>
                        <div class="color-select">
                            <LvColorpicker :value="appearanceSettings.paragraph_color" v-model="appearanceSettings.paragraph_color" :withoutInput="true"/>
                            <span>{{ __('Select Color', 'hydra-booking') }}</span>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="tfhb-admin-title" >
                <h2>{{ __('Typography', 'hydra-booking') }}</h2> 
                <p>{{ __('Set your own typography for your brand', 'hydra-booking') }}</p>
            </div>

            <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-tb-24">  
                <HbDropdown 
                    v-model="appearanceSettings.titleTypo"
                    required= "true"  
                    :label="__('For title', 'hydra-booking')"   
                    width="50"
                    selected = "1"
                    placeholder="For title"  
                    :option = "[
                        {'name': 'Inter', 'value': 'Inter'}, 
                        {'name': 'Roboto', 'value': 'Roboto'}
                    ]"
                    
                />

                <HbDropdown 
                    v-model="appearanceSettings.desTypo"
                    required= "true"  
                    :label="__('For paragraph', 'hydra-booking')"   
                    width="50"
                    selected = "1"
                    placeholder="For paragraph"  
                    :option = "[
                        {'name': 'Inter', 'value': 'Inter'}, 
                        {'name': 'Roboto', 'value': 'Roboto'}
                    ]"
                    
                />
            </div>
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="UpdateAppearanceSettings" 
                :buttonText="__('Save', 'hydra-booking')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
            />   

        </div>
    </div>
 
</template>