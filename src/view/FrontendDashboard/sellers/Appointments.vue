
<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, computed, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios'   
import { useRouter } from 'vue-router'
import Icon from '@/components/icon/LucideIcon.vue'
import HbPopup from '@/components/widgets/HbPopup.vue'; 
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import HbButton from '@/components/form-fields/HbButton.vue';
import { toast } from "vue3-toastify"; 
import useDateFormat from '@/store/dateformat'
const { Tfhb_Date, Tfhb_Time } = useDateFormat();
import { Meeting } from '@/store/meetings'
import { Booking } from '@/store/booking'
import { Host } from '@/store/hosts'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

import { AddonsSettings } from '@/store/settings/addons-settings.js';
import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';

// PDF generation libraries
import jsPDF from 'jspdf';
import QRCode from 'qrcode'; 
import html2canvas from 'html2canvas';
import JSZip from 'jszip';

const router = useRouter()
const bookingView = ref('list');

// Calendar view state
const currentDate = ref(new Date());
const calendarView = ref('dayGridMonth'); // 'timeGridDay', 'timeGridWeek', 'dayGridMonth'
const calendarRef = ref(null);
const showDetailsPanel = ref(false);

// Search and filter state
const searchQuery = ref('');
const selectedStatus = ref('all');
const selectedType = ref('all');

// Calendar events from API data
const calendarEvents = ref([]);

// List view state
const currentView = ref('list'); // 'calendar' or 'list'
const currentPage = ref(1);
const itemsPerPage = ref(10);
const listEvents = ref([]);

// Function to convert API data to calendar format
const convertApiDataToCalendarEvents = (apiData) => {
    if (!apiData || !Array.isArray(apiData)) return [];
    
    return apiData.map(item => {
        // Parse date and time
        const date = item.date; // Format: 2025-11-18
        const startTime = item.start_time; // Format: 17:45
        const endTime = item.end_time; // Format: 18:00
        
        // Convert to ISO datetime format
        const startDateTime = `${date}T${startTime}:00`;
        const endDateTime = `${date}T${endTime}:00`;
        
        // Get buyer name from API data
        const buyerData = item.buyers_data?.user_meta?.tfhb_buyers_data || {};
        const buyerName = buyerData.name || 
                         buyerData.name_of_participant || 
                         buyerData.family_name_of_participant ||
                         item.buyers_data?.display_name ||
                         'Unknown Buyer';
        
        // Get seller name from API data
        const sellerData = item.sellers_data?.user_meta?.tfhb_sellers_data || {};
        const sellerName = sellerData.name ||
                          item.sellers_data?.display_name ||
                          'Unknown Seller';
        
        // Get company name from buyers data first, then sellers
        const buyerCompany = buyerData.company_name || buyerData.travel_agent_name || '';
        const sellerCompany = sellerData.company_name || sellerData.denominazione_operatore_azienda || '';
        const companyName = buyerCompany || sellerCompany;
        
        // Create title with company name if available
        const title = companyName ? `${companyName} - ${buyerName}` : buyerName;
        
        // Determine colors based on status
        let backgroundColor, borderColor;
        switch (item.status) {
            case 'confirmed':
                backgroundColor = '#e8f5e8';
                borderColor = '#c8e6c9';
                break;
            case 'pending':
                backgroundColor = '#fff3e0';
                borderColor = '#ffcc80';
                break;
            case 'canceled':
                backgroundColor = '#ffebee';
                borderColor = '#ffcdd2';
                break;
            default:
                backgroundColor = '#f0f0f0';
                borderColor = '#d0d0d0';
        }
        
        // Format booking time for display
        const formatTime = (time) => {
            if (!time) return '';
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
            return `${displayHour}:${minutes} ${ampm}`;
        };
        
        const bookingTime = `${formatTime(startTime)} - ${formatTime(endTime)}`;
        
        return {
            id: item.id.toString(),
            title: title,
            start: startDateTime,
            end: endDateTime,
            backgroundColor: backgroundColor,
            borderColor: borderColor,
            extendedProps: {
                booking_id: item.booking_id ? item.booking_id.toString() : '',
                status: item.status,
                booking_date: date,
                booking_time: bookingTime,
                host_id: item.host_id?.toString() || '0',
                meeting_type: item.meeting_data?.meeting_type || 'one-to-one',
                meeting_id: item.meeting_id?.toString() || '',
                meeting_title: item.meeting_data?.title || '',
                attendees: [
                    {
                        attendee_name: buyerName,
                        email: item.buyers_data?.user_email || '',
                        status: item.status,
                        company: companyName
                    }
                ],
                // Store full API data for details panel
                apiData: item
            }
                 };
     });
 };

// Helper functions for details panel
const getCompanyInitials = (apiData) => {
    const buyerData = apiData?.buyers_data?.user_meta?.tfhb_buyers_data || {};
    const company = buyerData.company_name || buyerData.travel_agent_name || '';
    if (!company) return 'TA';
    return company.replace(/[^a-zA-Z]/g, '').substring(0, 2).toUpperCase();
};

const getBuyerName = (apiData) => {
    if (!apiData?.buyers_data?.user_meta?.tfhb_buyers_data) return 'Unknown Buyer';
    const buyerData = apiData.buyers_data.user_meta.tfhb_buyers_data;
    
    // Try to get full name first
    if (buyerData.name) return buyerData.name;
    
    // Otherwise combine first and last name
    const firstName = buyerData.name_of_participant || '';
    const lastName = buyerData.family_name_of_participant || '';
    return `${firstName} ${lastName}`.trim() || apiData.buyers_data.display_name || 'Unknown Buyer';
};

const getCompanyWebsite = (apiData) => {
    return apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.company_website || '';
};

const getAddress = (apiData) => {
    if (!apiData?.buyers_data?.user_meta?.tfhb_buyers_data) return '';
    const buyerData = apiData.buyers_data.user_meta.tfhb_buyers_data;
    const address = buyerData.address || '';
    const state = buyerData.state || '';
    return `${address}${address && state ? ', ' : ''}${state}`.trim();
};

const getAreasOfActivity = (apiData) => {
    return apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.areas_of_activity || [];
};

const getPreferredMeetings = (apiData) => {
    return apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.preferred_workshop_meetings || [];
};

