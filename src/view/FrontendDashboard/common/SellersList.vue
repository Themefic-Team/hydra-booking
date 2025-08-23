<script setup> 
import { ref, onBeforeMount, defineProps } from 'vue';  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import axios from 'axios';
const selectedSeller  = ref(null);
const skeleton = ref(true);
import { useRouter } from 'vue-router';
const router = useRouter();
import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
// const sellers = ref([
//     {
//         id: 1,
//         name: 'La Dimora dei Cavalieri',
//         subtitle: 'subtitle / brand payoff',
//         email: 'davide@google.com',
//         phone: '339 050403',
//         location: 'Perugia, Italia',
//         avatar: 'LD',
//         status: 'Active',
//         matchPercentage: 40,
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.ladimora.com',
//         staff: ['Staff 1', 'Staff 2', 'Staff 3', 'Staff 4'],
//         preferredWorkshops: ['Alberghi diffusi - 3* hotels and B&B'],
//         regionsOfInterest: ['Argentina and Latin America']
//     },
//     {
//         id: 2,
//         name: 'Joe Agency',
//         subtitle: 'Travel agency in Word',
//         email: 'customer@email.com',
//         phone: '+01 917 055-0403',
//         location: 'New York, NY 10169',
//         avatar: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face',
//         status: 'Active',
//         matchPercentage: 20,
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.joeagency.com',
//         staff: ['Staff 1', 'Staff 2', 'Staff 3', 'Staff 4', 'Staff 5'],
//         preferredWorkshops: ['Alberghi diffusi - 3* hotels and B&B'],
//         regionsOfInterest: ['Argentina and Latin America', 'Australia', 'Brazil', 'Canada', 'Europe', 'USA']
//     },
//     {
//         id: 3,
//         name: 'Name Brand',
//         subtitle: 'subtitle / brand payoff',
//         email: 'marco@agency.com',
//         phone: '+39 123 456 789',
//         location: 'Roma, Italia',
//         avatar: 'NB',
//         status: 'Active',
//         matchPercentage: 35,
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.namebrand.com',
//         staff: ['Staff 1', 'Staff 2'],
//         preferredWorkshops: ['Alberghi diffusi - 3* hotels and B&B'],
//         regionsOfInterest: ['Europe']
//     },
//     {
//         id: 4,
//         name: 'C4 Agency',
//         subtitle: 'Travel agency in Word',
//         email: 'sarah@travel.com',
//         phone: '+44 20 7946 0958',
//         location: 'London, UK',
//         avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
//         status: 'Active',
//         matchPercentage: 60,
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.c4agency.com',
//         staff: ['Staff 1', 'Staff 2', 'Staff 3'],
//         preferredWorkshops: ['Alberghi diffusi - 3* hotels and B&B'],
//         regionsOfInterest: ['North America']
//     },
//     {
//         id: 5,
//         name: 'HB Agency',
//         subtitle: 'subtitle / brand payoff',
//         email: 'carlos@tourism.com',
//         phone: '+34 91 123 4567',
//         location: 'Madrid, España',
//         avatar: 'HB',
//         status: 'Active',
//         matchPercentage: 25,
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.hbagency.com',
//         staff: ['Staff 1', 'Staff 2', 'Staff 3', 'Staff 4'],
//         preferredWorkshops: ['Alberghi diffusi - 3* hotels and B&B'],
//         regionsOfInterest: ['Latin America']
//     },
//     {
//         id: 6,
//         name: 'EL Agency',
//         subtitle: 'Travel agency in Word',
//         email: 'lisa@operations.com',
//         phone: '+1 555 123 4567',
//         location: 'San Francisco, CA',
//         avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop&crop=face',
//         status: 'Active',
//         matchPercentage: 45,
//         description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
//         website: 'www.elagency.com',
//         staff: ['Staff 1', 'Staff 2', 'Staff 3'],
//         preferredWorkshops: ['Alberghi diffusi - 3* hotels and B&B'],
//         regionsOfInterest: ['Asia Pacific']
//     }
// ])


const sellers = ref([]);
const filteredSellers = ref([]);
const searchQuery = ref('');

