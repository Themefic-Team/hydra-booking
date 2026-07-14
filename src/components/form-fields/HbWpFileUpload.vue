<script setup>
import { ref, watch, defineEmits } from 'vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify";
import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
const props = defineProps([
    'name', 
    'modelValue', 
    'required', 
    'label', 
    'btn_label', 
    'width',
    'subtitle', 'placeholder', 'description', 'disabled',
    'file_size', // 5 MB
    'file_format', // jpg, jpeg, png
    'multiple' // true for multiple files
])
const emit = defineEmits(['update:modelValue']);
const imageUrl = ref(props.multiple ? [] : props.modelValue);
const dragOver = ref(false);
const fileName = ref('');
const isUploading = ref(false);
const uploadProgress = ref(0);
const MAX_FILE_SIZE = props.file_size * 1024 * 1024 // Convert MB to bytes
const ALLOWED_FORMATS = props.file_format != '' ?  props.file_format.split(',') : '';

// Get current user ID from WordPress
const getCurrentUserId = () => {
    if (window.wpApiSettings && window.wpApiSettings.currentUser) {
        return window.wpApiSettings.currentUser.id;
    }
    // Fallback: try to get from wp_localize_script data
    if (window.hydraBookingData && window.hydraBookingData.currentUserId) {
        return window.hydraBookingData.currentUserId;
    }
    return 0;
}

const extractFileName = (url) => url ? url.split('/').pop().split('?')[0] : '';

const isImageFile = (url) => {
    if (!url) return false;
    const extension = url.split('.').pop().toLowerCase();
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    return imageExtensions.includes(extension);
};

const getFileIcon = (url) => {
    if (!url) return 'File';
    
    const extension = url.split('.').pop().toLowerCase();
    
    // Document icons
    if (['pdf'].includes(extension)) return 'FileText';
    if (['doc', 'docx'].includes(extension)) return 'FileText';
    if (['xls', 'xlsx'].includes(extension)) return 'FileSpreadsheet';
    if (['ppt', 'pptx'].includes(extension)) return 'Presentation';
    
    // Archive icons
    if (['zip', 'rar', '7z'].includes(extension)) return 'Archive';
    
    // Video icons
    if (['mp4', 'avi', 'mov', 'wmv'].includes(extension)) return 'Video';
    
    // Audio icons
    if (['mp3', 'wav', 'ogg'].includes(extension)) return 'Music';
    
    // Default file icon
    return 'File';
};

const validateFile = (file) => {  
    if (file.size > MAX_FILE_SIZE) {
        toast.error(`File size exceeds ${props.file_size}MB limit.`, {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });  
        return false
    }
    
    const fileExt = file.name.split('.').pop().toLowerCase();
    if (!ALLOWED_FORMATS.includes(fileExt)) {
        toast.error(`Invalid file format. Allowed formats: ${props.file_format}`, {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        });   
        return false
    }
    return true
} 
const imageChange = (attachment, type) => {  
    const url = type == 'clicked' ? attachment.url : attachment.source_url;
    
    if (props.multiple) {
        // For multiple files, add to array
        if (!imageUrl.value) imageUrl.value = [];
        imageUrl.value.push(url);
        emit('update:modelValue', [...imageUrl.value]);
    } else {
        // For single file, replace value
        imageUrl.value = url;
        fileName.value = extractFileName(url);
        emit('update:modelValue', url);
    }
    
    isUploading.value = false;
    uploadProgress.value = 0;
}

