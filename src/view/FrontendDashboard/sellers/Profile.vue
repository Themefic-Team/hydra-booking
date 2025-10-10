<script setup>

import { ref, reactive, onBeforeMount, onMounted, computed, watch } from 'vue';
import { __ } from '@wordpress/i18n';
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbText from '@/components/form-fields/HbText.vue'
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbTextarea from '@/components/form-fields/HbTextarea.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbRadio from '@/components/form-fields/HbRadio.vue'
import HbWpFileUpload from '@/components/form-fields/HbWpFileUpload.vue'
import Icon from '@/components/icon/LucideIcon.vue'
import useValidators from '@/store/validator'
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import HbPopup from '@/components/widgets/HbPopup.vue';

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
const { errors, isEmpty } = useValidators();

// --- API-driven user public info ---
const userPublicInformation = reactive({
    cover_image : '',
    avatar : '',
    companey_logo : '',
    description : '',
    // New fields
    staff: [],
    gallery: [],
    video: {
        title: '',
        description: '',
        url: ''
    },
    documents: [],
    links: [],
    social_share: {
        instagram: '',
        facebook: '',
        youtube: '',
        linkedin: ''
    },
    // More details fields will be populated from API
    more_details_fields: []
});
const loading = ref(false);
const skeleton = ref(true);
const saveSuccess = ref(false);
const saveError = ref('');

// --- Edit More Details State ---
const editMoreDetailsForm = reactive({});
const editMoreDetailsLoading = ref(false);
const editMoreDetailsSuccess = ref(false);
const editMoreDetailsError = ref('');
const editMoreDetailsPopup = ref(false);

// Computed property to filter out fields already present in the main profile section
const filteredMoreDetailsFields = computed(() => {
    if (!userPublicInformation.more_details_fields) return [];
    
    const mainProfileFields = [
        'name', 'job_title', 'email', 'telefono_diretto', 'address', 'sito_internet', 'description'
    ];
    
    return userPublicInformation.more_details_fields.filter(field => 
        !mainProfileFields.includes(field.name)
    );
});

// Check if form is ready for submission
const isEditFormReady = computed(() => {
    return Object.keys(editMoreDetailsForm).length > 0;
});

async function fetchUserPublicInfo() {
    loading.value = true;
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/user-public-info', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            },
            params: {
                user_id: tfhb_core_apps.user.id
            },
            withCredentials: true
        });
        if (response.data.success && response.data.data) {
            // Handle the new data structure: { info: {...}, registration_froms_fields: [...] }
            if (response.data.data.info) {
                Object.assign(userPublicInformation, response.data.data.info);
            }
            if (response.data.data.registration_froms_fields) {
                userPublicInformation.more_details_fields = response.data.data.registration_froms_fields;
            }
            skeleton.value = false;
        }
    } catch (e) {
        // handle error
    } finally {
        loading.value = false;
    }
}

async function saveUserPublicInfo() {
    loading.value = true;
    saveSuccess.value = false;
    saveError.value = '';
    try {
        const response = await axios.post(
            tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/user-public-info/update',
            { ...userPublicInformation, user_id: tfhb_core_apps.user.id },
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            }
        );
        if (response.data.success) {
            saveSuccess.value = true;
            toast.success('Profile updated successfully!', { position: "bottom-right" });
        } else {
            saveError.value = response.data.message || 'Failed to save.';
            toast.error(saveError.value, { position: "bottom-right" });
        }
    } catch (e) {
        saveError.value = 'Failed to save.';
        toast.error(saveError.value, { position: "bottom-right" });
    } finally {
        loading.value = false;
        setTimeout(() => { saveSuccess.value = false; }, 2000);
        // windows reload 
        // window.location.reload();
    }
}

// --- Password Change State ---
const passwordForm = reactive({
    current_password: '',
    new_password: '',
    confirm_password: ''
});
const passwordLoading = ref(false);
const passwordSuccess = ref('');
const passwordError = ref('');

async function changePassword() {
    passwordLoading.value = true;
    passwordSuccess.value = '';
    passwordError.value = '';
    try {
        const response = await axios.post(
            tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/change-password',
            { ...passwordForm },
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            }
        );
        if (response.data.success) {
            passwordSuccess.value = response.data.message || 'Password changed successfully!';
            passwordForm.current_password = '';
            passwordForm.new_password = '';
            passwordForm.confirm_password = '';
            toast.success(passwordSuccess.value, { position: "bottom-right" });
        } else {
            passwordError.value = response.data.message || 'Failed to change password.';
            toast.error(passwordError.value, { position: "bottom-right" });
        }
    } catch (e) {
        passwordError.value = 'Failed to change password.';
        toast.error(passwordError.value, { position: "bottom-right" });
    } finally {
        passwordLoading.value = false;
        setTimeout(() => { passwordSuccess.value = ''; }, 3000);
    }
}

