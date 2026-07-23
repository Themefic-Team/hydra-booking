<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useDragAndDrop } from "vue-fluid-dnd";
import { toast } from "vue3-toastify";
import Icon from '@/components/icon/LucideIcon.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbColor from '@/components/form-fields/HbColor.vue';
import HbFileUpload from '@/components/form-fields/HbFileUpload.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import Editor from 'primevue/editor';
import { AddonsSettings } from '@/store/settings/addons-settings';

const route = useRoute();

const role = computed(() => route.params.role);
const type = computed(() => route.params.type);

const typeLabels = {
    registration_confirmation: 'Registration Confirmation',
    password_setup: 'Password Setup Email',
};

const blockTypeLabels = {
    header: 'Header',
    gratitude: 'Greeting',
    buttons: 'Button',
    footer: 'Footer',
};

const eventPlaceholders = ['{event_name}', '{event_start_date}', '{event_end_date}', '{event_location}'];

const placeholdersByType = {
    registration_confirmation: ['{site_name}', '{email}', ...eventPlaceholders],
    password_setup: ['{site_name}', '{email}', '{password_setup_url}', ...eventPlaceholders],
};

const skeleton = ref(true);
const preloader = ref(false);
const blocks = ref([]);

// Fixed block set per template type - mirrors Hydra Booking's core Notifications
// email builder structure (Header / Greeting / Button / Footer), no add/remove UI.
function defaultBlocksFor(templateType) {
    if (templateType === 'registration_confirmation') {
        return [
            {
                id: 'header', type: 'header', order: 0, status: 1,
                logo: '', background: '#215732',
                content: '{site_name}',
            },
            {
                id: 'gratitude', type: 'gratitude', order: 1, status: 1,
                content: '<p style="font-weight: bold; margin: 0; font-size: 17px;">Hello,</p><p style="margin: 8px 0 0 0; font-size: 15px;">Your event registration is completed. We will reach out to you shortly.</p>',
            },
            {
                id: 'footer', type: 'footer', order: 2, status: 1,
                content: { description: { status: 1, content: '{site_name}' } },
            },
        ];
    }
    if (templateType === 'password_setup') {
        return [
            {
                id: 'header', type: 'header', order: 0, status: 1,
                logo: '', background: '#215732',
                content: '{site_name}',
            },
            {
                id: 'gratitude', type: 'gratitude', order: 1, status: 1,
                content: '<p style="font-weight: bold; margin: 0; font-size: 17px;">Hello,</p><p style="margin: 8px 0 0 0; font-size: 15px;">Thank you for registering with {site_name}. Please click the button below to set your password and access your dashboard.</p>',
            },
            {
                id: 'buttons', type: 'buttons', order: 2, status: 1,
                border_color: '#C0D8C4', button_label: 'Set Password',
                content: {
                    description: { status: 1, content: '<p style="font-size: 15px; margin: 0;">This link will expire in 24 hours. If you did not register for an account, please ignore this email.</p>' },
                    button: { status: 1, content: '{password_setup_url}' },
                },
            },
            {
                id: 'footer', type: 'footer', order: 3, status: 1,
                content: { description: { status: 1, content: '{site_name}' } },
            },
        ];
    }
    return [];
}

const templateData = computed(() => AddonsSettings.email_templates?.[role.value]?.[type.value]);

const loadTemplate = async () => {
    skeleton.value = true;
    await AddonsSettings.FetchEmailTemplates();
    const saved = templateData.value?.builder;
    blocks.value = (Array.isArray(saved) && saved.length) ? saved : defaultBlocksFor(type.value);
    if (templateData.value) {
        templateData.value.enabled = true;
    }
    skeleton.value = false;
};

watch([role, type], loadTemplate, { immediate: true });

const sortedBlocks = computed(() => [...blocks.value].sort((a, b) => a.order - b.order));

const handlerSelector = ".tfhb-icon-drag";
const { parent } = useDragAndDrop(blocks, { handlerSelector });

// Blocks are collapsed by default; clicking the heading expands/collapses one at a time.
const openBlocks = reactive({});
const toggleBlockOpen = (id) => {
    Object.keys(openBlocks).forEach((key) => {
        if (key !== id) openBlocks[key] = false;
    });
    openBlocks[id] = !openBlocks[id];
};

