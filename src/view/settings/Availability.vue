<script setup> 
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'
import AvailabilityPopupSingle from '@/components/availability/AvailabilityPopupSingle.vue';
import AvailabilitySingle from '@/components/availability/AvailabilitySingle.vue'; 
import HbPopup from '@/components/widgets/HbPopup.vue'; 
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
const deletePopup = ref(false);
// 


const openModal = () => {

    const local_time_zone = Intl.DateTimeFormat().resolvedOptions().timeZone;

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
    availabilityDataSingle.value.time_zone = GeneralSettings.value.time_zone ? GeneralSettings.value.time_zone : local_time_zone;
  
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
        }
      } );
      if (response.data.status) { 
        AvailabilityGet.data = response.data.availability;
        deletePopup.value = false;
         
        toast.success(response.data.message, {
            position: 'bottom-right', // Set the desired position
            "autoClose": 1500,
        }); 
      }
  } catch (error) {
      console.log(error);
  }
}

const deleteItemsData = reactive({
    key : 0,
    id : 0
});
// Delete Popup
const deletePopupToggle = (key, id) => { 
    // empty first deleteItemsData
    deleteItemsData.key = 0;
    deleteItemsData.id = 0;

    deletePopup.value = true;
    deleteItemsData.key = key;
    deleteItemsData.id = id;
}

onBeforeMount(() => { 
  fetchAvailabilitySettings();
});

 

</script>
<template>
     <div :class="{ 'tfhb-skeleton': skeleton }" class="thb-event-dashboard ">
    <div  class="tfhb-dashboard-heading">
        <div class="tfhb-admin-title tfhb-m-0"> 
            <h1 >{{ $tfhb_trans('Availability') }}</h1> 
            <p>{{ $tfhb_trans('Set up booking times when you are available') }}</p>
        </div>
        <div class="thb-admin-btn right"> 
            <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="openModal" 
                :buttonText="$tfhb_trans('Add New Availability')"
                icon="PlusCircle"  
                icon_position="left"
            />  
        </div> 
    </div>
    <div class="tfhb-content-wrap tfhb-flexbox tfhb-gap-tb-24"> 
         <AvailabilitySingle  v-for="(availability, key) in AvailabilityGet.data" :availability="availability" :key="key" @delete-availability="deletePopupToggle(key, availability.id)" @edit-availability="EditAvailabilitySettings(key, availability.id, availability)"  @mark-as-default="marAsDefault(key, availability.id, availability)"  />

     
         <AvailabilityPopupSingle v-if="isModalOpened" max_width="800px !important" :timeZone="timeZone.value" :display_overwrite="true"  :availabilityDataSingle="availabilityDataSingle.value" :isOpen="isModalOpened" @modal-close="closeModal" :is_host="false" @update-availability="fetchAvailabilitySettingsUpdate" />
    
    </div>

    <HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="542px" name="first-modal">
        <template #header> 

            
        </template>  

        <template #content>  
            <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                <div class="tfhb-close-icon">
                    <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                </div>
                <div class="tfhb-close-content">
                    <h3>{{ $tfhb_trans('Are you absolutely sure?') }}  </h3>  
                    <p>{{ $tfhb_trans('Data and bookings associated with this meeting will be deleted. It will not affect previously scheduled meetings.') }}</p>
                </div>
                <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click=" deletePopup = !deletePopup"
                        :buttonText="$tfhb_trans('Cancel')" 
                    />  
                    <HbButton  
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                        @click="deleteAvailabilitySettings(deleteItemsData.key, deleteItemsData.id)"
                        :buttonText="$tfhb_trans('Delete')"
                        icon="Trash2"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />
                    
                </div>
            </div> 
        </template> 
    </HbPopup>

</div>
 
</template>