<script setup> 
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import AvailabilityPopupSingle from '@/components/availability/AvailabilityPopupSingle.vue';
import AvailabilitySingle from '@/components/availability/AvailabilitySingle.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { toast } from "vue3-toastify"; 
import { Availability } from '@/store/availability';
const isModalOpened = ref(false);
const timeZone = reactive({}); 
const AvailabilityGet = reactive({
  data: [],
});
const GeneralSettings = reactive({});
const availabilityDataSingle = reactive({}) 
const skeleton = ref(true);
// 


const openModal = () => {
  availabilityDataSingle.value = {
    key: 0,
    id: 0,
    title: '',
    default_status: false,
    time_zone: '',
    date_status: 0,
    time_slots: [
        { 
            day: 'Monday',
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                },   
            ]
        },
        { 
            day: 'Tuesday', 
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        },
        { 
            day: 'Wednesday', 
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        },
        { 
            day: 'Thursday', 
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        },
        { 
            day: 'Friday', 
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        },
        { 
            day: 'Saturday', 
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        },
        { 
            day: 'Sunday', 
            status: 1,
            times: [
                {
                    start: '09:00',
                    end: '17:00',
                }
            ]
        }
    ],
    date_slots: [
    ]
  }; 
    availabilityDataSingle.value.time_zone = GeneralSettings.value.time_zone ? GeneralSettings.value.time_zone : '';
  
    availabilityDataSingle.value.time_slots = GeneralSettings.value.week_start_from ?  Availability.RearraingeWeekStart(GeneralSettings.value.week_start_from, availabilityDataSingle.value.time_slots) : availabilityDataSingle.value.time_slots;
 
    isModalOpened.value = true;
};

// Edit availability
const EditAvailabilitySettings = async (key, id, availability ) => { 
  availabilityDataSingle.value = availability;

  availabilityDataSingle.value.time_slots = GeneralSettings.value.week_start_from ?  Availability.RearraingeWeekStart(GeneralSettings.value.week_start_from, availabilityDataSingle.value.time_slots) : availabilityDataSingle.value.time_slots;
  isModalOpened.value = true;
}

const marAsDefault = async (key, id, availability ) => {  
    // Remove default from all
    if (AvailabilityGet.data.length > 1) {
        AvailabilityGet.data.forEach((item) => {
            item.default_status = false;
        });
    }
 
    AvailabilityGet.data[key].default_status = true;
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability/mark-as-default', {
            key: key,
            id: id,
            availabilityData: AvailabilityGet.data
        }, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                'capability': 'tfhb_manage_options'
            }
        });
        if (response.data.status) { 
            AvailabilityGet.data = response.data.availability;
            toast.success(response.data.message, {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        console.log(error);
    }
}

const closeModal = () => { 
  isModalOpened.value = false;
};


// Fetch generalSettings
const fetchAvailabilitySettings = async () => {

  try { 
      const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability', {
        headers: {
            'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            'capability': 'tfhb_manage_options'
        } 
      }); 
      if (response.data.status) { 
          timeZone.value = response.data.time_zone;     
          AvailabilityGet.data = response.data.availability; 
          GeneralSettings.value = response.data.general_settings; 


          skeleton.value = false;
      }
  } catch (error) {
      console.log(error);
  } 
}

// Fetch generalSettings pass value update avaulability
const fetchAvailabilitySettingsUpdate = async (data) => {
  AvailabilityGet.data = data; 
}

// Fetch generalSettings pass value update avaulability
const deleteAvailabilitySettings = async (key, id ) => { 
  const deleteAvailability = {
    key: key,
    id: id
  }
  try { 
      // const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability/'+key); 
      const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability/delete', deleteAvailability, {
        headers: {
            'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            'capability': 'tfhb_manage_options'
        }
      } );
      if (response.data.status) { 
        AvailabilityGet.data = response.data.availability;
         
        toast.success(response.data.message, {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        }); 
      }
  } catch (error) {
      console.log(error);
  }
}



onBeforeMount(() => { 
  fetchAvailabilitySettings();
});

 

</script>
<template>
     <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard ">
    <div  class="tfhb-dashboard-heading">
        <div class="tfhb-admin-title tfhb-m-0"> 
            <h1 >{{ __('Availability', 'hydra-booking') }}</h1> 
            <p>{{ __('Set up booking times when you are available', 'hydra-booking') }}</p>
        </div>
        <div class="thb-admin-btn right"> 
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="openModal" 
                :buttonText="__('Add New Availability', 'hydra-booking')"
                icon="PlusCircle"  
                icon_position="left"
            />  
        </div> 
    </div>
    <div class="tfhb-content-wrap tfhb-flexbox tfhb-gap-tb-24"> 
         <AvailabilitySingle  v-for="(availability, key) in AvailabilityGet.data" :availability="availability" :key="key" @delete-availability="deleteAvailabilitySettings(key, availability.id)" @edit-availability="EditAvailabilitySettings(key, availability.id, availability)"  @mark-as-default="marAsDefault(key, availability.id, availability)"  />

     
         <AvailabilityPopupSingle v-if="isModalOpened" max_width="800px !important" :timeZone="timeZone.value" :display_overwrite="true"  :availabilityDataSingle="availabilityDataSingle.value" :isOpen="isModalOpened" @modal-close="closeModal" :is_host="false" @update-availability="fetchAvailabilitySettingsUpdate" />
    
    </div>
</div>
 
</template>