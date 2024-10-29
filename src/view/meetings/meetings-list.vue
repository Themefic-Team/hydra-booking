<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter, RouterView } from 'vue-router' 
const router = useRouter();
import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue'; 
import ShareMeeting from '@/components/meetings/ShareMeeting.vue';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import { Host } from '@/store/hosts'
import { Meeting } from '@/store/meetings'

const FilterPreview = ref(false);
const FilterHostPreview = ref(true);
const FilterCatgoryPreview = ref(true);
const isModalOpened = ref(false);

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
const openModal = () => {
  isModalOpened.value = true;
};
const closeModal = () => { 
  isModalOpened.value = false;
};

onBeforeMount(() => { 
    Host.fetchHosts();
    Meeting.fetchMeetings();
    Meeting.fetchMeetingCategory()
});


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
    shareData.embed = '<iframe src="'+ tfhb_core_apps.admin_url +'/?hydra-booking=meeting&meeting-id='+data.id+'&type=iframe" title="description"  height="600" width="100%" ></iframe>'

    // Popup open
    sharePopup.value = true; 
}



const meeting = reactive({});
const TfhbMeetingType = (type, router) => { 
    if(type == 'one-to-group' && typeof tfhb_core_apps_pro === 'undefined') { 
        toast.error('This feature is only available in pro version', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });
        return;
    }

    meeting.meeting_type = type;
    console.log(meeting)
     alert(type)
    Meeting.CreatePopupMeeting(meeting, router);
}

const resetFilter = () => {
    filterData.title = '';
    filterData.fhosts = [];
    filterData.fcategory = [];
    Meeting.fetchMeetings();
}

// outside click
window.addEventListener('click', function(e) {
    if (!document.querySelector('.tfhb-filter-content-wrap').contains(e.target)) {
        FilterPreview.value = false;
    }
});

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
window.addEventListener('click', function(e) {
    if (!document.querySelector('.tfhb-meetings-list-wrap').contains(e.target)) {
        activeItemDropdown.value = 0;
    }
});

