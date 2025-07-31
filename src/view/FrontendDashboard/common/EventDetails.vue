
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';
const route = useRoute()
const eventDetails = ref({})
const skeleton = ref(true)
const activeTab = ref('Home')

const tabs = ['Home', 'Description', 'Gallery', 'Video', 'Documents', 'Links']

// Default data for demonstration
const defaultGalleryImages = [
  'https://via.placeholder.com/300x200/2E6B38/FFFFFF?text=Welcome+Screen',
  'https://via.placeholder.com/300x200/273F2B/FFFFFF?text=Outdoor+View',
  'https://via.placeholder.com/300x200/4C9959/FFFFFF?text=Product+Display',
  'https://via.placeholder.com/300x200/C0D8C4/FFFFFF?text=Networking',
  'https://via.placeholder.com/300x200/EEF6F0/FFFFFF?text=Conference',
  'https://via.placeholder.com/300x200/56765B/FFFFFF?text=Event+Scene'
]

const defaultProgramItems = [
  {
    title: 'Event Forum',
    subtitle: 'Main event forum and discussions'
  },
  {
    title: 'Event Lab',
    subtitle: 'Interactive workshops and sessions'
  }
]

const defaultLinks = [
  {
    title: 'Event Website',
    url: 'https://event-website.com'
  },
  {
    title: 'Event Partner',
    url: 'https://event-partner.com'
  }
]

// Lightbox state for gallery popup
const isGalleryPopupOpen = ref(false)
const popupImageSrc = ref('')

function openGalleryPopup(img) {
  popupImageSrc.value = img
  isGalleryPopupOpen.value = true
}
function closeGalleryPopup() {
  isGalleryPopupOpen.value = false
  popupImageSrc.value = ''
}

const fetchEventDetails = async () => {
  // If route.params.id is not available, show the event id from AddonsAuth.event
  let event_id = 0
  if (!route.params.id) {
    event_id = AddonsAuth.event?.id || 0
  } else{
    event_id = route.params.id
  }
  try {
    skeleton.value = true
    const response = await axios.get(`/wp-json/hydra-booking/v1/addons/sellers/event-details/${event_id}`, {
      headers: {
        'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
      }
    })
    
    if (response.data.status) {
      eventDetails.value = response.data.event_details || {}
    }
  } catch (error) {
    console.error('Error fetching event details:', error)
  } finally {
    skeleton.value = false
  }
}

onMounted(() => {
  fetchEventDetails()
})
</script>

