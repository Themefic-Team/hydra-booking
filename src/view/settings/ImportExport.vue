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
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbProPopup from '@/components/widgets/HbProPopup.vue'; 
// Store  
import { importExport } from '@/store/settings/importExport';

 
const ExportAsCSV = ref(false);
const ProPopup = ref(false);
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
    importExport.GetImportExportData();
    importExport.allData.steps = 'init';
    importExport.allData.import_file = null; 
});

const exportAsJson = () => {
    importExport.exportAllData(exportData)
    ExportAsCSV.value= false;
}
// router.push({ name: 'dashboard' }), importExport.allData.steps = 'start'
const goToDashboard = () => {
    window.location.reload();
} 
</script>
<template>
    <HbProPopup  v-if="tfhb_is_pro == false || $tfhb_license_status == false" :isOpen="ProPopup" @modal-close="ProPopup = false" max_width="500px" name="first-modal" gap="32px" />
    <div :class="{ 'tfhb-skeleton': false }" class="thb-event-dashboard">
  
        <div  class="tfhb-dashboard-heading ">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h1 >{{ $tfhb_trans('Import/Export') }}</h1>  
                <p>{{ $tfhb_trans('Easily back up or transfer your booking data') }}</p>
            </div>
            <div class="thb-admin-btn right">
                  <a href="https://themefic.com/docs/hydrabooking" target="_blank" class="tfhb-btn tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('View Documentation') }}<Icon name="ArrowUpRight" size=20 /></a>
            
                
            </div> 
        </div>
        <!-- Export CSV POPup -->
        <HbPopup v-if="$tfhb_is_pro == true && $tfhb_license_status == true " :isOpen="ExportAsCSV" @modal-close="ExportAsCSV = false" max_width="500px" name="first-modal" gap="32px">
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
                        @click="exportAsJson"
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
            <div v-show="importExport.allData.steps == 'init'" class="tfhb-admin-card-box tfhb-general-card tfhb-flexbox tfhb-gap-24 tfhb-justify-between tfhb-flex-col tfhb-mt-32">  
                <h3 class="tfhb-flexbox align-ceter tfhb-gap-8 tfhb-justify-normal">
                    {{ $tfhb_trans(`What would you like to do?`) }}   
                </h3> 
                <div class="tfhb-flexbox tfhb-gap-16 tfhb-justify-between">
                    <HbButton 
                        classValue="tfhb-btn   boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : ExportAsCSV = true"
                        :buttonText="$tfhb_trans('Export')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />
                    <HbButton 
                        classValue="tfhb-btn   boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.allData.steps = 'start'"
                        :buttonText="$tfhb_trans('Import')"
                        icon="FileUp"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />
                </div>
            </div>    
            <div v-show="importExport.allData.steps == 'start' " class="tfhb-import-wrap tfhb-full-width tfhb-flexbox tfhb-gap-24">     
                <div v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-meeting-limit tfhb-flexbox tfhb-gap-16" >
                    <div class=" tfhb-pro"> 
                        <h3 class="tfhb-flexbox align-ceter tfhb-gap-8 tfhb-justify-normal">
                            {{ $tfhb_trans(`Let’s get your data in!`) }}  
                            <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
                        </h3> 
                        <p>{{ $tfhb_trans('Need a sample template?') }} <a href="#">{{ $tfhb_trans('Download one') }} </a> {{ $tfhb_trans('here.') }}</p> 
                    </div> 
                
                </div>
                <div v-else class="tfhb-admin-title " > 
                    
                    <h3 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal ">
                        <div class="prev-navigator" @click="importExport.allData.steps = 'init'" style="cursor: pointer;">
                            <Icon  name="ArrowLeft" size=20 />  
                        </div>
                        {{ $tfhb_trans(`Let’s get your data in!`) }}   
                    </h3> 
                        <p>{{ $tfhb_trans('Need a sample template?') }} <span  @click.stop="importExport.ExportSampleData('all')">{{ $tfhb_trans('Download one') }} </span> {{ $tfhb_trans('here.') }}</p>
                </div> 
                <HbFileUpload
                    name="dashboard_logo"
                    v-model= "importExport.allData.import_file"
                    :label = "$tfhb_trans('Choose images or drag & drop it here.')"
                    :subtitle = "$tfhb_trans('Json file, Max 5 MB')"
                    :btn_label = "$tfhb_trans('Browse file')"
                    file_size ="5"
                    file_format ="json" 
                    @tfhbChange="readImportDataChange"
                    :wp_media = "false" 

                    width="100"
                /> 
                <div class="tfhb-import-btn-wrap tfhb-flexbox tfhb-justify-end tfhb-full-width">
                    <HbButton 
                        v-if="$tfhb_is_pro == true && $tfhb_license_status == true"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                        @click="clickToNextMapping"
                        :buttonText="$tfhb_trans('Next step')"
                        icon="ChevronRight"   
                        :disabled = "Object.keys(importExport.allData.import_column).length == 0"  
                        hover_icon="ArrowRight" 
                        :hover_animation="true" 
                        icon_position = 'right'
                    /> 
                </div>
            </div>  
            <!-- column mapping --> 
            <div v-show="importExport.allData.steps =='mapping'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24 tfhb-full-width">    
                <div class="tfhb-admin-title" > 
                    <h3 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal ">
                        <div class="prev-navigator" @click="importExport.allData.steps = 'start'" style="cursor: pointer;">
                            <Icon  name="ArrowLeft" size=20 />  
                        </div>
                        {{ $tfhb_trans('Select Data to Import') }}


                    </h3> 
                    <p>{{ $tfhb_trans('Choose which data you would like to to import your website') }}</p>
                </div>  
                <div class="tfhb-flexbox tfhb-gap-12 tfhb-full-width">  
                    <div v-for="(item, index) in importExport.allData.import_column" :key="index" class="tfhb-admin-card-box tfhb-flexbox  tfhb-flexbox tfhb-gap-8tfhb-gap-12 tfhb-full-width">  
                        <HbCheckbox 
                            class="tfhb-select-import-label "
                            v-model="importExport.allData.select_import[item]"
                            :label="index"
                            :name="'mark_select_import'+index"
                        />  


                        <div v-if="index == 'Hosts' && importExport.allData.select_import[item] == true" class="tfhb-sub-checkbox  tfhb-flexbox tfhb-gap-8"  style="margin-left: 30px;"> 
                            <HbCheckbox 
                                v-model="importExport.allData.is_overwrite_host"
                                :label="$tfhb_trans('Overwrite if host is already exists')"
                                :name="'is_overwrite'+index"
                            />
                            <HbCheckbox 
                                v-model="importExport.allData.is_create_new_user"
                                :label="$tfhb_trans('Create new users if user id not exists')"
                                :name="'is_create_new_user'+index"
                            />
                        </div>

                        <div v-if="index == 'Meetings' && importExport.allData.select_import[item] == true" class="tfhb-sub-checkbox  tfhb-flexbox tfhb-gap-8"  style="margin-left: 30px;">
                            <HbCheckbox 
                                v-model="importExport.allData.is_overwrite_meeting"
                                :label="$tfhb_trans('Overwrite existing data')"
                                name="meeting_is_overwrite"
                            />
                        </div>

                        <div v-if="index == 'Bookings' && importExport.allData.select_import[item] == true" class="tfhb-sub-checkbox  tfhb-flexbox tfhb-gap-8"  style="margin-left: 30px;">
                            <HbCheckbox 
                                v-model="importExport.allData.is_overwrite_booking"
                                :label="$tfhb_trans('Overwrite if booking is already exists')"
                                name="is_overwrite_booking"
                            />
                            <HbCheckbox 
                                v-model="importExport.allData.is_default_meeting"
                                :label="$tfhb_trans('Select default meeting if meeting id is not found')"
                                name="is_create_new_user"
                            /> 
                            <HbDropdown   
                                v-if="importExport.allData.is_default_meeting == true"
                                v-model="importExport.allData.default_meeting_id"    
                                width="100" 
                                :filter="true"
                                :placeholder="$tfhb_trans('Select Meeting')"   
                                :option = "importExport.meeting_list"

                            /> 
                        </div>

                    </div>
        

                </div> 
            
                
                <div class="tfhb-import-btn-wrap tfhb-flexbox tfhb-justify-end tfhb-full-width">
                    <div class="tfhb-flexbox tfhb-gap-8">
                        <!--   -->
                        <h3 v-if="importExport.importing"> {{ $tfhb_trans('Importing..') }}  {{ importExport.progress }} %</h3>
                        <HbButton 
                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                            @click="importExport.importAllData()"
                            :buttonText="$tfhb_trans('Looks Good, Start Import')"
                            icon="ChevronRight"   
                            hover_icon="ArrowRight"
                            :pre_loader="importExport.importing"
                            :hover_animation="true" 
                            :disabled="importExport.importing || (importExport.allData.select_import['settings'] == 0 && importExport.allData.select_import['tfhb_hosts'] == 0 && importExport.allData.select_import['tfhb_meetings'] == 0 && importExport.allData.select_import['tfhb_bookings'] == 0)"
                            icon_position = 'right'
                        /> 
                    </div>
                </div>
            </div>  
            <!-- Confirmation --> 
            <div v-show="importExport.allData.steps == 'completed'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24 tfhb-full-width">    
            
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
                            @click="goToDashboard()"
                            :buttonText="$tfhb_trans('Go to Import/Export')"
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
  
  .tfhb-select-import-label label {
	font-size: 20px !important;
}
.tfhb-import-wrap{
    max-width: 670px;
    width: 100%;
    padding: 32px;
    border-radius: 8px;
    background: var(--Surface-Secondary, #FFF);
    .tfhb-admin-card-box{
            margin-bottom: 0 !important;
        }
    
    .tfhb-admin-title{
        margin-bottom: 0 !important;
        p {
            span{
                color: #2E6B38 !important;
                text-decoration: underline !important;
                cursor: pointer;
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
</style>