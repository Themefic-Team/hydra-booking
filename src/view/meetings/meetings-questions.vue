<script setup>
import { __ } from '@wordpress/i18n';
import {reactive, ref} from 'vue'
import HbQuestion from '@/components/widgets/HbQuestion.vue'
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue'
import Icon from '@/components/icon/LucideIcon.vue' 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import axios from 'axios';
import { useRouter, useRoute, RouterView } from 'vue-router' 

const router = useRouter();
const emit = defineEmits(["update-meeting", "limits-frequency-add"]); 
const props = defineProps({
    meetingId: {
        type: Number,
        required: true
    },
    meeting: {
        type: Object,
        required: true
    },
    integrations: {
        type: Object,
        required: true
    },
    formsList: {
        type: Object,
        required: true
    },
    update_preloader: {
        type: Boolean,
        required: true
    }

}); 
const QuestionPopup = ref(false);
// Extra Qestion Data
const questions_data = reactive({});
// Remove removeExtraQuestion
const removeExtraQuestion = (key) => {
    props.meeting.questions.splice(key, 1);
}
function EditExtraQuestion(key){
    
    props.meeting.questions.forEach((question, qkey) => {
        if (qkey === key) {
            questions_data.key = key;
            questions_data.label = question.label;
            questions_data.name = question.name ?? '';
            questions_data.type = question.type;
            questions_data.placeholder = question.placeholder;
            questions_data.options = question.options;
            questions_data.required = question.required;
            questions_data.enable = question.enable ?? 1;
            QuestionPopup.value = true;
        }
    });
}
// Popup Saved addExtraQuestion
function addExtraQuestion(){
    props.meeting.questions[questions_data.key].label = questions_data.label
    props.meeting.questions[questions_data.key].type = questions_data.type
    props.meeting.questions[questions_data.key].placeholder = questions_data.placeholder
    // if type is radio, select, multi-select, checkbox then options will be added
    if (questions_data.type === 'radio' || questions_data.type === 'select' || questions_data.type === 'multi-select' || questions_data.type === 'checkbox') {
        props.meeting.questions[questions_data.key].options = questions_data.options
    }else{
        props.meeting.questions[questions_data.key].options = []
    }
    props.meeting.questions[questions_data.key].required = questions_data.required
    QuestionPopup.value = false;
}
// Add New Question
function QuestionPopupAdd(){
    props.meeting.questions.push({
        label: '',
        type:'',
        name: '',
        placeholder:'',
        options: ['Option 1', 'Option 2'],
        required: 0,
        enable: 1,
    });
    const lastIndexOfQuestion = props.meeting.questions.length - 1;
    questions_data.key = lastIndexOfQuestion;
    questions_data.label = '';
    questions_data.name = '';
    questions_data.type = '';
    questions_data.placeholder = '';
    questions_data.options = ['Option 1', 'Option 2'],
    questions_data.required = 0;
    questions_data.enable = 1;
    QuestionPopup.value = true;
}
// Popup close
function QuestionPopupClose(){
    QuestionPopup.value = false;
}
// Get Forms Data
 
const GetFormsData = async (value) => {
    let form_type =  value;  
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/question/forms-list', {
            form_type: form_type
        },
        {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        }
        );
        if (response.data.status) {  
            props.formsList.value = response.data.questionForms; 
        }
    } catch (error) {
        console.log(error);
    } 
}
</script>