<template> 
  <div class="event-details-container">
    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Event Banner -->
      <div class="event-banner-container">
        <img 
          v-if="eventDetails.event_banner" 
          :src="eventDetails.event_banner" 
          alt="Event Banner" 
          class="event-banner"
        />
        <img 
          v-else 
          :src="$tfhb_url + '/assets/app/images/meeting-cover.png'" 
          alt="Event Banner" 
          class="event-banner"
          
        />
        
        <!-- Event Logo Overlay -->
        <div class="event-logo-overlay">
          <img 
            v-if="eventDetails.event_logo" 
            :src="eventDetails.event_logo" 
            alt="Event Logo" 
            class="event-logo"
          />
          <img 
            v-else
            :src="$tfhb_url+'/assets/images/avator.png'" 
            alt="Event Logo" 
            class="event-logo"
          /> 
        </div>
      </div>

      <!-- Event Title and Date -->
      <div class="event-header">
        <h1 class="event-title">{{ eventDetails.title || 'Event Title' }}</h1>
        <p class="event-subtitle">{{ eventDetails.subtitle || 'Event Subtitle' }}</p>
        <p class="event-date">{{ eventDetails.date || 'Event Date' }}</p>
      </div>

      <!-- Navigation Tabs -->
      <div class="event-tabs">
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
            <h2>Description</h2>
              <p v-if="eventDetails.description != null">{{ eventDetails.description }}</p>
              <p v-else>No description found</p>
            </div>

          <div class="content-card">
            <h2>Gallery</h2>
            <div  v-if="eventDetails.gallery_images && eventDetails.gallery_images.length > 0" class="gallery-grid">
              <div v-for="(img, index) in eventDetails.gallery_images " :key="index" class="gallery-item">
                <img :src="img" alt="Gallery Image" @click="openGalleryPopup(img)" style="cursor:pointer;" />
              </div>
            </div>
            <div v-else>
              <p>No gallery images found</p>
            </div>
          </div>

          <div  class="content-card">
            <h2>Video</h2>
            <div  v-if="eventDetails.video_url != null"  >
              <p class="video-title">{{ eventDetails.video_title || 'Event Video Presentation' }}</p>
              <p class="video-description">{{ eventDetails.video_description || 'Video presentation of our exclusive event' }}</p>
              <div class="video-container">
                <iframe 
                  v-if="eventDetails.video_url" 
                  :src="eventDetails.video_url" 
                  frameborder="0" 
                  allowfullscreen
                  class="video-iframe"
                ></iframe>
                <div v-else class="video-placeholder">
                  <div class="video-placeholder-content">
                    <div class="play-button">▶</div>
                    <p>Video presentation</p>
                  </div>
                </div>
              </div>
            </div>
            <div v-else>
              <p>No video found</p>
            </div>
          </div>

          <div  class="content-card">
            <h2>Documents</h2>
            <div v-if="eventDetails.program_items && eventDetails.program_items.length > 0" class="program-list">
              <div v-for="(item, index) in eventDetails.program_items " :key="index" class="program-item" v-if="item && item.program_file != null && item.program_file.length > 0">
                <div class="program-icon">
                  <img v-if="item.program_icon" :src="item.program_icon" alt="Program Icon" />
                  <div v-else class="program-icon-placeholder">📋</div>
                </div>
                <div class="program-content">
                  <h3>{{ item.title || 'Untitled' }}</h3>
                  <p>{{ item.subtitle || 'No description' }}</p>
                </div>
              </div>
            </div>
            <div v-else class="program-list">
              <p>No documents found</p>
            </div>
          </div>

          <div  class="content-card">
            <h2>Links</h2>
            <div v-if="eventDetails.external_links != null && eventDetails.external_links.length > 0"  class="links-list">
              <div v-for="(link, index) in eventDetails.external_links " :key="index" class="link-item" v-if="link && link.url">
                <span class="link-icon">🌐</span>
                <a :href="link.url" target="_blank">{{ link.title || 'Untitled Link' }}</a>
              </div>
            </div>
            <div v-else class="links-list">
              <p>No links found</p>
            </div>
          </div>
        </div>

        <!-- Description Tab -->
        <div v-if="activeTab === 'Description'" class="content-card">
          <h2>Description</h2>
          <p v-if="eventDetails.description != null">{{ eventDetails.description }}</p>
          <p v-else>No description found</p>
        </div>

        <!-- Gallery Tab -->
        <div v-if="activeTab === 'Gallery'" class="content-card">
          <h2>Gallery</h2>
          <div v-if="eventDetails.gallery_images != null && eventDetails.gallery_images.length > 0" class="gallery-grid">
            <div v-for="(img, index) in eventDetails.gallery_images " :key="index" class="gallery-item">
              <img :src="img" alt="Gallery Image" @click="openGalleryPopup(img)" style="cursor:pointer;" />
            </div>
          </div>
          <div v-else class="gallery-grid">
            <p>No gallery images found</p>
          </div>
        </div>

        <!-- Gallery Popup Modal (duplicate for Gallery tab) -->
        <div v-if="isGalleryPopupOpen" class="gallery-popup-overlay" @click.self="closeGalleryPopup">
          <div class="gallery-popup-content">
            <button class="gallery-popup-close" @click="closeGalleryPopup">&times;</button>
            <img :src="popupImageSrc" alt="Gallery Preview" />
          </div>
        </div>

        <!-- Video Tab -->
        <div v-if="activeTab === 'Video'" class="content-card">
          <h2>Video</h2>
          <div  v-if="eventDetails.video_url != null && eventDetails.video_url.length > 0"  >
            <p class="video-title">{{ eventDetails.video_title || 'Event Video Presentation' }}</p>
            <p class="video-description">{{ eventDetails.video_description || 'Video presentation of our exclusive event' }}</p>
            <div class="video-container">
              <iframe 
                v-if="eventDetails.video_url" 
                :src="eventDetails.video_url" 
                frameborder="0" 
                allowfullscreen
                class="video-iframe"
              ></iframe>
              <div v-else class="video-placeholder">
                <div class="video-placeholder-content">
                  <div class="play-button">▶</div>
                  <p>Video presentation</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else>
            <p>No video found</p>
          </div>          
        </div>

        <!-- Documents Tab -->
        <div v-if="activeTab === 'Documents'" class="content-card">
          <h2>Documents</h2>
            <div v-if="eventDetails.program_items != null && eventDetails.program_items.length > 0" class="program-list">
              <div v-for="(item, index) in eventDetails.program_items " :key="index" class="program-item" v-if="item">
                <div class="program-icon">
                  <img v-if="item.program_icon" :src="item.program_icon" alt="Program Icon" />
                  <div v-else class="program-icon-placeholder">📋</div>
                </div>
                <div class="program-content">
                  <h3>{{ item.title || 'Untitled' }}</h3>
                  <p>{{ item.subtitle || 'No description' }}</p>
                </div>
              </div>
            </div>
            <div v-else class="program-list">
              <p>No documents found</p>
            </div>
        </div>

        <!-- Links Tab -->
        <div v-if="activeTab === 'Links'" class="content-card">
          <h2>Links</h2>
          <div v-if="eventDetails.external_links != null && eventDetails.external_links.length > 0" class="links-list">
            <div v-for="(link, index) in eventDetails.external_links " :key="index" class="link-item" v-if="link && link.url">
              <span class="link-icon">🌐</span>
              <a :href="link.url" target="_blank">{{ link.title || 'Untitled Link' }}</a>
            </div>
          </div>
          <div v-else class="links-list">
            <p>No links found</p>
          </div>
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
            <a :href="eventDetails.contact_info?.website || 'https://www.example.com'" target="_blank">
              {{ eventDetails.contact_info?.website || 'www.example.com' }}
            </a>
          </div>
          
          <div class="contact-item">
            <span class="contact-label">EMAIL</span>
            <a :href="`mailto:${eventDetails.contact_info?.email || 'info@example.com'}`">
              {{ eventDetails.contact_info?.email || 'info@example.com' }}
            </a>
          </div>
          
          <div class="contact-item">
            <span class="contact-label">PHONE</span>
            <div class="phone-numbers">
              <div>{{ eventDetails.contact_info?.phone || '+01 917 055-0403' }}</div>
              <div>{{ eventDetails.contact_info?.phone2 || '+01 920 036-1002' }}</div>
              <div>{{ eventDetails.contact_info?.phone3 || '+01 917 070-2102' }}</div>
            </div>
          </div>
          
          <div class="contact-item">
            <span class="contact-label">LOCATION</span>
            <div>{{ eventDetails.contact_info?.location || 'Event Location' }}</div>
          </div>
        </div>

        <div class="social-section">
          <h4>SOCIAL</h4>
          <div class="social-links">
            <a href="#" class="social-link">
              <span class="social-icon">📷</span>
              <span>Instagram</span>
            </a>
            <a href="#" class="social-link">
              <span class="social-icon">📘</span>
              <span>Facebook</span>
            </a>
            <a href="#" class="social-link">
              <span class="social-icon">📺</span>
              <span>YouTube</span>
            </a>
            <a href="#" class="social-link">
              <span class="social-icon">💼</span>
              <span>LinkedIn</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.event-details-container {
  display: flex;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Main Content */
.main-content {
  flex: 1;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  padding: 0;
  overflow-y: auto;
}

/* Event Banner */
.event-banner-container {
  position: relative;
  margin-bottom: 2rem;
  border-radius: 16px 16px 0px 0px;
}

.event-banner {
  width: 100%;
  height: 220px;
  object-fit: cover;
  border-radius: 16px 16px 0px 0px;
}

.event-banner-placeholder {
  width: 100%;
  height: 300px;
  background: linear-gradient(135deg, var(--tfhb-primary-color, #2E6B38) 0%, var(--tfhb-secondary-color, #273F2B) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.banner-placeholder-content {
  text-align: center;
}

.banner-placeholder-content h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
}

.banner-placeholder-content p {
  margin: 0;
  opacity: 0.8;
}

.event-logo-overlay {
  position: absolute;
  left: 2rem;
  bottom: -40px;
}

.event-logo {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid white;
  background: white;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.event-logo-placeholder {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid white;
  background: linear-gradient(45deg, var(--tfhb-primary-color, #2E6B38), var(--tfhb-secondary-color, #273F2B));
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-placeholder-content {
  width: 40px;
  height: 40px;
  background: white;
  border-radius: 50%;
}

/* Event Header */
.event-header {
  padding: 0 2rem 2rem 2rem;
  margin-top: 4rem;
}

.event-title {
  margin: 0 0 0.5rem 0;
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--tfhb-text-title-color, #141915);
}

.event-subtitle {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  color: var(--tfhb-paragraph-color, #273F2B);
}

.event-date {
  margin: 0;
  font-size: 1rem;
  color: var(--tfhb-primary-color, #2E6B38);
  font-weight: 500;
}

/* Tabs */
.event-tabs {
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
  /* font-weight: 600; */
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

.video-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-placeholder-content {
  text-align: center;
  color: var(--tfhb-paragraph-color, #273F2B);
}

.play-button {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--tfhb-primary-color, #2E6B38);
}

/* Program */
.program-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.program-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--tfhb-surface-background-color, #EEF6F0);
  border-radius: 8px;
  border-left: 4px solid var(--tfhb-primary-color, #2E6B38);
}

.program-icon {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
}

.program-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.program-icon-placeholder {
  width: 100%;
  height: 100%;
  background: var(--tfhb-surface-primary-color, #C0D8C4);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.program-content h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--tfhb-text-title-color, #141915);
}

.program-content p {
  margin: 0;
  color: var(--tfhb-paragraph-color, #273F2B);
  font-size: 0.875rem;
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
  width: 400px;
  padding: 0 2rem;
  flex-shrink: 0;
}

.contact-card {
  background: var(--tfhb-surface-secondary, #FFFFFF);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--tfhb-surface-primary-color, #C0D8C4);
}

.contact-card h3 {
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

/* Responsive Design */
@media (max-width: 1200px) {
  .event-details-container {
    flex-direction: column;
  }
  
  .main-content {
    order: 1;
  }
  
  .sidebar-right {
    width: 100%;
    order: 2;
  }
}

@media (max-width: 768px) {
  .event-tabs {
    overflow-x: auto;
    padding: 0 1rem;
  }
  
  .tab-content {
    padding: 1rem;
  }
  
  .event-header {
    padding: 0 1rem 1rem 1rem;
  }
  
  .event-title {
    font-size: 2rem;
  }
  
  .gallery-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
