<script setup>
import Icon from '@/components/icon/LucideIcon.vue'

const props = defineProps({
  title: String,
  desc: String,
  icon: String,

})


</script>

<template>
 <div class="tfhb-info-box tfhb-flexbox tfhb-gap-16 tfhb-p-24 tfhb-full-width">
    <div class="tfhb-info-box-icon">
        <Icon :name="icon ?? 'Info'" :size="20" />
    </div>
    <div class="tfhb-info-box-content tfhb-full-width">
        <h3 v-if="props.title">{{ props.title }}</h3>
        <slot name="content"> {{$tfhb_trans('default content')}}</slot>
    </div>

 </div>
</template>