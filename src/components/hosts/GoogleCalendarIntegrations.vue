<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbSelect from '@/components/form-fields/HbSelect.vue' 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbRadio from '@/components/form-fields/HbRadio.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
const gCalPopup = ref(false);

 

const props = defineProps([
    'google_calendar', 
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
        if(index == 0 && props.google_calendar.selected_calendar_id == ''){
            props.google_calendar.selected_calendar_id =  props.google_calendar.tfhb_google_calendar.email;
        }
        options.push({
            value: item.id,
            label: item.title,
        });
    }); 
    return options;
}

const disconnectIntegration = () => {

    // empty unset props.google_calendar.tfhb_google_calendar 

    props.google_calendar.tfhb_google_calendar  = undefined;
    props.google_calendar.selected_calendar_id  = '';
    gCalPopup.value = false;
    emit('update-integrations', 'google_calendar', props.google_calendar);
}
</script>
 
<template>  
      <!-- Zoom Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box ">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/google-calendar.png'" alt="">
            </span> 

            <div class="cartbox-text">
                <h3>{{ $tfhb_trans('Google Calendar/Meet') }}</h3> 
                <p>{{ $tfhb_trans('Connect your Google Calendar/Meet to sync your booked events.') }}</p>

            </div>
        </div> 
        
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox tfhb-justify-between">
            <!-- Checke -->
           
            <button  v-if="google_calendar.status != '1' && $user.role == 'tfhb_host'"   class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Not Connected') }} </button>

            <router-link  v-else-if="google_calendar.status != '1'" to="/settings/integrations#all" class="tfhb-btn  tfhb-flexbox tfhb-gap-8"> {{ $tfhb_trans('Go To Settings') }}  <Icon name="ArrowUpRight" size="20" /> </router-link>

            <button  v-else-if="google_calendar.connection_status == 1 && google_calendar.tfhb_google_calendar !== undefined && google_calendar.tfhb_google_calendar != null "  @click="gCalPopup = true" class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Settings') }}  <Icon name="ChevronRight" size=18 /></button>
             
            <a v-else :href="google_calendar.access_url" target="_blank"class="tfhb-btn tfhb-flexbox tfhb-gap-8">{{ $tfhb_trans('Get Access Token') }}</a>

        </div>

        <HbPopup v-if="google_calendar.tfhb_google_calendar !== undefined " :isOpen="gCalPopup" @modal-close="gCalPopup = false" max_width="800px" name="first-modal">
            <template #header> 
                <!-- {{ google_calendar }} -->
                <h3>{{ $tfhb_trans('Google Calendar') }}</h3>
                <p v-if="google_calendar.tfhb_google_calendar.email">{{ google_calendar.tfhb_google_calendar.email }}</p>
                
            </template>

            <template #content>  
                <p>
                    {{ $tfhb_trans('Enable the calendars you want to check for conflicts to prevent double bookings.') }}
                </p> 
                <div class="tfhb-admin-card-box tfhb-flexbox  tfhb-gap-16  tfhb-m-0"  >    
                    <HbRadio 
                        v-model="google_calendar.selected_calendar_id" 
                        :groups="true" 
                        :object="true"  
                        :name="'tfhb_calendar_items_'+index"
                        :options="storedOptionData(google_calendar.tfhb_google_calendar.items)"
                    />  
                </div>
                <div class="tfhb-submission-btn tfhb-mt-8 tfhb-mb-8 tfhb-flexbox tfhb-gap-8">
                    <HbButton  
                         @click.stop="emit('update-integrations', 'google_calendar', google_calendar)"
                        classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"  
                        :buttonText="'Update Host Settings' "
                        icon="ChevronRight" 
                        hover_icon="ArrowRight" 
                        :hover_animation="true"  
                    />   
                    <HbButton  
                        v-if="google_calendar.tfhb_google_calendar.items !== undefined && google_calendar.tfhb_google_calendar.items.length != 0"
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