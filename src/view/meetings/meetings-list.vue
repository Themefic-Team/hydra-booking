<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter, RouterView, onBeforeRouteLeave  } from 'vue-router' 
const router = useRouter();
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue'; 
import HbPreloader from '@/components/icon/HbPreloader.vue'
import ShareMeeting from '@/components/meetings/ShareMeeting.vue';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbProPopup from '@/components/widgets/HbProPopup.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';
import HbRadio from '@/components/form-fields/HbRadio.vue'; 
import { Host } from '@/store/hosts'
import { Meeting } from '@/store/meetings'

import { importExport } from '@/store/settings/importExport';
 
const FilterPreview = ref(false);
const FilterHostPreview = ref(true);
const FilterCatgoryPreview = ref(true); 
const ProPopup = ref(false);
const deletePopup = ref(false)
const deleteItem = reactive({
    id: 0,
    post_id: 0
});
const deleteItemData = (id, post_id) => {
    deleteItem.id = id;
    deleteItem.post_id = post_id;
    deletePopup.value = true;
}
const deleteItemConfirm = () => {
    Meeting.deleteMeeting(deleteItem.id, deleteItem.post_id);
    deletePopup.value = false;
}
 
function hideDropdownOutsideClick(e) {
    if (!document.querySelector('.tfhb-meetings-list-wrap').contains(e.target)) {
        activeItemDropdown.value = 0;
    }
    if (!document.querySelector('.tfhb-filter-box').contains(e.target)) {
        FilterPreview.value = false;
    }
    
}

onBeforeMount(() => { 
    Host.fetchHosts();
    Meeting.fetchMeetings();
    Meeting.fetchMeetingCategory();
    window.addEventListener('click', hideDropdownOutsideClick);

    

});

const CloneMeetings = (id, data) => {
    Meeting.cloneMeeting(id, data);
}

onBeforeRouteLeave((to, from, next) => {
    activeItemDropdown.value = 0;
    window.removeEventListener('click', hideDropdownOutsideClick);
    next();
})


// Filtering
const filterData = reactive({
    title: '',
    fhosts: [],
    fcategory: [],
    startDate: '',
    endDate: ''
})

// Share Popup Data
const shareData = reactive({
    title: '',
    time: '',
    meeting_type: '',
    share_type: 'link',
    link: '',
    shortcode: '',
    embed: ''
})

const sharePopup = reactive({
    value: false
})

const sharePopupData = (data) => { 
    shareData.share_type = 'link'
    shareData.title = data.title
    shareData.time = data.duration
    shareData.meeting_type = data.meeting_type
    shareData.shortcode = '[hydra_booking id="'+data.id+'"]'
    shareData.link = data.permalink
    shareData.embed = ' <div class="hydra-booking-embed-container" data-url="'+tfhb_core_apps.admin_url+'" data-meeting-id="'+data.id+'"></div> '+tfhb_core_apps.embed_script_link+''

    // Popup open
    sharePopup.value = true; 
}



