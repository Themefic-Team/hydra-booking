<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount, computed } from 'vue';
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify";
import { Meeting } from '@/store/meetings';
import useValidators from '@/store/validator'
const { errors } = useValidators();

import HbText from '@/components/form-fields/HbText.vue'
import HbTextarea from '@/components/form-fields/HbTextarea.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue';

const itemsPerPage = ref(5);
const currentPage = ref(1);
const skeleton = ref(true);
const CategoryData = reactive({
  id: '',
  title: '',
  description: '',
});

const update_preloader = ref(false);
// Create and Update Category
const UpdateCategory = async (validator_field) => { 


    // Clear the errors object
    Object.keys(errors).forEach(key => {
        delete errors[key];
    });
    
    // Errors Added
    if(validator_field){
        validator_field.forEach(field => {

        const fieldParts = field.split('___'); // Split the field into parts
        if(fieldParts[0] && !fieldParts[1]){
            if(!CategoryData[fieldParts[0]]){
                errors[fieldParts[0]] = 'Required this field';
            }
        }
        if(fieldParts[0] && fieldParts[1]){
            if(!CategoryData[fieldParts[0]][fieldParts[1]]){
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
    }


    update_preloader.value = true;
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/categories/create-update', CategoryData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );
      
        if (response.data.status) {  

            // Categories add to the list
            Meeting.meetingCategory = response.data.category;

            // Set Default
            CategoryData.id = '';
            CategoryData.title = '';
            CategoryData.description = '';

            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
            update_preloader.value = false;
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
        update_preloader.value = false;
    }
}

// Edit Category
const editCategory = (data) => {
    CategoryData.id = data.id;
    CategoryData.title = data.name;
    CategoryData.description = data.description;
}

// Delete Category
const removeCategory = async (key) => { 
    const DeleteData = {
        id: key
    }
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/meetings/categories/delete', DeleteData, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );
      
        if (response.data.status) {    
            // Categories add to the list
            Meeting.meetingCategory = response.data.category;
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        toast.error('Action successful', {
            position: 'bottom-right', // Set the desired position
        });
    }
}

onBeforeMount(() => { 
    Meeting.fetchMeetingCategory();
    skeleton.value = false;
});


const totalPages = computed(() => {
  return Math.ceil(Meeting.meetingCategory.length / itemsPerPage.value);
});

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return Meeting.meetingCategory.slice(start, end);
});

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value += 1;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value -= 1;
  }
};


</script>
<template>
    
    <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard ">
  
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('Meeting Category') }}</h1> 
                <p>{{ $tfhb_trans('Create and edit Meeting Category to organize your meetings.') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=15 /></a>
            </div> 
        </div>
        <div class="tfhb-content-wrap">

            <div class="tfhb-category-warp tfhb-flexbox tfhb-align-flex-start tfhb-gap-0 tfhb-justify-between">
                <div class="tfhb-admin-card-box tfhb-category-create-box tfhb-flexbox tfhb-gap-tb-24">  
                    <HbText  
                        v-model="CategoryData.title"
                        required= "true"  
                        :label="$tfhb_trans('Category Title')" 
                        :errors="errors.title"
                        name="title"
                    /> 
                    <HbTextarea  
                        v-model="CategoryData.description"
                        required= "false"  
                        name="description"
                        :label="$tfhb_trans('Description')"  
                    /> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="UpdateCategory(['title'])" 
                        :buttonText="CategoryData.id ? $tfhb_trans('Update Category'): $tfhb_trans('Save Category')"
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :pre_loader="update_preloader"
                        :hover_animation="true"
                    />    
                 </div>  
                <div class="tfhb-category-list">
                    <table class="table" cellpadding="0" :cellspacing="0">
                        <thead>
                            <tr>
                                <th width="180">{{ $tfhb_trans('Name') }}</th>
                                <th>{{ $tfhb_trans('Description') }}</th>
                                <th width="120">{{ $tfhb_trans('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody v-if="paginatedCategories">
                            <tr v-for="category in paginatedCategories">
                                <td>
                                    {{category.name}}
                                </td>
                                <td>
                                    {{category.description}}
                                </td>
                                <td>
                                    <div class="tfhb-overrides-action tfhb-flexbox tfhb-gap-16 tfhb-justify-normal">
                                        <button class="question-edit-btn" @click="editCategory(category)">
                                            <Icon name="PencilLine" :width="16" />
                                        </button>
                                        <button class="question-edit-btn" @click="removeCategory(category.id)">
                                            <Icon name="Trash" :width="16"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="tfhb-booking-details-pagination tfhb-flexbox tfhb-mt-32" v-if="totalPages > 1">
                        <div class="tfhb-prev-next-button">
                            <a href="#" @click.prevent="prevPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="currentPage === 1"><Icon name="ArrowLeft" width="20" />{{ $tfhb_trans('Previous') }}</a>
                        </div>
                        <div class="tfhb-pagination">
                            <ul class="tfhb-flexbox tfhb-gap-0 tfhb-justify-normal">
                                <li v-for="page in totalPages" :key="page" :class="{ active: page === currentPage }">
                                    <a href="#" @click.prevent="changePage(page)" :class="{ 'active-link': page === currentPage }">{{ page }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="tfhb-prev-next-button">
                            <a href="#" @click.prevent="nextPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="currentPage === totalPages">{{ $tfhb_trans('Next') }}<Icon name="ArrowRight" width="20" /></a>
                        </div>
                    </div>
                    
                </div>
            </div>
            


        </div>
    </div>
 
</template>