// Filtered list events based on search query
const filteredListEvents = computed(() => {
    if (!searchQuery.value.trim()) {
        return listEvents.value;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return listEvents.value.filter(event => {
        const apiData = event.extendedProps.apiData || {};
        const buyerData = apiData.buyers_data?.user_meta?.tfhb_buyers_data || {};
        const sellerData = apiData.sellers_data?.user_meta?.tfhb_sellers_data || {};
        
        // Search in title
        if (event.title.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in buyer name
        const buyerName = buyerData.name ||
                         buyerData.name_of_participant ||
                         buyerData.family_name_of_participant ||
                         apiData.buyers_data?.display_name ||
                         '';
        if (buyerName.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in company name (both buyer and seller)
        const buyerCompany = buyerData.company_name || buyerData.travel_agent_name || '';
        const sellerCompany = sellerData.company_name || sellerData.denominazione_operatore_azienda || '';
        if (buyerCompany.toLowerCase().includes(query) || sellerCompany.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in description
        const description = buyerData.description || '';
        if (description.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in status
        if (event.extendedProps.status.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in date
        const eventDate = new Date(event.start).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        if (eventDate.toLowerCase().includes(query)) {
            return true;
        }
        
        return false;
    });
});

// Pagination computed properties
const totalPages = computed(() => {
    return Math.ceil(filteredListEvents.value.length / itemsPerPage.value);
});

const paginatedEvents = computed(() => {
    // Sort events by date in ascending order
    const sortedEvents = [...filteredListEvents.value].sort((a, b) => {
        const dateA = new Date(a.start);
        const dateB = new Date(b.start);
        return dateA - dateB;
    });
    
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return sortedEvents.slice(start, end);
});

// Calendar configuration
const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridDay',
    headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: ''
    },
    events: calendarEvents.value,
    height: 'auto',
    allDaySlot: false,
    slotMinTime: '06:00:00',
    slotMaxTime: '18:00:00',
    slotDuration: '00:15:00',
    selectable: false,
    editable: false,
    eventClick: (info) => {
        handleCalendarEventClick(info);
    },
    dateSet: (dateInfo) => {
        currentDate.value = dateInfo.start;
    },
    viewDidMount: (viewInfo) => {
        calendarView.value = viewInfo.view.type;
        // Keep details panel visible for all views
    }
});

// Selected event data for details panel
const selectedEventData = ref(null);

// Responsive state
const isMobile = ref(false);
const isTablet = ref(false);
const isSmallScreen = computed(() => isMobile.value || isTablet.value || window.innerWidth < 1500);

// Details panel popup state for mobile/tablet
const showDetailsPopup = ref(false);

// Calendar view change handler
const changeCalendarView = (view) => {
    calendarView.value = view;
    if (calendarRef.value) {
        const calendarApi = calendarRef.value.getApi();
        if (calendarApi) {
            calendarApi.changeView(view);
        }
    }
    // Keep details panel visible for all views
};

// Go to today function
const goToToday = () => {
    if (calendarRef.value) {
        const calendarApi = calendarRef.value.getApi();
        if (calendarApi) {
            calendarApi.today();
            currentDate.value = new Date();
        }
    }
};

// Format current date for display
const formatCurrentDate = () => {
    const date = currentDate.value;
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return date.toLocaleDateString('en-US', options);
};

// Search and filter functions
const handleSearch = () => {
    // Reset to first page when searching
    currentPage.value = 1;
};

const handleStatusFilter = () => {
    // Filter events based on status
};

const handleTypeFilter = () => {
    // Filter events based on type
};

// View toggle and pagination functions
const toggleView = async (view) => {
    currentView.value = view;
    if (view === 'list') {
        listEvents.value = [...calendarEvents.value]; // Populate listEvents from calendarEvents
    } else if (view === 'calendar') {
        // Wait for the calendar component to be re-rendered
        await nextTick();
        // Ensure calendar events are properly refreshed when switching back to calendar view
        if (calendarRef.value) {
            const calendarApi = calendarRef.value.getApi();
            if (calendarApi) {
                calendarApi.removeAllEvents();
                calendarApi.addEventSource(calendarEvents.value);
            }
        }
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const handleListEventClick = (event) => {
    selectedEventData.value = event;
    if (isSmallScreen.value) {
        showDetailsPopup.value = true;
    } else {
        showDetailsPanel.value = true;
    }
};

// Calendar event click handler
const handleCalendarEventClick = (info) => {
    selectedEventData.value = info.event;
    if (isSmallScreen.value) {
        showDetailsPopup.value = true;
    } else {
        showDetailsPanel.value = true;
    }
};

// Close details handlers
const closeDetailsPanel = () => {
    showDetailsPanel.value = false;
    selectedEventData.value = null;
};

const closeDetailsPopup = () => {
    showDetailsPopup.value = false;
    selectedEventData.value = null;
};

// Responsive detection
const checkScreenSize = () => {
    const width = window.innerWidth;
    isMobile.value = width <= 768;
    isTablet.value = width > 768 && width <= 1024;
    
    // If we're on small screen (below 1500px) and details panel is open, close it and show popup instead
    if (width < 1500 && showDetailsPanel.value) {
        showDetailsPanel.value = false;
        if (selectedEventData.value) {
            showDetailsPopup.value = true;
        }
    }
};

// Window resize handler
const handleResize = () => {
    checkScreenSize();
};

const exportCalendar = (format) => {
    if (format === 'iCal') {
        exportAsICal();
    } else if (format === 'PDF') {
        exportAsPDF();
    }
};


// Export as iCal function
const exportAsICal = () => {
    if (!calendarEvents.value || calendarEvents.value.length === 0) {
        toast.error('No events to export', {
            position: 'bottom-right',
            "autoClose": 1500,
        });
        return;
    }

    // Generate iCal content
    let ical = "BEGIN:VCALENDAR\r\n";
    ical += "VERSION:2.0\r\n";
    ical += "PRODID:-//Hydra Booking//Calendar Export//EN\r\n";
    ical += "CALSCALE:GREGORIAN\r\n";
    ical += "METHOD:PUBLISH\r\n";

    calendarEvents.value.forEach(event => {
        const startDate = new Date(event.start);
        const endDate = new Date(event.end);
        
        // Format dates for iCal (YYYYMMDDTHHMMSSZ)
        const formatDate = (date) => {
            return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        };

        ical += "BEGIN:VEVENT\r\n";
        ical += "UID:" + event.id + "@hydra-booking\r\n";
        ical += "DTSTAMP:" + formatDate(new Date()) + "\r\n";
        ical += "DTSTART:" + formatDate(startDate) + "\r\n";
        ical += "DTEND:" + formatDate(endDate) + "\r\n";
        ical += "SUMMARY:" + event.title + "\r\n";
        ical += "STATUS:" + event.extendedProps.status.toUpperCase() + "\r\n";
        
        // Add description if available
        if (event.extendedProps.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.description) {
            ical += "DESCRIPTION:" + event.extendedProps.apiData.buyers_data.user_meta.tfhb_buyers_data.description + "\r\n";
        }
        
        // Add location if available
        if (event.extendedProps.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.address) {
            ical += "LOCATION:" + event.extendedProps.apiData.buyers_data.user_meta.tfhb_buyers_data.address + "\r\n";
        }
        
        ical += "END:VEVENT\r\n";
    });

    ical += "END:VCALENDAR\r\n";

    // Create and download the file
    const blob = new Blob([ical], { type: 'text/calendar' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `calendar-export-${new Date().toISOString().split('T')[0]}.ics`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    toast.success('Calendar exported as .iCal successfully', {
        position: 'bottom-right',
        "autoClose": 1500,
    });
};

// Export as PDF function
const exportAsPDF = async () => {
    if (!calendarEvents.value || calendarEvents.value.length === 0) {
        toast.error('No events to export', {
            position: 'bottom-right',
            "autoClose": 1500,
        });
        return;
    }

    // Get seller company name for header
    const getSellerCompanyName = (apiData) => {
        const sellerData = apiData?.sellers_data?.user_meta?.tfhb_sellers_data || {};
        return sellerData.denominazione_operatore_azienda || sellerData.company_name || apiData?.sellers_data?.display_name || 'Company Name';
    };

    // Get buyer location
    const getBuyerLocation = (apiData) => {
        const buyerData = apiData?.buyers_data?.user_meta?.tfhb_buyers_data || {};
        return buyerData.nation || buyerData.state || '';
    };

    try {
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = 210;
        const pageHeight = 297;
        const margin = 15;
        const contentWidth = pageWidth - (2 * margin);
        let yPosition = margin;

        // Get event information
        const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const firstEventData = calendarEvents.value.length > 0 ? calendarEvents.value[0].extendedProps.apiData : null;
        const sellerData = firstEventData?.sellers_data?.user_meta?.tfhb_sellers_data || {};
        const headerCompanyName = sellerData.denominazione_operatore_azienda || sellerData.company_name || firstEventData?.sellers_data?.display_name || 'Company Name';
        
        // Create header with two-column layout
        const headerHeight = 20;
        const leftColumnWidth = 50; // Width for logo column (reduced from 60)
        const rightColumnStart = margin + leftColumnWidth + 8; // Start position for right column
        
        // LEFT COLUMN - Logo or placeholder
        if (AddonsAuth.event?.event_details?.event_logo) {
            try {
                const logoUrl = AddonsAuth.event.event_details.event_logo;
                const maxLogoHeight = 60; // Reduced from 80 pixels
                
                // Create an image element to get dimensions
                const img = new Image();
                img.crossOrigin = 'anonymous'; // Handle CORS
                img.src = logoUrl;
                
                // Wait for image to load
                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                });
                
                // Calculate dimensions while maintaining aspect ratio
                let logoWidth = img.width;
                let logoHeight = img.height;
                
                if (logoHeight > maxLogoHeight) {
                    const ratio = maxLogoHeight / logoHeight;
                    logoHeight = maxLogoHeight;
                    logoWidth = logoWidth * ratio;
                }
                
                // Create canvas to preprocess the image (removes artifacts and fixes colors)
                const canvas = document.createElement('canvas');
                canvas.width = logoWidth;
                canvas.height = logoHeight;
                const ctx = canvas.getContext('2d');
                
                // Fill with white background to remove transparency artifacts
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                
                // Draw the image on top
                ctx.drawImage(img, 0, 0, logoWidth, logoHeight);
                
                // Convert canvas to high-quality JPEG data URL
                const processedLogoUrl = canvas.toDataURL('image/jpeg', 1.0);
                
                // Add logo to PDF
                pdf.addImage(processedLogoUrl, 'JPEG', margin, yPosition, logoWidth * 0.2645833333, logoHeight * 0.2645833333);
            } catch (error) {
                console.error('Error loading logo:', error);
                // Fallback to placeholder text if logo fails to load
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'bold');
                pdf.text('Logo Events', margin, yPosition + 4);
            }
        } else {
            // Show placeholder text if no logo
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Logo Events', margin, yPosition + 4);
        }
        
        // RIGHT COLUMN - Content block (left aligned)
        // 1. User role (reduced font size)
        pdf.setFontSize(7);
        pdf.setFont('helvetica', 'bold');
        const userRole = AddonsAuth.loggedInUser?.user_role || 'SELLERS';
        pdf.text(userRole.toUpperCase(), rightColumnStart, yPosition + 3);
        
        // 2. Current user brand name (reduced font size)
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'bold');
        pdf.text(headerCompanyName, rightColumnStart, yPosition + 7);
        
        // 3. Calendar export info (reduced font size)
        pdf.setFontSize(8);
        pdf.setFont('helvetica', 'normal');
        pdf.text(`Calendar Export: Generated on ${currentDate} / Total Events: ${calendarEvents.value.length}`, rightColumnStart, yPosition + 12);
        
        yPosition += headerHeight;

        // Add horizontal line
        pdf.line(margin, yPosition, pageWidth - margin, yPosition);
        yPosition += 4;

        // Get date range from events
        const sortedEvents = [...calendarEvents.value].sort((a, b) => new Date(a.start) - new Date(b.start));

        // Group events by day
        const eventsByDay = {};
        sortedEvents.forEach(event => {
            const eventDate = new Date(event.start);
            const dayKey = eventDate.toLocaleDateString('en-US', { 
                weekday: 'long',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            if (!eventsByDay[dayKey]) {
                eventsByDay[dayKey] = [];
            }
            eventsByDay[dayKey].push(event);
        });

        // Render events day by day
        Object.keys(eventsByDay).forEach((dayKey, dayIndex) => {
            const dayEvents = eventsByDay[dayKey];
            
            // Check if we need a new page (leave space for day header + at least 3 appointments)
            if (yPosition > pageHeight - 60) {
                pdf.addPage();
                yPosition = margin;
            }

            // Day header
            pdf.setFontSize(9);
            pdf.setFont('helvetica', 'bold');
            pdf.text(dayKey, margin, yPosition);
            yPosition += 5;

            // Draw separator line
            pdf.setDrawColor(200, 200, 200);
            pdf.setLineWidth(0.2);
            pdf.line(margin, yPosition, pageWidth - margin, yPosition);
            yPosition += 3;

            // Column widths for compact two-column layout
            const timeColWidth = 30; // Width for time column
            const timeColX = margin;
            const detailsColX = margin + timeColWidth + 3;
            const detailsColWidth = contentWidth - timeColWidth - 3;

            // Render appointments for this day
            dayEvents.forEach((event, index) => {
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);
                const apiData = event.extendedProps.apiData;

                // Check if we need a new page (keep ~18mm per appointment max)
                if (yPosition > pageHeight - 18) {
                    pdf.addPage();
                    yPosition = margin;
                }

                const rowStartY = yPosition;

                // Format time (compact format)
                const formatTime = (date) => {
                    return date.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: false
                    });
                };
                const timeText = `${formatTime(startDate)} - ${formatTime(endDate)}`;

                // TIME COLUMN (LEFT)
                pdf.setFontSize(8);
                pdf.setFont('helvetica', 'normal');
                pdf.text(timeText, timeColX, yPosition + 3);

                // DETAILS COLUMN (RIGHT)
                let detailsY = yPosition;

                // Meeting title (buyer company)
                const buyerData = apiData?.buyers_data?.user_meta?.tfhb_buyers_data || {};
                const buyersCompany = buyerData.company_name || buyerData.travel_agent_name || 'Company';
                
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'bold');
                const titleLines = pdf.splitTextToSize(`Meeting with ${buyersCompany}`, detailsColWidth);
                pdf.text(titleLines[0], detailsColX, detailsY + 3);
                detailsY += 4;

                // Contact person and location
                const buyerName = buyerData.name || buyerData.name_of_participant || apiData.buyers_data?.display_name || 'Contact';
                const location = getBuyerLocation(apiData);
                
                pdf.setFontSize(7);
                pdf.setFont('helvetica', 'normal');
                const contactText = `${buyerName}${location ? ' from ' + location : ''}`;
                const contactLines = pdf.splitTextToSize(contactText, detailsColWidth);
                pdf.text(contactLines[0], detailsColX, detailsY + 2.5);
                detailsY += 3.5;

                // Calculate row height and add separator
                const rowHeight = Math.max(10, detailsY - rowStartY + 2);
                yPosition += rowHeight;

                // Draw light separator line between appointments
                if (index < dayEvents.length - 1) {
                    pdf.setDrawColor(230, 230, 230);
                    pdf.setLineWidth(0.1);
                    pdf.line(margin, yPosition, pageWidth - margin, yPosition);
                    yPosition += 1;
                }
            });

            // Add space between days
            yPosition += 4;
        });

        const fileName = `agenda-${new Date().toISOString().split('T')[0]}.pdf`;
        pdf.save(fileName);
        toast.success('Calendar exported as PDF successfully', { position: 'bottom-right', "autoClose": 1500 });

    } catch (error) {
        console.error('PDF generation error:', error);
        toast.error('PDF generation failed', { position: 'bottom-right', "autoClose": 1500 });
    }
};

const deleteBooking = async ($id, $host) => { 
    let deleteBooking = {
        id: $id,
        host: $host
    }
    try { 
        const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/booking/delete', deleteBooking,{
            headers: {
                'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
            } 
        });

        if (response.data.status) { 
            Booking.bookings = response.data.bookings; 
            Booking.calendarbooking.events = response.data.booking_calendar;  
            toast.success(response.data.message, {
                position: 'bottom-right',
                "autoClose": 1500,
            }); 
        }
    } catch (error) {
        // Handle error silently
    }
}

