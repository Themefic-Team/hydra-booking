<script setup> 
import { __ } from '@wordpress/i18n'; 
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount, computed, watch } from 'vue';
import { useRoute, useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 


// import Form Field 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbText from '@/components/form-fields/HbText.vue' 
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbButton from '@/components/form-fields/HbButton.vue'
import Editor from 'primevue/editor';
const route = useRoute();
//  Load Time Zone 
const skeleton = ref(false);
const emit = defineEmits(["update-notification"]); 
const props = defineProps([
    'mediaurl', 
]);

const Notification = reactive(  { 
     host : {
        booking_confirmation: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_pending: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reminder: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
    
     },
     attendee : {
        booking_confirmation: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_pending: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
        booking_reminder: {
            status : 0,
            template : 'default',
            from : '',
            subject : '',
            body : '',
            builder: ''
        },
    
     }
});

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

const emailBuilder = reactive({ 
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
        content: {
            data_time: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}</td>'
            },
            host: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong>{{host.name}}</strong></td>'
            },
            about: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong>{{meeting.title}}</strong></td>'
            },
            description: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;">{{meeting.content}}</td>'
            },
            location: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong>{{meeting.location}}</strong></td>'
            }
        },
    },
    host_details: {
        status: 1,
        content: {
            name: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong>{{host.name}}</strong></td>'
            },
            email: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong></td>'
            },
            phone: {
                status: 1,
                content: '<td style="padding-left: 32px;font-size: 15px; line-height: 24px;"><strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong></td>'
            },
        }
    },
    instructions: {
        status: 1,
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

// Computed Property to Generate Email Preview
const emailTemplate = computed(() => {
    let emailContent = '';

    if (emailBuilder.header.status) {
        emailContent += `<tr>
            <td bgcolor="#215732" style="padding: 16px 32px; text-align: left;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="vertical-align: middle;">
                            ${emailBuilder.header.content}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>`;
    }

    if (emailBuilder.gratitude.status) {
        emailContent += `<tr><td style="padding: 32px 32px 0 32px;">${emailBuilder.gratitude.content}</td></tr>`;
    }

    if (emailBuilder.meeting_details.status) {
        emailContent += `
            <tr>
                <td style="padding: 0 32px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; margin-top: 32px">
                        <tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
        `;

        Object.keys(emailBuilder.meeting_details.content).forEach(key => {
            if (emailBuilder.meeting_details.content[key].status) {
                emailContent += `
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                <tr>
                                    <td style="vertical-align: top; font-size: 15px; width: 120px;">
                                        ${getIcon(key)}
                                        ${formatLabel(key)}
                                    </td>
                                    ${emailBuilder.meeting_details.content[key].content}
                                </tr>
                            </table>
                        </td>
                    </tr>
                `;
            }
        });

        emailContent += `</table></td></tr>`;
    }

    if (emailBuilder.host_details.status) {
        emailContent += `
            <tr>
                <td style="padding: 32px 32px 0 32px">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px;">
                        <tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
        `;

        Object.keys(emailBuilder.host_details.content).forEach(key => {
            if (emailBuilder.host_details.content[key].status) {
                emailContent += `
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                <tr>
                                    <td style="vertical-align: top; font-size: 15px; width: 120px;">
                                        ${getIcon(key)}
                                        ${formatLabel(key)}
                                    </td>
                                    ${emailBuilder.host_details.content[key].content}
                                </tr>
                            </table>
                        </td>
                    </tr>
                `;
            }
        });

        emailContent += `</table></td></tr>`;
    }

    if (emailBuilder.instructions.status) {
        emailContent += `
            <tr>
                <td style="font-weight: bold; font-size: 17px; padding: 32px 32px 24px 32px;">Instructions</td>
            </tr>
            <tr>
                <td style="font-size: 15px; padding: 0 32px 0 32px;">${emailBuilder.instructions.content}</td>
            </tr>`;
    }
    if (emailBuilder.cancel_reschedule.status) {
        emailContent += ` <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 32px 0;"><tr><td style="padding: 0 32px;"><table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4;">`;
            if (emailBuilder.cancel_reschedule.content.description.content) {
                emailContent += ` <tr>
                    <td style="font-size: 15px;padding: 24px 0 16px 0;">${emailBuilder.cancel_reschedule.content.description.content}</td>
                </tr>`;
            }
            if (emailBuilder.cancel_reschedule.content.cancel.content || emailBuilder.cancel_reschedule.content.reschedule.content) {
                emailContent += `<tr>
                <td style="font-size: 15px; padding-bottom: 24px;">`;
                    if (emailBuilder.cancel_reschedule.content.cancel.content){
                        emailContent += `<a href="${emailBuilder.cancel_reschedule.content.cancel.content}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;">Cancel</a>`;
                    }
                    if (emailBuilder.cancel_reschedule.content.reschedule.content){
                        emailContent += `<a href="${emailBuilder.cancel_reschedule.content.reschedule.content}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;"">Reschedule</a>`;
                    }
                emailContent += `</td></tr>`;
            }
        emailContent += `</table></td></tr></table>`;
    }

    if (emailBuilder.footer.status) {
        emailContent += `<tr>
            <td align="center">
                <table width="100%" role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;">
                    <tr>`;
                        if (emailBuilder.footer.content.description.content) {
                        emailContent += `<td align="left">
                            ${emailBuilder.footer.content.description.content}
                        </td>`;
                        }
                        if (emailBuilder.footer.content.social) {
                        emailContent += `<td align="right" style="vertical-align: baseline;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>`;
                                    if (emailBuilder.footer.content.social.facebook) {
                                    emailContent += `<td style="padding-left: 24px;">
                                        <a href="${emailBuilder.footer.content.social.facebook}" style="text-decoration: none;"><img src="${props.mediaurl}assets/images/facebook-logo.svg" alt="Facebook"></a>
                                    </td>`;
                                    }
                                    if (emailBuilder.footer.content.social.x) {
                                    emailContent += `<td style="padding-left: 24px;">
                                        <a href="${emailBuilder.footer.content.social.x}" style="text-decoration: none;"><img src="${props.mediaurl}assets/images/twitter-x-logo.svg" alt="twitter"></a>
                                    </td>`;
                                    }
                                    if (emailBuilder.footer.content.social.youtube) {
                                    emailContent += `<td style="padding-left: 24px;">
                                        <a href="${emailBuilder.footer.content.social.youtube}" style="text-decoration: none;"><img src="${props.mediaurl}assets/images/youtube-logo.svg" alt="youtube"></a>
                                    </td>`;
                                    }
                        emailContent += `</tr>
                            </table>
                        </td>`;
                        }
                emailContent += `</tr>
                </table>
            </td>
        </tr>`;
    }

    return `<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" bgcolor="#FFFFFF" >${emailContent}</table>`;
});

// Utility Functions
const getIcon = (key) => {
    const icons = {
        data_time: 'calendar-days.svg',
        host: 'user.svg',
        about: 'Meeting.svg',
        description: 'file-text.svg',
        location: 'Location.svg',
        name: 'user.svg',
        email: 'mail.svg',
        phone: 'phone.svg',
    };
    return `<img src="${props.mediaurl}assets/images/${icons[key] || 'default.svg'}" alt="${key}" style="float: left;margin-right: 8px;">`;
};

const formatLabel = (key) => {
    const labels = {
        data_time: 'Date & Time:',
        host: 'Host:',
        about: 'About:',
        description: 'Description:',
        location: 'Location:',
        name: 'Name:',
        email: 'Email:',
        phone: 'Phone:',
    };
    return labels[key] || key;
};

watch(emailTemplate, (newTemplate) => {
  if (Notification[route.params.type] && Notification[route.params.type][route.params.id]) {
    Notification[route.params.type][route.params.id].body = newTemplate;
    Notification[route.params.type][route.params.id].builder = emailBuilder;
  }
});


const preloader = ref(false);
const fetchNotification = async () => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        });
        if (response.data.status) { 
            // console.log(response.data.integration_settings);
            Notification.host = response.data.notification_settings.host ? response.data.notification_settings.host : Notification.host; 
            Notification.attendee = response.data.notification_settings.attendee ? response.data.notification_settings.attendee : Notification.attendee;
            
            if(response.data.notification_settings[route.params.type][route.params.id].builder==''){
                Notification[route.params.type][route.params.id].builder = emailBuilder;
            }else{
                // console.log(response.data.notification_settings[route.params.type][route.params.id].builder);

                Object.assign(emailBuilder, response.data.notification_settings[route.params.type][route.params.id].builder);
            }
            
            skeleton.value = false;
        }
    } catch (error) {
        console.log(error);
    } 
}

