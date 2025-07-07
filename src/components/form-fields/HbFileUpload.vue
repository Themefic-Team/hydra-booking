<script setup>
import { ref, defineEmits, defineProps } from 'vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from 'vue3-toastify'

const props = defineProps([
  'name',
  'modelValue',
  'required',
  'label',
  'btn_label',
  'width',
  'subtitle', 'placeholder', 'description', 'disabled',
  'file_size', // in MB
  'wp_media',
  'file_format' // e.g. "jpg, jpeg, png"
])

const emit = defineEmits(['update:modelValue', 'tfhbChange'])
const dragOver = ref(false);
const isUploading = ref(false);
const fileName = ref('');

const handleDropOnlyFile = (event) => {
    isUploading.value = true;
    event.preventDefault()
    dragOver.value = false
    const files = event.dataTransfer.files
    if (!files.length) return

    const file = files[0]

     if(checkValidation(file) == false){
      return false;
    }


    setTimeout(() => {
        emit('update:modelValue', file) 
        emit('tfhbChange', file)

        isUploading.value = false;
    }, 1000  )
   
}

const inputOnChange = (event) => {
    isUploading.value = true;
    const file = event.target.files[0]
    if(checkValidation(file) == false){
      return false;
    }
    // set time out  for on sec 
    setTimeout(() => {
        emit('update:modelValue', file) 
        emit('tfhbChange', file) 
        isUploading.value = false; 
    }, 1000  )
   
}


const checkValidation = (file) => { 
    // Format validation
    const allowedFormats = (props.file_format || '').split(',').map(f => f.trim().toLowerCase())
    const fileExtension = file.name.split('.').pop().toLowerCase();
    fileName.value = file.name;
    // if file name is more then then 10 charecter srink file name exp file-name***.svg
    if (file.name.length > 10) {
        fileName.value = file.name.substring(0, 10) + '***.' + fileExtension
    }
    
    if (allowedFormats.length && !allowedFormats.includes(fileExtension)) {  
          toast.error(`File type must be one of: ${allowedFormats.join(', ')}`, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
        return false;
    }

    //   // Size validation
    const maxSizeMB = parseFloat(props.file_size || 5)
    const fileSizeMB = file.size / 1024 / 1024
    if (fileSizeMB > maxSizeMB) { 
         toast.error(`File size must be less than ${maxSizeMB} MB`, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
        return false;
    }
    return true;

}
</script>

<template>
  <div class="tfhb-single-form-field tfhb-file-upload tfhb-flexbox" :class="name"
       :style="{ width: width ? 'calc(' + (width || 100) + '% - 12px)' : '100%' }">
    <div class="tfhb-single-form-field-wrap tfhb-flexbox tfhb-file-upload-wrap tfhb-full-width tfhb-gap-8"
         v-if="wp_media === false"
         @dragover.prevent="dragOver = true"
         @dragleave.prevent="dragOver = false"
         @drop="handleDropOnlyFile">

      <div class="upload-drag-drop-wrap tfhb-full-width tfhb-flexbox tfhb-gap-16"
           :class="{ 'drag-over': dragOver }">

        <label :for="name" class="tfhb-btn secondary-btn flex-btn file-input-label">
          <Icon name="Search" class="mr-2" /> 
          <span v-if="fileName == ''">{{ isUploading ? 'Uploading...' : (props.btn_label || 'Upload File') }}</span>
          <span v-else>{{ fileName }}</span>
          <input 
            :name="name"
            :id="name"
            type="file"
            hidden
            @change="inputOnChange"
          />
        </label>

        <div v-if="props.label" class="tfhb-drag-drop-text tfhb-flexbox tfhb-gap-8">
          <div v-if="props.subtitle">{{ props.subtitle }}</div>
          <div class="drag-drop-instruction">{{ props.label }}</div>
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
        cursor: pointer;
        border: 1px solid #AC0C22;
        border-radius: 4px;
        height: 24px;
        width: 24px;
        display: flex;
        align-items: center;
        justify-content: center;

        svg path {
          stroke: #AC0C22;
        }
      }

      .tfhb-upload-flie-img-wrap {
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
          max-width: 200px;
          display: block;
        }
      }
    }
  }

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
