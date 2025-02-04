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
</script>

<template > 
    
    <div @transitionend="onTransitionEnd"  class="tfhb-frontend-sidebar ">
         <div class="tfhb-frontend-sidebar-menu tfhb-full-width"> 
            <span class="tfhb-sidbar-slide-icon " :class="collapsed ? 'collapsed' : ''" @click="toggleSidebar">
                <Icon :name="collapsed ? 'PanelLeftOpen' : 'PanelLeftClose'" />
            </span> 
            <h6 class="tfhb-sidebar-menu-heading">
                <template v-if="!collapsed">
                    GENERAL
                </template> 
            </h6>

            <ul class="tfhb-sidebar-menu">
                <li>
                    <router-link   @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" to="/" exact :class="{ 'active': $route.path === '/' }">
                        <Icon name="LayoutDashboard" size="20" /> 
                        <span v-if="!collapsed" >Dashboard</span> 
                    </router-link>
                </li>
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12"  to="/meetings" exact :class="{ 'active': $route.path === '/meetings' }" >
                        <Icon name="Presentation" size="20" /> 
                        <span v-if="!collapsed" > Meetings</span>
                    </router-link>
                </li>
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12"  to="/bookings" exact :class="{ 'active': $route.path === '/booking' }">
                        <Icon name="CalendarCheck" size="20" /> 
                        <span v-if="!collapsed" > Bookings</span>
                    </router-link>
                </li>
                <li v-if="$user.role[0] != 'tfhb_host' ">
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" to="/hosts" exact :class="{ 'active': $route.path === '/hosts' }">
                        <Icon name="User" size="20" /> 
                        <span v-if="!collapsed" > Hosts</span>
                    </router-link>
                </li>
                <li v-if="$user.role[0] != 'tfhb_host' " class="tfhb-dropdown-menu">
                    <router-link  to="/settings" exact :class="{ 'active': $route.path.includes('/settings') }"  @click="showGeneralMenu = !showGeneralMenu" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" >
                        <Icon name="Settings" size="20" /> 
                        <span v-if="!collapsed"  class="tfhb-flexbox tfhb-justify-between " style="width:calc(100% - 38px)"> Settings 
                            <span class="dropdown-icon">
                                <Icon  v-if="showGeneralMenu == true" name="ChevronDown" size="20" /> 
                                <Icon v-if="showGeneralMenu == false" name="ChevronUp" size="20" /> 
                            </span>
                        </span>
                        
                    </router-link >
                    <transition name="accordion">
                        <ul v-if="showGeneralMenu == true" class="tfhb-dropdown">
                            <li>
                                <router-link to="/settings/general" exact :class="{ 'active': $route.path === '/settings/general' }  "class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" >
                                    <Icon name="SlidersHorizontal" size="20" /> 
                                    <span >{{ $tfhb_trans('General') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/availability" :class="{ 'active': $route.path === '/settings/availability' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                    <Icon name="Clock" size="20" /> 
                                    <span >{{ $tfhb_trans('Availability') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/notifications" :class="{ 'active': $route.path === '/settings/notifications' }"class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                    <Icon name="BellDot" size="20" /> 
                                    <span >{{ $tfhb_trans('Notifications') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/integrations" :class="{ 'active': $route.path === '/settings/integrations' }"class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                    <Icon name="Unplug" size="20" /> 
                                    <span >{{ $tfhb_trans('Integrations') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link  to="/settings/appearance" :class="{ 'active': $route.path === '/settings/appearance' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                    <Icon name="SwatchBook" size="20" /> 
                                    <span >{{ $tfhb_trans('Appearance') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/category" :class="{ 'active': $route.path === '/settings/category' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                    <Icon name="ClipboardList" size="20" /> 
                                    <span >{{ $tfhb_trans('Meeting Category') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/hosts-settings" exact :class="{ 'active': $route.path.startsWith('/settings/hosts-settings') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb">
                                    <Icon name="UserCog" size="20" /> 
                                    <span >{{ $tfhb_trans('Host Settings') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/license" exact :class="{ 'active': $route.path.startsWith('/settings/license') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb">
                                    <Icon name="FileLock2" size="20" /> 
                                    <span >{{ $tfhb_trans('License') }}</span>
                                </router-link>
                            </li>  
                        </ul>
                    </transition> 
                </li>

            </ul>
 
         </div>
 

    </div>
    
</template>  
<style scoped>  
 
</style>