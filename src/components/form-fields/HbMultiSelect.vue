<script setup>
import {ref} from 'vue'; 
import MultiSelect from 'primevue/multiselect'; 
import Icon from '@/components/icon/LucideIcon.vue'
const props = defineProps([
    'modelValue',
    'name',
    'required',
    'type',
    'width',
    'label',
    'subtitle',
    'placeholder',
    'description', 
    'option',
    'errors',
    'filter',
    'optionType', 
    'parent_key',
    'single_key',
    'selected',
    'icon',
    'tooltip',
    'tooltipText',
  ])

 
const emit = defineEmits(['update:modelValue', 'tfhb-onchange', 'add-change', 'add-click', 'tfhb_start_change', 'tfhb_body_value_change'])
const handleChange = (e) => {  
    emit('update:modelValue', e.value)
    emit('tfhb-onchange', e.value)
    emit('add-change', e)
    emit('tfhb_start_change', props.parent_key, props.single_key, e.value)
    emit('tfhb_body_value_change', props.single_key, e.value);
}

const focusOnSearch = (e) => {  
 
 
  setTimeout(() => {
        const searchInput = document.querySelector('.p-dropdown-filter');
        if (searchInput) {
          searchInput.focus();
        }
      }, 10); // Slight delay to wait for rendering
}
</script>

<template> 
  <div class="tfhb-single-form-field" :class="name" 
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
    >
      <div class="tfhb-single-form-field-wrap tfhb-field-dropdown"> 
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
         
          <IftaLabel>
           
            <MultiSelect 
                v-model="props.modelValue"  
                @change="handleChange"    
                filter
                :maxSelectedLabels="3" 
                optionLabel="name"
                optionValue="value"
                :options="option"  
                @show="focusOnSearch"
                :overlay="false"
                :placeholder="placeholder" 
                :style="{ 'width': '100%' }"  
                :class="errors ? 'tfhb-required' : ''"
            /> 
        </IftaLabel>
    
      </div>

  </div>
   
</template> 
<style scoped>
</style> 