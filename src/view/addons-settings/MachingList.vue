
<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onMounted, onBeforeUnmount, computed, watch } from 'vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import Header from '@/components/Header.vue';
import { toast } from "vue3-toastify";
import { Notification } from '@/store/notification';
import { useRouter } from 'vue-router';
import axios from 'axios';
// Reactive data
const matchingData = ref([]);
const sellers = ref([]);
const buyers = ref([]);
const filters = reactive({
  buyer_id: '',
  seller_id: '',
  date_search: '',
  page: 1,
  per_page: 20
});

const selectedMatchingIds = ref([]);
const selectAll = ref(false);
const total = ref(0);
const currentPage = ref(1);
const totalPages = ref(1);
const startPage = ref(1);
const endPage = ref(1);
const visiblePages = ref([]);
const adminUrl = ref(window.location.origin + '/wp-admin/');
const loading = ref(false);

const router = useRouter();

// Computed properties
const startItem = computed(() => {
  return ((currentPage.value - 1) * filters.per_page) + 1;
});

const endItem = computed(() => {
  return Math.min(currentPage.value * filters.per_page, total.value);
});

// Computed property to get sequential ID for each matching item
const getSequentialId = (matching) => {
  const index = matchingData.value.indexOf(matching);
  // When filters are applied, restart numbering from 1 for filtered results
  // Otherwise use pagination-based numbering
  const hasActiveFilters = filters.buyer_id || filters.seller_id || filters.date_search;
  if (hasActiveFilters) {
    return index + 1;
  }
  return (currentPage.value - 1) * filters.per_page + index + 1;
};

// Methods
const loadData = async () => {
  loading.value = true;
  try {
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/matching-list', {
      filters: filters
    }, {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': tfhb_core_apps.rest_nonce
      }
    });
    const data = response.data;
    
    if (data.success) {
      matchingData.value = data.data.matching;
      total.value = data.data.total;
      totalPages.value = Math.ceil(total.value / filters.per_page);
      updatePagination();
    }
  } catch (error) { 
    toast.error('Error loading matching data', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  } finally {
    loading.value = false;
  }
};

const loadSellersAndBuyers = async () => {
  try {
    const [sellersResponse, buyersResponse] = await Promise.all([
      axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/sellers-list', {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': tfhb_core_apps.rest_nonce
        }
      }),
      axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/buyers-list', {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': tfhb_core_apps.rest_nonce
        }
      })
    ]);
    
    const sellersData = sellersResponse.data;
    const buyersData = buyersResponse.data;
    
    if (sellersData.success) {
      sellers.value = sellersData.data;
    }
    
    if (buyersData.success) {
      buyers.value = buyersData.data;
    }
  } catch (error) {
    console.error('Error loading sellers/buyers:', error);
    toast.error('Error loading sellers/buyers', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const applyFilters = () => {
  filters.page = 1;
  // alert(1);
  loadData();
};

const clearFilters = () => {
  Object.assign(filters, {
    buyer_id: '',
    seller_id: '',
    date_search: '',
    page: 1,
    per_page: 20
  });
  loadData();
};

const deleteMatching = async (id) => {
  if (!confirm('Are you sure you want to delete this matching?')) {
    return;
  }
  
  try {
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/delete-matching', {
      id: id
    }, {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': tfhb_core_apps.rest_nonce
      }
    });
    
    const data = response.data;
    
    if (data.success) {
      toast.success('Matching deleted successfully', {
        position: 'bottom-right',
        autoClose: 1500,
      });
      loadData();
    } else {
      toast.error('Error deleting matching', {
        position: 'bottom-right',
        autoClose: 1500,
      });
    }
  } catch (error) { 
    toast.error('Error deleting matching', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const applyBulkAction = async () => {
  if (selectedMatchingIds.value.length > 0) {
    if (!confirm(`Are you sure you want to delete ${selectedMatchingIds.value.length} matching(s)?`)) {
      return;
    }
    
    try {
      const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/bulk-delete-matching', {
        ids: selectedMatchingIds.value
      }, {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': tfhb_core_apps.rest_nonce
        }
      });
      
      const data = response.data;
      
      if (data.success) {
        toast.success('Matchings deleted successfully', {
          position: 'bottom-right',
          autoClose: 1500,
        });
        selectedMatchingIds.value = [];
        selectAll.value = false;
        loadData();
      } else {
        toast.error('Error deleting matchings', {
          position: 'bottom-right',
          autoClose: 1500,
        });
      }
    } catch (error) {
      console.error('Error bulk deleting matchings:', error);
      toast.error('Error deleting matchings', {
        position: 'bottom-right',
        autoClose: 1500,
      });
    }
  }
};

