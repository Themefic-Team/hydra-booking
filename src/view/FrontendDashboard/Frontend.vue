<script setup> 
import { ref, onMounted, defineProps  } from 'vue'; 
import Sidebar from './Sidebar.vue';
import topHeader from './topHeader.vue';


// Store 
import { Notification } from '@/store/notification';

const collapsedSideBar = ref(false);
 
</script>

<template > 
    <topHeader :title="$tfhb_trans('Dashboard')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
        
    <div class="tfhb-frontend-dashboard tfhb-flexbox tfhb-gap-8 tfhb-justify-between tfhb-align-normal">
        <!-- Load Sidebar -->
        <Sidebar :collapsed="collapsedSideBar" @toggle="collapsedSideBar = !collapsedSideBar"  />
        
        <!-- Load Route view Content -->
        <div class="tfhb-frontend-main-content">
            <router-view /> 
        </div>
      
    </div>
    
</template>  
<style scoped>
.tfhb-frontend-dashboard { 
  height: 100vh; 
}

.tfhb-frontend-sidebar {
  width: 272px;
  transition: width 0.3s ease; 
}

.tfhb-frontend-sidebar.collapsed {
  width: 82px;
}

.tfhb-frontend-main-content {
  flex: 1; 
  transition: margin-left 0.3s ease;
}
</style>