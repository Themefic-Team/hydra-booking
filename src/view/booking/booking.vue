<script setup>
import { RouterView } from 'vue-router' 
import Header from '@/components/Header.vue';

import { ref, onMounted  } from 'vue';
import { Notification } from '@/store/notification';
onMounted(() => { 
    Notification.fetchNotifications();
}); 
</script>
<template>

<!-- {{ tfhbClass }} -->
<div class="tfhb-admin-booking">

    <Header :title="$tfhb_trans('Bookings')" :notifications="Notification.Data"  :total_unread="Notification.total_unread"  @MarkAsRead="Notification.MarkAsRead()"/>
    
    <router-view />
</div> 
</template>

<style scoped>

</style>