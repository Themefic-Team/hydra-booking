<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import HbQuestion from '@/components/widgets/HbQuestion.vue';
import Icon from '@/components/icon/LucideIcon.vue';
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue'

const informationPopup = ref(false);
import { AddonsSettings } from '@/store/settings/addons-settings';

 
// Popup Open.
const QuestionPopupOpen = () => {
    AddonsSettings.Sellers.registration_froms_fields.push({
        label: '',
        type:'',
        name:'',
        placeholder:'',
        options: ['Option 1', 'Option 2'],
        required: 0,
        enable: 1,
    });
    const lastIndexOfQuestion = AddonsSettings.Sellers.registration_froms_fields.length - 1;
    questions_data.key = lastIndexOfQuestion;
    questions_data.label = '';
    questions_data.name = '';
    questions_data.type = '';
    questions_data.placeholder = '';
    questions_data.options = ['Option 1', 'Option 2'];
    questions_data.required = 0;
    questions_data.enable = 1;
    informationPopup.value = true;
}

// Popup Saved addExtraQuestion
function addExtraInformation(){  
    AddonsSettings.Sellers.registration_froms_fields[questions_data.key].label = questions_data.label
    AddonsSettings.Sellers.registration_froms_fields[questions_data.key].type = questions_data.type
    AddonsSettings.Sellers.registration_froms_fields[questions_data.key].placeholder = questions_data.placeholder

    // if type is radio, select, multi-select, checkbox then options will be added
    if (questions_data.type === 'radio' || questions_data.type === 'select' || questions_data.type === 'multi-select' || questions_data.type === 'checkbox') {
        AddonsSettings.Sellers.registration_froms_fields[questions_data.key].options = questions_data.options
    }else{
        AddonsSettings.Sellers.registration_froms_fields[questions_data.key].options = []
    }
    
    AddonsSettings.Sellers.registration_froms_fields[questions_data.key].required = questions_data.required
    informationPopup.value = false;
}

// Edit Extra Question
function EditExtraInformation(key){ 
    AddonsSettings.Sellers.registration_froms_fields.forEach((question, qkey) => {
        if (qkey === key) {
            questions_data.key = key;
            questions_data.label = question.label;
            questions_data.name = question.name;
            questions_data.type = question.type;
            questions_data.placeholder = question.placeholder;
            questions_data.options = question.options;
            questions_data.required = question.required;
            questions_data.enable = question.enable;
            informationPopup.value = true;
        }
    });
}

// Remove removeExtraQuestion
const removeExtraQuestion = (key) => {
    AddonsSettings.Sellers.registration_froms_fields.splice(key, 1)
}

// Extra Qestion Data
const questions_data =  reactive({});
 
</script>

<template>   
    <div class="tfhb-admin-title" >
        <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">{{ $tfhb_trans('Seller Registration Forms Builders') }}   </h2> 
        <p>{{ $tfhb_trans('Create and customize the information fields for your hosts') }}</p>
    </div>
    <div    class="tfhb-admin-card-box  tfhb-gap-24 tfhb-m-0"  >   
        <div v-if="AddonsSettings.Sellers.registration_froms_fields !=''"  class="tfhb-host-info-builder-wrap  tfhb-mb-16" >
            
            <HbQuestion 
                :question_value="AddonsSettings.Sellers.registration_froms_fields"
                :skip_remove="-1"
                @question-edit="EditExtraInformation"
                @question-remove="removeExtraQuestion"
            />
        </div>
        
        <button class="tfhb-btn tfhb-flexbox tfhb-gap-8"  @click="QuestionPopupOpen()" >
            <Icon name="PlusCircle" :width="20"/>
            {{ $tfhb_trans('Add more Information') }}
        </button>

        <HbPopup :isOpen="informationPopup" @modal-close="informationPopup = false" max_width="400px" name="first-modal">
            <template #header> 
                <h3>{{ $tfhb_trans('Add Information for Hosts') }}</h3>
            </template>

            <template #content>  
                <HbQuestionForm 
                    :questions_data="questions_data"
                    @question-add="addExtraInformation"
                    @question-cancel="informationPopup = false"
                />
            </template> 
        </HbPopup>
    </div>
{{ AddonsSettings.Sellers }}
     <HbButton 
        classValue="tfhb-btn boxed-btn flex-btn tfhb-mt-16" 
        @click="AddonsSettings.UpdateSellersSettings()" 
        :buttonText="$tfhb_trans('Save Changes')"
        icon="ChevronRight" 
        hover_icon="ArrowRight" 
        :pre_loader="AddonsSettings.update_preloader"
        :hover_animation="true"
    />   
        
</template>

<style scoped>
</style> 