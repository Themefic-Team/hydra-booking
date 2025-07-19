import { createRouter, createWebHashHistory } from 'vue-router';
import Dashboard from '../view/dashboard/Dashboard.vue';  
import Booking from '../view/booking/booking.vue'; 
import Settings from '../view/settings/Settings.vue';
import setupWizard from '../view/setup-wizard/setup-wizard.vue';
import Hosts from '../view/hosts/hosts.vue';
import Meetings from '../view/meetings/meetings.vue';
import { AuthData } from '@/store/auth';

// Event - Consider moving this to a separate auth utility
const user = tfhb_core_apps.user || {};
const user_id = user.id || '';
const host_id = user.host_id || ''; 
const user_role = user.role?.[0] || '';

// Helper function to check if user has role-based access
const hasRoleAccess = (requiredRole) => {
    return user_role === requiredRole;
};

// Route groups for better organization
const bookingRoutes = {
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
            path: 'create',
            name: 'BookingCreate',
            meta: { Capabilities: 'tfhb_manage_booking' },
            component: () => import('../view/booking/booking-create.vue')
        },
        {
            path: ':id/edit',
            name: 'BookingUpdate',
            meta: { Capabilities: 'tfhb_manage_booking' },
            component: () => import('../view/booking/booking-update.vue'),
            props: true
        }, 
        {
            path: ':id',
            name: 'BookingDetails',
            meta: { Capabilities: 'tfhb_manage_booking' },
            component: () => import('../view/booking/booking-details.vue'),
            props: true
        }
    ]
};

const hostRoutes = {
    path: '/hosts',
    name: 'hosts',
    component: Hosts,
    meta: { Capabilities: 'tfhb_manage_hosts' },
    redirect: { name: 'HostsLists' },
    children: [ 
        {
            path: '',
            name: 'HostsLists',
            meta: { Capabilities: 'tfhb_manage_hosts' },
            component: () => import('../view/hosts/hosts-list.vue')
        }, 
        {
            path: ':id',
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
                    props: true,
                    component: () => import('../view/hosts/hosts-integrations.vue')
                }, 
                {
                    path: 'calendars',
                    name: 'HostsProfileCalendars',
                    meta: { Capabilities: 'tfhb_manage_integrations' },
                    props: true,
                    component: () => import('../view/hosts/hosts-calendars.vue')
                }
            ]
        }
    ]
};

const meetingRoutes = {
    path: '/meetings',
    name: 'meetings',
    component: Meetings,
    meta: { Capabilities: 'tfhb_manage_meetings' },
    redirect: { name: 'MeetingsLists' },
    children: [ 
        {
            path: '',
            name: 'MeetingsLists',
            meta: { Capabilities: 'tfhb_manage_meetings' },
            component: () => import('../view/meetings/meetings-list.vue')
        },
        {
            path: ':id/quick-create',
            props: true,
            name: 'MeetingsCreateSingle',
            meta: { Capabilities: 'tfhb_manage_meetings' },
            component: () => import('../view/meetings/meetings-create-single.vue')
        },
        {
            path: ':id/create',
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
                    props: true,
                    component: () => import('../view/meetings/meetings-availability.vue')
                },
                {
                    path: 'limits',
                    name: 'MeetingsCreateLimits',
                    meta: { Capabilities: 'tfhb_manage_meetings' },
                    props: true,
                    component: () => import('../view/meetings/meetings-limits.vue')
                },
                {
                    path: 'questions',
                    name: 'MeetingsCreateQuestions',
                    meta: { Capabilities: 'tfhb_manage_meetings' },
                    props: true,
                    component: () => import('../view/meetings/meetings-questions.vue')
                },
                {
                    path: 'notifications',
                    name: 'MeetingsCreateNotifications',
                    meta: { Capabilities: 'tfhb_manage_meetings' },
                    props: true,
                    component: () => import('../view/meetings/meetings-notifications.vue')
                },
                {
                    path: 'payment',
                    name: 'MeetingsCreatePayment',
                    meta: { Capabilities: 'tfhb_manage_meetings' },
                    props: true,
                    component: () => import('../view/meetings/meetings-payment.vue')
                },
                {
                    path: 'integrations',
                    name: 'MeetingsCreateIntegrations',
                    meta: { Capabilities: 'tfhb_manage_meetings' },
                    props: true,
                    component: () => import('../view/meetings/meetings-integrations.vue')
                }
            ]
        }
    ]
};

