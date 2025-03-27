<script setup> 
import { __ } from '@wordpress/i18n'; 
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount, computed, watch, nextTick } from 'vue';
import { useRoute, useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 
import draggable from 'vuedraggable';

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
    '{{host.phone}}',
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

const TfhbOnFocus = (event) => {
    nextTick(() => {
        // Hide all shortcode boxes first
        document.querySelectorAll(".tfhb-mail-shortcode").forEach((el) => {
            el.style.display = "none";
        });

        // Find the nearest shortcode box and show it
        const parentBox = event.target.closest(".tfhb-shortcode-box");
        if (parentBox) {
            const shortcodeBox = parentBox.querySelector(".tfhb-mail-shortcode");
            if (shortcodeBox) {
                shortcodeBox.style.display = "flex"; // Show the shortcode box
            }
        }
    });
}

const emailBuilder = reactive({ 
    header: {
        order: 0,
        status: 1,
        title: 'Header',
        content: '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>'
    },
    gratitude: {
        order: 1,
        status: 1,
        title: 'Greetings',
        content: '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with Host Name was confirmed.</p>',
    },
    meeting_details: {
        order: 2,
        status: 1,
        title: 'Meeting Details',
        content: {
            data_time: {
                status: 1,
                title: 'Date & Time:',
                content: '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
            },
            host: {
                status: 1,
                title: 'Host:',
                content: '<strong>{{host.name}}</strong>'
            },
            about: {
                status: 1,
                title: 'About:',
                content: '<strong>{{meeting.title}}</strong>'
            },
            description: {
                status: 1,
                title: 'Description:',
                content: '{{meeting.content}}'
            },
            location: {
                status: 1,
                title: 'Location:',
                content: '<strong>{{booking.location_details_html}}</strong>'
            }
        },
    },
    host_details: {
        order: 3,
        status: 1,
        title: 'Host Details',
        content: {
            name: {
                status: 1,
                title: 'Name:',
                content: '<strong>{{host.name}}</strong>'
            },
            email: {
                status: 1,
                title: 'Email:',
                content: '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
            },
            phone: {
                status: 1,
                title: 'Phone:',
                content: '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
            },
        }
    },
    instructions: {
        order: 4,
        status: 1,
        title: 'Instructions',
        content: '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
    },
    cancel_reschedule: {
        order: 5,
        status: 1,
        title: 'Buttons',
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
        order: 6,
        status: 1,
        title: 'Footer',
        content: {
            description: {
                status: 1,
                content: '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
            },
            social: {
                status: 1,
                data: [
                    {
                        title: 'Facebook',
                        url: '#'
                    },
                    {
                        title: 'Twitter',
                        url: '#'
                    },
                    {
                        title: 'Youtube',
                        url: '#'
                    },
                ]
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
    if (!contentVisibility.hasOwnProperty(key)) return; // Ensure the key exists

    if (subKey) {
        if (emailBuilder[key]?.content?.[subKey]?.status) {
            // Ensure parent stays open and toggle sub-item
            if (typeof contentVisibility[key] === "object") {
                contentVisibility[key].main = true;
                contentVisibility[key][subKey] = !contentVisibility[key][subKey];
            }
        }
    } else {
        // Close all other parents first
        Object.keys(contentVisibility).forEach(parentKey => {
            if (parentKey !== key) {
                if (typeof contentVisibility[parentKey] === "object") {
                    contentVisibility[parentKey].main = false;
                    Object.keys(contentVisibility[parentKey]).forEach(sub => {
                        if (sub !== "main") contentVisibility[parentKey][sub] = false;
                    });
                } else {
                    contentVisibility[parentKey] = false;
                }
            }
        });

        // Toggle the clicked section
        if (typeof contentVisibility[key] === "boolean") {
            contentVisibility[key] = !contentVisibility[key];
        } else if (typeof contentVisibility[key] === "object") {
            contentVisibility[key].main = !contentVisibility[key].main;
        }
    }
};


// Dragging state
let draggedKey = null;

// Start dragging function
const dragStart = (key) => {
  draggedKey = key;
};

// Drop function to reorder
const drop = (key) => {
  if (draggedKey !== null) {
    const temp = emailBuilder[draggedKey].order;
    emailBuilder[draggedKey].order = emailBuilder[key].order;
    emailBuilder[key].order = temp;
  }
  draggedKey = null;
};

// Computed sorted
const sortedEmailBuilder = computed(() => {
  return Object.entries(emailBuilder)
    .sort((a, b) => a[1].order - b[1].order)
    .reduce((acc, [key, value]) => {
      acc[key] = value;
      return acc;
    }, {});
});

// Computed Property to Generate Email Preview
const emailTemplate = computed(() => {
    let emailContent = '';

    Object.keys(sortedEmailBuilder.value).forEach(key => {
        const section = sortedEmailBuilder.value[key];
        if (section.status && key === 'header') {
            emailContent += `<tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
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

        if (section.status && key === 'gratitude') {
            emailContent += `<tr><td style="padding: 32px 32px 0 32px;">${emailBuilder.gratitude.content}</td></tr>`;
        }

        if (section.status && key === 'meeting_details') {
            emailContent += `
                <tr>
                    <td style="padding: 0 32px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; margin-top: 32px">
                            <tr><td style="font-weight: bold; font-size: 16px;">${emailBuilder.meeting_details.title}</td></tr>
            `;

            Object.keys(emailBuilder.meeting_details.content).forEach(key => {
                if (emailBuilder.meeting_details.content[key].status) {
                    emailContent += `
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            ${getIcon(key)}
                                            ${formatLabel(key)}
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            ${emailBuilder.meeting_details.content[key].content}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    `;
                }
            });

            emailContent += `</table></td></tr>`;
        }

        if (section.status && key === 'host_details') {
            emailContent += `
                <tr>
                    <td style="padding: 32px 32px 0 32px">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px;">
                            <tr><td style="font-weight: bold; font-size: 16px;">${emailBuilder.host_details.title}</td></tr>
            `;

            Object.keys(emailBuilder.host_details.content).forEach(key => {
                if (emailBuilder.host_details.content[key].status) {
                    emailContent += `
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            ${getIcon(key)}
                                            ${formatLabel(key)}
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            ${emailBuilder.host_details.content[key].content}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    `;
                }
            });

            emailContent += `</table></td></tr>`;
        }

        if (section.status && key === 'instructions') {
            emailContent += `
                <tr>
                    <td style="font-weight: bold; font-size: 17px; padding: 32px 32px 24px 32px;">${emailBuilder.instructions.title}</td>
                </tr>
                <tr>
                    <td style="font-size: 15px; padding: 0 32px 0 32px;">${emailBuilder.instructions.content}</td>
                </tr>`;
        }
        if (section.status && key === 'cancel_reschedule') {
            emailContent += ` <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 32px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">`;
                if (emailBuilder.cancel_reschedule.content.description.content) {
                    emailContent += ` <tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">${emailBuilder.cancel_reschedule.content.description.content}</td>
                    </tr>`;
                }
                if (emailBuilder.cancel_reschedule.content.cancel.content || emailBuilder.cancel_reschedule.content.reschedule.content) {
                    emailContent += `<tr>
                    <td style="font-size: 15px; padding-bottom: 24px;">`;
                        if (emailBuilder.cancel_reschedule.content.cancel.content){
                            emailContent += `<a href="${emailBuilder.cancel_reschedule.content.cancel.content}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a>`;
                        }
                        if (emailBuilder.cancel_reschedule.content.reschedule.content){
                            emailContent += `<a href="${emailBuilder.cancel_reschedule.content.reschedule.content}" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a>`;
                        }
                    emailContent += `</td></tr>`;
                }
            emailContent += `</table></td></tr></table>`;
        }

        if (section.status && key === 'footer') {
            emailContent += `<tr>
                <td align="center">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tr>`;
                            if (emailBuilder.footer.content.description.content) {
                            emailContent += `<td align="left">
                                ${emailBuilder.footer.content.description.content}
                            </td>`;
                            }
                            if (emailBuilder.footer.content.social) {
                            emailContent += `<td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">`;
                                    emailBuilder.footer.content.social.data.forEach(social => {
                                    if (social.url && social.title) {
                                        emailContent += `<tr><td style="padding-bottom: 4px;">
                                            <a href="${social.url}" style="text-decoration: none; color: #FFF;">
                                                ${social.title}
                                            </a>
                                        </td></tr>`;
                                    }
                                });
                            emailContent += `
                                </table>
                            </td>`;
                            }
                    emailContent += `</tr>
                    </table>
                </td>
            </tr>`;
        }
    });

    return `<table role="presentation" cellspacing="0" cellpadding="0" border="0" max-width="600" bgcolor="#FFFFFF" style="width: 100%; max-width: 600px; margin: 0 auto;">${emailContent}</table>`;
});

// Utility Functions
const getIcon = (key) => {
    const icons = {
        data_time: 'calendar-days.png',
        host: 'user.png',
        about: 'Meeting.png',
        description: 'file-text.png',
        location: 'Location.png',
        name: 'user.png',
        email: 'mail.png',
        phone: 'phone.png',
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

// Remove Social
const removeSocial = (key) => {
    emailBuilder.footer.content.social.data.splice(key, 1);
}
// Add new Social
const addSocial = (key) => {
    emailBuilder.footer.content.social.data.push({
        title: '',
        url: '',
    });
}

</script>

<template>
    <!-- {{ Notification[route.params.type][route.params.id] }} -->
    <!-- Single Notification  -->
    <div class="tfhb-notification-single tfhb-email-builder tfhb-flexbox tfhb-justify-between tfhb-flexbox-nowrap">
        <div class="tfhb-builder-tools">
            
            <div class="tfhb-template-info tfhb-flexbox tfhb-gap-16 tfhb-mb-32">
                
                <HbText  
                    v-model="Notification[$route.params.type][$route.params.id].from"   
                    type="email"
                    :label="$tfhb_trans('From')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter From Email')"  
                /> 
                <div class="tfhb-shortcode-box tfhb-full-width">
                    <HbText  
                        v-model="Notification[$route.params.type][$route.params.id].subject"  
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
            
            <div class="single-tools" v-for="(email, key) in sortedEmailBuilder" :key="key"
            :draggable="key!='header' && key!='footer' ? true : ''"
            @dragstart="dragStart(key)"
            @dragover.prevent
            @drop="drop(key)"
            >
                <!-- Dynamic Heading -->
                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                    <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox(key)">
                        <Icon name="GripVertical" :width="20"/> 
                        {{ email.title }} 
                    </div>
                    <HbSwitch v-model="emailBuilder[key].status" />
                </div>

                <!-- Dynamic Content header/gratitude/instructions -->
                <div class="tools-content" v-show="contentVisibility[key] && emailBuilder[key].status" v-if="key === 'header' || key === 'gratitude' || key === 'instructions'">
                    <div class="tfhb-shortcode-box tfhb-full-width">
                        <div @click="TfhbOnFocus">
                            <Editor 
                                v-model="emailBuilder[key].content"  
                                :placeholder="$tfhb_trans('Mail Body')"    
                                editorStyle="height: 180px" 
                            />
                        </div>
                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                            <span  class="tfhb-mail-shortcode-badge" v-for="(value, shortcodeKey) in meetingShortcode" :key="shortcodeKey" @click="copyShortcode(value)">
                                {{ value }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Content meeting_details/host_details -->
                <div class="tools-content" v-show="contentVisibility[key].main && emailBuilder[key].status" v-if="key === 'meeting_details' || key === 'host_details'">
                    <HbText 
                        v-model="emailBuilder[key].title"  
                        :placeholder="$tfhb_trans('Heading')"    
                    />
                    <div class="single-tools" v-for="(section, subKey) in emailBuilder[key].content" :key="subKey">
                        <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                            <Icon name="GripVertical" @click="ContentBox(key, subKey)" :width="20"/> 
                            <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox(key, subKey)">
                                <div class="tfhb-flexbox tfhb-head">
                                    {{ emailBuilder[key].content[subKey].title }}
                                </div>
                                <HbSwitch v-model="emailBuilder[key].content[subKey].status" />
                            </div>
                        </div>

                        <div class="tools-content" v-show="contentVisibility[key][subKey] && emailBuilder[key].content[subKey].status">
                            <div class="tfhb-shortcode-box tfhb-full-width">
                                <div @click="TfhbOnFocus">
                                    <Editor 
                                        v-model="emailBuilder[key].content[subKey].content"  
                                        :placeholder="$tfhb_trans('Mail Body')"    
                                        editorStyle="height: 100px" 
                                    />
                                </div>
                                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                    <span class="tfhb-mail-shortcode-badge" v-for="(value, shortcodeKey) in meetingShortcode" :key="shortcodeKey" @click="copyShortcode(value)">
                                        {{ value }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <!-- Dynamic Content cancel_reschedule -->
                <div class="tools-content" v-show="contentVisibility[key].main && emailBuilder[key].status" v-if="key === 'cancel_reschedule'">
                    
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

                <!-- Dynamic Content Footer -->
                <div class="tools-content" v-show="contentVisibility[key].main && emailBuilder[key].status" v-if="key === 'footer'">
                    
                    <!-- Description -->
                    <div class="single-tools">
                        <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                            <Icon name="GripVertical" @click="ContentBox(key, 'description')" :width="20"/> 
                            <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox(key, 'description')">
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

                    <!-- Socail -->
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
                            <div class="tfhb-socail-repeater tfhb-flexbox tfhb-gap-8">
                                <div v-for="(social, skey) in emailBuilder.footer.content.social.data" :key="skey" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-between">
                                    <HbText 
                                        v-model="social.title"
                                        :placeholder="$tfhb_trans('Social Title')"  
                                        width="45"  
                                    />
                                    <HbText 
                                        v-model="social.url"
                                        :placeholder="$tfhb_trans('Social URL:')"   
                                        width="45"   
                                    />
                                    <div v-if="skey == 0" class="tfhb-availability-schedule-clone-single">
                                        <button class="tfhb-availability-schedule-btn" @click="addSocial()"><Icon name="Plus" size=20 /> </button> 
                                    </div>
                                    <div v-else class="tfhb-availability-schedule-clone-single">
                                        <button class="tfhb-availability-schedule-btn" @click="removeSocial(skey)"><Icon name="X" size=20 /> </button> 
                                    </div>
                                </div>
                            </div>
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
        <div class="tfhb-email-preview" v-html="emailTemplate"></div>
    </div>
    <!-- Single Integrations  -->

 
</template>