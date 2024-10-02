<script setup>
import Icon from '@/components/icon/LucideIcon.vue';
import { ref } from 'vue';
const props = defineProps([
    'classValue', 
    'buttonText', 
    'icon', 
    'hover_icon', 
    'hover_animation',   
    'icon_position'

]) 
const hover_icon_status = ref(false)
// if hover_animation is true then add hover effect
//  

</script>

<template>
    <button :class="classValue" @mouseover="hover_icon_status = true"  @mouseleave="hover_icon_status = false">
        <span  v-if="icon_position == 'left'">
            <!-- <transition v-if="props.hover_animation == true" name="icon-fade" mode="out-in"> -->
                <Icon v-if="hover_icon !='' && hover_icon_status == true" :name="hover_icon" size=20 />
                <Icon v-else :name="icon"  size=20 />
            <!-- </transition>  -->
            <Icon v-else :name="icon"  size=20 /> 
        </span>
        <span >{{ buttonText }} </span>
         
         <!-- {{ hover_icon_status }} -->
         <span v-if="icon_position != 'left'" >
            <!-- <transition v-if="props.hover_animation == true" name="icon-fade" mode="out-in"> -->
                <Icon v-if="hover_icon !='' && hover_icon_status == true" :name="hover_icon" size=20 />
                <Icon v-else :name="icon"  size=20 />
            <!-- </transition>  -->
            <Icon v-else :name="icon"  size=20 /> 
        </span>
           
         
         
    </button>
</template>

<style scoped>
.icon-fade-enter-active, .icon-fade-leave-active {
  transition: opacity 0.3s ease;
}
.icon-fade-enter-from, .icon-fade-leave-to {
  opacity: 0;
}
</style> 