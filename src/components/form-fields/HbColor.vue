<script setup> 
import LvColorpicker from 'lightvue/color-picker';
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
const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="tfhb-single-form-field" :class="name" 
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : '100%' }" 
    >
    <div class="tfhb-single-form-field-wrap ">
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
        <div class="tfhb-single-colorbox" >
            <div class="color-select " >
                <!-- <LvColorpicker 
                :value="props.modelValue" 
                v-model="props.modelValue" 
                :name= "name"
                :id="name"   
                :key="props.modelValue" 
                :class="errors ? 'tfhb-required' : ''" 
                :withoutInput="true"/> -->
                <LvColorpicker 
                  :value="props.modelValue" 
                  @input="emit('update:modelValue', $event)"  
                  :key="props.modelValue"  
                  :withoutInput="true"
                />
                <span v-if="placeholder">{{ placeholder }}</span>
                <span v-else>{{ $tfhb_trans('Select Color') }}</span>
            </div>
        </div>
       
             
    </div> 
  </div>
   
</template>

<style scoped>
</style> 