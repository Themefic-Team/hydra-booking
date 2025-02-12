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
</script>

<template>
    <div class="tfhb-question-box"> 
        <div class="tfhb-single-form-field tfhb-flexbox tfhb-gap-24" :style="{ 'width': '100%' }"> 
            <div class="tfhb-single-form-field-wrap tfhb-single-question-box tfhb-full-width" v-for="(question, key)  in question_value" :key="key"
            :class="{ 'tfhb-disable': question.enable == 0 }"
            > 
                <label>{{ question.placeholder }}</label>
                <div class="tfhb-flexbox tfhb-gap-16 tfhb-field-select">
                    <div class="tfhb-question-type tfhb-full-width">
                        <span v-if="question.enable == 0" class="status disabled">{{ $tfhb_trans('Disabled') }}</span>
                        <HbText  
                            v-model="question.type"
                            disabled="disabled"
                        /> 
                    </div>
                    <HbSwitch 
                        v-model="question.enable"
                        v-if="key > skip_remove"
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