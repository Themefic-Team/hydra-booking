<script setup> 
import { ref, onBeforeMount, defineProps  } from 'vue'; 
import Sidebar from './Sidebar.vue';
import topHeader from './topHeader.vue';
import AttendeeTopHeader from './attendees/AttendeeTopHeader.vue';
import AttendeeSidebar from './attendees/AttendeeSidebar.vue';


// Store 
import { Notification } from '@/store/notification';
import { FdDashboard } from '@/store/frontend-dashboard.js';

const collapsedSideBar = ref(false);

// on before mount 

onBeforeMount(() => {  
  FdDashboard.FetchUserAuth(); 
}); 

 
 
</script>

<template > 
<div v-if="FdDashboard.user_role == 'tfhb_attendee'"> 
    <div class="tfhb-frontend-dashboard tfhb-flexbox tfhb-gap-8 tfhb-justify-between tfhb-align-normal">
 
        <AttendeeSidebar :collapsed="collapsedSideBar" @toggle="collapsedSideBar = !collapsedSideBar"  />
        <!-- Load Route view Content -->
        <div class="tfhb-frontend-main-content">
            <router-view /> 
        </div>
      
    </div>
    
</div>

<div  v-if="FdDashboard.user_role == 'tfhb_host' || FdDashboard.user_role != '' ">
    <topHeader :notifications="Notification.Data" :userAuth="FdDashboard.userAuth" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
 
    <div class="tfhb-frontend-dashboard tfhb-flexbox tfhb-gap-8 tfhb-justify-between tfhb-align-normal">
        <!-- Load Sidebar -->
        <Sidebar :collapsed="collapsedSideBar" @toggle="collapsedSideBar = !collapsedSideBar"  />
    
        <!-- Load Route view Content -->
        <div class="tfhb-frontend-main-content">
            <router-view /> 
        </div>
      
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