const settingsRoutes = {
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
            path: 'hosts',
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
                    path: 'permissions',
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
                    path: 'categories',
                    name: 'MeetingsCategoriesShortcodeSettings',
                    meta: { Capabilities: 'tfhb_manage_settings' },
                    component: () => import('@/components/settings/shortcodes/CategoriesShortcodes.vue')
                }
            ]
        },
        {
            path: 'frontend-dashboard',
            name: 'FrontendDashboard',
            meta: { Capabilities: 'tfhb_manage_settings' },
            component: () => import('../view/settings/FrontendDashboard.vue')
        },
        {
            path: 'addons',
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
                    path: 'exhibitors',
                    name: 'AddonsSettingsExhibitors',
                    meta: { Capabilities: 'tfhb_manage_settings' },
                    component: () => import('@/components/settings/addons-settings/exhibitors-settings.vue')
                }
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
                    path: ':type/:id',
                    props: true,
                    name: 'EmailTemplateSingle',
                    meta: { Capabilities: 'tfhb_manage_meetings' },
                    component: () => import('../view/settings/EmailSingle.vue')
                }
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
            meta: { Capabilities: 'tfhb_manage_settings' },
            component: () => import('../view/settings/Appearance.vue')
        },  
        {
            path: 'categories',
            name: 'SettingsCategory',
            meta: { Capabilities: 'tfhb_manage_settings' },
            component: () => import('../view/settings/Category.vue')
        },
        {
            path: 'license',
            name: 'LicenseSettings',
            meta: { Capabilities: 'tfhb_manage_settings' },
            component: () => import('../view/settings/License.vue')
        }
    ]
};

// Frontend dashboard routes
const frontendDashboardRoutes = [
    {
        path: '/profile',
        name: 'FrontendDashboardProfile',
        meta: { Capabilities: 'tfhb_manage_options' },
        component: () => import('../view/FrontendDashboard/userProfile.vue'),
        redirect: { name: 'FrontendDashboardInformation' },
        children: [
            {
                path: 'information',
                name: 'FrontendDashboardInformation',
                meta: { Capabilities: 'tfhb_manage_hosts' },
                component: () => import('../view/FrontendDashboard/userInformation.vue')
            }, 
            {
                path: 'availability',
                name: 'FrontendDashboardAvailability',
                meta: { Capabilities: 'tfhb_manage_hosts' },
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
            }
        ]
    },
    {
        path: '/attendees',
        name: 'AttendeeDashboard',
        meta: { Capabilities: 'tfhb_manage_options', requiresRole: 'tfhb_attendee' },
        component: () => import('../view/FrontendDashboard/attendees/attendeeDashboard.vue')
    },
    {
        path: '/buyers',
        name: 'BuyersDashboard',
        meta: { Capabilities: 'tfhb_manage_options', requiresRole: 'tfhb_buyers' },
        component: () => import('../view/FrontendDashboard/buyers/BuyersDashboard.vue'),
        redirect: { name: 'BuyersDashboardEventList' }, 
        children: [
            {
                path: 'events',
                name: 'BuyersDashboardEventList',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/buyers/EventList.vue')
            },  
            {
                path: 'calendar',
                name: 'BuyersDashboardCalendar',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/buyers/Calenders.vue')
            },  
            {
                path: 'all-events',
                name: 'BuyersDashboardAllEvents',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/buyers/AllEvents.vue')
            },  
            {
                path: 'profile',
                name: 'BuyersDashboardProfile',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/buyers/Profile.vue')
            },  
            {
                path: 'sellers',
                name: 'BuyersDashboardSellers',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/Common/SellersList.vue')
            }
        ]
    },
    {
        path: '/sellers',
        name: 'SellersDashboard',
        meta: { Capabilities: 'tfhb_manage_options', requiresRole: 'tfhb_sellers' },
        component: () => import('../view/FrontendDashboard/sellers/SellersDashboard.vue'),
        redirect: { name: 'SellersDashboardEventList' }, 
        children: [
            {
                path: 'events',
                name: 'SellersDashboardEventList',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/Common/EventList.vue')
            },  
            {
                path: 'appointments',
                name: 'SellersDashboardAppointments',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/sellers/Appointments.vue')
            },  
            {
                path: 'all-events',
                name: 'SellersDashboardAllEvents',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/sellers/AllEvents.vue')
            },  
            {
                path: 'profile',
                name: 'SellersDashboardProfile',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/sellers/Profile.vue')
            },  
            {
                path: 'buyers',
                name: 'SellersDashboardBuyers',
                meta: { Capabilities: 'tfhb_manage_options' },
                component: () => import('../view/FrontendDashboard/Common/BuyersList.vue')
            }
        ]
    }
];

