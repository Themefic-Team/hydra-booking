<script setup>
import {ref} from 'vue'
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue'
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
                                <Icon name="Clock" size="16" />  
                            </div>
                            <div class="user-info-title">
                                {{ props.shareData.time }} {{ $tfhb_trans('minutes') }}
                            </div>
                        </div>
                    </li>
                    <li v-if="props.shareData.meeting_type">
                        <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-one'==props.shareData.meeting_type">
                            <div class="user-info-icon">
                                <Icon name="UserRound" size="16" /> 
                                <Icon name="ArrowRight" size="16" /> 
                                <Icon name="UserRound" size="16" /> 
                            </div>
                            <div class="user-info-title">
                                {{ $tfhb_trans('One to One') }}
                            </div>
                        </div>
                        <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-group'==props.shareData.meeting_type">
                            <div class="user-info-icon">
                                <Icon name="UserRound" size="16" /> 
                                <Icon name="ArrowRight" size="16" /> 
                                <Icon name="UsersRound" size="16" /> 
                            </div>
                            <div class="user-info-title">
                                {{ $tfhb_trans('One to Group') }}
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="tfhb-share-type tfhb-full-width">
                    <ul class="tfhb-flexbox tfhb-gap-8">
                        <li :class="'link'==props.shareData.share_type ? 'active' : ''" @click="ShareTabs('link')">{{ $tfhb_trans('Share link') }}</li>
                        <li :class="'short'==props.shareData.share_type ? 'active' : ''" @click="ShareTabs('short')">{{ $tfhb_trans('Short code') }}</li>
                        <li :class="'embed'==props.shareData.share_type ? 'active' : ''" @click="ShareTabs('embed')">Embed code</li>
                    </ul>
                </div>

                <div class="tfhb-shareing-data tfhb-full-width">
                    <div class="share-link" v-if="'link'==props.shareData.share_type">
                        <input type="text" :value="props.shareData.link" readonly>

                        <div class="tfhb-copy-btn ">
                            <button class="tfhb-btn boxed-btn flex-btn" @click="copyMeeting(props.shareData.link)">{{ $tfhb_trans('Copy link') }}</button>
                        </div>
                    </div>
                    <div class="share-link" v-if="'short'==props.shareData.share_type">
                        <input type="text" :value="props.shareData.shortcode" readonly>

                        <div class="tfhb-copy-btn">
                            <button class="tfhb-btn boxed-btn flex-btn" @click="copyMeeting(props.shareData.shortcode)">{{ $tfhb_trans('Copy Code') }}</button>
                        </div>
                    </div>
                    <div class="share-link" v-if="'embed'==props.shareData.share_type">
                        <input type="text" :value="props.shareData.embed" readonly>

                        <div class="tfhb-copy-btn">
                            <button class="tfhb-btn boxed-btn flex-btn" @click="copyMeeting(props.shareData.embed)">{{ $tfhb_trans('Copy Code') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </template> 
    </HbPopup>
</template>

<style scoped>
</style> 