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
</script>

<template>    
<div class="chat-messages">
    <iframe 
        v-if="AddonsAuth.event_settings.live_chat_url != ''"
        :src="AddonsAuth.event_settings.live_chat_url" 
        style="width:100%; height:680px; border:none;" 
        title="Live Chat"
        allowfullscreen
    ></iframe>
</div>
</template> 

<style scoped>
/* Your component styles go here */
.chat-messages {
	width: 800px;
    margin: 0 auto;
}
iframe #wpadminbar{
	display: none;
}

</style>

<style scoped>
 


</style>

