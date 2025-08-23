
<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
const route = useRoute()
const eventDetails = ref({})
const skeleton = ref(true)
const activeTab = ref('Home')

const tabs = ['Home', 'About', 'Staff', 'Gallery', 'Video', 'Documents', 'Links']

// Computed properties to check if tab content is empty
const hasAboutContent = computed(() => {
  const description = AddonsAuth.loggedInUser?.user_data?.description
  return description && description.trim() !== ''
})

const hasStaffContent = computed(() => userStaff.value.length > 0)

const hasGalleryContent = computed(() => userGallery.value.length > 0)

const hasVideoContent = computed(() => {
  const video = userVideo.value
  return video && video.url && video.url.trim() !== ''
})

const hasDocumentsContent = computed(() => userDocuments.value.length > 0)

const hasLinksContent = computed(() => userLinks.value.length > 0)

// Additional computed properties to check if sidebar sections have content
const hasContactInfo = computed(() => {
  const userData = AddonsAuth.loggedInUser?.user_data
  return (
    (userData?.company_website && userData.company_website.trim() !== '') ||
    (userData?.email && userData.email.trim() !== '') ||
    (userData?.mobile_no && userData.mobile_no.trim() !== '') ||
    (userData?.address && userData.address.trim() !== '') ||
    Object.values(userSocialShare.value).some(link => link && link.trim() !== '')
  )
})

const hasBuyerInfo = computed(() => {
  const userData = AddonsAuth.loggedInUser?.user_data
  return (
    (userData?.areas_of_activity && userData.areas_of_activity.length > 0) ||
    (userData?.nation && userData.nation.length > 0)
  )
})

const hasBuyerInterests = computed(() => {
  const userData = AddonsAuth.loggedInUser?.user_data
  return userData?.preferred_workshop_meetings && userData.preferred_workshop_meetings.length > 0
})

// Filtered tabs array that only includes tabs with content
const visibleTabs = computed(() => {
  const tabVisibilityMap = {
    'Home': true, // Home tab always shows as it contains summary of all content
    'About': hasAboutContent.value,
    'Staff': hasStaffContent.value,
    'Gallery': hasGalleryContent.value,
    'Video': hasVideoContent.value,
    'Documents': hasDocumentsContent.value,
    'Links': hasLinksContent.value
  }
  
  return tabs.filter(tab => tabVisibilityMap[tab])
})

// Ensure activeTab is valid when tabs change
const validActiveTab = computed(() => {
  if (visibleTabs.value.includes(activeTab.value)) {
    return activeTab.value
  }
  return visibleTabs.value[0] || 'Home'
})

// Dummy data based on the image structure
 
// Computed properties to get user data
const userData = computed(() => AddonsAuth.loggedInUser?.user_data || {})
const userStaff = computed(() => userData.value.staff || [])
const userGallery = computed(() => userData.value.gallery || [])
const userVideo = computed(() => userData.value.video || { title: '', description: '', url: '' })
const userDocuments = computed(() => userData.value.documents || [])
const userLinks = computed(() => userData.value.links || [])
const userSocialShare = computed(() => userData.value.social_share || {})

// Lightbox state for gallery popup
const isGalleryPopupOpen = ref(false)
const popupImageSrc = ref('')

function openGalleryPopup(img) {
  // Handle both string URLs and object with url property
  popupImageSrc.value = typeof img === 'string' ? img : img.url
  isGalleryPopupOpen.value = true
}
function closeGalleryPopup() {
  isGalleryPopupOpen.value = false
  popupImageSrc.value = ''
}

// const fetchEventDetails = async () => {
//   // If route.params.id is not available, show the event id from AddonsAuth.event
//   let event_id = 0
//   if (!route.params.id) {
//     event_id = AddonsAuth.event?.id || 0
//   } else{
//     event_id = route.params.id
//   }
//   try {
//     skeleton.value = true
//     const response = await axios.get(`/wp-json/hydra-booking/v1/addons/sellers/event-details/${event_id}`, {
//       headers: {
//         'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
//       }
//     })
    
