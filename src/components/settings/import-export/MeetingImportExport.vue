<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router'  
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbText from '@/components/form-fields/HbText.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbMultiSelect from '@/components/form-fields/HbMultiSelect.vue';
import HbRadio from '@/components/form-fields/HbRadio.vue';
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import HbButton from '@/components/form-fields/HbButton.vue';

// Store 
import { importExport } from '@/store/settings/importExport';


// Extra Qestion Data
const questions_data =  reactive({});

// Import Data Function 
const readImportDdata = (event) => { 
   importExport.readMeetingImportData(event);
   
}
const ExportAsCSV = ref(false);
const exportData = reactive({
    type: 'CSV',
    date_range: 'days',
    start_date: '',
    end_date: '',
});

 
</script>

<template>  

<!-- Export CSV POPup -->
    <HbPopup  :isOpen="ExportAsCSV" @modal-close="ExportAsCSV = false" max_width="500px" name="first-modal" gap="32px">
        <template #header>  
            <h3>{{$tfhb_trans('Export Meeting as')}} {{$tfhb_trans(exportData.type)}}</h3>
        </template>

        <template #content> 
            
            <HbRadio  
                required= "true"
                v-model="exportData.date_range"
                name="request_header"
                :label="$tfhb_trans('Date Range')"
                :groups="true" 
                :options="[
                    {'label': 'Today', 'value': 'days'},  
                    {'label': 'Last 7 Days', 'value': 'weeks'},
                    {'label': 'Current Month', 'value': 'months'},
                    {'label': 'Last Year', 'value': 'years'}, 
                    {'label': 'All', 'value': 'all'}, 
                    {'label': 'Custom', 'value': 'custom'} 
                ]" 
            />
        <div v-if="exportData.date_range == 'custom'" class="custom-date-range" >
            <label for="">{{ $tfhb_trans('Select Date Range') }}</label>
            <div class="tfhb-filter-dates tfhb-flexbox">
                
                <HbDateTime 
                    v-model="exportData.start_date"
                    :label="''" 
                    width="40"
                    enableTime='true'
                    icon="CalendarDays"
                    :placeholder="$tfhb_trans('From')"   
                /> 
                <div class="tfhb-calender-move-icon">
                    <Icon name="MoveRight" size="20px" /> 
                </div>
                <HbDateTime 
                    v-model="exportData.end_date"
                    :label="''" 
                    width="40"
                    icon="CalendarDays"
                    enableTime='true'
                    :placeholder="$tfhb_trans('To')"  
                />  
            </div> 
        </div>

        <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width"> 
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                @click="importExport.exportMeetings(exportData)"
                :buttonText="$tfhb_trans('Export Meetings')"
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
    <div class="tfhb-admin-card-box"  >   
        <!-- {{ importExport }} -->
        {{ importExport.booking.column }}
        <div class="tfhb-dashboard-heading tfhb-flexbox tfhb-mb-16">
            <div class="tfhb-admin-title "> 
                <h3 >{{ $tfhb_trans('Import/Export Meetings from a CSV file') }}</h3> 
                <p>{{ $tfhb_trans('This tool allows you to import or merge booking data to your store from a CSV file.') }}</p>
            </div> 
            <HbButton 
                classValue="tfhb-btn  boxed-btn tfhb-flexbox tfhb-gap-8" 
                @click="ExportAsCSV = true"
                :buttonText="$tfhb_trans('Export as CSV')"
                icon="FileDown"   
                :hover_animation="false" 
                icon_position = 'left'
            />
        </div>

        <div class="tfhb-content-wrap "> 
            <!-- {{ importExport.booking.import_data }} -->
           <div class="tfhb-upload-csv tfhb-flexbox tfhb-gap-16">
                <div class="tfhb-hydra-content-wrap">      
                    <HbText  
                        v-model="importExport.meeting.import_file"
                        type="file"
                        required= "true"  
                        :label="$tfhb_trans('Select the file to import')"  
                        :width="100"
                        name="name"
                        selected = "1"
                         @change="readImportDdata" 
                        :placeholder="$tfhb_trans('Please select the file to import')" 
                    /> 
                    <br>
                   
                </div>  
           </div>
           <!-- Export Column -->
            <div v-if="importExport.meeting.import_column.length > 0"  class="tfhb-import-column-data tfhb-admin-card-box">
                {{importExport.meeting.rearrange_column}}
                <div class="tfhb-admin-title "> 
                    <h3 >{{ $tfhb_trans('Map CSV fields to Booking') }}</h3> 
                    <p>{{ $tfhb_trans('Select fields from your CSV file to map against booking fields, or to ignore during import.') }}</p>
                </div> 
                <!-- Time format -->
                <div class="tfhb-import-export-column-wrap tfhb-flexbox tfhb-gap-8" v-for="(item, index) in importExport.meeting.import_column">
                   
                    <HbDropdown 
                   
                        v-model="importExport.meeting.rearrange_column[item]"   
                        :label="item"
                        width="100"
                        :selected = "item"
                        :placeholder="$tfhb_trans('Select Time Format')"   
                        :option = "importExport.meeting.column"  
                    />
                 
                   
                </div>
                <br>
                <button @click="importExport.importMeeting" class="tfhb-btn boxed-btn flex-btn"><Icon name="Download" size=20 /> {{ $tfhb_trans('Run The Import') }}</button> 
                <!-- Time format --> 
            </div>
              
        </div>
    </div> 
</template>

<style scoped>
</style> 