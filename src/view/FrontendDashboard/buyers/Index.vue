<script setup> 
import { ref, onBeforeMount, defineProps  } from 'vue';  
// import BuyersTopHeader from './BuyersTopHeader.vue';
import BuyersSidebar from './BuyersSidebar.vue';
import TopHeader from '../common/TopHeader.vue';
const collapsedSideBar = ref(false);
</script>

<template > 
   <TopHeader   /> 
 
   <div class="tfhb-frontend-dashboard tfhb-flexbox tfhb-gap-8 tfhb-justify-between tfhb-align-normal">
  
        <BuyersSidebar :collapsed="collapsedSideBar" @toggle="collapsedSideBar = !collapsedSideBar"  />
        
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