const UpdateNotification = async () => {   
    preloader.value = true;
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification/update', Notification, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            } 
        } );

        if (response.data.status) {    
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 

            preloader.value = false;
            
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });

            preloader.value = false;
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}

onBeforeMount(() => {  
    fetchNotification();
});

</script>

<template>
    <!-- {{ Notification[route.params.type][route.params.id] }} -->
    <!-- Single Notification  -->
    <div class="tfhb-notification-single tfhb-email-builder tfhb-flexbox tfhb-justify-between tfhb-flexbox-nowrap">
        <div class="tfhb-builder-tools">

            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('header')" :width="20"/> 
                        {{ $tfhb_trans('Header') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.header.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.header && emailBuilder.header.status">
                    <Editor 
                        v-model="emailBuilder.header.content"  
                        :placeholder="$tfhb_trans('Mail Body')"    
                        editorStyle="height: 180px" 
                    />
                </div>
            </div>

            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('gratitude')" :width="20"/> 
                        {{ $tfhb_trans('Gratitude') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.gratitude.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.gratitude && emailBuilder.gratitude.status">
                    <Editor 
                        v-model="emailBuilder.gratitude.content"  
                        :placeholder="$tfhb_trans('Mail Body')"    
                        editorStyle="height: 180px" 
                    />
                </div>
            </div>

            <!-- Meeting Details Section -->
            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('meeting_details')" :width="20"/> 
                        {{ $tfhb_trans('Meeting Details') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.meeting_details.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.meeting_details.main && emailBuilder.meeting_details.status">
                    
                    <!-- Nested: Date & Time -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('meeting_details', 'data_time')" :width="20"/> 
                                {{ $tfhb_trans('Date & Time:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.meeting_details.content.data_time.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.meeting_details.data_time && emailBuilder.meeting_details.content.data_time.status">
                            <Editor 
                                v-model="emailBuilder.meeting_details.content.data_time.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('meeting_details', 'host')" :width="20"/> 
                                {{ $tfhb_trans('Host:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.meeting_details.content.host.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.meeting_details.host && emailBuilder.meeting_details.content.host.status">
                            <Editor 
                                v-model="emailBuilder.meeting_details.content.host.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('meeting_details', 'about')" :width="20"/> 
                                {{ $tfhb_trans('About:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.meeting_details.content.about.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.meeting_details.about && emailBuilder.meeting_details.content.about.status">
                            <Editor 
                                v-model="emailBuilder.meeting_details.content.about.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('meeting_details', 'description')" :width="20"/> 
                                {{ $tfhb_trans('Description:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.meeting_details.content.description.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.meeting_details.description && emailBuilder.meeting_details.content.description.status">
                            <Editor 
                                v-model="emailBuilder.meeting_details.content.description.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('meeting_details', 'location')" :width="20"/> 
                                {{ $tfhb_trans('Location:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.meeting_details.content.location.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.meeting_details.location && emailBuilder.meeting_details.content.location.status">
                            <Editor 
                                v-model="emailBuilder.meeting_details.content.location.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- Host Details Section -->
            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('host_details')" :width="20"/> 
                        {{ $tfhb_trans('Host Details') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.host_details.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.host_details.main && emailBuilder.host_details.status">
                    
                    <!-- Name -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('host_details', 'name')" :width="20"/> 
                                {{ $tfhb_trans('Name:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.host_details.content.name.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.host_details.name && emailBuilder.host_details.content.name.status">
                            <Editor 
                                v-model="emailBuilder.host_details.content.name.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('host_details', 'email')" :width="20"/> 
                                {{ $tfhb_trans('Email:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.host_details.content.email.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.host_details.email && emailBuilder.host_details.content.email.status">
                            <Editor 
                                v-model="emailBuilder.host_details.content.email.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('host_details', 'phone')" :width="20"/> 
                                {{ $tfhb_trans('Phone:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.host_details.content.phone.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.host_details.phone && emailBuilder.host_details.content.phone.status">
                            <Editor 
                                v-model="emailBuilder.host_details.content.phone.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('instructions')" :width="20"/> 
                        {{ $tfhb_trans('Instructions') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.instructions.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.instructions && emailBuilder.instructions.status">
                    <Editor 
                        v-model="emailBuilder.instructions.content"  
                        :placeholder="$tfhb_trans('Mail Body')"    
                        editorStyle="height: 180px" 
                    />
                </div>
            </div>

            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('cancel_reschedule')" :width="20"/> 
                        {{ $tfhb_trans('Cancel & Reschedule') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.cancel_reschedule.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.cancel_reschedule.main && emailBuilder.cancel_reschedule.status">
                    
                    <!-- Description -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('cancel_reschedule', 'description')" :width="20"/> 
                                {{ $tfhb_trans('Heading:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.cancel_reschedule.content.description.status" />
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
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('cancel_reschedule', 'cancel')" :width="20"/> 
                                {{ $tfhb_trans('Cancel URL:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.cancel_reschedule.content.cancel.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.cancel_reschedule.cancel && emailBuilder.cancel_reschedule.content.cancel.status">
                            <HbText 
                                v-model="emailBuilder.cancel_reschedule.content.cancel.content"  
                                :placeholder="$tfhb_trans('Cancel URL:')"    
                            />
                        </div>
                    </div>

                    <!-- Reschedule -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('cancel_reschedule', 'reschedule')" :width="20"/> 
                                {{ $tfhb_trans('Reschedule URL:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.cancel_reschedule.content.reschedule.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.cancel_reschedule.reschedule && emailBuilder.cancel_reschedule.content.reschedule.status">
                            <HbText 
                                v-model="emailBuilder.cancel_reschedule.content.reschedule.content"  
                                :placeholder="$tfhb_trans('Reschedule URL:')"    
                            />
                        </div>
                    </div>

                </div>
            </div>

            <div class="single-tools">
                <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                    <div class="tfhb-flexbox">
                        <Icon name="Menu" @click="ContentBox('footer')" :width="20"/> 
                        {{ $tfhb_trans('Footer') }}
                    </div>
                    <HbSwitch v-model="emailBuilder.footer.status" />
                </div>
                <div class="tools-content" v-show="contentVisibility.footer.main && emailBuilder.footer.status">
                    
                    <!-- Description -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('footer', 'description')" :width="20"/> 
                                {{ $tfhb_trans('Quick Content:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.footer.content.description.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="contentVisibility.footer.description && emailBuilder.footer.content.description.status">
                            <Editor 
                                v-model="emailBuilder.footer.content.description.content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 100px" 
                            />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="single-tools">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between">
                            <div class="tfhb-flexbox">
                                <Icon name="Menu" @click="ContentBox('footer', 'social')" :width="20"/> 
                                {{ $tfhb_trans('Social:') }}
                            </div>
                            <HbSwitch v-model="emailBuilder.footer.content.social.status" />
                        </div>
                        <div class="tools-content" 
                            v-show="emailBuilder.footer.content.social && emailBuilder.footer.content.social.status">
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

        </div>

        <div class="tfhb-email-preview-wrap  tfhb-flexbox"> 
            
            <!-- Time format -->
            <HbDropdown 
                    
                v-model="Notification[$route.params.type][$route.params.id].template"  
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
                v-model="Notification[$route.params.type][$route.params.id].from"   
                type="email"
                width="50"
                :label="$tfhb_trans('From')"  
                selected = "1"
                :placeholder="$tfhb_trans('Enter From Email')"  
            /> 

            <HbText  
                v-model="Notification[$route.params.type][$route.params.id].subject"  
                required= "true"  
                :label="$tfhb_trans('Subject')"  
                selected = "1"
                type = "text"
                :placeholder="$tfhb_trans('Enter Mail Subject')"  
            /> 
            <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8"> 
                <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyShortcode(value)" >{{ value}}</span>
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
        <div class="email-preview" v-html="emailTemplate"></div>
    </div>
    <!-- Single Integrations  -->

 
</template>