// Main routes array
const routes = [
    {
        path: '/',
        name: 'dashboard', 
        meta: { Capabilities: 'tfhb_manage_dashboard' },
        component: Dashboard
    },
    bookingRoutes,
    hostRoutes,
    meetingRoutes,
    settingsRoutes,
    {
        path: '/setup-wizard',
        component: setupWizard,
        name: 'setupWizard',
        meta: { Capabilities: 'tfhb_manage_settings' }
    },
    ...frontendDashboardRoutes
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return new Promise((resolve) => {
            setTimeout(() => { 
                resolve({ top: 0, behavior: 'smooth' });
            }, 100);
        });
    }
});

// Improved navigation guards
router.beforeEach(async (to, from, next) => {
    // Handle setup wizard body class
    if (to.name === 'setupWizard') { 
        document.body.classList.add('tfhb-setup-wizard-body');
    } else { 
        document.body.classList.remove('tfhb-setup-wizard-body');
    }

    // Skip capability check if no capabilities are defined
    if (!to.meta.Capabilities) {
        next();
        return;
    }

    try {
        // Fetch user authentication data
        await AuthData.fetchAuth(); 
        
        // Check basic capabilities
        const hasCapabilities = AuthData.Capabilities(to.meta.Capabilities);  
        
        if (!hasCapabilities) {
            console.warn('Access denied: Insufficient capabilities');
            alert('Sorry, you are not allowed to access this page.');
            next('/');
            return;
        }

        // Role-based routing logic
        const roleRedirects = {
            'tfhb_host': () => {
                if (to.name === 'HostsLists') {
                    return { name: 'HostsProfile', params: { id: host_id } };
                }
                return null;
            },
            'tfhb_buyers': () => {
                if (to.name === 'dashboard') {
                    return { name: 'BuyersDashboard' };
                }
                return null;
            },
            'tfhb_sellers': () => {
                if (to.name === 'dashboard') {
                    return { name: 'SellersDashboard' };
                }
                return null;
            }
        };

        const redirect = roleRedirects[user_role]?.();
        if (redirect) {
            next(redirect);
            return;
        }

        // Check role-specific route access
        if (to.meta.requiresRole && to.meta.requiresRole !== user_role) {
            console.warn(`Access denied: Required role ${to.meta.requiresRole}, user has ${user_role}`);
            alert('Sorry, you are not allowed to access this page.');
            next('/');
            return;
        }

        next();

    } catch (error) {
        console.error('Error in navigation guard:', error);
        alert('An error occurred while checking permissions.');
        next('/');
    }
});

export default router;