<script setup>
import Icon from '@/components/icon/LucideIcon.vue';
import { ref } from 'vue';
const props = defineProps([
    'classValue', 
    'buttonText', 
    'icon', 
    'hover_icon', 
    'hover_animation',   
    'icon_position',
    'width',
    

]) 
const hover_icon_status = ref(false)
// if hover_animation is true then add hover effect
//  

</script>

<template>
    <button :class="classValue" class="" @mouseover="hover_icon_status = true"  @mouseleave="hover_icon_status = false"
    :style="{ 'width':  width ? width : 'auto' }" 
    >
        <span class="tfhb-button-icon" :class="icon_position" v-if="icon_position == 'left'">
           
            <Icon  :name="icon"  size=20 /> 
        </span>
        <span class="tfhb-button-text">{{ buttonText }} </span>
         
         <!-- {{ hover_icon_status }} -->
         <span class="tfhb-button-icon" :class="icon_position" v-if="icon_position != 'left'" >
           
            <Icon :name="icon"  size=20 /> 
        </span>
           
         
         
    </button>
</template>

<style scoped>

</style> 