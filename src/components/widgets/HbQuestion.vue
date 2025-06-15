<script setup>
import Icon from '@/components/icon/LucideIcon.vue'
import HbText from '../form-fields/HbText.vue';
import HbSwitch from '../form-fields/HbSwitch.vue';
const props = defineProps([
    'name',
    'modelValue',
    'width',
    'question_value',
    'skip_remove',
])
const emit = defineEmits(['update:modelValue', 'question-edit', 'question-remove'])

const capitalizedFirstChar = (value) => {
    // if first character is lowercase, convert it to uppercase 
    return value.charAt(0).toUpperCase() + value.slice(1)
}
</script>

<template>
    <div class="tfhb-question-box"> 
        <div class="tfhb-single-form-field tfhb-flexbox tfhb-gap-24" :style="{ 'width': '100%' }"> 
            <div class="tfhb-single-form-field-wrap tfhb-single-question-box tfhb-full-width" v-for="(question, key)  in question_value" :key="key"
            :class="{ 'tfhb-disable': question.enable == 0 }"
            > 
                <div class="tfhb-question-label tfhb-flexbox tfhb-gap-4">
                    <label v-if="key <= 1">{{ capitalizedFirstChar(question.label) }} <span v-if="1 == question.required ">*</span> </label>
                    <label v-else>{{question.label}} <span v-if="1 == question.required ">*</span> </label>
                    <span  v-if="question.type != ''"  class="status info">{{ capitalizedFirstChar(question.type) }} </span>
                </div>
                <div class="tfhb-flexbox tfhb-gap-16 tfhb-field-select">
                    <div class="tfhb-question-type tfhb-full-width">
                        <div class="tfhb-question-type-tags">
                            <span v-if="question.enable == 0" class="status disabled">{{ $tfhb_trans('Disabled') }}</span>
                        </div>
                        <!-- <span v-if="question.enable == 0" class="status disabled">{{ $tfhb_trans('Disabled') }}</span> -->
                        <HbText  
                            v-model="question.placeholder"
                            disabled="disabled"
                  
                        /> 
                    </div>
                    <HbSwitch 
                        v-model="question.enable"
                        v-if="key > skip_remove"
                        class="tfhb-tooltip"
                        :tooltip="true"
                        :tooltipText = "$tfhb_trans('Enable or disable this field.')"
                    />
                    <button class="question-edit-btn" v-if="key > skip_remove">
                        <Icon name="PencilLine" :width="16" @click="emit('question-edit', key)"/>
                    </button>
                    <button class="question-edit-btn" v-if="key > skip_remove" @click="emit('question-remove', key)">
                        <Icon name="X" :width="16"/>
                    </button>
                </div>
                    
            </div> 

        </div>
    </div>
</template>

<style scoped>
</style> 