

<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onMounted } from 'vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import { toast } from "vue3-toastify";
import { useRouter } from 'vue-router';
import Header from '@/components/Header.vue';
import { Notification } from '@/store/notification';

import axios from 'axios' 
// Reactive data
const formData = reactive({
  seller_id: '',
  buyer_id: '',
  select_date: '',
  select_time_slot: '',
  meeting_id: ''
});

const sellers = ref([]);
const buyers = ref([]);
const meetingDates = ref([]);
const timeSlots = ref([]);
const meetingId = ref('');
const sellerDetails = ref('');
const buyerDetails = ref('');
const showSellerInfo = ref(false);
const showBuyerInfo = ref(false);
const loading = ref(false);

const router = useRouter();

// Methods
const loadInitialData = async () => {
  try {
    const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/add-matching-page-data', {
      headers: {
        'X-WP-Nonce': tfhb_core_apps.rest_nonce,
      }
    });
    
    if (response.data.success) {
     
      sellers.value = response.data.data.sellers;
      buyers.value = response.data.data.buyers;
      meetingDates.value = response.data.data.meeting_availability.dates;
      meetingId.value = response.data.data.meeting_availability.meeting_id;
      formData.meeting_id = meetingId.value;
    }
  } catch (error) {
    // console.error('Error loading initial data:', error);
    toast.error('Error loading initial data', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const onSellerChange = async () => { 
  if (formData.seller_id) {
    await loadUserInfo(formData.seller_id, 'seller');
    showSellerInfo.value = true;
    
    // Reload time slots if date is already selected to check availability
    if (formData.select_date) {
      await loadTimeSlots();
    }
  } else {
    showSellerInfo.value = false;
    sellerDetails.value = '';
    
    // Reload time slots if date is already selected
    if (formData.select_date) {
      await loadTimeSlots();
    }
  }
};

const onBuyerChange = async () => {
  if (formData.buyer_id) {
    await loadUserInfo(formData.buyer_id, 'buyer');
    showBuyerInfo.value = true;
    
    // Reload time slots if date is already selected to check availability
    if (formData.select_date) {
      await loadTimeSlots();
    }
  } else {
    showBuyerInfo.value = false;
    buyerDetails.value = '';
    
    // Reload time slots if date is already selected
    if (formData.select_date) {
      await loadTimeSlots();
    }
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

const loadUserInfo = async (userId, userType) => {
  try {
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/user-info', {
      user_id: userId,
      user_type: userType
    }, {
      headers: {
        'X-WP-Nonce': tfhb_core_apps.rest_nonce,
      }
    });
    
    if (response.data.success) {
      if (userType === 'seller') {
        sellerDetails.value = response.data.data.html;
      } else {
        buyerDetails.value = response.data.data.html;
      }
    }
  } catch (error) {
    console.error('Error loading user info:', error);
    toast.error('Error loading user information', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const loadTimeSlots = async () => {
  try {
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/time-slots', {
      meeting_id: meetingId.value,
      selected_date: formData.select_date,
      buyer_id: formData.buyer_id || null,
      seller_id: formData.seller_id || null
    }, {
      headers: {
        'X-WP-Nonce': tfhb_core_apps.rest_nonce,
      }
    });
    
    if (response.data.success) {
      timeSlots.value = response.data.data.time_slots || [];
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
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/add-matching', {
      meeting_id: meetingId.value,
      buyer_id: formData.buyer_id,
      seller_id: formData.seller_id,
      select_date: formData.select_date,
      select_time_slot: formData.select_time_slot,
      selected_end_time: formData.selected_end_time
    }, {
      headers: {
        'X-WP-Nonce': tfhb_core_apps.rest_nonce,
      }
    });
    
    if (response.data.success) {
      toast.success('Matching added successfully!', {
        position: 'bottom-right',
        autoClose: 1500,
      });
      // Redirect to matching list vue example
      router.push('/addons-view-matching');
      // window.location.href = `${window.location.origin}/wp-admin/admin.php?page=hydra-addons-matching`;
    } else {
      toast.error(response.data.message || 'Error adding matching', {
        position: 'bottom-right',
        autoClose: 1500,
      });
    }
  } catch (error) {
    console.error('Error submitting form:', error);
    toast.error('Error adding matching', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  } finally {
    loading.value = false;
  }
};

const getTimeSlotPlaceholder = () => {
  if (!formData.select_date) {
    return 'Please Select Date First';
  }
  
  if (timeSlots.value.length === 0) {
    if (formData.buyer_id && formData.seller_id) {
      return 'No Available Time Slots (Buyer or Seller Already Booked)';
    } else if (formData.buyer_id || formData.seller_id) {
      return 'No Available Time Slots (User Already Booked)';
    } else {
      return 'No Time Slots Available';
    }
  }
  
  return 'Select Time Slot';
};

const validateForm = () => {
  if (!formData.seller_id) {
    toast.error('Please select a seller', {
      position: 'bottom-right',
      autoClose: 1500,
    });
    return false;
  }
  
  if (!formData.buyer_id) {
    toast.error('Please select a buyer', {
      position: 'bottom-right',
      autoClose: 1500,
    });
    return false;
  }
  
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



// Lifecycle
onMounted(() => {
  loadInitialData();
  
  Notification.fetchNotifications();
});
</script>
<template>

  <div class="tfhb-admin-dashboard tfhb-admin-meetings "> 
    <Header v-if="$front_end_dashboard == false" :title="$tfhb_trans('Add New Matching')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
     
    <div class="tfhb-admin-card-box tfhb-mt-24" :style="{ maxWidth: '1000px', margin: '0 auto' }">
      <form @submit.prevent="submitForm" id="add-matching-form">
        <div class="tfhb-form-grid tfhb-gap-24">
          <!-- Seller Selection -->
          <div class="tfhb-single-form-field">
            <!-- {{ sellers }} -->
            <HbDropdown
              v-model="formData.seller_id"
              :label="$tfhb_trans('Seller')"
              :filter="true"
              :placeholder="$tfhb_trans('Select Seller')"
              :option="sellers.map(seller => ({
                name: `(${seller.tfhb_sellers_data.denominazione_operatore_azienda}) ${seller.user_email}`,
                value: seller.ID
              }))"
              @tfhb-onchange="onSellerChange"
              required
            />
          </div>
          
          <!-- Buyer Selection -->
          <div class="tfhb-single-form-field">
            <HbDropdown
              v-model="formData.buyer_id"
              :label="$tfhb_trans('Buyer')"
              :filter="true"
              :placeholder="$tfhb_trans('Select Buyer')"
              :option="buyers.map(buyer => ({
                name: `(${buyer.tfhb_buyers_data.travel_agent_name}) ${buyer.user_email} `,
                value: buyer.ID
              }))"
              @tfhb-onchange="onBuyerChange"
              required
            />
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
              :placeholder="getTimeSlotPlaceholder()"
              :option="timeSlots.map(slot => ({
                name: `${slot.start} - ${slot.end}`,
                value: slot.start,
                data: { end: slot.end }
              }))"
              @tfhb-onchange="onTimeSlotChange"
              required
              :disabled="timeSlots.length === 0"
            />
          </div>
        </div>
        
        <!-- User Information Panels -->
        <div class="tfhb-user-info-panels tfhb-mt-32" v-if="showSellerInfo || showBuyerInfo">
          <div class="tfhb-form-grid tfhb-gap-24">
            <!-- Seller Information -->
            <div class="tfhb-single-form-field" v-if="showSellerInfo">
              <div class="tfhb-admin-card-box">
                <h3 class="tfhb-card-title">{{ $tfhb_trans('Seller Information') }}</h3>
                <div class="tfhb-user-details" v-html="sellerDetails"></div>
              </div>
            </div>
            
            <!-- Buyer Information -->
            <div class="tfhb-single-form-field" v-if="showBuyerInfo">
              <div class="tfhb-admin-card-box">
                <h3 class="tfhb-card-title">{{ $tfhb_trans('Buyer Information') }}</h3>
                <div class="tfhb-user-details" v-html="buyerDetails"></div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Submit Button -->
        <div class="tfhb-form-actions tfhb-mt-32">
          <HbButton
            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"
            @click="submitForm"
            :buttonText="loading ? $tfhb_trans('Adding...') : $tfhb_trans('Add Matching')"
            icon="Plus"
            :disabled="loading"
            :pre_loader="loading"
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

/* Responsive Design */
@media (max-width: 768px) {
  .tfhb-form-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .tfhb-user-info-panels .tfhb-form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
