<script setup> 
import { __ } from '@wordpress/i18n';
// Use children routes for the tabs 
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import { toast } from "vue3-toastify";
import useValidators from '@/store/validator'
const { errors, isEmpty } = useValidators();

import HbFileUpload from '@/components/form-fields/HbFileUpload.vue';  
import HbButton from '@/components/form-fields/HbButton.vue'; 
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue'; 
import HbPopup from '@/components/widgets/HbPopup.vue'; 


// Store  
import { importExport } from '@/store/settings/importExport';

 
const ExportAsCSV = ref(false);
const exportData = reactive({
    type: 'Json',
    select_export: [], 
});

const changeImportFileData = (event) => {    
    let file = event.target.files[0]; 
   importExport.readAllImportData(file);
   
}
const readImportDataChange = (file) => {    
    importExport.readAllImportData(file);
   
} 
const clickToNextMapping = () => {
    // file is empty 
    if(importExport.allData.import_file == '' || importExport.allData.import_file == null){
        toast.error('Please choose file to import!', {
            position: 'bottom-right', // Set the desired position
            autoClose: 1500,
        });
        return false;
    }
    window.scrollTo(0, 0);
    importExport.import_pre_loader = true;
    importExport.allData.steps = 'mapping';
    importExport.import_pre_loader = false;
}

onBeforeMount(() => {  
});


