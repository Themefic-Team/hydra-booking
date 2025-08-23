<script setup>
import { __ } from '@wordpress/i18n'; 
import { ref, onMounted, onBeforeMount, computed } from 'vue';
import { onBeforeRouteLeave } from 'vue-router' 
import Header from '@/components/Header.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';
import HbDropdown from '@/components/form-fields/HbDropdown.vue';
import HbMultiSelect from '@/components/form-fields/HbMultiSelect.vue';
import { AddonsUsers } from './addons-settings.js'; 
import { Notification } from '@/store/notification';
import { toast } from "vue3-toastify";

// Computed properties
const paginatedUsers = computed(() => {
    return AddonsUsers.getPaginatedUsers();
});
const editUserDetailsPopup = ref(false);

const allUsersSelected = computed(() => {
    const currentUsers = paginatedUsers.value;
    return currentUsers.length > 0 && currentUsers.every(user => AddonsUsers.selected_users.includes(user.id));
});

const selectedCount = computed(() => {
    return AddonsUsers.selected_users.length;
});

// Methods
const handleSearch = (event) => {
    AddonsUsers.searchUsers(event.target.value);
};

const handleStatusUpdate = async (userId, action) => {
    await AddonsUsers.updateUserStatus(userId, AddonsUsers.current_tab, action);
};

const handleBulkAction = async () => {
    if (!AddonsUsers.bulk_action) {
        toast.warning('Please select an action', {
            position: 'bottom-right',
            autoClose: 1500,
        });
        return;
    }
    
    await AddonsUsers.bulkUpdateStatus(AddonsUsers.bulk_action);
    AddonsUsers.bulk_action = '';
};

const handleTabChange = (tab) => {
    AddonsUsers.changeTab(tab);
};

const handleUserSelection = (userId) => {
    AddonsUsers.toggleUserSelection(userId);
};

const handleAllUsersSelection = () => {
    AddonsUsers.toggleAllUsersSelection();
};

const showUserDetails = (user) => {
    AddonsUsers.showUserDetails(user, AddonsUsers.current_tab);
};

const closeUserDetails = () => {
    AddonsUsers.closeUserDetails();
};

const changePage = (page) => {
    AddonsUsers.changePage(page);
};

const nextPage = () => {
    if (AddonsUsers.pagination.current_page < AddonsUsers.pagination.total_pages) {
        AddonsUsers.pagination.current_page += 1;
    }
};

