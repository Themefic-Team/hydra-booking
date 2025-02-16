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
    'tooltipText',
    'colors',
    'class'

])
const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="tfhb-single-form-field" :class="name" 
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
    > 
    <div class="tfhb-single-form-field-wrap tfhb-field-color-palette " :class="props.class ? props.class : ''">
         <!--if has label show label with tag else remove tags  -->
         <label  :for="name" class="" >
            <div class="tfhb-pelette-wrap tfhb-flexbox">
                <span :style="{ 'background-color':  props.colors.primary ? props.colors.primary : '#ddd' }"></span> 
                <span :style="{ 'background-color':  props.colors.secondary ? props.colors.secondary : '#ccc' }"></span> 
                <span :style="{ 'background-color':  props.colors.text_title ? props.colors.text_title : '#ddd' }"></span> 
                <span :style="{ 'background-color':  props.colors.text_paragraph ? props.colors.text_paragraph : '#ccc' }"></span> 
            </div>
            
            <div class="tfhb-pelette-content-wrap tfhb-flexbox tfhb-justify-between" >
                <span> {{ label }} </span>  
                <!-- <div class="tfhb-pelette-wrap-checkbox">
                    <input 
                    :id="name" 
                    :v-model="props.modelValue"  
                    @change="checkedValue" 
                    :name="name"
                    :value="props.value"
                    :checked="props.modelValue == props.value ? true : false"
                    type="radio"
                    />     
                    <span class="checkmark"></span> 
                </div> -->
            </div>
      
        </label>
    </div> 
  </div>
   
</template>

<style scoped>
</style> 