</script>
<template>
    
    <div :class="{ 'tfhb-skeleton': false }" class="thb-event-dashboard">
  
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('Import/Export') }}</h1>  
                <p>{{ $tfhb_trans('Import/Export your data') }}</p>
            </div>
            <div class="thb-admin-btn right"> 
                <HbButton 
                    classValue="tfhb-btn  secondary-btn tfhb-flexbox tfhb-gap-8" 
                    @click="ExportAsCSV = true"
                    :buttonText="$tfhb_trans('Export')"
                    icon="FileDown"   
                    :hover_animation="false" 
                    icon_position = 'left'
                />
            </div> 
        </div>
        <!-- Export CSV POPup -->
        <HbPopup  :isOpen="ExportAsCSV" @modal-close="ExportAsCSV = false" max_width="500px" name="first-modal" gap="32px">
            <template #header>  
                <h3>{{$tfhb_trans('Export Hydra Booking Data as Json')}}</h3>
            </template>

            <template #content>  
                <HbCheckbox  
                    required= "true"
                    v-model="exportData.select_export"
                    name="request_header"
                    :label="$tfhb_trans('Select What to Export')"
                    :groups="true" 
                    :width="100"
                    :options="['Settings', 'Hosts', 'Meetings', 'Bookings']"  
                /> 
                <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width"> 
                    <HbButton 
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="importExport.exportAllData(exportData)"
                        :buttonText="$tfhb_trans('Export Now')"
                        icon="ChevronRight"   
                        hover_icon="ArrowRight"
                        :pre_loader="exportAsPreloader"
                        :hover_animation="true" 
                        icon_position = 'right'
                    /> 
                
                </div>
            </template> 
        </HbPopup>
    <!-- Export CSV POPup -->
        <div class=" tfhb-flexbox tfhb-justify-center tfhb-full-width">
            <!-- Getting start with imported data -->
            <div v-if="importExport.allData.steps =='start'" class="tfhb-import-wrap tfhb-full-width tfhb-flexbox tfhb-gap-24">     
                <div class="tfhb-admin-title" > 
                    <h3>{{ $tfhb_trans(`Let’s get your data in!`) }}</h3> 
                    <p>{{ $tfhb_trans('Need a sample template?') }} <a href="#">{{ $tfhb_trans('Download one') }} </a> {{ $tfhb_trans('here.') }}</p>
                </div>
                {{importExport.allData.import_column}}
                <HbFileUpload
                    name="dashboard_logo"
                    v-model= "importExport.allData.import_file"
                    :label = "$tfhb_trans('Choose images or drag & drop it here.')"
                    :subtitle = "$tfhb_trans('Json file, Max 5 MB')"
                    :btn_label = "$tfhb_trans('Browse file')"
                    file_size ="5"
                    file_format ="json"
                    @change="changeImportFileData"
                    @tfhbChange="readImportDataChange"
                    :wp_media = "false" 

                    width="100"
                />
                <!-- <HbCheckbox 
                    v-model="importExport.meeting.is_overwrite"
                    :label="$tfhb_trans('Overwrite existing data')"
                    name="meeting_is_overwrite"
                /> -->
                <div class="tfhb-import-btn-wrap tfhb-flexbox tfhb-justify-end tfhb-full-width">
                    <HbButton 
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="clickToNextMapping"
                        :buttonText="$tfhb_trans('Next step')"
                        icon="ChevronRight"   
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        icon_position = 'right'
                    /> 
                </div>
            </div> 

            <!-- column mapping -->
            <div v-if="importExport.allData.steps =='mapping'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24 tfhb-full-width">    
                <div class="tfhb-admin-title" > 
                    <h3>{{ $tfhb_trans('Which data you want to import') }}</h3> 
                    <p>{{ $tfhb_trans('Select data which data you want to import in your website') }}</p>
                </div> 
                <div class="tfhb-flexbox tfhb-gap-12 tfhb-full-width">
                   {{importExport.allData.column}}
                   {{ importExport.allData.select_import }}
                    <HbCheckbox  
                        required= "true"
                        v-model="importExport.allData.select_import"
                        name="request_header"
                        :label="$tfhb_trans('Select What to Import')"
                        :groups="true" 
                        :width="100"
                        :options="importExport.allData.column"  
                    /> 
                </div>
                
                <div class="tfhb-import-btn-wrap tfhb-flexbox tfhb-justify-end tfhb-full-width">
                    <div class="tfhb-flexbox tfhb-gap-8">
                        <!--   -->
                        <h3 v-if="importExport.importing"> {{ $tfhb_trans('Importing..') }}  {{ importExport.progress }} %</h3>
                        <HbButton 
                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                            @click="importExport.importMeeting()"
                            :buttonText="$tfhb_trans('Looks Good, Start Import')"
                            icon="ChevronRight"   
                            hover_icon="ArrowRight"
                            :pre_loader="importExport.importing"
                            :hover_animation="true" 
                            :disabled="importExport.importing"
                            icon_position = 'right'
                        /> 
                    </div>
                </div>
            </div> 

            <!-- Confirmation -->
            <div v-if="importExport.allData.steps == 'completed'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24 tfhb-full-width">    
            
                <div class="tfhb-flexbox tfhb-gap-12 tfhb-full-width">
                    <img  style="margin: 0 auto;"  :src="$tfhb_url+'/assets/images/confirmed.svg'" alt="" >
                </div>
                <div class="tfhb-admin-title tfhb-full-width" style="text-align: center;" > 
                    <h3>{{ $tfhb_trans('Import completed!') }}</h3> 
                    <p>{{ $tfhb_trans('Your data has been successfully added.') }}</p>
                </div> 
                
                <div class="tfhb-import-btn-wrap tfhb-flexbox tfhb-justify-center tfhb-full-width">
                    <div> 
                        <HbButton 
                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                            @click="router.push({ name: 'MeetingsLists' })"
                            :buttonText="$tfhb_trans('Go to meetings')"
                            icon="ChevronRight"   
                            hover_icon="ArrowRight" 
                            :hover_animation="true" 
                            icon_position = 'right'
                        /> 
                    </div>
                </div>
            </div>

        </div>
    </div>
 
</template>

<style scoped lang="scss">
 .tfhb-import-cotent{ 
    .tfhb-import-wrap{
        max-width: 670px;
        width: 100%;
        padding: 32px;
        border-radius: 8px;
        background: var(--Surface-Secondary, #FFF);
        .tfhb-admin-title{
            margin-bottom: 0 !important;
            p {
                a{
                    color: #2E6B38;
                    text-decoration: underline !important;
                }
            }
        }
        .tfhb-import-export-column-wrap{
            .import-column-status, .import-column-arrow {
                text-align: center;
            }
            .tfhb-import-export-column-name{
                padding: 8px 8px 8px 12px;
                border-radius: 6px;
                background: var(--Surface-Primary, #F9FBF9);
                span{
                    font-size: 15px;
                }
            }
             
        }
    }
 }
</style>