const UploadImage = () => {   
    if (isUploading.value) return
    
    const currentUserId = AddonsAuth.loggedInUser.ID;
    
    // Determine media type based on file format
    const getMediaType = () => {
        if (!props.file_format) return 'image';
        
        const formats = props.file_format.toLowerCase().split(',');
        const imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        const videoFormats = ['mp4', 'avi', 'mov', 'wmv'];
        const audioFormats = ['mp3', 'wav', 'ogg'];
        
        if (formats.some(format => imageFormats.includes(format.trim()))) {
            return 'image';
        } else if (formats.some(format => videoFormats.includes(format.trim()))) {
            return 'video';
        } else if (formats.some(format => audioFormats.includes(format.trim()))) {
            return 'audio';
        } else {
            return ''; // Empty string means all file types
        }
    };
    
    const mediaType = getMediaType();
    const titleText = props.multiple ? 'Upload Files' : 'Upload File';
    const buttonText = props.multiple ? 'Use these files' : 'Use this file';
    
    const mediaUploader = wp.media({
        title: titleText,
        button: { text: buttonText },
        multiple: props.multiple ? 'add' : false,
        // Filter to show only current user's media
        library: {
            type: mediaType,
            author: currentUserId
        }
    })

    mediaUploader.on('select', function () {
        const selection = mediaUploader.state().get('selection');
        
        if (props.multiple) {
            // Handle multiple files
            selection.each(function(attachment) {
                imageChange(attachment.toJSON(), 'clicked');
            });
        } else {
            // Handle single file
            const attachment = selection.first().toJSON();
            imageChange(attachment, 'clicked');
        }
    })

    mediaUploader.open()
}

const handleDrop = async (event) => {
    event.preventDefault()
    dragOver.value = false
    
    const files = Array.from(event.dataTransfer.files);
    
    if (props.multiple) {
        // Handle multiple files
        for (const file of files) {
            if (validateFile(file)) {
                await uploadFileToWordPress(file);
            }
        }
    } else {
        // Handle single file
        const file = files[0];
        if (file && validateFile(file)) {
            await uploadFileToWordPress(file);
        }
    }
}

const uploadFileToWordPress = async (file) => {
    isUploading.value = true
    uploadProgress.value = 0
    const formData = new FormData()
    formData.append('file', file)
    
    // Add author parameter to associate file with current user
    const currentUserId = getCurrentUserId();
    if (currentUserId > 0) {
        formData.append('author', currentUserId)
    }

    try {
        const response = await fetch(`${window.wpApiSettings.root}wp/v2/media`, {
            method: 'POST',
            headers: { 'X-WP-Nonce': window.wpApiSettings.nonce },
            body: formData
        })

        if (!response.ok) throw new Error('Upload failed')

        const result = await response.json()
        imageChange(result, 'dragDrop') 
    } catch (error) {
        console.error('Error uploading file:', error)
        isUploading.value = false
    }
}

const removeImage = (index = null) => {
    if (props.multiple) {
        // Remove specific image from array
        if (index !== null) {
            imageUrl.value.splice(index, 1);
            emit('update:modelValue', [...imageUrl.value]);
        } else {
            // Clear all images
            imageUrl.value = [];
            emit('update:modelValue', []);
        }
    } else {
        // Single file mode
        imageUrl.value = '';
        fileName.value = '';
        emit('update:modelValue', '');
    }
}

watch(() => props.modelValue, (newVal) => {
    if (props.multiple) {
        imageUrl.value = Array.isArray(newVal) ? newVal : [];
    } else {
        imageUrl.value = newVal;
        fileName.value = extractFileName(newVal);
    }
}) 
</script>

