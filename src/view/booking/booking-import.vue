<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import { useRouter, RouterView, onBeforeRouteLeave  } from 'vue-router' 

import { toast } from "vue3-toastify"; 
import Icon from '@/components/icon/LucideIcon.vue' 
import HbPreloader from '@/components/icon/HbPreloader.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbButton from '@/components/form-fields/HbButton.vue'; 
import HbFileUpload from '@/components/form-fields/HbFileUpload.vue';  
import HbCheckbox from '@/components/form-fields/HbCheckbox.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';

import { importExport } from '@/store/settings/importExport';


const router = useRouter();
onBeforeMount(() => {   
    importExport.GetImportExportData();
});

const changeImportFileData = (event) => {    
    let file = event.target.files[0]; 
   importExport.readHostImportData(file);
   
}
const readImportDataChange = (file) => {    
    importExport.readBookingImportData(file);
   
} 
const clickToNextMapping = () => {
    // file is empty 
    if(importExport.booking.import_file == '' || importExport.booking.import_file == null){
        toast.error('Please choose file to import!', {
            position: 'bottom-right', // Set the desired position
            autoClose: 1500,
        });
        return false;
    }
    window.scrollTo(0, 0);
    importExport.import_pre_loader = true;
    importExport.booking.steps = 'mapping';
    importExport.import_pre_loader = false;
}

 

</script>
<template>
 
 <div class="tfhb-import-cotent tfhb-flexbox tfhb-justify-center" :class="{ 'tfhb-skeleton': skeleton }">
    <!-- Getting start with imported data -->
    <div v-if="importExport.booking.steps =='start'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24">     
        
        <div class="tfhb-admin-title" > 
            <h3>{{ $tfhb_trans(`Let’s get your data in!`) }}</h3> 
            <p>{{ $tfhb_trans('Need a sample template?') }} <a href="#">{{ $tfhb_trans('Download one') }} </a> {{ $tfhb_trans('here.') }}</p>
        </div>
        <HbFileUpload
            name="dashboard_logo"
            v-model= "importExport.booking.import_file"
            :label = "$tfhb_trans('Choose images or drag & drop it here.')"
            :subtitle = "$tfhb_trans('CSV file, Max 5 MB')"
            :btn_label = "$tfhb_trans('Browse file')"
            file_size ="5"
            file_format ="csv"
            @change="changeImportFileData"
            @tfhbChange="readImportDataChange"
            :wp_media = "false" 

            width="100"
         />
         <HbCheckbox 
            v-model="importExport.booking.is_overwrite"
            :label="$tfhb_trans('Overwrite if booking is already exists')"
            name="is_overwrite"
        />
        <HbCheckbox 
            v-model="importExport.booking.is_default_meeting"
            :label="$tfhb_trans('Select default meeting if meeting id is not found')"
            name="is_create_new_user"
        /> 
        <HbDropdown   
            v-if="importExport.booking.is_default_meeting == true"
            v-model="importExport.booking.default_meeting_id"    
            width="100" 
            :filter="true"
            :placeholder="$tfhb_trans('Select Meeting')"   
            :option = "importExport.meeting_list"

        />  
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
    <div v-if="importExport.booking.steps =='mapping'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24">    
        <div class="tfhb-admin-title" > 
            <h3>{{ $tfhb_trans('Map properties') }}</h3> 
            <p>{{ $tfhb_trans('Match your file’s columns to our fields. We’ll help you get it right') }}</p>
        </div> 
        <div class="tfhb-flexbox tfhb-gap-12 tfhb-full-width">
            <div class="tfhb-import-export-column-wrap tfhb-flexbox  tfhb-gap-4 tfhb-justify-between tfhb-full-width">
                
                <div style="width:43%" ><h4>{{$tfhb_trans('Column in your file')}}</h4></div>
                <div style="width:6%" ></div>
                <div style="width:43%" ><h4>{{$tfhb_trans('Attributes')}}</h4></div>
                <div class="import-column-status"></div>
                
            </div> 
            <div class="tfhb-import-export-column-wrap tfhb-flexbox  tfhb-gap-4 tfhb-justify-between tfhb-full-width" v-for="(item, index) in importExport.booking.import_column"> 
                <div style="width:43%" class="tfhb-import-export-column-name"><span>{{ item }}</span></div>
                <div style="width:6%" class="import-column-arrow"><Icon name="MoveRight" /></div>  
                <HbDropdown   
                    v-model="importExport.booking.rearrange_column[item]"    
                    width="43"
                    :selected = "item"
                    :placeholder="$tfhb_trans('Select Time Format')"   
                    :option = "importExport.booking.column"  
                    @change = "changeMeetingColumn(item, $event)"

                />  
                <div v-if="item === importExport.booking.rearrange_column[item]" class="import-column-status ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="#E6FAEE"/>
                        <path d="M9 12L11 14L15 10" stroke="#17723F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div v-else-if="importExport.booking.rearrange_column[item] == ''" class="import-column-status ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="#FEECEE"/>
                        <path d="M15 9L9 15" stroke="#AC0C22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 9L15 15" stroke="#AC0C22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                </div>
                <div v-else class="import-column-status ">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="#FEECEE"/>
                        <path d="M15 9L9 15" stroke="#AC0C22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 9L15 15" stroke="#AC0C22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                </div>
                
            </div>
        </div>
        
        <div class="tfhb-import-btn-wrap tfhb-flexbox tfhb-justify-end tfhb-full-width">
            <div class="tfhb-flexbox tfhb-gap-8">
                <!--   -->
                <h3 v-if="importExport.importing"> {{ $tfhb_trans('Importing..') }}  {{ importExport.progress }} %</h3>
                <HbButton 
                    classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                    @click="importExport.importBooking()"
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
    <div v-if="importExport.booking.steps == 'completed'" class="tfhb-import-wrap tfhb-flexbox tfhb-gap-24">    
       
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
                    @click="router.push({ name: 'BookingLists' })"
                    :buttonText="$tfhb_trans('Go to Bookings')"
                    icon="ChevronRight"   
                    hover_icon="ArrowRight" 
                    :hover_animation="true" 
                    icon_position = 'right'
                /> 
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