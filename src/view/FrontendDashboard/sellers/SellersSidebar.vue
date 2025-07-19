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
                    <router-link   @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 " to="/sellers/event-list" exact :class="{ 'active': $route.path === '/sellers/event-list' }">
                        <Icon name="CalendarDays" size="20" /> 
                        <span v-if="!collapsed" >{{ $tfhb_trans('All Events') }}</span> 
                    </router-link>
                </li>  
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/sellers/my-appointments" exact :class="{ 'active': $route.path === '/sellers/my-appointments' }" >
                        <Icon name="CalendarClock" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('My Appointments') }}</span>
                    </router-link>
                </li>  
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/sellers/sellers-list" exact :class="{ 'active': $route.path === '/sellers/sellers-list' }" >
                        <Icon name="Users" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('View Buyers') }}</span>
                    </router-link>
                </li>  
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/sellers/profile" exact :class="{ 'active': $route.path === '/sellers/profile' }" >
                        <Icon name="Contact" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Profile') }}</span>
                    </router-link>
                </li>
                <!-- <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/sellers/calenders" exact :class="{ 'active': $route.path === '/buyers-dashboard/calenders' }" >
                        <Icon name="Presentation" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Event Info') }}</span>
                    </router-link>
                </li>   -->
            </ul>
 
         </div>
 

    </div>
    
</template>  
<style scoped>  
 
</style>