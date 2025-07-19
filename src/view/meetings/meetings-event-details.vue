<script setup>

import { onBeforeMount, onMounted, ref, reactive } from 'vue'; 
import HbQuestion from '@/components/widgets/HbQuestion.vue'
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue'
import Icon from '@/components/icon/LucideIcon.vue' 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbInput from '@/components/form-fields/HbText.vue'
import HbWpFileUpload from '@/components/form-fields/HbWpFileUpload.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue'
import { toast } from "vue3-toastify"; 
import axios from 'axios';
import { useRouter, useRoute, RouterView } from 'vue-router' 

const router = useRouter();
const emit = defineEmits(["update-meeting", "limits-frequency-add"]); 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },
    integrations: {
        type: Object,
        required: true
    },
    formsList: {
        type: Object,
        required: true
    },
    update_preloader: {
        type: Boolean,
        required: true
    }

}); 

// Event details reactive data structure
const event_details = reactive({
    // Basic Event Information 
    event_logo: '',
    event_banner: '',
    
    // Description
    description: '',
    
    // Gallery
    gallery_images: [],
    
    // Video
    video_url: '',
    video_title: '',
    video_description: '',
    
    // Program
    program_items: [
        {
            title: '',
            subtitle: '',
            icon: 'Calendar',
            program_icon: ''
        }
    ],
    
    // Links
    external_links: [
        {
            title: '',
            url: '',
            icon: 'Globe'
        }
    ],
    
    // Contact Information
    contact_info: {
        website: '',
        email: '',
        phone: '',
        location: '',
        social_media: {
            instagram: '',
            facebook: '',
            youtube: '',
            linkedin: ''
        }
    }
});

// Methods for managing dynamic arrays
const addProgramItem = () => {
    event_details.program_items.push({
        title: '',
        subtitle: '',
        icon: 'Calendar',
        program_icon: ''
    });
};

const removeProgramItem = (index) => {
    event_details.program_items.splice(index, 1);
};

const addExternalLink = () => {
    event_details.external_links.push({
        title: '',
        url: '',
        icon: 'Globe'
    });
};

const removeExternalLink = (index) => {
    event_details.external_links.splice(index, 1);
};

// Save event details
const saveEventDetails = async () => {
    try {
        // Show loading state
        props.update_preloader = true;
        
        // Prepare data for API
        const eventDetailsData = {
            meeting_id: props.meetingId,
            event_details: event_details
        };
        
        // API call to save event details
        const response = await axios.post(
            tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/meetings/event-details/update', 
            eventDetailsData, 
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                }
            }
        );
        
        if (response.data.status === true) {
            // Success - emit to parent component
            emit('update-meeting', {
                event_details: event_details
            });
            
            // Show success message
            toast.success(response.data.message || 'Event details saved successfully!', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        } else {
            // Show error message
            toast.error(response.data.message || 'Failed to save event details', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    } catch (error) {
        console.error('Error saving event details:', error);
        
        // Show error message
        toast.error('An error occurred while saving event details', {
            position: 'bottom-right',
            autoClose: 1500,
        });
    } finally {
        // Hide loading state
        props.update_preloader = false;
    }
};

// Initialize with meeting data if available
if (props.meeting && props.meeting.event_details) {
    Object.assign(event_details, props.meeting.event_details);
}

// Fetch event details from API
const fetchEventDetails = async () => {
    try {
        const response = await axios.get(
            tfhb_core_apps.rest_route + `hydra-booking/v1/addons/meetings/event-details/${props.meetingId}`,
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                }
            }
        );
        
        if (response.data.status === true && response.data.event_details) {
            // Update the reactive data with fetched event details
            Object.assign(event_details, response.data.event_details);
        }
    } catch (error) {
        console.error('Error fetching event details:', error);
    }
};
//  on before mount
onBeforeMount(() => {
    fetchEventDetails();
});
</script>

