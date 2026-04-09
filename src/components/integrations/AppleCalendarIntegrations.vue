<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();

// import Form Field 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbButton from '@/components/form-fields/HbButton.vue';

const props = defineProps([
    'apple_calendar', 
    'class', 
    'display', 
    'pre_loader', 
    'ispopup'
])
const emit = defineEmits([ "update-integrations", 'popup-open-control', 'popup-close-control' ]); 

const closePopup = () => { 
    emit('popup-close-control', false)
}
</script>
 
<template>
    <!-- Apple Calendar Integrations -->
    <div class="tfhb-integrations-single-block tfhb-admin-card-box"
        :class="props.class, {
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
    >
        <span v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-badge tfhb-badge-pro tfhb-flexbox tfhb-gap-8">
            <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}
        </span>

        <div :class="display == 'list' ? 'tfhb-flexbox' : ''" class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url + '/assets/images/ical.png'" alt="">
            </span>
            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Apple Calendar') }}</h3>
                <p>{{ $tfhb_trans('Connect Apple Calendar via CalDAV to sync your booked events.') }}</p>
            </div>
        </div>

        <!-- Not pro: show upgrade button -->
        <div v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-integrations-single-block-btn tfhb-flexbox">
            <router-link to="/settings/license" class="tfhb-btn tfhb-flexbox tfhb-gap-8">
                {{ $tfhb_trans('Upgrade to Pro') }} <Icon name="ChevronRight" size=18 />
            </router-link>
        </div>

        <!-- Pro: show connect button + status toggle -->
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <HbButton
                @click="emit('popup-open-control')"
                classValue="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"
                :buttonText="apple_calendar.connection_status == 1 ? $tfhb_trans('Connected') : $tfhb_trans('Connect')"
                :hover_animation="true"
            />
            <HbSwitch
                v-if="apple_calendar.connection_status == 1"
                @change="emit('update-integrations', 'apple_calendar', apple_calendar)"
                v-model="apple_calendar.status"
            />
        </div>

        <!-- Popup -->
        <HbPopup
            v-if="$tfhb_is_pro == true && $tfhb_license_status == true"
            :isOpen="ispopup"
            @modal-close="closePopup"
            max_width="600px"
            name="first-modal"
        >
            <template #header>
                <h2>{{ $tfhb_trans('Add Apple Calendar') }}</h2>
            </template>

            <template #content>
                <p>
                    {{ $tfhb_trans('Enter your Apple ID (iCloud email) and an App-Specific Password generated from your Apple ID account settings.') }}
                    <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/integrations/apple-calendar-integration/" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8">
                        {{ $tfhb_trans('Read Documentation') }}
                    </a>
                </p>
                <HbText
                    v-model="apple_calendar.apple_id"
                    required="true"
                    name="apple_id"
                    :errors="errors.apple_id"
                    :label="$tfhb_trans('Apple ID (Email)')"
                    selected="1"
                    :placeholder="$tfhb_trans('Enter Apple ID (Email)')"
                />
                <HbText
                    v-model="apple_calendar.app_password"
                    required="true"
                    type="password"
                    name="app_password"
                    :errors="errors.app_password"
                    :label="$tfhb_trans('App-Specific Password')"
                    selected="1"
                    :placeholder="apple_calendar.app_password_set == 1 ? $tfhb_trans('Password already set. Enter new password to change.') : $tfhb_trans('Enter App-Specific Password')"
                />
                <HbButton
                    @click.stop="emit('update-integrations', 'apple_calendar', apple_calendar, ['apple_id', 'app_password'])"
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8 tfhb-icon-hover-animation"
                    :buttonText="$tfhb_trans('Save & Validate')"
                    icon="ChevronRight"
                    hover_icon="ArrowRight"
                    :hover_animation="true"
                    :pre_loader="props.pre_loader"
                />
            </template>
        </HbPopup>

    </div>
    <!-- Single Integrations -->

</template>
 

<style scoped>
</style> 