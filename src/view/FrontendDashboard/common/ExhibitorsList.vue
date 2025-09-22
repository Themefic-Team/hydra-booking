<script setup> 
import { ref, onBeforeMount, defineProps } from 'vue';  
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import axios from 'axios';
import { useRouter } from 'vue-router';
const router = useRouter();
const selectedExhibitor  = ref(null);
const skeleton = ref(true);
const exhibitors = ref([]);
const filteredExhibitors = ref([]);
const searchQuery = ref('');
import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';

async function fetchExhibitors() {
    
    try {
        const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/addons/exhibitors-list', {
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce,
            },
            params: {
                current_user_id: tfhb_core_apps.user.id
            },
            withCredentials: true
        });
        if (response.data.success && response.data.data) {
            exhibitors.value = response.data.data;
            
            // Sort exhibitors alphabetically by company name (nome_e_cognome)
            exhibitors.value.sort((a, b) => {
                const companyNameA = (a.data?.nome_e_cognome || '').toLowerCase();
                const companyNameB = (b.data?.nome_e_cognome || '').toLowerCase();
                
                // Handle empty values by putting them at the end
                if (!companyNameA && !companyNameB) return 0;
                if (!companyNameA) return 1;
                if (!companyNameB) return -1;
                
                // Sort alphabetically
                return companyNameA.localeCompare(companyNameB);
            });
            
            filteredExhibitors.value = [...exhibitors.value];
        }
    } catch (e) {
        // handle error
    } finally {
        skeleton.value = false;
    }
}
onBeforeMount(async () => {
    await fetchExhibitors();
});

const selectExhibitor = (exhibitor) => {
    selectedExhibitor.value = exhibitor;
    // console.log(selectedExhibitor.value);
}

const closeExhibitorDetails = () => {
    selectedExhibitor.value = null
}

