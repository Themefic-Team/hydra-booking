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
                    <router-link   @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 " to="/" exact :class="{ 'active': $route.path === '/' }">
                        <Icon name="LayoutDashboard" size="20" /> 
                        <span v-if="!collapsed" >{{ $tfhb_trans('Dashboard') }}</span> 
                    </router-link>
                </li>
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/meetings" exact :class="{ 'active': $route.path === '/meetings' }" >
                        <Icon name="Presentation" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Meetings') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/bookings" exact :class="{ 'active': $route.path === '/booking' }">
                        <Icon name="CalendarCheck" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Bookings') }}</span>
                    </router-link>
                </li>
                <li v-if="$user.role[0] != 'tfhb_host' ">
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 " to="/hosts" exact :class="{ 'active': $route.path === '/hosts' }">
                        <Icon name="User" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Hosts') }}</span>
                    </router-link>
                </li> 
 
                <li v-if="$user.caps.tfhb_manage_settings == true ">
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 " to="/settings/integrations/#all" exact :class="{ 'active': $route.path === '/settings/integrations' }">
                        <Icon name="Unplug" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Integrations') }}</span>
                    </router-link>
                </li> 
                <li v-if="$user.caps.tfhb_manage_settings == true " :class="{ 'active': $route.path.includes('/settings') }" class="tfhb-dropdown-menu">
                    <!-- <router-link  to="/settings" exact :class="{ 'active': $route.path.includes('/settings') }"  class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" > -->
                        <router-link  @click="showGeneralMenu = !showGeneralMenu"  v-if="!collapsed" to="/settings/general" exact :class="{ 'active': $route.path === '/settings/general' }  "class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12" >
                            <Icon name="SlidersHorizontal" size="20" />  
                            <span v-if="!collapsed"  class="tfhb-flexbox tfhb-justify-between " style="width:calc(100% - 38px)"> {{ $tfhb_trans('Settings') }} 
                                <span class="dropdown-icon">
                                    <Icon  v-if="showGeneralMenu == true" name="ChevronDown" size="20" /> 
                                    <Icon v-if="showGeneralMenu == false" name="ChevronRight" size="20" /> 
                                </span>
                            </span>
                        </router-link>
                        <button v-else :class="{ 'active': $route.path.includes('/settings') }" @click="showGeneralMenu = !showGeneralMenu"   class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 ">
                            <Icon name="Settings" size="20" /> 
                            <span v-if="!collapsed"  class="tfhb-flexbox tfhb-justify-between " style="width:calc(100% - 38px)"> {{ $tfhb_trans('Settings') }} 
                                <span class="dropdown-icon">
                                    <Icon  v-if="showGeneralMenu == true" name="ChevronDown" size="20" /> 
                                    <Icon v-if="showGeneralMenu == false" name="ChevronRight" size="20" /> 
                                </span>
                            </span>
                        </button>
                       
                        
                    <!-- </router-link > -->
                    <transition name="accordion">
                        <ul v-if="showGeneralMenu == true" class="tfhb-dropdown">
                            <li>
                                <router-link to="/settings/general" exact :class="{ 'active': $route.path === '/settings/general' }  "class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12" >
                                    <Icon name="SlidersHorizontal" size="20" /> 
                                    <span >{{ $tfhb_trans('General') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/availability" :class="{ 'active': $route.path === '/settings/availability' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12" exact>
                                    <Icon name="Clock" size="20" /> 
                                    <span >{{ $tfhb_trans('Availability') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/notifications" :class="{ 'active': $route.path === '/settings/notifications' }"class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12" exact>
                                    <Icon name="BellDot" size="20" /> 
                                    <span >{{ $tfhb_trans('Notifications') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link  to="/settings/appearance" :class="{ 'active': $route.path === '/settings/appearance' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12" exact>
                                    <Icon name="SwatchBook" size="20" /> 
                                    <span >{{ $tfhb_trans('Appearance') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/category" :class="{ 'active': $route.path === '/settings/category' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12" exact>
                                    <Icon name="ClipboardList" size="20" /> 
                                    <span >{{ $tfhb_trans('Meeting Category') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/hosts-settings" exact :class="{ 'active': $route.path.startsWith('/settings/hosts-settings') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12">
                                    <Icon name="UserCog" size="20" /> 
                                    <span >{{ $tfhb_trans('Host Settings') }}</span>
                                </router-link>
                            </li> 

                            <li>
                                <router-link to="/settings/shortcodes" exact :class="{ 'active': $route.path.startsWith('/settings/shortcodes') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12">
                                    <Icon name="Braces" size="20" /> 
                                    <span >{{ $tfhb_trans('Shortcodes') }}</span>
                                </router-link>
                            </li> 
                            <li>
                                <router-link to="/settings/license" exact :class="{ 'active': $route.path.startsWith('/settings/license') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12">
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