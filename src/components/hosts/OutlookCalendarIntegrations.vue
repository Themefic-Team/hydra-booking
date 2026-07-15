<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field  
import HbSelect from '@/components/form-fields/HbSelect.vue' 
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbRadio from '@/components/form-fields/HbRadio.vue';
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
const outlookCalPopup = ref(false);

 

const props = defineProps([
    'outlook_calendar', 
    'class', 
    'display', 
])
const emit = defineEmits([ "update-integrations", ]);  

const storedOptionData = (data) => {
    // if data is undefined
    if(data == undefined){
        return [];
    }
    let options = [];
    // data suild be array single array
    data.forEach((item, index) => {  
        // fist item id auto selected
        if(index == 0 && props.outlook_calendar.selected_calendar_id == ''){
            props.outlook_calendar.selected_calendar_id =  props.outlook_calendar.tfhb_outlook_calendar.email || item.id;
        }
        options.push({
            value: item.id,
            label: item.title,
        });
    }); 
    return options;
}

const disconnectIntegration = () => {
  

    props.outlook_calendar.tfhb_outlook_calendar  = undefined;
    props.outlook_calendar.selected_calendar_id  = '';
    props.outlook_calendar.two_way_sync = 0;
    props.outlook_calendar.sync_interval = 15;
    outlookCalPopup.value = false;
    emit('update-integrations', 'outlook_calendar', props.outlook_calendar);
}
const updateOutlookCalendarSettings = () => {
    emit('update-integrations', 'outlook_calendar', props.outlook_calendar);
    outlookCalPopup.value = false;
}
</script>
 
<template> 
      <!-- Zoom Integrations  -->
      <div  class="tfhb-integrations-single-block tfhb-admin-card-box "
        :class="props.class,{
            'tfhb-pro': !$tfhb_is_pro || !$tfhb_license_status,
        }"
      > 
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/outlook-calendar.png'" alt="" >
            </span> 

            <div class="cartbox-text"> 
                <h3>{{ $tfhb_trans('Outlook Calendar') }}</h3> 
                <p>{{ $tfhb_trans('Connect your Outlook Calendar to sync your booked events.') }}</p>

            </div>
        </div> 
        <div v-if="$tfhb_is_pro == false || $tfhb_license_status == false" class="tfhb-integrations-single-block-btn tfhb-flexbox"> 
            <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size=20 /> {{ $tfhb_trans('Pro') }}</span>
           
        </div>
      
        <div v-else class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- Checke -->
            <!-- <button @click="gCalPopup = true" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ outlook_calendar.connection_status == 1 ? 'Connected' : 'Connect'  }} <Icon name="ChevronRight" size=18 /></button> -->
             <!-- a tag for get access token  -->
            <!-- <a   :href="'https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/calendar&redirect_uri='+outlook_calendar.redirect_url+'&response_type=code&client_id='+outlook_calendar.client_id+'&access_type=online'" target="_blank"class="tfhb-btn tfhb-flexbox tfhb-gap-8">Get Access Token</a> -->
            <button   v-if="outlook_calendar.connection_status == 1 && outlook_calendar.tfhb_outlook_calendar !== undefined && outlook_calendar.tfhb_outlook_calendar !== null"  @click="outlookCalPopup = true" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Settings') }}  <Icon name="ChevronRight" size=18 /></button>
              
            <a v-else :href="outlook_calendar.access_url" target="_blank"class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Get Access Token') }}</a>

        </div>

        <HbPopup v-if="outlook_calendar.tfhb_outlook_calendar !== undefined && $tfhb_is_pro == true" :isOpen="outlookCalPopup" @modal-close="outlookCalPopup = false" max_width="800px" name="first-modal">
            <template #header> 
                <!-- {{ outlook_calendar }} -->
                <h3>{{ $tfhb_trans('Outlook Calendar') }}</h3>
                <p v-if="outlook_calendar.tfhb_outlook_calendar.email">{{ outlook_calendar.tfhb_outlook_calendar.email }}</p>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Enable the calendars you want to check for conflicts to prevent double bookings.') }}
                </p>  
                <div class="tfhb-admin-card-box tfhb-flexbox  tfhb-gap-16  tfhb-m-0"  >   
                  
                    <HbRadio 
                        v-model="outlook_calendar.selected_calendar_id" 
                        :groups="true" 
                        :object="true"  
                        :name="'tfhb_calendar_items_'+index"
                        :options="storedOptionData(outlook_calendar.tfhb_outlook_calendar.items)"
                    />  
                </div> 
                <div class="tfhb-two-way-sync tfhb-full-width tfhb-mt-16 tfhb-mb-16">
                    <HbSwitch  
                        v-model="outlook_calendar.two_way_sync"  
                        name="host_two_way_sync"
                        :label="$tfhb_trans('Enable Two-Way Sync')"   
                    /> 
                    <div v-if="outlook_calendar.two_way_sync == 1" class="tfhb-sync-interval tfhb-mt-16">
                        <HbSelect
                            v-model="outlook_calendar.sync_interval"
                            name="host_sync_interval"
                            :label="$tfhb_trans('Checking Time (Sync Interval)')"
                            :placeholder="$tfhb_trans('Select Sync Interval')"
                            :option="{
                                '5': $tfhb_trans('Every 5 Minutes'),
                                '10': $tfhb_trans('Every 10 Minutes'),
                                '15': $tfhb_trans('Every 15 Minutes'),
                                '30': $tfhb_trans('Every 30 Minutes'),
                                '60': $tfhb_trans('Every 1 Hour'),
                                '360': $tfhb_trans('Every 6 Hours'),
                                '720': $tfhb_trans('Every 12 Hours'),
                                '1440': $tfhb_trans('Every 24 Hours')
                            }"
                        
                        />
                    </div>
                </div> 
                <div class="tfhb-submission-btn tfhb-mt-8 tfhb-mb-8 tfhb-flexbox tfhb-gap-8">
                    <HbButton  
                         @click.stop="updateOutlookCalendarSettings()"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"  
                        :buttonText="'Update Host Settings' "
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"  
                    />   
                    <HbButton  
                        v-if="outlook_calendar.tfhb_outlook_calendar.items !== undefined && outlook_calendar.tfhb_outlook_calendar.items.length != 0"
                         @click.stop="disconnectIntegration()"
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8"  
                        :buttonText="'Disconnect' "
                        icon="Unplug"  
                        icon_position="left"
                    />   
                   
                </div> 
            </template> 
        </HbPopup>
 
    </div>  
    <!-- Single Integrations  -->

</template>
 

<style scoped>
</style> 