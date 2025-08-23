<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { toast } from "vue3-toastify";
import { Notification } from '@/store/notification';

import Header from '@/components/Header.vue';

// Router setup
const router = useRouter();
const route = useRoute();

// Reactive data
const matchingId = ref(null);
const matchingData = reactive({});
const formData = reactive({
  select_date: '',
  select_time_slot: '',
  meeting_id: '',
  selected_end_time: ''
});

const meetingDates = ref([]);
const timeSlots = ref([]);
const meetingId = ref('');
const sellerInfo = reactive({});
const buyerInfo = reactive({});
const sellerDetails = ref('');
const buyerDetails = ref('');
const loading = ref(false);
const adminUrl = ref(window.location.origin + '/wp-admin/');

// Methods
const getMatchingIdFromRoute = () => {
  matchingId.value = route.params.id;
  if (!matchingId.value) {
    toast.error('Invalid matching ID', {
      position: 'bottom-right',
      autoClose: 1500,
    });
    goToMatchingList();
  }
};

const loadInitialData = async () => {
  try {
    const [matchingResponse, pageDataResponse] = await Promise.all([
      fetch(`/wp-json/hydra-booking/v1/addons/matching/${matchingId.value}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.wpApiSettings?.nonce || getNonce() || ''
        }
      }),
      fetch(`/wp-json/hydra-booking/v1/addons/edit-matching-page-data/${matchingId.value}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.wpApiSettings?.nonce || getNonce() || ''
        }
      })
    ]);
    
    const matchingDataResponse = await matchingResponse.json();
    const pageData = await pageDataResponse.json();
    
    if (matchingDataResponse.success) {
      Object.assign(matchingData, matchingDataResponse.data);
      formData.select_date = matchingData.date;
      formData.select_time_slot = matchingData.start_time;
      formData.selected_end_time = matchingData.end_time;
    }
    
    if (pageData.success) {
      meetingDates.value = pageData.data.meeting_availability.dates;
      meetingId.value = pageData.data.meeting_availability.meeting_id;
      formData.meeting_id = meetingId.value;
      timeSlots.value = pageData.data.time_slots || [];
    }
    
    // Load user information
    await loadUserInfo();
    
  } catch (error) {
    console.error('Error loading initial data:', error);
    toast.error('Error loading initial data', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const loadUserInfo = async () => {
  try {
    const [sellerResponse, buyerResponse] = await Promise.all([
      fetch('/wp-json/hydra-booking/v1/addons/user-info', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.wpApiSettings?.nonce || getNonce() || ''
        },
        body: JSON.stringify({
          user_id: matchingData.sellers_id,
          user_type: 'seller'
        })
      }),
      fetch('/wp-json/hydra-booking/v1/addons/user-info', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.wpApiSettings?.nonce || getNonce() || ''
        },
        body: JSON.stringify({
          user_id: matchingData.buyers_id,
          user_type: 'buyer'
        })
      })
    ]);
    
    const sellerData = await sellerResponse.json();
    const buyerData = await buyerResponse.json();
    
    if (sellerData.success) {
      sellerDetails.value = sellerData.data.html;
      Object.assign(sellerInfo, {
        display_name: sellerData.data.display_name,
        user_email: sellerData.data.user_email
      });
    }
    
    if (buyerData.success) {
      buyerDetails.value = buyerData.data.html;
      Object.assign(buyerInfo, {
        display_name: buyerData.data.display_name,
        user_email: buyerData.data.user_email
      });
    }
    
  } catch (error) {
    console.error('Error loading user info:', error);
    toast.error('Error loading user information', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const onDateChange = async () => {
  if (formData.select_date && meetingId.value) {
    await loadTimeSlots();
  } else {
    timeSlots.value = [];
    formData.select_time_slot = '';
  }
};

const onTimeSlotChange = () => {
  // Store the end time for the selected slot
  const selectedSlot = timeSlots.value.find(slot => slot.start === formData.select_time_slot);
  if (selectedSlot) {
    formData.selected_end_time = selectedSlot.end;
  }
};

const loadTimeSlots = async () => {
  try {
    const response = await fetch('/wp-json/hydra-booking/v1/addons/time-slots', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.wpApiSettings?.nonce || getNonce() || ''
      },
      body: JSON.stringify({
        meeting_id: meetingId.value,
        selected_date: formData.select_date
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      timeSlots.value = data.data.time_slots || [];
    } else {
      timeSlots.value = [];
    }
  } catch (error) {
    console.error('Error loading time slots:', error);
    timeSlots.value = [];
    toast.error('Error loading time slots', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const submitForm = async () => {
  if (!validateForm()) {
    return;
  }
  
  loading.value = true;
  
  try {
    const response = await fetch('/wp-json/hydra-booking/v1/addons/update-matching', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.wpApiSettings?.nonce || getNonce() || ''
      },
      body: JSON.stringify({
        matching_id: matchingId.value,
        meeting_id: meetingId.value,
        select_date: formData.select_date,
        select_time_slot: formData.select_time_slot,
        selected_end_time: formData.selected_end_time
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      toast.success('Matching updated successfully!', {
        position: 'bottom-right',
        autoClose: 1500,
      });
      // Redirect to matching list
      goToMatchingList();
    } else {
      toast.error(data.message || 'Error updating matching', {
        position: 'bottom-right',
        autoClose: 1500,
      });
    }
  } catch (error) {
    console.error('Error submitting form:', error);
    toast.error('Error updating matching', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  } finally {
    loading.value = false;
  }
};

const validateForm = () => {
  if (!formData.select_date) {
    toast.error('Please select a date', {
      position: 'bottom-right',
      autoClose: 1500,
    });
    return false;
  }
  
  if (!formData.select_time_slot) {
    toast.error('Please select a time slot', {
      position: 'bottom-right',
      autoClose: 1500,
    });
    return false;
  }
  
  return true;
};

const goToMatchingList = () => {
  router.push('/addons-view-matching');
};

const getNonce = () => {
  return window.tfhb_addons_admin_js?.nonce || '';
};

// Lifecycle
onMounted(() => { 
  getMatchingIdFromRoute();
  loadInitialData();
});
</script>

<template> 
  <div class="tfhb-admin-dashboard tfhb-admin-meetings "> 
    <Header v-if="$front_end_dashboard == false" :title="$tfhb_trans('Edit Matching')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
     
    <div class="tfhb-admin-card-box tfhb-mt-24" :style="{ maxWidth: '1000px', margin: '0 auto' }">
      <form @submit.prevent="submitForm" id="edit-matching-form">
        <input type="hidden" name="matching_id" :value="matchingId">
        
        <div class="tfhb-form-grid tfhb-gap-24">
          <!-- Seller Information (Read-only) -->
          <div class="tfhb-single-form-field">
            <div class="tfhb-form-field-disabled">
              <label class="tfhb-form-label">{{ $tfhb_trans('Seller') }}</label>
              <div class="tfhb-disabled-field">
                {{ sellerInfo.display_name }} ({{ sellerInfo.user_email }})
                <small class="tfhb-field-note">{{ $tfhb_trans('Seller cannot be changed') }}</small>
              </div>
            </div>
          </div>
          
          <!-- Buyer Information (Read-only) -->
          <div class="tfhb-single-form-field">
            <div class="tfhb-form-field-disabled">
              <label class="tfhb-form-label">{{ $tfhb_trans('Buyer') }}</label>
              <div class="tfhb-disabled-field">
                {{ buyerInfo.display_name }} ({{ buyerInfo.user_email }})
                <small class="tfhb-field-note">{{ $tfhb_trans('Buyer cannot be changed') }}</small>
              </div>
            </div>
          </div>
          
          <!-- Date Selection -->
          <div class="tfhb-single-form-field">
            <HbDropdown
              v-model="formData.select_date"
              :label="$tfhb_trans('Select Date')"
              :placeholder="$tfhb_trans('Select Date')"
              :option="meetingDates.map(date => ({
                name: date,
                value: date
              }))"
              @tfhb-onchange="onDateChange"
              required
            />
            <input type="hidden" name="meeting_id" :value="meetingId">
          </div>

          <!-- Time Slot Selection -->
          <div class="tfhb-single-form-field">
            <HbDropdown
              v-model="formData.select_time_slot"
              :label="$tfhb_trans('Select Time Slot')"
              :placeholder="timeSlots.length > 0 ? $tfhb_trans('Select Time Slot') : $tfhb_trans('Please Select Date First')"
              :option="timeSlots.map(slot => ({
                name: `${slot.start} - ${slot.end}`,
                value: slot.start,
                data: { end: slot.end }
              }))"
              @tfhb-onchange="onTimeSlotChange"
              required
              :disabled="timeSlots.length === 0"
            />
            <input type="hidden" id="current_start_time" :value="matchingData.start_time">
            <input type="hidden" id="current_end_time" :value="matchingData.end_time">
          </div>
        </div>
        
        <!-- User Information Panels -->
        <div class="tfhb-user-info-panels tfhb-mt-32">
          <div class="tfhb-form-grid tfhb-gap-24">
            <!-- Seller Information -->
            <div class="tfhb-single-form-field">
              <div class="tfhb-admin-card-box">
                <h3 class="tfhb-card-title">{{ $tfhb_trans('Seller Information') }}</h3>
                <div class="tfhb-user-details" v-html="sellerDetails"></div>
              </div>
            </div>
            
            <!-- Buyer Information -->
            <div class="tfhb-single-form-field">
              <div class="tfhb-admin-card-box">
                <h3 class="tfhb-card-title">{{ $tfhb_trans('Buyer Information') }}</h3>
                <div class="tfhb-user-details" v-html="buyerDetails"></div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="tfhb-form-actions tfhb-mt-32">
          <HbButton
            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"
            @click="submitForm"
            :buttonText="loading ? $tfhb_trans('Updating...') : $tfhb_trans('Update Matching')"
            icon="Save"
            :disabled="loading"
            :pre_loader="loading"
            :hover_animation="false"
            icon_position="left"
          />
          <HbButton
            classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8"
            @click="goToMatchingList"
            :buttonText="$tfhb_trans('Cancel')"
            icon="X"
            :hover_animation="false"
            icon_position="left"
          />
        </div>
      </form>
    </div>
  </div>
</template>


<style scoped>
.tfhb-form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.tfhb-user-info-panels {
  border-top: 1px solid #e1e1e1;
  padding-top: 24px;
}

.tfhb-admin-card-box {
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.tfhb-card-title {
  margin: 0 0 16px 0;
  color: #23282d;
  border-bottom: 2px solid #0073aa;
  padding-bottom: 8px;
  font-size: 16px;
  font-weight: 600;
}

.tfhb-user-details {
  font-size: 14px;
  line-height: 1.6;
}

.tfhb-form-actions {
  display: flex;
  justify-content: flex-start;
  gap: 16px;
}

.tfhb-form-field-disabled {
  margin-bottom: 16px;
}

.tfhb-form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #23282d;
}

.tfhb-disabled-field {
  background: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 12px;
  color: #666;
  font-size: 14px;
}

.tfhb-field-note {
  display: block;
  margin-top: 4px;
  color: #999;
  font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
  .tfhb-form-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .tfhb-user-info-panels .tfhb-form-grid {
    grid-template-columns: 1fr;
  }
  
  .tfhb-form-actions {
    flex-direction: column;
    gap: 12px;
  }
}
</style>
