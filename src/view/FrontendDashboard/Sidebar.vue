<script setup>  
import { ref, onMounted, defineProps  } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import { RouterView } from 'vue-router' 
const collapsedSideBar = ref(false);

const props = defineProps({
  collapsed: Boolean,
});
const emit = defineEmits(['toggle']);
 
</script>

<template > 
    
    <div @transitionend="onTransitionEnd" :class="{ collapsed: collapsed }" class="tfhb-frontend-sidebar  tfhb-flexbox tfhb-justify-between tfhb-align-normal">
         <div class="tfhb-frontend-sidebar-menu tfhb-full-width">
            <span class="tfhb-sidbar-slide-icon " :class="collapsed ? 'collapsed' : ''" @click="$emit('toggle')">
                <Icon :name="collapsed ? 'PanelLeftOpen' : 'PanelLeftClose'" />
            </span>

            <h6 class="tfhb-sidebar-menu-heading">
                <template v-if="!collapsed">
                    GENERAL
                </template> 
            </h6>

            <ul class="tfhb-sidebar-menu">
                <li>
                    <router-link   class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" to="/" exact :class="{ 'active': $route.path === '/' }">
                        <Icon name="LayoutDashboard" /> 
                        <span v-if="!collapsed" >Dashboard</span> 
                    </router-link>
                </li>
                <li>
                    <router-link  class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12"  to="/meetings" exact :class="{ 'active': $route.path === '/meetings' }" >
                        <Icon name="Presentation" />
                        <span v-if="!collapsed" > Meetings</span>
                    </router-link>
                </li>
                <li>
                    <router-link class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12"  to="/booking" exact :class="{ 'active': $route.path === '/booking' }">
                        <Icon name="CalendarCheck" />
                        <span v-if="!collapsed" > Booking</span>
                    </router-link>
                </li>
                <li>
                    <router-link class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" to="/hosts" exact :class="{ 'active': $route.path === '/hosts' }">
                        <Icon name="User" />
                        <span v-if="!collapsed" > Hosts</span>
                    </router-link>
                </li>
                <li class="tfhb-dropdown-menu">
                    <router-link  class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" to="/settings" exact :class="{ 'active': $route.path === '/settings' }">
                        <Icon name="Settings" />
                        <span v-if="!collapsed"  class="tfhb-flexbox tfhb-justify-between " style="width:calc(100% - 38px)"> Settings

                            <span class="dropdown-icon">
                                <Icon name="ChevronDown" />
                                <Icon name="ChevronUp" />
                            </span>
                        </span>
                        
                    </router-link>
                    
                    <ul class="tfhb-dropdown">
                        <li>
                            <router-link to="/settings/general" exact :class="{ 'active': $route.path === '/settings/general' }  "class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" >
                                <Icon name="SlidersHorizontal" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('General') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link to="/settings/availability" :class="{ 'active': $route.path === '/settings/availability' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                <Icon name="Clock" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('Availability') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link to="/settings/notifications" :class="{ 'active': $route.path === '/settings/notifications' }"class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                <Icon name="BellDot" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('Notifications') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link to="/settings/integrations" :class="{ 'active': $route.path === '/settings/integrations' }"class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                <Icon name="Unplug" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('Integrations') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link  to="/settings/appearance" :class="{ 'active': $route.path === '/settings/appearance' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                <Icon name="SwatchBook" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('Appearance') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link to="/settings/category" :class="{ 'active': $route.path === '/settings/category' }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb" exact>
                                <Icon name="ClipboardList" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('Meeting Category') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link to="/settings/hosts-settings" exact :class="{ 'active': $route.path.startsWith('/settings/hosts-settings') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb">
                                <Icon name="UserCog" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('Host Settings') }}</span>
                            </router-link>
                        </li> 
                        <li>
                            <router-link to="/settings/license" exact :class="{ 'active': $route.path.startsWith('/settings/license') }" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12 tfhb">
                                <Icon name="FileLock2" />
                                <span v-if="!collapsed" >{{ $tfhb_trans('License') }}</span>
                            </router-link>
                        </li>  
                    </ul>
                </li>

            </ul>

            <div class="divider"></div>
 

            <h6 class="tfhb-sidebar-menu-heading">
                <template v-if="!collapsed">
                    MORE
                </template> 
            </h6>

            <ul class="tfhb-sidebar-menu">
                <li>
                    <a href="#" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" exact>
                        <Icon name="BadgePercent" />
                        <span v-if="!collapsed" >Offers</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 tfhb-p-12" exact>
                        <Icon name="MessageCircleQuestion" />
                        <span v-if="!collapsed" >Support Center</span>
                    </a>
                </li>
            
            </ul>
         </div>

         <div v-if="!collapsed"  class="tfhb-frontend-sidebar-card  tfhb-mt-72 tfhb-flexbox tfhb-gap-8 tfhb-justify-center">
            <span class="sidebar-card-icon">
                <svg width="42" height="55" viewBox="0 0 42 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.9307 30.3453L17.2307 22.9981L11.1612 18.2703H18.5723L21.0001 10.6675L23.364 18.2703H30.839L24.7696 22.9981L27.0057 30.3453L21.0001 25.8092L14.9307 30.3453ZM5.92233 54.112V34.6898C4.00566 32.6879 2.63205 30.4944 1.8015 28.1092C0.970942 25.724 0.555664 23.3175 0.555664 20.8898C0.555664 15.0972 2.51492 10.2417 6.43344 6.32309C10.352 2.40457 15.2076 0.445312 21.0001 0.445312C26.7927 0.445312 31.6482 2.40457 35.5668 6.32309C39.4853 10.2417 41.4446 15.0972 41.4446 20.8898C41.4446 23.3175 41.0293 25.724 40.1987 28.1092C39.3682 30.4944 37.9946 32.6879 36.0779 34.6898V54.112L21.0001 49.0648L5.92233 54.112ZM21.0001 37.5009C25.6427 37.5009 29.5718 35.893 32.7876 32.6773C36.0034 29.4615 37.6112 25.5323 37.6112 20.8898C37.6112 16.2472 36.0034 12.318 32.7876 9.10226C29.5718 5.88651 25.6427 4.27865 21.0001 4.27865C16.3576 4.27865 12.4284 5.88651 9.21261 9.10226C5.99683 12.318 4.389 16.2472 4.389 20.8898C4.389 25.5323 5.99683 29.4615 9.21261 32.6773C12.4284 35.893 16.3576 37.5009 21.0001 37.5009Z" fill="#2E6B38"/>
                </svg>
            </span>
            <h4  v-if="!collapsed"  style="text-align: center;">Make Your Scheduling Effortless</h4>
            <HbButton 
                v-if="!collapsed" 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                @click="ExportBookingAsCSV"
                :buttonText="$tfhb_trans('Upgrade - Save 29%')" 
            /> 
         </div>

    </div>
    
</template>  
<style scoped>  
 
</style>