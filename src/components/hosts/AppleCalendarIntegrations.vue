<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';

const router = useRouter();
const appleCalPopup = ref(false);

const props = defineProps([
    'apple_calendar', 
    'class', 
    'display', 
])
const emit = defineEmits([ "update-integrations", ]);  

const disconnectIntegration = () => {
    props.apple_calendar.apple_id     = '';
    props.apple_calendar.app_password = '';
    appleCalPopup.value = false;
    emit('update-integrations', 'apple_calendar', props.apple_calendar);
}
</script>
 
<template>
    <!-- Apple Calendar Host Integration -->
    <div class="tfhb-integrations-single-block tfhb-admin-card-box"
        :class="props.class, {
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
    >
        <div :class="display == 'list' ? 'tfhb-flexbox' : ''" class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url + '/assets/images/ical.png'" alt="">
            </span>
            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Apple Calendar') }}</h3>
                <p>{{ $tfhb_trans('Connect your Apple Calendar to sync your booked events.') }}</p>
            </div>
        </div>

        <!-- Not pro -->
        <div v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8">
                <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}
            </span>
        </div>

        <!-- Global settings not enabled: go to Settings page -->
        <div v-else-if="!apple_calendar.status" class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <button @click="router.push({ name: 'SettingsIntegrations' })" class="tfhb-btn tfhb-flexbox tfhb-gap-8">
                {{ $tfhb_trans('Go to Settings') }} <Icon name="Settings" size=18 />
            </button>
        </div>

        <!-- Global settings enabled: host can configure own credentials -->
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <button @click="appleCalPopup = true" class="tfhb-btn tfhb-flexbox tfhb-gap-8">
                {{ apple_calendar.connection_status == 1 ? $tfhb_trans('Settings') : $tfhb_trans('Connect') }}
                <Icon name="ChevronRight" size=18 />
            </button>
            <HbSwitch
                v-if="apple_calendar.connection_status == 1"
                v-model="apple_calendar.host_status"
                @change="emit('update-integrations', 'apple_calendar', apple_calendar)"
            />
        </div>

        <!-- Credentials popup -->
        <HbPopup
            v-if="$tfhb_is_pro == true && apple_calendar.status"
            :isOpen="appleCalPopup"
            @modal-close="appleCalPopup = false"
            max_width="600px"
            name="first-modal"
        >
            <template #header>
                <h3>{{ $tfhb_trans('Apple Calendar') }}</h3>
                <p v-if="apple_calendar.apple_id">{{ apple_calendar.apple_id }}</p>
            </template>

            <template #content>
                <p>{{ $tfhb_trans('Enter your Apple ID and an App-Specific Password to sync your personal calendar with Hydra Booking.') }}</p>
                <HbText
                    v-model="apple_calendar.apple_id"
                    required="true"
                    :label="$tfhb_trans('Apple ID (Email)')"
                    selected="1"
                    :placeholder="$tfhb_trans('Enter Apple ID (Email)')"
                />
                <HbText
                    v-model="apple_calendar.app_password"
                    required="true"
                    type="password"
                    :label="$tfhb_trans('App-Specific Password')"
                    selected="1"
                    :placeholder="apple_calendar.app_password_set == 1 ? $tfhb_trans('Password already set. Enter new password to change.') : $tfhb_trans('Enter App-Specific Password')"
                />
                <div class="tfhb-submission-btn tfhb-mt-8 tfhb-mb-8 tfhb-flexbox tfhb-gap-8">
                    <HbButton
                        @click.stop="emit('update-integrations', 'apple_calendar', apple_calendar); appleCalPopup = false"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"
                        :buttonText="$tfhb_trans('Save Settings')"
                        icon="ChevronRight"
                        hover_icon="ArrowRight"
                        :hover_animation="true"
                    />
                    <HbButton
                        v-if="apple_calendar.connection_status == 1"
                        @click.stop="disconnectIntegration()"
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8"
                        :buttonText="$tfhb_trans('Disconnect')"
                        icon="Unplug"
                        icon_position="left"
                    />
                </div>
            </template>
        </HbPopup>

    </div>
    <!-- Single Integrations -->

</template>
 

<style scoped>
</style>
