<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import axios from 'axios'  
import Icon from '@/components/icon/LucideIcon.vue' 
import HbButton from '@/components/form-fields/HbButton.vue'
import AvailabilityPopupSingle from '@/components/availability/AvailabilityPopupSingle.vue';
import AvailabilitySingle from '@/components/availability/AvailabilitySingle.vue';
import { toast } from "vue3-toastify"; 
import { Host } from '@/store/hosts';
import { Availability } from '@/store/availability';
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'

const emit = defineEmits(["availability-tabs", "save-host-info"]); 
const props = defineProps({
    hostId: {
        type: Number,
        required: true
    },
    settingsAvailabilityData: {
        type: Object,
        required: true
    },
    host: {
        type: Object,
        required: true
    },
    update_host_preloader: {
        type: Boolean,
        required: true
    }
});

const HostAvailability = reactive({
    availability_type: 'settings'
})


const isModalOpened = ref(false);
const timeZone = reactive({}); 
const AvailabilityGet = reactive({
  data: [],
});
const availabilityDataSingle = reactive({}) 
const skeleton = ref(true);

const GeneralSettings = reactive({});
const deletePopup = ref(false);
const openModal = () => {

  const local_time_zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  availabilityDataSingle.value = {
    host: props.hostId,
    user_id: props.host.user_id,
    key: 0,
    id: '',
    title: '',
    default_status: false,
    time_zone: '',
    date_status: 0,
    time_slots: [

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
        },
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
    ],
    date_slots: [],
    availability_id: ''
  };

  availabilityDataSingle.value.time_zone = Availability.GeneralSettings.time_zone ? Availability.GeneralSettings.time_zone : local_time_zone;
  
  availabilityDataSingle.value.time_slots = Availability.GeneralSettings.week_start_from ?  Availability.RearraingeWeekStart(Availability.GeneralSettings.week_start_from, availabilityDataSingle.value.time_slots) : availabilityDataSingle.value.time_slots;

  isModalOpened.value = true;
};

const closeModal = () => { 
  isModalOpened.value = false;
};

// Fetch  Use existing availability
const Settings_avalibility = ref();
const fetchAvailabilitySingle = async (setting) => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability/'+setting, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );
        if (response.data.status) { 
            Settings_avalibility.value = response.data; 
            Settings_avalibility.value.availability.time_slots = Availability.GeneralSettings.week_start_from ?  Availability.RearraingeWeekStart(Availability.GeneralSettings.week_start_from, Settings_avalibility.value.availability.time_slots) : Settings_avalibility.value.availability.time_slots;
            

        }
    } catch (error) {
        console.log(error);
    } 
}

const Settings_Avalibility_Callback = (value) => {
    // console.log(e);
    if(value){
        fetchAvailabilitySingle(value);
    }
}

// Fetch  Use Custom availability
const fetchAvailabilitySettings = async () => { 
    let data = {
        id: Host.hostInfo
    };  
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/availability', data, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        } );
        
        if (response.data.status) {    
            AvailabilityGet.data = response.data.availability; 
            timeZone.value = response.data.time_zone;      
        }else{
            toast.error(response.data.message, {
                position: 'bottom-right', // Set the desired position
            });
        }

    } catch (error) {
        console.log(error);
    } 
}

onBeforeMount(() => { 
    Host.fetchHost(props.hostId).then(() => {
        fetchAvailabilitySettings();
        fetchDefaultAvailabilitySingle(props.host.availability_id);
    });
    Availability.fetchAvailability();
});

// Edit availability
const EditAvailabilitySettings = async (key, id, availability ) => { 
  availability.time_slots = Availability.GeneralSettings.week_start_from ?  Availability.RearraingeWeekStart(Availability.GeneralSettings.week_start_from, availability.time_slots) : availability.time_slots;

  availabilityDataSingle.value = availability;
  availabilityDataSingle.value.id = key;
  availabilityDataSingle.value.host = props.hostId;
  isModalOpened.value = true;
}

// Fetch generalSettings pass value update avaulability
const fetchAvailabilitySettingsUpdate = async (data) => {
  AvailabilityGet.data = data; 
  props.host.availability = data; 
}