<template>
    <div class="meeting-create-details tfhb-gap-24"> 
        <div class="tfhb-meeting-range tfhb-full-width">
            <div class="tfhb-admin-title   tfhb-full-width">
                <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
                    {{ $tfhb_trans('Meeting Questions for Attendee') }}
                    <!-- <HbSwitch 
                        v-model="meeting.questions_type" 
                    /> --> 
                </h2> 
                <p>{{ $tfhb_trans('Create your own booking page questions') }}</p>
            </div>

            <div class="tfhb-flexbox tfhb-gap-0 tfhb-align-normal tfhb-justify-between">
                <div class="tfhb-single-meeting-range tfhb-admin-card-box tfhb-border-box tfhb-m-0 tfhb-align-baseline">
                    <label for="tfhb_continuos_date" class="tfhb-m-0 tfhb-flexbox tfhb-gap-16 tfhb-align-normal">
                        <div class="tfhb-range-checkbox">
                            <input id="tfhb_continuos_date" name="tfhb_range_date" value="custom" type="radio" v-model="meeting.questions_type" :checked="meeting.questions_type == 'custom' ? true : false">
                            <span class="checkmark"></span> 
                        </div>
                        <div class="tfhb-range-title">
                            <h4 class="tfhb-m-0">{{ $tfhb_trans('Create custom form') }}</h4> 
                            <!-- <p class="tfhb-m-0">{{ $tfhb_trans('Meeting will be go for indefinitely into the future') }}</p> -->
                        </div>
                    </label>
                </div>
                <div class="tfhb-single-meeting-range tfhb-admin-card-box tfhb-border-box tfhb-m-0 tfhb-align-baseline"> 
                    <label for="tfhb_specific_date" class="tfhb-m-0 tfhb-flexbox tfhb-gap-16 tfhb-align-normal">
                        <div class="tfhb-range-checkbox">
                            <input id="tfhb_specific_date" name="tfhb_range_date" type="radio" value="existing" v-model="meeting.questions_type" :checked="meeting.questions_type == 'existing' ? true : false">
                            <span class="checkmark"></span> 
                        </div>
                        <div class="tfhb-range-title">
                            <h4 class="tfhb-m-0">{{ $tfhb_trans('Use existing form') }}</h4> 
                            <!-- <p class="tfhb-m-0">{{ $tfhb_trans('Meeting will be only available on specific dates') }}</p> -->
                        </div>
                    </label> 
                </div>
            </div>
        </div>
     

        <div class="tfhb-admin-card-box tfhb-gap-24 tfhb-m-0 tfhb-full-width" v-if="meeting.questions_type == 'custom'">  
            
            <HbQuestion 
                :question_value="meeting.questions"
                :skip_remove="1"
                @question-edit="EditExtraQuestion"
                @question-remove="removeExtraQuestion"
            />

            
            <HbButton  
                classValue="tfhb-btn flex-btn" 
                @click="QuestionPopupAdd()"
                :buttonText="$tfhb_trans('Add more questions')"
                icon="PlusCircle"  
                :hover_animation="false" 
                icon_position="left"
            />  

            <HbPopup :isOpen="QuestionPopup" @modal-close="QuestionPopup = false" max_width="400px" name="first-modal">
                <template #header> 
                    <h3>{{ $tfhb_trans('Add Question for Attendee') }}</h3>
                </template>
                <template #content>  
                    <HbQuestionForm 
                    :questions_data="questions_data"
                    @question-add="addExtraQuestion"
                    @question-cancel="QuestionPopupClose"
                    />
                </template> 
            </HbPopup>

        </div>

        <div class="tfhb-admin-card-box  tfhb-m-0 tfhb-full-width"  style="gap:4px 24px"   v-if="meeting.questions_type == 'existing'">  
  
               <!-- Time format -->
               
               <HbDropdown 
                    
                    v-model="meeting.questions_form_type"  
                    required= "true" 
                    :label="$tfhb_trans('Select Form Types')"  
                    width="50"
                    :selected = "1"
                    :placeholder="$tfhb_trans('Select Form Types')"   
                    :option = "[
                        {'name': 'Contact Form 7', 'value': 'wpcf7' },  
                        {'name': 'Fluent Forms', 'value': 'fluent-forms', disable:  integrations.fluent_status},  
                        {'name': 'Forminator Forms', 'value': 'forminator', disable:  integrations.forminator_status},  
                        // {'name': 'Gravity Forms', 'value': 'gravityforms', disable:  integrations.gravity_status},  
                    ]"
                    @tfhb-onchange="GetFormsData" 
                    
                />
              

                <!-- Time format -->
               <HbDropdown 
                    v-if = "meeting.questions_form_type != ''"
                    v-model="meeting.questions_form"  
                    required= "true" 
                    :label="$tfhb_trans('Select Form')"  
                    width="50" 
                    :placeholder="$tfhb_trans('Select Form')"   
                    :option = "props.formsList.value" 
                   
                /> 
                <div  v-if="meeting.questions_form_type == 'wpcf7' && integrations.cf7_status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4"> {{ $tfhb_trans('Contact Form 7 is not connected.') }}  
                    <HbButton 
                        v-if="$user.role != 'tfhb_host'"
                        classValue="tfhb-btn flex-btn" 
                        @click="() => router.push({ name: 'SettingsIntegrations' })" 
                        :buttonText="$tfhb_trans('Please Configure')"
                    />  
                </div>
                <div  v-if="meeting.questions_form_type == 'fluent-forms' && integrations.fluent_status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4">  {{ $tfhb_trans('Fluent Forms is not connected.') }}  
                    <HbButton 
                        v-if="$user.role != 'tfhb_host'"
                        classValue="tfhb-btn flex-btn" 
                        @click="() => router.push({ name: 'SettingsIntegrations' })" 
                        :buttonText="$tfhb_trans('Please Configure')"
                    />  
                </div>
                <!-- <div  v-if="meeting.questions_form_type == 'gravityforms' && integrations.gravity_status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4">  {{ $tfhb_trans('Gravity Forms is not connected.') }} 
                    <HbButton 
                        v-if="$user.role != 'tfhb_host'"
                        classValue="tfhb-btn flex-btn" 
                        @click="() => router.push({ name: 'SettingsIntegrations' })" 
                        :buttonText="$tfhb_trans('Please Configure')"
                    />  
                </div> -->
                <div  v-if="meeting.questions_form_type == 'forminator' && integrations.forminator_status == true" class="tfhb-warning-message tfhb-flexbox tfhb-gap-4">  {{ $tfhb_trans('Forminator Forms is not connected.') }} 
                    <HbButton 
                        v-if="$user.role != 'tfhb_host'"
                        classValue="tfhb-btn flex-btn" 
                        @click="() => router.push({ name: 'SettingsIntegrations' })" 
                        :buttonText="$tfhb_trans('Please Configure')"
                    />  
                </div>

        </div>

        <div class="tfhb-submission-btn"> 
            <HbButton  
                classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
                @click="emit('update-meeting')"
                :buttonText="$tfhb_trans('Save & Continue')"
                icon="ChevronRight" 
                hover_icon="ArrowRight" 
                :hover_animation="true"
                :pre_loader="props.update_preloader"
            />  
        </div>
        <!--Bookings -->
    </div>
</template>