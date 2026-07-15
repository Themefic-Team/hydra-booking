<script setup>
import Icon from '@/components/icon/LucideIcon.vue';
const props = defineProps([
        'name',
        'modelValue',
        'required',
        'type',
        'label',
        'subtitle',
        'placeholder',
        'description',
        'width',
        'tooltip',
        'tooltipText',
        'disabled'
])
const emit = defineEmits(['update:modelValue'])

const checkedValue = (e) => {   
    if(e.target.checked){
        emit('update:modelValue', 1);
    }else{
        emit('update:modelValue', 0);
    } 
}
</script>

<template>

    <div class="tfhb-single-form-field" :class="name"
      :style="{ 'width':  width ? 'calc('+(width || 100)+'% - 12px)' : 'auto' }"
    >
        <div class="tfhb-single-form-field-wrap tfhb-field-swicher" >
            <div class="tfhb-swicher-wrap tfhb-flexbox tfhb-gap-8">
                <!-- Checkbox swicher -->
                <!--   @change="checkedValue" -->
                <label class="switch" :style="disabled ? { opacity: 0.5, pointerEvents: 'none' } : {}">

                    <input
                        type="checkbox"
                        :id="name"
                        :v-model="props.modelValue"
                        @change="checkedValue"
                        :name="name"
                        :checked="props.modelValue == 1 ? true : false"
                        :disabled="disabled"
                        >
                    <div class="slider"></div>
                </label>
                <label class="tfhb-schedule-swicher tfhb-flexbox tfhb-gap-4" v-if="label" :for="name">
                    {{ label }} <span  v-if="required == 'true'"> *</span>
                    <span v-if="tooltip" class="tfhb-tooltip">
                        <Icon name="Info" size=15 />
                        <span class="tfhb-tooltiptext">
                            {{ tooltipText }}
                        </span>
                    </span>
                </label>
            </div>


        </div>
    </div>
</template>