// Fetch generalSettings pass value update avaulability
const deleteAvailabilitySettings = async (key, id, user_id ) => { 
  const deleteAvailability = {
    key: key,
    id: id,
    user_id: user_id
  }
  try { 
      const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/hosts/availability/delete', deleteAvailability, {
        headers: {
            'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
        }
      } );
      if (response.data.status) { 
        AvailabilityGet.data = response.data.availability; 
        props.host.availability = response.data.availability; 
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

// Default Availability
const fetchDefaultAvailabilitySingle = async (setting) => {
    try { 
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/availability/'+setting, {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        }); 
        if (response.data.status) { 
            Settings_avalibility.value = response.data;
            Settings_avalibility.value.availability.time_slots = Availability.GeneralSettings.week_start_from ?  Availability.RearraingeWeekStart(Availability.GeneralSettings.week_start_from, Settings_avalibility.value.availability.time_slots) : Settings_avalibility.value.availability.time_slots;
        }
    } catch (error) {
        console.log(error);
    } 
}

// Date & Time Format
// Date & Time Format
const formatTime = (time) =>  {
    const [hour, minute] = time.split(':');
    const formattedHour = (parseInt(hour) % 12 || 12);
    const period = parseInt(hour) < 12 ? 'AM' : 'PM';
    return `${formattedHour}:${minute} ${period}`;
}
 
const formatTimeSlots = (timeSlots) =>  {
    return timeSlots.map(slot => {
    return `${formatTime(slot.start)} - ${formatTime(slot.end)}`
    }).join(', ');
}

const deleteItemsData = reactive({
    key : 0,
    id : 0,
    user_id : 0
});
// Delete Popup
const deletePopupToggle = (key, id, user_id) => { 
    // empty first deleteItemsData
    deleteItemsData.key = 0;
    deleteItemsData.id = 0;
    deleteItemsData.user_id = user_id;

    deletePopup.value = true;
    deleteItemsData.key = key;
    deleteItemsData.id = id;
    deleteItemsData.user_id = user_id;
}
</script>

<template>

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
                    @click="deleteAvailabilitySettings(deleteItemsData.key, deleteItemsData.id, deleteItemsData.user_id)"
                    :buttonText="$tfhb_trans('Delete')"
                    icon="Trash2"   
                    :hover_animation="false" 
                    icon_position = 'left'
                />
                
            </div>
        </div> 
    </template> 
</HbPopup>
<div class="tfhb-host-availability">
    <!-- {{host}} -->
    <div class="tfhb-availaility-tabs tfhb-mb-24">
        <ul class="tfhb-flexbox tfhb-gap-16 tfhb-justify-normal">
            <li class="tfhb-flexbox tfhb-gap-8" :class="'settings'==host.availability_type ? 'active' : ''" @click="emit('availability-tabs', 'settings')"><Icon name="Heart" :width="20" /> {{ $tfhb_trans('Use existing availability') }}</li>
            <li v-if="true == $user.caps.tfhb_manage_custom_availability" class="tfhb-flexbox tfhb-gap-8" :class="'custom'==host.availability_type ? 'active' : ''" @click="emit('availability-tabs', 'custom')"><Icon name="PencilLine" :width="20" />  {{ $tfhb_trans('Custom availability') }}</li>
        </ul>
    </div>

 
    <!-- Duration -->    
    <HbDropdown 
        v-model="props.host.availability_id" 
        required= "true" 
        :label="$tfhb_trans('Choose Schedule')"    
        :placeholder="$tfhb_trans('Choose Schedule')"   
        :option = "settingsAvailabilityData" 
        v-if="'settings'==host.availability_type"
        @tfhb-onchange="Settings_Avalibility_Callback"
    />
  
    <!-- Duration -->

    <!-- Settings Data -->
    <div class="tfhb-admin-card-box tfhb-flexbox tfhb-gap-24 tfhb-mt-24 tfhb-mb-24 tfhb-availability-details-wrap" v-if="Settings_avalibility && 'settings'== host.availability_type">  
        <div  class="tfhb-availability-schedule-single tfhb-schedule-heading tfhb-flexbox tfhb-full-width tfhb-justify-between">
            <div class="tfhb-admin-title"> 
                <h3> {{ $tfhb_trans('Schedule Preview') }} </h3>  
            </div>
            <div class="thb-admin-btn right"> 
                <span>{{ Settings_avalibility.availability.time_zone }}</span> 
            </div> 
        </div>
        
        <div v-for="(time_slot, key) in Settings_avalibility.availability.time_slots" :key="key" class="tfhb-availability-schedule-single tfhb-flexbox tfhb-align-baseline tfhb-full-width tfhb-gap-16"
            :class="{
                'tfhb-justify-between' : 1,
            }"
        >
            <div class="tfhb-swicher-wrap  tfhb-flexbox tfhb-gap-8">
                 <!-- Checkbox swicher -->
                 <label class="switch">
                        <input id="swicher" disabled v-model="time_slot.status" true-value="1" type="checkbox">
                        <span class="slider"></span>
                    </label>
                <label class="tfhb-schedule-swicher" for="swicher"> {{time_slot.day}}</label>
                <!-- Swicher -->
            </div>
            <div v-if="time_slot.status == 1" class="tfhb-availability-schedule-wrap"  style="width: 75%;"> 
                <div v-for="(time, tkey) in time_slot.times" :key="tkey" class="tfhb-availability-schedule-inner tfhb-flexbox">
                    <div class="tfhb-availability-schedule-time tfhb-flexbox tfhb-gap-8 tfhb-justify-between">
                        
                        <div class="tfhb-single-form-field" style="width: calc(45% - 12px);" selected="1">
                            <div class="tfhb-single-form-field-wrap tfhb-field-date">
                                <input type="text" data-input="true" class="flatpickr-input" :value="time.start" readonly="readonly">
                                <span class="tfhb-flat-icon">
                                    <Icon name="Clock4" size=20 />
                                </span>
                            </div>
                        </div>

                        <Icon name="MoveRight" size=20 /> 

                        <div class="tfhb-single-form-field" style="width: calc(45% - 12px);" selected="1">
                            <div class="tfhb-single-form-field-wrap tfhb-field-date">
                                <input type="text" data-input="true" class="flatpickr-input" :value="time.end" readonly="readonly">
                                <span class="tfhb-flat-icon">
                                    <Icon name="Clock4" size=20 />
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div v-else class="tfhb-availability-schedule-wrap" style="width: 75%;"> 
                <h5 class="tfhb-availability-schedule">{{ $tfhb_trans('Unavailable') }}</h5>
            </div>
        </div>
        
 

    </div>  

    <!-- Date Overrides -->
    <div class="tfhb-admin-card-box tfhb-m-0 tfhb-flexbox tfhb-full-width" v-if="Settings_avalibility && 'settings'==host.availability_type && Settings_avalibility.availability.date_slots > 0">  
        <div  class="tfhb-dashboard-heading tfhb-full-width" :style="{margin: '0 !important'}">
            <div class="tfhb-admin-title tfhb-m-0"> 
                <h3>{{ $tfhb_trans('Add date overrides') }} </h3>  
                <p>{{ $tfhb_trans('Add dates when your availability changes from your daily hours') }}</p>
            </div> 
        </div>

        <div class="tfhb-admin-card-box tfhb-m-0 tfhb-full-width" v-for="(date_slot, key) in Settings_avalibility.availability.date_slots" :key="key">
            <div class="tfhb-flexbox">
                <div class="tfhb-overrides-date">
                    <h4>{{ date_slot.date }}</h4>
                    <p class="tfhb-m-0">{{ date_slot.available!=1 ? formatTimeSlots(date_slot.times) : 'Unavailable' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="tfhb-dashboard-heading" v-if="'custom'==host.availability_type && true == $user.caps.tfhb_manage_custom_availability">
        <div class="tfhb-admin-title"> 
            <h3 >{{ $tfhb_trans('Availability') }}</h3> 
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

    <div class="tfhb-content-wrap tfhb-host-availability-list-wrap tfhb-flexbox" v-if="'custom'==host.availability_type">

        <AvailabilitySingle  v-for="(availability, key) in AvailabilityGet.data" :availability="availability" :key="key"  @edit-availability="EditAvailabilitySettings(key, availability.id, availability)" @delete-availability="deletePopupToggle(key, availability.id, host.user_id)" />

       
        <AvailabilityPopupSingle v-if="isModalOpened" max_width="850px !important" :timeZone="timeZone.value" :display_overwrite="true" :availabilityDataSingle="availabilityDataSingle.value" :isOpen="isModalOpened" @modal-close="closeModal" :is_host="true" @update-availability="fetchAvailabilitySettingsUpdate" />

    </div>
    <div class="tfhb-submission-btn tfhb-mt-24">
        <HbButton 
            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation" 
            @click="emit('save-host-info')"
            :buttonText="$tfhb_trans('Save & Continue')"
            icon="ChevronRight" 
            hover_icon="ArrowRight" 
            :hover_animation="true"
            :pre_loader="props.update_host_preloader"
        />   
    </div>
</div>
</template>