const sellersAgenda = async (id) => {
  let data = {
    'sellers_id': id
  }
  try {
    
      const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/sellers/sellers-agenda', data, {
          headers: {
              'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
          } 
      });
      
      if (response.data && response.data.status && response.data.agenda) {  
          // Convert API data to calendar events
          const agendaData = Array.isArray(response.data.agenda) ? response.data.agenda : [];
          const convertedEvents = convertApiDataToCalendarEvents(agendaData);
          
          calendarEvents.value = convertedEvents;
          listEvents.value = [...convertedEvents];
          
          // Update calendar with new events
          if (calendarRef.value) {
              const calendarApi = calendarRef.value.getApi();
              if (calendarApi) {
                  calendarApi.removeAllEvents();
                  calendarApi.addEventSource(convertedEvents);
              }
          }
      } else {
          console.warn('Invalid response format from sellers-agenda API');
          calendarEvents.value = [];
          listEvents.value = [];
      }
  } catch (error) {
      console.error('Error fetching sellers agenda:', error);
      calendarEvents.value = [];
      listEvents.value = [];
  } 
}

 
 

const Booking_Status_Callback = (value) => {
    // Update booking status logic here
    toast.success('Status updated successfully', {
        position: 'bottom-right',
        "autoClose": 1500,
    });
}

const truncateString = (str, num) => {
    if (str.length <= num) {
        return str
    }
    return str.slice(0, num) + '...'
}

 
const DownloadBadgePDFWithQRCode = async (user) => {
    try { 
        console.log('Starting PDF generation for user:', user);
        
        // Validate user object
        if (!user) {
            throw new Error('User object is required');
        }
        
        // Extract user data from the complex structure with better fallbacks
        const userName = user.user_data?.name || 
                        (user.first_name && user.last_name ? `${user.first_name} ${user.last_name}`.trim() : '') || 
                        user.display_name || 
                        'User Name';
        
        const userEmail = user.user_data?.email || 
                          user.user_email || 
                          'No Email';
        
        const userRole = user.user_role || 
                        'Participant';
        
        const companyName = user.user_data?.denominazione_operatore_azienda || 
                           user.user_data?.denominazione_operatore_azienda || 
                           'Company Name';
        
        const jobTitle = user.user_data?.job_title || 
                        user.user_data?.incarico || 
                        'Position';
        
        const region = user.user_data?.regione || 'Region';
         
        
        // Create QR code data with more comprehensive information
        const qr_data = `Name: ${userName}  | Role: ${userRole} | Email: ${userEmail}`;
        
        console.log('Generating QR code for data:', qr_data);
        
        // Generate QR code as data URL
        const qrCodeDataURL = await QRCode.toDataURL(qr_data, {
            width: 150,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
        
        console.log('QR code generated successfully');
        
        // Create new PDF document (A4 size)
        const pdf = new jsPDF('portrait', 'mm', 'a4');
        
        // A4 dimensions: 210mm x 297mm
        const pageWidth = 210;
        const pageHeight = 297;
        let backgroundImageUrl = '';
        
        // Determine background image based on user role 
        if (user.user_role === 'buyers' || user.user_role === 'Buyers') {
            backgroundImageUrl = AddonsSettings?.buyers?.badge_pdf_image || '';
        } else if (user.user_role === 'sellers' || user.user_role === 'Sellers') {
            backgroundImageUrl = AddonsSettings?.Sellers?.badge_pdf_image || '';
        } else if (user.user_role === 'exhibitors' || user.user_role === 'Exhibitors') {
            backgroundImageUrl = AddonsSettings?.Exhibitors?.badge_pdf_image || '';
        }
        
        console.log('Background image URL:', backgroundImageUrl);
        
        // Function to create PDF without background
        const createPDFWithoutBackground = () => {
            try {
                console.log('Creating PDF without background');
                
                // Calculate bottom right quadrant positions
                const quadrantWidth = pageWidth / 2;
                const quadrantHeight = pageHeight / 2;
                const startX = quadrantWidth; // Start from right half
                const startY = quadrantHeight; // Start from bottom half
                
                // Add QR code (centered in bottom right quadrant)
                const qrSize = 35; // Reduced to 35mm x 35mm for better proportion
                const qrX = startX + (quadrantWidth - qrSize) / 2;
                const qrY = startY + 70; // Moved up 10mm from 80 to 70
                pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                 
                // Helper function to fit text within available width
                const fitTextToWidth = (text, maxWidth, initialFontSize, minFontSize = 8) => {
                    pdf.setFontSize(initialFontSize);
                    let currentWidth = pdf.getTextWidth(text);
                    let fontSize = initialFontSize;
                    
                    // Try to shrink font size first
                    while (currentWidth > maxWidth && fontSize > minFontSize) {
                        fontSize -= 0.5;
                        pdf.setFontSize(fontSize);
                        currentWidth = pdf.getTextWidth(text);
                    }
                    
                    // If still too wide, split into multiple lines
                    if (currentWidth > maxWidth) {
                        const words = text.split(' ');
                        const lines = [];
                        let currentLine = '';
                        
                        for (let i = 0; i < words.length; i++) {
                            const testLine = currentLine + (currentLine ? ' ' : '') + words[i];
                            const testWidth = pdf.getTextWidth(testLine);
                            
                            if (testWidth > maxWidth && currentLine) {
                                lines.push(currentLine);
                                currentLine = words[i];
                            } else {
                                currentLine = testLine;
                            }
                        }
                        if (currentLine) lines.push(currentLine);
                        
                        return { lines, fontSize };
                    }
                    
                    return { lines: [text], fontSize };
                };
                
                // Add job title (centered in bottom right quadrant, below QR code)
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                // userRole sould be capitalized first word 
                const jobTitleResult = fitTextToWidth(userRole.charAt(0).toUpperCase() + userRole.slice(1), quadrantWidth - 10, 10);
                pdf.setFontSize(jobTitleResult.fontSize);
                let currentY = startY + 110;
                jobTitleResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < jobTitleResult.lines.length - 1) currentY += 5;
                });
                
                // Add user name (centered in bottom right quadrant, below job title)
                currentY += 8;
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'bold');
                const nameResult = fitTextToWidth(userName, quadrantWidth - 10, 14, 9);
                pdf.setFontSize(nameResult.fontSize);
                nameResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < nameResult.lines.length - 1) currentY += 5;
                });

                // Company
                currentY += 7;
                const companyName = (user.user_data?.denominazione_operatore_azienda || '').trim();
 
                pdf.setFont('helvetica', 'normal');
                const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                pdf.setFontSize(companyResult.fontSize);
                companyResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < companyResult.lines.length - 1) currentY += 5;
                });

                // Convert nation object/array to comma-separated string
                currentY += 7;
                const nation = user.user_data.regione || '';
                const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                pdf.setFontSize(nationResult.fontSize);
                nationResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < nationResult.lines.length - 1) currentY += 5;
                }); 
                 
                // Save the PDF
                const fileName = `badge_${companyName.replace(/\s+/g, '_')}_${Date.now()}.pdf`;
                pdf.save(fileName);
                
                console.log('PDF saved successfully:', fileName);
                
                toast.success('Badge PDF generated successfully! (without background)', {
                    position: 'bottom-right',
                    autoClose: 3000,
                });
            } catch (error) {
                console.error('Error creating PDF without background:', error);
                toast.error('Failed to create PDF. Please try again.', {
                    position: 'bottom-right',
                    autoClose: 3000,
                });
            }
        };
        
        // Function to load image and create PDF
        const createPDFWithBackground = () => {
            try {
                console.log('Creating PDF with background');
                
                // Add background image if available
                if (backgroundImageUrl) {
                    pdf.addImage(backgroundImageUrl, 'PNG', 0, 0, pageWidth, pageHeight);
                }
                
                // Calculate bottom right quadrant positions
                const quadrantWidth = pageWidth / 2;
                const quadrantHeight = pageHeight / 2;
                const startX = quadrantWidth; // Start from right half
                const startY = quadrantHeight; // Start from bottom half
                
                // Add QR code (centered in bottom right quadrant)
                const qrSize = 35; // Reduced to 35mm x 35mm for better proportion
                const qrX = startX + (quadrantWidth - qrSize) / 2;
                const qrY = startY + 70; // Moved up 10mm from 80 to 70
                pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                
                // Helper function to fit text within available width
                const fitTextToWidth = (text, maxWidth, initialFontSize, minFontSize = 8) => {
                    pdf.setFontSize(initialFontSize);
                    let currentWidth = pdf.getTextWidth(text);
                    let fontSize = initialFontSize;
                    
                    // Try to shrink font size first
                    while (currentWidth > maxWidth && fontSize > minFontSize) {
                        fontSize -= 0.5;
                        pdf.setFontSize(fontSize);
                        currentWidth = pdf.getTextWidth(text);
                    }
                    
                    // If still too wide, split into multiple lines
                    if (currentWidth > maxWidth) {
                        const words = text.split(' ');
                        const lines = [];
                        let currentLine = '';
                        
                        for (let i = 0; i < words.length; i++) {
                            const testLine = currentLine + (currentLine ? ' ' : '') + words[i];
                            const testWidth = pdf.getTextWidth(testLine);
                            
                            if (testWidth > maxWidth && currentLine) {
                                lines.push(currentLine);
                                currentLine = words[i];
                            } else {
                                currentLine = testLine;
                            }
                        }
                        if (currentLine) lines.push(currentLine);
                        
                        return { lines, fontSize };
                    }
                    
                    return { lines: [text], fontSize };
                };
 
                // Add job title (centered in bottom right quadrant, below QR code)
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                const jobTitleResult = fitTextToWidth(userRole.charAt(0).toUpperCase() + userRole.slice(1), quadrantWidth - 10, 10);
                pdf.setFontSize(jobTitleResult.fontSize);
                let currentY = startY + 110;
                jobTitleResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < jobTitleResult.lines.length - 1) currentY += 5;
                });
                
                // Add user name (centered in bottom right quadrant, below job title)
                currentY += 8;
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'bold');
                const nameResult = fitTextToWidth(userName, quadrantWidth - 10, 14, 9);
                pdf.setFontSize(nameResult.fontSize);
                nameResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < nameResult.lines.length - 1) currentY += 5;
                });
                

                // Company
                currentY += 7;
                const companyName = (user.user_data?.denominazione_operatore_azienda || '').trim(); 
                pdf.setFont('helvetica', 'normal');
                const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                pdf.setFontSize(companyResult.fontSize);
                companyResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < companyResult.lines.length - 1) currentY += 5;
                });

                // Convert nation object/array to comma-separated string
                currentY += 7;
                const nation = user.user_data.regione || '';
                const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                pdf.setFontSize(nationResult.fontSize);
                nationResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < nationResult.lines.length - 1) currentY += 5;
                }); 

                // Save the PDF
                const fileName = `badge_${companyName.replace(/\s+/g, '_')}_${Date.now()}.pdf`;
                pdf.save(fileName);
                
                console.log('PDF saved successfully:', fileName);
                
                toast.success('Badge PDF generated successfully!', {
                    position: 'bottom-right',
                    autoClose: 3000,
                });
            } catch (error) {
                console.error('Error creating PDF with background:', error);
                // Fallback to creating PDF without background
                createPDFWithoutBackground();
            }
        };
        
        // Try to load the background image first
        if (backgroundImageUrl) {
            console.log('Attempting to load background image');
            const img = new Image();
            img.crossOrigin = 'anonymous'; // Handle CORS if needed
            
            img.onload = () => {
                console.log('Background image loaded successfully');
                try {
                    createPDFWithBackground();
                } catch (error) {
                    console.error('Error creating PDF with background:', error);
                    // Fallback to creating PDF without background
                    createPDFWithoutBackground();
                }
            };
            
            img.onerror = () => {
                console.warn('Background image failed to load, creating PDF without background');
                createPDFWithoutBackground();
            };
            
            // Start loading the image
            img.src = backgroundImageUrl;
        } else {
            console.log('No background image, creating PDF directly');
            // No background image, create PDF directly
            createPDFWithoutBackground();
        }
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        toast.error(`Failed to generate PDF: ${error.message}`, {
            position: 'bottom-right',
            autoClose: 5000,
        });
    }
}