// trunkate string 
const truncateString = (str, num) => {
    if (str.length <= num) {
        return str
    }
    return str.slice(0, num) + '...'
}
</script>
<template>
<!-- {{ filterData }} -->

    <div class="tfhb-dashboard-heading tfhb-flexbox">
        <div class="tfhb-filter-box tfhb-flexbox">
            <div class="tfhb-filter-content-wrap " :class="FilterPreview ? 'active' : ''">
                <div class="tfhb-filter-icon tfhb-filter-btn tfhb-flexbox"  @click="FilterPreview=!FilterPreview"><Icon name="Filter" size=20 /> 
                {{ __('Filter', 'hydra-booking') }}</div>
                <transition name="tfhb-dropdown-transition">
                    <div class="tfhb-filter-box-content" v-show="FilterPreview">
                        <div class="tfhb-filter-form">
                            <div class="tfhb-filter-category">
                                <div class="tfhb-host-filter-box tfhb-flexbox" @click="FilterHostPreview=!FilterHostPreview">
                                    {{ __('All Host', 'hydra-booking') }} <Icon name="ChevronUp" size=20 v-if="FilterHostPreview"/> <Icon name="ChevronDown" size=20 v-else="FilterHostPreview"/>
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
                                <div class="tfhb-host-filter-box tfhb-flexbox" @click="FilterCatgoryPreview=!FilterCatgoryPreview">
                                    {{ __('All Category', 'hydra-booking') }} <Icon name="ChevronUp" size=20 v-if="FilterCatgoryPreview"/> <Icon name="ChevronDown" size=20 v-else="FilterCatgoryPreview"/>
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
                                {{ __('Reset Filter', 'hydra-booking') }}
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
        <div class="thb-admin-btn right">
            <button class="tfhb-btn boxed-btn flex-btn" @click="openModal"><Icon name="PlusCircle" size=20 /> {{ __('Create New Meeting', 'hydra-booking') }}</button>
        </div> 
    </div>

 
    <HbPopup :isOpen="isModalOpened" @modal-close="closeModal" max_width="400px" name="first-modal">
        <template #header> 
            <!-- {{ google_calendar }} -->
            <h2>{{ __('Create New Meeting Type', 'hydra-booking') }} </h2>
            
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
                            <h3>{{ __('One to One', 'hydra-booking') }}</h3>
                        </div>
                        <p>{{ __('One host with one invitee. Good for: 1:1 interview, coffee chats', 'hydra-booking') }}</p>
                    </div>
                    <div class="tfhb-meeting-type-icon">
                        <Icon name="ArrowRight" width="20"/>
                    </div>
                </div>
            </div>
            <div class="tfhb-meeting-person-type" 
                    :class="
                    {
                        'tfhb-pro': !$tfhb_is_pro, 
                    }
                    "
                > 
                <span class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8" v-if="$tfhb_is_pro == false"><Icon name="Crown" size=20 />  {{ __('Pro', 'hydra-booking') }}</span>
                <div class="tfhb-meeting-type-card tfhb-flexbox tfhb-gap-32 tfhb-p-24" @click="TfhbMeetingType('one-to-group', router)">
                    <div class="tfhb-meeting-type-content">
                        <div class="tfhb-flexbox tfhb-justify-normal tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-justify-normal tfhb-gap-4">
                                <Icon name="UserRound" size=20 /> 
                                <Icon name="ArrowRight" size=20 /> 
                                <Icon name="UsersRound" size=20 /> 
                            </div>
                            <h3>{{ __('One to Group', 'hydra-booking') }}</h3>
                        </div>
                        <p>{{ __('One host with group of invitee. Good for: webinars, online clasess', 'hydra-booking') }}</p>
                    </div>
                    <div class="tfhb-meeting-type-icon">
                        <!-- <Icon name="ArrowRight" width="20"/> -->
                    </div>
                </div>
            </div>
        </template> 
    </HbPopup>

    <HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="400px" name="first-modal">
       

        <template #content>  
            <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                <div class="tfhb-close-icon">
                    <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                </div>
                <div class="tfhb-close-content">
                    <h3>{{ __('Are you absolutely sure??', 'hydra-booking') }}  </h3>  
                    <p>{{ __('Data and bookings associated with this meeting will be deleted. It will not affect previously scheduled meetings.', 'hydra-booking') }}</p>
                </div>
                <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                    <button class="tfhb-btn secondary-btn flex-btn" @click=" deletePopup = !deletePopup">{{ __('Cancel', 'hydra-booking') }}</button>
                    <button class="tfhb-btn  flex-btn boxed-btn-danger" @click="deleteItemConfirm">{{ __('Delete', 'hydra-booking') }}</button>
                </div>
            </div> 
        </template> 
    </HbPopup>

    

    <div class="tfhb-meetings-list-content">
        <div  v-if="Meeting.meetings.length > 0" class="tfhb-meetings-list-wrap tfhb-flexbox tfhb-justify-normal">

            <!-- Single Meeting -->
            <div class="tfhb-single-meeting tfhb-flexbox" v-for="(smeeting, key) in Meeting.meetings"> 
                <div class="single-meeting-content-box tfhb-gap-4 tfhb-flexbox tfhb-full-width">
                    <div class="single-meeting-content">
                        <h3> {{ smeeting.title ? truncateString(smeeting.title, 60) : 'No Title' }} </h3>
                        <div class="meeting-user-info">
                            <ul class="tfhb-flexbox">
                                <li v-if="smeeting.duration">
                                    <div class="tfhb-flexbox">
                                        <div class="user-info-icon">
                                            <Icon name="Clock" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ smeeting.duration }} {{ __('minutes', 'hydra-booking') }}
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
                                            {{ __('One to One', 'hydra-booking') }}
                                        </div>
                                    </div>
                                    <div class="tfhb-flexbox" v-if="'one-to-group'==smeeting.meeting_type">
                                        <div class="user-info-icon">
                                            <Icon name="UserRound" size=16 /> 
                                            <Icon name="ArrowRight" size=16 /> 
                                            <Icon name="UsersRound" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ __('One to Group', 'hydra-booking') }}
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
                                                {{smeeting.total_booking}} Booked
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
                                <router-link :to="{ name: 'MeetingsCreate', params: { id: smeeting.id } }" class="tfhb-dropdown-single">{{ __('Edit', 'hydra-booking') }}</router-link>
                                
                                <!-- <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="Meeting.deleteMeeting(smeeting.id, smeeting.post_id)">{{ __('Delete', 'hydra-booking') }}</span> -->
                                <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="deleteItemData(smeeting.id, smeeting.post_id)">{{ __('Delete', 'hydra-booking') }}</span>
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="single-meeting-action-btn tfhb-flexbox">
                    <a :href="smeeting.permalink" class="tfhb-flexbox" target="_blank">
                        <Icon name="Eye" size=20 /> 
                        {{ __('Preview', 'hydra-booking') }}
                    </a>
                    <a href="#" class="tfhb-flexbox" @click.prevent="sharePopupData(smeeting)">
                        <Icon name="Share2" size=20 /> 
                        {{ __('Share', 'hydra-booking') }}
                    </a>
                </div>
            </div>

            <!-- Share Meeting -->
            <ShareMeeting :shareData="shareData" :sharePopup="sharePopup"  />
             
            
        </div>
        <div v-else class="tfhb-empty-notice-box-wrap tfhb-flexbox tfhb-gap-16 tfhb-full-width">  
            <img :src="$tfhb_url+'/assets/images/icon-calendar.svg'" alt="" >
            <p>{{ __('No Meeting Created', 'hydra-booking') }}</p> 
        </div>
    </div>
    
</template>

<style scoped>
 
</style>