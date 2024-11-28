<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import { RouterView, useRouter } from 'vue-router' 
import HbText from '@/components/form-fields/HbText.vue'
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import ShareMeeting from '@/components/meetings/ShareMeeting.vue';
import { setupWizard } from '@/store/setupWizard'; 



const router = useRouter();

// Toast
import { toast } from "vue3-toastify"; 
import { BookTemplateIcon } from 'lucide-vue-next';

const sharePopup = ref(false)

const props = defineProps({
    setupWizard : {
        type: Object,
        required: true
    }
}); 

// / Share Popup Data
const shareData = reactive({
    title: '',
    time: '',
    meeting_type: '',
    share_type: 'link',
    link: '',
    shortcode: '',
    embed: ''
})


const ShareTabs = (tab) => {
    shareData.share_type = tab;
}

const sharePopupData = (data) => {

    shareData.share_type = 'link'
    shareData.title = data.title
    shareData.time = data.duration
    shareData.meeting_type = data.meeting_type
    shareData.shortcode = '[hydra_booking id="'+data.id+'"]'
    shareData.link = tfhb_core_apps.admin_url + '/' + data.slug
    shareData.embed = ' <div class="hydra-booking-embed-container" data-url="'+tfhb_core_apps.admin_url+'" data-meeting-id="'+data.id+'""></div> '+tfhb_core_apps.embed_script_link+''
     // Popup open
    sharePopup.value = true;
}