async function fetchSellers() {
    
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/sellers-list', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            },
            params: {
                current_user_id: tfhb_core_apps.user.id
            },
            withCredentials: true
        });
        if (response.data.success && response.data.data) {
            sellers.value = response.data.data;
            filteredSellers.value = response.data.data;
        }
    } catch (e) {
        // handle error
    } finally {
        skeleton.value = false;
    }
}

onBeforeMount(async () => {
    await fetchSellers();
});

const selectSeller = (seller) => {
    selectedSeller.value = seller
}

const closeSellerDetails = () => {
    selectedSeller.value = null
}

const Tfhb_Seller_Filter = (event) => {
    const query = event.target.value.toLowerCase().trim();
    searchQuery.value = query;
    
    if (!query) {
        filteredSellers.value = sellers.value;
        return;
    }
    
    filteredSellers.value = sellers.value.filter(seller => {
        // Search in name
        if (seller.data.name && 
            seller.data.name.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in job title
        if (seller.data.job_title && 
            seller.data.job_title.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in email
        if (seller.data.email && 
            seller.data.email.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in phone number
        if (seller.data.telefono_diretto && 
            seller.data.telefono_diretto.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in address/location
        if (seller.data.location && 
            seller.data.location.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in description
        if (seller.data.description && 
            seller.data.description.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in website
        if (seller.data.sito_internet && 
            seller.data.sito_internet.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in company name
        if (seller.data['denominazione-operatore-azienda'] && 
            seller.data['denominazione-operatore-azienda'].toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in alternative company name
        if (seller.data['eventuale-altra-denominazione'] && 
            seller.data['eventuale-altra-denominazione'].toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in VAT number
        if (seller.data['pi_cf'] && 
            seller.data['pi_cf'].toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in activity area
        if (seller.data.ambito_di_attivita && 
            seller.data.ambito_di_attivita.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in specialization
        if (seller.data.specializzazione && 
            Array.isArray(seller.data.specializzazione)) {
            const specializationMatch = seller.data.specializzazione.some(spec => 
                spec.toLowerCase().includes(query)
            );
            if (specializationMatch) return true;
        }
        
        // Search in buyer interest origin
        if (seller.data.provenienza_buyer_interesse && 
            Array.isArray(seller.data.provenienza_buyer_interesse)) {
            const buyerMatch = seller.data.provenienza_buyer_interesse.some(buyer => 
                buyer.toLowerCase().includes(query)
            );
            if (buyerMatch) return true;
        }
        
        // Search in region
        if (seller.data.regione && 
            seller.data.regione.toLowerCase().includes(query)) {
            return true;
        }
        
        return false;
    });
}
const redirectToChat = (user_id) => { 
    AddonsAuth.chat_user_id = user_id;
    router.push({ name: 'HydraAddonsMessages' });
}
</script>

<template> 

    <div class="sellers-dashboard">
        <!-- Header Section with Filters -->
        <div class="sellers-header">
           

            <!-- Filter Section (from hosts.vue) -->
            <div class="tfhb-dashboard-heading tfhb-flexbox tfhb-justify-between">
                <div class="tfhb-header-filters">
                    <input type="text" @keyup="Tfhb_Seller_Filter" placeholder="Search by name, job title, email, phone, address..." /> 
                    <span><Icon name="Search" size=20 /></span>
                </div>
                 <!-- <HbButton 
                    classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8"  
                    :buttonText="$tfhb_trans('Filter')"
                    icon="SlidersHorizontal"   
                    :hover_animation="false" 
                    icon_position = 'left'
                />  -->
                <!-- <div class="thb-admin-btn right tfhb-flexbox tfhb-gap-16"> 
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.exportSellers()"
                        :buttonText="$tfhb_trans('Export Badge')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.exportSellers()"
                        :buttonText="$tfhb_trans('Export')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : router.push({ name: 'SellersImport' })"
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
                </div>   -->
            </div>


            <div class="header-content">
                <div class="header-left">
                    <h1 class="sellers-title">Sellers</h1>
                    <p class="sellers-subtitle">Lorem ipsum dolor sit amet consectetur.</p>
                </div>
                
                <div class="header-right">
                    <div class="total-sellers">
                        <Icon name="Users" size=20 />
                        <span>Total Sellers <b>{{ filteredSellers.length }}</b></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="sellers-content">
            <!-- Sellers List -->
            <div class="sellers-list" :class="{ 'with-details': selectedSeller }">
                <div class="sellers-grid">
                    <div 
                        v-for="seller in filteredSellers" 
                        :key="seller.id" 
                        class="seller-card"
                        :class="{ 'selected': selectedSeller && selectedSeller.id === seller.id }" 
                    >
                    <!-- {{ seller }} -->
                        <div class="seller-card-header">
                            <div class="seller-avatar">
                                <img v-if="seller.data.avatar && seller.data.avatar.startsWith('http')" :src="seller.data.avatar" alt="Seller Avatar">
                                <img 
                                    v-else
                                    :src="$tfhb_url+'/assets/images/avator.png'" 
                                    alt="Event Logo" 
                                    class="event-logo"
                                /> 
                            </div>
                            <div class="seller-info">
                                <h3 class="seller-name">{{ seller.data.name }}</h3>
                                <p class="seller-subtitle">{{ seller.data.ambito_di_attività }}</p>
                            </div> 
                        </div>
                        
                        <div class="seller-card-content">
                            <div class="contact-info">
                                <div class="contact-item">
                                    <Icon name="Mail" size=16 />
                                    <span>{{ seller.data.email }}</span>
                                </div>
                                <div class="contact-item">
                                    <Icon name="Phone" size=16 />
                                    <span>{{ seller.data.telefono_diretto }}</span>
                                </div>
                                <div  v-if="seller.data.location" 
                                class="contact-item">
                                    <Icon name="MapPin" size=16 />
                                    <span>{{ seller.data.location }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="seller-card-actions">
                            <button class="action-btn"  @click="selectSeller(seller)">
                                <Icon name="Eye" size=16 />
                            </button>
                            <button class="action-btn" @click="redirectToChat(seller.id)">
                                <Icon name="MessageCircle" size=16 />
                            </button>
                            <!-- <button class="action-btn">
                                <Icon name="MoreVertical" size=16 />
                            </button> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seller Details Sidebar -->
            <div v-if="selectedSeller" class="seller-details-sidebar">
                <div class="seller-details-header">
                    <h2 class="details-title">
                        <Icon name="MessageCircle" size=20 />
                        Sellers
                    </h2>
                    <div class="header-actions">
                        <!-- <span class="match-percentage-large">{{ selectedSeller.data.matchPercentage }}% Mach</span>s -->
                        <button class="close-btn" @click="closeSellerDetails">
                            <Icon name="X" size=20 />
                        </button>
                    </div>
                </div>

                <div class="seller-details-content">
                    <div class="seller-profile">
                        <div class="seller-avatar-large">
                            <img v-if="selectedSeller.data.avatar && selectedSeller.data.avatar.startsWith('http')" :src="selectedSeller.data.avatar" alt="Seller Avatar">
                            <img 
                                v-else
                                :src="$tfhb_url+'/assets/images/avator.png'" 
                                alt="Event Logo" 
                                class="event-logo"
                            /> 
                            <!-- <div class="online-indicator"></div> -->
                        </div>
                        <h3 class="seller-name-large">{{ selectedSeller.data.name }}</h3>
                        <p class="seller-subtitle-large">{{ selectedSeller.data.job_title }}</p>
                    </div>

                    <div class="seller-details-sections">
                        <div class="detail-section">
                            <h4>DESCRIPTION</h4>
                            <p>{{ selectedSeller.data.description }}</p>
                            <!-- <a href="#" class="read-more">read more</a> -->
                        </div>

                        <div class="detail-section">
                            <h4>SITE</h4>
                            <p>{{ selectedSeller.data.sito_internet }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>EMAIL</h4>
                            <p>{{ selectedSeller.data.email }}</p>
                        </div>

                        <div v-if="selectedSeller.data.location"  class="detail-section">
                            <h4>PHONE</h4>
                            <p>{{ selectedSeller.data.telefono_diretto }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>LOCATION</h4>
                            <p>{{ selectedSeller.data.location }}</p>
                        </div>

                        <!-- <div class="detail-section">
                            <h4>STAFF</h4>
                            <div class="staff-container">
                                <div v-for="(staff, index) in selectedSeller.staff.slice(0, 4)" :key="index" class="staff-avatar">
                                    <span class="staff-initial">{{ staff.charAt(0) }}</span>
                                </div>
                                <div v-if="selectedSeller.staff.length > 4" class="staff-more">
                                    +{{ selectedSeller.staff.length - 4 }}
                                </div>
                            </div>
                        </div> -->

                        <div class="detail-section">
                            <h4>aree di provenienza Buyer di interesse</h4>
                            <div class="tags-container">
                                <span v-for="workshop in selectedSeller.data.provenienza_Buyer_interesse" :key="workshop" class="tag">
                                    {{ workshop }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h4> Specializzazione:</h4>
                            <div class="tags-container">
                                <span v-for="region in selectedSeller.data.specializzazione" :key="region" class="tag">
                                    {{ region }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="seller-details-footer">
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
.sellers-dashboard {
    padding: 24px;
    background-color: #fff;
    min-height: 100vh;
}

.sellers-header {
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
    margin-top: 24px;
}


.header-left {
    .sellers-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--tfhb-text-title-color, #141915);
        margin: 0 0 8px 0;
    }
    
    .sellers-subtitle {
        color: var(--tfhb-paragraph-color, #273F2B);
        margin: 0;
    }
}

.header-right {
    .total-sellers {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #F3EEF5; 
        padding: 12px 20px;
        border-radius: 8px;  
    }
}

// Filter Section Styles (from hosts.vue)
.tfhb-dashboard-heading {
    padding-bottom: 16px;
    border-bottom: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
    margin: 0 !important;
    border-radius: 0 !important;
    
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
        position: absolute !important;
        right: 10px;
        top: 9px;
        margin: 0 !important;
        color: var(--tfhb-admin-secondary-default, #273F2B) !important;
        width: 20px;
        display: inline-block;
        left: auto;
        top: 20px;
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

.sellers-content {
    display: flex;
    gap: 24px;
    transition: all 0.3s ease;
}

.sellers-list {
    flex: 1;
    transition: all 0.3s ease;
    
    &.with-details {
        flex: 0 0 60%;
    }
}

.sellers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.seller-card {
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

.seller-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.seller-avatar {
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

.seller-info {
    flex: 1;
    
    .seller-name {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .seller-subtitle {
        margin: 0;
        font-size: 14px;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.seller-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    
    .match-percentage {
        font-size: 12px;
        font-weight: 600;
        color: var(--tfhb-primary-color, #2E6B38);
    }
    
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

.seller-card-content {
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

.seller-card-actions {
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

// Seller Details Sidebar
.seller-details-sidebar {
    flex: 0 0 40%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}

.seller-details-header {
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
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        
        .match-percentage-large {
            font-size: 14px;
            font-weight: 600;
            color: var(--tfhb-primary-color, #2E6B38);
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
}

.seller-details-content {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
}

.seller-profile {
    text-align: center;
    margin-bottom: 32px;
    
    .seller-avatar-large {
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
    
    .seller-name-large {
        margin: 0 0 8px 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .seller-subtitle-large {
        margin: 0;
        font-size: 16px;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.seller-details-sections {
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

.staff-container {
    display: flex;
    align-items: center;
    gap: 8px;
    
    .staff-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--tfhb-primary-color, #2E6B38);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white !important;
        font-size: 12px;
        font-weight: 600;
    }
    
    .staff-more {
        font-size: 12px;
        font-weight: 600;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    
    .tag {
        background: #D6E0E8;
        color: #000;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        
        &:hover {
            color: #fff !important;
            background: var(--tfhb-primary-hover-color, #4C9959);
        }
    }
}

.seller-details-footer {
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
    .sellers-content {
        flex-direction: column;
    }
    
    .seller-details-sidebar {
        flex: none;
        width: 100%;
    }
    
    .sellers-list.with-details {
        flex: none;
    }
}

@media (max-width: 768px) {
    .sellers-grid {
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