// Edit More Details functions
async function saveMoreDetails() {
    editMoreDetailsLoading.value = true;
    editMoreDetailsError.value = '';
    
    try {
        // Create the data to send - only include the fields that were edited
        const dataToSend = {
            user_id: tfhb_core_apps.user.id,
            ...editMoreDetailsForm
        };
        
        const response = await axios.post(
            tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/user-public-info/update',
            dataToSend,
            {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            }
        );
        
        if (response.data.success) {
            editMoreDetailsSuccess.value = true;
            
            // Update the local userPublicInformation with the form values
            Object.keys(editMoreDetailsForm).forEach(fieldName => {
                if (userPublicInformation.hasOwnProperty(fieldName)) {
                    userPublicInformation[fieldName] = editMoreDetailsForm[fieldName];
                }
            });
            
            toast.success('More details updated successfully!', { position: "bottom-right" });
            
            // Close popup after success
            setTimeout(() => {
                closeEditMoreDetails();
            }, 1500);
        } else {
            editMoreDetailsError.value = response.data.message || 'Failed to update more details.';
            toast.error(editMoreDetailsError.value, { position: "bottom-right" });
        }
    } catch (e) {
        console.error('Error saving more details:', e);
        editMoreDetailsError.value = 'Failed to update more details.';
        toast.error(editMoreDetailsError.value, { position: "bottom-right" });
    } finally {
        editMoreDetailsLoading.value = false;
    }
}

function openEditMoreDetails() {
    editMoreDetailsPopup.value = true;
    initializeEditForm();
}

function closeEditMoreDetails() {
    editMoreDetailsPopup.value = false;
    clearEditForm();
}

function initializeEditForm() {
    // Clear the form first
    Object.keys(editMoreDetailsForm).forEach(key => {
        delete editMoreDetailsForm[key];
    });
    
    // Initialize form with current user data
    if (filteredMoreDetailsFields.value.length > 0) {
        filteredMoreDetailsFields.value.forEach(field => {
            const fieldName = field.name;
            const currentValue = userPublicInformation[fieldName];
            
            if (field.type === 'checkbox' || field.type === 'radio') {
                // For checkbox/radio fields, ensure it's an array
                editMoreDetailsForm[fieldName] = Array.isArray(currentValue) ? [...currentValue] : (currentValue ? [currentValue] : []);
            } else {
                // For text, select, textarea fields
                editMoreDetailsForm[fieldName] = currentValue || '';
            }
        });
    }
}

function clearEditForm() {
    Object.keys(editMoreDetailsForm).forEach(key => {
        delete editMoreDetailsForm[key];
    });
    editMoreDetailsError.value = '';
    editMoreDetailsSuccess.value = false;
}

function validateEditForm() {
    // No validation required as per user request
    return true;
}

// Watch for popup open to initialize form
watch(editMoreDetailsPopup, (newVal) => {
    if (newVal) {
        // Small delay to ensure popup is rendered
        setTimeout(() => {
            initializeEditForm();
        }, 100);
    }
});

onMounted(fetchUserPublicInfo);

const imageChange = (attachment) => {   
    userPublicInformation.avatar = attachment.url; 
    const image = document.querySelector('.avatar_display'); 
    image.src = attachment.url; 
    activeProfileDropdown.value = false;
}
const UploadImage = () => {   
    // Create a custom media frame that only shows user's own media
    const mediaUploader = wp.media({
        title: 'Select or Upload Media',
        button: { text: 'Use this media' },
        multiple: false,
        library: {
            type: 'image',
            author: AddonsAuth.loggedInUser.ID || 0
        }
    })

    // Add custom filter to ensure only user's media is shown
    mediaUploader.on('ready', function() {
        // Filter to only show current user's media
        const currentUserId = AddonsAuth.loggedInUser.ID || 0;
        
        // Check if library and props exist before accessing
        if (mediaUploader.library && mediaUploader.library.props && currentUserId) {
            try {
                mediaUploader.library.props.set('author', currentUserId);
            } catch (error) {
                console.warn('Could not set author filter:', error);
            }
        }
    })

    mediaUploader.on('select', function () {
        const attachment = mediaUploader.state().get('selection').first().toJSON()
        imageChange(attachment);
    })

    mediaUploader.open()
} 
const EmptyImage = () => {   
    userPublicInformation.avatar = ''; 
    activeProfileDropdown.value = false;
}
    

