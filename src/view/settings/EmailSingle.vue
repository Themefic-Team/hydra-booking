<script setup> 
import { __ } from '@wordpress/i18n'; 
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount, computed, watch, nextTick } from 'vue';
import { useRoute} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify"; 
import { useDragAndDrop } from "vue-fluid-dnd";

// import Form Field 
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbText from '@/components/form-fields/HbText.vue' 
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbButton from '@/components/form-fields/HbButton.vue'
import Editor from 'primevue/editor';
import HbFileUpload from '@/components/form-fields/HbFileUpload.vue'; 
import HbColor from '@/components/form-fields/HbColor.vue'; 
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
    
    },
    telegram : {
        booking_confirmation: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            body : '',
            builder: ''
        }
    },
    twilio : {
        booking_confirmation: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
            body : '',
            builder: ''
        },
    },
    slack : {
        booking_confirmation: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_cancel: {
            status : 0,
            body : '',
            builder: ''
        },
        booking_reschedule: {
            status : 0,
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

const emailBuilder = ref([
    {
        id: 'header',
        order: 0,
        status: 1,
        title: 'Header',
        content: '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
        logo: '',
        background: '#215732'
    },
    {
        id: 'gratitude',
        order: 1,
        status: 1,
        title: 'Greetings',
        content: '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with {{host.name}} was confirmed.</p>',
    },
    {
        id: 'meeting_details',
        order: 2,
        status: 1,
        title: 'Meeting Details',
        border_color: '#C0D8C4',
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
    {
        id: 'host_details',
        order: 3,
        status: 1,
        title: 'Host Details',
        border_color: '#C0D8C4',
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
    {
        id: 'instructions',
        order: 4,
        status: 1,
        title: 'Instructions',
        content: '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
    },
    {
        id: 'cancel_reschedule',
        order: 5,
        status: 1,
        title: 'Buttons',
        border_color: '#C0D8C4',
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
    {
        id: 'add_to_calendar',
        order: 5,
        status: 1,
        title: 'Add to Calendar',
        border_color: '#C0D8C4',
        content: {
            description: {
                status: 1,
                content: 'Add to calendar'
            },
            list: {
                status: 1,
                content: '<div><a style=\'margin-right: 8px;\' href=\'{{booking.add_to_calendar.google}}\' target=\'_blank\'><img src=\'${props.mediaurl}/assets/app/images/google-calendar.svg\' alt=\'icon\' /></a><a style=\'margin-right: 8px;\' href=\'{{booking.add_to_calendar.outlook}}\' target=\'_blank\'><img src=\'${props.mediaurl}/assets/app/images/outlook-calendar.svg\' alt=\'icon\' /></a><a style=\'margin-right: 8px;\' href=\'{{booking.add_to_calendar.yahoo}}\' target=\'_blank\'><img src=\'${props.mediaurl}/assets/app/images/yahoo-calendar.svg\' alt=\'icon\' /></a><a style=\'margin-right: 8px;\' href=\'{{booking.add_to_calendar.other}}\' target=\'_blank\'><img src=\'${props.mediaurl}/assets/app/images/other-calendar.svg\' alt=\'icon\' /></a></div>'
                
            }, 
        }
    },
    {
        id: 'footer',
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
]);

const handlerSelector = ".tfhb-icon-drag";
const { parent } = useDragAndDrop(emailBuilder,{  
    handlerSelector
});

const copyBodyShortcode = (value) => {
  const current = Notification[route.params.type][route.params.id].body || '';
  Notification[route.params.type][route.params.id].body = current + ' ' + value;
};

const copySubjectShortcode = (value) => {
  const current = Notification[route.params.type][route.params.id].subject || '';
  Notification[route.params.type][route.params.id].subject = current + ' ' + value;
};

const copyShortcode = (value, key) => {
  const current = emailBuilder.value[key].content || '';
  emailBuilder.value[key].content = current + ' ' + value;
};

const copySubShortcode = (value, key, subKey) => {
    const current = emailBuilder.value[key].content[subKey].content || '';
    emailBuilder.value[key].content[subKey].content = current + ' ' + value;
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

const ContentBox = (key, subKey = null, index = null) => {
    if (!contentVisibility.hasOwnProperty(key)) return; // Ensure the key exists

    if (subKey) {
        if (emailBuilder.value[index]?.content?.[subKey]?.status) {
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

// Computed sorted
const sortedEmailBuilder = computed(() => {
  return Object.entries(emailBuilder.value)
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
        if (section.status && section.id === 'header') {
            emailContent += `<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tr>
                <td bgcolor="${emailBuilder.value[key].background}" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tr>`;
                            if (emailBuilder.value[key].logo) {
                                 emailContent += `<td style="vertical-align: middle; width: 36px; padding-right: 8px">
                                    <img src="${emailBuilder.value[key].logo}" alt="HydraBooking"  style="max-height: 36px;display: block;">
                                </td>`;
                            }
                            if (emailBuilder.value[key].content) {
                                emailContent += `<td style="vertical-align: middle;">
                                    ${emailBuilder.value[key].content}
                                </td>`;
                            }
                    emailContent += `</tr>
                    </table>
                </td>
            </tr></table>`;
        }

        if (section.status && section.id === 'gratitude') {
            emailContent += `
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tr><td>${emailBuilder.value[key].content}</td></tr> </table></td></tr></table>`;
        }

        if (section.status && section.id === 'meeting_details') {
            emailContent += `
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed ${emailBuilder.
                        
                        value[key].border_color}; border-radius: 8px; padding: 24px; background: #fff;">
                            <tr><td style="font-weight: bold; font-size: 16px;">${emailBuilder.
                                
                                value[key].title}</td></tr>
            `;

            Object.keys(emailBuilder.
            
            value[key].content).forEach(tkey => {
                if (emailBuilder.value[key].content[tkey].status) {
                    emailContent += `
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            ${getIcon(tkey)}
                                            ${formatLabel(tkey)}
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            ${emailBuilder.value[key].content[tkey].content}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    `;
                }
            });

            emailContent += `</table></td></tr> </table></td></tr></table>`;
        }

        if (section.status && section.id === 'host_details') {
            emailContent += `
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed ${emailBuilder.value[key].border_color}; border-radius: 8px; padding: 24px; background: #fff;">
                            <tr><td style="font-weight: bold; font-size: 16px;">${emailBuilder.value[key].title}</td></tr>
            `;

            Object.keys(emailBuilder.value[key].content).forEach(tkey => {
                if (emailBuilder.value[key].content[tkey].status) {
                    emailContent += `
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            ${getIcon(tkey)}
                                            ${formatLabel(tkey)}
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            ${emailBuilder.value[key].content[tkey].content}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    `;
                }
            });

            emailContent += `</table></td></tr> </table></td></tr></table>`;
        }

        if (section.status && section.id === 'instructions') {

            emailContent += ` <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">`;

            if (emailBuilder.value[key].title) {
                emailContent += `
                <tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">${emailBuilder.value[key].title}</td>
                </tr>`;
            }
            if (emailBuilder.value[key].content) {
                emailContent += `
                <tr>
                    <td style="font-size: 15px;">${emailBuilder.value[key].content}</td>
                </tr>`;
            }

            emailContent += `</table></td></tr></table>`;
            
        }
        if (section.status && section.id === 'cancel_reschedule') {
            emailContent += ` <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed ${emailBuilder.value[key].border_color};border-bottom: 1px dashed ${emailBuilder.value[key].border_color}; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">`;
                if (emailBuilder.value[key].content.description.content && emailBuilder.value[key].content.description.status) {
                    emailContent += ` <tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">${emailBuilder.value[key].content.description.content}</td>
                    </tr>`;
                }
                if (emailBuilder.value[key].content.cancel.content || emailBuilder.value[key].content.reschedule.content) {
                    emailContent += `<tr>
                    <td style="font-size: 15px; padding-bottom: 24px;">`;
                        if (emailBuilder.value[key].content.cancel.content && emailBuilder.value[key].content.cancel.status){
                            emailContent += `<a href="${emailBuilder.value[key].content.cancel.content}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid ${emailBuilder.value[key].border_color};background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a>`;
                        }
                        if (emailBuilder.value[key].content.reschedule.content && emailBuilder.value[key].content.reschedule.status){
                            emailContent += `<a href="${emailBuilder.value[key].content.reschedule.content}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid ${emailBuilder.value[key].border_color};background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a>`;
                        }
                    emailContent += `</td></tr>`;
                }
            emailContent += `</table></td></tr></table>`;
        }
        
        if (section.status && section.id === 'add_to_calendar') { 
            emailContent += `<table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding-bottom: 16px;width: 100%; max-width: 600px; margin: 0 auto;"  ><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-bottom: 1px dashed ${emailBuilder.value[key].border_color}; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">`;
            emailContent += ` <tr>
                <td style="font-size: 15px;padding: 8px 0 12px 0; text-align: center;">${emailBuilder.value[key].content.description.content}</td>  
            </tr>`;
            emailContent += ` <tr>
                <td style="font-size: 15px;padding: 8px 0 8px 0; text-align: center;">${emailBuilder.value[key].content.list.content}</td>  
            </tr>`;
            emailContent += `</table></td></tr></table>`;
        }

        if (section.status && section.id === 'footer') {
            emailContent += `<tr>
                <td align="center">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tr>`;
                            if (emailBuilder.value[key].content.description.content && emailBuilder.value[key].content.description.status) {
                            emailContent += `<td align="left">
                                ${emailBuilder.value[key].content.description.content}
                            </td>`;
                            }
                            if (emailBuilder.value[key].content.social && emailBuilder.value[key].content.social.status) {
                            emailContent += `<td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">`;
                                    emailBuilder.value[key].content.social.data.forEach(social => {
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

    return `${emailContent}`;
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
    Notification[route.params.type][route.params.id].builder = emailBuilder.value;
  }
});


const preloader = ref(false);
const fetchNotification = async () => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/notification', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        });
        if (response.data.status) { 
            Notification.host = response.data.notification_settings.host ? response.data.notification_settings.host : Notification.host; 
            Notification.attendee = response.data.notification_settings.attendee ? response.data.notification_settings.attendee : Notification.attendee;
            Notification.telegram = response.data.notification_settings.telegram ? response.data.notification_settings.telegram : Notification.telegram;
            Notification.twilio = response.data.notification_settings.twilio ? response.data.notification_settings.twilio : Notification.twilio;
            Notification.slack = response.data.notification_settings.slack ? response.data.notification_settings.slack : Notification.slack;
            if(response.data.notification_settings[route.params.type][route.params.id].builder==''){
                Notification[route.params.type][route.params.id].builder = emailBuilder.value;
            }else{
                emailBuilder.value = response.data.notification_settings[route.params.type][route.params.id].builder;
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

const getFooterBlock = () => {
  return emailBuilder.value.find(block => block.id === 'footer');
};
// Remove Socail
const removeSocial = (key) => {
  const footer = getFooterBlock();
  if (footer?.content?.social?.data) {
    footer.content.social.data.splice(key, 1);
  }
};

// Add Social
const addSocial = () => {
  const footer = getFooterBlock();
  if (footer?.content?.social?.data) {
    footer.content.social.data.push({
      title: '',
      url: '',
    });
  }
};


</script>

<template>
    <!-- {{ Notification[route.params.type][route.params.id].body }} -->
    <!-- {{ route.params.type }} -->

    <!-- Single Notification  -->
    <div class="tfhb-notification-single tfhb-email-builder tfhb-flexbox tfhb-justify-between tfhb-flexbox-nowrap">
        <div class="tfhb-builder-tools" v-if="route.params.type!='telegram' && route.params.type!='twilio' && route.params.type!='slack'">

            <div class="tfhb-template-info tfhb-flexbox tfhb-gap-16 tfhb-mb-32">
                
                <HbText  
                    v-model="Notification[route.params.type][route.params.id].from"   
                    type="email"
                    :label="$tfhb_trans('From')"  
                    selected = "1"
                    :placeholder="$tfhb_trans('Enter From Email')"  
                /> 
                <div class="tfhb-shortcode-box tfhb-full-width">
                    <HbText  
                        v-model="Notification[route.params.type][route.params.id].subject"  
                        required= "true"  
                        :label="$tfhb_trans('Subject')"  
                        selected = "1"
                        type = "text"
                        :placeholder="$tfhb_trans('Enter Mail Subject')"  
                        @tfhb-onclick="TfhbOnFocus"
                    /> 
                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                        <span  class="tfhb-mail-shortcode-badge"  v-for="(value, skey) in meetingShortcode" :key="skey" @click="copySubjectShortcode(value)" >{{ value}}</span>
                    </div>
                </div>
            </div>

            <ul ref="parent" class="number-list">
                <li class="single-tools" v-for="(email, key) in emailBuilder" :index="key" 
                >
                    <!-- Dynamic Heading -->
                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                        <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="ContentBox(email.id)">
                            <div class="tfhb-icon-drag">
                                <Icon name="GripVertical" :width="20"/> 
                            </div>
                            {{ email.title }} 
                        </div>
                        <HbSwitch v-model="emailBuilder[key].status" />
                    </div>

                    <!-- Dynamic Content header/gratitude/instructions -->
                    <div class="tools-content" v-show="contentVisibility[email.id] && emailBuilder[key].status" v-if="email.id === 'header' || email.id === 'gratitude' || email.id === 'instructions'">
                        <div class="tfhb-shortcode-box tfhb-full-width">
                            <div class="tfhb-header-logo">
                                <HbFileUpload
                                    name="logo"
                                    v-model= "emailBuilder[key].logo"
                                    :label = "$tfhb_trans('Choose images or drag & drop it here.')"
                                    :subtitle = "$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                                    :btn_label = "$tfhb_trans('Upload logo')"
                                    file_size ="5"
                                    file_format ="jpg,jpeg,png"
                                    v-if="email.id === 'header'"
                                />
                            </div>
                            <div class="tfhb-header-bg">
                                <HbColor  
                                    v-model= "emailBuilder[key].background" 
                                    :label="$tfhb_trans('Header Background')"
                                    name="background"
                                    selected = "1" 
                                    v-if="email.id === 'header'"
                                />  
                            </div>
                            <div @click="TfhbOnFocus">
                                <Editor 
                                    v-model="emailBuilder[key].content"  
                                    :placeholder="$tfhb_trans('Mail Body')"    
                                    editorStyle="height: 180px" 
                                />
                            </div>
                            <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                <span  class="tfhb-mail-shortcode-badge" v-for="(value, shortcodeKey) in meetingShortcode" :key="shortcodeKey" @click="copyShortcode(value, key)">
                                    {{ value }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Content meeting_details/host_details -->
                    <div class="tools-content" v-show="contentVisibility[email.id].main && emailBuilder[key].status" v-if="email.id === 'meeting_details' || email.id === 'host_details'">
                        <HbText 
                            v-model="emailBuilder[key].title"  
                            :placeholder="$tfhb_trans('Heading')"    
                        />
                        <div class="tfhb-header-bg">
                            <HbColor  
                                v-model= "emailBuilder[key].border_color" 
                                :label="$tfhb_trans('Border Color')"
                                name="border_color"
                                selected = "1" 
                            />  
                        </div>
                        <div class="single-tools" v-for="(section, subKey) in emailBuilder[key].content" :key="subKey">
                            <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox(email.id, subKey, key)">
                                    <div class="tfhb-flexbox tfhb-head">
                                        {{ emailBuilder[key].content[subKey].title }}
                                    </div>
                                    <HbSwitch v-model="emailBuilder[key].content[subKey].status" />
                                </div>
                            </div>

                            <div class="tools-content" v-show="contentVisibility[email.id][subKey] && emailBuilder[key].content[subKey].status">
                                <div class="tfhb-shortcode-box tfhb-full-width">
                                    <div @click="TfhbOnFocus">
                                        <Editor 
                                            v-model="emailBuilder[key].content[subKey].content"  
                                            :placeholder="$tfhb_trans('Mail Body')"    
                                            editorStyle="height: 100px" 
                                        />
                                    </div>
                                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                        <span class="tfhb-mail-shortcode-badge" v-for="(value, shortcodeKey) in meetingShortcode" :key="shortcodeKey" @click="copySubShortcode(value, key, subKey)">
                                            {{ value }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <!-- Dynamic Content cancel_reschedule -->
                    <div class="tools-content" v-show="contentVisibility[email.id].main && emailBuilder[key].status" v-if="email.id === 'cancel_reschedule'">
                        
                        <div class="tfhb-header-bg">
                            <HbColor  
                                v-model= "emailBuilder[key].border_color" 
                                :label="$tfhb_trans('Border Color')"
                                name="border_color"
                                selected = "1" 
                            /> 
                        </div>
                        <!-- Description -->
                        <div class="single-tools">
                            <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('cancel_reschedule', 'description', key)">
                                    <div class="tfhb-flexbox tfhb-head">
                                        {{ $tfhb_trans('Heading:') }}
                                    </div>
                                    <HbSwitch v-model="emailBuilder[key].content.description.status" />
                                </div>
                            </div>
                            <div class="tools-content" 
                                v-show="contentVisibility[email.id].description && emailBuilder[key].content.description.status">
                                <Editor 
                                    v-model="emailBuilder[key].content.description.content"  
                                    :placeholder="$tfhb_trans('Mail Body')"    
                                    editorStyle="height: 100px" 
                                />
                            </div>
                        </div>

                        <!-- Cancel -->
                        <div class="single-tools">
                            <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('cancel_reschedule', 'cancel', key)">
                                    <div class="tfhb-flexbox tfhb-head">
                                        {{ $tfhb_trans('Cancel URL:') }}
                                    </div>
                                    <HbSwitch v-model="emailBuilder[key].content.cancel.status" />
                                </div>
                            </div>
                            <div class="tools-content" 
                                v-show="contentVisibility[email.id].cancel && emailBuilder[key].content.cancel.status">
                                <div class="tfhb-shortcode-box tfhb-full-width">
                                    <HbText 
                                        v-model="emailBuilder[key].content.cancel.content"  
                                        :placeholder="$tfhb_trans('Cancel URL:')"    
                                        @tfhb-onclick="TfhbOnFocus"
                                    />
                                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                        <span  class="tfhb-mail-shortcode-badge"  v-for="(value, skey) in meetingShortcode" :key="skey" @click="copySubShortcode(value, key, 'cancel')" >{{ value}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reschedule -->
                        <div class="single-tools">
                            <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('cancel_reschedule', 'reschedule', key)">
                                    <div class="tfhb-flexbox tfhb-head">
                                        {{ $tfhb_trans('Reschedule URL:') }}
                                    </div>
                                    <HbSwitch v-model="emailBuilder[key].content.reschedule.status" />
                                </div>
                            </div>
                            <div class="tools-content" 
                                v-show="contentVisibility[email.id].reschedule && emailBuilder[key].content.reschedule.status">
                                
                                <div class="tfhb-shortcode-box tfhb-full-width">
                                    <HbText 
                                        v-model="emailBuilder[key].content.reschedule.content"  
                                        :placeholder="$tfhb_trans('Reschedule URL:')"    
                                        @tfhb-onclick="TfhbOnFocus"
                                    />
                                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                        <span  class="tfhb-mail-shortcode-badge"  v-for="(value, skey) in meetingShortcode" :key="skey" @click="copySubShortcode(value, key, 'reschedule')" >{{ value}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Dynamic Content Footer -->
                    <div class="tools-content" v-show="contentVisibility[email.id].main && emailBuilder[key].status" v-if="email.id === 'footer'">
                        
                        <!-- Description -->
                        <div class="single-tools">
                            <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('footer', 'description', key)">
                                    <div class="tfhb-flexbox tfhb-head">
                                        {{ $tfhb_trans('Quick Content:') }}
                                    </div>
                                    <HbSwitch v-model="emailBuilder[key].content.description.status" />
                                </div>
                            </div>
                            
                            <div class="tools-content" 
                                v-show="contentVisibility[email.id].description && emailBuilder[key].content.description.status">
                                <div class="tfhb-shortcode-box tfhb-full-width">
                                    <div @click="TfhbOnFocus">
                                        <Editor 
                                            v-model="emailBuilder[key].content.description.content"  
                                            :placeholder="$tfhb_trans('Mail Body')"    
                                            editorStyle="height: 100px" 
                                        />
                                    </div>
                                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8" style="display: none;"> 
                                        <span  class="tfhb-mail-shortcode-badge"  v-for="(value, skey) in meetingShortcode" :key="skey" @click="copySubShortcode(value, key, 'description')" >{{ value}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Socail -->
                        <div class="single-tools">
                            <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8" @click="ContentBox('footer', 'social', key)">
                                    <div class="tfhb-flexbox tfhb-head">
                                        {{ $tfhb_trans('Social:') }}
                                    </div>
                                    <HbSwitch v-model="emailBuilder[key].content.social.status" />
                                </div>
                            </div>
                            <div class="tools-content" 
                                v-show="contentVisibility[email.id].social && emailBuilder[key].content.social.status">
                                <div class="tfhb-socail-repeater tfhb-flexbox tfhb-gap-8">
                                    <div v-for="(social, skey) in emailBuilder[key].content.social.data" :key="skey" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-between tfhb-full-width">
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

                </li>
            </ul>
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
        <div class="tfhb-email-preview" v-html="emailTemplate" v-if="route.params.type!='telegram' && route.params.type!='twilio' && route.params.type!='slack'"></div>

        <div class="tfhb-email-body" v-if="route.params.type=='telegram' || route.params.type=='twilio' || route.params.type=='slack'">
            <Editor 
                v-model="Notification[route.params.type][route.params.id].body"  
                :placeholder="$tfhb_trans('Mail Body')"    
                editorStyle="height: 280px" 
            />
            <HbButton  
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-mt-24" 
                @click="UpdateNotification"
                :buttonText="$tfhb_trans('Update')" 
                icon="ChevronRight"
                hover_icon="ArrowRight"
                :hover_animation="true"
                :pre_loader="preloader"
            /> 
        </div>

        <div class="tfhb-email-shortcodes-list" v-if="route.params.type=='telegram' || route.params.type=='twilio' || route.params.type=='slack'">
            <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8"> 
                <span  class="tfhb-mail-shortcode-badge"  v-for="(value, key) in meetingShortcode" :key="key" @click="copyBodyShortcode(value)" >{{ value}}</span>
            </div>
        </div>
    </div>
    <!-- Single Integrations  -->

 
</template>