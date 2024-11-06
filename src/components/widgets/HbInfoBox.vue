<script setup>
import Icon from '@/components/icon/LucideIcon.vue'

const props = defineProps({
  title: String,
  desc: String,
})

</script>

<template>
 <div class="tfhb-info-box tfhb-flexbox tfhb-gap-16 tfhb-p-24">
    <div class="tfhb-info-box-icon">
        <Icon name="Info" :size="20" />
    </div>
    <div class="tfhb-info-box-content">
        <h3 v-if="props.title">{{ props.title }}</h3>
        <slot name="content"> default content </slot>
    </div>

 </div>
</template>