<script setup>
import { ref, watch, defineEmits } from 'vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify";
const props = defineProps([
    'name', 
    'modelValue', 
    'required', 
    'label', 
    'btn_label', 
    'width',
    'subtitle', 'placeholder', 'description', 'disabled',
    'file_size', // 5 MB
    'file_format' // jpg, jpeg, png
])
const emit = defineEmits(['update:modelValue']);
const imageUrl = ref(props.modelValue);
const dragOver = ref(false);
const fileName = ref('');
const isUploading = ref(false);
const uploadProgress = ref(0);
const MAX_FILE_SIZE = props.file_size * 1024 * 1024 // Convert MB to bytes
const ALLOWED_FORMATS = props.file_format != '' ?  props.file_format.split(',') : '';

const extractFileName = (url) => url ? url.split('/').pop().split('?')[0] : '';

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
    if(type == 'clicked'){ 
        imageUrl.value = attachment.url  
        fileName.value = extractFileName(attachment.url) 
        emit('update:modelValue', attachment.url)
    }else{
        imageUrl.value = attachment.source_url  
        fileName.value = extractFileName(attachment.source_url) 
        emit('update:modelValue', attachment.source_url)
    } 
    isUploading.value = false
    uploadProgress.value = 0
}

const UploadImage = () => {   
    if (isUploading.value) return
    const mediaUploader = wp.media({
        title: 'Upload Image',
        button: { text: 'Use this image' },
        multiple: false
    })

    mediaUploader.on('select', function () {
        const attachment = mediaUploader.state().get('selection').first().toJSON()
        imageChange(attachment, 'clicked')
    })

    mediaUploader.open()
}

const handleDrop = async (event) => {
    event.preventDefault()
    dragOver.value = false
    
    const file = event.dataTransfer.files[0]
    if (file && validateFile(file)) {
        await uploadFileToWordPress(file)
    }
}

const uploadFileToWordPress = async (file) => {
    isUploading.value = true
    uploadProgress.value = 0
    const formData = new FormData()
    formData.append('file', file)

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

const removeImage = () => {
    imageUrl.value = ''
    fileName.value = ''
    emit('update:modelValue', '')
}

watch(() => props.modelValue, (newVal) => {
    imageUrl.value = newVal
    fileName.value = extractFileName(newVal)
}) 
</script>

<template> 
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

        <div class="upload-file-preview tfhb-full-width" v-if="imageUrl">
            <div class="upload-file-preview-items tfhb-flexbox tfhb-justify-between tfhb-gap-16">
                <div class="tfhb-flexbox tfhb-upload-flie-img-wrap tfhb-gap-16">
                    <img :src="imageUrl" alt="Uploaded Image">
                    <span class="file-name">{{ fileName || 'Uploaded Image' }}</span>
                </div>
                
                <span class="remove-icon" @click="removeImage"> 
                    <Icon name="X" size=14 />
                </span>
            </div>
        </div>
    </div> 
    
  </div>
</template>

<style lang="scss">
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
