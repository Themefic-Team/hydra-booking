<script setup>
import Icon from '@/components/icon/LucideIcon.vue';
import HbPreloader from '@/components/icon/HbPreloader.vue';
import { ref } from 'vue';
const props = defineProps([
    'classValue', 
    'buttonText', 
    'icon', 
    'icon_size', 
    'hover_icon', 
    'hover_animation',   
    'icon_position',
    'width',
    'pre_loader',
    'pre_loader_color',
    'disabled',
    

]) 
const hover_icon_status = ref(false)
// if hover_animation is true then add hover effect
//  
// :style="{ 'width':  width ? width : 'auto' }" 



</script>

<template> 
    <button   @mouseover="hover_icon_status = true"  @mouseleave="hover_icon_status = false" :disabled="props.pre_loader || props.disabled" 
        :style="{ 'width':  width ? width : 'auto' }" 
        :class="[
            classValue,
            { 'disabled': props.pre_loader || props.disabled },
            { 'tfhb-icon-hover-animation': props.hover_animation }
        ]" 
    >
        <span   class="tfhb-button-icon" :class="icon_position" v-if="icon_position == 'left' && icon">
             
            <Icon v-if="hover_icon && props.pre_loader != true" class="tfhb-btn-hover-icon" :name="hover_icon"  size=20 /> 
            <HbPreloader  v-if="props.pre_loader == true"  :color="pre_loader_color ?? '#ffffff'" />
            <Icon v-else class="tfhb-btn-icon"  :name="icon"  size=20 /> 
        </span>
        <span class="tfhb-button-text">{{ buttonText }} </span>
         
         <!-- {{ hover_icon_status }} -->
         <span  class="tfhb-button-icon" :class="icon_position" v-if="icon_position != 'left' && icon" >

            <Icon v-if="hover_icon && props.pre_loader != true" class="tfhb-btn-hover-icon" :name="hover_icon"  size=20 /> 
           
            <HbPreloader  v-if="props.pre_loader == true"  :color="pre_loader_color ?? '#ffffff'" />
            <Icon v-else class="tfhb-btn-icon"  :name="icon"  :size="props.icon_size ? props.icon_size : 20" /> 
        </span>
           
         
         
    </button>
</template>

<style scoped>

</style> 