const StepFour = () => {
    props.setupWizard.currentStep = 'step-end';
}
const copyMeeting = (link) => {
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = link;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action
    toast.success(link + ' is Copied', {
        position: "bottom-right",
        duration: 2000
    }); 
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

const pre_loader = ref(false);
const gotoDashboard = () => {
    
    // weit for 2 sec
    pre_loader.value = true;
    setTimeout(() => {
        pre_loader.value = false;
        router.push({ name: 'dashboard' });
        props.setupWizard.currentStep = 'getting-start'
    }, 500);  
    
}
// outside click
window.addEventListener('click', function(e) { 
    if( props.setupWizard.currentStep == 'step-four' ){
        if (!document.querySelector('.tfhb-single-meeting').contains(e.target)) {
            
            activeItemDropdown.value = 0;
        }
    }
    
});
</script>

<template>
 

     <!-- Step Four -->
     <div class="tfhb-setup-wizard-content-wrap tfhb-admin-meetings tfhb-s-w-step-four tfhb-flexbox tfhb-gap-32">
       
        <div class="tfhb-s-w-icon-text">
            <div class="tfhb-step-wizard-steper tfhb-flexbox tfhb-gap-16" >
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 active"></span>
                <span class="tfhb-step-bar step-1 active"></span>
            </div> 

            <div class="tfhb-s-w-success-data tfhb-mt-32">
                <img :src="$tfhb_url+'/assets/images/success.gif'" style="height: 50px;" alt="">
            </div>
            <h2>{{ $tfhb_trans('Your Meeting is ready!') }}</h2>
            <p>{{ $tfhb_trans[`Your HydraBooking meeting is ready. Click 'Preview' to check your booking page or 'Share' to send the link to your attendees`] }}</p> 
        </div>

        <div class="tfhb-meetings-list-content" >
            <div class="tfhb-meetings-list-wrap tfhb-flexbox tfhb-justify-normal">

                <!-- Single Meeting --> 
                <div class="tfhb-single-meeting  tfhb-flexbox"> 
                    <div class="single-meeting-content-box tfhb-gap-4 tfhb-flexbox tfhb-full-width">
                        <div class="single-meeting-content">
                            <h3> {{ setupWizard.data.meeting ? setupWizard.data.meeting.title : 'No Title' }} </h3>
                            <div class="meeting-user-info">
                                <ul class="tfhb-flexbox">
                                    <li v-if="setupWizard.data.meeting.duration">
                                        <div class="tfhb-flexbox">
                                            <div class="user-info-icon">
                                                <Icon name="Clock" size=16 /> 
                                            </div>
                                            <div class="user-info-title">
                                                {{ setupWizard.data.meeting.duration }} {{ $tfhb_trans('minutes') }}
                                            </div>
                                        </div>
                                    </li>
                                    <li v-if="setupWizard.data.meeting.meeting_type">
                                        <div class="tfhb-flexbox" v-if="'one-to-one'==setupWizard.data.meeting.meeting_type">
                                            <div class="user-info-icon">
                                                <Icon name="UserRound" size=16 /> 
                                                <Icon name="ArrowRight" size=16 /> 
                                                <Icon name="UserRound" size=16 /> 
                                            </div>
                                            <div class="user-info-title">
                                                {{ $tfhb_trans('One to One') }}
                                            </div>
                                        </div>
                                        <div class="tfhb-flexbox" v-if="'one-to-group'==setupWizard.data.meeting.meeting_type">
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
                                            <div v-if="setupWizard.data.meeting.meeting_price" class="user-info-title">
                                                {{ setupWizard.data.meeting.meeting_price }}
                                            </div>
                                            <div else class="user-info-title">
                                                Free
                                            </div>
                                        </div>
                                    </li>
                                    <li v-if="setupWizard.data.meeting.host_first_name">
                                        <div class="tfhb-flexbox">
                                            <div class="user-info-icon">
                                                <Icon name="User" size=16 /> 
                                            </div>
                                            <div class="user-info-title">
                                                {{ setupWizard.data.meeting.host_first_name }} {{ setupWizard.data.meeting.host_last_name }}
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
                                                    {{setupWizard.data.meeting.total_booking}} Booked
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div @click="activeSingleMeetingDropdown(setupWizard.data.meeting.id)" class="tfhb-single-hosts-action tfhb-dropdown">
                            <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt="">
                            <transition name="tfhb-dropdown-transition">
                                <div v-show="setupWizard.data.meeting.id == activeItemDropdown" class="tfhb-dropdown-wrap"> 
                                    <!-- route link -->
                                    <router-link :to="{ name: 'MeetingsCreate', params: { id: setupWizard.data.meeting.id } }" class="tfhb-dropdown-single tfhb-flexbox tfhb-gap-4 tfhb-align-center">
                                        <Icon name="SquarePen" size=16 />
                                        {{ $tfhb_trans('Edit') }}
                                    </router-link>
                                    
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="single-meeting-action-btn tfhb-flexbox tfhb-justify-between">
                        <a :href="setupWizard.data.meeting.permalink" class="tfhb-flexbox" target="_blank">
                            <Icon name="Eye" size=20 /> 
                            {{ $tfhb_trans('Preview') }}
                        </a>
                        <a href="#" class="tfhb-flexbox" @click.prevent="sharePopupData(setupWizard.data.meeting)">
                            <Icon name="Share2" size=20 /> 
                            {{ $tfhb_trans('Share') }}
                        </a>
                    </div>
                </div>


                <HbPopup :isOpen="sharePopup" @modal-close="sharePopup = false" max_width="600px" name="first-modal">
        
                    <template #header> 
                        <h3>{{ shareData.title }}</h3>
                    </template>

                    <template #content>  
                        <div class="tfhb-meeting-durationinfo tfhb-flexbox tfhb-gap-32 tfhb-full-width">
                            <ul class="tfhb-locationtype tfhb-flexbox tfhb-justify-normal tfhb-full-width tfhb-gap-32">
                                <li v-if="shareData.time">
                                    <div class="tfhb-flexbox tfhb-gap-8">
                                        <div class="user-info-icon">
                                            <Icon name="Clock" size=16 />  
                                        </div>
                                        <div class="user-info-title">
                                            {{ shareData.time }} {{ $tfhb_trans('minutes') }}
                                        </div>
                                    </div>
                                </li>
                                <li v-if="shareData.meeting_type">
                                    <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-one'==shareData.meeting_type">
                                        <div class="user-info-icon">
                                            <Icon name="UserRound" size=16 /> 
                                            <Icon name="ArrowRight" size=16 /> 
                                            <Icon name="UserRound" size=16 /> 
                                        </div>
                                        <div class="user-info-title">
                                            {{ $tfhb_trans('One to One') }}
                                        </div>
                                    </div>
                                    <div class="tfhb-flexbox tfhb-gap-8" v-if="'one-to-group'==shareData.meeting_type">
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
                            </ul>

                            <div class="tfhb-share-type tfhb-full-width">
                                <ul class="tfhb-flexbox tfhb-gap-8">
                                    <li :class="'link'==shareData.share_type ? 'active' : ''" @click="ShareTabs('link')">{{ $tfhb_trans('Share link') }}</li>
                                    <li :class="'short'==shareData.share_type ? 'active' : ''" @click="ShareTabs('short')">{{ $tfhb_trans('Short code') }}</li>
                                    <li :class="'embed'==shareData.share_type ? 'active' : ''" @click="ShareTabs('embed')">Embed code</li>
                                </ul>
                            </div>

                            <div class="tfhb-shareing-data tfhb-full-width">
                                <div class="share-link" v-if="'link'==shareData.share_type"> 
                                    <HbText 
                                        v-model="shareData.link"  
                                        :readonly="true"

                                    />
                                    <div class="tfhb-copy-btn "> 
                                        <HbButton 
                                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                                            @click="copyMeeting(shareData.link)" 
                                            :buttonText="$tfhb_trans('Copy Code')" 
                                        />  
                                    </div>
                                </div>
                                <div class="share-link" v-if="'short'==shareData.share_type"> 
                                    <HbText 
                                        v-model="shareData.shortcode"  
                                        :readonly="true"

                                    />
                                    <div class="tfhb-copy-btn">
                                        <HbButton 
                                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                                            @click="copyMeeting(shareData.shortcode)" 
                                            :buttonText="$tfhb_trans('Copy Code')" 
                                        /> 
                                    </div>
                                </div>
                                <div class="share-link tfhb-flexbox tfhb-gap-24 tfhb-justify-end" v-if="'embed'==shareData.share_type">
                                
            
                                    <HbTextarea 
                                        v-model="shareData.embed"  
                                        :readonly="true"

                                    />
            
                                    <HbButton 
                                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 " 
                                        @click="copyMeeting(shareData.embed)" 
                                        :buttonText="$tfhb_trans('Copy Code')" 
                                    /> 
                                </div>
                                
                            </div>
                        </div>
                    </template> 
                </HbPopup> 

                </div>
        </div>
      
        <div class="tfhb-flexbox tfhb-justify-center tfhb-gap-16">
            <div class="tfhb-submission-btn tfhb-flexbox">
                <HbButton 
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 icon-left tfhb-icon-hover-animation tfhb-justify-center" 
                    @click="StepFour" 
                    :buttonText="$tfhb_trans('View Integrations')"
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true"   
                />  
                
            
                <!-- <button @click="props.setupWizard.currentStep = 'step-one'" class="tfhb-btn tfhb-btn tfhb-flexbox tfhb-gap-8" >Skip<Icon name="ChevronRight" size=20 />  </button> -->
            </div>
                
            <HbButton 
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 " 
                @click="gotoDashboard" 
                :buttonText="$tfhb_trans('Back to Dashboard ')"   
            />  
        </div>
        <div class="tfhb-submission-btn tfhb-flexbox">
             
           
          
            <!-- <button class="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" @click="" > <Icon name="ChevronLeft" size=20 /> Back </button>
            <button class="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" @click="StepFour" >Complete setup<Icon name="ChevronRight" size=20 />  </button> -->
        </div>
     </div>
     <!-- Step Four -->

</template>
 