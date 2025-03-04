<script setup> 
import { __ } from '@wordpress/i18n'; 
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount,computed } from 'vue';
import { useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 


// import Form Field 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';   
import HbText from '@/components/form-fields/HbText.vue' 
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbEditor from '@/components/form-fields/HbEditor.vue';
import HbButton from '@/components/form-fields/HbButton.vue'
import Editor from 'primevue/editor';

//  Load Time Zone 
const skeleton = ref(false);


const props = defineProps([
    'title', 
    'label', 
    'data',
    'ispopup',
    'update_preloader',
    'isSingle',
    'categoryKey',
    'emailKey'
])
const emit = defineEmits(['update-notification', 'popup-open-control', 'popup-close-control']);

const meetingShortcode = ref([
    '{{meeting.title}}',
    '{{meeting.date}}', 
    '{{meeting.location}}', 
    '{{meeting.duration}}', 
    '{{meeting.time}}', 
    '{{host.name}}', 
    '{{host.email}}',  
    '{{attendee.name}}', 
    '{{attendee.email}}', 
    '{{attendee.additional_data}}',
    '{{wp.admin_email}}',
    "{{booking.cancel_reason}}",
    "{{booking.cancel_link}}",
    "{{booking.full_start_end_host_timezone}}",
    "{{booking.start_date_time_for_host}}",
    "{{booking.start_date_time_for_attendee}}",
    "{{booking.full_start_end_attendee_timezone}}",
    "{{booking.rescheduled_reason}}",
    "{{booking.rescheduled_link}}",
    "{{booking.location_details_html}}",
])
const copyShortcode = (value) => { 
    //  copy to clipboard without navigator 
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show a toast notification or perform any other action
    toast.success(value + ' is Copied');
}

// Initialize contentVisibility with a nested structure
const contentVisibility = reactive({
    header: false,
    gratitude: false,
    meeting_details: {
        main: false,
        data_time: false,
        host: false,
        about: false,
        description: false,
        location: false
    },
    host_details: {
        main: false,
        name: false,
        email: false,
        phone: false
    },
    instructions: false,
    cancel_reschedule: {
        main: false,
        description: false,
        cancel: false,
        reschedule: false
    },
    footer: {
        main: false,
        description: false,
        social: false
    },
});

const ContentBox = (key, subKey = null) => {
    if (subKey) {
        if (emailBuilder[key].content[subKey].status) {
            contentVisibility[key][subKey] = !contentVisibility[key][subKey];
        }
    } else {
        if (emailBuilder[key].status) {
            if (typeof contentVisibility[key] === 'boolean') {
                contentVisibility[key] = !contentVisibility[key];
            } else {
                contentVisibility[key].main = !contentVisibility[key].main;
            }
        }
    }
};

const defaultEmailBuilder = reactive({ 
    header: {
        status: 1,
        content: '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>'
    },
    gratitude: {
        status: 1,
        content: '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with Host Name was confirmed.</p>',
    },
    meeting_details: {
        status: 1,
        title: 'Meeting Details',
        content: {
            data_time: {
                status: 1,
                content: '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
            },
            host: {
                status: 1,
                content: '<strong>{{host.name}}</strong>'
            },
            about: {
                status: 1,
                content: '<strong>{{meeting.title}}</strong>'
            },
            description: {
                status: 1,
                content: '{{meeting.content}}'
            },
            location: {
                status: 1,
                content: '<strong>{{booking.location_details_html}}</strong>'
            }
        },
    },
    host_details: {
        status: 1,
        title: 'Host Details',
        content: {
            name: {
                status: 1,
                content: '<strong>{{host.name}}</strong>'
            },
            email: {
                status: 1,
                content: '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
            },
            phone: {
                status: 1,
                content: '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
            },
        }
    },
    instructions: {
        status: 1,
        title: 'Instructions',
        content: '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
    },
    cancel_reschedule: {
        status: 1,
        content: {
            description: {
                status: 1,
                content: 'You can cancel or reschedule this event for any reason.'
            },
            cancel: {
                status: 1,
                content: '{{booking.cancel_link}}'
            },
            reschedule: {
                status: 1,
                content: '{{booking.rescheduled_link}}'
            },
        }
    },
    footer: {
        status: 1,
        content: {
            description: {
                status: 1,
                content: '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
            },
            social: {
                status: 1,
                facebook: '#',
                x: '#',
                youtube: '#',
            },
        }
    },
});

const emailBuilder = computed(() => {
    return props.data?.builder && Object.keys(props.data.builder).length 
        ? props.data.builder 
        : defaultEmailBuilder;
});

const closePopup = () => { 
    emit('popup-close-control', false)
}
</script>
<template>
    <!-- Single Notification  -->
    <div class="tfhb-notification-single tfhb-flexbox tfhb-justify-between">
        <div class="tfhb-swicher-wrap  tfhb-flexbox"> 

            <!-- Checkbox swicher -->
            <HbSwitch v-model="props.data.status"  @change="emit('update-notification')"  :label="props.label "  /> 

        </div>
        <router-link v-if="isSingle" class="tfhb-btn tfhb-edit flex-btn" :to="{ name: 'EmailTemplateSingle', params: { id: emailKey, type: categoryKey } }">
            <Icon name="PencilLine" size=15 /> {{ $tfhb_trans('Edit') }}
        </router-link>
        <button v-else class="tfhb-btn tfhb-edit flex-btn" @click="emit('popup-open-control')" ><Icon name="PencilLine" size=15 /> {{ $tfhb_trans('Edit') }} </button>

        
        <HbPopup :isOpen="ispopup" @modal-close="closePopup" max_width="700px" name="first-modal">
            <template #header> 
                <h3>{{ title }}</h3>
                
            </template>

            <template #content>
                <div class="tfhb-email-builder">
                    <div class="tfhb-builder-tools">
                    <div class="tfhb-template-info tfhb-flexbox tfhb-gap-16 tfhb-mb-32">
                        
                        <HbDropdown 
                            v-model="data.template"  
                            required= "true" 
                            :label="$tfhb_trans('Select Template')"  
                            :selected = "1"
                            :placeholder="$tfhb_trans('Select Template')"   
                            :option = "[
                                {'name': 'Default', 'value': 'default'},  
                            ]"  
                        /> 
                        <HbText  
                            v-model="data.from"   
                            type="email"
                            :label="$tfhb_trans('From')"  
                            selected = "1"
                            :placeholder="$tfhb_trans('Enter From Email')"  
                        /> 
                        <div class="tfhb-shortcode-box tfhb-full-width">
                            <HbText  
                                v-model="data.subject"  
                                required= "true"  
                                :label="$tfhb_trans('Subject')"  
                                selected = "1"
                                type = "text"
                                :placeholder="$tfhb_trans('Enter Mail Subject')"  
                                @tfhb-onclick="TfhbOnFocus"
                            /> 
                            <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('header')">
                                <Icon name="GripVertical" :width="20"/> 
                                {{ $tfhb_trans('Header') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.header.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.header && emailBuilder.header.status">
                            <div class="tfhb-shortcode-box tfhb-full-width">
                                <div @click="TfhbOnFocus">
                                    <Editor 
                                        v-model="emailBuilder.header.content"  
                                        :placeholder="$tfhb_trans('Mail Body')"    
                                        editorStyle="height: 180px" 
                                    />
                                </div>
                                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                    <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('gratitude')">
                                <Icon name="GripVertical" :width="20"/> 
                                {{ $tfhb_trans('Gratitude') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.gratitude.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.gratitude && emailBuilder.gratitude.status">
                            <div class="tfhb-shortcode-box tfhb-full-width">
                                <div @click="TfhbOnFocus">
                                    <Editor 
                                        v-model="emailBuilder.gratitude.content"  
                                        :placeholder="$tfhb_trans('Mail Body')"    
                                        editorStyle="height: 180px" 
                                    />
                                </div>
                                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                    <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Details Section -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('meeting_details')">
                                <Icon name="GripVertical" :width="20"/> 
                                {{ $tfhb_trans('Meeting Details') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.meeting_details.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.meeting_details.main && emailBuilder.meeting_details.status">
                            <HbText 
                                v-model="emailBuilder.meeting_details.title"  
                                :placeholder="$tfhb_trans('Heading')"    
                            />
                            <!-- Nested: Date & Time -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('meeting_details', 'data_time')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('meeting_details', 'data_time')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Date & Time:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.meeting_details.content.data_time.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.meeting_details.data_time && emailBuilder.meeting_details.content.data_time.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.meeting_details.content.data_time.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('meeting_details', 'host')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('meeting_details', 'host')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Host:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.meeting_details.content.host.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.meeting_details.host && emailBuilder.meeting_details.content.host.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.meeting_details.content.host.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('meeting_details', 'about')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('meeting_details', 'about')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('About:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.meeting_details.content.about.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.meeting_details.about && emailBuilder.meeting_details.content.about.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.meeting_details.content.about.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('meeting_details', 'description')" :width="20"/>
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('meeting_details', 'description')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Description:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.meeting_details.content.description.status" />
                                    </div> 
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.meeting_details.description && emailBuilder.meeting_details.content.description.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.meeting_details.content.description.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('meeting_details', 'location')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('meeting_details', 'location')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Location:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.meeting_details.content.location.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.meeting_details.location && emailBuilder.meeting_details.content.location.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.meeting_details.content.location.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <!-- Host Details Section -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('host_details')">
                                <Icon name="GripVertical" :width="20"/> 

                                {{ $tfhb_trans('Host Details') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.host_details.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.host_details.main && emailBuilder.host_details.status">
                            <HbText 
                                v-model="emailBuilder.host_details.title"  
                                :placeholder="$tfhb_trans('Heading')"    
                            />
                            <!-- Name -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('host_details', 'name')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('host_details', 'name')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Name:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.host_details.content.name.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.host_details.name && emailBuilder.host_details.content.name.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.host_details.content.name.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('host_details', 'email')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('host_details', 'email')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Email:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.host_details.content.email.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.host_details.email && emailBuilder.host_details.content.email.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.host_details.content.email.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('host_details', 'phone')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('host_details', 'phone')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Phone:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.host_details.content.phone.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.host_details.phone && emailBuilder.host_details.content.phone.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.host_details.content.phone.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('instructions')">
                                <Icon name="GripVertical" :width="20"/> 
                                {{ $tfhb_trans('Instructions') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.instructions.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.instructions && emailBuilder.instructions.status">
                            <HbText 
                                v-model="emailBuilder.instructions.title"  
                                :placeholder="$tfhb_trans('Instructions Heading')"    
                            />
                            <Editor 
                                v-model="emailBuilder.instructions.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 180px" 
                            />
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('cancel_reschedule')">
                                <Icon name="GripVertical" :width="20"/> 
                                {{ $tfhb_trans('Cancel & Reschedule') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.cancel_reschedule.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.cancel_reschedule.main && emailBuilder.cancel_reschedule.status">
                            
                            <!-- Description -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('cancel_reschedule', 'description')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('cancel_reschedule', 'description')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Heading:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.cancel_reschedule.content.description.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.cancel_reschedule.description && emailBuilder.cancel_reschedule.content.description.status">
                                    <Editor 
                                        v-model="emailBuilder.cancel_reschedule.content.description.content"  
                                        :placeholder="$tfhb_trans('Mail Body')"    
                                        editorStyle="height: 100px" 
                                    />
                                </div>
                            </div>

                            <!-- Cancel -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('cancel_reschedule', 'cancel')" :width="20"/>
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('cancel_reschedule', 'cancel')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Cancel URL:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.cancel_reschedule.content.cancel.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.cancel_reschedule.cancel && emailBuilder.cancel_reschedule.content.cancel.status">
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <HbText 
                                            v-model="emailBuilder.cancel_reschedule.content.cancel.content"  
                                            :placeholder="$tfhb_trans('Cancel URL:')"    
                                            @tfhb-onclick="TfhbOnFocus"
                                        />
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reschedule -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('cancel_reschedule', 'reschedule')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('cancel_reschedule', 'reschedule')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Reschedule URL:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.cancel_reschedule.content.reschedule.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.cancel_reschedule.reschedule && emailBuilder.cancel_reschedule.content.reschedule.status">
                                    
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <HbText 
                                            v-model="emailBuilder.cancel_reschedule.content.reschedule.content"  
                                            :placeholder="$tfhb_trans('Reschedule URL:')"    
                                            @tfhb-onclick="TfhbOnFocus"
                                        />
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox('footer')">
                                <Icon name="GripVertical" :width="20"/> 
                                {{ $tfhb_trans('Footer') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.footer.status" />
                        </div>
                        <div class="tools-content" v-show="contentVisibility.footer.main && emailBuilder.footer.status">
                            
                            <!-- Description -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('footer', 'description')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('footer', 'description')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Quick Content:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.footer.content.description.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.footer.description && emailBuilder.footer.content.description.status">
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <div @click="TfhbOnFocus">
                                            <Editor 
                                                v-model="emailBuilder.footer.content.description.content"  
                                                :placeholder="$tfhb_trans('Mail Body')"    
                                                editorStyle="height: 100px" 
                                            />
                                        </div>
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                            <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <Icon name="GripVertical" @click="ContentBox('footer', 'social')" :width="20"/> 
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('footer', 'social')">
                                        <div class="tfhb-flexbox tfhb-head">
                                            {{ $tfhb_trans('Social:') }}
                                        </div>
                                        <HbSwitch v-model="emailBuilder.footer.content.social.status" />
                                    </div>
                                </div>
                                <div class="tools-content" 
                                    v-show="contentVisibility.footer.social && emailBuilder.footer.content.social.status">
                                    <HbText 
                                        v-model="emailBuilder.footer.content.social.facebook"  
                                        :placeholder="$tfhb_trans('Facebook URL:')"    
                                    />
                                    <HbText 
                                        v-model="emailBuilder.footer.content.social.x"  
                                        :placeholder="$tfhb_trans('X (previously twitter)')"    
                                    />
                                    <HbText 
                                        v-model="emailBuilder.footer.content.social.youtube"  
                                        :placeholder="$tfhb_trans('Youtube')"    
                                    />
                                </div>
                            </div>

                        </div>
                    </div>
                    <HbButton  
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="UpdateNotification"
                        :buttonText="$tfhb_trans('Update')" 
                        icon="ChevronRight"
                        hover_icon="ArrowRight"
                        :hover_animation="true"
                        :pre_loader="preloader"
                    /> 
                    </div>
                </div>
                <!-- Time format -->
                <HbDropdown 
                    
                    v-model="data.template"  
                    required= "true" 
                    :label="$tfhb_trans('Select Template')"  
                    width="50"
                    :selected = "1"
                    :placeholder="$tfhb_trans('Select Template')"   
                    :option = "[
                        {'name': 'Default', 'value': 'default'},  
                    ]"  
                /> 
                <!-- Time format --> 
                <HbText  
                    v-model="props.data.from"   
                    type="email"
                    width="50"
                    :label="$tfhb_trans('From')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter From Email')"  
                /> 

                <HbText  
                    v-model="props.data.subject"  
                    required= "true"  
                    :label="$tfhb_trans('Subject')"  
                    selected = "1"
                    type = "text"
                    :placeholder="$tfhb_trans('Enter Mail Subject')"  
                /> 
 
                
                <div class="tfhb-single-form-field" style="width: 100%;"  >
                    <div class="tfhb-single-form-field-wrap tfhb-field-input">
                        <!--if has label show label with tag else remove tags  --> 
                        <label for="">{{ $tfhb_trans('Mail Body') }}</label>  
                        <Editor 
                            v-model="props.data.body"  
                            :placeholder="$tfhb_trans('Mail Body')"    
                            editorStyle="height: 250px" 
                        />
                    </div> 
                </div> 
                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8"> 
                    <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>

                </div>

                <HbButton  
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="emit('update-notification')"
                    :buttonText="$tfhb_trans('Update')" 
                    icon="ChevronRight"
                    hover_icon="ArrowRight"
                    :hover_animation="true"
                    :pre_loader="props.update_preloader"
                />  
             </template> 
        </HbPopup>
    </div>
    <!-- Single Integrations  -->

 
</template>