const meeting = reactive({});
const TfhbMeetingType = (type, router) => {  
    // return false;
    if(type == 'one-to-group' && typeof tfhb_core_apps_pro === 'undefined' ) { 
       
        toast.error('This feature is only available in pro version', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return;
    }
    if((type == 'one-to-group' && tfhb_core_apps_pro.tfhb_is_pro !=true)) {  
        toast.error('This feature is only available in pro version', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return;
    }

    meeting.meeting_type = type; 
    Meeting.CreatePopupMeeting(meeting, router);
}

const resetFilter = () => {
    filterData.title = '';
    filterData.fhosts = [];
    filterData.fcategory = [];
    Meeting.fetchMeetings();
}



const activeItemDropdown = ref(0);
// on click add class active
const activeSingleMeetingDropdown = (id) => {
    if(activeItemDropdown.value == id) {
        activeItemDropdown.value = 0;
        return;
    }
    activeItemDropdown.value = id; 

}
// outside click

// trunkate string 
const truncateString = (str, num) => {
    if (str.length <= num) {
        return str
    }
    return str.slice(0, num) + '...'
}

const ExportAsCSV = ref(false);
const exportData = reactive({
    type: 'CSV',
    date_range: 'days',
    start_date: '',
    end_date: '',
});

</script>
<template>
<!-- {{ filterData }} -->

    <HbProPopup  v-if="tfhb_is_pro == false || $tfhb_license_status == false" :isOpen="ProPopup" @modal-close="ProPopup = false" max_width="500px" name="first-modal" gap="32px" />   
    
    <div class="tfhb-dashboard-heading tfhb-flexbox tfhb-justify-between" >
        <div class="tfhb-filter-box tfhb-flexbox">
            <div class="tfhb-filter-content-wrap " :class="FilterPreview ? 'active' : ''">
                <div class="tfhb-filter-icon tfhb-filter-btn tfhb-flexbox"  @click="FilterPreview=!FilterPreview"><Icon name="Filter" size=20 /> 
                {{ $tfhb_trans('Filter') }}</div>
                <transition name="tfhb-dropdown-transition">
                    <div class="tfhb-filter-box-content" v-show="FilterPreview">
                        <div class="tfhb-filter-form">
                            <div class="tfhb-filter-category">
                                <div class="tfhb-host-filter-box tfhb-flexbox tfhb-justify-between" @click="FilterHostPreview=!FilterHostPreview">
                                    {{ $tfhb_trans('All Host') }} <Icon name="ChevronUp" size=20 v-if="FilterHostPreview"/> <Icon name="ChevronDown" size=20 v-else="FilterHostPreview"/>
                                </div>
                                <div class="tfhb-filter-category-box" v-show="FilterHostPreview">
                                    <ul class="tfhb-flexbox">
                                        <li class="tfhb-flexbox" v-for="(shost, key) in Host.hosts" :key="key">
                                            <label>
                                                <input type="checkbox" :value="shost.value" v-model="filterData.fhosts" @change="Meeting.Tfhb_Meeting_Select_Filter(filterData)">
                                                <span class="checkmark"></span>
                                                {{ shost.name }}
                                            </label>
                                            <!-- <div class="tfhb-category-items">
                                                25
                                            </div> -->
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="tfhb-filter-category">
                                <div class="tfhb-host-filter-box tfhb-flexbox tfhb-justify-between" @click="FilterCatgoryPreview=!FilterCatgoryPreview">
                                    {{ $tfhb_trans('All Category') }} <Icon name="ChevronUp" size=20 v-if="FilterCatgoryPreview"/> <Icon name="ChevronDown" size=20 v-else="FilterCatgoryPreview"/>
                                </div>
                                <div class="tfhb-filter-category-box" v-show="FilterCatgoryPreview">
                                    <ul class="tfhb-flexbox">
                                        <li class="tfhb-flexbox" v-for="(mcategory, key) in Meeting.meetingCategory" :key="key">
                                            <label>
                                                <input type="checkbox" :value="mcategory.id" v-model="filterData.fcategory" @change="Meeting.Tfhb_Meeting_Select_Filter(filterData)">
                                                <span class="checkmark"></span>
                                                {{ mcategory.name }}
                                            </label>
                                            <!-- <div class="tfhb-category-items">
                                                25
                                            </div> -->
                                        </li>
                                    </ul>
                                </div>
                            </div>
                

                        </div>
                        <div class="tfhb-reset-btn" v-if="filterData.fcategory.length > 0 || filterData.fhosts.length > 0 || filterData.title">
                            <button @click="resetFilter" class="tfhb-flexbox">
                                <Icon name="RefreshCw" size=20 /> 
                                {{ $tfhb_trans('Reset Filter') }}
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="tfhb-header-filters">
                <input type="text" v-model="filterData.title" @keyup="Meeting.Tfhb_Meeting_Filter(filterData)" placeholder="Search by meeting title" /> 
                <span><Icon name="Search" size=20 /></span>
            </div>
        </div>
        <div class="thb-admin-btn tfhb-flexbox tfhb-gap-16">
            <HbButton 
                v-if="$user.role != 'tfhb_host'"
                classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : ExportAsCSV = true"
                :buttonText="$tfhb_trans('Export')"
                icon="FileDown"   
                :hover_animation="false" 
                icon_position = 'left'
            />    
            <HbButton 
                v-if="$user.role != 'tfhb_host'"
                classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                @click="tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : router.push({ name: 'MeetingsImport' })"
                :buttonText="$tfhb_trans('Import')"
                icon="FileUp"   
                :hover_animation="false" 
                icon_position = 'left'
            />   
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                @click="Meeting.isModalOpened = true"
                :buttonText="$tfhb_trans('Create New Meeting')"
                icon="PlusCircle"   
                :hover_animation="false" 
                icon_position = 'left'
            />   
        </div> 
    </div> 
    <!-- Export CSV POPup -->
    <HbPopup  :isOpen="ExportAsCSV" @modal-close="ExportAsCSV = false" max_width="500px" name="first-modal" gap="32px">
        <template #header>  
            <h3>{{$tfhb_trans('Export Meeting as')}} {{$tfhb_trans(exportData.type)}}</h3>
        </template>

        <template #content> 
            
            <HbRadio  
                required= "true"
                v-model="exportData.date_range"
                name="request_header"
                :label="$tfhb_trans('Date Range')"
                :groups="true" 
                :options="[
                    {'label': 'Today', 'value': 'days'},  
                    {'label': 'Last 7 Days', 'value': 'weeks'},
                    {'label': 'Current Month', 'value': 'months'},
                    {'label': 'Last Year', 'value': 'years'}, 
                    {'label': 'All', 'value': 'all'}, 
                    {'label': 'Custom', 'value': 'custom'} 
                ]" 
            />
        <div v-if="exportData.date_range == 'custom'" class="custom-date-range" >
            <label for="">{{ $tfhb_trans('Select Date Range') }}</label>
            <div class="tfhb-filter-dates tfhb-flexbox">
                
                <HbDateTime 
                    v-model="exportData.start_date"
                    :label="''" 
                    width="40"
                    enableTime='true'
                    icon="CalendarDays"
                    :placeholder="$tfhb_trans('From')"   
                /> 
                <div class="tfhb-calender-move-icon">
                    <Icon name="MoveRight" size="20px" /> 
                </div>
                <HbDateTime 
                    v-model="exportData.end_date"
                    :label="''" 
                    width="40"
                    icon="CalendarDays"
                    enableTime='true'
                    :placeholder="$tfhb_trans('To')"  
                />  
            </div> 
        </div>

        <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width"> 
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                @click="importExport.exportMeetings(exportData)"
                :buttonText="$tfhb_trans('Export Meetings')"
                icon="ChevronRight"   
                hover_icon="ArrowRight"
                :pre_loader="exportAsPreloader"
                :hover_animation="true" 
                icon_position = 'right'
            /> 
        
        </div>
        </template> 
    </HbPopup>
<!-- Export CSV POPup -->

 
    <HbPopup :isOpen="Meeting.isModalOpened" @modal-close="Meeting.isModalOpened = false" max_width="400px" name="first-modal">
        <template #header> 
            <!-- {{ google_calendar }} -->
            <h2>{{ $tfhb_trans('Create New Meeting') }} </h2>
            
        </template>

        <template #content>  
            <div class="tfhb-meeting-person-type">
                <div class="tfhb-meeting-type-card tfhb-flexbox tfhb-gap-32 tfhb-p-24" @click="TfhbMeetingType('one-to-one', router)">
                    <div class="tfhb-meeting-type-content">
                        <div class="tfhb-flexbox tfhb-justify-normal tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-justify-normal tfhb-gap-4">
                                <Icon name="UserRound" size=20 /> 
                                <Icon name="ArrowRight" size=20 /> 
                                <Icon name="UserRound" size=20 /> 
                            </div>
                            <h3>{{ $tfhb_trans('One to One') }}</h3>
                        </div>
                        <p>{{ $tfhb_trans('One host with one invitee. Good for: 1:1 interview, coffee chats') }}</p>
                    </div>
                    <div class="tfhb-meeting-type-icon">
                        <Icon v-if="Meeting.pre_loader == false" name="ArrowRight" width="20"/>
                        <HbPreloader v-else color="#2E6B38" />
                        
                    </div>
                </div>
            </div> 
            <div class="tfhb-meeting-person-type" 
                :class=" {
                    'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status, 
                }"
            > 
                <span class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8" v-if="$tfhb_is_pro == false || $tfhb_license_status == false"><Icon name="Crown" size=20 />  {{ $tfhb_trans('Pro') }}</span>
                <div class="tfhb-meeting-type-card tfhb-flexbox tfhb-gap-32 tfhb-p-24" @click="TfhbMeetingType('one-to-group', router)">
                    <div class="tfhb-meeting-type-content">
                        <div class="tfhb-flexbox tfhb-justify-normal tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-justify-normal tfhb-gap-4">
                                <Icon name="UserRound" size=20 /> 
                                <Icon name="ArrowRight" size=20 /> 
                                <Icon name="UsersRound" size=20 /> 
                            </div>
                            <h3>{{ $tfhb_trans('One to Group') }}</h3>
                        </div>
                        <p>{{ $tfhb_trans('One host with group of invitee. Good for: webinars, online clasess') }}</p>
                    </div>
                    <div class="tfhb-meeting-type-icon">
                        <div v-if="$tfhb_is_pro == true && $tfhb_license_status == true" class="tfhb-meeting-type-icon">
                            <Icon v-if="Meeting.pre_loader_group == false" name="ArrowRight" width="20"/>
                            <HbPreloader v-else color="#2E6B38" />
                            
                        </div>
                    </div>
                </div>
            </div>
        </template> 
    </HbPopup>

    <HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="542px" name="first-modal">
        <template #header> 

            
        </template>  

        <template #content>  
            <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                <div class="tfhb-close-icon">
                    <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                </div>
                <div class="tfhb-close-content">
                    <h3>{{ $tfhb_trans('Are you absolutely sure?') }}  </h3>  
                    <p>{{ $tfhb_trans('Data and bookings associated with this meeting will be deleted. It will not affect previously scheduled meetings.') }}</p>
                </div>
                <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click=" deletePopup = !deletePopup"
                        :buttonText="$tfhb_trans('Cancel')" 
                    />  
                    <HbButton  
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                        @click="deleteItemConfirm"
                        :buttonText="$tfhb_trans('Delete')"
                        icon="Trash2"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />
                    
                </div>
            </div> 
        </template> 
    </HbPopup>

    

    <div class="tfhb-meetings-list-content" :class="{ 'tfhb-skeleton': Meeting.skeleton }"> 
        <div  v-if="Meeting.meetings.length > 0" class="tfhb-meetings-list-wrap tfhb-flexbox tfhb-justify-normal">

            <!-- Single Meeting -->
            <div class="tfhb-single-meeting tfhb-flexbox " v-for="(smeeting, key) in Meeting.meetings"> 
                <div class="single-meeting-content-box tfhb-gap-4 tfhb-flexbox tfhb-full-width">
                    <div class="single-meeting-content">
                        <h3> <router-link :to="{ name: 'MeetingsCreate', params: { id: smeeting.id } }">{{ smeeting.title ? truncateString(smeeting.title, 60) : 'No Title' }} </router-link> </h3>
                        <div class="meeting-user-info">
                            <ul class="tfhb-flexbox">
                                <li v-if="smeeting.duration">
                                    <div class="tfhb-flexbox">
                                        <div class="user-info-icon">
                                            <Icon name="Clock" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ smeeting.duration }} {{ $tfhb_trans('minutes') }}
                                        </div>
                                    </div>
                                </li>
                                <li v-if="smeeting.meeting_type">
                                    <div class="tfhb-flexbox" v-if="'one-to-one'==smeeting.meeting_type">
                                        <div class="user-info-icon">
                                            <Icon name="UserRound" size=16 /> 
                                            <Icon name="ArrowRight" size=16 /> 
                                            <Icon name="UserRound" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ $tfhb_trans('One to One') }}
                                        </div>
                                    </div>
                                    <div class="tfhb-flexbox" v-if="'one-to-group'==smeeting.meeting_type">
                                        <div class="user-info-icon">
                                            <Icon name="UserRound" size=16 /> 
                                            <Icon name="ArrowRight" size=16 /> 
                                            <Icon name="UsersRound" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ $tfhb_trans('One to Group') }}
                                        </div>
                                    </div>
                                </li>
                                <li >
                                    <div class="tfhb-flexbox">
                                        <div class="user-info-icon">
                                            <Icon name="Banknote" size=16 /> 
                                        </div>
                                        <div v-if="smeeting.meeting_price" class="user-info-title">
                                            {{ smeeting.meeting_price }}
                                        </div>
                                        <div v-else class="user-info-title">
                                            Free
                                        </div>
                                        
                                    </div>
                                </li>
                                <li v-if="smeeting.host_first_name">
                                    <div class="tfhb-flexbox">
                                        <div class="user-info-icon">
                                            <Icon name="User" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ smeeting.host_first_name }} {{ smeeting.host_last_name }}
                                        </div>
                                    </div>
                                </li>
                                <li class="tfhb-booked-items">
                                    <div class="tfhb-flexbox ">
                                        <div class="booked-items tfhb-flexbox "> 
                                            <div class="user-info-icon">
                                                <Icon name="CalendarCheck" size=16 /> 
                                            </div>
                                            <div class="user-info-title">
                                                {{smeeting.total_booking}} {{ $tfhb_trans('Booked') }}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div @click="activeSingleMeetingDropdown(smeeting.id)" class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt="">
                        <transition name="tfhb-dropdown-transition">
                            <div v-show="smeeting.id == activeItemDropdown" class="tfhb-dropdown-wrap "> 
                                <!-- route link -->
                                <router-link :to="{ name: 'MeetingsCreate', params: { id: smeeting.id } }" class="tfhb-dropdown-single"><Icon name="SquarePen" size=16 />{{ $tfhb_trans('Edit') }}</router-link>

                                <span class="tfhb-dropdown-single" @click="CloneMeetings(smeeting.id, smeeting)"><Icon name="CopyCheck" size=16 />{{ $tfhb_trans('Clone') }}</span>
                                
                                <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="deleteItemData(smeeting.id, smeeting.post_id)"><Icon name="Trash2" size=16 />{{ $tfhb_trans('Delete') }}</span>
                               
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="single-meeting-action-btn tfhb-flexbox tfhb-justify-between">
                    <a :href="smeeting.permalink" class="tfhb-flexbox" target="_blank">
                        <Icon name="Eye" size=20 /> 
                        {{ $tfhb_trans('Preview') }}
                    </a>
                    <a href="#" class="tfhb-flexbox" @click.prevent="sharePopupData(smeeting)">
                        <Icon name="Share2" size=20 /> 
                        {{ $tfhb_trans('Share') }}
                    </a>
                </div>
            </div>

            <!-- Share Meeting -->
            <ShareMeeting :shareData="shareData" :sharePopup="sharePopup"  />
             
            
        </div>
        <div v-else class="tfhb-empty-notice-box-wrap tfhb-flexbox tfhb-gap-16 tfhb-full-width">  
            <span > <img :src="$tfhb_url+'/assets/images/icon-calendar.svg'" alt="" > </span>
            <p>{{ $tfhb_trans('No Meeting Created') }}</p> 
        </div>
    </div>
    
</template>

<style scoped>
 
</style>