const Tfhb_Exhibitor_Filter = (event) => {
    const query = event.target.value.toLowerCase().trim();
    searchQuery.value = query;
    
    if (!query) {
        filteredExhibitors.value = [...exhibitors.value];
        return;
    }
    
    filteredExhibitors.value = exhibitors.value.filter(exhibitor => {
        // Search in name
        if (exhibitor.data.name && 
            exhibitor.data.name.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in job title
        if (exhibitor.data.job_title && 
            exhibitor.data.job_title.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in email
        if (exhibitor.data.email && 
            exhibitor.data.email.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in phone number
        if (exhibitor.data.telefono_diretto && 
            exhibitor.data.telefono_diretto.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in address/location
        if (exhibitor.data.location && 
            exhibitor.data.location.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in description
        if (exhibitor.data.description && 
            exhibitor.data.description.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in website
        if (exhibitor.data.sito_internet && 
            exhibitor.data.sito_internet.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in company name
        if (exhibitor.data['denominazione-operatore-azienda'] && 
            exhibitor.data['denominazione-operatore-azienda'].toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in alternative company name
        if (exhibitor.data['eventuale-altra-denominazione'] && 
            exhibitor.data['eventuale-altra-denominazione'].toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in VAT number
        if (exhibitor.data['pi_cf'] && 
            exhibitor.data['pi_cf'].toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in activity area
        if (exhibitor.data.ambito_di_attivita && 
            exhibitor.data.ambito_di_attivita.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in specialization
        if (exhibitor.data.specializzazione && 
            Array.isArray(exhibitor.data.specializzazione)) {
            const specializationMatch = exhibitor.data.specializzazione.some(spec => 
                spec.toLowerCase().includes(query)
            );
            if (specializationMatch) return true;
        }
        
        // Search in buyer interest origin
        if (exhibitor.data.provenienza_buyer_interesse && 
            Array.isArray(exhibitor.data.provenienza_buyer_interesse)) {
            const buyerMatch = exhibitor.data.provenienza_buyer_interesse.some(buyer => 
                buyer.toLowerCase().includes(query)
            );
            if (buyerMatch) return true;
        }
        
        // Search in region
        if (exhibitor.data.regione && 
            exhibitor.data.regione.toLowerCase().includes(query)) {
            return true;
        }
        
        return false;
    });
}

const redirectToChat = (buyerId) => { 
    AddonsAuth.chat_user_id = buyerId;
    router.push({ name: 'HydraAddonsMessages' });
}
</script>

<template> 

    <div class="exhibitors-dashboard">
        <!-- Header Section with Filters -->
        <div class="exhibitors-header">
           

            <!-- Filter Section (from hosts.vue) -->
            <div class="tfhb-dashboard-heading tfhb-flexbox tfhb-justify-between">
                <div class="tfhb-header-filters">
                    <input type="text" @keyup="Tfhb_Exhibitor_Filter" placeholder="Search by name, job title, email, phone, address..." /> 
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
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.exportExhibitors()"
                        :buttonText="$tfhb_trans('Export Badge')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : importExport.exportExhibitors()"
                        :buttonText="$tfhb_trans('Export')"
                        icon="FileDown"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />   
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click="$tfhb_is_pro == false || $tfhb_license_status == false ? ProPopup = true : router.push({ name: 'ExhibitorsImport' })"
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
                    <h1 class="exhibitors-title">Exhibitors</h1>
                    <p class="exhibitors-subtitle">Lorem ipsum dolor sit amet consectetur.</p>
                </div>
                
                <div class="header-right">
                    <div class="total-exhibitors">
                        <Icon name="Users" size=20 />
                        <span>Total Exhibitors <b>{{ filteredExhibitors.length }}</b></span>
                    </div>
                </div>
            </div>
        </div> 
        <!-- Main Content Area -->
        <div class="exhibitors-content">
            <!-- Exhibitors List -->
            <div class="exhibitors-list" :class="{ 'with-details': selectedExhibitor }">
                <div class="exhibitors-grid">
                    <div 
                        v-for="exhibitor in filteredExhibitors" 
                        :key="exhibitor.id" 
                        class="exhibitor-card"
                        :class="{ 'selected': selectedExhibitor && selectedExhibitor.id === exhibitor.id }"
                       
                    >
                    <!-- {{ exhibitor }} -->
                        <div class="exhibitor-card-header">
                            <div class="exhibitor-avatar">
                                <img v-if="exhibitor.data.avatar && exhibitor.data.avatar.startsWith('http')" :src="exhibitor.data.avatar" alt="Exhibitor Avatar">
                                <img 
                                    v-else
                                    :src="$tfhb_url+'/assets/images/avator.png'" 
                                    alt="Event Logo" 
                                    class="event-logo"
                                /> 
                            </div> 
                            <div class="exhibitor-info">
                                <h3 v-if="exhibitor.data.nome_e_cognome != ''" class="exhibitor-name">{{ exhibitor.data.nome_e_cognome  }}</h3>
                                <!-- <h3 v-else class="exhibitor-name">{{ selectedExhibitor.data.job_title }}</h3> -->
                                <!-- <p class="exhibitor-subtitle">{{ exhibitor.data.ambito_di_attività }}</p> -->
                            </div> 
                        </div>
                        
                        <div class="exhibitor-card-content">
                            <div class="contact-info">
                                <div class="contact-item">
                                    <Icon name="User" size=16 />
                                    <span>{{ exhibitor.data.name }}</span>
                                </div>
                                <div class="contact-item">
                                    <Icon name="Mail" size=16 />
                                    <span>{{ exhibitor.data.email }}</span>
                                </div>
                                <!-- <div class="contact-item">
                                    <Icon name="Phone" size=16 />
                                    <span>{{ exhibitor.data.telefono_diretto }}</span>
                                </div> -->
                                <div  v-if="exhibitor.data.location" 
                                class="contact-item">
                                    <Icon name="MapPin" size=16 />
                                    <span>{{ exhibitor.data.location }}</span>
                                </div>
                                <div v-else 
                                class="contact-item">
                                    <Icon name="MapPin" size=16 />
                                    <span>{{ exhibitor.data.sede_legale_dell_attivit }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="exhibitor-card-actions">
                            <button class="action-btn"  @click="selectExhibitor(exhibitor)">
                                <Icon name="Eye" size=16 />
                            </button>
                            <button class="action-btn" @click="redirectToChat(exhibitor.id)">
                                <Icon name="MessageCircle" size=16 />
                            </button>
                            <!-- <button class="action-btn">
                                <Icon name="MoreVertical" size=16 />
                            </button> -->
                            <a :href="'#/exhibitors-list/profile/'+exhibitor.id " class="action-btn" style="font-size: 15px;">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exhibitor Details Sidebar -->
            <div v-if="selectedExhibitor" class="exhibitor-details-sidebar">
                <div class="exhibitor-details-header">
                    <h2 class="details-title">
                        <!-- <Icon name="MessageCircle" size=20 /> -->
                        Exhibitors
                    </h2>
                    <div class="header-actions">
                        <!-- <span class="match-percentage-large">{{ selectedExhibitor.data.matchPercentage }}% Mach</span>s -->
                        <button class="action-btn" @click="redirectToChat(selectedExhibitor.id)">
                            <Icon name="MessageCircle" size=16 />
                        </button>
                        <!-- <button class="action-btn">
                            <Icon name="MoreVertical" size=16 />
                        </button> -->
                        <a :href="'#/exhibitors-list/profile/'+selectedExhibitor.id " class="action-btn" style="font-size: 15px;">
                            View
                        </a>
                        <button class="close-btn" @click="closeExhibitorDetails">
                            <Icon name="X" size=20 />
                        </button>
                    </div>
                </div>

                <div class="exhibitor-details-content">
                    <div class="exhibitor-profile">
                        <div class="exhibitor-avatar-large">
                            <img v-if="selectedExhibitor.data.avatar && selectedExhibitor.data.avatar.startsWith('http')" :src="selectedExhibitor.data.avatar" alt="Exhibitor Avatar">
                            <img 
                                v-else
                                :src="$tfhb_url+'/assets/images/avator.png'" 
                                alt="Event Logo" 
                                class="event-logo"
                            /> 
                            <!-- <div class="online-indicator"></div> -->
                        </div>
                        <!-- {{ selectedExhibitor }} -->
                        <h3 v-if="selectedExhibitor.data.nome_e_cognome" class="exhibitor-name-large">{{ selectedExhibitor.data.nome_e_cognome }}</h3>
                        <h3 v-else class="exhibitor-name-large">{{ selectedExhibitor.data.job_title }}</h3>
                        <p  class="exhibitor-subtitle-large">{{ selectedExhibitor.data.sede_legale_dell_attivit }}</p>
                    </div>

                    <div class="exhibitor-details-sections">
                        <div class="detail-section">
                            <h4>DESCRIPTION</h4>
                            <p>{{ selectedExhibitor.data.description }}</p>
                            <!-- <a href="#" class="read-more">read more</a> -->
                        </div>

                        <div v-if="selectedExhibitor.data.sito_internet_aziendale != ''" class="detail-section">
                            <h4>SITE</h4>
                            <p>{{ selectedExhibitor.data.sito_internet_aziendale }}</p>
                        </div>

                        <div class="detail-section">
                            <h4>EMAIL</h4>
                            <p>{{ selectedExhibitor.data.email }}</p>
                        </div>

                        <div v-if="selectedExhibitor.data.telefono_diretto"  class="detail-section">
                            <h4>PHONE</h4>
                            <p>{{ selectedExhibitor.data.telefono_diretto }}</p>
                        </div>

                        <div v-if="selectedExhibitor.data.location" class="detail-section">
                            <h4>LOCATION</h4>
                            <p>{{ selectedExhibitor.data.location }}</p>
                        </div>
 
                    </div>
                </div>

                <!-- <div class="exhibitor-details-footer">
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
.exhibitors-dashboard {
    padding: 24px;
    background-color: #fff;
    min-height: 100vh;
}

.exhibitors-header {
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
    .exhibitors-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--tfhb-text-title-color, #141915);
        margin: 0 0 8px 0;
    }
    
    .exhibitors-subtitle {
        color: var(--tfhb-paragraph-color, #273F2B);
        margin: 0;
    }
}

.header-right {
    .total-exhibitors {
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

.exhibitors-content {
    display: flex;
    gap: 24px;
    transition: all 0.3s ease;
}

.exhibitors-list {
    flex: 1;
    transition: all 0.3s ease;
    
    &.with-details {
        flex: 0 0 60%;
    }
}

.exhibitors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.exhibitor-card {
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

.exhibitor-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.exhibitor-avatar {
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

.exhibitor-info {
    flex: 1;
    
    .exhibitor-name {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .exhibitor-subtitle {
        margin: 0;
        font-size: 14px;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.exhibitor-status {
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

.exhibitor-card-content {
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

.exhibitor-card-actions {
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

// Exhibitor Details Sidebar
.exhibitor-details-sidebar {
    flex: 0 0 40%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 24px;
    max-height: calc(100vh - 48px);
    overflow: hidden;
}

.exhibitor-details-header {
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

.exhibitor-details-content {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    min-height: 0; /* Allows flex item to shrink below content size */
}

.exhibitor-profile {
    text-align: center;
    margin-bottom: 32px;
    
    .exhibitor-avatar-large {
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
    
    .exhibitor-name-large {
        margin: 0 0 8px 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--tfhb-text-title-color, #141915);
    }
    
    .exhibitor-subtitle-large {
        margin: 0;
        font-size: 16px;
        color: var(--tfhb-paragraph-color, #273F2B);
    }
}

.exhibitor-details-sections {
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

.exhibitor-details-footer {
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
    .exhibitors-content {
        flex-direction: column;
    }
    
    .exhibitor-details-sidebar {
        flex: none;
        width: 100%;
        position: static; /* Remove sticky behavior on mobile */
        max-height: none;
    }
    
    .exhibitors-list.with-details {
        flex: none;
    }
}

@media (max-width: 768px) {
    .exhibitors-grid {
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