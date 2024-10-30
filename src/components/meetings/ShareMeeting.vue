<script setup>
import { __ } from '@wordpress/i18n';
import {ref} from 'vue'
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import HbText from '@/components/form-fields/HbText.vue'
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbInput from '@/components/form-fields/HbTextarea.vue'
const props = defineProps([
    'sharePopup', 
    'shareData'
])
const copyMeeting = (link) => {
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = link;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action
    toast.success( link + ' is Copied' );
}
const ShareTabs = (tab) => {
    props.shareData.share_type = tab;
}
 
</script>

<template> 

    <HbPopup :isOpen="props.sharePopup.value" @modal-close="props.sharePopup.value = false" max_width="600px" name="first-modal">
        
        <template #header> 
            <h3>{{ props.shareData.title }}</h3>
        </template>

        <template #content>  
            <div class="tfhb-meeting-durationinfo tfhb-flexbox tfhb-gap-32 tfhb-full-width">
                <ul class="tfhb-locationtype tfhb-flexbox tfhb-justify-normal tfhb-full-width tfhb-gap-32">
                    <li v-if="props.shareData.time">
                        <div class="tfhb-flexbox tfhb-gap-8">
                            <div class="user-info-icon">
                                <Icon name="Clock" size=16 />  
                            </div>
                            <div class="user-info-title">
                                {{ props.shareData.time }} {{ __('minutes', 'hydra-booking') }}
                            </div>
                        </div>
                    </li>
                    <li v-if="props.shareData.meeting_type">
                        <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-one'==props.shareData.meeting_type">
                            <div class="user-info-icon">
                                <Icon name="UserRound" size=16 /> 
                                <Icon name="ArrowRight" size=16 /> 
                                <Icon name="UserRound" size=16 /> 
                            </div>
                            <div class="user-info-title">
                                {{ __('One to One', 'hydra-booking') }}
                            </div>
                        </div>
                        <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-group'==props.shareData.meeting_type">
                            <div class="user-info-icon">
                                <Icon name="UserRound" size=16 /> 
                                <Icon name="ArrowRight" size=16 /> 
                                <Icon name="UsersRound" size=16 /> 
                            </div>
                            <div class="user-info-title">
                                {{ __('One to Group', 'hydra-booking') }}
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="tfhb-share-type tfhb-full-width">
                    <ul class="tfhb-flexbox tfhb-gap-8">
                        <li :class="'link'==props.shareData.share_type ? 'active' : ''" @click="ShareTabs('link')">{{ __('Share link', 'hydra-booking') }}</li>
                        <li :class="'short'==props.shareData.share_type ? 'active' : ''" @click="ShareTabs('short')">{{ __('Short code', 'hydra-booking') }}</li>
                        <li :class="'embed'==props.shareData.share_type ? 'active' : ''" @click="ShareTabs('embed')">Embed code</li>
                    </ul>
                </div>

                <div class="tfhb-shareing-data tfhb-full-width">
                    <div class="share-link" v-if="'link'==props.shareData.share_type"> 
                        <HbText 
                            v-model="props.shareData.link"  
                            :readonly="true"

                        />
                        <div class="tfhb-copy-btn "> 
                            <HbButton 
                                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                                @click="copyMeeting(props.shareData.embed)" 
                                :buttonText="__('Copy Code', 'hydra-booking')" 
                            />  
                        </div>
                    </div>
                    <div class="share-link" v-if="'short'==props.shareData.share_type"> 
                        <HbText 
                            v-model="props.shareData.shortcode"  
                            :readonly="true"

                        />
                        <div class="tfhb-copy-btn">
                            <HbButton 
                                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                                @click="copyMeeting(props.shareData.embed)" 
                                :buttonText="__('Copy Code', 'hydra-booking')" 
                            /> 
                        </div>
                    </div>
                    <div class="share-link tfhb-flexbox tfhb-gap-24 tfhb-justify-end" v-if="'embed'==props.shareData.share_type">
                       
 
                        <HbTextarea 
                            v-model="props.shareData.embed"  
                            :readonly="true"

                        />
 
                        <HbButton 
                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                            @click="copyMeeting(props.shareData.embed)" 
                            :buttonText="__('Copy Code', 'hydra-booking')" 
                        /> 
                    </div>
                    
                </div>
            </div>
        </template> 
    </HbPopup>
</template>

<style scoped>
</style> 