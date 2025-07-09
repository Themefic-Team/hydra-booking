<script setup>  
import { ref, onMounted, defineProps  } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import { RouterView, useRoute } from 'vue-router' 
const collapsedSideBar = ref(false);

const props = defineProps({
  collapsed: Boolean,
});
const emit = defineEmits(['toggle']);
const route = useRoute();
const showGeneralMenu = ref(false);

//  if click tfhb-sidebar-menu li  a and it has child ul then show the child ul
const toggleSidebar = () => {
    document.querySelector('.tfhb-frontend-sidebar').classList.toggle('collapsed');
    emit('toggle');
}
// if tfhb-responsive-menu-trigger click add a active class into tfhb-frontend-sidebar  div  using add class not toggle
const toggleSidebarResponsive= () => {
    document.querySelector('.tfhb-frontend-sidebar').classList.toggle('responsive-active');
}
</script>

<template > 
    
    <div @transitionend="onTransitionEnd"  class="tfhb-frontend-sidebar ">
         <div class="tfhb-frontend-sidebar-menu tfhb-full-width"> 
            <span class="tfhb-sidbar-slide-icon " :class="collapsed ? 'collapsed' : ''" @click="toggleSidebar">
                <Icon :name="collapsed ? 'PanelLeftOpen' : 'PanelLeftClose'" />
            </span> 
            <div class="tfhb-sidbar-responsive-close" @click="toggleSidebarResponsive">
               <span> <Icon name="X" size="20" /></span>
            </div> 
            <h6 class="tfhb-sidebar-menu-heading">
                <template v-if="!collapsed">
                    {{ $tfhb_trans('GENERAL') }}
                </template> 
            </h6>

            <ul class="tfhb-sidebar-menu">
                <li>
                    <router-link   @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 " to="/#" exact :class="{ 'active': $route.path === '/' }">
                        <Icon name="LayoutDashboard" size="20" /> 
                        <span v-if="!collapsed" >{{ $tfhb_trans('Dashboard') }}</span> 
                    </router-link>
                </li>
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/#" exact :class="{ 'active': $route.path === '/meetings' }" >
                        <Icon name="Presentation" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Schedule') }}</span>
                    </router-link>
                </li> 
                <li v-if="$user.role[0] != 'tfhb_host' ">
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 " to="/#" exact :class="{ 'active': $route.path === '/#' }">
                        <Icon name="User" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('My Profile') }}</span>
                    </router-link>
                </li>  

            </ul>
 
         </div>
 

    </div>
    
</template>  
<style scoped>  
 
</style>