const prevPage = () => {
    if (AddonsUsers.pagination.current_page > 1) {
        AddonsUsers.pagination.current_page -= 1;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

const getStatusClass = (status) => {
    if (!status) return 'status-inactive';
    const statusStr = String(status).toLowerCase().trim();
    return statusStr === 'active' ? 'status-active' : 'status-inactive';
};

const getStatusText = (status) => {
    return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Inactive';
};

const isUserInactive = (status) => {
    // Handle different possible inactive status values
    if (!status) return true;
    const statusStr = String(status).toLowerCase().trim();
    return statusStr === 'inactive' || statusStr === '0' || statusStr === 'false' || statusStr === '';
};

// Get field options for checkbox/select fields based on user type
const getFieldOptions = (fieldName) => {
    const userType = AddonsUsers.edit_user_popup.user_type;
    const userData = AddonsUsers.edit_user_popup.user_data;
    
    // Get the registration form fields from settings
    let settings = null;
    if (userType === 'sellers') {
        settings = window.tfhb_core_apps?.sellers_settings?.registration_froms_fields || [];
    } else if (userType === 'buyers') {
        settings = window.tfhb_core_apps?.buyers_settings?.registration_froms_fields || [];
    } else if (userType === 'exhibitors') {
        settings = window.tfhb_core_apps?.exhibitors_settings?.registration_froms_fields || [];
    }
    
    if (settings && Array.isArray(settings)) {
        const field = settings.find(f => f.name === fieldName);
        if (field && field.options && Array.isArray(field.options)) {
            return field.options;
        }
    }
    
    // Fallback: return empty array if no options found
    return [];
};

// Get field type for proper input rendering
const getFieldType = (fieldName) => {
    const userType = AddonsUsers.edit_user_popup.user_type;
    
    // Get the registration form fields from settings
    let settings = null;
    if (userType === 'sellers') {
        settings = window.tfhb_core_apps?.sellers_settings?.registration_froms_fields || [];
    } else if (userType === 'buyers') {
        settings = window.tfhb_core_apps?.buyers_settings?.registration_froms_fields || [];
    } else if (userType === 'exhibitors') {
        settings = window.tfhb_core_apps?.exhibitors_settings?.registration_froms_fields || [];
    }
    
    if (settings && Array.isArray(settings)) {
        const field = settings.find(f => f.name === fieldName);
        if (field && field.type) {
            return field.type;
        }
    }
    
    // Fallback: return 'text' if no type found
    return 'text';
};

// Lifecycle
onBeforeMount(() => {
    AddonsUsers.init();
});

onBeforeRouteLeave(() => {
    // Cleanup if needed
});
</script>

<template>
    <div class="tfhb-admin-dashboard tfhb-admin-meetings "> 
        <Header v-if="$front_end_dashboard == false" :title="$tfhb_trans('Addons Users Management')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
     
    </div>
    <div class="tfhb-addons-users-wrap">
        
        <!-- Dashboard Heading Wrap --> 

        <!-- Filter Box -->
         <div class="tfhb-filter-box tfhb-flexbox tfhb-justify-between tfhb-align-center tfhb-gap-8 tfhb-mt-24">
             <!-- Search -->
             <div class="tfhb-header-filters">
                 <input 
                     type="text"  
                     :placeholder="$tfhb_trans('Search users...')" 
                     :value="AddonsUsers.search_query"
                     @input="handleSearch"
                 /> 
                 <span><Icon name="Search" size="20" /></span>
             </div>

             <!-- Bulk Actions -->
             <div class="tfhb-bulk-actions tfhb-flexbox tfhb-align-center tfhb-gap-8">
                 <HbDropdown
                     v-model="AddonsUsers.bulk_action"
                     :placeholder="$tfhb_trans('Bulk Actions')"
                     :option="[
                         {'name': 'Active', 'value': 'activate'},
                         {'name': 'Deactive', 'value': 'deactivate'}
                     ]"
                     @tfhb-onchange="[]"
                 />
                 <HbButton 
                     v-if="AddonsUsers.bulk_action && selectedCount > 0"
                     classValue="tfhb-btn boxed-btn"
                     @click="handleBulkAction"
                     :buttonText="`${AddonsUsers.bulk_action === 'activate' ? 'Make Active' : 'Make Deactive'} ${selectedCount} Users`"
                     :pre_loader="AddonsUsers.update_preloader"
                     :hover_animation="false"
                 />
             </div>
         </div>

        <!-- Tab Buttons -->
        <div class="tfhb-tab-buttons tfhb-flexbox tfhb-gap-8 tfhb-mt-24">
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                :class="AddonsUsers.current_tab === 'sellers' ? 'active' : ''"
                @click="handleTabChange('sellers')"
                :buttonText="$tfhb_trans('Sellers')"
                icon="Users"
                :hover_animation="false"
                icon_position="left"
            />
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                :class="AddonsUsers.current_tab === 'buyers' ? 'active' : ''"
                @click="handleTabChange('buyers')"
                :buttonText="$tfhb_trans('Buyers')"
                icon="UserCheck"
                :hover_animation="false"
                icon_position="left"
            />
            <HbButton 
                classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                :class="AddonsUsers.current_tab === 'exhibitors' ? 'active' : ''"
                @click="handleTabChange('exhibitors')"
                :buttonText="$tfhb_trans('Exhibitors')"
                icon="Building2"
                :hover_animation="false"
                icon_position="left"
            />
        </div>

        <!-- Users Table -->
        <div :class="{'tfhb-skeleton': AddonsUsers.skeleton}" class="tfhb-booking-details tfhb-mt-32" v-if="paginatedUsers.length > 0">
            <table class="table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="column-cb">
                            <input 
                                type="checkbox" 
                                :checked="allUsersSelected"
                                @change="handleAllUsersSelection"
                            />
                        </th>
                        <th>{{ $tfhb_trans('Name') }}</th>
                        <th>{{ $tfhb_trans('Email') }}</th>
                        <th>{{ $tfhb_trans('Status') }}</th>
                        <th>{{ $tfhb_trans('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in paginatedUsers" :key="user.id">
                        <td class="column-cb">
                            <input 
                                type="checkbox" 
                                :checked="AddonsUsers.selected_users.includes(user.id)"
                                @change="handleUserSelection(user.id)"
                            />
                        </td>
                        <td>
                            <div class="tfhb-list-data-event-title">
                                <strong>{{ user.name || 'N/A' }}</strong>
                            </div>
                        </td>
                        <td>
                            <div class="tfhb-list-data-event-title">
                                {{ user.email || 'N/A' }}
                            </div>
                        </td>
                        <td>
                            <div class="tfhb-details-status tfhb-flexbox tfhb-justify-normal tfhb-gap-0">
                                <div class="status" :class="getStatusClass(user.status)">
                                    {{ getStatusText(user.status) }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="tfhb-details-action tfhb-flexbox tfhb-justify-normal tfhb-gap-16">
                                <span @click.stop="showUserDetails(user)">
                                    <Icon name="Eye" width="20" />
                                </span>
                                <span @click.stop="AddonsUsers.showEditUser(user, AddonsUsers.current_tab)" class="tfhb-edit-btn tfhb-flexbox tfhb-justify-center tfhb-align-center tfhb-gap-4">
                                    {{ $tfhb_trans('Edit') }}
                                </span>
                                <span v-if="isUserInactive(user.status)" @click.stop="handleStatusUpdate(user.id, 'activate')" class="tfhb-activate-btn tfhb-flexbox tfhb-justify-center tfhb-align-center tfhb-gap-4">
                                     <Icon name="Check" width="16" />
                                     {{ $tfhb_trans('Active') }}
                                 </span> 
                                 <span v-else @click.stop="handleStatusUpdate(user.id, 'deactivate')" class="tfhb-deactivate-btn tfhb-flexbox tfhb-justify-center tfhb-align-center tfhb-gap-4">
                                     <Icon name="X" width="16" />
                                     {{ $tfhb_trans('Deactive') }}
                                 </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        

        <!-- Empty State -->
        <div v-else-if="!AddonsUsers.skeleton" class="tfhb-empty-notice-box-wrap tfhb-flexbox tfhb-gap-16 tfhb-booking-notice tfhb-mt-32">
            <Icon name="Users" size="48" />
            <p>{{ $tfhb_trans('No users found') }}</p>
        </div>

        <!-- Pagination -->
        <div v-if="AddonsUsers.pagination.total_pages > 1" class="tfhb-booking-details-pagination tfhb-flexbox tfhb-mt-32">
            <div class="tfhb-prev-next-button">
                <a href="#" @click.prevent="prevPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="AddonsUsers.pagination.current_page === 1">
                    <Icon name="ArrowLeft" width="20" />{{ $tfhb_trans('Previous') }}
                </a>
            </div>
            <div class="tfhb-pagination">
                <ul class="tfhb-flexbox tfhb-gap-0 tfhb-justify-normal">
                    <li v-for="page in AddonsUsers.pagination.total_pages" :key="page" :class="{ active: page === AddonsUsers.pagination.current_page }">
                        <a href="#" @click.prevent="changePage(page)" :class="{ 'active-link': page === AddonsUsers.pagination.current_page }">{{ page }}</a>
                    </li>
                </ul>
            </div>
            <div class="tfhb-prev-next-button">
                <a href="#" @click.prevent="nextPage" class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal" :disabled="AddonsUsers.pagination.current_page === AddonsUsers.pagination.total_pages">
                    {{ $tfhb_trans('Next') }}<Icon name="ArrowRight" width="20" />
                </a>
            </div>
        </div>

        <!-- User Details Popup -->
        <HbPopup 
            :isOpen="AddonsUsers.user_details_popup.show" 
            @modal-close="closeUserDetails" 
            max_width="800px" 
            name="user-details-modal"
        >
            <template #header>
                <h3>{{ $tfhb_trans('User Details') }}</h3>
            </template>
            
            <template #content>
                <div v-if="AddonsUsers.user_details_popup.user_data" class="tfhb-booking-info tfhb-full-width tfhb-flexbox tfhb-gap-16">
                    <!-- Basic Info -->
                    <div class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width">
                        <h3>{{ $tfhb_trans('Basic Information') }}</h3>
                        <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-12">
                            <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                                <Icon name="User" size="20" /> 
                                {{ AddonsUsers.user_details_popup.user_data.name || 'N/A' }}
                            </div>
                            <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                                <Icon name="Mail" size="20" /> 
                                {{ AddonsUsers.user_details_popup.user_data.email || 'N/A' }}
                            </div>
                            <div class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                                <Icon name="CheckCircle" size="20" /> 
                                <span :class="getStatusClass(AddonsUsers.user_details_popup.user_data.status)">
                                    {{ getStatusText(AddonsUsers.user_details_popup.user_data.status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- User Data -->
                     <div v-if="AddonsUsers.user_details_popup.user_data.data" class="tfhb-admin-card-box tfhb-booking-info-wrap tfhb-full-width">
                         <h3>{{ $tfhb_trans('Registration Data') }}</h3>
                         <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-12">
                             <div v-for="(value, key) in AddonsUsers.user_details_popup.user_data.data" :key="key" class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8">
                                 <Icon name="FileText" size="20" />
                                 <div class="tfhb-booking-details">
                                     <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>
                                     <span v-if="Array.isArray(value)" class="tfhb-array-list">
                                         <span v-for="item in value" :key="item" class="tfhb-array-item">{{ item }}</span>
                                     </span>
                                     <span v-else-if="typeof value === 'object'" class="tfhb-object-value">
                                         {{ JSON.stringify(value, null, 2) }}
                                     </span>
                                     <span v-else-if="value === '' || value === null || value === undefined" class="tfhb-empty-value">
                                         {{ $tfhb_trans('Not provided') }}
                                     </span>
                                     <span v-else>{{ value }}</span>
                                 </div>
                             </div>
                         </div>
                     </div>
                </div>
            </template>
        </HbPopup>

        <!-- Edit User Popup -->
        <HbPopup 
            :isOpen="AddonsUsers.edit_user_popup.show" 
            @modal-close="AddonsUsers.closeEditUser()" 
            max_width="1000px" 
            name="edit-user-modal"
            gap="24px"
        >
            <template #header>
                <h3>{{ $tfhb_trans('Edit User Data') }} - {{ AddonsUsers.edit_user_popup.user_data?.name || 'User' }}</h3>
            </template>
            
            <template #content>
                <div v-if="AddonsUsers.edit_user_popup.user_data" class="tfhb-edit-user-form">
                    <!-- Success/Error Messages -->
                    <div v-if="AddonsUsers.edit_user_popup.success" class="tfhb-success-message">
                        {{ $tfhb_trans('User data updated successfully!') }}
                    </div>
                    <div v-if="AddonsUsers.edit_user_popup.error" class="tfhb-error-message">
                        {{ AddonsUsers.edit_user_popup.error }}
                    </div>
                    
                                         <!-- Dynamic Form Fields -->
                     <div class="tfhb-form-fields-container">
                         <div v-if="Object.keys(AddonsUsers.edit_user_popup.form_data).length === 0" class="tfhb-loading-message">
                             {{ $tfhb_trans('Loading form fields...') }}
                         </div>
                         <div v-else>
                             <!-- Basic Information Section -->
                             <div class="tfhb-form-section">
                                 <h4>{{ $tfhb_trans('Basic Information') }}</h4>
                                 <div class="tfhb-form-single-column">
                                     <div v-for="(value, key) in AddonsUsers.edit_user_popup.form_data" :key="key" class="tfhb-form-field">
                                         <label :for="key" class="tfhb-field-label">
                                             {{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                         </label>
                                         
                                         <!-- Text Input -->
                                         <div v-if="typeof value === 'string' && !key.includes('url') && !key.includes('image')" class="tfhb-field-input">
                                             <input 
                                                 :type="key === 'email' ? 'email' : 'text'"
                                                 :id="key"
                                                 v-model="AddonsUsers.edit_user_popup.form_data[key]"
                                                 :placeholder="key.replace(/_/g, ' ')"
                                                 class="tfhb-input"
                                                 :disabled="key === 'email'"
                                             />
                                         </div>
                                         
                                         <!-- URL Input -->
                                         <div v-else-if="typeof value === 'string' && (key.includes('url') || key.includes('image'))" class="tfhb-field-input">
                                             <input 
                                                 type="url"
                                                 :id="key"
                                                 v-model="AddonsUsers.edit_user_popup.form_data[key]"
                                                 :placeholder="'Enter ' + key.replace(/_/g, ' ')"
                                                 class="tfhb-input"
                                             />
                                         </div>
                                         
                                         <!-- Array Input (for checkbox fields from registration) -->
                                         <div v-else-if="Array.isArray(value)" class="tfhb-field-input">
                                             <div class="tfhb-checkbox-group">
                                                 <label v-for="option in getFieldOptions(key)" :key="option" class="tfhb-checkbox-item">
                                                     <input 
                                                         type="checkbox"
                                                         :value="option"
                                                         v-model="AddonsUsers.edit_user_popup.form_data[key]"
                                                         class="tfhb-checkbox"
                                                     />
                                                     <span class="tfhb-checkbox-label">{{ option }}</span>
                                                 </label>
                                             </div>
                                         </div>
                                         
                                         <!-- Select Input (for select fields from registration) -->
                                         <div v-else-if="getFieldType(key) === 'select'" class="tfhb-field-input">
                                             <select 
                                                 :id="key"
                                                 v-model="AddonsUsers.edit_user_popup.form_data[key]"
                                                 class="tfhb-select"
                                             >
                                                 <option value="">{{ $tfhb_trans('Select an option') }}</option>
                                                 <option v-for="option in getFieldOptions(key)" :key="option" :value="option">
                                                     {{ option }}
                                                 </option>
                                             </select>
                                         </div>
                                         
                                         
                                         
                                         <!-- Default Input -->
                                         <div v-else class="tfhb-field-input">
                                             <input 
                                                 type="text"
                                                 :id="key"
                                                 v-model="AddonsUsers.edit_user_popup.form_data[key]"
                                                 :placeholder="'Enter ' + key.replace(/_/g, ' ')"
                                                 class="tfhb-input"
                                             />
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                    
                    <!-- Action Buttons -->
                    <div class="tfhb-form-actions">
                        <HbButton 
                            classValue="tfhb-btn secondary-btn"
                            @click="AddonsUsers.closeEditUser()"
                            :buttonText="$tfhb_trans('Cancel')"
                            :disabled="AddonsUsers.edit_user_popup.loading"
                        />
                        <HbButton 
                            classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation"
                            @click="AddonsUsers.saveEditUser()"
                            :buttonText="$tfhb_trans('Save Changes')"
                            icon="Save"
                            :pre_loader="AddonsUsers.edit_user_popup.loading"
                            :disabled="AddonsUsers.edit_user_popup.loading"
                        />
                    </div>
                </div>
            </template>
        </HbPopup>


    </div>
</template>

<style scoped>
/* Basic styling using existing classes */
.tfhb-addons-users-wrap {
    padding: 20px;
}

.tfhb-header-title h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.tfhb-header-title p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.tfhb-tab-buttons {
    gap: 8px;
}

.tfhb-tab-buttons .tfhb-btn.active {
    background: #0073aa;
    color: white;
}

 .tfhb-filter-box {
     align-items: center;
 }

 .tfhb-bulk-actions {
     align-items: center;
     gap: 8px;
     justify-content: flex-end;
 }

 .tfhb-header-filters {
     display: flex;
     align-items: center;
     gap: 8px;
 }

/* Status styling */
.status.status-active {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status.status-inactive {
    background: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

/* Array and object display */
.tfhb-array-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.tfhb-array-item {
    background: #0073aa;
    color: #fff !important;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 11px;
}

.tfhb-object-value {
    background: #f5f5f5;
    padding: 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    white-space: pre-wrap;
}

.tfhb-empty-value {
    color: #999;
    font-style: italic;
}

 /* Checkbox styling */
 .tfhb-booking-details input[type="checkbox"] {
     width: 16px;
     height: 16px;
 }
 
 /* Button styling using existing classes */
 .tfhb-activate-btn,
 .tfhb-deactivate-btn {
     padding: 4px 8px !important;
     border-radius: 4px !important;
     font-size: 12px !important;
     font-weight: 500 !important;
     cursor: pointer !important;
     transition: background-color 0.2s ease !important;
     min-width: 80px !important;
     text-align: center !important;
 }

 /* Activate button styling - Green for all user types */
 .tfhb-activate-btn {
     background: #28a745 !important;
     color: #fff !important;
 }
 
 .tfhb-activate-btn:hover {
     background: #218838 !important;
 }

 /* Deactivate button styling - Red for all user types */
 .tfhb-deactivate-btn {
     background: #dc3545 !important;
     color: #fff !important;
 }
 
 .tfhb-deactivate-btn:hover {
     background: #c82333 !important;
 }

 /* Sellers specific colors (if needed) */
 .tfhb-sellers .tfhb-activate-btn {
     background: #28a745 !important;
 }
 
 .tfhb-sellers .tfhb-deactivate-btn {
     background: #dc3545 !important;
 }

 /* Buyers specific colors (if needed) */
 .tfhb-buyers .tfhb-activate-btn {
     background: #28a745 !important;
 }
 
 .tfhb-buyers .tfhb-deactivate-btn {
     background: #dc3545 !important;
 }

 /* Exhibitors specific colors (if needed) */
 .tfhb-exhibitors .tfhb-activate-btn {
     background: #28a745 !important;
 }
 
 .tfhb-exhibitors .tfhb-deactivate-btn {
     background: #dc3545 !important;
 }

/* Responsive adjustments */
@media (max-width: 768px) {
    .tfhb-filter-box {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .tfhb-bulk-actions {
        justify-content: center;
    }
    
    .tfhb-tab-buttons {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Edit User Popup Styles */
.tfhb-edit-user-form {
    max-width: 100%;
}

.tfhb-form-section {
    margin-bottom: 2rem;
}

.tfhb-form-section h4 {
    margin: 0 0 1rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d3748;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e3e6ed;
}

.tfhb-form-single-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.tfhb-form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.tfhb-field-label {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.875rem;
}

.tfhb-field-input {
    width: 100%;
}

.tfhb-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #ffffff;
}

.tfhb-input:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.tfhb-input:disabled {
    background-color: #f7fafc;
    color: #718096;
    cursor: not-allowed;
}

.tfhb-checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fb;
    border: 1px solid #e3e6ed;
    border-radius: 6px;
}

.tfhb-checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.tfhb-checkbox-item:hover {
    background: #e9ecef;
}

.tfhb-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #0073aa;
    cursor: pointer;
}

.tfhb-checkbox-label {
    font-size: 0.875rem;
    color: #2d3748;
    cursor: pointer;
    user-select: none;
}

.tfhb-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #ffffff;
    cursor: pointer;
}

.tfhb-select:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}



.tfhb-form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
    margin-top: 2rem;
}

.tfhb-success-message {
    background: #d1fae5;
    color: #065f46;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border: 1px solid #a7f3d0;
    margin-bottom: 1rem;
    font-weight: 500;
}

.tfhb-error-message {
    background: #fee2e2;
    color: #991b1b;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border: 1px solid #fca5a5;
    margin-bottom: 1rem;
    font-weight: 500;
}

.tfhb-loading-message {
    text-align: center;
    padding: 2rem 1rem;
    color: #3b82f6;
    font-weight: 500;
}

/* Responsive Design for Edit Form */
@media (max-width: 768px) {
    .tfhb-form-single-column {
        gap: 1rem;
    }
    
    .tfhb-form-actions {
        flex-direction: column;
    }
    
    .tfhb-form-actions .tfhb-btn {
        width: 100%;
    }
    

    
    .tfhb-checkbox-group {
        padding: 0.75rem;
    }
}
</style>
 