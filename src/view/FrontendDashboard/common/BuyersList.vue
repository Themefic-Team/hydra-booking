<script setup> 
import { ref, reactive, onBeforeMount, defineProps } from 'vue';  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import axios from 'axios';
const selectedBuyer = ref(null)
const skeleton = ref(true);
// const buyers = ref([
//     {
//         id: 1,
//         name: 'Davide Rossetti',
//         role: 'Sales Manager',
//         email: 'davide@google.com',
//         phone: '339 050403',
//         location: 'Perugia, Italia',
//         avatar: 'DR',
//         status: 'Active',
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.davideagency.com',
//         preferredWorkshops: ['Italian incoming Travel Agents and TO', 'Genealogists', 'Concierge and booking services'],
//         regionsOfInterest: ['Argentina and Latin America']
//     },
//     {
//         id: 2,
//         name: 'Anna White',
//         role: 'Director / Joe Agency',
//         email: 'customer@email.com',
//         phone: '+01 917 055-0403',
//         location: 'New York, NY 10169',
//         avatar: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face',
//         status: 'Active',
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.joeagency.com',
//         preferredWorkshops: ['Italian incoming Travel Agents and TO', 'Genealogists', 'Concierge and booking services', 'Luxury accomodation (4-5* hotels and historical dwellings)'],
//         regionsOfInterest: ['Argentina and Latin America']
//     },
//     {
//         id: 3,
//         name: 'Marco Rossi',
//         role: 'Marketing Manager',
//         email: 'marco@agency.com',
//         phone: '+39 123 456 789',
//         location: 'Roma, Italia',
//         avatar: 'MR',
//         status: 'Disable',
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.marcoagency.com',
//         preferredWorkshops: ['Italian incoming Travel Agents and TO', 'Genealogists'],
//         regionsOfInterest: ['Europe']
//     },
//     {
//         id: 4,
//         name: 'Sarah Johnson',
//         role: 'Business Development',
//         email: 'sarah@travel.com',
//         phone: '+44 20 7946 0958',
//         location: 'London, UK',
//         avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
//         status: 'Active',
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.sarahagency.com',
//         preferredWorkshops: ['Concierge and booking services', 'Luxury accomodation (4-5* hotels and historical dwellings)'],
//         regionsOfInterest: ['North America']
//     },
//     {
//         id: 5,
//         name: 'Carlos Rodriguez',
//         role: 'Tourism Director',
//         email: 'carlos@tourism.com',
//         phone: '+34 91 123 4567',
//         location: 'Madrid, España',
//         avatar: 'CR',
//         status: 'Active',
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.carlosagency.com',
//         preferredWorkshops: ['Italian incoming Travel Agents and TO', 'Genealogists'],
//         regionsOfInterest: ['Latin America']
//     },
//     {
//         id: 6,
//         name: 'Lisa Chen',
//         role: 'Operations Manager',
//         email: 'lisa@operations.com',
//         phone: '+1 555 123 4567',
//         location: 'San Francisco, CA',
//         avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop&crop=face',
//         status: 'Active',
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.lisaagency.com',
//         preferredWorkshops: ['Concierge and booking services', 'Luxury accomodation (4-5* hotels and historical dwellings)'],
//         regionsOfInterest: ['Asia Pacific']
//     }
// ]);

const buyers = ref([]);
async function fetchBuyers() {
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/buyers-list', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            },
            params: {
                current_user_id: tfhb_core_apps.user.id
            },
            withCredentials: true
        });
        if (response.data.success && response.data.data) {
            buyers.value = response.data.data;
        }
    } catch (e) {
        // handle error
    } finally {
        skeleton.value = false;
    }
}
onBeforeMount(async () => {
    await fetchBuyers();
});
const selectBuyer = (buyer) => {
    selectedBuyer.value = buyer
}

const closeBuyerDetails = () => {
    selectedBuyer.value = null
}

