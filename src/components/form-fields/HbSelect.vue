<script setup>
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
    'tooltip',
    'tooltipText'
])
const emit = defineEmits(['update:modelValue', 'tfhb-onchange'])
</script>

<template> 
  <div class="tfhb-single-form-field" :class="name" 
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
    >
      <div class="tfhb-single-form-field-wrap tfhb-field-select">
              
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
          
          <select 
              :value="props.modelValue" 
              :required= "required"
              :id="name" 
              :name="name" 
              @input="emit('update:modelValue', $event.target.value)" 
              :type="type"
              :placeholder="placeholder"
              @change="emit('tfhb-onchange', $event)"
              :class="errors ? 'tfhb-required' : ''"
          >  
              <option value="">{{ placeholder }}</option>
              <option v-for="(value, key) in option" :key="key" selected :value="key">{{ value }}</option>
          </select> 
      </div>

  </div>
   
</template>

<style scoped>
</style> 