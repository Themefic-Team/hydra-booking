<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, watch } from 'vue';
import axios from 'axios';
import Icon from '@/components/icon/LucideIcon.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbInfoBox from '@/components/widgets/HbInfoBox.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbText from '@/components/form-fields/HbText.vue';
import { toast } from "vue3-toastify";

const skeleton = ref(true);
const resetLoader = ref(false);
const generateMeetingLoader = ref(false);
const iCalSettings = reactive({
    url: '',
    secret: ''
});

const meetings = ref([]);
const selectedMeeting = ref(null);
const generatedMeetingUrl = ref('');
const activePlatform = ref('google');

const fetchICalSettings = async () => {
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/icalendar', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            }
        });
        if (response.data.status) {
            iCalSettings.url = response.data.url;
            iCalSettings.secret = response.data.secret;
            skeleton.value = false;
        }
    } catch (error) {
        console.error(error);
    }
};

const fetchMeetings = async () => {
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/icalendar/meetings', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            }
        });
        if (response.data.status) {
            meetings.value = response.data.meetings.map(m => ({
                name: m.title,
                value: m.id,
                url: m.url,
                hash: m.hash
            }));
        }
    } catch (error) {
        console.error(error);
    }
};

const generateMeetingUrl = async () => {
    if (!selectedMeeting.value) {
        toast.error(__('Please select a meeting', 'hydra-booking'), {
            position: 'bottom-right',
            autoClose: 1500,
        });
        return;
    }

    // If we already have a saved hash/url in our local list, just show it
    const meeting = meetings.value.find(m => m.value === selectedMeeting.value);
    if (meeting && meeting.url) {
        generatedMeetingUrl.value = meeting.url;
        toast.success(__('Using existing saved link', 'hydra-booking'), {
            position: 'bottom-right',
            autoClose: 1500,
        });
        return;
    }

    generateMeetingLoader.value = true;
    try {
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/icalendar/generate-meeting-url', {
            meeting_id: selectedMeeting.value
        }, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            }
        });
        if (response.data.status) {
            generatedMeetingUrl.value = response.data.url;
            // Update local state so we don't fetch again
            const index = meetings.value.findIndex(m => m.value === selectedMeeting.value);
            if (index !== -1) {
                meetings.value[index].url = response.data.url;
                meetings.value[index].hash = response.data.hash;
            }
            toast.success(__('Meeting iCal URL generated and saved', 'hydra-booking'), {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    } catch (error) {
        console.error(error);
    } finally {
        generateMeetingLoader.value = false;
    }
};

const resetICalSecret = async () => {
    if (!confirm(__('Are you sure you want to reset the secret token? Your existing calendar links will stop working.', 'hydra-booking'))) {
        return;
    }
    resetLoader.value = true;
    try {
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/icalendar/reset', {}, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            }
        });
        if (response.data.status) {
            iCalSettings.url = response.data.url;
            iCalSettings.secret = response.data.secret;
            toast.success(response.data.message || 'Token reset successfully', {
                position: 'bottom-right',
                autoClose: 1500,
            });
            // Refresh meetings too as their URLs contain the secret
            fetchMeetings();
        }
    } catch (error) {
        console.error(error);
    } finally {
        resetLoader.value = false;
    }
};

const copyToClipboard = (text) => {
    if (!text) return;

    const handleSuccess = () => {
        toast.success(__('Link copied to clipboard', 'hydra-booking'), {
            position: 'bottom-right',
            autoClose: 1500,
        });
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(handleSuccess).catch(err => {
            console.error('Clipboard API failed: ', err);
            fallbackCopy(text, handleSuccess);
        });
    } else {
        fallbackCopy(text, handleSuccess);
    }
};

