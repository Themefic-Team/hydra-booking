<script setup>

import {ref} from 'vue';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import Icon from '@/components/icon/LucideIcon.vue'

const props = defineProps([
    'name',
    'modelValue',
    'fieldClass',
    'enableTime',
    'noCalendar',
    'config', // flatpickr config
    'required',
    'type',
    'label',
    'width',
    'subtitle',
    'placeholder',
    'description', 
    'change', 
    'icon',
    'icon',
    'tooltip',
    'tooltipText'
])
const emit = defineEmits(['update:modelValue'])

// Read more at https://flatpickr.js.org/options/
const config = ref(props.config || {});

const flatPickrChange = (e) => { 
    let date = e.target.value;
    emit('dateChange', date); 
   

}

</script>

<template>

  <div class="tfhb-single-form-field " :class="[name,fieldClass]" 
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
    >
    <div class="tfhb-single-form-field-wrap tfhb-field-date">
         <!--if has label show label with tag else remove tags  -->
         
         <label class="tfhb-flexbox tfhb-gap-4" v-if="label" :for="name">{{ label }} <span  v-if="required == 'true'"> *</span>  
          <span v-if="tooltip" class="tfhb-tooltip">
            <Icon name="Info" size=15 />
            <span class="tfhb-tooltiptext"> 
              {{ tooltipText }}
            </span>
          </span>
        
        </label>
        <h4 v-if="subtitle">{{ subtitle }}</h4>
        <p v-if="description">{{ description }}</p> 
        <flatPickr  @input="emit('update:modelValue', $event.target.value)"  :placeholder="props.placeholder" :value="props.modelValue" :config="props.config" @change="flatPickrChange"  />
    
        <span class="tfhb-flat-icon"><Icon v-if="icon" :name="icon" size=20 /> </span>
             
    </div> 
  </div>
   
</template>

<style scoped>
</style> 