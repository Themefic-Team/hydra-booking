<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { onBeforeMount, reactive, ref } from 'vue';
import { RouterView } from 'vue-router'  
import Icon from '@/components/icon/LucideIcon.vue'

import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';
import HbMultiSelect from '@/components/form-fields/HbMultiSelect.vue';
import useValidators from '@/store/validator';
import { toast } from "vue3-toastify"; 
const { errors, isEmpty } = useValidators();
// Store 

import { ShortcodeData } from '@/store/settings/shortcode';

const shortCodeField = reactive({
    title: '',
    subtitle: '',
    category: [],
    hosts: [],
    short_by: 'id',
    order_by: 'DESC',
    limit: '10',
});
const shortcode = ref(`[tfhb_categories title="Title" subtitle="Sub title" sort_by="id" order_by="DESC" limit="9"]`)

// copy to clipboard
function copyToClipboard(text) {
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    toast.success('Shortcode copied to clipboard', {
        position: 'bottom-right', // Set the desired position
        "autoClose": 1500,
    });  

}
// Generate shortcode
const generateShortcode = () => {

    ShortcodeData.preview_skeleton = true;
    let shortcode_field = `[tfhb_categories`;
    Object.keys(shortCodeField).forEach(key => {
        shortcode_field += ` ${key}="${shortCodeField[key]}"`;
    });
    shortcode_field += ']';
    shortcode.value = shortcode_field;
    ShortcodeData.generateShortPreview(shortcode.value);
}

// reset the shortcode field
const resetShortcode =  () => { 
    ShortcodeData.preview_skeleton_reset = true;
    shortCodeField.title = '';
    shortCodeField.subtitle = ''; 
    shortCodeField.short_by = 'id';
    shortCodeField.order_by = 'DESC';
    shortCodeField.limit = '10';
    shortcode.value = `[tfhb_categories title="Title" subtitle="Sub title" category="all" hosts="all" sort_by="id" order_by="DESC" limit="9"]`;
    ShortcodeData.generateShortPreview(shortcode.value);

};


 
onBeforeMount(() => {   
    ShortcodeData.generateShortPreview(shortcode.value);
});
 
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': ShortcodeData.skeleton }" class="tfhb-settings-dashboard tfhb-full-width tfhb-flexbox tfhb-gap-16"> 
        <div  class="tfhb-dashboard-heading tfhb-mb-16 tfhb-full-width">
            <div class="tfhb-admin-title "> 
                <h3 >{{ $tfhb_trans('Categories Shortcode') }}</h3>  
                <p>{{ $tfhb_trans('Generate a shortcode to embed your meetings categories list on your website.') }}</p>
            </div> 
        </div> 
        <div class="share-link tfhb-flexbox tfhb-gap-8 tfhb-full-width " > 
            <HbText 
                v-model="shortcode"  
                :readonly="true"

            />
            <div class="tfhb-copy-btn "> 
                <HbButton 
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 "   
                    icon="Copy"
                    @click="copyToClipboard(shortcode)" 
                />  
            </div>
        </div>
        <div class="tfhb-settings-shortcode-wrap tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-16 tfhb-align-normal tfhb-justify-between"> 
          
            <div class="settings tfhb-flexbox tfhb-gap-16 tfhb-align-normal tfhb-justify-between">  
                 
                 <!-- Time Zone -->
                 <HbText  
                    v-model="shortCodeField.title"   
                    :label="$tfhb_trans('Shortcode title')"  
                    selected = "1" 
                    :placeholder="$tfhb_trans('Type a title for your shortcode')"  
                    @change="generateShortcode()"
                /> 
                <HbText  
                    v-model="shortCodeField.subtitle"   
                    :label="$tfhb_trans('Shortcode sub-title')"    
                    selected = "1"
                    :placeholder="$tfhb_trans(' Type a sub-title for your shortcode')"  
                    @change="generateShortcode()"
                />   
                <HbDropdown 
                    @add-change="generateShortcode()"
                    v-model="shortCodeField.short_by"   
                    :label="$tfhb_trans('Sort By')"   
                    :selected = "1"
                    width="50"
                    :placeholder="$tfhb_trans('Sort by meetings')"   
                    :option = "[
                        {'name': 'ID', 'value': 'id'}, 
                        {'name': 'Title', 'value': 'tittle'}, 
                    ]" 
                />
                <!-- Time format -->
                <HbDropdown  
                    @add-change="generateShortcode()"
                    v-model="shortCodeField.order_by"     
                    :label="$tfhb_trans('Order By')"   
                    :selected = "1" 
                    width="50"
                    :placeholder="$tfhb_trans(' Order by meetings')"  
                    :option = "[
                        {'name': 'ASC', 'value': 'ASC'}, 
                        {'name': 'DESC', 'value': 'DESC'}, 
                    ]" 
                />
                <!-- Time format --> 
                <!-- Time Zone --> 
                <HbText  
                    v-model="shortCodeField.limit"   
                    type="number"  
                    @change="generateShortcode()"
                    :label="$tfhb_trans(' Display Limit')"  
                    selected = "1"
                    :placeholder="$tfhb_trans(' Type a number to limit the number of meetings displayed')"  
                />  
                <div class="tfhb-hydra-shortcode-btn tfhb-flexbox  tfhb-gap-8">
                    <HbButton 
                        classValue="tfhb-btn secondary-btn flex-btn"  
                        :buttonText="$tfhb_trans('Reset')"
                        icon="RefreshCcw" 
                        icon_position="left"  
                        @click="resetShortcode()" 
                        :pre_loader="ShortcodeData.preview_skeleton_reset"
                    /> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn"  
                        :buttonText="$tfhb_trans('Generate')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        :pre_loader="ShortcodeData.preview_skeleton"
                        @click=" generateShortcode()"
                    />

                </div>  
            </div>  
            <div class="preview">   
                <div :class="{ 'tfhb-skeleton': ShortcodeData.preview_skeleton || ShortcodeData.preview_skeleton_reset }" class="tfhb-desktop-wrapper">  
                    <div class="desktop-view tfhb-full-width">
                        <div v-html="ShortcodeData.output"></div>     
                    </div>
                </div> 
            </div>
            
             
        </div> 
 
    </div>
 
</template>
<!-- load sass -->
<style lang="scss">
.tfhb-settings-dashboard{
    align-items: start !important;
    .tfhb-admin-title{
        margin-bottom: 0 !important;
    }
    .share-link{
        position: relative;
        input{
            padding-right: 100px !important;
        }
        .tfhb-copy-btn{
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            button{
                height: 100%;
                border-radius: 4px;
            }
            // cursor: pointer;
        }
             > .tfhb-single-form-field{
                // width: calc(100% - 80px) !important;
            }
            .tfhb-copy-btn{
                // max-width: 75px;
            }
        }
    .tfhb-settings-shortcode-wrap{
       >div{

        width: calc(50% - 16px); 
       }
       .preview{
        padding: 16px;
        background-color: #E1F2E4;
        // border-radius: ;
       }
       
       
        
    }
    .tfhb-hydra-shortcode-btn{
        width: 100% !important;
    }
    .tfhb-desktop-wrapper{
        max-height: 400px;
        overflow-y: auto;
        position: relative;
        &:after{
            position: absolute;
            inset: 0;
            content: "";
            cursor: not-allowed;

        }
    }
    .desktop-view {
        zoom: 0.4; /* Scale down */
    }
    
}

</style>