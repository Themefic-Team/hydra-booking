<script setup>
import { ref } from 'vue';
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
const showPassword = ref(false);
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
        
        <div v-if="type === 'password'" class="tfhb-password-input-wrapper" style="position: relative; width: 100%; display: flex; align-items: center;">
          <input 
            :value="props.modelValue" 
            :required= "required"
            :name= "name"
            :id="name" 
            @input="emit('update:modelValue', $event.target.value)" 
            :type="showPassword ? 'text' : 'password'"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :class="errors ? 'tfhb-required' : ''"
            :min="limit"
            style="width: 100%; padding-right: 40px !important;"
            @click="emit('tfhb-onclick', $event)"
          /> 
          <span 
            class="tfhb-password-toggle-btn"
            @click.stop="showPassword = !showPassword"
            style="position: absolute; right: 12px; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center; z-index: 2; height: 20px; width: 20px;"
            :title="showPassword ? 'Hide Secret Key' : 'Show Secret Key'"
          >
            <Icon :name="showPassword ? 'EyeOff' : 'Eye'" :size="18" />
          </span>
        </div>

        <input 
          v-else
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
          style="width: 100%;"
          @click="emit('tfhb-onclick', $event)"
        /> 
             
    </div> 
  </div>
   
</template>

<style scoped>
</style> 