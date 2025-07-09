
import { createRouter, createWebHashHistory } from 'vue-router';
import Dashboard from '../view/dashboard/Dashboard.vue';  
import Booking from '../view/booking/booking.vue'; 
import Settings from '../view/settings/Settings.vue';
import setupWizard from '../view/setup-wizard/setup-wizard.vue';
import Hosts from '../view/hosts/hosts.vue';
import Meetings from '../view/meetings/meetings.vue';
import { AuthData } from '@/store/auth';

// Event 
const user = tfhb_core_apps.user || '';
const user_id = user.id || '';
const host_id = user.host_id || ''; 
const user_role = user.role[0] || '';

const routes = [
    // Define your routes here
    // For example:
    {
        path: '/',
        name: 'dashboard', 
        meta: { Capabilities: 'tfhb_manage_dashboard' },
        component: Dashboard
    },  
    {
        path: '/bookings',
        name: 'booking', 
        meta: { Capabilities: 'tfhb_manage_booking' },
        component: Booking, 
        redirect: { name: 'BookingLists' },
        children: [ 
            {
                path: 'list',
                name: 'BookingLists',
                meta: { Capabilities: 'tfhb_manage_booking' },
                component: () => import('../view/booking/booking-list.vue')
            }, 
            {
                path: 'create-new',
                name: 'BookingCreate',
                meta: { Capabilities: 'tfhb_manage_booking' },
                component: () => import('../view/booking/booking-create.vue')
            },
            {
                path: 'update/:id',
                name: 'bookingUpdate',
                meta: { Capabilities: 'tfhb_manage_booking' },
                component: () => import('../view/booking/booking-update.vue')
            }, 
            {
                path: 'details/:id',
                name: 'bookingDetails',
                meta: { Capabilities: 'tfhb_manage_booking' },
                component: () => import('../view/booking/booking-details.vue')
            }, 
        ]
    }, 
    // Hosts routes
    {
        path: '/hosts',
        name: 'hosts',
        component: Hosts,
        meta: { Capabilities: 'tfhb_manage_hosts' },
        redirect: { name: 'HostsLists' },
        children: [ 
            {
                path: 'list',
                name: 'HostsLists',
                meta: { Capabilities: 'tfhb_manage_hosts' },
                component: () => import('../view/hosts/hosts-list.vue')
            }, 
            {
                path: 'profile/:id',
                name: 'HostsProfile',
                meta: { Capabilities: 'tfhb_manage_hosts' },
                props: true,
                component: () => import('../view/hosts/hosts-profile.vue'),
                redirect: { name: 'HostsProfileInformation' },
                children: [
                    {
                        path: 'information',
                        name: 'HostsProfileInformation',
                        meta: { Capabilities: 'tfhb_manage_hosts' },
                        props: true,
                        component: () => import('../view/hosts/hosts-information.vue')
                    }, 
                    {
                        path: 'availability',
                        name: 'HostsAvailability',
                        meta: { Capabilities: 'tfhb_manage_hosts' },
                        props: true,
                        component: () => import('../view/hosts/hosts-availability.vue')
                    }, 
                    {
                        path: 'integrations',
                        name: 'HostsProfileIntegrations',
                        meta: { Capabilities: 'tfhb_manage_integrations' },
                        component: () => import('../view/hosts/hosts-integrations.vue')
                    }, 
                    {
                        path: 'calendars',
                        name: 'HostsProfileCalendars',
                        meta: { Capabilities: 'tfhb_manage_integrations' },
                        component: () => import('../view/hosts/hosts-calendars.vue')
                    }, 
                ]
            }, 
        ]
    }, 
   
    // Meetings routes
    {
        path: '/meetings',
        name: 'meetings',
        component: Meetings,
        meta: { Capabilities: 'tfhb_manage_meetings' },
        redirect: { name: 'MeetingsLists' },
        children: [ 
            {
                path: 'create-meeting-single/:id',
                props: true,
                name: 'MeetingsCreateSingle',
                meta: { Capabilities: 'tfhb_manage_meetings' },
                component: () => import('../view/meetings/meetings-create-single.vue')
            },
            {
                path: 'list',
                name: 'MeetingsLists',
                meta: { Capabilities: 'tfhb_manage_meetings' },
                component: () => import('../view/meetings/meetings-list.vue')
            }, 
            
            {
                path: 'single/:id',
                name: 'MeetingsCreate',
                meta: { Capabilities: 'tfhb_manage_meetings' },
                component: () => import('../view/meetings/meetings-create.vue'),
                props: true,
                redirect: { name: 'MeetingsCreateDetails' },
                children: [
                    
                    {
                        path: 'details',
                        props: true,
                        name: 'MeetingsCreateDetails',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-details.vue')
                    },
                    {
                        path: 'availability',
                        name: 'MeetingsCreateAvailability',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-availability.vue')
                    },
                    {
                        path: 'limits',
                        name: 'MeetingsCreateLimits',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-limits.vue')
                    },
                    {
                        path: 'questions',
                        name: 'MeetingsCreateQuestions',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-questions.vue')
                    },
                    {
                        path: 'notifications',
                        name: 'MeetingsCreateNotifications',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-notifications.vue')
                    },
                    {
                        path: 'payment',
                        name: 'MeetingsCreatePayment',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-payment.vue')
                    },
                    // {
                    //     path: 'webhook',
                    //     name: 'MeetingsCreateWebhook',
                    //     meta: { Capabilities: 'tfhb_manage_meetings' },
                    //     component: () => import('../view/meetings/meetings-webhook.vue')
                    // },
                    {
                        path: 'integrations',
                        name: 'MeetingsCreateIntegrations',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/meetings/meetings-integrations.vue')
                    }
                ]
            }, 
        ]
    }, 
 
    
    // Settings routes
    {
        path: '/settings',
        component: Settings,
        meta: { Capabilities: 'tfhb_manage_settings' },
        redirect: { name: 'SettingsGeneral' },
        children: [ 
            {
                path: 'general',
                name: 'SettingsGeneral',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/General.vue')
            },   
            {
                path: 'hosts-settings',
                name: 'HostsSettings',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/HostsSettings.vue'),
                redirect: { name: 'HostsSettingsInformationBuilder' },
                children: [ 
                    {
                        path: 'information-builder',
                        name: 'HostsSettingsInformationBuilder',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/HostsInformationBuilder.vue')
                    },
                    {
                        path: 'permission',
                        name: 'HostsSettingsPermission',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/hostsPermission.vue')
                    }
                ]
            },
            {
                path: 'shortcodes',
                name: 'ShortcodeSettings',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/Shortcodes.vue'),
                redirect: { name: 'MeetingsShortcodeSettings' },
                children: [ 
                    {
                        path: 'meetings',
                        name: 'MeetingsShortcodeSettings',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/shortcodes/MeetingsShortcodes.vue')
                    }, 
                    {
                        path: 'hosts',
                        name: 'HostsShortcodeSettings',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/shortcodes/HostsShortcodes.vue')
                    }, 
                    {
                        path: 'meeting-categories',
                        name: 'MeetingsCategoriesShortcodeSettings',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/shortcodes/CategoriesShortcodes.vue')
                    }, 
                ]
            },
            {
                path: 'fd-dashboard',
                name: 'FrontendDashboard',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/FrontendDashboard.vue'),
                // redirect: { name: 'FrontendDashboardGeneral' },
                children: [ 
                    // {
                    //     path: 'general',
                    //     name: 'FrontendDashboardGeneral',
                    //     meta: { Capabilities: 'tfhb_manage_settings' },
                    //     component: () => import('@/components/settings/fd-dashboard/General.vue')
                    // },
                    // {
                    //     path: 'login',
                    //     name: 'FrontendDashboardLogin',
                    //     meta: { Capabilities: 'tfhb_manage_settings' },
                    //     component: () => import('@/components/settings/fd-dashboard/login.vue')
                    // },
                    // {
                    //     path: 'signup',
                    //     name: 'FrontendDashboardSignup',
                    //     meta: { Capabilities: 'tfhb_manage_settings' },
                    //     component: () => import('@/components/settings/fd-dashboard/Signup.vue')
                    // }
                ]
            },

            // For The addons 
             {
                path: 'addons-settings',
                name: 'AddonsSettings',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/AddonsSettings.vue'),
                redirect: { name: 'AddonsSettingsBuyers' },
                children: [ 
                    {
                        path: 'buyers',
                        name: 'AddonsSettingsBuyers',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/addons-settings/buyers-settings.vue')
                    }, 
                    {
                        path: 'sellers',
                        name: 'AddonsSettingsSellers',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/addons-settings/sellers-settings.vue')
                    }, 
                    {
                        path: 'sellers',
                        name: 'AddonsSettingsExhibitors',
                        meta: { Capabilities: 'tfhb_manage_settings' },
                        component: () => import('@/components/settings/addons-settings/exhibitors-settings.vue')
                    }, 
                ]
            },
            {
                path: 'availability',
                name: 'SettingsAvailability',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/Availability.vue')
            }, 
            {
                path: 'notifications',
                name: 'SettingsNotifications',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/Notifications.vue'),
                children: [ 
                    {
                        path: 'single/:type/:id',
                        props: true,
                        name: 'EmailTemplateSingle',
                        meta: { Capabilities: 'tfhb_manage_meetings' },
                        component: () => import('../view/settings/EmailSingle.vue')
                    },
                ]
            },
            {
                path: 'integrations',
                name: 'SettingsIntegrations',
                meta: { Capabilities: 'tfhb_manage_settings' },
                component: () => import('../view/settings/Integrations.vue')
            },  
            {
                path: 'appearance',
                name: 'SettingsAppearance',
                component: () => import('../view/settings/Appearance.vue')
            },  
            {
                path: 'category',
                name: 'SettingsCategory',
                component: () => import('../view/settings/Category.vue')
            },
            {
                path: 'license',
                name: 'LicenseCategory',
                component: () => import('../view/settings/License.vue')
            },
             
        ]
        
    },
    // ...

     // Setup Wizard routes
     {
        path: '/setup-wizard',
        component: setupWizard,
        name: 'setupWizard',
        meta: { Capabilities: 'tfhb_manage_settings' },
        // redirect: { name: 'SettingsGeneral' },
       
        
    },
    // ...

    // frontend dasboard profile
    {
        path: '/frontend-dashboard/profile',
        name: 'FrontendDashboardProfile',
        meta: { Capabilities: 'tfhb_manage_options' },
        props: true,
        component: () => import('../view/FrontendDashboard/userProfile.vue'),
        redirect: { name: 'FrontendDashboardInformation' },
        children: [
            {
                path: 'information',
                name: 'FrontendDashboardInformation',
                meta: { Capabilities: 'tfhb_manage_hosts' },
                props: true,
                component: () => import('../view/FrontendDashboard/userInformation.vue')
            }, 
            {
                path: 'availability',
                name: 'FrontendDashboardAvailability',
                meta: { Capabilities: 'tfhb_manage_hosts' },
                props: true,
                component: () => import('../view/hosts/hosts-availability.vue')
            }, 
            {
                path: 'integrations',
                name: 'FrontendDashboardIntegrations',
                meta: { Capabilities: 'tfhb_manage_integrations' },
                component: () => import('../view/hosts/hosts-integrations.vue')
            }, 
            {
                path: 'calendars',
                name: 'FrontendDashboardCalendars',
                meta: { Capabilities: 'tfhb_manage_integrations' },
                component: () => import('../view/hosts/hosts-calendars.vue')
            }, 
        ]
    },
    // Attendee Dashboard
    {
        path: '/attendee-dashboard',
        name: 'AttendeeDashboard',
        meta: { Capabilities: 'tfhb_manage_options' },
        props: true,
        component: () => import('../view/FrontendDashboard/attendees/attendeeDashboard.vue'),
        // redirect: { name: 'AttendeeDashboardInformation' }, 
    }
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Return a promise for smooth scrolling
    return new Promise((resolve) => {
      setTimeout(() => { 
        resolve({ top: 0, behavior: 'smooth' });
      }, 0);
    });
  },
});
 

