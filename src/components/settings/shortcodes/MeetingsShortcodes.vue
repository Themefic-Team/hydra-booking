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
    short_by: '',
    order_by: '',
    limit: '',
});
const shortcode = ref(`[tfhb_meetings title="Title" subtitle="Sub title" category="all" hosts="all" sort_by="id" order_by="DESC" display_limit="9"]`)

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
    let shortcode_field = `[tfhb_meetings`;
    Object.keys(shortCodeField).forEach(key => {
        if ((key === 'hosts' && shortCodeField[key].length == 0) || (key === 'category' && shortCodeField[key].length == 0) ) { 
            shortcode_field += ` ${key}="all"`;
        }else{ 
            shortcode_field += ` ${key}="${shortCodeField[key]}"`;
        }
    });
    shortcode_field += ']';
    shortcode.value = shortcode_field;
}


 
onBeforeMount(() => {   
    //  hostsSettings.fetchHostsSettings();
});
 
</script>
<template> 
    <div :class="{ 'tfhb-skeleton': false }" class="tfhb-settings-dashboard  tfhb-flexbox tfhb-gap-16 tfhb-full-width"> 
        {{ ShortcodeData }}
        {{shortCodeField}}
        <div class="tfhb-settings-shortcode-wrap settings ">
            {{shortcode}}
            <div  class="tfhb-dashboard-heading tfhb-mb-16">
                <div class="tfhb-admin-title "> 
                    <h3 >{{ $tfhb_trans('Meetings Shortcode') }}</h3>  
                </div> 
            </div> 
            <div class="tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between">  

                <!-- Time Zone -->
                <HbText  
                    v-model="shortCodeField.title"   
                    :label="$tfhb_trans('Shortcode title')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Type a title for your shortcode')"  
                /> 
                <HbText  
                    v-model="shortCodeField.subtitle"   
                    :label="$tfhb_trans('Shortcode sub-title')"  
                    selected = "1"
                    :placeholder="$tfhb_trans(' Type a sub-title for your shortcode')"  
                />  
                <HbMultiSelect  
                    v-model="shortCodeField.category" 
                    :selected = "1"
                    @add-change="Booking.fetchBookings()"
                    :placeholder="$tfhb_trans(' Meeting category : All')"  
                    :option="ShortcodeData.categoryList"
                /> 
               
                <HbMultiSelect  
                    v-model="shortCodeField.hosts" 
                    :selected = "1"
                    @add-change="Booking.fetchBookings()"
                    :placeholder="$tfhb_trans('Select host : All')"  
                    :option="ShortcodeData.hostsList"
                />  
                <HbDropdown 
                    
                    v-model="shortCodeField.short_by"   
                    :label="$tfhb_trans('Sort By')"   
                    :selected = "1"
                    width="50"
                    :placeholder="$tfhb_trans('Sort by meetings')"   
                    :option = "[
                        {'name': 'ID', 'value': 'id'}, 
                        {'name': 'Title', 'value': 'tittle'},
                        {'name': 'Date', 'value': 'date'}
                    ]" 
                />
                <!-- Time format -->
                <HbDropdown  
                    v-model="shortCodeField.order_by"     
                    :label="$tfhb_trans('Order By')"   
                    :selected = "1" 
                    width="50"
                    :placeholder="$tfhb_trans(' Order by meetings')"  
                    :option = "[
                        {'name': 'ASC', 'value': 'ASC'}, 
                        {'name': 'DESC', 'value': 'DESC'},
                        {'name': 'RANDOM', 'value': 'RANDOM'}
                    ]" 
                />
                <!-- Time format --> 
                <!-- Time Zone --> 
                <HbText  
                    v-model="shortCodeField.limit"   
                    type="number"  
                    :label="$tfhb_trans(' Display Limit')"  
                    selected = "1"
                    :placeholder="$tfhb_trans(' Type a number to limit the number of meetings displayed')"  
                />  
                <div class="tfhb-hydra-shortcode-btn tfhb-flexbox tfhb-justify-center tfhb-gap-8">
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation"  
                        :buttonText="$tfhb_trans('Reset')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                    /> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation"  
                        :buttonText="$tfhb_trans('Generate')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        @click=" generateShortcode()"
                    />

                </div>  
            </div>   
            
        </div> 
        <div class="tfhb-settings-shortcode-wrap preview">
            <div  class="tfhb-dashboard-heading tfhb-mb-16">
                <div class="tfhb-admin-title "> 
                    <h3 >{{ $tfhb_trans('Preview') }}</h3>  
                </div> 
            </div>
            <div class="share-link tfhb-flexbox tfhb-gap-8" > 
                <HbText 
                    v-model="shortcode"  
                    :readonly="true"

                />
                <div class="tfhb-copy-btn "> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 "   
                        icon="Copy"
                        @click="copyToClipboard(shortcode)"
                        :hover_animation="true"
                    />  
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
    .tfhb-settings-shortcode-wrap{
        width: calc(50% - 17px); 
        .share-link{
             > .tfhb-single-form-field{
                width: calc(100% - 75px) !important;
            }
        }
        
    }
    .tfhb-hydra-shortcode-btn{
        width: 100% !important;
    }
}

</style>