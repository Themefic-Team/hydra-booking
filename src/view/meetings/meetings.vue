<script setup>
import { RouterView, useRoute } from 'vue-router' 
import Header from '@/components/Header.vue';
import { ref, onMounted  } from 'vue';
import { Notification } from '@/store/notification';
onMounted(() => { 
    Notification.fetchNotifications();
}); 
// get current route url
const route = useRoute();

 
</script>
<template>

<!-- {{ tfhbClass }} -->
<div class="tfhb-admin-meetings" :class="{ 'tfhb-admin-frontend-end-wrap': $route.path === '/meetings/list' }">
    <Header v-if="$front_end_dashboard == false"  :title="$tfhb_trans('Meetings')":notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" />
    
    <router-view />
</div> 
</template>

<style scoped>

</style>