<template>
    <div class="meeting-create-details tfhb-gap-24"> 
        <div class="tfhb-meeting-range tfhb-full-width">
            <div class="tfhb-admin-title tfhb-full-width">
                <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    {{ $tfhb_trans('Event Details') }}
                </h2> 
                <p>{{ $tfhb_trans('Manage the event details and information') }}</p>
            </div> 
        </div>

        <!-- Basic Event Information -->
        <div class="tfhb-section"> 
            <div class="tfhb-flexbox tfhb-gap-16"> 
                <HbWpFileUpload
                    name="event_logo"
                    v-model="event_details.event_logo"
                    :label="$tfhb_trans('Choose image or drag & drop it here.')"
                    :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                    :btn_label="$tfhb_trans('Upload Event Logo')"
                    file_size="5"
                    file_format="jpg,jpeg,png"
                    width="50"
                />
                <HbWpFileUpload
                    name="event_banner"
                    v-model="event_details.event_banner"
                    :label="$tfhb_trans('Choose image or drag & drop it here.')"
                    :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                    :btn_label="$tfhb_trans('Upload Event Banner')"
                    file_size="5"
                    file_format="jpg,jpeg,png"
                    width="50"
                />
            </div>
            
        </div> 

        <!-- Gallery -->
        <div class="tfhb-section">
            <h3>{{ $tfhb_trans('Gallery') }}</h3>
            <HbWpFileUpload
                name="gallery_images"
                v-model="event_details.gallery_images"
                :label="$tfhb_trans('Choose images or drag & drop them here.')"
                :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB each.')"
                :btn_label="$tfhb_trans('Upload Gallery Images')"
                file_size="5"
                file_format="jpg,jpeg,png"
                width="100%"
                :multiple="true"
            />
        </div>

        <!-- Video -->
        <div class="tfhb-section">
            <h3>{{ $tfhb_trans('Video') }}</h3>
            <div class="tfhb-form-grid">
                <HbInput
                    v-model="event_details.video_url"
                    :label="$tfhb_trans('Video URL')"
                    :placeholder="$tfhb_trans('Enter video URL (YouTube, Vimeo, etc.)')"
                />
                <HbInput
                    v-model="event_details.video_title"
                    :label="$tfhb_trans('Video Title')"
                    :placeholder="$tfhb_trans('Enter video title')"
                />
            </div>
            <HbTextarea
                v-model="event_details.video_description"
                :label="$tfhb_trans('Video Description')"
                :placeholder="$tfhb_trans('Enter video description')"
                rows="3"
            />
        </div>

        <!-- Program -->
        <div class="tfhb-section">
            <h3>{{ $tfhb_trans('Program') }}</h3>
            <div v-for="(item, index) in event_details.program_items" :key="index" class="tfhb-program-item">
                <div class="tfhb-flexbox tfhb-gap-16">
                    <HbInput
                        v-model="item.title"
                        :label="$tfhb_trans('Program Title')"
                        :placeholder="$tfhb_trans('Enter program title')"
                        width="50"
                    />
                    <HbInput
                        v-model="item.subtitle"
                        :label="$tfhb_trans('Program Subtitle')"
                        :placeholder="$tfhb_trans('Enter program subtitle')"
                        width="50"
                    />
                    <HbWpFileUpload
                        :name="`program_icon_${index}`"
                        v-model="item.program_icon"
                        :label="$tfhb_trans('Choose image or drag & drop it here.')"
                        :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                        :btn_label="$tfhb_trans('Upload Program Icon')"
                        file_size="5"
                        file_format="jpg,jpeg,png"
                        width="100"
                    />
                    <HbButton
                        v-if="event_details.program_items.length > 1"
                        @click="removeProgramItem(index)"
                        :buttonText="$tfhb_trans('Remove')"
                        classValue="tfhb-btn-danger"
                    />
                </div>
            </div>
            <HbButton
                @click="addProgramItem"
                :buttonText="$tfhb_trans('Add Program Item')"
                icon="Plus"
            />
        </div>

        <!-- External Links -->
        <div class="tfhb-section">
            <h3>{{ $tfhb_trans('External Links') }}</h3>
            <div v-for="(link, index) in event_details.external_links" :key="index" class="tfhb-link-item">
                <div class="tfhb-form-grid">
                    <HbInput
                        v-model="link.title"
                        :label="$tfhb_trans('Link Title')"
                        :placeholder="$tfhb_trans('Enter link title')"
                    />
                    <HbInput
                        v-model="link.url"
                        :label="$tfhb_trans('Link URL')"
                        :placeholder="$tfhb_trans('Enter link URL')"
                        type="url"
                    />
                    <HbButton
                        v-if="event_details.external_links.length > 1"
                        @click="removeExternalLink(index)"
                        :buttonText="$tfhb_trans('Remove')"
                        classValue="tfhb-btn-danger"
                    />
                </div>
            </div>
            <HbButton
                @click="addExternalLink"
                :buttonText="$tfhb_trans('Add External Link')"
                icon="Plus"
            />
        </div>

        <!-- Contact Information -->
        <div class="tfhb-section">
            <h3>{{ $tfhb_trans('Contact Information') }}</h3>
            <div class="tfhb-form-grid">
                <HbInput
                    v-model="event_details.contact_info.website"
                    :label="$tfhb_trans('Website')"
                    :placeholder="$tfhb_trans('Enter website URL')"
                    type="url"
                />
                <HbInput
                    v-model="event_details.contact_info.email"
                    :label="$tfhb_trans('Email')"
                    :placeholder="$tfhb_trans('Enter email address')"
                    type="email"
                />
                <HbInput
                    v-model="event_details.contact_info.phone"
                    :label="$tfhb_trans('Phone')"
                    :placeholder="$tfhb_trans('Enter phone number')"
                    type="tel"
                />
                <HbInput
                    v-model="event_details.contact_info.location"
                    :label="$tfhb_trans('Location')"
                    :placeholder="$tfhb_trans('Enter location address')"
                />
            </div>
            
            <h4>{{ $tfhb_trans('Social Media') }}</h4>
            <div class="tfhb-form-grid">
                <HbInput
                    v-model="event_details.contact_info.social_media.instagram"
                    :label="$tfhb_trans('Instagram')"
                    :placeholder="$tfhb_trans('Enter Instagram URL')"
                    type="url"
                />
                <HbInput
                    v-model="event_details.contact_info.social_media.facebook"
                    :label="$tfhb_trans('Facebook')"
                    :placeholder="$tfhb_trans('Enter Facebook URL')"
                    type="url"
                />
                <HbInput
                    v-model="event_details.contact_info.social_media.youtube"
                    :label="$tfhb_trans('YouTube')"
                    :placeholder="$tfhb_trans('Enter YouTube URL')"
                    type="url"
                />
                <HbInput
                    v-model="event_details.contact_info.social_media.linkedin"
                    :label="$tfhb_trans('LinkedIn')"
                    :placeholder="$tfhb_trans('Enter LinkedIn URL')"
                    type="url"
                />
            </div>
        </div>
     
        <div class="tfhb-submission-btn"> 
            <HbButton  
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="saveEventDetails"
                :buttonText="$tfhb_trans('Save Event Details')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                :pre_loader="props.update_preloader"
            />  
        </div>
    </div>
</template>

<style scoped>
.tfhb-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    width: 100%;
}

.tfhb-section h3 {
    margin-bottom: 1rem;
    color: #374151;
    font-size: 1.125rem;
    font-weight: 600;
}

.tfhb-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
    width: 100%;
}

.tfhb-program-item,
.tfhb-link-item {
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    margin-bottom: 1rem;
    background: #f9fafb;
    width: 100%;
}

.tfhb-btn-danger {
    background-color: #dc2626;
    color: white;
}

.tfhb-btn-danger:hover {
    background-color: #b91c1c;
}

.meeting-create-details {
    width: 100%;
}

.tfhb-meeting-range {
    width: 100%;
}
</style>