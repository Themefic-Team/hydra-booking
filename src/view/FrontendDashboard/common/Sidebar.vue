<script setup>  
import { ref, onMounted, defineProps, onBeforeMount  } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import { RouterView, useRoute } from 'vue-router' 

import { useRouter } from 'vue-router';
const router = useRouter();
const collapsedSideBar = ref(false);

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
const props = defineProps({
  collapsed: Boolean,
});
const emit = defineEmits(['toggle']);
const route = useRoute();
const showGeneralMenu = ref(false);
const role_prefix = ref('');
//  if click tfhb-sidebar-menu li  a and it has child ul then show the child ul
const toggleSidebar = () => {
    document.querySelector('.tfhb-frontend-sidebar').classList.toggle('collapsed');
    emit('toggle');
}
// if tfhb-responsive-menu-trigger click add a active class into tfhb-frontend-sidebar  div  using add class not toggle
const toggleSidebarResponsive= () => {
    document.querySelector('.tfhb-frontend-sidebar').classList.toggle('responsive-active');
}

onBeforeMount(async () => {
    await AddonsAuth.FetchSettings();  
});
const redirectToChat = () => { 
    showGeneralMenu.value = false;
    AddonsAuth.chat_user_id = 0;
    router.push({ name: 'HydraAddonsMessages' });
}
</script>

<template >  
<!-- {{ AddonsAuth }} -->
    <div @transitionend="onTransitionEnd"  class="tfhb-frontend-sidebar ">
         <div class="tfhb-frontend-sidebar-menu tfhb-full-width"> 
            <span class="tfhb-sidbar-slide-icon " :class="collapsed ? 'collapsed' : ''" @click="toggleSidebar">
                <Icon :name="collapsed ? 'PanelLeftOpen' : 'PanelLeftClose'" />
            </span> 
            <div class="tfhb-sidbar-responsive-close" @click="toggleSidebarResponsive">
               <span> <Icon name="X" size="20" /></span>
            </div> 

            <router-link   @click="showGeneralMenu = false" class="tfhb-sidebar-menu-heading event-details-link" to="/event/details" exact :class="{ 'active': $route.path === '/event/details' }">
                     
                <template v-if="!collapsed">
                    {{ AddonsAuth.event.title }} <br>
                    <span v-if="AddonsAuth.event.availability_range && AddonsAuth.event.availability_range.start && AddonsAuth.event.availability_range.end">
                        From {{
                            new Date(AddonsAuth.event.availability_range.start).getDate()
                        }} to {{
                            new Date(AddonsAuth.event.availability_range.end).getDate()
                        }} {{
                            new Date(AddonsAuth.event.availability_range.end).toLocaleString('default', { month: 'short' })
                        }}, {{
                            new Date(AddonsAuth.event.availability_range.end).getFullYear()
                        }}
                    </span>
                </template> 
            </router-link>
            <br>
            <h6 class="tfhb-sidebar-menu-heading">
                <template v-if="!collapsed">
                    {{ $tfhb_trans('GENERAL') }}
                </template> 
            </h6>

            <ul class="tfhb-sidebar-menu"> 
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  :to="'/'+(AddonsAuth.loggedInUser?.user_role || 'buyers')+'/my-profile'" exact :class="{ 'active': $route.path === '/'+(AddonsAuth.loggedInUser?.user_role || 'buyers')+'/my-profile' }" >
                        <Icon name="CircleUser" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('My Profile') }}</span>
                    </router-link>
                </li>   
                <li v-if="AddonsAuth.loggedInUser?.user_role !== 'tfhb_exhibitors'">
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  :to="'/'+(AddonsAuth.loggedInUser?.user_role || 'buyers')+'/my-appointments'" exact :class="{ 'active': $route.path === '/'+(AddonsAuth.loggedInUser?.user_role || 'buyers')+'/my-appointments' }" >
                        <Icon name="CalendarClock" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Agenda') }}</span>
                    </router-link>
                </li>   
                <!-- <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/buyers/profile" exact :class="{ 'active': $route.path === '/buyers/profile' }" >
                        <Icon name="Contact" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Profile') }}</span>
                    </router-link>
                </li> -->
                <!-- <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/buyers/calenders" exact :class="{ 'active': $route.path === '/buyers/calenders' }" >
                        <Icon name="Presentation" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Event Info') }}</span>
                    </router-link>
                </li>   -->
            </ul>

            <h6 class="tfhb-sidebar-menu-heading">
                <template v-if="!collapsed">
                    {{ $tfhb_trans('Users List') }}
                </template> 
            </h6>
            <ul class="tfhb-sidebar-menu"> 
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/buyer-list" exact :class="{ 'active': $route.path === '/buyer-list' }" >
                        <Icon name="Users" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Buyers') }}</span>
                    </router-link>
                </li>   
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/seller-list" exact :class="{ 'active': $route.path === '/seller-list' }" >
                        <Icon name="Users" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Sellers') }}</span>
                    </router-link>
                </li>   
                <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/exhibitors-list" exact :class="{ 'active': $route.path === '/exhibitors-list' }" >
                        <Icon name="Users" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Exhibitors') }}</span>
                    </router-link>
                </li>   
                <li>
                    <button  class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  @click="redirectToChat()" :class="{ 'active': $route.path === '/messages' }" >
                        <Icon name="MessageCircleMore" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Message') }}</span>
                        <div v-html="AddonsAuth.message_count"></div>
                    </button>
                </li>   
                <!-- <li>
                    <router-link  @click="showGeneralMenu = false" class="tfhb-sidebar-menu-item tfhb-flexbox tfhb-gap-12 "  to="/sellers/seller-list" exact :class="{ 'active': $route.path === '/sellers/seller-list' }" >
                        <Icon name="Users" size="20" /> 
                        <span v-if="!collapsed" > {{ $tfhb_trans('Buyers') }}</span>
                    </router-link>
                </li>    -->
            </ul>

 
         </div>
 

    </div>
    
</template>  
<style scoped lang="scss">  
 .event-details-link{
    font-size: 20px !important;
    display: inline-block;
    margin-bottom: 20px !important;
    color: #C0D8C4 !important;
    font-weight: 700; 
    // hover
    &:hover, &.active{
        color: #fff !important;
    }

 }

</style>