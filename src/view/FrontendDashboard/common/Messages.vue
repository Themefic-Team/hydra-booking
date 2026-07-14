<script setup>
import { __ } from '@wordpress/i18n';
import { ref, onBeforeMount, computed,  watch } from 'vue'; 
import { onBeforeRouteLeave, useRoute  } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
import { FdDashboard } from '@/store/frontend-dashboard.js';
import axios from 'axios';

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
// http://hydra-custom-project.local/chat/#/new-conversation?&to=1&subject=Have+a+question+to+you&message=Lorem+Ipsum+is+simply+dummy+text+of+the+printing+and+typesetting+industry.&
// [better_messages_pm_button text="Private Message" subject="Have a question to you" message="Lorem Ipsum is simply dummy text of the printing and typesetting industry." target="_self" class="extra-class" fast_start="0" url_only="0"]

// http://hydra-custom-project.local/chat/#/new-conversation?&to=1&subject=Have+a+question+to+you&
onBeforeMount(async () => {

    await AddonsAuth.FetchSettings();
    if(AddonsAuth.chat_user_id != 0){
        AddonsAuth.event_settings.live_chat_url =  AddonsAuth.event_settings.live_chat_url + '#/new-conversation?&to='+AddonsAuth.chat_user_id+'&subject=Have+a+question+to+you&';
    }
});

// The chat page (matching-system) is served from the same origin, so we can reach
// into its document and strip the theme's default top margin/padding that shows
// up as dead space above the chat widget when it's embedded in this iframe.
const onChatIframeLoad = (event) => {
    try {
        const iframeDoc = event.target.contentDocument || event.target.contentWindow?.document;
        if (!iframeDoc) return;

        const style = iframeDoc.createElement('style');
        style.textContent = `
            html, body, #page, .site, .site-content, .ast-container, .ast-desktop {
                margin: 0 !important;
                padding: 0 !important;
            }
            .bp-messages-wrap-main, .bp-messages-wrap {
                margin-top: 0 !important;
            }
        `;
        iframeDoc.head.appendChild(style);
    } catch (error) {
        // Cross-origin or not-yet-ready document - safe to ignore
    }
};
</script>

<template>
<div class="chat-messages tfhb-admin-dashboard">
    <div class="chat-messages-header">
        <h1>{{ $tfhb_trans('Message') }}</h1>
        <p>{{ $tfhb_trans('Chat directly with buyers, sellers, and exhibitors taking part in the event.') }}</p>
    </div>
    <iframe
        v-if="AddonsAuth.event_settings.live_chat_url != ''"
        :src="AddonsAuth.event_settings.live_chat_url"
        class="chat-messages-iframe"
        title="Live Chat"
        allowfullscreen
        @load="onChatIframeLoad"
    ></iframe>
</div>
</template>

<style scoped>
/* Your component styles go here */
.chat-messages  {
	width: auto;
	margin: 0 auto;
	padding: 20px !important;
}

.chat-messages-header {
    margin-bottom: 1.5rem;
}

.chat-messages-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--tfhb-text-title-color, #141915);
}

.chat-messages-header p {
    margin: 0;
    color: var(--tfhb-paragraph-color, #273F2B);
}

.chat-messages-iframe {
    width: 100%;
    height: calc(100vh - 260px);
    min-height: 480px;
    border: none;
}

iframe #wpadminbar{
	display: none;
}
/* .tfhb-admin-meetings { 
	padding: 20px !important;
} */
</style>

<style scoped>
 


</style>