const imageChangeFeature = (attachment) => {   
    userPublicInformation.cover_image = attachment.url; 
    const image = document.querySelector('.featured_image_display'); 
    image.src = attachment.url; 
}
const UploadImageFeature  = () => {   
    // Create a custom media frame that only shows user's own media
    const mediaUploader = wp.media({
        title: 'Select or Upload Media',
        button: { text: 'Use this media' },
        multiple: false,
        library: {
            type: 'image',
            author: AddonsAuth.loggedInUser.ID || 0
        }
    })

    // Add custom filter to ensure only user's media is shown
    mediaUploader.on('ready', function() {
        // Filter to only show current user's media
        const currentUserId = AddonsAuth.loggedInUser.ID || 0;
        
        // Check if library and props exist before accessing
        if (mediaUploader.library && mediaUploader.library.props && currentUserId) {
            try {
                mediaUploader.library.props.set('author', currentUserId);
            } catch (error) {
                console.warn('Could not set author filter:', error);
            }
        }
    })

    mediaUploader.on('select', function () {
        const attachment = mediaUploader.state().get('selection').first().toJSON()
        imageChangeFeature(attachment);
    })

    mediaUploader.open()
}


const EmptyImageFeatured  = () => {
    userPublicInformation.cover_image = '';
} 
// Profile Image and cover image dropdown
const activeCoverDropdown = ref(false);
const activeProfileDropdown = ref(false);

// hide activeCoverDropdown when clicked outside
document.addEventListener('click', (e) => { 
    if ( !e.target.closest('.edit-profile-image')) { 
        activeProfileDropdown.value = false;
    }
    if ( !e.target.closest('.edit-cover-image')) { 
        activeCoverDropdown.value = false;
    }
});
</script>