function renderBlock(block) {
    if (!block.status) return '';
    switch (block.type) {
        case 'header': {
            let inner = '';
            if (block.logo) {
                inner += `<td style="vertical-align: middle; width: 36px; padding-right: 8px"><img src="${block.logo}" alt="Logo" style="max-height: 36px; display: block;"></td>`;
            }
            if (block.content) {
                // color/size/weight set on the cell (not just inline in the editor content) so any
                // text the rich-text editor adds still inherits white/bold styling on the dark header.
                inner += `<td style="vertical-align: middle; color: #FFFFFF; font-size: 20px; font-weight: 600;">${block.content}</td>`;
            }
            return `<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tr><td bgcolor="${block.background}" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;"><table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>${inner}</tr></table></td></tr></table>`;
        }
        case 'gratitude':
            return `<table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="width: 100%; max-width: 600px; margin: 0 auto;"><tr><td style="padding: 16px 32px;">${block.content}</td></tr></table>`;
        case 'buttons': {
            let inner = '';
            if (block.content.description.content && block.content.description.status) {
                inner += `<tr><td style="font-size: 15px; padding: 24px 0 16px 0;">${block.content.description.content}</td></tr>`;
            }
            if (block.content.button.content && block.content.button.status) {
                inner += `<tr><td style="font-size: 15px; padding-bottom: 24px;"><a href="${block.content.button.content}" style="padding: 8px 24px; border-radius: 8px; border: 1px solid ${block.border_color}; background: #FFF; color: #273F2B; display: inline-block; text-decoration: none;">${block.button_label}</a></td></tr>`;
            }
            return `<table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="width: 100%; max-width: 600px; margin: 0 auto;"><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed ${block.border_color}; border-bottom: 1px dashed ${block.border_color}; padding: 0 32px; width: 100%;">${inner}</table></td></tr></table>`;
        }
        case 'footer': {
            let inner = '';
            if (block.content.description.content && block.content.description.status) {
                // color/size/weight on the cell (not just inline in the editor content) so any
                // text the rich-text editor adds still inherits white/bold styling on the dark footer.
                inner += `<td align="left" style="color: #FFFFFF; font-size: 16.5px; font-weight: bold;">${block.content.description.content}</td>`;
            }
            return `<table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="width: 100%; max-width: 600px; margin: 0 auto;"><tr><td style="padding: 16px 32px; border-radius: 0 0 8px 8px;"><table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"><tr>${inner}</tr></table></td></tr></table>`;
        }
        default:
            return '';
    }
}

const emailBodyHtml = computed(() => sortedBlocks.value.map(renderBlock).join(''));

watch(emailBodyHtml, (html) => {
    if (templateData.value) {
        templateData.value.body = html;
        templateData.value.builder = blocks.value;
    }
});

// Fallback for insecure contexts (plain http://) where navigator.clipboard
// is unavailable - the async Clipboard API only works on https:// or localhost.
const copyWithFallback = (value) => {
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    const copied = document.execCommand('copy');
    document.body.removeChild(textarea);
    return copied;
};

const copyPlaceholder = async (value) => {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);
        } else if (!copyWithFallback(value)) {
            throw new Error('execCommand copy failed');
        }
        toast.success(`${value} copied - paste it into any field below`, {
            position: 'bottom-right',
            autoClose: 1500,
        });
    } catch (error) {
        toast.error('Could not copy to clipboard', {
            position: 'bottom-right',
        });
    }
};

