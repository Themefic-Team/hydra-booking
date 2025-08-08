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
//     const response = await axios.get(`/wp-json/hydra-booking/v1/addons/exhibitors/event-details/${event_id}`, {
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
          :src="AddonsAuth.loggedInUser?.user_data?.cover_image || ''" 
          alt="Company Banner" 
          class="company-banner"
        />
        
        <!-- Company Logo Overlay -->
        <div class="company-logo-overlay">
          <img 
            :src="AddonsAuth.loggedInUser?.user_data?.cover_image || ''" 
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
          v-for="tab in tabs" 
          :key="tab" 
          :class="['tab-button', { active: activeTab === tab }]"
          @click="activeTab = tab"
        >
          {{ tab }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Home Tab -->
        <div v-if="activeTab === 'Home'" class="home-content">
          <div class="content-card">
            <h2>About</h2>
            <p>{{ AddonsAuth.loggedInUser?.user_data?.description || 'No description available' }}</p>
          </div>

          <div class="content-card">
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

          <div class="content-card">
            <h2>Gallery</h2>
            <div class="gallery-grid" v-if="userGallery.length > 0">
              <div v-for="(img, index) in userGallery" :key="index" class="gallery-item">
                <img :src="img.url" :alt="img.title" @click="openGalleryPopup(img)" style="cursor:pointer;" />
              </div>
            </div>
            <p v-else class="no-data-message">No gallery images added yet.</p>
          </div>

          <div class="content-card" v-if="userVideo.url">
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

          <div class="content-card" v-if="!userVideo.url">
            <h2>Video</h2>
            <p class="no-video-message">No video content available yet.</p>
          </div>

          <div class="content-card">
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

          <div class="content-card">
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

        <!-- About Tab -->
        <div v-if="activeTab === 'About'" class="content-card">
          <h2>About</h2>
          <p>{{ AddonsAuth.loggedInUser.user_data.description }}</p>
        </div>

        <!-- Staff Tab -->
        <div v-if="activeTab === 'Staff'" class="content-card">
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
        <div v-if="activeTab === 'Gallery'" class="content-card">
          <h2>Gallery</h2>
          <div class="gallery-grid" v-if="userGallery.length > 0">
            <div v-for="(img, index) in userGallery" :key="index" class="gallery-item">
              <img :src="img.url" :alt="img.title" @click="openGalleryPopup(img)" style="cursor:pointer;" />
            </div>
          </div>
          <p v-else class="no-data-message">No gallery images added yet.</p>
        </div>

        <!-- Video Tab -->
        <div v-if="activeTab === 'Video' && userVideo.url" class="content-card">
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

        <div v-if="activeTab === 'Video' && !userVideo.url" class="content-card">
          <h2>Video</h2>
          <p class="no-video-message">No video content available yet.</p>
        </div>

        <!-- Documents Tab -->
        <div v-if="activeTab === 'Documents'" class="content-card">
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
        <div v-if="activeTab === 'Links'" class="content-card">
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
    <div class="sidebar-right">
      <div class="contact-card">
        <h3>Contact information</h3>
        
        <div class="contact-section">
          <div class="contact-item">
            <span class="contact-label">SITE</span>
            <a :href="`https://${AddonsAuth.loggedInUser.user_data.company_website}`" target="_blank">
              {{ AddonsAuth.loggedInUser.user_data.company_website }}
            </a>
          </div>
          
          <div class="contact-item">
            <span class="contact-label">EMAIL</span>
            <a :href="`mailto:${AddonsAuth.loggedInUser.user_data.email}`">
              {{ AddonsAuth.loggedInUser.user_data.email }}
            </a>
          </div>
          
          <div class="contact-item">
            <span class="contact-label">PHONE</span>
            <div class="phone-numbers">
              <div>{{ AddonsAuth.loggedInUser.user_data.mobile_no }}</div>
            </div>
          </div>
          
          <div class="contact-item">
            <span class="contact-label">LOCATION</span>
            <div>{{ AddonsAuth.loggedInUser.user_data.address }}</div>
          </div>
        </div>

        <div class="social-section">
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
            <div v-if="!userSocialShare.instagram && !userSocialShare.facebook && !userSocialShare.youtube && !userSocialShare.linkedin" class="no-social-links">
              <span>No social media links added yet</span>
            </div>
          </div>
        </div>
      </div>

      <div class="seller-info-card">
        <h3>Exhibitor info</h3>
        
        <div class="info-section">
          <h4>MAIN AREAS OF ACTIVITY</h4>
          <div class="tags-container">
            <span v-for="(area, index) in AddonsAuth.loggedInUser.user_data.areas_of_activity" :key="index" class="tag">
              {{ area }}
            </span>
          </div>
        </div>

        <div class="info-section">
          <h4>NATION</h4>
          <div class="tags-container">
            <!-- <span class="tag">{{ AddonsAuth.loggedInUser.user_data.nation }}</span> -->
            <span v-for="(area, index) in AddonsAuth.loggedInUser.user_data.nation " :key="index" class="tag">
              {{ area }}
            </span>
          </div>
        </div>
      </div>

      <div class="sellers-interests-card">
        <h3>Exhibitor interests</h3>
        
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
.profile-container {
  display: flex;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--tfhb-surface-background-color, #EEF6F0);
}

/* Main Content */
.main-content {
  flex: 1;
  background: var(--tfhb-surface-secondary, #FFFFFF);
  padding: 0;
  overflow-y: auto;
}

/* Company Banner */
.company-banner-container {
  position: relative;
  margin-bottom: 2rem;
}

.company-banner {
  width: 100%;
  height: 300px;
  object-fit: cover;
}

.company-logo-overlay {
  position: absolute;
  left: 2rem;
  bottom: -40px;
}

.company-logo {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid white;
  background: white;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
}

.company-title {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--tfhb-text-title-color, #141915);
}

.company-type {
  background: var(--tfhb-primary-color, #2E6B38);
  color: white !important;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.star-icon {
  font-size: 1.25rem;
  color: #FFD700;
}

.company-subtitle {
  margin: 0;
  font-size: 1.125rem;
  color: var(--tfhb-paragraph-color, #273F2B);
}

/* Tabs */
.company-tabs {
  padding: 0 2rem;
  border-top: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
  border-bottom: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
  display: flex;
  gap: 0;
}

.tab-button {
  background: none;
  border: none;
  padding: 1rem 1.5rem;
  font-size: 1rem;
  color: var(--tfhb-paragraph-color, #273F2B);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
  font-weight: 500;
  border-radius: 0;
}

.tab-button:hover {
  color: var(--tfhb-text-title-color, #141915);
}

.tab-button.active {
  color: var(--tfhb-primary-color, #2E6B38);
  border-bottom-color: var(--tfhb-primary-color, #2E6B38);
}

/* Tab Content */
.tab-content {
  padding: 2rem;
}

.content-card {
  background: var(--tfhb-surface-secondary, #FFFFFF);
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

.content-card h2 {
  margin: 0 0 1.5rem 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
}

.content-card p {
  margin: 0 0 1rem 0;
  line-height: 1.6;
  color: var(--tfhb-paragraph-color, #273F2B);
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
  border-radius: 8px;
}

.staff-image {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
}

.staff-info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
}

.staff-info p {
  margin: 0;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-size: 0.875rem;
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
  border-radius: 8px;
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

/* Video */
.video-title {
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
  margin-bottom: 0.5rem !important;
}

.video-description {
  color: var(--tfhb-paragraph-color, #273F2B);
  margin-bottom: 1.5rem !important;
}

.video-container {
  position: relative;
  width: 100%;
  height: 0;
  padding-bottom: 56.25%;
  border-radius: 8px;
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

.no-video-message {
  color: var(--tfhb-paragraph-color, #273F2B);
  font-style: italic;
  text-align: center;
  padding: 2rem;
}

.no-data-message {
  color: var(--tfhb-paragraph-color, #273F2B);
  font-style: italic;
  text-align: center;
  padding: 2rem;
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
  border-radius: 8px;
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
  transition: background-color 0.2s;
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
  border-radius: 8px;
}

.document-content h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
}

.document-content p {
  margin: 0 0 0.5rem 0;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-size: 0.875rem;
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
  border-radius: 8px;
  transition: background-color 0.2s;
}

.link-item:hover {
  background: var(--tfhb-surface-primary-color, #C0D8C4);
}

.link-icon {
  font-size: 1.125rem;
}

.link-item a {
  color: var(--tfhb-primary-color, #2E6B38);
  text-decoration: none;
  font-weight: 500;
}

.link-item a:hover {
  text-decoration: underline;
}

/* Right Sidebar */
.sidebar-right {
  width: 500px;
  padding: 0 2rem;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 2rem;
} 
.contact-card,
.seller-info-card,
.sellers-interests-card {
  background: var(--tfhb-surface-secondary, #FFFFFF);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

.contact-card h3,
.seller-info-card h3,
.sellers-interests-card h3 {
  margin: 0 0 2rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
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
}

.contact-item a {
  color: var(--tfhb-primary-color, #2E6B38);
  text-decoration: none;
  font-weight: 500;
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
}

.social-section h4 {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--tfhb-paragraph-color, #273F2B);
  text-transform: uppercase;
  letter-spacing: 0.05em;
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
  border-radius: 8px;
  text-decoration: none;
  color: var(--tfhb-text-title-color, #141915);
  transition: background-color 0.2s;
}

.social-link:hover {
  background: var(--tfhb-surface-primary-color, #C0D8C4);
}

.social-icon {
  font-size: 1.125rem;
}

.no-social-links {
  padding: 0.75rem;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-style: italic;
  text-align: center;
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
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  background: var(--tfhb-surface-background-color, #EEF6F0);
  color: var(--tfhb-text-title-color, #141915);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
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
  }
  
  .contact-card,
  .seller-info-card,
  .sellers-interests-card {
    flex: 1;
    min-width: 300px;
  }
}

@media (max-width: 768px) {
  .company-tabs {
    overflow-x: auto;
    padding: 0 1rem;
  }
  
  .tab-content {
    padding: 1rem;
  }
  
  .company-header {
    padding: 0 1rem 1rem 1rem;
  }
  
  .company-title {
    font-size: 2rem;
  }
  
  .gallery-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .sidebar-right {
    flex-direction: column;
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
}

.gallery-popup-content {
  position: relative;
  background: #fff;
  padding: 1rem;
  border-radius: 8px;
  max-width: 90vw;
  max-height: 90vh;
  box-shadow: 0 2px 16px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.gallery-popup-content img {
  max-width: 80vw;
  max-height: 80vh;
  border-radius: 4px;
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
}
</style>
