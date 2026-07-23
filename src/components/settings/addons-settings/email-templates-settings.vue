<script setup>
import { ref, onBeforeMount } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { AddonsSettings } from '@/store/settings/addons-settings';

const activeRole = ref('buyers');

const roles = [
    { key: 'buyers', label: 'Buyers' },
    { key: 'sellers', label: 'Sellers' },
    { key: 'exhibitors', label: 'Exhibitors' },
];

const templateTypes = [
    {
        key: 'registration_confirmation',
        label: 'Registration Confirmation',
    },
    {
        key: 'password_setup',
        label: 'Password Setup Email',
    },
    // 'import_welcome' (CSV Import Welcome Email) hidden for now - re-add here to bring it back.
];

onBeforeMount(() => {
    AddonsSettings.FetchEmailTemplates();
});
</script>
<template>
    <div class="tfhb-admin-title">
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Event Email Templates') }}</h2>
        <p>{{ $tfhb_trans('Customize the emails sent to Buyers, Sellers and Exhibitors during registration. This is separate from Hydra Booking\'s core Notifications settings, which only cover Host/Attendee meeting booking emails.') }}</p>
    </div>

    <div class="tfhb-notification-button-tabs tfhb-flexbox tfhb-mb-16">
        <button
            v-for="role in roles"
            :key="role.key"
            type="button"
            class="tfhb-btn tfhb-notification-tabs tab-btn flex-btn"
            :class="activeRole === role.key ? 'active' : ''"
            @click="activeRole = role.key"
        >
            {{ $tfhb_trans(role.label) }}
        </button>
    </div>

    <div class="tfhb-notification-wrap tfhb-notification-attendee tfhb-admin-card-box">
        <div
            v-for="type in templateTypes"
            :key="type.key"
            class="tfhb-notification-single tfhb-flexbox tfhb-justify-between"
        >
            <div class="tfhb-swicher-wrap tfhb-flexbox">
                <HbSwitch
                    v-model="AddonsSettings.email_templates[activeRole][type.key].enabled"
                    :label="$tfhb_trans(type.label)"
                />
            </div>

            <router-link
                class="tfhb-btn tfhb-edit flex-btn"
                :to="{ name: 'AddonsSettingsEmailTemplateEdit', params: { role: activeRole, type: type.key } }"
            >
                <Icon name="PencilLine" size="15" /> {{ $tfhb_trans('Edit') }}
            </router-link>
        </div>

        <HbButton
            classValue="tfhb-btn boxed-btn tfhb-mt-16"
            @click="AddonsSettings.UpdateEmailTemplates(activeRole)"
            :buttonText="$tfhb_trans('Save')"
            :pre_loader="AddonsSettings.update_preloader"
            :hover_animation="false"
        />
    </div>
</template>
