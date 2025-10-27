import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const AddonsUsers = reactive({
    skeleton: true,
    update_preloader: false,
    users: {
        buyers: [],
        sellers: [],
        exhibitors: []
    },
    filtered_users: {
        buyers: [],
        sellers: [],
        exhibitors: []
    },
    current_tab: 'sellers',
    search_query: '',
    selected_users: [],
    bulk_action: '',
    pagination: {
        current_page: 1,
        per_page: 10,
        total_pages: 1,
        total_items: 0
    },
    filters: {
        status: 'all',
        date_range: {
            from: '',
            to: ''
        }
    },
    user_details_popup: {
        show: false,
        user_data: null,
        user_type: ''
    },
    
    // Edit user popup state
    edit_user_popup: {
        show: false,
        user_data: null,
        user_type: '',
        user_id: null,
        form_data: {},
        loading: false,
        success: false,
        error: ''
    },

    // Fetch all users for a specific type
    async fetchUsers(userType = 'sellers') {
        this.skeleton = true;
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + `hydra-booking/v1/addons/${userType}-list`, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                params: {
                    current_user_id: tfhb_core_apps.user.id
                },
                withCredentials: true
            });
            
            if (response.data.success && response.data.data) {
                this.users[userType] = response.data.data;
                console.log(`Fetched ${userType} users:`, this.users[userType]);
                
                // Log status values to debug
                this.users[userType].forEach(user => {
                    console.log(`User ${user.id} (${user.name}) status: "${user.status}" (type: ${typeof user.status})`);
                });
                
                // Initialize filtered users with all users and sort them
                this.filtered_users[userType] = [...this.users[userType]];
                
                // Sort users alphabetically by company name
                this.filtered_users[userType].sort((a, b) => {
                    let companyNameA = '';
                    let companyNameB = '';
                    
                    // Get company name based on user type
                    if (userType === 'sellers') {
                        companyNameA = (a.data?.denominazione_operatore_azienda || '').toLowerCase();
                        companyNameB = (b.data?.denominazione_operatore_azienda || '').toLowerCase();
                    } else if (userType === 'buyers') {
                        companyNameA = (a.data?.travel_agent_name || '').toLowerCase();
                        companyNameB = (b.data?.travel_agent_name || '').toLowerCase();
                    } else if (userType === 'exhibitors') {
                        companyNameA = (a.data?.nome_e_cognome || '').toLowerCase();
                        companyNameB = (b.data?.nome_e_cognome || '').toLowerCase();
                    }
                    
                    // Handle empty values by putting them at the end
                    if (!companyNameA && !companyNameB) return 0;
                    if (!companyNameA) return 1;
                    if (!companyNameB) return -1;
                    
                    // Sort alphabetically
                    return companyNameA.localeCompare(companyNameB);
                });
                
                this.updatePagination();
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            toast.error('Failed to fetch users', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        } finally {
            this.skeleton = false;
        }
    },

    // Update user status (activate/deactivate)
    async updateUserStatus(userId, userType, action) {
        console.log(`Attempting to ${action} user ${userId} of type ${userType}`);
        
        try {
            const requestData = {
                user_id: userId,
                action: action
            };
            
            console.log('Request data:', requestData);
            console.log('API endpoint:', tfhb_core_apps.rest_route + `hydra-booking/v1/addons/${userType}-status`);
            
            const response = await axios.post(tfhb_core_apps.rest_route + `hydra-booking/v1/addons/${userType}-status`, requestData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });

            console.log('Response received:', response.data);

            if (response.data.success) {
                // Update local user data
                const userIndex = this.users[userType].findIndex(user => user.id === userId);
                if (userIndex !== -1) {
                    this.users[userType][userIndex].status = action === 'activate' ? 'active' : 'inactive';
                    console.log(`Updated local user status to: ${this.users[userType][userIndex].status}`);
                    
                    // Update filtered users as well
                    const filteredUserIndex = this.filtered_users[userType].findIndex(user => user.id === userId);
                    if (filteredUserIndex !== -1) {
                        this.filtered_users[userType][filteredUserIndex].status = action === 'activate' ? 'active' : 'inactive';
                    }
                }

                toast.success(response.data.message || `User ${action}d successfully`, {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                
                // Refresh the user list to ensure UI is updated
                await this.fetchUsers(userType);
            } else {
                console.error('API returned error:', response.data);
                toast.error(response.data.message || 'Failed to update user status', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
            }
        } catch (error) {
            console.error('Error updating user status:', error);
            console.error('Error details:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status
            });
            toast.error('Failed to update user status', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    },

    // Bulk update user statuses
    async bulkUpdateStatus(action) {
        if (this.selected_users.length === 0) {
            toast.warning('Please select users first', {
                position: 'bottom-right',
                autoClose: 1500,
            });
            return;
        }

        this.update_preloader = true;
        try {
            console.log(`Bulk ${action} for users:`, this.selected_users);
            
            const response = await axios.post(tfhb_core_apps.rest_route + `hydra-booking/v1/addons/${this.current_tab}-bulk-status`, {
                user_ids: this.selected_users,
                action: action
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });

            console.log('Bulk update response:', response.data);

            if (response.data.success) {
                // Update local user data immediately
                this.users[this.current_tab].forEach(user => {
                    if (this.selected_users.includes(user.id)) {
                        user.status = action === 'activate' ? 'active' : 'inactive';
                        console.log(`Updated user ${user.id} status to: ${user.status}`);
                    }
                });
                
                // Update filtered users as well
                this.filtered_users[this.current_tab].forEach(user => {
                    if (this.selected_users.includes(user.id)) {
                        user.status = action === 'activate' ? 'active' : 'inactive';
                    }
                });

                // Clear selections
                this.selected_users = [];
                
                // Update pagination
                this.updatePagination();
                
                toast.success(response.data.message || `Users ${action === 'activate' ? 'activated' : 'deactivated'} successfully`, {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                
                // Refresh the user list to ensure UI is updated
                await this.fetchUsers(this.current_tab);
            } else {
                console.error('API returned error:', response.data);
                toast.error(response.data.message || 'Failed to update users', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
            }
        } catch (error) {
            console.error('Error bulk updating users:', error);
            console.error('Error details:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status
            });
            toast.error('Failed to update users', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        } finally {
            this.update_preloader = false;
        }
    },

    // Search users
    searchUsers(query) {
        this.search_query = query;
        this.pagination.current_page = 1;
        // Filter users based on search query
        this.filterUsers();
    },

    // Filter users based on current filters
    filterUsers() {
        const currentTab = this.current_tab;
        const searchQuery = this.search_query.toLowerCase().trim();
        
        let filteredUsers = [];
        
        if (!searchQuery) {
            // If no search query, show all users
            filteredUsers = [...this.users[currentTab]];
        } else {
            // Filter users based on search query
            filteredUsers = this.users[currentTab].filter(user => {
                const name = (user.name || '').toLowerCase();
                const email = (user.email || '').toLowerCase();
                
                // Get company name based on user type
                let companyName = '';
                let alternativeCompanyName = '';
                
                if (currentTab === 'sellers') {
                    companyName = (user.data?.denominazione_operatore_azienda || '').toLowerCase();
                    alternativeCompanyName = (user.data?.eventuale_altra_denominazione || '').toLowerCase();
                } else if (currentTab === 'buyers') {
                    companyName = (user.data?.travel_agent_name || '').toLowerCase();
                } else if (currentTab === 'exhibitors') {
                    companyName = (user.data?.company_name || '').toLowerCase();
                }
                
                return name.includes(searchQuery) || 
                       email.includes(searchQuery) || 
                       companyName.includes(searchQuery) ||
                       (alternativeCompanyName && alternativeCompanyName.includes(searchQuery));
            });
        }
        
        // Sort users alphabetically by company name
        filteredUsers.sort((a, b) => {
            let companyNameA = '';
            let companyNameB = '';
            
            // Get company name based on user type
            if (currentTab === 'sellers') {
                companyNameA = (a.data?.denominazione_operatore_azienda || '').toLowerCase();
                companyNameB = (b.data?.denominazione_operatore_azienda || '').toLowerCase();
            } else if (currentTab === 'buyers') {
                companyNameA = (a.data?.travel_agent_name || '').toLowerCase();
                companyNameB = (b.data?.travel_agent_name || '').toLowerCase();
            } else if (currentTab === 'exhibitors') {
                companyNameA = (a.data?.nome_e_cognome || '').toLowerCase();
                companyNameB = (b.data?.nome_e_cognome || '').toLowerCase();
            }
            
            // Handle empty values by putting them at the end
            if (!companyNameA && !companyNameB) return 0;
            if (!companyNameA) return 1;
            if (!companyNameB) return -1;
            
            // Sort alphabetically
            return companyNameA.localeCompare(companyNameB);
        });
        
        this.filtered_users[currentTab] = filteredUsers;
        this.pagination.current_page = 1;
        this.updatePagination();
    },

    // Update pagination
    updatePagination() {
        const totalItems = this.filtered_users[this.current_tab].length;
        this.pagination.total_items = totalItems;
        this.pagination.total_pages = Math.ceil(totalItems / this.pagination.per_page);
        
        if (this.pagination.current_page > this.pagination.total_pages) {
            this.pagination.current_page = this.pagination.total_pages || 1;
        }
    },

    // Get paginated users
    getPaginatedUsers() {
        const start = (this.pagination.current_page - 1) * this.pagination.per_page;
        const end = start + this.pagination.per_page;
        return this.filtered_users[this.current_tab].slice(start, end);
    },

    // Change page
    changePage(page) {
        if (page >= 1 && page <= this.pagination.total_pages) {
            this.pagination.current_page = page;
        }
    },

    // Select/deselect user
    toggleUserSelection(userId) {
        const index = this.selected_users.indexOf(userId);
        if (index === -1) {
            this.selected_users.push(userId);
        } else {
            this.selected_users.splice(index, 1);
        }
    },

    // Select/deselect all users on current page
    toggleAllUsersSelection() {
        const currentUsers = this.getPaginatedUsers();
        const allSelected = currentUsers.every(user => this.selected_users.includes(user.id));
        
        if (allSelected) {
            // Deselect all current page users
            currentUsers.forEach(user => {
                const index = this.selected_users.indexOf(user.id);
                if (index !== -1) {
                    this.selected_users.splice(index, 1);
                }
            });
        } else {
            // Select all current page users
            currentUsers.forEach(user => {
                if (!this.selected_users.includes(user.id)) {
                    this.selected_users.push(user.id);
                }
            });
        }
    },

    // Show user details popup
    showUserDetails(user, userType) {
        this.user_details_popup.user_data = user;
        this.user_details_popup.user_type = userType;
        this.user_details_popup.show = true;
    },

    // Close user details popup
    closeUserDetails() {
        this.user_details_popup.show = false;
        this.user_details_popup.user_data = null;
        this.user_details_popup.user_type = '';
    },

    // Show edit user popup
    showEditUser(user, userType) {
        this.edit_user_popup.user_data = user;
        this.edit_user_popup.user_type = userType;
        this.edit_user_popup.user_id = user.id;
        this.edit_user_popup.show = true;
        this.initializeEditForm();
    },

    // Close edit user popup
    closeEditUser() {
        this.edit_user_popup.show = false;
        this.edit_user_popup.user_data = null;
        this.edit_user_popup.user_type = '';
        this.edit_user_popup.user_id = null;
        this.edit_user_popup.form_data = {};
        this.edit_user_popup.loading = false;
        this.edit_user_popup.success = false;
        this.edit_user_popup.error = '';
    },

    // Initialize edit form with user data
    initializeEditForm() {
        if (!this.edit_user_popup.user_data) return;
        
        // Get user data from the appropriate meta field
        const userData = this.edit_user_popup.user_data.data || {};
        
        // Get registration form fields for this user type
        const userType = this.edit_user_popup.user_type;
        let registrationFields = [];
        
        if (userType === 'sellers') {
            registrationFields = window.tfhb_core_apps?.sellers_settings?.registration_froms_fields || [];
        } else if (userType === 'buyers') {
            registrationFields = window.tfhb_core_apps.buyers_settings?.registration_froms_fields || [];
        } else if (userType === 'exhibitors') {
            registrationFields = window.tfhb_core_apps.exhibitors_settings?.registration_froms_fields || [];
        }
        
        // Only include registration form fields, filter out extra fields
        const filteredFormData = {};
        if (Array.isArray(registrationFields)) {
            registrationFields.forEach(field => {
                if (field.name && userData.hasOwnProperty(field.name)) {
                    filteredFormData[field.name] = userData[field.name];
                }
            });
        }
        
        // Initialize form with only registration fields
        this.edit_user_popup.form_data = filteredFormData;
        
        // Remove email from editable data for security
        delete this.edit_user_popup.form_data.email;
        
        // Ensure checkbox fields are properly initialized as arrays
        this.ensureCheckboxFieldsAsArrays();
    },
    
    // Ensure checkbox fields are properly initialized as arrays
    ensureCheckboxFieldsAsArrays() {
        const userType = this.edit_user_popup.user_type;
        
        // Get registration form fields from settings
        let settings = null;
        if (userType === 'sellers') {
            settings = window.tfhb_core_apps?.sellers_settings?.registration_froms_fields || [];
        } else if (userType === 'buyers') {
            settings = window.tfhb_core_apps?.buyers_settings?.registration_froms_fields || [];
        } else if (userType === 'exhibitors') {
            settings = window.tfhb_core_apps?.exhibitors_settings?.registration_froms_fields || [];
        }
        
        if (settings && Array.isArray(settings)) {
            settings.forEach(field => {
                if (field.type === 'checkbox' && field.name && this.edit_user_popup.form_data.hasOwnProperty(field.name)) {
                    // Ensure checkbox fields are arrays
                    if (!Array.isArray(this.edit_user_popup.form_data[field.name])) {
                        this.edit_user_popup.form_data[field.name] = [];
                    }
                }
            });
        }
    },

    // Save edited user data
    async saveEditUser() {
        if (!this.edit_user_popup.user_id || !this.edit_user_popup.user_type) return;
        
        this.edit_user_popup.loading = true;
        this.edit_user_popup.error = '';
        
        try {
            const endpoint = `hydra-booking/v1/addons/${this.edit_user_popup.user_type}-edit`;
            const response = await axios.post(tfhb_core_apps.rest_route + endpoint, {
                user_id: this.edit_user_popup.user_id,
                user_data: this.edit_user_popup.form_data
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                this.edit_user_popup.success = true;
                
                // Update local user data
                const userIndex = this.users[this.edit_user_popup.user_type].findIndex(
                    user => user.id === this.edit_user_popup.user_id
                );
                if (userIndex !== -1) {
                    this.users[this.edit_user_popup.user_type][userIndex].data = response.data.data;
                }
                
                toast.success(response.data.message || 'User data updated successfully!', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                
                // Close popup after success
                setTimeout(() => {
                    this.closeEditUser();
                }, 1500);
            } else {
                this.edit_user_popup.error = response.data.message || 'Failed to update user data.';
                toast.error(this.edit_user_popup.error, {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
            }
        } catch (error) {
            console.error('Error updating user data:', error);
            this.edit_user_popup.error = 'Failed to update user data.';
            toast.error(this.edit_user_popup.error, {
                position: 'bottom-right',
                autoClose: 1500,
            });
        } finally {
            this.edit_user_popup.loading = false;
        }
    },

    // Change tab
    changeTab(tab) {
        this.current_tab = tab;
        this.selected_users = [];
        this.search_query = '';
        this.pagination.current_page = 1;
        this.fetchUsers(tab);
        // Filter users after fetching
        this.filterUsers();
    },

    // Initialize
    async init() {
        await this.fetchUsers(this.current_tab);
        await this.fetchRegistrationFields();
    },
    
    // Fetch registration form fields for editing
    async fetchRegistrationFields() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/registration-fields', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.status) {
                // Store registration fields globally for access in edit forms
                window.tfhb_core_apps = window.tfhb_core_apps || {};
                window.tfhb_core_apps.sellers_settings = { registration_froms_fields: response.data.sellers_fields };
                window.tfhb_core_apps.buyers_settings = { registration_froms_fields: response.data.buyers_fields };
                window.tfhb_core_apps.exhibitors_settings = { registration_froms_fields: response.data.exhibitors_fields };
            }
        } catch (error) {
            console.error('Error fetching registration fields:', error);
        }
    }
});

// Matching System Store
const MatchingSystem = reactive({
    loading: false,
    matchingData: [],
    sellers: [],
    buyers: [],
    availableDates: [],
    availableTimeSlots: [],
    meetingInfo: {
        meeting_id: 1
    },
    pagination: {
        current_page: 1,
        per_page: 20,
        total_pages: 1,
        total_items: 0
    },
    filters: {
        buyer_id: '',
        seller_id: '',
        date_search: '',
        page: 1,
        per_page: 20
    },
    selectedMatchingIds: [],
    selectAll: false,
    bulkAction: '-1',

    // Fetch matching data
    async fetchMatchingData() {
        this.loading = true;
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/matching-list', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                params: this.filters,
                withCredentials: true
            });
            
            if (response.data.success) {
                this.matchingData = response.data.data.matching || [];
                this.pagination.total_items = response.data.data.total || 0;
                this.pagination.total_pages = Math.ceil(this.pagination.total_items / this.pagination.per_page);
            } else {
                toast.error('Failed to fetch matching data', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
            }
        } catch (error) {
            console.error('Error fetching matching data:', error);
            toast.error('Failed to fetch matching data', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        } finally {
            this.loading = false;
        }
    },

    // Fetch sellers list
    async fetchSellers() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/sellers-list', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                this.sellers = response.data.data || [];
            }
        } catch (error) {
            console.error('Error fetching sellers:', error);
        }
    },

    // Fetch buyers list
    async fetchBuyers() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/buyers-list', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                this.buyers = response.data.data || [];
            }
        } catch (error) {
            console.error('Error fetching buyers:', error);
        }
    },

    // Fetch meeting availability
    async fetchMeetingAvailability() {
        try {
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/meeting-availability', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                this.availableDates = response.data.data.dates || [];
                this.meetingInfo.meeting_id = response.data.data.meeting_id || 1;
            }
        } catch (error) {
            console.error('Error fetching meeting availability:', error);
            toast.error('Failed to fetch meeting availability', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    },

    // Fetch time slots for a specific date
    async fetchTimeSlots(date) {
        if (!date || !this.meetingInfo.meeting_id) return;

        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/time-slots', {
                meeting_id: this.meetingInfo.meeting_id,
                selected_date: date
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                this.availableTimeSlots = response.data.data.time_slots || [];
            } else {
                this.availableTimeSlots = [];
                if (response.data.data.message) {
                    toast.warning(response.data.data.message, {
                        position: 'bottom-right',
                        autoClose: 1500,
                    });
                }
            }
        } catch (error) {
            console.error('Error fetching time slots:', error);
            toast.error('Failed to fetch time slots', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    },

    // Fetch user information
    async fetchUserInfo(userId, userType) {
        if (!userId) return '';

        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/user-info', {
                user_id: userId,
                user_type: userType
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                return response.data.data.html;
            }
        } catch (error) {
            console.error('Error fetching user info:', error);
        }
        return '';
    },

    // Add new matching
    async addMatching(matchingData) {
        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/add-matching', matchingData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                toast.success(response.data.message || 'Matching added successfully', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: true, data: response.data };
            } else {
                toast.error(response.data.message || 'Failed to add matching', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: false, message: response.data.message };
            }
        } catch (error) {
            console.error('Error adding matching:', error);
            toast.error('Failed to add matching', {
                position: 'bottom-right',
                autoClose: 1500,
            });
            return { success: false, message: 'Network error' };
        }
    },

    // Update existing matching
    async updateMatching(matchingData) {
        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/update-matching', matchingData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                toast.success(response.data.message || 'Matching updated successfully', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: true, data: response.data };
            } else {
                toast.error(response.data.message || 'Failed to update matching', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: false, message: response.data.message };
            }
        } catch (error) {
            console.error('Error updating matching:', error);
            toast.error('Failed to update matching', {
                position: 'bottom-right',
                autoClose: 1500,
            });
            return { success: false, message: 'Network error' };
        }
    },

    // Delete matching
    async deleteMatching(id) {
        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/delete-matching', { id }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                toast.success('Matching deleted successfully', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: true };
            } else {
                toast.error(response.data.message || 'Failed to delete matching', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: false, message: response.data.message };
            }
        } catch (error) {
            console.error('Error deleting matching:', error);
            toast.error('Failed to delete matching', {
                position: 'bottom-right',
                autoClose: 1500,
            });
            return { success: false, message: 'Network error' };
        }
    },

    // Bulk delete matchings
    async bulkDeleteMatchings(ids) {
        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/bulk-delete-matching', { ids }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                withCredentials: true
            });
            
            if (response.data.success) {
                toast.success(response.data.message || 'Matchings deleted successfully', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: true };
            } else {
                toast.error(response.data.message || 'Failed to delete matchings', {
                    position: 'bottom-right',
                    autoClose: 1500,
                });
                return { success: false, message: response.data.message };
            }
        } catch (error) {
            console.error('Error bulk deleting matchings:', error);
            toast.error('Failed to delete matchings', {
                position: 'bottom-right',
                autoClose: 1500,
            });
            return { success: false, message: 'Network error' };
        }
    },

    // Export matching data
    async exportMatching() {
        try {
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/export-matching', {
                filters: this.filters
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                },
                responseType: 'blob',
                withCredentials: true
            });
            
            // Create and download CSV file
            const blob = new Blob([response.data], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'matching-export.csv';
            a.click();
            window.URL.revokeObjectURL(url);
            
            toast.success('CSV exported successfully', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        } catch (error) {
            console.error('Error exporting CSV:', error);
            toast.error('Failed to export CSV', {
                position: 'bottom-right',
                autoClose: 1500,
            });
        }
    },

    // Apply filters
    applyFilters() {
        this.pagination.current_page = 1;
        this.fetchMatchingData();
    },

    // Clear filters
    clearFilters() {
        this.filters = {
            buyer_id: '',
            seller_id: '',
            date_search: '',
            page: 1,
            per_page: 20
        };
        this.pagination.current_page = 1;
        this.fetchMatchingData();
    },

    // Change page
    changePage(page) {
        if (page >= 1 && page <= this.pagination.total_pages) {
            this.pagination.current_page = page;
            this.fetchMatchingData();
        }
    },

    // Toggle select all
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedMatchingIds = this.getPaginatedMatchingData().map(item => item.id);
        } else {
            this.selectedMatchingIds = [];
        }
    },

    // Get paginated matching data
    getPaginatedMatchingData() {
        const start = (this.pagination.current_page - 1) * this.pagination.per_page;
        const end = start + this.pagination.per_page;
        return this.matchingData.slice(start, end);
    },

    // Initialize
    async init() {
        await Promise.all([
            this.fetchMatchingData(),
            this.fetchSellers(),
            this.fetchBuyers(),
            this.fetchMeetingAvailability()
        ]);
    }
});

export { AddonsUsers, MatchingSystem };
