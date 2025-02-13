<script setup>
import { __ } from '@wordpress/i18n';
import HbText from '../form-fields/HbText.vue';
import HbSwitch from '../form-fields/HbSwitch.vue';
import HbSelect from '../form-fields/HbSelect.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue'
import Icon from '@/components/icon/LucideIcon.vue';
import { toast } from "vue3-toastify"; 
import useValidators from '@/store/validator';
const { errors, isEmpty } = useValidators();
const emit = defineEmits(['update:modelValue', 'question-add', 'question-cancel'])

const props = defineProps({
    questions_data: {
        type: Object,
        required: true
    }
});

const AddNewOptions = () => { 
    props.questions_data.options.push('Option ' + (props.questions_data.options.length + 1));
}

const DeleteOptions = (index) => {
    props.questions_data.options.splice(index, 1);
}
const UpdateQuestionsData = async (validator_field) => {
     
   // Clear the errors object
   Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {
        

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!props.questions_data[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!props.questions_data[fieldParts[0]][fieldParts[1]]){
                errors[fieldParts[0]+'___'+[fieldParts[1]]] = 'Required this field';
            }
        }
            
        });
    }

     // Errors Checked
     const isEmpty = Object.keys(errors).length === 0;
    if(!isEmpty){
         
        toast.error('Fill Up The Required Fields', {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        }); 
        return
    }else{
        emit('question-add');
    }


}
 
</script>

<template> 
    <!-- if change  questions_data.type -->
    <HbDropdown 
        v-model="questions_data.type"
        required= "true" 
        :label="$tfhb_trans('Field type')"  
        :selected = "1"  
        :placeholder="$tfhb_trans('Field type')" 
        :option = "[
            {name: 'Text', value: 'text'}, 
            {name: 'Email', value: 'email'}, 
            {name: 'Textarea', value: 'textarea'}, 
            {name: 'Number', value: 'number'}, 
            {name: 'Phone', value: 'phone'}, 
            {name: 'Radio', value: 'radio'}, 
            {name: 'Select', value: 'select'},  
            {name: 'Checkbox', value: 'checkbox'}, 
            {name: 'Date', value: 'date'}
        ]"
        name="type" 
        :errors="errors.type"
    />

    <HbText  
        v-model="questions_data.label"
        required= "true"  
        :label="__('Label', 'hydra-booking')"  
        :placeholder="__('Enter field Label', 'hydra-booking')" 
        name="type" 
        :errors="errors.label"
    /> 
    <HbText  
        v-if="questions_data.type != 'radio' &&  questions_data.type != 'checkbox' && questions_data.type != 'select'"  
        v-model="questions_data.placeholder" 
        :label="__('Placeholder', 'hydra-booking')"  
        :placeholder="__('Enter field placeholder ', 'hydra-booking')"  
        :errors="errors.placeholder"
    /> 

    <!-- Options -->
    <div 
        v-if="
            questions_data.type == 'radio' || 
            questions_data.type == 'select' ||
            questions_data.type == 'multi-select' ||
            questions_data.type == 'checkbox'  
        " 
        class="tfhb-single-form-field"   :style="{ 'width': '100%' }" 
    > 
        <div class="tfhb-single-form-field-wrap tfhb-field-options"> 
            <label   for="name">{{ $tfhb_trans('Options') }} <span  > *</span> </label>
            <div  class="tfhb-options-fields tfhb-flexbox tfhb-gap-16" v-for="(option, index) in questions_data.options" :key="index"> 
                <input 
                    v-model="questions_data.options[index]"
                    type="text"
                    placeholder="Option 1"
                />
                <button class="tfhb-btn tfhb-flexbox tfhb-gap-8"  @click="DeleteOptions(index)">
                    <Icon name="Trash" :width="20"/> 
                </button>   
            </div>
            <button class="tfhb-btn tfhb-flexbox tfhb-gap-8" @click="AddNewOptions" >
                <Icon name="PlusCircle" :width="20"/>
                {{ $tfhb_trans('Add New Option') }}
            </button>
        </div> 
    </div>
    <!-- Options -->

    <HbSwitch  
         v-if=" questions_data.type != 'checkbox'"
        v-model="questions_data.required"
        :label="$tfhb_trans('Required')"  
    /> 

    <div class="tfhb-action-btn tfhb-full-width tfhb-flexbox tfhb-gap-16 tfhb-justify-normal">
        <!-- <button class="tfhb-btn secondary-btn" @click="emit('question-cancel')">Cancel</button>  -->
         <HbButton  
            classValue="tfhb-btn secondary-btn" 
            @click="emit('question-cancel')"
            :buttonText="$tfhb_trans('Cancel')" 
        />  
        <HbButton  
            classValue="tfhb-btn boxed-btn" 
            @click="UpdateQuestionsData(['type', 'label'])"
            :buttonText="$tfhb_trans('Save')" 
        /> 
    </div>
</template>

<style scoped>
</style> 