<script setup>
import Icon from '@/components/icon/LucideIcon.vue';
const props = defineProps([
    'name',
    'modelValue',
    'required',
    'type',
    'label',
    'width',
    'subtitle',
    'placeholder',
    'description', 
    'limit',
    'disabled', 
    'readonly', 
    'errors',
    'tooltip',
    'tooltipText'

])
const emit = defineEmits(['update:modelValue', 'tfhb-onclick'])
</script>

<template>
  <div class="tfhb-single-form-field" :class="name" 
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
    >
    <div class="tfhb-single-form-field-wrap tfhb-field-input">
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
        
        <input 
          :value="props.modelValue" 
          :required= "required"
          :name= "name"
          :id="name" 
          @input="emit('update:modelValue', $event.target.value)" 
          :type="type || 'text'"
          :placeholder="placeholder"
          :disabled="disabled"
          :readonly="readonly"
          :class="errors ? 'tfhb-required' : ''"
          :min="limit"
          @click="emit('tfhb-onclick', $event)"
        /> 
             
    </div> 
  </div>
   
</template>

<style scoped>
</style> 