const exportMatching = async () => {
  try {
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/export-matching', {
      filters: filters
    }, {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': tfhb_core_apps.rest_nonce
      }
    });
    
    const csvContent = response.data;
    
    // Create and download CSV file
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'matching-export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
    
    toast.success('Export completed successfully', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  } catch (error) {
    console.error('Error exporting matching:', error);
    toast.error('Error exporting matching', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};

const ExportAScsv = async () => {
  try {
    // First, get the total count to determine how many records we need to fetch
    const countResponse = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/matching-list', {
      filters: {
        ...filters,
        page: 1,
        per_page: 1 // Just get one record to get the total count
      }
    }, {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': tfhb_core_apps.rest_nonce
      }
    });
    
    const countData = countResponse.data;
    const totalRecords = countData.success ? countData.data.total : 0;
    
    if (totalRecords === 0) {
      toast.error('No data available for export', {
        position: 'bottom-right',
        autoClose: 1500,
      });
      return;
    }
    
    // Now get all matching data using the total count
    const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/matching-list', {
      filters: {
        ...filters,
        page: 1,
        per_page: totalRecords + 100 // Add some buffer to ensure we get all records
      }
    }, {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': tfhb_core_apps.rest_nonce
      }
    });
    
    const data = response.data;
    
    if (data.success && data.data.matching) {
      const allMatchingData = data.data.matching;
      
      // Create CSV headers
      const headers = [
        'SL No',
        'Buyer Company Name',
        'Buyer Email',
        'Buyer Contact Person',
        'Buyer Country',
        'Buyer Status',
        'Seller Company Name',
        'Seller Email',
        'Seller Contact Person',
        'Seller Country',
        'Seller Status',
        'Date',
        'Start Time',
        'End Time',
        'Status'
      ];
      
      // Create CSV rows
      const csvRows = [headers.join(',')];
      
      allMatchingData.forEach((matching, index) => {
        const row = [
          index + 1, // Sequential number
          matching.buyers?.meta?.tfhb_buyers_data?.travel_agent_name || 'N/A',
          matching.buyers?.user_email || 'N/A',
          matching.buyers?.display_name || 'N/A',
          matching.buyers?.meta?.tfhb_buyers_data?.nation?.join(' | ') || 'N/A',
          matching.buyers?.meta?.tfhb_buyers_status || 'N/A',
          matching.sellers?.meta?.tfhb_sellers_data?.denominazione_operatore_azienda || 'N/A',
          matching.sellers?.user_email || 'N/A',
          matching.sellers?.display_name || 'N/A',
          matching.sellers?.meta?.tfhb_sellers_data?.provenienza_Buyer_interesse?.join(' | ') || 'N/A',
          matching.sellers?.meta?.tfhb_sellers_status || 'N/A',
          matching.date || 'N/A',
          matching.start_time || 'N/A',
          matching.end_time || 'N/A',
          matching.status || 'N/A'
        ];
        
        // Escape CSV values (handle commas and quotes)
        const escapedRow = row.map(value => {
          const stringValue = String(value || '');
          if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
            return `"${stringValue.replace(/"/g, '""')}"`;
          }
          return stringValue;
        });
        
        csvRows.push(escapedRow.join(','));
      });
      
      // Create CSV content
      const csvContent = csvRows.join('\n');
      
      // Create and download CSV file
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `matching-list-export-${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
      
      toast.success(`Export completed successfully. ${allMatchingData.length} records exported.`, {
        position: 'bottom-right',
        autoClose: 2000,
      });
    } else {
      toast.error('No data available for export', {
        position: 'bottom-right',
        autoClose: 1500,
      });
    }
  } catch (error) {
    console.error('Error exporting CSV:', error);
    toast.error('Error exporting CSV', {
      position: 'bottom-right',
      autoClose: 1500,
    });
  }
};
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedMatchingIds.value = matchingData.value.map(matching => matching.id);
  } else {
    selectedMatchingIds.value = [];
  }
};

const updatePagination = () => {
  startPage.value = Math.max(1, currentPage.value - 2);
  endPage.value = Math.min(totalPages.value, currentPage.value + 2);
  
  visiblePages.value = [];
  for (let i = startPage.value; i <= endPage.value; i++) {
    visiblePages.value.push(i);
  }
};

const changePage = (page) => {
  currentPage.value = page;
  filters.page = page;
  loadData();
};

const goToAddMatching = () => {
  // Redirect to add matching vue example
  router.push('/addons-add-matching');
  // window.location.href = `${adminUrl.value}admin.php?page=hydra-addons-add-matching`;
};

const goToEditMatching = (id) => {
  // Redirect to edit matching vue example
  router.push(`/addons-edit-matching/${id}`);
  // window.location.href = `${adminUrl.value}admin.php?page=hydra-addons-edit-matching&id=${id}`;
};

const capitalizeFirst = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
};

const getNonce = () => {
  return window.tfhb_addons_admin_js?.nonce || '';
};

// Refresh data globally
const refreshMatchingData = () => {
  document.dispatchEvent(new Event('matching-data-refresh'));
};

// Watchers
watch(selectedMatchingIds, (newVal) => {
  selectAll.value = newVal.length === matchingData.value.length && matchingData.value.length > 0;
});

// Lifecycle
onMounted(() => {
  loadData();
  loadSellersAndBuyers();
  Notification.fetchNotifications();
  
  // Listen for refresh events
  document.addEventListener('matching-data-refresh', loadData);
});

onBeforeUnmount(() => {
  // Clean up event listeners
  document.removeEventListener('matching-data-refresh', loadData);
});
</script>
<template>
  <div class="tfhb-admin-dashboard tfhb-admin-meetings "> 
    <Header v-if="$front_end_dashboard == false" :title="$tfhb_trans('Matching List')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
 
    <!-- Filters Section -->
    <div class="tfhb-admin-card-box tfhb-mt-24 tfhb-flexbox tfhb-gap-8 tfhb-justify-between" >
  
      <form @submit.prevent="applyFilters" id="matching-filters">
        <div class="tfhb-filter-form tfhb-flexbox tfhb-gap-16 tfhb-justify-start">
          <!-- Sellers Dropdown -->
          <div class="tfhb-single-form-field">
            <HbDropdown
              v-model="filters.seller_id"
              :label="$tfhb_trans('')"
              :filter="true"
              :placeholder="$tfhb_trans('All Sellers')"
              :option="sellers.map(seller => ({
                name: (seller.data.denominazione_operatore_azienda ? ' (' + seller.data.denominazione_operatore_azienda + ') ' : '') + seller.email,
                value: seller.id
              }))"
              @tfhb-onchange="applyFilters"
            />
          </div>
          
          <!-- Buyers Dropdown -->
           <!-- {{ buyers }} -->
          <div class="tfhb-single-form-field">
            <HbDropdown
              v-model="filters.buyer_id"
              :label="$tfhb_trans('')"
              :filter="true"
              :placeholder="$tfhb_trans('All Buyers')"
              :option="buyers.map(buyer => ({
                name:  (buyer.data.travel_agent_name ? ' (' + buyer.data.travel_agent_name + ') ' : '') + buyer.email,
                value: buyer.id
              }))"
              @tfhb-onchange="applyFilters"
            />
          </div>
          
          <!-- Date Search -->
          <div class="tfhb-single-form-field">
            <HbDateTime
              v-model="filters.date_search"
              :label="$tfhb_trans('')"
              :placeholder="$tfhb_trans('Search by date...')"
              icon="CalendarDays"
              enableTime="false"
              @dateChange="applyFilters"
            />
          </div>
          
          <!-- Action Buttons -->
          <div class="tfhb-filter-actions tfhb-flexbox tfhb-gap-8">
            <HbButton
              classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8"
              @click="clearFilters"
              :buttonText="$tfhb_trans('Clear Filters')"
              icon="RefreshCw"
              :hover_animation="false"
              icon_position="left"
            />
            <!-- <HbButton
              classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"
              @click="exportMatching"
              :buttonText="$tfhb_trans('Export CSV')"
              icon="FileDown"
              :hover_animation="false"
              icon_position="left"
            /> -->
          </div>
        </div>
      </form>

      <div class="tfhb-cta-export tfhb-flexbox tfhb-gap-8">
        <HbButton
          classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"
          @click="ExportAScsv"
          :buttonText="$tfhb_trans('Export as CSV')"
          icon="FileDown"
          :hover_animation="false"
          icon_position="left"
        />
        <HbButton
          classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"
          @click="goToAddMatching"
          :buttonText="$tfhb_trans('Add New Matching')"
          icon="Plus"
          :hover_animation="false"
          icon_position="left"
        />
      </div>
    </div>
    
    <!-- Bulk Actions -->
    <div class="tfhb-bulk-actions tfhb-mt-16" v-if="selectedMatchingIds.length > 0">
      <div class="tfhb-admin-card-box">
        <div class="tfhb-flexbox tfhb-gap-16 tfhb-align-center">
          <span class="tfhb-selected-count">
            {{ $tfhb_trans('Selected') }}: {{ selectedMatchingIds.length }}
          </span>
          <HbButton
            classValue="tfhb-btn danger-btn tfhb-flexbox tfhb-gap-8"
            @click="applyBulkAction"
            :buttonText="$tfhb_trans('Delete Selected')"
            icon="Trash2"
            :hover_animation="false"
            icon_position="left"
          />
        </div>
      </div>
    </div>
    
    <!-- Matching List Table -->
    <div class="tfhb-admin-card-box tfhb-mt-24">
      <div class="tfhb-table-container">
        <table class="tfhb-table">
          <thead>
            <tr>
              <th class="tfhb-checkbox-column">
                <input 
                  type="checkbox" 
                  v-model="selectAll" 
                  @change="toggleSelectAll" 
                  id="cb-select-all-1"
                >
              </th>
              <th>{{ $tfhb_trans('SL No') }}</th>
              <th>{{ $tfhb_trans('Buyer') }}</th>
              <th>{{ $tfhb_trans('Seller') }}</th>
              <th>{{ $tfhb_trans('Date') }}</th>
              <th>{{ $tfhb_trans('Time') }}</th>
              <th>{{ $tfhb_trans('Status') }}</th>
              <th>{{ $tfhb_trans('Actions') }}</th>
            </tr>
          </thead>
          
          <tbody>
            <tr v-for="matching in matchingData" :key="matching.id" :id="`matching-${matching.id}`">
              <td class="tfhb-checkbox-column">
                <input 
                  type="checkbox" 
                  :value="matching.id" 
                  v-model="selectedMatchingIds"
                  name="matching_ids[]"
                >
              </td>
              <td>{{ getSequentialId(matching) }}</td>
              <td>
           
                <div v-if="matching.buyers && matching.buyers.display_name" class="tfhb-user-info">
                  <strong>{{ matching.buyers?.meta?.tfhb_buyers_data?.travel_agent_name || 'N/A' }}</strong>
                  <small>{{ matching.buyers.user_email }}</small> 
                  <small>{{ matching.buyers.display_name }}</small>
                  <span v-if="matching.buyers?.meta?.tfhb_buyers_data?.nation && matching.buyers.meta.tfhb_buyers_data.nation.length > 0">
                    <small v-for="(nation, index) in matching.buyers.meta.tfhb_buyers_data.nation" :key="nation">{{ nation }}<span v-if="index < matching.buyers.meta.tfhb_buyers_data.nation.length - 1"> | </span></small>
                  </span>
                  <div v-if="matching.buyers?.meta?.tfhb_buyers_status" class="tfhb-user-status">
                    {{ capitalizeFirst(matching.buyers.meta.tfhb_buyers_status) }}
                  </div> 
                </div>
                <div v-else class="tfhb-user-not-found">
                  <em>{{ $tfhb_trans('Buyer not found') }}</em>
                </div>
              </td>
              <td>
                <div v-if="matching.sellers && matching.sellers.display_name" class="tfhb-user-info">
                  <strong>{{ matching.sellers?.meta?.tfhb_sellers_data?.denominazione_operatore_azienda || 'N/A' }}</strong> 
                  <small>{{ matching.sellers.user_email }}</small>
                  <small>{{ matching.sellers.display_name }}</small>
                  <span v-if="matching.sellers?.meta?.tfhb_sellers_data?.provenienza_Buyer_interesse && matching.sellers.meta.tfhb_sellers_data.provenienza_Buyer_interesse.length > 0">
                    <small v-for="(nation, index) in matching.sellers.meta.tfhb_sellers_data.provenienza_Buyer_interesse" :key="nation">{{ nation }}<span v-if="index < matching.sellers.meta.tfhb_sellers_data.provenienza_Buyer_interesse.length - 1"> | </span></small>
                  </span>
                  <div v-if="matching.sellers?.meta?.tfhb_sellers_status" class="tfhb-user-status">
                    {{ capitalizeFirst(matching.sellers.meta.tfhb_sellers_status) }}
                  </div> 
                </div>
                <div v-else class="tfhb-user-not-found">
                  <em>{{ $tfhb_trans('Seller not found') }}</em>
                </div>
              </td>
              <td>{{ matching.date }}</td>
              <td>{{ matching.start_time }} - {{ matching.end_time }}</td>
              <td>
                <span :class="`tfhb-status tfhb-status-${matching.status}`">
                  {{ capitalizeFirst(matching.status) }}
                </span>
              </td>
              <td class="">
                <div class="tfhb-flexbox tfhb-gap-8">
                    <HbButton
                    classValue="tfhb-btn  tfhb-flexbox tfhb-gap-4"
                    @click="goToEditMatching(matching.id)"
                    :buttonText="$tfhb_trans('')"
                    icon="Edit"
                    :hover_animation="false"
                    icon_position="left"
                  />
                  <HbButton
                    classValue="tfhb-btn tfhb-flexbox tfhb-gap-4"
                    @click="deleteMatching(matching.id)"
                    :buttonText="$tfhb_trans('')"
                    icon="Trash2"
                    :hover_animation="false"
                    icon_position="left"
                  />
                </div>
              </td>
            </tr>
            <tr v-if="matchingData.length === 0">
              <td colspan="8" class="tfhb-no-data">
                {{ $tfhb_trans('No matching records found.') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="tfhb-pagination tfhb-mt-24" v-if="totalPages > 1">
      <div class="tfhb-admin-card-box">
        <div class="tfhb-flexbox tfhb-justify-between tfhb-align-center">
          <span class="tfhb-pagination-info">
            {{ $tfhb_trans('Showing') }} {{ startItem }}-{{ endItem }} {{ $tfhb_trans('of') }} {{ total }} {{ $tfhb_trans('matching') }}{{ total !== 1 ? 's' : '' }}
          </span>
          
          <div class="tfhb-pagination-controls tfhb-flexbox tfhb-gap-8">
            <HbButton
              v-if="currentPage > 1"
              classValue="tfhb-btn secondary-btn"
              @click="changePage(currentPage - 1)"
              :buttonText="$tfhb_trans('Previous')"
              icon="ChevronLeft"
              :hover_animation="false"
              icon_position="left"
            />
            
            <div class="tfhb-page-numbers tfhb-flexbox tfhb-gap-4">
              <a 
                v-if="startPage > 1" 
                @click.prevent="changePage(1)"
                class="tfhb-page-number"
              >1</a>
              <span v-if="startPage > 2" class="tfhb-page-dots">…</span>
              
              <span 
                v-for="page in visiblePages" 
                :key="page"
                :class="page === currentPage ? 'tfhb-page-number tfhb-current' : 'tfhb-page-number'"
                @click.prevent="changePage(page)"
              >
                {{ page }}
              </span>
              
              <span v-if="endPage < totalPages - 1" class="tfhb-page-dots">…</span>
              <a 
                v-if="endPage < totalPages" 
                @click.prevent="changePage(totalPages)"
                class="tfhb-page-number"
              >{{ totalPages }}</a>
            </div>
            
            <HbButton
              v-if="currentPage < totalPages"
              classValue="tfhb-btn secondary-btn"
              @click="changePage(currentPage + 1)"
              :buttonText="$tfhb_trans('Next')"
              icon="ChevronRight"
              :hover_animation="false"
              icon_position="right"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped>
.tfhb-filter-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  align-items: end;
}

.tfhb-filter-actions {
  grid-column: 1 / -1;
  justify-content: flex-end;
}

.tfhb-bulk-actions {
  margin-top: 16px;
}

.tfhb-selected-count {
  font-weight: 600;
  color: #0073aa;
}

.tfhb-table-container {
  overflow-x: auto;
}

.tfhb-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.tfhb-table th,
.tfhb-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e1e1e1;
}

.tfhb-table th {
  background: #f9f9f9;
  font-weight: 600;
  color: #23282d;
}

.tfhb-checkbox-column {
  width: 40px;
  text-align: center;
}

.tfhb-user-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.tfhb-user-info strong {
  color: #23282d;
}

.tfhb-user-info small {
  color: #666;
  font-size: 12px;
}

.tfhb-user-status {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 3px;
  background: #f0f0f0;
  color: #666;
  display: inline-block;
}

.tfhb-user-not-found {
  color: #999;
  font-style: italic;
}

.tfhb-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.tfhb-status-pending {
  background: #fff3cd;
  color: #856404;
}

.tfhb-status-confirmed {
  background: #d4edda;
  color: #155724;
}

.tfhb-status-canceled {
  background: #f8d7da;
  color: #721c24;
}

.tfhb-status-completed {
  background: #d1ecf1;
  color: #0c5460;
}

.tfhb-actions {
  display: flex;
  gap: 8px;
}

.tfhb-no-data {
  text-align: center;
  color: #999;
  font-style: italic;
  padding: 40px;
}

.tfhb-pagination {
  margin-top: 24px;
}

.tfhb-pagination-info {
  color: #666;
  font-size: 14px;
}

.tfhb-pagination-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tfhb-page-numbers {
  display: flex;
  align-items: center;
  gap: 4px;
}

.tfhb-page-number {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  text-decoration: none;
  color: #0073aa;
  cursor: pointer;
  transition: all 0.2s;
}

.tfhb-page-number:hover {
  background: #f1f1f1;
  border-color: #999;
}

.tfhb-page-number.tfhb-current {
  background: #0073aa;
  border-color: #0073aa;
  color: #fff;
  cursor: default;
}

.tfhb-page-dots {
  padding: 8px;
  color: #666;
  font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
  .tfhb-filter-form {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .tfhb-filter-actions {
    justify-content: stretch;
  }
  
  .tfhb-table-container {
    font-size: 12px;
  }
  
  .tfhb-table th,
  .tfhb-table td {
    padding: 8px 6px;
  }
  
  .tfhb-actions {
    flex-direction: column;
    gap: 4px;
  }
  
  .tfhb-pagination-controls {
    flex-direction: column;
    gap: 12px;
  }
}
</style>