const DownloadStaffBadgePDFWithQRCode = async (user) => {
    try { 
        console.log('Starting staff badge generation for user:', user);
        
        // Validate user object
        if (!user || !user.user_data) {
            throw new Error('User object or user data is required');
        }
        
        // Get staff array from user data
        const staffArray = user.user_data.staff || [];
        console.log('Staff array:', staffArray);
        
        if (!Array.isArray(staffArray) || staffArray.length === 0) {
            toast.warning('No staff members found', {
                position: 'bottom-right',
                autoClose: 3000,
            });
            return;
        }
        
        // Filter staff members who are present at event
        const presentStaff = staffArray.filter(member => member.is_present_at_event === '1' || member.is_present_at_event === 1);
        console.log('Present staff:', presentStaff);
        
        if (presentStaff.length === 0) {
            toast.warning('No staff members marked as present at event', {
                position: 'bottom-right',
                autoClose: 3000,
            });
            return;
        }
        
        toast.info(`Generating ${presentStaff.length} staff badge(s)...`, {
            position: 'bottom-right',
            autoClose: 2000,
        });
        
        // Create a new JSZip instance
        const zip = new JSZip();
        
        // Get company name from seller's user data
        const companyName = (user.user_data?.denominazione_operatore_azienda || '').trim(); 
        
        // Get background image for staff badges (using sellers badge template)
        const backgroundImageUrl = AddonsSettings?.Sellers?.badge_pdf_image || '';
        
        // Generate PDF for each present staff member
        for (let i = 0; i < presentStaff.length; i++) {
            const member = presentStaff[i];
            const staffName = member.name || `Staff ${i + 1}`;
            const staffPosition = member.position || 'Staff';
            
            try {
                // Create QR code data for staff member (similar format to seller badge)
                const qr_data = `Name: ${staffName} | Role: Staff | Position: ${staffPosition} | Company: ${companyName}`;
                
                // Generate QR code as data URL
                const qrCodeDataURL = await QRCode.toDataURL(qr_data, {
                    width: 150,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                });
                
                // Create new PDF document (A4 size)
                const pdf = new jsPDF('portrait', 'mm', 'a4');
                
                // A4 dimensions: 210mm x 297mm
                const pageWidth = 210;
                const pageHeight = 297;
                
                // Add background image if available and valid
                if (backgroundImageUrl && backgroundImageUrl.trim() !== '') {
                    try {
                        // Try to add the background image
                        pdf.addImage(backgroundImageUrl, 'PNG', 0, 0, pageWidth, pageHeight);
                    } catch (imgError) {
                        console.warn(`Could not add background image for ${staffName}:`, imgError);
                        // Continue without background image
                    }
                }
                
                // Calculate bottom right quadrant positions
                const quadrantWidth = pageWidth / 2;
                const quadrantHeight = pageHeight / 2;
                const startX = quadrantWidth; // Start from right half
                const startY = quadrantHeight; // Start from bottom half
                
                // Add QR code (centered in bottom right quadrant)
                const qrSize = 35;
                const qrX = startX + (quadrantWidth - qrSize) / 2;
                const qrY = startY + 70;
                pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                
                // Helper function to fit text within available width (same as seller badge)
                const fitTextToWidth = (text, maxWidth, initialFontSize, minFontSize = 8) => {
                    pdf.setFontSize(initialFontSize);
                    let currentWidth = pdf.getTextWidth(text);
                    let fontSize = initialFontSize;
                    
                    // Try to shrink font size first
                    while (currentWidth > maxWidth && fontSize > minFontSize) {
                        fontSize -= 0.5;
                        pdf.setFontSize(fontSize);
                        currentWidth = pdf.getTextWidth(text);
                    }
                    
                    // If still too wide, split into multiple lines
                    if (currentWidth > maxWidth) {
                        const words = text.split(' ');
                        const lines = [];
                        let currentLine = '';
                        
                        for (let i = 0; i < words.length; i++) {
                            const testLine = currentLine + (currentLine ? ' ' : '') + words[i];
                            const testWidth = pdf.getTextWidth(testLine);
                            
                            if (testWidth > maxWidth && currentLine) {
                                lines.push(currentLine);
                                currentLine = words[i];
                            } else {
                                currentLine = testLine;
                            }
                        }
                        if (currentLine) lines.push(currentLine);
                        
                        return { lines, fontSize };
                    }
                    
                    return { lines: [text], fontSize };
                };
                
                // Add role/position label (centered in bottom right quadrant, below QR code)
                // Using "Staff" as the role label (similar to "sellers" in seller badge)
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                const roleLabel = 'Sellers';
                const roleLabelResult = fitTextToWidth(roleLabel, quadrantWidth - 10, 10);
                pdf.setFontSize(roleLabelResult.fontSize);
                let currentY = startY + 110;
                roleLabelResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < roleLabelResult.lines.length - 1) currentY += 5;
                });
                
                // Add staff name (centered in bottom right quadrant, below role - BOLD and larger)
                currentY += 8;
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'bold');
                const nameResult = fitTextToWidth(staffName, quadrantWidth - 10, 14, 9);
                pdf.setFontSize(nameResult.fontSize);
                nameResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < nameResult.lines.length - 1) currentY += 5;
                });
                
                // Add company name (centered, below name)
                currentY += 7;
                pdf.setFont('helvetica', 'normal');
                const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                pdf.setFontSize(companyResult.fontSize);
                companyResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < companyResult.lines.length - 1) currentY += 5;
                });
                
                // Add position (centered, below company - this is like the region field in seller badge)
                currentY += 7;
                pdf.setFont('helvetica', 'normal');
                const positionResult = fitTextToWidth(staffPosition, quadrantWidth - 10, 9);
                pdf.setFontSize(positionResult.fontSize);
                positionResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < positionResult.lines.length - 1) currentY += 5;
                });
                
                // Get PDF as blob
                const pdfBlob = pdf.output('blob');
                
                // Add to zip file with sanitized filename
                const fileName = `staff_badge_${companyName.replace(/\s+/g, '_')}_${staffName.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '')}_${Date.now()}_${i}.pdf`;
                zip.file(fileName, pdfBlob);
                
                console.log(`PDF generated for staff: ${staffName}`);
                
            } catch (error) {
                console.error(`Error generating PDF for staff ${staffName}:`, error);
                toast.error(`Failed to generate badge for ${staffName}`, {
                    position: 'bottom-right',
                    autoClose: 3000,
                });
            }
        }
        
        // Generate zip file
        const zipBlob = await zip.generateAsync({ type: 'blob' });
        
        // Create download link
        const link = document.createElement('a');
        link.href = URL.createObjectURL(zipBlob);
        link.download = `staff_badges_${companyName.replace(/\s+/g, '_')}_${Date.now()}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up object URL
        URL.revokeObjectURL(link.href);
        
        toast.success(`Successfully generated ${presentStaff.length} staff badge(s) as ZIP file!`, {
            position: 'bottom-right',
            autoClose: 3000,
        });
        
    } catch (error) {
        console.error('Error downloading staff badge PDF:', error);
        toast.error('Failed to generate staff badges. Please try again.', {
            position: 'bottom-right',
            autoClose: 3000,
        });
    }
}

onBeforeMount(() => { 
    // Booking.fetchBookings('upcoming');
    // Meeting.fetchMeetings();
    // Host.fetchHosts();
    
    AddonsSettings.FetchAddonsSettings();
    // Load seller's agenda data
    if (AddonsAuth.loggedInUser?.ID) {
        sellersAgenda(AddonsAuth.loggedInUser.ID);
    }
});

onMounted(() => {
    // Initialize responsive detection
    checkScreenSize();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
const redirectToChat = (user_id) => { 
    AddonsAuth.chat_user_id = user_id;
    router.push({ name: 'HydraAddonsMessages' });
}
</script>

<template> 
<!-- Booking Calendar View -->
<div class="tfhb-booking-calendar-container">
    <!-- Calendar Header Controls -->
    <div class="tfhb-calendar-header tfhb-flexbox tfhb-justify-between tfhb-align-center tfhb-mb-24">
        <div class="tfhb-calendar-navigation tfhb-flexbox tfhb-gap-16">
            <!-- View Toggle Buttons -->
            <div class="tfhb-view-toggle tfhb-flexbox tfhb-gap-8">
                <button 
                    class="tfhb-btn boxed-btn" 
                    :class="currentView === 'calendar' ? 'active' : ''"
                    @click="toggleView('calendar')"
                >
                    <Icon name="Calendar" size=16 />
                    {{ $tfhb_trans('Calendar') }}
                </button>
                <button 
                    class="tfhb-btn boxed-btn" 
                    :class="currentView === 'list' ? 'active' : ''"
                    @click="toggleView('list')"
                >
                    <Icon name="List" size=16 />
                    {{ $tfhb_trans('List') }}
                </button>
            </div>
            
            <!-- Calendar View Toggle (only show when in calendar view) -->
            
        </div>
        
        <div v-if="currentView === 'calendar'" class="tfhb-calendar-date-display tfhb-flexbox tfhb-gap-16 tfhb-align-center">
          <div  class="tfhb-calendar-view-toggle tfhb-flexbox tfhb-gap-8">
                <button 
                    class="tfhb-btn boxed-btn" 
                    :class="calendarView === 'timeGridDay' ? 'active' : ''"
                    @click="changeCalendarView('timeGridDay')"
                >
                    <Icon name="CalendarDays" size=16 />
                    {{ $tfhb_trans('Day') }}
                </button>
                <button 
                    class="tfhb-btn boxed-btn" 
                    :class="calendarView === 'timeGridWeek' ? 'active' : ''"
                    @click="changeCalendarView('timeGridWeek')"
                >
                    <Icon name="Calendar" size=16 />
                    {{ $tfhb_trans('Week') }}
                </button>
                <button 
                    class="tfhb-btn boxed-btn" 
                    :class="calendarView === 'dayGridMonth' ? 'active' : ''"
                    @click="changeCalendarView('dayGridMonth')"
                >
                    <Icon name="Grid3X3" size=16 />
                    {{ $tfhb_trans('Month') }}
                </button>
            </div>
            <div class="tfhb-current-date">
                {{ formatCurrentDate() }}
            </div>
        </div>
    </div>

         <!-- Search and Filter Controls -->
     <div class="tfhb-calendar-controls tfhb-flexbox tfhb-justify-between tfhb-align-center tfhb-mb-24">
      
        <!-- Export Buttons -->
        <div class="tfhb-export-buttons tfhb-flexbox tfhb-gap-8">
            <button class="tfhb-btn secondary-btn" @click="exportCalendar('iCal')">
                <Icon name="FileDown" size=16 />
                Export as .iCal
            </button>
            <button class="tfhb-btn secondary-btn" @click="exportCalendar('PDF')">
                <Icon name="FileText" size=16 />
               Export Calendar PDF
            </button> 
             <button class="tfhb-btn secondary-btn" @click="DownloadBadgePDFWithQRCode(AddonsAuth.loggedInUser)" :disabled="!AddonsAuth.loggedInUser">
                 <Icon name="FileText" size=16 />
                Export Badge PDF
             </button>
            <button class="tfhb-btn secondary-btn" @click="DownloadStaffBadgePDFWithQRCode(AddonsAuth.loggedInUser)" :disabled="!AddonsAuth.loggedInUser">
                <Icon name="FileText" size=16 />
               Export Present Staff Badge
            </button>
        </div>


        <!-- Search Input - Only visible in list view -->
        <div v-if="currentView === 'list'" class="tfhb-search-filters tfhb-flexbox tfhb-gap-16">
            <div class="tfhb-search-box">
                <input 
                    type="text" 
                    v-model="searchQuery"
                    placeholder="Search appointments..."
                    @input="handleSearch"
                />
                <Icon name="Search" size=16 />
            </div>
        </div>
    </div>

    <!-- Calendar and Details Layout -->
    <div class="tfhb-calendar-layout" :class="{ 'with-details': showDetailsPanel }">
        <!-- Calendar View -->
        <div v-if="currentView === 'calendar'" class="tfhb-booking-calendar">  
            <FullCalendar 
                ref="calendarRef"
                class='demo-app-calendar' 
                :options='calendarOptions'
            >
                <template v-slot:eventContent='arg'>
                    <div class="tfhb-calendar-event-content">
                        <div class="tfhb-event-title">{{ truncateString(arg.event.title, 30) }}</div>
                        <div class="tfhb-event-time">{{ arg.event.extendedProps.booking_time }}</div>
                        <div class="tfhb-event-status" :class="arg.event.extendedProps.status">
                            {{ arg.event.extendedProps.status }}
                        </div>
                    </div>
                </template>
            </FullCalendar>
        </div>

        <!-- List View -->
        <div v-if="currentView === 'list'" class="tfhb-list-view">
            <div class="tfhb-list-header">
                <div class="tfhb-list-info">
                    <h3>{{ $tfhb_trans('Appointments') }}</h3>
                    <span class="tfhb-list-count">
                        {{ filteredListEvents.length }} {{ $tfhb_trans('appointments') }}
                        <span v-if="searchQuery.trim() && filteredListEvents.length !== listEvents.length">
                            ({{ $tfhb_trans('filtered') }})
                        </span>
                    </span>
                </div>
            </div>
            
            <div class="tfhb-list-container">
                <div v-if="paginatedEvents.length === 0" class="tfhb-empty-state">
                    <Icon name="CalendarX" size=48 />
                    <h4>{{ $tfhb_trans('No appointments found') }}</h4>
                    <p>{{ $tfhb_trans('There are no appointments to display at the moment.') }}</p>
                </div>
                
                <div v-else class="tfhb-list-table">
                    <!-- Table Header -->
                    <div class="tfhb-table-header">
                        <div class="tfhb-table-cell tfhb-cell-title">{{ $tfhb_trans('Appointment') }}</div>
                        <div class="tfhb-table-cell tfhb-cell-date">{{ $tfhb_trans('Date') }}</div>
                        <div class="tfhb-table-cell tfhb-cell-time">{{ $tfhb_trans('Time') }}</div>
                        <div class="tfhb-table-cell tfhb-cell-contact">{{ $tfhb_trans('Contact') }}</div>
                        <div class="tfhb-table-cell tfhb-cell-status">{{ $tfhb_trans('Status') }}</div>
                        <div class="tfhb-table-cell tfhb-cell-actions">{{ $tfhb_trans('Actions') }}</div>
                    </div>
                    
                    <!-- Table Rows -->
                    <div 
                        v-for="event in paginatedEvents" 
                        :key="event.id" 
                        class="tfhb-table-row" 
                        @click="handleListEventClick(event)"
                    >
                    <!-- {{ event.extendedProps.apiData.buyers_data.user_meta.tfhb_buyers_data.travel_agent_name }} -->
                        <div class="tfhb-table-cell tfhb-cell-title">
                            <div class="tfhb-cell-content">
                                <h4>{{ event.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.travel_agent_name || getBuyerName(event.extendedProps.apiData) }}</h4>
                           
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-date">
                            <div class="tfhb-cell-content">
                                <Icon name="Calendar" size=14 />
                                <span>{{ new Date(event.start).toLocaleDateString('en-US', { 
                                    month: 'short', 
                                    day: 'numeric',
                                    year: 'numeric'
                                }) }}</span>
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-time">
                            <div class="tfhb-cell-content">
                                <Icon name="Clock" size=14 />
                                <span>{{ event.extendedProps.booking_time }}</span>
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-contact">
                            <div class="tfhb-cell-content">
                                <Icon name="User" size=14 />
                                <span>{{ getBuyerName(event.extendedProps.apiData) }}</span>
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-status">
                            <div class="tfhb-cell-content">
                                <span class="tfhb-status-badge" :class="event.extendedProps.status">
                                    {{ event.extendedProps.status }}
                                </span>
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-actions">
                            <div class="tfhb-cell-content">
                                <Icon name="ChevronRight" size=16 />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="totalPages > 1" class="tfhb-pagination">
                <button 
                    class="tfhb-page-btn" 
                    :disabled="currentPage === 1"
                    @click="prevPage"
                >
                    <Icon name="ChevronLeft" size=16 />
                    {{ $tfhb_trans('Previous') }}
                </button>
                
                <div class="tfhb-page-numbers">
                    <button 
                        v-for="page in totalPages" 
                        :key="page"
                        class="tfhb-page-btn" 
                        :class="{ active: currentPage === page }"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>
                </div>
                
                <button 
                    class="tfhb-page-btn" 
                    :disabled="currentPage === totalPages"
                    @click="nextPage"
                >
                    {{ $tfhb_trans('Next') }}
                    <Icon name="ChevronRight" size=16 />
                </button>
            </div>
        </div>

        <!-- Details Panel (for desktop views) -->
        <div v-if="showDetailsPanel && selectedEventData && !isSmallScreen" class="tfhb-details-panel">
            <div class="tfhb-details-header">
                <h3>Buyer Details</h3>
                <div class="tfhb-details-actions">
                   <!-- <span class="match-percentage-large">{{ selectedExhibitor.data.matchPercentage }}% Mach</span>s -->
                    <!-- {{ selectedEventData.extendedProps.apiData.buyers_data }} -->
                   <button class="action-btn" @click="redirectToChat(selectedEventData.extendedProps.apiData.buyers_data.ID)">
                        <Icon name="MessageCircle" size=16 />
                    </button>
                    <!-- <button class="action-btn">
                        <Icon name="MoreVertical" size=16 />
                    </button> -->
                    <a :href="'#/buyer-list/profile/'+selectedEventData.extendedProps.apiData.buyers_data.ID" class="action-btn" style="font-size: 15px;">
                        View
                    </a>
                    <Icon name="X" size=16 @click="closeDetailsPanel" class="tfhb-close-btn" />
                </div>
            </div>

            <div class="tfhb-company-info" v-if="selectedEventData.extendedProps.apiData">
                <div class="tfhb-company-logo">
                  <img v-if="selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.avatar" :src="selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.avatar" alt="Sellers Avatar"
                  :style="{
                    'width': '80px',
                    'height': '80px', 
                    'border-radius': '50%'
                  }"
                  >
                  <img v-else :src="$tfhb_url+'/assets/images/avator.png'" alt="Sellers Avatar"
                  :style="{
                    'width': '80px',
                    'height': '80px', 
                    'border-radius': '50%'
                  }">
                </div>
                <h4 class="tfhb-company-name">{{ selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.travel_agent_name || getBuyerName(selectedEventData?.extendedProps?.apiData) }}</h4>
                 <!-- selectedEventData.buyers_data.user_meta.tfhb_buyers_data.travel_agent_name }}</h4> -->
            </div>

            <div class="tfhb-details-section" v-if="selectedEventData.extendedProps.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.description">
                <label class="tfhb-section-label">DESCRIPTION</label>
                <p class="tfhb-description-text">
                    {{ selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.description || 'No description available' }}
                </p>
            </div>

            <div class="tfhb-details-section">
                <label class="tfhb-section-label">CONTACT PERSON</label>
                <div class="tfhb-staff-info">
                    <div class="tfhb-staff-avatar">
                        <Icon name="User" size=20 />
                    </div>
                    <div class="tfhb-staff-details">
                        <span class="tfhb-staff-name">
                            {{ getBuyerName(selectedEventData.extendedProps.apiData) }}
                        </span>
                        <span class="tfhb-staff-role">
                            {{ selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.job_title || 'Travel Agent' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="tfhb-details-section" v-if="getCompanyWebsite(selectedEventData.extendedProps.apiData)">
                <label class="tfhb-section-label">WEBSITE</label>
                <p class="tfhb-contact-info">{{ getCompanyWebsite(selectedEventData.extendedProps.apiData) }}</p>
            </div>

            <div class="tfhb-details-section">
                <label class="tfhb-section-label">EMAIL</label>
                <p class="tfhb-contact-info">{{ selectedEventData.extendedProps.apiData?.buyers_data?.user_email || 'No email available' }}</p>
            </div>

            <div class="tfhb-details-section" v-if="getAddress(selectedEventData.extendedProps.apiData)">
                <label class="tfhb-section-label">ADDRESS</label>
                <p class="tfhb-contact-info">{{ getAddress(selectedEventData.extendedProps.apiData) }}</p>
            </div>

            <div class="tfhb-details-section" v-if="getAreasOfActivity(selectedEventData.extendedProps.apiData).length">
                <label class="tfhb-section-label">AREAS OF ACTIVITY</label>
                <div class="tfhb-activity-tags">
                    <span class="tfhb-tag" v-for="activity in getAreasOfActivity(selectedEventData.extendedProps.apiData)" :key="activity">
                        {{ activity }}
                    </span>
                </div>
            </div>

            <div class="tfhb-details-section" v-if="getPreferredMeetings(selectedEventData.extendedProps.apiData).length">
                <label class="tfhb-section-label">PREFERRED WORKSHOP MEETINGS</label>
                <div class="tfhb-activity-tags">
                    <span class="tfhb-tag" v-for="meeting in getPreferredMeetings(selectedEventData.extendedProps.apiData)" :key="meeting">
                        {{ meeting }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Panel Popup (for mobile/tablet views) -->
    <HbPopup 
        :isOpen="showDetailsPopup" 
        @modal-close="closeDetailsPopup" 
        max_width="500px" 
        name="details-modal" 
        gap="24px" 
        class="tfhb-details-popup"
    >
        <template #header>
            <div class="tfhb-popup-header">
                <h3>Buyer Details</h3>
                <div class="tfhb-popup-actions">
                    <button class="action-btn" @click="redirectToChat(selectedEventData?.extendedProps?.apiData?.buyers_data?.ID)">
                        <Icon name="MessageCircle" size=16 />
                    </button>
                    <a :href="'#/buyer-list/profile/'+selectedEventData?.extendedProps?.apiData?.buyers_data?.ID" class="action-btn" style="font-size: 15px;">
                        View
                    </a>
                </div>
            </div>
        </template>

        <template #content>
            <div v-if="selectedEventData" class="tfhb-popup-details">
                <div class="tfhb-company-info" v-if="selectedEventData.extendedProps.apiData">
                    <div class="tfhb-company-logo">
                      <img v-if="selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.avatar" :src="selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.avatar" alt="Buyer Avatar"
                      :style="{
                        'width': '80px',
                        'height': '80px', 
                        'border-radius': '50%'
                      }"
                      >
                      <img v-else :src="$tfhb_url+'/assets/images/avator.png'" alt="Buyer Avatar"
                      :style="{
                        'width': '80px',
                        'height': '80px', 
                        'border-radius': '50%'
                      }">
                    </div>
                    <h4 class="tfhb-company-name">{{ selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.travel_agent_name || getBuyerName(selectedEventData?.extendedProps?.apiData) }}</h4>
                </div>

                <div class="tfhb-details-section" v-if="selectedEventData.extendedProps.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.description">
                    <label class="tfhb-section-label">DESCRIPTION</label>
                    <p class="tfhb-description-text">
                        {{ selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.description || 'No description available' }}
                    </p>
                </div>

                <div class="tfhb-details-section">
                    <label class="tfhb-section-label">CONTACT PERSON</label>
                    <div class="tfhb-staff-info">
                        <div class="tfhb-staff-avatar">
                            <Icon name="User" size=20 />
                        </div>
                        <div class="tfhb-staff-details">
                            <span class="tfhb-staff-name">
                                {{ getBuyerName(selectedEventData.extendedProps.apiData) }}
                            </span>
                            <span class="tfhb-staff-role">
                                {{ selectedEventData?.extendedProps?.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.job_title || 'Travel Agent' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="tfhb-details-section" v-if="getCompanyWebsite(selectedEventData.extendedProps.apiData)">
                    <label class="tfhb-section-label">WEBSITE</label>
                    <p class="tfhb-contact-info">{{ getCompanyWebsite(selectedEventData.extendedProps.apiData) }}</p>
                </div>

                <div class="tfhb-details-section">
                    <label class="tfhb-section-label">EMAIL</label>
                    <p class="tfhb-contact-info">{{ selectedEventData.extendedProps.apiData?.buyers_data?.user_email || 'No email available' }}</p>
                </div>

                <div class="tfhb-details-section" v-if="getAddress(selectedEventData.extendedProps.apiData)">
                    <label class="tfhb-section-label">ADDRESS</label>
                    <p class="tfhb-contact-info">{{ getAddress(selectedEventData.extendedProps.apiData) }}</p>
                </div>

                <div class="tfhb-details-section" v-if="getAreasOfActivity(selectedEventData.extendedProps.apiData).length">
                    <label class="tfhb-section-label">AREAS OF ACTIVITY</label>
                    <div class="tfhb-activity-tags">
                        <span class="tfhb-tag" v-for="activity in getAreasOfActivity(selectedEventData.extendedProps.apiData)" :key="activity">
                            {{ activity }}
                        </span>
                    </div>
                </div>

                <div class="tfhb-details-section" v-if="getPreferredMeetings(selectedEventData.extendedProps.apiData).length">
                    <label class="tfhb-section-label">PREFERRED WORKSHOP MEETINGS</label>
                    <div class="tfhb-activity-tags">
                        <span class="tfhb-tag" v-for="meeting in getPreferredMeetings(selectedEventData.extendedProps.apiData)" :key="meeting">
                            {{ meeting }}
                        </span>
                    </div>
                </div>
            </div>
        </template>
    </HbPopup>
</div>

<!-- Booking Calendar Edit Popup (for Week/Month View) -->
<!-- HbPopup :isOpen="BookingEditPopup" @modal-close="BookingEditPopup = false" max_width="300px" name="first-modal" gap="24px" class="tfhb-booking-calendar-popup"> -->
<!--     <template #header>  -->
<!--         <h3>{{ truncateString( singleCalendarBookingData.title , 50) }}</h3> -->
<!--     </template> -->

<!--     <template #content>  -->
<!--         <HbDropdown   -->
<!--             v-model="singleCalendarBookingData.status" -->
<!--             :label="$tfhb_trans('Status')"  -->
<!--             :selected = "1"  -->
<!--             :placeholder="$tfhb_trans('Select Booking status')"    -->
<!--             :option = "[ -->
<!--                 {'name': 'Pending', 'value': 'pending'},   -->
<!--                 {'name': 'Confirmed', 'value': 'confirmed'},    -->
<!--                 {'name': 'Completed', 'value': 'completed'},    -->
<!--                 {'name': 'Canceled', 'value': 'canceled'} -->
<!--             ]" -->
<!--             @tfhb-onchange="Booking_Status_Callback"  -->
<!--         />   -->

<!--         <div class="tfhb-single-form-field" style="width: 100%;"> -->
<!--             <div class="tfhb-single-form-field-wrap tfhb-field-input"> -->
<!--                 <label>{{$tfhb_trans('Date')}}</label> -->
<!--                 <div class="tfhb-time-date-view tfhb-flexbox"> -->
<!--                     <Icon name="CalendarDays" size=20 /> -->
<!--                     <input type="text" readonly :value="singleCalendarBookingData.booking_date"> -->
<!--                 </div> -->
<!--             </div> -->
<!--         </div> -->

<!--         <div class="tfhb-single-form-field" style="width: 100%;"> -->
<!--             <div class="tfhb-single-form-field-wrap tfhb-field-input"> -->
<!--                 <label>{{$tfhb_trans('Time')}}</label> -->
<!--                 <div class="tfhb-time-date-view tfhb-flexbox"> -->
<!--                     <Icon name="Clock4" size=20 /> -->
<!--                     <input type="text" readonly :value="singleCalendarBookingData.booking_time"> -->
<!--                 </div> -->
<!--             </div> -->
<!--         </div> -->

<!--         <div class="tfhb-popup-actions tfhb-flexbox tfhb-full-width tfhb-gap-8"> -->
<!--             <HbButton   -->
<!--                 classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8"  -->
<!--                 @click="deleteBooking(singleCalendarBookingData.booking_id, singleCalendarBookingData.host_id)" -->
<!--                 :buttonText="$tfhb_trans('Delete')" -->
<!--                 icon="Trash2"    -->
<!--                 :hover_animation="false"  -->
<!--                 icon_position = 'left' -->
<!--             />  -->

<!--             <HbButton   -->
<!--                 @click.stop="router.push({ name: 'bookingDetails', params: { id: singleCalendarBookingData.booking_id } })" -->
<!--                 classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8"   -->
<!--                 :buttonText="$tfhb_trans('View')" -->
<!--                 icon="Eye"    -->
<!--                 :hover_animation="false"  -->
<!--                 icon_position = 'left' -->
<!--             />  -->
<!--         </div> -->
<!--     </template>  -->
<!-- </HbPopup> -->
</template>

<style scoped> 

.action-btn {
	border: none !important;
	background-color: transparent;
}
.tfhb-appointments-container {
  padding: 24px;
  background: var(--tfhb-surface-background-color, #f8f9fa);
  min-height: 100vh;
}
.tfhb-btn.secondary-btn.active{
  color: #fff !important;
}
.tfhb-btn.secondary-btn {
	display: flex;
	align-items: center;
	gap: 8px;
}
/* Calendar Styles */
.tfhb-booking-calendar-container {
  background: #fff;
  border-radius: 12px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e8e8e8;
}

.tfhb-calendar-header {
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 20px;
  margin-bottom: 24px;
}

.tfhb-calendar-view-toggle .tfhb-btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid #e0e0e0;
  background: #fafafa;
  color: #666;
  min-width: 80px;
}

.tfhb-calendar-view-toggle .tfhb-btn:hover {
  background: #f0f0f0;
  border-color: #d0d0d0;
  transform: translateY(-1px);
}

.tfhb-calendar-view-toggle .tfhb-btn.active { 
  color: #fff !important; 
}

.tfhb-current-date {
  font-size: 20px;
  font-weight: 700;
  color: #333;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 12px 20px;
  border-radius: 10px;
  border: 1px solid #e0e0e0;
}

.tfhb-booking-calendar {
  min-height: 700px;
  background: #fafafa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e8e8e8;
}

/* Calendar Event Styles */
.tfhb-calendar-event-content {
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.95);
  border-left: 4px solid #4CAF50;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
}

.tfhb-event-title {
  font-weight: 700;
  font-size: 13px;
  color: #2c3e50;
  margin-bottom: 4px;
  line-height: 1.3;
}

.tfhb-event-time {
  font-size: 11px;
  color: #7f8c8d;
  margin-bottom: 4px;
  font-weight: 500;
}

.tfhb-event-status {
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.tfhb-event-status.confirmed {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.tfhb-event-status.pending {
  background: linear-gradient(135deg, #fff3e0, #ffcc80);
  color: #f57c00;
  border: 1px solid #ffb74d;
}

.tfhb-event-status.canceled {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  color: #c62828;
  border: 1px solid #ef9a9a;
}

/* FullCalendar Customization */
:deep(.fc) {
  font-family: inherit;
  background: transparent;
  min-width: 100%;
}

:deep(.fc-view-harness) {
  min-width: 100%;
}

:deep(.fc-scroller) {
  overflow: visible !important;
}

:deep(.fc-scroller-liquid) {
  overflow: visible !important;
}

:deep(.fc-toolbar) {
  margin-bottom: 20px;
  padding: 0;
}

:deep(.fc-toolbar-title) {
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
  text-transform: capitalize;
}

:deep(.fc-button) {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border: 2px solid #dee2e6;
  color: #495057;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 14px;
}

:deep(.fc-button:hover) {
  background: linear-gradient(135deg, #e9ecef, #dee2e6);
  border-color: #adb5bd;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

:deep(.fc-button:active) {
  transform: translateY(0);
}

:deep(.fc-event) {
  border-radius: 8px;
  border: none;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  cursor: pointer;
}

:deep(.fc-event:hover) {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  transform: translateY(-2px);
}

:deep(.fc-timegrid-slot) {
  height: 70px;
  border-color: #e8e8e8;
}

:deep(.fc-timegrid-slot-label) {
  font-size: 13px;
  color: #6c757d;
  font-weight: 600;
}

:deep(.fc-daygrid-day) {
  min-height: 120px;
  border-color: #e8e8e8;
}

:deep(.fc-daygrid-day-number) {
  font-weight: 700;
  color: #2c3e50;
  font-size: 16px;
}

:deep(.fc-col-header-cell) {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-color: #dee2e6;
  font-weight: 700;
  color: #495057;
  padding: 12px 0;
}

:deep(.fc-timegrid-axis) {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-color: #dee2e6;
}

:deep(.fc-day-today) {
  background: rgba(76, 175, 80, 0.1) !important;
}

:deep(.fc-day-today .fc-daygrid-day-number) {
  background: #4CAF50;
  color: white;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Calendar Controls Styles */
.tfhb-calendar-controls {
  background: #f8f9fa;
  padding: 16px 24px;
  border-radius: 10px;
  border: 1px solid #e8e8e8;
  margin-bottom: 24px;
}

.tfhb-search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.tfhb-search-box input {
  padding: 10px 16px 10px 40px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  width: 250px;
  background: white;
  transition: all 0.3s ease;
}

.tfhb-search-box input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.tfhb-search-box svg {
  position: absolute;
  left: 12px;
  color: #666;
}

.tfhb-filter-dropdown select {
  padding: 10px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  color: #333;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
}

.tfhb-filter-dropdown select:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.tfhb-export-buttons .tfhb-btn {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid #e0e0e0;
  background: white;
  color: #333;
}

.tfhb-export-buttons .tfhb-btn:hover {
  background: #f0f0f0;
  border-color: #d0d0d0;
  transform: translateY(-1px);
}

/* Calendar Layout */
.tfhb-calendar-layout {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}

.tfhb-calendar-layout.with-details .tfhb-booking-calendar,
.tfhb-calendar-layout.with-details .tfhb-list-view {
  flex: 1;
  max-width: calc(100% - 374px); /* 350px panel + 24px gap */
  min-width: 800px; /* Increased minimum width to accommodate all columns */
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-width: thin;
  scrollbar-color: #c1c1c1 #f1f1f1;
}

.tfhb-calendar-layout.with-details .tfhb-booking-calendar::-webkit-scrollbar,
.tfhb-calendar-layout.with-details .tfhb-list-view::-webkit-scrollbar {
  height: 8px;
}

.tfhb-calendar-layout.with-details .tfhb-booking-calendar::-webkit-scrollbar-track,
.tfhb-calendar-layout.with-details .tfhb-list-view::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.tfhb-calendar-layout.with-details .tfhb-booking-calendar::-webkit-scrollbar-thumb,
.tfhb-calendar-layout.with-details .tfhb-list-view::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.tfhb-calendar-layout.with-details .tfhb-booking-calendar::-webkit-scrollbar-thumb:hover,
.tfhb-calendar-layout.with-details .tfhb-list-view::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.tfhb-calendar-layout:not(.with-details) .tfhb-booking-calendar,
.tfhb-calendar-layout:not(.with-details) .tfhb-list-view {
  flex: 1;
  width: 100%;
}

.tfhb-booking-calendar {
  min-height: 700px;
  background: #fafafa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e8e8e8;
}

/* Details Panel Styles */
.tfhb-details-panel {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e8e8e8;
  width: 350px;
  height: fit-content;
  position: sticky;
  top: 24px;
  max-height: calc(100vh - 48px);
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #c1c1c1 #f1f1f1;
  flex-shrink: 0; /* Prevent panel from shrinking */
}

.tfhb-details-panel::-webkit-scrollbar {
  width: 6px;
}

.tfhb-details-panel::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.tfhb-details-panel::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.tfhb-details-panel::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.tfhb-details-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.tfhb-details-header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #333;
}

.tfhb-details-actions {
  display: flex;
  gap: 12px;
  color: #666;
}

.tfhb-details-actions svg {
  cursor: pointer;
  transition: color 0.3s ease;
}

.tfhb-details-actions svg:hover {
  color: #4CAF50;
}

.tfhb-close-btn {
  cursor: pointer;
  transition: color 0.3s ease;
}

.tfhb-close-btn:hover {
  color: #e74c3c; /* A red color for the close button */
}

.tfhb-company-info {
  text-align: center;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.tfhb-company-logo {
  margin-bottom: 16px;
}

.tfhb-logo-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4CAF50, #45a049);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  padding: 12px;
}

.tfhb-logo-text {
  font-size: 16px;
  font-weight: 700;
  color: white;
  line-height: 1;
  margin-bottom: 2px;
}

.tfhb-logo-subtitle {
  font-size: 8px;
  color: rgba(255, 255, 255, 0.8);
  text-align: center;
  line-height: 1;
}

.tfhb-company-name {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #333;
}

.tfhb-details-section {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.tfhb-details-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.tfhb-section-label {
  font-size: 12px;
  font-weight: 600;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  display: block;
}

.tfhb-description-text {
  font-size: 13px;
  color: #666;
  line-height: 1.6;
  margin: 0;
}

.tfhb-read-more {
  color: #4CAF50;
  text-decoration: none;
  font-weight: 600;
  font-size: 12px;
}

.tfhb-read-more:hover {
  text-decoration: underline;
}

.tfhb-staff-info {
  display: flex;
  align-items: center;
}

.tfhb-staff-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  flex-shrink: 0;
  color: #666;
}

.tfhb-staff-details {
  display: flex;
  flex-direction: column;
}

.tfhb-staff-name {
  font-size: 14px;
  font-weight: 700;
  color: #333;
  line-height: 1.2;
}

.tfhb-staff-role {
  font-size: 12px;
  color: #777;
  margin-top: 2px;
}

.tfhb-contact-info {
  font-size: 13px;
  color: #555;
  line-height: 1.5;
  margin: 0;
}

.tfhb-activity-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tfhb-tag {
  background: linear-gradient(135deg, #6c5ce7, #5f3dc4);
  color: white !important;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* View Toggle Styles */
.tfhb-view-toggle {
  display: flex;
  gap: 8px;
}

.tfhb-view-toggle .tfhb-btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid #e0e0e0;
  background: #fafafa;
  color: #666;
  min-width: 100px;
}

.tfhb-view-toggle .tfhb-btn:hover {
  background: #f0f0f0;
  border-color: #d0d0d0;
  transform: translateY(-1px);
}

.tfhb-view-toggle .tfhb-btn.active { 
  color: #fff !important; 
}

/* List View Styles */
.tfhb-list-view {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e8e8e8;
  min-height: 700px;
  width: 100%;
}

.tfhb-list-header {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #f0f0f0;
}

.tfhb-list-info h3 {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 700;
  color: #333;
}

.tfhb-list-count {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.tfhb-list-container {
  min-height: 500px;
  width: 100%;
}

.tfhb-empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.tfhb-empty-state h4 {
  margin: 16px 0 8px 0;
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.tfhb-empty-state p {
  margin: 0;
  font-size: 14px;
  color: #888;
}

/* Table-like List View */
.tfhb-list-table {
  width: 100%;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
}

.tfhb-table-header {
  display: flex;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 2px solid #dee2e6;
  font-weight: 700;
  color: #495057;
  font-size: 14px;
}

.tfhb-table-row {
  display: flex;
  border-bottom: 1px solid #e0e0e0;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fff;
}

.tfhb-table-row:hover {
  background: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tfhb-table-row:last-child {
  border-bottom: none;
}

.tfhb-table-cell {
  padding: 16px 12px;
  display: flex;
  align-items: center;
  border-right: 1px solid #e0e0e0;
  flex: 1;
}

.tfhb-table-cell:last-child {
  border-right: none;
}

.tfhb-cell-content {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.tfhb-cell-title {
  flex: 2;
  min-width: 180px; /* Slightly reduced to give more space to other columns */
}

.tfhb-cell-date {
  flex: 1;
  min-width: 120px;
}

.tfhb-cell-time {
  flex: 1;
  min-width: 100px;
}

.tfhb-cell-contact {
  flex: 1;
  min-width: 140px; /* Slightly reduced to give more space to status column */
}

.tfhb-cell-status {
  flex: 1;
  min-width: 120px; /* Increased minimum width to show full status text */
}

.tfhb-cell-actions {
  flex: 0.5;
  min-width: 80px;
  justify-content: center;
}

.tfhb-cell-title h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  line-height: 1.3;
}

.tfhb-cell-description {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
  line-height: 1.2;
}

.tfhb-status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tfhb-status-badge.confirmed {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.tfhb-status-badge.pending {
  background: linear-gradient(135deg, #fff3e0, #ffcc80);
  color: #f57c00;
  border: 1px solid #ffb74d;
}

.tfhb-status-badge.canceled {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  color: #c62828;
  border: 1px solid #ef9a9a;
}

/* Pagination Styles */
.tfhb-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f0;
}

.tfhb-page-numbers {
  display: flex;
  gap: 4px;
}

.tfhb-page-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  background: white;
  color: #666;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 40px;
  justify-content: center;
}

.tfhb-page-btn:hover:not(:disabled) {
  background: #f0f0f0;
  border-color: #d0d0d0;
  transform: translateY(-1px);
}

.tfhb-page-btn.active {
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  border-color: #4CAF50;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.tfhb-page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .tfhb-calendar-layout.with-details .tfhb-booking-calendar {
    max-width: calc(100% - 400px);
    min-width: 500px;
  }
  
  .tfhb-details-panel {
    width: 320px;
  }
}

/* Additional responsive adjustments for details panel */
@media (min-width: 1025px) and (max-width: 1400px) {
  .tfhb-calendar-layout.with-details .tfhb-booking-calendar,
  .tfhb-calendar-layout.with-details .tfhb-list-view {
    min-width: 700px; /* Adjusted for medium screens */
  }
  
  .tfhb-details-panel {
    width: 320px; /* Slightly smaller panel for medium screens */
  }
}

@media (max-width: 768px) {
  .tfhb-calendar-header {
    flex-direction: column;
    gap: 20px;
  }
  
  .tfhb-calendar-view-toggle,
  .tfhb-view-toggle {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .tfhb-current-date {
    text-align: center;
    font-size: 18px;
  }
  
  .tfhb-booking-calendar-container {
    padding: 20px;
  }
  
  .tfhb-booking-calendar,
  .tfhb-list-view {
    padding: 15px;
  }

  .tfhb-calendar-controls {
    flex-direction: column;
    gap: 16px;
  }

  .tfhb-search-filters {
    flex-direction: column;
    gap: 12px;
  }

  .tfhb-search-box input {
    width: 100%;
  }

  .tfhb-calendar-layout {
    flex-direction: column;
  }

  .tfhb-calendar-layout.with-details .tfhb-booking-calendar,
  .tfhb-calendar-layout.with-details .tfhb-list-view {
    max-width: 100%;
    min-width: 100%;
    overflow-x: auto;
  }

  .tfhb-details-panel {
    margin-left: 0;
    margin-top: 24px;
    width: 100%;
    position: static;
    height: auto;
    overflow-y: visible;
    max-height: none;
  }

  .tfhb-list-table {
    overflow-x: auto;
  }

  .tfhb-table-header,
  .tfhb-table-row {
    min-width: 800px;
  }

  .tfhb-table-cell {
    padding: 12px 8px;
    font-size: 13px;
  }

  .tfhb-cell-title {
    min-width: 150px;
  }

  .tfhb-cell-date {
    min-width: 100px;
  }

  .tfhb-cell-time {
    min-width: 80px;
  }

  .tfhb-cell-contact {
    min-width: 120px;
  }

  .tfhb-cell-status {
    min-width: 100px; /* Increased for mobile to show full status */
  }

  .tfhb-cell-actions {
    min-width: 60px;
  }

  .tfhb-pagination {
    flex-direction: column;
    gap: 16px;
  }

  .tfhb-page-numbers {
    order: -1;
  }
}

/* Details Popup Styles */
.tfhb-details-popup {
  max-height: 100vh;
  overflow-y: auto;
  max-width: 100vw !important;
  width: 100vw !important;
  margin: 0 !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  transform: none !important;
  z-index: 9999 !important;
  border-radius: 0;
  box-shadow: none;
}

.tfhb-popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.tfhb-popup-header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #333;
}

.tfhb-popup-actions {
  display: flex;
  gap: 12px;
  color: #666;
}

.tfhb-popup-actions svg {
  cursor: pointer;
  transition: color 0.3s ease;
}

.tfhb-popup-actions svg:hover {
  color: #4CAF50;
}

.tfhb-popup-details {
  padding: 0;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #c1c1c1 #f1f1f1;
}

.tfhb-popup-details::-webkit-scrollbar {
  width: 6px;
}

.tfhb-popup-details::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.tfhb-popup-details::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.tfhb-popup-details::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.tfhb-popup-details .tfhb-company-info {
  text-align: center;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.tfhb-popup-details .tfhb-company-logo {
  margin-bottom: 16px;
}

.tfhb-popup-details .tfhb-company-name {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #333;
}

.tfhb-popup-details .tfhb-details-section {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #f0f0f0;
}

.tfhb-popup-details .tfhb-details-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.tfhb-popup-details .tfhb-section-label {
  font-size: 12px;
  font-weight: 600;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  display: block;
}

.tfhb-popup-details .tfhb-description-text {
  font-size: 13px;
  color: #666;
  line-height: 1.6;
  margin: 0;
}

.tfhb-popup-details .tfhb-staff-info {
  display: flex;
  align-items: center;
}

.tfhb-popup-details .tfhb-staff-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  flex-shrink: 0;
  color: #666;
}

.tfhb-popup-details .tfhb-staff-details {
  display: flex;
  flex-direction: column;
}

.tfhb-popup-details .tfhb-staff-name {
  font-size: 14px;
  font-weight: 700;
  color: #333;
  line-height: 1.2;
}

.tfhb-popup-details .tfhb-staff-role {
  font-size: 12px;
  color: #777;
  margin-top: 2px;
}

.tfhb-popup-details .tfhb-contact-info {
  font-size: 13px;
  color: #555;
  line-height: 1.5;
  margin: 0;
}

.tfhb-popup-details .tfhb-activity-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tfhb-popup-details .tfhb-tag {
  background: linear-gradient(135deg, #6c5ce7, #5f3dc4);
  color: white !important;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* Overlay and positioning fixes */
.tfhb-details-popup :deep(.modal-overlay) {
  background: rgba(0, 0, 0, 0.7) !important;
  backdrop-filter: blur(5px);
  z-index: 9998 !important;
}

.tfhb-details-popup :deep(.modal-content) {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  transform: none !important;
  max-height: 100vh !important;
  max-width: 100vw !important;
  width: 100vw !important;
  margin: 0 !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  z-index: 9999 !important;
  overflow: hidden;
}

/* Mobile specific adjustments */
@media (max-width: 768px) {
  .tfhb-details-popup :deep(.modal-content) {
    width: 100vw !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
  }
  
  .tfhb-details-popup {
    max-width: 100vw !important;
    width: 100vw !important;
    max-height: 100vh !important;
  }
  
  .tfhb-popup-details {
    max-height: calc(100vh - 100px) !important;
    padding: 0 20px;
  }
}

/* Responsive Design - Hide details panel on screens below 1500px */
@media (max-width: 1499px) {
  .tfhb-details-panel {
    display: none;
  }
  
  .tfhb-calendar-layout.with-details .tfhb-booking-calendar,
  .tfhb-calendar-layout.with-details .tfhb-list-view {
    max-width: 100%;
    min-width: 100%;
  }
}

@media (max-width: 768px) {
  /* Hide details panel on mobile - use popup instead */
  .tfhb-details-panel {
    display: none;
  }
}
</style>