// Navigation guards to check authentication status
router.beforeEach(async (to, from, next) => { 
 
    if (to.name == 'setupWizard') { 
        document.body.classList.add('tfhb-setup-wizard-body');
    }else{ 
        document.body.classList.remove('tfhb-setup-wizard-body');
    }
    if (to.meta.Capabilities === undefined) {
        // If no capabilities are defined for the route, proceed to the next route
        next();
        return;
    }

    try {
        // Fetch user authentication data based on capabilities
        await AuthData.fetchAuth();

        // Check if the user has the required capabilities for the route
        const hasCapabilities = AuthData.Capabilities(to.meta.Capabilities);  
        if (hasCapabilities) {
            // Host Route for if use is host 
            if(  user_role == 'tfhb_host' && to.name == 'HostsLists' ){
                // next({ name: 'HostsProfile', params: { id: user_id } }); 
                next({ name: 'HostsProfile', params: { id: host_id } }); 
            }
            if(  user_role == 'tfhb_attendee'  && to.name == 'dashboard' ){
                alert(1)
                // next({ name: 'HostsProfile', params: { id: user_id } }); 
                next({ name: 'AttendeeDashboard'}); 
            }
            // User has the required capabilities, continue to the next route
            next();
        } else {
            // User is not authenticated
            // Redirect to the home page or display an alert
            alert('Sorry, you are not allowed to access this page.');
            next('/');
        } 

        
       
 

    } catch (error) {
        // Handle error if fetching authentication data fails
        console.error('Error fetching authentication data:', error);
        // Redirect to the home page or display an alert
        alert('An error occurred while fetching authentication data.');
        next('/');
    }
});

export default router;