const Tfhb_Buyer_Filter = (event) => {
    // Implement buyer filter functionality
    console.log('Filtering buyers:', event.target.value)
}
</script>

<template> 

    <div class="buyers-dashboard">
        <!-- Header Section with Filters -->
        <div class="buyers-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="buyers-title">Buyers</h1>
                    <p class="buyers-subtitle">Lorem ipsum dolor sit amet consectetur.</p>
                </div>
                
                <div class="header-right">
                    <div class="total-buyers">
                        <Icon name="Users" size=20 />
                        <span>Total Buyer {{ buyers.length }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter Section (from hosts.vue) -->
            <div class="tfhb-dashboard-heading tfhb-flexbox tfhb-justify-between">
                <div class="tfhb-header-filters">
                    <input type="text" @keyup="Tfhb_Buyer_Filter" placeholder="Search by host name" /> 
                    <span><Icon name="Search" size=20 /></span>
                </div>
                <div class="thb-admin-btn right tfhb-flexbox tfhb-gap-16"> 
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.exportBuyers()"
                        :buttonText="$tfhb_trans('Export Badge')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.exportBuyers()"
                        :buttonText="$tfhb_trans('Export')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : router.push({ name: 'BuyersImport' })"
                        :buttonText="$tfhb_trans('Import')"
                        icon="FileUp"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn boxed-btn flex-btn" 
                        @click="openModal"
                        :buttonText="$tfhb_trans('Add New Host')"
                        icon="PlusCircle"  
                        icon_position="left"
                    />  
                </div>  
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="buyers-content">
            <!-- Buyers List -->
            <div class="buyers-list" :class="{ 'with-details': selectedBuyer }">
                <div class="buyers-grid">
                    <div 
                        v-for="buyer in buyers" 
                        :key="buyer.id" 
                        class="buyer-card"
                        :class="{ 'selected': selectedBuyer && selectedBuyer.id === buyer.id }"
                        @click="selectBuyer(buyer)"
                    >
                        <div class="buyer-card-header">
                            <div class="buyer-avatar">
                                <img v-if="buyer.data.avatar && buyer.data.avatar.startsWith('http')" :src="buyer.data.avatar" alt="Buyer Avatar">
                                <span v-else class="avatar-text">{{ buyer.data.avatar }}</span>
                            </div>
                            <div class="buyer-info">
                                <h3 class="buyer-name">{{ buyer.data.name_of_participant }}</h3>
                                <p class="buyer-role">{{ buyer.data.job_title }}</p>
                            </div>
                            <div class="buyer-status">
                              
                                <span class="status-badge" :class="{ 
                                    'warning': buyer.status === 'inactive' 
                                }">{{ buyer.status }}</span>
                            </div>
                        </div>
                        
                        <div class="buyer-card-content">
                            <div class="contact-info">
                                <div class="contact-item">
                                    <Icon name="Mail" size=16 />
                                    <span>{{ buyer.data.email }}</span>
                                </div>
                                <div class="contact-item">
                                    <Icon name="Phone" size=16 />
                                    <span>{{ buyer.data.mobile_no }}</span>
                                </div>
                                <div class="contact-item">
                                    <Icon name="MapPin" size=16 />
                                    <span>{{ buyer.data.address }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- <div class="buyer-card-actions">
                            <button class="action-btn">
                                <Icon name="Star" size=16 />
                            </button>
                            <button class="action-btn">
                                <Icon name="MessageCircle" size=16 />
                            </button>
                            <button class="action-btn">
                                <Icon name="MoreVertical" size=16 />
                            </button>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Buyer Details Sidebar -->
            <div v-if="selectedBuyer" class="buyer-details-sidebar">
                
                <div class="buyer-details-header">
                    <h2 class="details-title">
                        <Icon name="MessageCircle" size=20 />
                        Buyer
                    </h2>
                    <button class="close-btn" @click="closeBuyerDetails">
                        <Icon name="X" size=20 />
                    </button>
                </div>

                <div class="buyer-details-content">
                    <div class="buyer-profile">
                        <div class="buyer-avatar-large">
                            <img v-if="selectedBuyer.data.avatar && selectedBuyer.data.avatar.startsWith('http')" :src="selectedBuyer.data.avatar" alt="Buyer Avatar">
                            <span v-else class="avatar-text-large">{{ selectedBuyer.data.avatar }}</span>
                            <!-- <div class="online-indicator"></div> -->
                        </div>
                        <h3 class="buyer-name-large">{{ selectedBuyer.data.name_of_participant }}</h3>
                        <p class="buyer-role-large">{{ selectedBuyer.data.job_title }}</p>
                    </div>

                    <div class="buyer-details-sections">
                        <div class="detail-section">
                            <h4>DESCRIPTION</h4>
                            <p>{{ selectedBuyer.data.description }}</p>
                            <a href="#" class="read-more">read more</a>
                        </div>

                        <div class="detail-section">
                            <h4>SITE</h4>
                            <p>{{ selectedBuyer.data.website }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>EMAIL</h4>
                            <p>{{ selectedBuyer.data.email }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>PHONE</h4>
                            <p>{{ selectedBuyer.data.mobile_no }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>LOCATION</h4>
                            <p>{{ selectedBuyer.data.address }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>BUYERS' PREFERRED WORKSHOP MEETINGS WITH:</h4>
                            <div class="tags-container">
                                <span v-for="workshop in selectedBuyer.data.preferred_workshop_meetings" :key="workshop" class="tag">
                                    {{ workshop }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h4>AREAS OF ACTIVITY:</h4>
                            <div class="tags-container">
                                <span v-for="region in selectedBuyer.data.areas_of_activity" :key="region" class="tag">
                                    {{ region }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="buyer-details-footer">
                    <button class="nav-btn">
                        <Icon name="X" size=20 />
                    </button>
                    <button class="nav-btn">
                        <Icon name="ChevronRight" size=20 />
                    </button>
                </div> -->
            </div>
        </div>
    </div> 
</template>  

<style scoped lang="scss">
.buyers-dashboard {
    padding: 24px;
    background-color: #f8fafc;
    min-height: 100vh;
}

.buyers-header {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.header-left {
    .buyers-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--tfhb-text-title-color, #141915);
        margin: 0 0 8px 0;
    }
    
    .buyers-subtitle {
        color: var(--tfhb-paragraph-color, #273F2B);
        margin: 0;
    }
}

.header-right {
    .total-buyers {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--tfhb-primary-color, #2E6B38);
        color: white !important;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
    }
}

// Filter Section Styles (from hosts.vue)
.tfhb-dashboard-heading {
    padding-top: 16px;
    border-top: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

.tfhb-header-filters {
    position: relative;
    
    input {
        padding: 12px 16px 12px 40px;
        border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
        border-radius: 8px;
        width: 300px;
        font-size: 14px;
        
        &:focus {
            outline: none;
            border-color: var(--tfhb-primary-color, #2E6B38);
        }
    }
    
    span {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.thb-admin-btn {
    .tfhb-btn {
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
        
        &.secondary-btn {
            background: var(--tfhb-surface-background-color, #EEF6F0);
            color: var(--tfhb-text-title-color, #141915);
            border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
            
            &:hover {
                background: var(--tfhb-surface-primary-color, #C0D8C4);
            }
        }
        
        &.boxed-btn {
            background: var(--tfhb-primary-color, #2E6B38);
            color: white;
            border: 1px solid var(--tfhb-primary-color, #2E6B38);
            
            &:hover {
                background: var(--tfhb-primary-hover-color, #4C9959);
            }
        }
    }
}

.buyers-content {
    display: flex;
    gap: 24px;
    transition: all 0.3s ease;
}

.buyers-list {
    flex: 1;
    transition: all 0.3s ease;
    
    &.with-details {
        flex: 0 0 60%;
    }
}

.buyers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.buyer-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    
    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    &.selected {
        border-color: var(--tfhb-primary-color, #2E6B38);
        background: var(--tfhb-surface-background-color, #EEF6F0);
    }
}

.buyer-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.buyer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--tfhb-primary-color, #2E6B38);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white !important;
    font-weight: 600;
    font-size: 18px;
    overflow: hidden;
    
    img {
        height: 100%;
        width: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .avatar-text {
        font-size: 18px;
        font-weight: 600;
    }
}

.buyer-info {
    flex: 1;
    
    .buyer-name {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .buyer-role {
        margin: 0;
        font-size: 14px;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.buyer-status {
    .status-badge {
        background: #E6FAEE;
        color: #17723F;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        
        &.warning {
            background: #FFF8E5;
            color: #A37800;
        }
    }
}

.buyer-card-content {
    margin-bottom: 16px;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--tfhb-paragraph-color, #273F2B);
}

.buyer-card-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.action-btn {
    background: none;
    border: none;
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
    color: var(--tfhb-paragraph-color, #273F2B);
    transition: all 0.2s;
    
    &:hover {
        background: var(--tfhb-surface-background-color, #EEF6F0);
        color: var(--tfhb-primary-color, #2E6B38);
    }
}

// Buyer Details Sidebar
.buyer-details-sidebar {
    flex: 0 0 40%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}

.buyer-details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 16px 24px;
    border-bottom: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
    
    .details-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .close-btn {
        background: none;
        border: none;
        padding: 8px;
        border-radius: 6px;
        cursor: pointer;
        color: var(--tfhb-paragraph-color, #273F2B);
        
        &:hover {
            background: var(--tfhb-surface-background-color, #EEF6F0);
        }
    }
}

.buyer-details-content {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
}

.buyer-profile {
    text-align: center;
    margin-bottom: 32px;
    
    .buyer-avatar-large {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--tfhb-primary-color, #2E6B38);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white !important;
        font-weight: 600;
        font-size: 24px;
        margin: 0 auto 16px auto;
        overflow: hidden;
        
        img {
            height: 100%;
            width: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .avatar-text-large {
            font-size: 24px;
            font-weight: 600;
        }
        
        .online-indicator {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid white !important;
        }
    }
    
    .buyer-name-large {
        margin: 0 0 8px 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .buyer-role-large {
        margin: 0;
        font-size: 16px;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.buyer-details-sections {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.detail-section {
    h4 {
        margin: 0 0 8px 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--tfhb-paragraph-color, #273F2B);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    p {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: var(--tfhb-text-title-color, #141915);
        line-height: 1.5;
    }
    
    .read-more {
        color: var(--tfhb-primary-color, #2E6B38);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        
        &:hover {
            text-decoration: underline;
        }
    }
}

.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    
    .tag {
        background: var(--tfhb-primary-color, #2E6B38);
        color: white !important;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        
        &:hover {
            background: var(--tfhb-primary-hover-color, #4C9959);
        }
    }
}

.buyer-details-footer {
    display: flex;
    justify-content: space-between;
    padding: 16px 24px;
    border-top: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
    
    .nav-btn {
        background: none;
        border: none;
        padding: 8px;
        border-radius: 6px;
        cursor: pointer;
        color: var(--tfhb-paragraph-color, #273F2B);
        
        &:hover {
            background: var(--tfhb-surface-background-color, #EEF6F0);
        }
    }
}

// Responsive Design
@media (max-width: 1200px) {
    .buyers-content {
        flex-direction: column;
    }
    
    .buyer-details-sidebar {
        flex: none;
        width: 100%;
    }
    
    .buyers-list.with-details {
        flex: none;
    }
}

@media (max-width: 768px) {
    .buyers-grid {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }
    
    .tfhb-header-filters input {
        width: 100%;
    }
}
</style>