<template> 
<!-- {{ AddonsAuth.loggedInUser.ID }} -->
  <div class="tfhb-single-form-field tfhb-file-upload tfhb-flexbox" :class="name" 
      :style="{ 'width': width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
  >

    <div 
      class="tfhb-single-form-field-wrap tfhb-flexbox tfhb-file-upload-wrap tfhb-flexbox tfhb-full-width tfhb-gap-8"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop="handleDrop"
    >
        <div class="upload-drag-drop-wrap tfhb-full-width tfhb-flexbox tfhb-gap-16" :class="{ 'drag-over': dragOver }">
            <HbButton 
                classValue="tfhb-btn secondary-btn flex-btn"  
                :buttonText="isUploading ? 'Uploading...' : (props.btn_label ? props.btn_label : 'Upload File')"
                icon="Upload"   
                icon_position="left"
                :disabled="isUploading"
                @click="UploadImage()"
            />  

            <div v-if="props.label" class="tfhb-drag-drop-text tfhb-flexbox tfhb-gap-8">
                <div v-if="props.subtitle">{{ props.subtitle }}</div>
                <div class="drag-drop-instruction">{{ props.label }}</div>
            </div>

            <input 
                type="text" 
                :value="imageUrl" 
                :required="required"
                :name="name"
                :id="name"
                hidden
            />  
        </div>

        <!-- Progress Bar -->
        <div v-if="isUploading" class="progress-bar-container">
            <div class="progress-bar" :style="{ width: uploadProgress + '%' }"></div>
        </div>

        <!-- Single file preview -->
        <div class="upload-file-preview tfhb-full-width" v-if="!multiple && imageUrl">
            <div class="upload-file-preview-items tfhb-flexbox tfhb-justify-between tfhb-gap-16">
                <div class="tfhb-flexbox tfhb-upload-flie-img-wrap tfhb-gap-16">
                    <img v-if="isImageFile(imageUrl)" :src="imageUrl" alt="Uploaded File">
                    <div v-else class="file-icon">
                        <Icon :name="getFileIcon(imageUrl)" size=24 />
                    </div>
                    <span class="file-name">{{ fileName || 'Uploaded File' }}</span>
                </div>
                
                <span class="remove-icon" @click="removeImage"> 
                    <Icon name="X" size=16 />
                </span>
            </div>
        </div>

        <!-- Multiple files preview -->
        <div class="upload-file-preview tfhb-full-width" v-if="multiple && imageUrl && imageUrl.length > 0">
            <div class="upload-file-preview-items tfhb-flexbox tfhb-justify-between tfhb-gap-16" v-for="(url, index) in imageUrl" :key="index">
                <div class="tfhb-flexbox tfhb-upload-flie-img-wrap tfhb-gap-16">
                    <img v-if="isImageFile(url)" :src="url" alt="Uploaded File">
                    <div v-else class="file-icon">
                        <Icon :name="getFileIcon(url)" size=24 />
                    </div>
                    <span class="file-name">{{ extractFileName(url) || 'Uploaded File' }}</span>
                </div>
                
                <span class="remove-icon" @click="removeImage(index)"> 
                    <Icon name="X" size=16 />
                </span>
            </div>
        </div>
    </div> 
    
  </div>
</template>

<style lang="scss">
#menu-item-upload,
#menu-item-browse {
	color: #808285 !important;
}
.tfhb-file-upload-wrap {
    .upload-drag-drop-wrap {
        border: 1px dashed #C0D8C4;
        background-color: #F9FBF9;
        padding: 32px;
        text-align: center;
        flex-direction: column;
        border-radius: 8px;
        transition: 0.3s ease;
        cursor: pointer;
        position: relative;

        .tfhb-drag-drop-text { 
            flex-direction: column;
            font-size: 14px;
            color: #666;
        }

        .drag-drop-instruction {
            font-size: 12px;
            color: #888;
        }
    }

    .upload-drag-drop-wrap.drag-over {
        background-color: #e0f3e0;
        border-color: #68a868;
    }

    .upload-file-preview { 
        margin-top: 16px;
        
        .upload-file-preview-items {
            border-radius: 8px;
            border: 1px dashed #C0D8C4;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            
            .remove-icon { 
                flex-shrink: 0;
                cursor: pointer;
                height: 24px;
                width: 24px;
                border: 1px solid #AC0C22;
                border-radius: 4px;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center; 
                svg path {
                    stroke: #AC0C22;
                }
            }
            .tfhb-upload-flie-img-wrap{
                max-width: calc(100% - 40px);
                
                img {
                    max-height: 40px;
                    border-radius: 4px;
                    max-width: 200px;
                }
                
                .file-icon {
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color: #f3f4f6;
                    border-radius: 4px;
                    border: 1px solid #d1d5db;
                    
                    svg {
                        color: #6b7280;
                    }
                }
                
                .file-name { 
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    display: block;
                    max-width: 200px; /* Ensures it respects flexbox */
                }
            }
        }
    }

    /* Progress Bar */
    .progress-bar-container {
        width: 100%;
        height: 6px;
        background-color: #eee;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 10px;
    }

    .progress-bar {
        height: 100%;
        background-color: #68a868;
        transition: width 0.3s ease-in-out;
    }
}
</style>