<template>   
    <div :class="{ 'tfhb-skeleton': skeleton }" class="tfhb-admin-card-box tfhb-host-profile-image-wrap">
    
    <div class="tfhb-admin-card-box tfhb-host-profile-image-wrap"   
        :style="{
            'background-image': `url('${userPublicInformation.cover_image || $tfhb_url + '/assets/app/images/meeting-cover.png'}')`, 
        }"
    >
        <span class="tfhb-profile-overlay"></span>
    
        <div class="tfhb-single-form-field-wrap avatar_display-wrap tfhb-flexbox" >
            
            <div   class="tfhb-field-image" > 
                <div  class="tfhb-dropdown edit-profile-image">  
                    <span  @click="activeProfileDropdown = !activeProfileDropdown"> <Icon name="Edit" size=16 /></span> 
                    <transition  name="tfhb-dropdown-transition">
                        <div v-if="activeProfileDropdown" class="tfhb-dropdown-wrap"> 
                            <span class="tfhb-dropdown-single"  @click="UploadImage" > <Icon name="Upload" size=20 /> {{ $tfhb_trans('Upload image') }}</span>
                    
                            <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="EmptyImage" ><Icon name="Trash2" size=20 />{{ $tfhb_trans('Delete') }}</span>
                        </div>
                    </transition>
                </div>
                <img   class='avatar_display'  :src="userPublicInformation.avatar || $tfhb_url+'/assets/images/avator.png'" >
            </div>
            <div class="tfhb-image-box-content">  
            <h4 v-if="label !=''" :for="name">{{ $tfhb_trans('Profile image') }} <span  v-if="required == 'true'"> *</span> </h4>
            <p v-if="description !=''"  class="tfhb-m-0">{{ $tfhb_trans('Recommended Image Size: 120x120px') }}</p>
            </div>
        </div> 
        <div  class="tfhb-dropdown edit-cover-image"> 
            <HbButton 
                classValue="tfhb-btn secondary-btn flex-btn"  
                :buttonText="$tfhb_trans('Edit cover image')"
                icon="Edit"   
                @click="activeCoverDropdown =!activeCoverDropdown"
                icon_position="left"  
            /> 
            <transition  name="tfhb-dropdown-transition">
                <div v-if="activeCoverDropdown" class="tfhb-dropdown-wrap"> 
                    <span class="tfhb-dropdown-single" @click="UploadImageFeature" > <Icon name="Upload" size=20  /> {{ $tfhb_trans('Upload image') }}</span>
            
                    <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="EmptyImageFeatured"  ><Icon name="Trash2" size=20 />{{ $tfhb_trans('Delete') }}</span>
                </div>
            </transition>
        </div>
    </div>

    <div class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('Public Information') }}    </h2>  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">  
        <HbText  
            v-model="userPublicInformation.denominazione_operatore_azienda"  
            required= "true"  
            :label="$tfhb_trans('Company Name')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your company name')" 
            width="50"
        /> 
        <HbText  
            v-model="userPublicInformation.name"  
            :label="$tfhb_trans('Parsonal Name')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type personal name')" 
            width="50"
        />  
        <HbText  
            v-model="userPublicInformation.email"  
            required= "true"  
            :label="$tfhb_trans('Email')"  
            selected = "1"
             :placeholder="$tfhb_trans('Type your email')" 
            width="50"
            disabled="true"
        /> 
        <HbText  
            v-model="userPublicInformation.telefono_diretto"   
            :label="$tfhb_trans('Mobile')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your mobile no')" 
            width="50"  
        />  
        
        <HbText  
            v-model="userPublicInformation.address"   
            :label="$tfhb_trans('Address')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your Address')" 
            width="50"  
        />     
        <HbText  
            v-model="userPublicInformation.sito_internet"   
            :label="$tfhb_trans('Website')"  
            selected = "1"
            :placeholder="$tfhb_trans('Type your website')" 
            width="50"  
        />  
        <HbTextarea  
            v-model="userPublicInformation.description"   
            :label="$tfhb_trans('Description')"  
            :placeholder="$tfhb_trans('Type your description')" 
            width="50"  
        />  

        <div class="companey_logo tfhb-full-width">
            <label for="companey_logo_icon">Company Logo</label>
            <HbWpFileUpload
                :name="`companey_logo_icon`"
                v-model="userPublicInformation.companey_logo"
                :label="$tfhb_trans('Company Logo')"
                :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                :btn_label="$tfhb_trans('Upload Company Logo')"
                file_size="5"
                file_format="jpg,jpeg,png"
                width="100"
            />
        </div>
        
        <!-- New Sections Parent Card -->
        <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">
            <div class="tfhb-admin-title">
                <h2>{{ $tfhb_trans('Additional Information') }}</h2>
                <p>{{ $tfhb_trans('Manage your staff, gallery, video, documents, links, and social media') }}</p>
            </div>
            
            <!-- Staff Section -->
            <div class="tfhb-section-container">
                <div class="tfhb-section-title">
                    <h3>{{ $tfhb_trans('Staff') }}</h3>
                </div>
                <div class="tfhb-staff-section">
                    <div v-for="(member, index) in userPublicInformation.staff" :key="index" class="tfhb-staff-item tfhb-flexbox tfhb-gap-16">
                        <HbText  
                            v-model="member.name"  
                            :label="$tfhb_trans('Name')"  
                            :placeholder="$tfhb_trans('Staff member name')" 
                            width="50"
                        /> 
                        <HbText  
                            v-model="member.position"  
                            :label="$tfhb_trans('Position')"  
                            :placeholder="$tfhb_trans('Job position')" 
                            width="50"
                        />
                        <HbWpFileUpload
                            :name="`staff_image_${index}`"
                            v-model="member.image"
                            :label="$tfhb_trans('Staff Image')"
                            :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                            :btn_label="$tfhb_trans('Upload Staff Image')"
                            file_size="5"
                            file_format="jpg,jpeg,png"
                            width="100"
                        />
                        <HbButton 
                            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                            @click="userPublicInformation.staff.splice(index, 1)"
                            :buttonText="$tfhb_trans('Remove')"
                            icon="Trash2"
                        />
                    </div>
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="userPublicInformation.staff.push({name: '', position: '', image: ''})"
                        :buttonText="$tfhb_trans('Add Staff Member')"
                        icon="UserPlus"
                        hover_icon="UserPlus"
                        :hover_animation="true"
                    />
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="tfhb-section-container">
                <div class="tfhb-section-title">
                    <h3>{{ $tfhb_trans('Gallery') }}</h3>
                </div>
                <div class="tfhb-gallery-section ">
                    <div v-for="(image, index) in userPublicInformation.gallery" :key="index" class="tfhb-gallery-item tfhb-flexbox tfhb-gap-16">
                        <HbWpFileUpload
                            :name="`gallery_image_${index}`"
                            v-model="image.url"
                            :label="$tfhb_trans('Gallery Image')"
                            :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                            :btn_label="$tfhb_trans('Upload Gallery Image')"
                            file_size="5"
                            file_format="jpg,jpeg,png"
                            width="100"
                        />
                        <HbText  
                            v-model="image.title"  
                            :label="$tfhb_trans('Title')"  
                            :placeholder="$tfhb_trans('Image title')" 
                            width="100"
                        />
                        <HbButton 
                            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                            @click="userPublicInformation.gallery.splice(index, 1)"
                            :buttonText="$tfhb_trans('Remove')"
                            icon="Trash2"
                        />
                    </div>
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="userPublicInformation.gallery.push({url: '', title: ''})"
                        :buttonText="$tfhb_trans('Add Gallery Image')"
                        icon="ImagePlus"
                        hover_icon="ImagePlus"
                        :hover_animation="true"
                    />
                </div>
            </div>

            <!-- Video Section -->
            <div class="tfhb-section-container">
                <div class="tfhb-section-title">
                    <h3>{{ $tfhb_trans('Video') }}</h3>
                </div>
                <HbText  
                    v-model="userPublicInformation.video.title"  
                    :label="$tfhb_trans('Video Title')"  
                    :placeholder="$tfhb_trans('Enter video title')" 
                    width="100"
                />
                <HbTextarea  
                    v-model="userPublicInformation.video.description"   
                    :label="$tfhb_trans('Video Description')"  
                    :placeholder="$tfhb_trans('Enter video description')" 
                    width="100"  
                />
                <HbText  
                    v-model="userPublicInformation.video.url"  
                    :label="$tfhb_trans('Video URL')"  
                    :placeholder="$tfhb_trans('Enter video URL (YouTube, Vimeo, etc.)')" 
                    width="100"
                />
            </div>

            <!-- Documents Section -->
            <div class="tfhb-section-container">
                <div class="tfhb-section-title">
                    <h3>{{ $tfhb_trans('Documents') }}</h3>
                </div>
                <div class="tfhb-documents-section  ">
                    <div v-for="(doc, index) in userPublicInformation.documents" :key="index" class="tfhb-document-item tfhb-flexbox tfhb-gap-16">
                        <HbText  
                            v-model="doc.title"  
                            :label="$tfhb_trans('Document Title')"  
                            :placeholder="$tfhb_trans('Document title')" 
                            width="50"
                        />
                        <HbText  
                            v-model="doc.subtitle"  
                            :label="$tfhb_trans('Subtitle')"  
                            :placeholder="$tfhb_trans('Document subtitle')" 
                            width="50"
                        />
                        <HbWpFileUpload
                            :name="`document_icon_${index}`"
                            v-model="doc.icon"
                            :label="$tfhb_trans('Document Icon')"
                            :subtitle="$tfhb_trans('JPG, JPEG, PNG. Max 5 MB.')"
                            :btn_label="$tfhb_trans('Upload Document Icon')"
                            file_size="5"
                            file_format="jpg,jpeg,png"
                            width="50"
                        />
                        <HbWpFileUpload
                            :name="`document_file_${index}`"
                            v-model="doc.url"
                            :label="$tfhb_trans('Document File')"
                            :subtitle="$tfhb_trans('PDF, DOC, DOCX, XLS, XLSX. Max 10 MB.')"
                            :btn_label="$tfhb_trans('Upload Document File')"
                            file_size="10"
                            file_format="pdf,doc,docx,xls,xlsx"
                            width="50"
                        />
                        <!-- <HbText  
                            v-model="doc.size"  
                            :label="$tfhb_trans('File Size')"  
                            :placeholder="$tfhb_trans('e.g., 900 kb')" 
                            width="50"
                        /> -->
                        <HbButton 
                            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                            @click="userPublicInformation.documents.splice(index, 1)"
                            :buttonText="$tfhb_trans('Remove')"
                            icon="Trash2"
                        />
                    </div>
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="userPublicInformation.documents.push({title: '', subtitle: '', icon: '', url: '', size: ''})"
                        :buttonText="$tfhb_trans('Add Document')"
                        icon="FileText"
                        hover_icon="FileText"
                        :hover_animation="true"
                    />
                </div>
            </div>

            <!-- Links Section -->
            <div class="tfhb-section-container">
                <div class="tfhb-section-title">
                    <h3>{{ $tfhb_trans('Links') }}</h3>
                </div>
                <div class="tfhb-links-section">
                    <div v-for="(link, index) in userPublicInformation.links" :key="index" class="tfhb-link-item tfhb-flexbox tfhb-gap-16">
                        <HbText  
                            v-model="link.title"  
                            :label="$tfhb_trans('Link Title')"  
                            :placeholder="$tfhb_trans('Link title')" 
                            width="50"
                        />
                        <HbText  
                            v-model="link.url"  
                            :label="$tfhb_trans('Link URL')"  
                            :placeholder="$tfhb_trans('https://example.com')" 
                            width="50"
                        />
                        <HbButton 
                            classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                            @click="userPublicInformation.links.splice(index, 1)"
                            :buttonText="$tfhb_trans('Remove')"
                            icon="Trash2"
                        />
                    </div>
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                        @click="userPublicInformation.links.push({title: '', url: ''})"
                        :buttonText="$tfhb_trans('Add Link')"
                        icon="Link"
                        hover_icon="Link"
                        :hover_animation="true"
                    />
                </div>
            </div>

            <!-- Social Share Section -->
            <div class="tfhb-section-container">
                <div class="tfhb-section-title">
                    <h3>{{ $tfhb_trans('Social Media') }}</h3>
                </div>
                <div class="tfhb-flexbox tfhb-gap-16">
                    <HbText  
                        v-model="userPublicInformation.social_share.instagram"  
                        :label="$tfhb_trans('Instagram')"  
                        :placeholder="$tfhb_trans('Instagram profile URL')" 
                        width="50"
                    />
                    <HbText  
                        v-model="userPublicInformation.social_share.facebook"  
                        :label="$tfhb_trans('Facebook')"  
                        :placeholder="$tfhb_trans('Facebook profile URL')" 
                        width="50"
                    />
                    <HbText  
                        v-model="userPublicInformation.social_share.youtube"  
                        :label="$tfhb_trans('YouTube')"  
                        :placeholder="$tfhb_trans('YouTube channel URL')" 
                        width="50"
                    />
                    <HbText  
                        v-model="userPublicInformation.social_share.linkedin"  
                        :label="$tfhb_trans('LinkedIn')"  
                        :placeholder="$tfhb_trans('LinkedIn profile URL')" 
                        width="50"
                    />
                </div>
            </div>
        </div>
         <HbButton 
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="saveUserPublicInfo"
            :buttonText="$tfhb_trans('Save & Continue')"
            icon="ChevronRight" 
            hover_icon="ArrowRight" 
            :hover_animation="true"
            :pre_loader="loading"
        />  
    </div>   

    <div class="tfhb-admin-title tfhb-flexbox tfhb-justify-between" >
        <h2>{{ $tfhb_trans('More Details') }}    </h2>  
        <HbButton 
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="openEditMoreDetails"
            :buttonText="$tfhb_trans('Edit More Details')" 
            :hover_animation="true" 
        />  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox" style="flex-direction:column;">
    
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Alternative Company Name') }}:</strong>
        <span>{{ userPublicInformation['eventuale_altra_denominazione'] }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('VAT Number') }}:</strong>
        <span>{{ userPublicInformation['pi_cf'] }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Activity Area') }}:</strong>
        <span>{{ userPublicInformation.ambito_di_attività }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Specialization') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.specializzazione)">
            {{ userPublicInformation.specializzazione.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.specializzazione }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Website') }}:</strong>
        <span>{{ userPublicInformation['sito_internet'] }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Buyer Interest Origin') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.provenienza_Buyer_interesse)">
            {{ userPublicInformation.provenienza_Buyer_interesse.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.provenienza_Buyer_interesse }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Region') }}:</strong>
        <span>{{ userPublicInformation.regione }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Name') }}:</strong>
        <span>{{ userPublicInformation.name }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Position') }}:</strong>
        <span>{{ userPublicInformation.incarico }}</span>
      </div> 
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Direct Phone') }}:</strong>
        <span>{{ userPublicInformation.telefono_diretto }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Second Participant') }}:</strong>
        <span>
          <template v-if="Array.isArray(userPublicInformation.eventuale_secondo_partecipante)">
            {{ userPublicInformation.eventuale_secondo_partecipante.join(', ') }}
          </template>
          <template v-else>
            {{ userPublicInformation.eventuale_secondo_partecipante }}
          </template>
        </span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Second Seller Name') }}:</strong>
        <span>{{ userPublicInformation.nome_econdo_seller_partecipante }}</span>
      </div>
      <div class="tfhb-info-row">
        <strong>{{ $tfhb_trans('Second Position') }}:</strong>
        <span>{{ userPublicInformation.incarico_2 }}</span>
      </div>
      

    </div>

    <!-- Edit More Details Popup -->
    <HbPopup 
      :isOpen="editMoreDetailsPopup"
      @modal-close="closeEditMoreDetails"
      max_width="800px"
      name="first-modal"
      gap="24px"
      class="tfhb-booking-calendar-popup"
    >
        <template #header> 
            <h3>{{ $tfhb_trans('Edit More Details') }}</h3>
        </template>

        <template #content> 
            <div class="tfhb-edit-more-details-form">
                <!-- Success/Error Messages -->
                <div v-if="editMoreDetailsSuccess" class="tfhb-success-message">
                    {{ $tfhb_trans('More details updated successfully!') }}
                </div>
                <div v-if="editMoreDetailsError" class="tfhb-error-message">
                    {{ editMoreDetailsError }}
                </div>
                
                <!-- Dynamic Form Fields -->
                <div class="tfhb-form-fields-container">
                    <div v-if="!isEditFormReady && editMoreDetailsPopup" class="tfhb-loading-message">
                        {{ $tfhb_trans('Loading form fields...') }}
                    </div>
                    <div v-else-if="filteredMoreDetailsFields.length === 0" class="tfhb-no-fields-message">
                        {{ $tfhb_trans('No additional fields available for editing.') }}
                    </div>
                    <div v-else v-for="field in filteredMoreDetailsFields" :key="field.name" class="tfhb-form-field">
                        <label :for="field.name" class="tfhb-field-label">
                            {{ field.label }}
                        </label>
                        
                        <!-- Text Input -->
                        <div v-if="field.type === 'text' || field.type === 'email' || field.type === 'phone'" class="tfhb-field-input">
                            <input 
                                :type="field.type === 'phone' ? 'text' : field.type"
                                :id="field.name"
                                v-model="editMoreDetailsForm[field.name]"
                                :placeholder="field.placeholder"
                                class="tfhb-input"
                            />
                        </div>
                        
                        <!-- Select Dropdown -->
                        <div v-else-if="field.type === 'select'" class="tfhb-field-input">
                            <select 
                                :id="field.name"
                                v-model="editMoreDetailsForm[field.name]"
                                class="tfhb-select"
                            >
                                <option value="">{{ $tfhb_trans('Select an option') }}</option>
                                <option v-for="option in field.options" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        
                        <!-- Checkbox Group -->
                        <div v-else-if="field.type === 'checkbox'" class="tfhb-field-input">
                            <div class="tfhb-checkbox-group">
                                <label v-for="option in field.options" :key="option" class="tfhb-checkbox-item">
                                    <input 
                                        type="checkbox"
                                        :value="option"
                                        v-model="editMoreDetailsForm[field.name]"
                                        class="tfhb-checkbox"
                                    />
                                    <span class="tfhb-checkbox-label">{{ option }}</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Radio Group -->
                        <div v-else-if="field.type === 'radio'" class="tfhb-field-input">
                            <div class="tfhb-radio-group">
                                <label v-for="option in field.options" :key="option" class="tfhb-radio-item">
                                    <input 
                                        type="radio"
                                        :name="field.name"
                                        :value="option"
                                        v-model="editMoreDetailsForm[field.name]"
                                        class="tfhb-radio"
                                    />
                                    <span class="tfhb-radio-label">{{ option }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="tfhb-form-actions">
                    <HbButton 
                        classValue="tfhb-btn secondary-btn"
                        @click="closeEditMoreDetails"
                        :buttonText="$tfhb_trans('Cancel')"
                        :disabled="editMoreDetailsLoading"
                    />
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation"
                        @click="saveMoreDetails"
                        :buttonText="$tfhb_trans('Save Changes')"
                        icon="Save"
                        :pre_loader="editMoreDetailsLoading"
                        :disabled="editMoreDetailsLoading || !isEditFormReady"
                    />
                </div>
                
            </div>
        </template>
    </HbPopup>
    
    <div class="tfhb-admin-title" >
        <h2>{{ $tfhb_trans('Change Password') }}    </h2>  
    </div>
    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-mb-24">
        <HbText
            v-model="passwordForm.current_password"
            :label="$tfhb_trans('Current Password')"
            type="password"
            required="true"
            width="32"
        />
        <HbText
            v-model="passwordForm.new_password"
            :label="$tfhb_trans('New Password')"
            type="password"
            required="true"
            width="32"
        />
        <HbText
            v-model="passwordForm.confirm_password"
            :label="$tfhb_trans('Confirm New Password')"
            type="password"
            required="true"
            width="32"
        />
    </div>
    <HbButton
        classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation"
        @click="changePassword"
        :buttonText="$tfhb_trans('Change Password')"
        icon="Lock"
        :pre_loader="passwordLoading"
    />
   
    </div>
</template>


 
<style scoped lang="scss"> 
.tfhb-host-profile-image-wrap{
    max-width: 1024px;
    margin: 0 auto;
}
/* Your component styles go here */ 
.avatar_display-wrap, .featured_image_display-wrap {
  position: relative;

  .tfhb-image-field-close {
    position: absolute;
    top: 0;
    left: 54px;
    padding: 2px;
    border-radius: 50%;
    height: 20px;
    width: 20px;
    background-color: #FEECEE;
    z-index: 1;
    text-align: center;
    color: #AC0C22 !important;
    cursor: pointer;
    transition: 0.4s;
        &:hover {
            background-color: #AC0C22;
            color: #fff !important;
        }
    }

    .tfhb-field-image{
        position: relative;
        .tfhb-image-field-close{
            left: auto;
            right: 4px;
            top: 4px;
            margin: 0 !important;
            
        }
    }
}

.featured_image_display-wrap .tfhb-field-image { 
  max-width: 300px;
  width: auto;
  border-radius: 8px;
}
.featured_image_display-wrap .tfhb-field-image {
    .featured_image_display{ 
        border-radius: 8px;
    } 
}

/* New section styles */
.tfhb-section-title {
  margin: 2rem 0 1rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e3e6ed;
}

.tfhb-section-title h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
}

.tfhb-staff-section,
.tfhb-gallery-section,
.tfhb-documents-section,
.tfhb-links-section {
  margin-bottom: 2rem;
}

/* Form grid layout */
.tfhb-admin-card-box.tfhb-flexbox {
  display: flex; 
  gap: 1.5rem;
  align-items: stretch;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 100%;
}

/* Section containers */
.tfhb-section-container {
  background: #f8f9fb;
  border: 1px solid #e3e6ed;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  width: 100%;
}

.tfhb-section-container:last-child {
  margin-bottom: 0;
}

/* Section title styling */
.tfhb-section-title {
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e3e6ed;
}

.tfhb-section-title h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
}

/* Form field width override - make all fields 50% width */
.tfhb-admin-card-box .tfhb-single-form-field-wrap,
.tfhb-single-form-field-wrap {
  width: 50% !important;
  margin-bottom: 1.5rem;
}

/* Make textarea fields full width */
.tfhb-single-form-field-wrap textarea,
.tfhb-admin-card-box .tfhb-single-form-field-wrap textarea {
  width: 100% !important;
}

/* Remove old button styles since we're using the standard button classes */
.tfhb-staff-item,
.tfhb-gallery-item,
.tfhb-document-item,
.tfhb-link-item {
  background: #ffffff;
  border: 1px solid #e3e6ed;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  position: relative;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.tfhb-staff-item:hover,
.tfhb-gallery-item:hover,
.tfhb-document-item:hover,
.tfhb-link-item:hover {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  border-color: #cbd5e0;
}

// .tfhb-staff-item .tfhb-btn,
// .tfhb-gallery-item .tfhb-btn,
// .tfhb-document-item .tfhb-btn,
// .tfhb-link-item .tfhb-btn {
//   position: absolute;
//   top: 1rem;
//   right: 1rem;
//   min-width: auto;
//   padding: 0.5rem 1rem;
//   font-size: 0.875rem;
// }

/* Responsive design */
@media (max-width: 768px) {
  .tfhb-admin-card-box.tfhb-flexbox {
    flex-direction: column;
    padding: 1.5rem;
  }
  
  .tfhb-staff-item,
  .tfhb-gallery-item,
  .tfhb-document-item,
  .tfhb-link-item {
    padding: 1rem;
  }
  
  .tfhb-section-container {
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .tfhb-section-title h3 {
    font-size: 1.125rem;
  }
  
  /* Make all fields full width on mobile */
  .tfhb-admin-card-box .tfhb-single-form-field-wrap,
  .tfhb-single-form-field-wrap {
    width: 100% !important;
  }
}

.tfhb-info-row {
  display: flex;
  align-items: flex-start;
  width: 100%;
  padding: 12px 18px;
  background: #f8f9fb;
  border-radius: 6px;
  border: 1px solid #e3e6ed;
  box-shadow: 0 1px 2px rgba(30,40,90,0.04);
  transition: background 0.2s;
  box-sizing: border-box;
}
.tfhb-info-row strong {
  min-width: 240px;
  font-weight: 600;
  color: #2d3748;
  margin-right: 12px;
  line-height: 1.5;
}
.tfhb-info-row span {
  flex: 1;
  color: #4a5568;
  word-break: break-word;
  line-height: 1.5;
}
.tfhb-info-row:hover {
  background: #eef2f7;
}

/* Edit More Details Styles */
.tfhb-edit-more-details-btn {
  margin-top: 20px;
  text-align: center;
}

.tfhb-form-fields-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.tfhb-form-field {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.tfhb-field-label {
  font-weight: 700;
  width: 280px;
  text-align: left;
  padding-right: 16px;
}

.tfhb-field-input {
  flex: 1;
  min-width: 0;
}

.tfhb-input,
.tfhb-select,
.tfhb-textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.tfhb-input:focus,
.tfhb-select:focus,
.tfhb-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.tfhb-checkbox-group,
.tfhb-radio-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tfhb-checkbox-item,
.tfhb-radio-item {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.tfhb-checkbox-label,
.tfhb-radio-label {
  font-size: 14px;
  color: #374151;
  cursor: pointer;
}

.tfhb-form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #f0f0f0;
}

.tfhb-success-message {
  background: #d1fae5;
  color: #065f46;
  padding: 12px 16px;
  border-radius: 6px;
  border: 1px solid #a7f3d0;
  margin: 16px 0;
}

.tfhb-error-message {
  background: #fee2e2;
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 6px;
  border: 1px solid #fca5a5;
  margin: 16px 0;
}

.tfhb-no-fields-message {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
  font-style: italic;
}

.tfhb-loading-message {
  text-align: center;
  padding: 24px 16px;
  color: #3b82f6;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .tfhb-form-field {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .tfhb-field-label {
    width: 100%;
    padding-right: 0;
    padding-bottom: 8px;
  }
  
  .tfhb-field-input {
    width: 100%;
  }
  
  .tfhb-form-actions {
    flex-direction: column;
  }
  
  .tfhb-form-actions .tfhb-btn {
    width: 100%;
  }
}
 
</style>