//     if (response.data.status) {
//       eventDetails.value = response.data.event_details || {}
//     }
//   } catch (error) {
//     console.error('Error fetching event details:', error)
//   } finally {
//     skeleton.value = false
//   }
// }

// onMounted(() => {
//   fetchEventDetails()
// })
</script>

<template>  
  <div class="profile-container">
    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Company Banner -->
      <div class="company-banner-container">
        <img 
          :src="AddonsAuth.loggedInUser?.user_data?.cover_image || $tfhb_url + '/assets/app/images/meeting-cover.png'" 
          alt="Company Banner" 
          class="company-banner"
        />
        
        <!-- Company Logo Overlay -->
        <div class="company-logo-overlay">
          <img 
            :src="AddonsAuth.loggedInUser?.user_data?.companey_logo || $tfhb_url+'/assets/images/avator.png'"
            alt="Company Logo" 
            class="company-logo"
          />
        </div>
      </div>

      <!-- Company Title and Type -->
      <div class="company-header">
        <div class="company-title-section">
          <h1 class="company-title">{{ AddonsAuth.loggedInUser?.user_data?.name_of_participant || 'User' }}</h1>
          <span class="company-type">{{ AddonsAuth.loggedInUser?.user_role || 'User' }}</span>
          <span class="star-icon">⭐</span>
        </div>
        <p class="company-subtitle">{{ AddonsAuth.loggedInUser?.user_data?.job_title || '' }}</p>
      </div>

      <!-- Navigation Tabs -->
      <div class="company-tabs">
        <button 
          v-for="tab in visibleTabs" 
          :key="tab" 
          :class="['tab-button', { active: validActiveTab === tab }]"
          @click="activeTab = tab"
        >
          {{ tab }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Home Tab -->
        <div v-if="validActiveTab === 'Home'" class="home-content">
          <div class="content-card" v-if="hasAboutContent">
            <h2>About</h2>
            <p>{{ AddonsAuth.loggedInUser?.user_data?.description }}</p>
          </div>

          <div class="content-card" v-if="hasStaffContent">
            <h2>Staff</h2>
            <div class="staff-list">
              <div v-for="(member, index) in userStaff" :key="index" class="staff-item">
                <img :src="member.image" :alt="member.name" class="staff-image" />
                <div class="staff-info">
                  <h3>{{ member.name }}</h3>
                  <p>{{ member.position }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="content-card" v-if="hasGalleryContent">
            <h2>Gallery</h2>
            <div class="gallery-grid">
              <div v-for="(img, index) in userGallery" :key="index" class="gallery-item">
                <img :src="img.url" :alt="img.title" @click="openGalleryPopup(img)" style="cursor:pointer;" />
              </div>
            </div>
          </div>

          <div class="content-card" v-if="hasVideoContent">
            <h2>Video</h2>
            <p class="video-title">{{ userVideo.title }}</p>
            <p class="video-description">{{ userVideo.description }}</p>
            <div class="video-container">
              <iframe 
                :src="userVideo.url" 
                frameborder="0" 
                allowfullscreen
                class="video-iframe"
              ></iframe>
            </div>
          </div>

          <div class="content-card" v-if="hasDocumentsContent">
            <h2>Documents</h2>
            <div class="documents-list">
              <div v-for="(doc, index) in userDocuments" :key="index" class="document-item">
                <div class="document-icon">
                  <img :src="doc.icon || 'https://via.placeholder.com/40x40/2E6B38/FFFFFF?text=DOC'" alt="Document Icon" />
                </div>
                <div class="document-content">
                  <h3>{{ doc.title }}</h3>
                  <p>{{ doc.subtitle }}</p>
                  <span class="document-size">{{ doc.size }}</span>
                </div>
                <a v-if="doc.url" :href="doc.url" target="_blank" class="document-download">
                  <span>📥</span>
                </a>
              </div>
            </div>
          </div>

          <div class="content-card" v-if="hasLinksContent">
            <h2>Links</h2>
            <div class="links-list">
              <div v-for="(link, index) in userLinks" :key="index" class="link-item">
                <span class="link-icon">🌐</span>
                <a :href="link.url" target="_blank">{{ link.title }}</a>
              </div>
            </div>
          </div>
        </div>

        <!-- About Tab -->
        <div v-if="validActiveTab === 'About'" class="content-card">
          <h2>About</h2>
          <p>{{ AddonsAuth.loggedInUser.user_data.description }}</p>
        </div>

        <!-- Staff Tab -->
        <div v-if="validActiveTab === 'Staff'" class="content-card">
          <h2>Staff</h2>
          <div class="staff-list" v-if="userStaff.length > 0">
            <div v-for="(member, index) in userStaff" :key="index" class="staff-item">
              <img :src="member.image" :alt="member.name" class="staff-image" />
              <div class="staff-info">
                <h3>{{ member.name }}</h3>
                <p>{{ member.position }}</p>
              </div>
            </div>
          </div>
          <p v-else class="no-data-message">No staff members added yet.</p>
        </div>

        <!-- Gallery Tab -->
        <div v-if="validActiveTab === 'Gallery'" class="content-card">
          <h2>Gallery</h2>
          <div class="gallery-grid" v-if="userGallery.length > 0">
            <div v-for="(img, index) in userGallery" :key="index" class="gallery-item">
              <img :src="img.url" :alt="img.title" @click="openGalleryPopup(img)" style="cursor:pointer;" />
            </div>
          </div>
          <p v-else class="no-data-message">No gallery images added yet.</p>
        </div>

        <!-- Video Tab -->
        <div v-if="validActiveTab === 'Video' && userVideo.url" class="content-card">
          <h2>Video</h2>
          <p class="video-title">{{ userVideo.title }}</p>
          <p class="video-description">{{ userVideo.description }}</p>
          <div class="video-container">
            <iframe 
              :src="userVideo.url" 
              frameborder="0" 
              allowfullscreen
              class="video-iframe"
            ></iframe>
          </div>
        </div>

        <div v-if="validActiveTab === 'Video' && !userVideo.url" class="content-card">
          <h2>Video</h2>
          <p class="no-video-message">No video content available yet.</p>
        </div>

        <!-- Documents Tab -->
        <div v-if="validActiveTab === 'Documents'" class="content-card">
          <h2>Documents</h2>
          <div class="documents-list" v-if="userDocuments.length > 0">
            <div v-for="(doc, index) in userDocuments" :key="index" class="document-item">
              <div class="document-icon">
                <img :src="doc.icon || 'https://via.placeholder.com/40x40/2E6B38/FFFFFF?text=DOC'" alt="Document Icon" />
              </div>
              <div class="document-content">
                <h3>{{ doc.title }}</h3>
                <p>{{ doc.subtitle }}</p>
                <span class="document-size">{{ doc.size }}</span>
              </div>
              <a v-if="doc.url" :href="doc.url" target="_blank" class="document-download">
                <span>📥</span>
              </a>
            </div>
          </div>
          <p v-else class="no-data-message">No documents added yet.</p>
        </div>

        <!-- Links Tab -->
        <div v-if="validActiveTab === 'Links'" class="content-card">
          <h2>Links</h2>
          <div class="links-list" v-if="userLinks.length > 0">
            <div v-for="(link, index) in userLinks" :key="index" class="link-item">
              <span class="link-icon">🌐</span>
              <a :href="link.url" target="_blank">{{ link.title }}</a>
            </div>
          </div>
          <p v-else class="no-data-message">No links added yet.</p>
        </div>
      </div>
    </div>

    <!-- Right Sidebar - Contact Information -->
    <div class="sidebar-right" v-if="hasContactInfo || hasBuyerInfo || hasBuyerInterests">
      <div class="contact-card" v-if="hasContactInfo">
        <h3>Contact information</h3>
        
        <div class="contact-section">
          <div class="contact-item" v-if="AddonsAuth.loggedInUser.user_data.company_website">
            <span class="contact-label">SITE</span>
            <a :href="`https://${AddonsAuth.loggedInUser.user_data.company_website}`" target="_blank">
              {{ AddonsAuth.loggedInUser.user_data.company_website }}
            </a>
          </div>
          
          <div class="contact-item" v-if="AddonsAuth.loggedInUser.user_data.email">
            <span class="contact-label">EMAIL</span>
            <a :href="`mailto:${AddonsAuth.loggedInUser.user_data.email}`">
              {{ AddonsAuth.loggedInUser.user_data.email }}
            </a>
          </div>
          
          <div class="contact-item" v-if="AddonsAuth.loggedInUser.user_data.mobile_no">
            <span class="contact-label">PHONE</span>
            <div class="phone-numbers">
              <div>{{ AddonsAuth.loggedInUser.user_data.mobile_no }}</div>
            </div>
          </div>
          
          <div class="contact-item" v-if="AddonsAuth.loggedInUser.user_data.address">
            <span class="contact-label">LOCATION</span>
            <div>{{ AddonsAuth.loggedInUser.user_data.address }}</div>
          </div>
        </div>

        <div class="social-section" v-if="Object.values(userSocialShare).some(link => link && link.trim() !== '')">
          <h4>SOCIAL</h4>
          <div class="social-links">
            <a v-if="userSocialShare.instagram" :href="userSocialShare.instagram" target="_blank" class="social-link">
              <span class="social-icon">📷</span>
              <span>Instagram</span>
            </a>
            <a v-if="userSocialShare.facebook" :href="userSocialShare.facebook" target="_blank" class="social-link">
              <span class="social-icon">📘</span>
              <span>Facebook</span>
            </a>
            <a v-if="userSocialShare.youtube" :href="userSocialShare.youtube" target="_blank" class="social-link">
              <span class="social-icon">📺</span>
              <span>YouTube</span>
            </a>
            <a v-if="userSocialShare.linkedin" :href="userSocialShare.linkedin" target="_blank" class="social-link">
              <span class="social-icon">💼</span>
              <span>LinkedIn</span>
            </a>
          </div>
        </div>
      </div>

      <div class="buyer-info-card" v-if="hasBuyerInfo">
        <h3>Buyer info</h3>
        
        <div class="info-section" v-if="AddonsAuth.loggedInUser.user_data.areas_of_activity && AddonsAuth.loggedInUser.user_data.areas_of_activity.length > 0">
          <h4>MAIN AREAS OF ACTIVITY</h4>
          <div class="tags-container">
            <span v-for="(area, index) in AddonsAuth.loggedInUser.user_data.areas_of_activity" :key="index" class="tag">
              {{ area }}
            </span>
          </div>
        </div>

        <div class="info-section" v-if="AddonsAuth.loggedInUser.user_data.nation && AddonsAuth.loggedInUser.user_data.nation.length > 0">
          <h4>NATION</h4>
          <div class="tags-container">
            <span v-for="(area, index) in AddonsAuth.loggedInUser.user_data.nation" :key="index" class="tag">
              {{ area }}
            </span>
          </div>
        </div>
      </div>

      <div class="sellers-interests-card" v-if="hasBuyerInterests">
        <h3>Buyer interests</h3>
        
        <div class="info-section">
          <h4>PREFERRED WORKSHOP MEETINGS WITH</h4>
          <div class="tags-container">
            <span v-for="(preference, index) in AddonsAuth.loggedInUser.user_data.preferred_workshop_meetings" :key="index" class="tag">
              {{ preference }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Gallery Popup Modal -->
  <div v-if="isGalleryPopupOpen" class="gallery-popup-overlay" @click.self="closeGalleryPopup">
    <div class="gallery-popup-content">
      <button class="gallery-popup-close" @click="closeGalleryPopup">&times;</button>
      <img :src="popupImageSrc" alt="Gallery Preview" />
    </div>
  </div>
</template>

<style scoped> 
/* CSS Custom Properties for consistent theming and cross-browser compatibility */
:root {
  --tfhb-font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  --tfhb-font-size-base: 16px;
  --tfhb-line-height-base: 1.5;
  --tfhb-border-radius: 8px;
  --tfhb-transition: all 0.2s ease;
}

.profile-container {
  display: flex;
  min-height: 100vh;
  font-family: var(--tfhb-font-family);
  font-size: var(--tfhb-font-size-base);
  line-height: var(--tfhb-line-height-base);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 0;
  overflow-y: auto;
  min-width: 0; /* Prevents flex item from overflowing */
}

/* Company Banner */
.company-banner-container {
  position: relative;
  margin-bottom: 2rem;
  width: 100%;
}

.company-banner {
  width: 100%;
  height: 300px;
  object-fit: cover;
  display: block; /* Removes inline spacing issues */
}

.company-logo-overlay {
  position: absolute;
  left: 2rem;
  bottom: -40px;
  z-index: 2;
}

.company-logo {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid white;
  background: white;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: block;
}

/* Company Header */
.company-header {
  padding: 0 2rem 2rem 2rem;
  margin-top: 4rem;
}

.company-title-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.company-title {
  margin: 0;
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: 700;
  color: var(--tfhb-text-title-color, #141915);
  line-height: 1.2;
  word-wrap: break-word;
}

.company-type {
  background: var(--tfhb-primary-color, #2E6B38);
  color: white !important;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  flex-shrink: 0;
}

.star-icon {
  font-size: 1.25rem;
  color: #FFD700;
  flex-shrink: 0;
}

.company-subtitle {
  margin: 0;
  font-size: clamp(1rem, 2.5vw, 1.125rem);
  color: var(--tfhb-paragraph-color, #273F2B);
  line-height: 1.4;
}

/* Tabs */
.company-tabs {
  padding: 0 2rem;
  border-top: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
  border-bottom: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
  display: flex;
  gap: 0;
  flex-wrap: wrap;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: var(--tfhb-surface-primary-color, #C0D8C4) transparent;
}

.company-tabs::-webkit-scrollbar {
  height: 4px;
}

.company-tabs::-webkit-scrollbar-track {
  background: transparent;
}

.company-tabs::-webkit-scrollbar-thumb {
  background: var(--tfhb-surface-primary-color, #C0D8C4);
  border-radius: 2px;
}

.tab-button {
  background: none;
  border: none;
  padding: 1rem 1.5rem;
  font-size: 1rem;
  color: var(--tfhb-paragraph-color, #273F2B);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: var(--tfhb-transition);
  font-weight: 500;
  border-radius: 0;
  white-space: nowrap;
  flex-shrink: 0;
  min-height: 48px; /* Touch-friendly sizing */
  display: flex;
  align-items: center;
  justify-content: center;
}

.tab-button:hover {
  color: var(--tfhb-text-title-color, #141915);
}

.tab-button.active {
  color: var(--tfhb-primary-color, #2E6B38);
  border-bottom-color: var(--tfhb-primary-color, #2E6B38);
}

.tab-button:focus {
  outline: 2px solid var(--tfhb-primary-color, #2E6B38);
  outline-offset: -2px;
}

/* Tab Content */
.tab-content {
  padding: 2rem;
}

.content-card {
  background: var(--tfhb-surface-secondary, #FFFFFF);
  border-radius: var(--tfhb-border-radius);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

.content-card h2 {
  margin: 0 0 1.5rem 0;
  font-size: clamp(1.25rem, 3vw, 1.5rem);
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
  line-height: 1.3;
}

.content-card p {
  margin: 0 0 1rem 0;
  line-height: var(--tfhb-line-height-base);
  color: var(--tfhb-paragraph-color, #273F2B);
  font-size: 1rem;
}

/* Staff */
.staff-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.staff-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  border-radius: var(--tfhb-border-radius);
}

.staff-image {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.staff-info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
  line-height: 1.3;
}

.staff-info p {
  margin: 0;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-size: 0.875rem;
  line-height: 1.4;
}

/* Gallery */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.gallery-item img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: var(--tfhb-border-radius);
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
  display: block;
}

/* Video */
.video-title {
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
  margin-bottom: 0.5rem !important;
  font-size: 1.125rem;
  line-height: 1.3;
}

.video-description {
  color: var(--tfhb-paragraph-color, #273F2B);
  margin-bottom: 1.5rem !important;
  line-height: var(--tfhb-line-height-base);
}

.video-container {
  position: relative;
  width: 100%;
  height: 0;
  padding-bottom: 56.25%;
  border-radius: var(--tfhb-border-radius);
  overflow: hidden;
}

.video-iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
}

.no-video-message,
.no-data-message {
  color: var(--tfhb-paragraph-color, #273F2B);
  font-style: italic;
  text-align: center;
  padding: 2rem;
  line-height: var(--tfhb-line-height-base);
}

/* Documents */
.documents-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.document-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  border-radius: var(--tfhb-border-radius);
  border-left: 4px solid var(--tfhb-primary-color, #2E6B38);
  position: relative;
}

.document-download {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem;
  background: var(--tfhb-primary-color, #2E6B38);
  color: white;
  border-radius: 4px;
  text-decoration: none;
  transition: var(--tfhb-transition);
  min-height: 32px;
  min-width: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.document-download:hover {
  background: var(--tfhb-text-title-color, #141915);
}

.document-icon {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
}

.document-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--tfhb-border-radius);
  display: block;
}

.document-content h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
  line-height: 1.3;
}

.document-content p {
  margin: 0 0 0.5rem 0;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-size: 0.875rem;
  line-height: 1.4;
}

.document-size {
  font-size: 0.75rem;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-weight: 500;
}

/* Links */
.links-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.link-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  border-radius: var(--tfhb-border-radius);
  transition: var(--tfhb-transition);
}

.link-item:hover {
  background: var(--tfhb-surface-primary-color, #C0D8C4);
}

.link-icon {
  font-size: 1.125rem;
  flex-shrink: 0;
}

.link-item a {
  color: var(--tfhb-primary-color, #2E6B38);
  text-decoration: none;
  font-weight: 500;
  line-height: 1.4;
}

.link-item a:hover {
  text-decoration: underline;
}

/* Right Sidebar */
.sidebar-right {
  width: 400px;
  padding: 0 2rem;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.contact-card,
.buyer-info-card,
.sellers-interests-card {
  background: var(--tfhb-surface-secondary, #FFFFFF);
  border-radius: var(--tfhb-border-radius);
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

.contact-card h3,
.buyer-info-card h3,
.sellers-interests-card h3 {
  margin: 0 0 2rem 0;
  font-size: clamp(1.125rem, 2.5vw, 1.25rem);
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
  line-height: 1.3;
}

.contact-section {
  margin-bottom: 2rem;
}

.contact-item {
  margin-bottom: 1.5rem;
}

.contact-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--tfhb-paragraph-color, #273F2B);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
  line-height: 1.3;
}

.contact-item a {
  color: var(--tfhb-primary-color, #2E6B38);
  text-decoration: none;
  font-weight: 500;
  line-height: 1.4;
}

.contact-item a:hover {
  text-decoration: underline;
}

.phone-numbers {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.phone-numbers div {
  color: var(--tfhb-text-title-color, #141915);
  font-weight: 500;
  line-height: 1.4;
}

.social-section h4 {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--tfhb-paragraph-color, #273F2B);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  line-height: 1.3;
}

.social-links {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.social-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  border-radius: var(--tfhb-border-radius);
  text-decoration: none;
  color: var(--tfhb-text-title-color, #141915);
  transition: var(--tfhb-transition);
  min-height: 44px; /* Touch-friendly sizing */
}

.social-link:hover {
  background: var(--tfhb-surface-primary-color, #C0D8C4);
}

.social-icon {
  font-size: 1.125rem;
  flex-shrink: 0;
}

.no-social-links {
  padding: 0.75rem;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-style: italic;
  text-align: center;
  line-height: var(--tfhb-line-height-base);
}

/* Info Sections */
.info-section {
  margin-bottom: 2rem;
}

.info-section:last-child {
  margin-bottom: 0;
}

.info-section h4 {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--tfhb-paragraph-color, #273F2B);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  line-height: 1.3;
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: flex-start;
}

.tag {
  background: var(--tfhb-surface-background-color, #EEF6F0);
  color: var(--tfhb-text-title-color, #141915);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
  line-height: 1.3;
  white-space: normal;
  word-wrap: break-word;
  max-width: 100%;
  overflow-wrap: break-word;
  hyphens: auto;
  display: inline-block;
  min-width: 0;
  flex-shrink: 1;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .profile-container {
    flex-direction: column;
  }
  
  .main-content {
    order: 1;
  }
  
  .sidebar-right {
    width: 100%;
    order: 2;
    flex-direction: row;
    flex-wrap: wrap;
    padding: 0 1rem;
  }
  
  .contact-card,
  .buyer-info-card,
  .sellers-interests-card {
    flex: 1;
    min-width: 300px;
  }
}

@media (max-width: 768px) {
  .company-tabs {
    padding: 0 1rem;
    gap: 0;
  }
  
  .tab-content {
    padding: 1rem;
  }
  
  .company-header {
    padding: 0 1rem 1rem 1rem;
    margin-top: 2rem;
  }
  
  .company-title {
    font-size: clamp(1.5rem, 5vw, 2rem);
  }
  
  .company-subtitle {
    font-size: clamp(0.875rem, 3vw, 1rem);
  }
  
  .content-card {
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .content-card h2 {
    font-size: clamp(1.125rem, 4vw, 1.25rem);
  }
  
  .gallery-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .sidebar-right {
    flex-direction: column;
    padding: 0 1rem;
  }
  
  .contact-card,
  .buyer-info-card,
  .sellers-interests-card {
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .company-header {
    padding: 0 0.75rem 1rem 0.75rem;
  }
  
  .company-tabs {
    padding: 0 0.75rem;
  }
  
  .tab-content {
    padding: 0.75rem;
  }
  
  .content-card {
    padding: 1rem;
    margin-bottom: 1rem;
  }
  
  .sidebar-right {
    padding: 0 0.75rem;
  }
  
  .company-logo-overlay {
    left: 1rem;
  }
  
  .company-logo {
    width: 60px;
    height: 60px;
  }
  
  .company-banner {
    height: 200px;
  }
}

/* Gallery Popup Styles */
.gallery-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  box-sizing: border-box;
}

.gallery-popup-content {
  position: relative;
  background: #fff;
  padding: 1rem;
  border-radius: var(--tfhb-border-radius);
  max-width: 90vw;
  max-height: 90vh;
  box-shadow: 0 2px 16px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.gallery-popup-content img {
  max-width: 100%;
  max-height: 100%;
  border-radius: 4px;
  object-fit: contain;
}

.gallery-popup-close {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: transparent;
  border: none;
  font-size: 2rem;
  color: #333;
  cursor: pointer;
  z-index: 10;
  min-height: 44px;
  min-width: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: var(--tfhb-transition);
}

.gallery-popup-close:hover {
  background: rgba(0,0,0,0.1);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .tab-button.active {
    border-bottom-width: 3px;
  }
  
  .content-card {
    border-width: 2px;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .tab-button,
  .social-link,
  .link-item,
  .document-download,
  .gallery-popup-close {
    transition: none;
  }
}

/* Print styles */
@media print {
  .company-tabs,
  .gallery-popup-overlay {
    display: none;
  }
  
  .profile-container {
    flex-direction: column;
  }
  
  .sidebar-right {
    width: 100%;
  }
}
</style>