const saveTemplate = async () => {
    preloader.value = true;
    await AddonsSettings.UpdateEmailTemplates(role.value);
    preloader.value = false;
};
</script>
<template>
    <div :class="{ 'tfhb-skeleton': skeleton }">
        <div class="tfhb-dashboard-heading tfhb-mb-16">
            <div class="tfhb-admin-title tfhb-m-0">
                <div class="tfhb-flexbox tfhb-gap-4">
                    <router-link class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-inline-flexbox" :to="{ name: 'AddonsSettingsEmailTemplates' }">
                        <Icon name="ArrowLeft" :width="20" />
                    </router-link>
                    <h1>{{ $tfhb_trans(typeLabels[type] || type) }}</h1>
                </div>
                <p class="tfhb-capitalize">{{ role }}</p>
            </div>
        </div>

        <div class="tfhb-notification-single tfhb-email-builder tfhb-flexbox tfhb-justify-between tfhb-flexbox-nowrap" v-if="templateData">
            <div class="tfhb-builder-tools">
                <div class="tfhb-template-info tfhb-flexbox tfhb-gap-16 tfhb-mb-32">
                    <div class="tfhb-shortcode-box tfhb-full-width">
                        <HbText
                            v-model="templateData.subject"
                            required="true"
                            :label="$tfhb_trans('Subject')"
                            :placeholder="$tfhb_trans('Enter Mail Subject')"
                        />
                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8">
                            <span
                                class="tfhb-mail-shortcode-badge"
                                v-for="placeholder in placeholdersByType[type]"
                                :key="placeholder"
                                @click="copyPlaceholder(placeholder)"
                            >{{ placeholder }}</span>
                        </div>
                    </div>
                </div>

                <ul ref="parent" class="number-list">
                    <li class="single-tools" v-for="block in sortedBlocks" :key="block.id">
                        <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                            <div class="tfhb-flexbox tfhb-head tfhb-gap-8" @click="toggleBlockOpen(block.id)">
                                <div class="tfhb-icon-drag">
                                    <Icon name="GripVertical" :width="20" />
                                </div>
                                {{ $tfhb_trans(blockTypeLabels[block.type]) }}
                            </div>
                            <HbSwitch v-model="block.status" />
                        </div>

                        <!-- Header -->
                        <div class="tools-content" v-show="openBlocks[block.id] && block.status" v-if="block.type === 'header'">
                            <div class="tfhb-shortcode-box tfhb-full-width">
                                <div class="tfhb-header-logo">
                                    <HbFileUpload
                                        name="logo"
                                        v-model="block.logo"
                                        :label="$tfhb_trans('Choose images or drag & drop it here.')"
                                        :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                                        :btn_label="$tfhb_trans('Upload logo')"
                                        file_size="5"
                                        file_format="jpg,jpeg,png"
                                    />
                                </div>
                                <div class="tfhb-header-bg">
                                    <HbColor v-model="block.background" :label="$tfhb_trans('Header Background')" name="background" selected="1" />
                                </div>
                                <Editor v-model="block.content" :placeholder="$tfhb_trans('Mail Body')" editorStyle="height: 180px" />
                                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8">
                                    <span
                                        class="tfhb-mail-shortcode-badge"
                                        v-for="placeholder in placeholdersByType[type]"
                                        :key="placeholder"
                                        @click="copyPlaceholder(placeholder)"
                                    >{{ placeholder }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Greeting -->
                        <div class="tools-content" v-show="openBlocks[block.id] && block.status" v-if="block.type === 'gratitude'">
                            <div class="tfhb-shortcode-box tfhb-full-width">
                                <Editor v-model="block.content" :placeholder="$tfhb_trans('Mail Body')" editorStyle="height: 180px" />
                                <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8">
                                    <span
                                        class="tfhb-mail-shortcode-badge"
                                        v-for="placeholder in placeholdersByType[type]"
                                        :key="placeholder"
                                        @click="copyPlaceholder(placeholder)"
                                    >{{ placeholder }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="tools-content" v-show="openBlocks[block.id] && block.status" v-if="block.type === 'buttons'">
                            <HbColor v-model="block.border_color" :label="$tfhb_trans('Border Color')" name="border_color" selected="1" />
                            <HbText v-model="block.button_label" :label="$tfhb_trans('Button Label')" />

                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                                        <div class="tfhb-flexbox tfhb-head">{{ $tfhb_trans('Description:') }}</div>
                                        <HbSwitch v-model="block.content.description.status" />
                                    </div>
                                </div>
                                <div class="tools-content" v-show="block.content.description.status">
                                    <Editor v-model="block.content.description.content" :placeholder="$tfhb_trans('Mail Body')" editorStyle="height: 100px" />
                                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8">
                                        <span
                                            class="tfhb-mail-shortcode-badge"
                                            v-for="placeholder in placeholdersByType[type]"
                                            :key="placeholder"
                                            @click="copyPlaceholder(placeholder)"
                                        >{{ placeholder }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                                        <div class="tfhb-flexbox tfhb-head">{{ $tfhb_trans('Button URL:') }}</div>
                                        <HbSwitch v-model="block.content.button.status" />
                                    </div>
                                </div>
                                <div class="tools-content" v-show="block.content.button.status">
                                    <div class="tfhb-shortcode-box tfhb-full-width">
                                        <HbText v-model="block.content.button.content" :placeholder="$tfhb_trans('Button URL:')" />
                                        <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8">
                                            <span
                                                class="tfhb-mail-shortcode-badge"
                                                v-for="placeholder in placeholdersByType[type]"
                                                :key="placeholder"
                                                @click="copyPlaceholder(placeholder)"
                                            >{{ placeholder }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="tools-content" v-show="openBlocks[block.id] && block.status" v-if="block.type === 'footer'">
                            <div class="single-tools">
                                <div class="tfhb-sub-tools tfhb-flexbox tfhb-gap-8">
                                    <div class="tools-heading tfhb-flexbox tfhb-justify-between tfhb-gap-8">
                                        <div class="tfhb-flexbox tfhb-head">{{ $tfhb_trans('Quick Content:') }}</div>
                                        <HbSwitch v-model="block.content.description.status" />
                                    </div>
                                </div>
                                <div class="tools-content" v-show="block.content.description.status">
                                    <Editor v-model="block.content.description.content" :placeholder="$tfhb_trans('Mail Body')" editorStyle="height: 100px" />
                                    <div class="tfhb-mail-shortcode tfhb-flexbox tfhb-gap-8">
                                        <span
                                            class="tfhb-mail-shortcode-badge"
                                            v-for="placeholder in placeholdersByType[type]"
                                            :key="placeholder"
                                            @click="copyPlaceholder(placeholder)"
                                        >{{ placeholder }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>

                <HbButton
                    classValue="tfhb-btn boxed-btn tfhb-mt-24"
                    @click="saveTemplate"
                    :buttonText="$tfhb_trans('Update')"
                    icon="ChevronRight"
                    hover_icon="ArrowRight"
                    :hover_animation="true"
                    :pre_loader="preloader"
                />
            </div>

            <div class="tfhb-email-preview" v-html="emailBodyHtml"></div>
        </div>
    </div>
</template>