const fallbackCopy = (text, callback) => {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-9999px";
    textArea.style.top = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        const successful = document.execCommand('copy');
        if (successful && callback) {
            callback();
        } else if (!successful) {
            toast.error(__('Failed to copy link', 'hydra-booking'), {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    } catch (err) {
        toast.error(__('Failed to copy link', 'hydra-booking'), {
            position: 'bottom-right',
            autoClose: 1500,
        });
    }
    document.body.removeChild(textArea);
};

watch(selectedMeeting, (newId) => {
    const meeting = meetings.value.find(m => m.value === newId);
    if (meeting && meeting.url) {
        generatedMeetingUrl.value = meeting.url;
    } else {
        generatedMeetingUrl.value = '';
    }
});

onBeforeMount(() => {
    fetchICalSettings();
    fetchMeetings();
});
</script>

<template>
    <div :class="{ 'tfhb-skeleton': skeleton }" class="tfhb-ical-settings-page">
        <div class="tfhb-dashboard-heading tfhb-mb-16 tfhb-flexbox tfhb-justify-between tfhb-align-center">
             <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('iCalendar Settings') }}</h1>  
                <p>{{ $tfhb_trans('Manage your iCal settings') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking/hydrabooking-settings/icalendar-settings/" target="_blank" class="tfhb-btn tfhb-docs-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}  <Icon name="ArrowUpRight" size=20 /></a>
            </div> 
        </div>

        <div class="tfhb-content-wrap tfhb-ical-content">
            <!-- Info Box -->
            <HbInfoBox class="tfhb-mb-24" icon="Pin">
                <template #content>
                    <p>{{ $tfhb_trans('Depending on the external service you use, it may take up to 24 hours for new data to refresh.') }}</p>
                </template>
            </HbInfoBox>

            <!-- URL and Reset Section -->
            <div class="tfhb-ical-url-section tfhb-mb-24">
                <h3 class="tfhb-mb-16">{{ $tfhb_trans('Global iCalendar Feed') }}</h3>
                <div class="tfhb-flexbox tfhb-gap-12 tfhb-mb-16">
                    <div class="tfhb-url-input-wrap" style="width: 70%;">
                        <div class="tfhb-input-with-icon">
                            <span class="tfhb-input-icon"><Icon name="Link" size="18" /></span>
                            <input type="text" :value="iCalSettings.url" readonly class="tfhb-url-field" />
                        </div>
                    </div>
                    <HbButton 
                        @click="resetICalSecret" 
                        :buttonText="$tfhb_trans('Reset Token')" 
                        classValue="tfhb-btn secondary-btn" 
                        :pre_loader="resetLoader"
                    />
                </div>

                <HbButton 
                    @click="copyToClipboard(iCalSettings.url)" 
                    :buttonText="$tfhb_trans('Copy Global iCal Link')" 
                    icon="Copy" 
                    hover_icon="Copy" 
                    :hover_animation="true"
                    icon_position="right"
                    classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                />
            </div>

            <!-- Meeting Specific Section -->
            <div class="tfhb-ical-meeting-section tfhb-mt-32 tfhb-pt-32  ">
                <h3 class="tfhb-mb-16">{{ $tfhb_trans('Meeting Specific iCalendar') }}</h3>
                <div class="tfhb-flexbox tfhb-gap-12 tfhb-align-end tfhb-mb-16">
                    <div class="tfhb-flex-grow" style="max-width: 400px;">
                        <HbDropdown 
                            v-model="selectedMeeting"
                            :option="meetings"
                            :placeholder="$tfhb_trans('Select a Meeting')"
                            :label="$tfhb_trans('Generate link for a specific meeting')"
                        />
                    </div>
                    <HbButton 
                        @click="generateMeetingUrl" 
                        :buttonText="generatedMeetingUrl ? $tfhb_trans('Get Link') : $tfhb_trans('Generate')" 
                        classValue="tfhb-btn boxed-btn" 
                        :pre_loader="generateMeetingLoader"
                        :disabled="!selectedMeeting"
                    />
                </div>

                <div v-if="generatedMeetingUrl" class="tfhb-generated-url-area animate__animated animate__fadeIn">
                    <div class="tfhb-input-with-icon tfhb-mb-16" style="width: 70%;">
                        <span class="tfhb-input-icon"><Icon name="Link" size="18" /></span>
                        <input type="text" :value="generatedMeetingUrl" readonly class="tfhb-url-field" />
                    </div>
                    <HbButton 
                        @click="copyToClipboard(generatedMeetingUrl)" 
                        :buttonText="$tfhb_trans('Copy Meeting iCal Link')" 
                        icon="Copy" 
                        hover_icon="Copy" 
                        :hover_animation="true"
                        icon_position="right"
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                    />
                </div>

                <div class="tfhb-ical-footer-text tfhb-mt-16">
                    <p>
                        {{ $tfhb_trans('iCalendar link is compatible with') }} 
                        <strong>{{ $tfhb_trans('every solution') }}</strong> 
                        {{ $tfhb_trans('capable of reading that format.') }}
                    </p>
                    <p><strong>{{ $tfhb_trans('Like Google, Outlook, iCloud and a lot more!') }}</strong></p>
                </div>
            </div>

            <!-- Instructions Section -->
            <div class="tfhb-ical-instructions-section tfhb-mt-40">
                <div class="tfhb-instructions-header tfhb-mb-24">
                    <h3>{{ $tfhb_trans('How to sync your calendar') }}</h3>
                    <p>{{ $tfhb_trans('Choose your platform to see the subscription steps') }}</p>
                </div>

                <div class="tfhb-platform-tabs tfhb-mb-32">
                    <button 
                        @click="activePlatform = 'google'" 
                        :class="{ 'active': activePlatform === 'google' }"
                        class="tfhb-platform-tab"
                    >
                        <Icon name="Globe" size="18" />
                        <span>Google</span>
                    </button>
                    <button 
                        @click="activePlatform = 'outlook'" 
                        :class="{ 'active': activePlatform === 'outlook' }"
                        class="tfhb-platform-tab"
                    >
                        <Icon name="Mail" size="18" />
                        <span>Outlook</span>
                    </button>
                    <button 
                        @click="activePlatform = 'macos'" 
                        :class="{ 'active': activePlatform === 'macos' }"
                        class="tfhb-platform-tab"
                    >
                        <Icon name="Monitor" size="18" />
                        <span>macOS</span>
                    </button>
                    <button 
                        @click="activePlatform = 'iphone'" 
                        :class="{ 'active': activePlatform === 'iphone' }"
                        class="tfhb-platform-tab"
                    >
                        <Icon name="Smartphone" size="18" />
                        <span>iPhone</span>
                    </button>
                </div>

                <div class="tfhb-instruction-content animate__animated animate__fadeIn" :key="activePlatform">
                    <!-- Google Calendar -->
                    <div v-if="activePlatform === 'google'" class="tfhb-steps">
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">1</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Go to') }} <a href="https://calendar.google.com" target="_blank" class="tfhb-text-link">Google Calendar</a>
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">2</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('On the left side, find "Other calendars" and click the') }} <span class="tfhb-badge-icon small"><Icon name="Plus" size="12" /></span> {{ $tfhb_trans('icon.') }}
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">3</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Select') }} <strong>{{ $tfhb_trans('From URL') }}</strong> {{ $tfhb_trans('from the menu.') }}
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">4</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Paste your iCal link into the URL field and click') }} <strong>{{ $tfhb_trans('Add Calendar') }}</strong>.
                            </div>
                        </div>
                    </div>

                    <!-- Outlook -->
                    <div v-if="activePlatform === 'outlook'" class="tfhb-steps">
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">1</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Go to') }} <a href="https://outlook.live.com/calendar" target="_blank" class="tfhb-text-link">Outlook Calendar</a>
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">2</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Click on') }} <strong>{{ $tfhb_trans('Add calendar') }}</strong> {{ $tfhb_trans('in the navigation pane.') }}
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">3</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Choose') }} <strong>{{ $tfhb_trans('Subscribe from web') }}</strong>.
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">4</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Paste your link, give it a name, and click') }} <strong>{{ $tfhb_trans('Import') }}</strong> {{ $tfhb_trans('or') }} <strong>{{ $tfhb_trans('Save') }}</strong>.
                            </div>
                        </div>
                    </div>

                    <!-- macOS -->
                    <div v-if="activePlatform === 'macos'" class="tfhb-steps">
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">1</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Open the') }} <strong>{{ $tfhb_trans('Calendar App') }}</strong> {{ $tfhb_trans('on your Mac.') }}
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">2</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Go to') }} <strong>{{ $tfhb_trans('File') }}</strong> &rarr; <strong>{{ $tfhb_trans('New Calendar Subscription...') }}</strong>
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">3</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Paste your iCal link and click') }} <strong>{{ $tfhb_trans('Subscribe') }}</strong>.
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">4</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Adjust the name and refresh interval (e.g., Every hour) and click') }} <strong>{{ $tfhb_trans('OK') }}</strong>.
                            </div>
                        </div>
                    </div>

                    <!-- iPhone -->
                    <div v-if="activePlatform === 'iphone'" class="tfhb-steps">
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">1</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Open the') }} <strong>{{ $tfhb_trans('Calendar App') }}</strong> {{ $tfhb_trans('and tap') }} <strong>{{ $tfhb_trans('Calendars') }}</strong> {{ $tfhb_trans('at the bottom.') }}
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">2</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Tap') }} <strong>{{ $tfhb_trans('Add Calendar') }}</strong> &rarr; <strong>{{ $tfhb_trans('Add Subscribed Calendar') }}</strong>.
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">3</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Paste your iCal link and tap') }} <strong>{{ $tfhb_trans('Subscribe') }}</strong>.
                            </div>
                        </div>
                        <div class="tfhb-step-item">
                            <div class="tfhb-step-number">4</div>
                            <div class="tfhb-step-text">
                                {{ $tfhb_trans('Review the settings and tap') }} <strong>{{ $tfhb_trans('Add') }}</strong> {{ $tfhb_trans('at the top right.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tfhb-ical-external-footer tfhb-mt-24 tfhb-text-center">
            <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-text-link">
                {{ $tfhb_trans('Learn more about') }} {{ $tfhb_trans('iCalendar') }}
            </a>
        </div>
    </div>
</template>

<style scoped>
.tfhb-ical-settings-page {
    width: 100%;
}

.tfhb-flexbox {
    display: flex;
    width: 100%;
}

.tfhb-btn.flex-btn {
    width: 100%;
    justify-content: center;
}

.tfhb-badge-pro {
    background: #E8F5E9;
    color: #2E7D32;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

.tfhb-ical-content {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 32px;
}

.tfhb-input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.tfhb-input-icon {
    position: absolute;
    left: 12px;
    color: #64748b !important;
    top: 8px;
}

.tfhb-url-field {
    width: 100%;
    padding: 8px 8px 8px 40px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #475569;
    outline: none;
}

.tfhb-ical-footer-text p {
    margin: 4px 0;
    font-size: 14px;
    color: #64748b;
}

.tfhb-ical-instructions-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 32px;
}

.tfhb-instructions-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.tfhb-instructions-header p {
    color: #64748b;
    font-size: 14px;
}

.tfhb-platform-tabs {
    display: flex;
    gap: 8px;
    background: #f1f5f9;
    padding: 6px;
    border-radius: 10px;
    width: fit-content;
}

.tfhb-platform-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    background: transparent;
    border-radius: 8px;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tfhb-platform-tab:hover {
    color: #334155;
    background: rgba(255, 255, 255, 0.5);
}

.tfhb-platform-tab.active {
    background: #fff;
    color: #3b82f6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.tfhb-platform-tab span {
    margin-top: 1px;
}

.tfhb-step-item {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    position: relative;
}

.tfhb-step-item:last-child {
    margin-bottom: 0;
}

.tfhb-step-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 14px;
    top: 32px;
    bottom: -16px;
    width: 2px;
    background: #f1f5f9;
}

.tfhb-step-number {
    width: 30px;
    height: 30px;
    background: #eff6ff;
    color: #3b82f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px #dbeafe;
    z-index: 1;
}

.tfhb-step-text {
    font-size: 14.5px;
    color: #475569;
    line-height: 1.6;
    padding-top: 4px;
}

.tfhb-step-text strong {
    color: #1e293b;
    font-weight: 600;
}

.tfhb-badge-icon.small {
    width: 20px;
    height: 20px;
    margin: 0 2px;
}

.tfhb-mt-40 {
    margin-top: 40px;
}
</style>