
<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount, computed, nextTick } from 'vue';
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
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

import { AddonsAuth } from '@/view/FrontendDashboard/common/StoreCommon';

const router = useRouter()
const bookingView = ref('calendar');

// View toggle state
const currentView = ref('calendar'); // 'calendar' or 'list'

// List view state
const currentPage = ref(1);
const itemsPerPage = ref(10);
const listEvents = ref([]);

// Calendar view state
const currentDate = ref(new Date());
const calendarView = ref('timeGridDay'); // 'timeGridDay', 'timeGridWeek', 'dayGridMonth'
const calendarRef = ref(null);
const showDetailsPanel = ref(false);

// Search and filter state
const searchQuery = ref('');
const selectedStatus = ref('all');
const selectedType = ref('all');

// Calendar events from API data
const calendarEvents = ref([]);

const convertApiDataToCalendarEvents = (apiData) => {
    if (!apiData || !Array.isArray(apiData)) return [];
    
    return apiData.map(item => {
        // Parse date and time
        const date = item.date; // Format: 2025-08-19
        const startTime = item.start_time; // Format: 16:45
        const endTime = item.end_time; // Format: 17:00
        
        // Convert to ISO datetime format
        const startDateTime = `${date}T${startTime}:00`;
        const endDateTime = `${date}T${endTime}:00`;
        
        // Get buyer name from API data
        const buyerName = item.buyers_data?.user_meta?.tfhb_buyers_data?.name_of_participant || 
                         item.buyers_data?.user_meta?.tfhb_buyers_data?.family_name_of_participant ||
                         item.buyers_data?.display_name ||
                         'Unknown Buyer';
        
        // Get seller name from API data
        const sellerName = item.sellers_data?.user_meta?.tfhb_sellers_data?.name ||
                          item.sellers_data?.display_name ||
                          'Unknown Seller';
        
        // Get company name
        const companyName = sellerName;
        
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
                booking_id: item.booking_id.toString(),
                status: item.status,
                booking_date: date,
                booking_time: bookingTime,
                host_id: item.host_id?.toString() || '0',
                meeting_type: item.meeting_data?.meeting_type || 'one-to-one',
                attendees: [
                    {
                        attendee_name: sellerName,
                        email: item.buyers_data?.user_email || '',
                        status: item.status
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
    if (!apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.['denominazione-operatore-azienda']) return 'SE';
    const company = apiData.sellers_data.user_meta.tfhb_sellers_data['denominazione-operatore-azienda'];
    return company.replace(/[^a-zA-Z]/g, '').substring(0, 2).toUpperCase();
};

const getSellerName = (apiData) => {
    if (!apiData?.sellers_data?.user_meta?.tfhb_sellers_data) return 'Unknown Seller';
    const sellerData = apiData.sellers_data.user_meta.tfhb_sellers_data;
    const firstName = sellerData.name || '';
    const lastName = sellerData.family_name || '';
    return `${firstName} ${lastName}`.trim() || apiData.sellers_data.display_name || 'Unknown Seller';
};

const getCompanyWebsite = (apiData) => {
    return apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.['sito-internet'] || '';
};

const getAddress = (apiData) => {
    if (!apiData?.sellers_data?.user_meta?.tfhb_sellers_data) return '';
    const sellerData = apiData.sellers_data.user_meta.tfhb_sellers_data;
    const address = sellerData.sede_legale_dell_attivit || '';
    const region = sellerData.regione || '';
    return `${address}${address && region ? ', ' : ''}${region}`.trim();
};

const getAreasOfActivity = (apiData) => {
console.log(apiData);
    return apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.ambito_di_attivit || [];
};

const getSpecializations = (apiData) => {
    return apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.specializzazione || [];
};

const getBuyerInterests = (apiData) => {
    return apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.provenienza_buyer_interesse || [];
};

// Calendar configuration
const calendarOptions = computed(() => ({
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
        // Show details panel for all views
        showDetailsPanel.value = true;
        selectedEventData.value = info.event;
    },
    dateSet: (dateInfo) => {
        currentDate.value = dateInfo.start;
    },
    viewDidMount: (viewInfo) => {
        calendarView.value = viewInfo.view.type;
        // Keep details panel visible for all views
    },
    eventDidMount: (info) => {
        // Event mounted
    },
    eventDidUnmount: (info) => {
        // Event unmounted
    },
    loading: (isLoading) => {
        // Calendar loading
    }
}));

// Selected event data for details panel
const selectedEventData = ref(null);

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

const exportCalendar = (format) => {
    if (format === 'iCal') {
        exportAsICal();
    } else if (format === 'PDF') {
        exportAsPDF();
    } else {
        toast.success(`Calendar exported as ${format}`, {
            position: 'bottom-right',
            "autoClose": 1500,
        });
    }
};

const exportAsICal = () => {
    if (!calendarEvents.value || calendarEvents.value.length === 0) {
        toast.error('No events to export', { position: 'bottom-right', "autoClose": 1500 });
        return;
    }
    let ical = "BEGIN:VCALENDAR\r\n";
    ical += "VERSION:2.0\r\n";
    ical += "PRODID:-//Hydra Booking//Calendar Export//EN\r\n";
    ical += "CALSCALE:GREGORIAN\r\n";
    ical += "METHOD:PUBLISH\r\n";
    calendarEvents.value.forEach(event => {
        const startDate = new Date(event.start);
        const endDate = new Date(event.end);
        const formatDate = (date) => date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        ical += "BEGIN:VEVENT\r\n";
        ical += "UID:" + event.id + "@hydra-booking\r\n";
        ical += "DTSTAMP:" + formatDate(new Date()) + "\r\n";
        ical += "DTSTART:" + formatDate(startDate) + "\r\n";
        ical += "DTEND:" + formatDate(endDate) + "\r\n";
        ical += "SUMMARY:" + event.title + "\r\n";
        ical += "STATUS:" + event.extendedProps.status.toUpperCase() + "\r\n";
        if (event.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.description) {
            ical += "DESCRIPTION:" + event.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data.description + "\r\n";
        }
        if (event.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.sede_legale_dell_attivit) {
            ical += "LOCATION:" + event.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data.sede_legale_dell_attivit + "\r\n";
        }
        ical += "END:VEVENT\r\n";
    });
    ical += "END:VCALENDAR\r\n";
    const blob = new Blob([ical], { type: 'text/calendar' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `calendar-export-${new Date().toISOString().split('T')[0]}.ics`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    toast.success('Calendar exported as .iCal successfully', { position: 'bottom-right', "autoClose": 1500 });
};

const exportAsPDF = () => {
    if (!calendarEvents.value || calendarEvents.value.length === 0) {
        toast.error('No events to export', { position: 'bottom-right', "autoClose": 1500 });
        return;
    }

    const createPDFContainer = () => {
        const container = document.createElement('div');
        container.style.position = 'absolute';
        container.style.left = '-9999px';
        container.style.top = '0';
        container.style.width = '800px';
        container.style.padding = '40px';
        container.style.backgroundColor = 'white';
        container.style.fontFamily = 'Arial, sans-serif';
        container.style.fontSize = '12px';
        container.style.lineHeight = '1.4';
        
        const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const header = document.createElement('div');
        header.innerHTML = `
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">
                <h1 style="color: #333; margin: 0 0 10px 0; font-size: 24px;">Calendar Export</h1>
                <p style="color: #666; margin: 5px 0; font-size: 14px;">Generated on ${currentDate}</p>
                <p style="color: #666; margin: 5px 0; font-size: 14px;">Total Events: ${calendarEvents.value.length}</p>
            </div>
        `;
        container.appendChild(header);
        if (calendarEvents.value.length === 0) {
            const noEvents = document.createElement('div');
            noEvents.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No events to display</p>';
            container.appendChild(noEvents);
        } else {
            const sortedEvents = [...calendarEvents.value].sort((a, b) => new Date(a.start) - new Date(b.start));
            sortedEvents.forEach(event => {
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);
                const formattedDate = startDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                const formattedTime = startDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + ' - ' + endDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                const apiData = event.extendedProps.apiData;
                const sellerName = getSellerName(apiData);
                const companyName = getCompanyWebsite(apiData);
                const address = getAddress(apiData);
                const description = apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.description || 'No description available';
                let statusColor = '#666';
                switch (event.extendedProps.status) {
                    case 'confirmed': statusColor = '#2e7d32'; break;
                    case 'pending': statusColor = '#f57c00'; break;
                    case 'canceled': statusColor = '#c62828'; break;
                }
                const eventDiv = document.createElement('div');
                eventDiv.style.border = '1px solid #ddd';
                eventDiv.style.marginBottom = '20px';
                eventDiv.style.padding = '15px';
                eventDiv.style.borderRadius = '8px';
                eventDiv.style.pageBreakInside = 'avoid';
                eventDiv.innerHTML = `
                    <div style="font-weight: bold; font-size: 16px; color: #333; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                        ${event.title}
                    </div>
                    <div style="color: #666; font-size: 14px; line-height: 1.6;">
                        <div style="margin-bottom: 8px;"><strong>Date:</strong> ${formattedDate}</div>
                        <div style="margin-bottom: 8px;"><strong>Time:</strong> ${formattedTime}</div>
                        <div style="margin-bottom: 8px;">
                            <strong>Status:</strong> 
                            <span style="color: ${statusColor}; font-weight: bold; text-transform: uppercase;">${event.extendedProps.status}</span>
                        </div>
                        <div style="margin-bottom: 8px;"><strong>Contact:</strong> ${sellerName}</div>
                        ${companyName ? `<div style="margin-bottom: 8px;"><strong>Company:</strong> ${companyName}</div>` : ''}
                        ${address ? `<div style="margin-bottom: 8px;"><strong>Address:</strong> ${address}</div>` : ''}
                        <div style="margin-bottom: 8px;"><strong>Description:</strong> ${description}</div>
                    </div>
                `;
                container.appendChild(eventDiv);
            });
        }
        return container;
    };

    try {
        const container = createPDFContainer();
        document.body.appendChild(container);
        html2canvas(container, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#ffffff' }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const imgWidth = 210; const pageHeight = 295; const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            const fileName = `calendar-export-${new Date().toISOString().split('T')[0]}.pdf`;
            pdf.save(fileName);
            document.body.removeChild(container);
            toast.success('Calendar exported as PDF successfully', { position: 'bottom-right', "autoClose": 1500 });
        }).catch(error => {
            console.error('PDF generation error:', error);
            document.body.removeChild(container);
            toast.error('PDF generation failed. Using print method instead.', { position: 'bottom-right', "autoClose": 3000 });
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`<!DOCTYPE html><html><head><title>Calendar Export</title><style>body { font-family: Arial, sans-serif; margin: 20px; }.event { border: 1px solid #ddd; margin-bottom: 15px; padding: 15px; }.event-title { font-weight: bold; font-size: 16px; margin-bottom: 8px; }.event-details { color: #666; font-size: 14px; line-height: 1.4; }</style></head><body><h1>Calendar Export</h1><p>Generated on ${new Date().toLocaleDateString()}</p>${calendarEvents.value.map(event => `<div class="event"><div class="event-title">${event.title}</div><div class="event-details"><strong>Date:</strong> ${new Date(event.start).toLocaleDateString()}<br><strong>Time:</strong> ${new Date(event.start).toLocaleTimeString()} - ${new Date(event.end).toLocaleTimeString()}<br><strong>Status:</strong> ${event.extendedProps.status}</div></div>`).join('')}</body></html>`);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        });
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

const buyersAgenda = async (id) => {   
  let data = {
    'buyers_id': id
  }
  try { 
      const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/buyers/buyers-agenda', data, {
          headers: {
              'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
          } 
      });
      
      if (response.data.status) {   
          // Convert API data to calendar events
          const convertedEvents = convertApiDataToCalendarEvents(response.data.agenda);
          calendarEvents.value = convertedEvents;
          
          // Also populate list events
          listEvents.value = [...convertedEvents];
          
          // Force calendar to refresh
          if (calendarRef.value) {
              const calendarApi = calendarRef.value.getApi();
              if (calendarApi) {
                  calendarApi.removeAllEvents();
                  calendarApi.addEventSource(convertedEvents);
              }
          }
          
          // Also try to refresh the calendar view
          nextTick(() => {
              if (calendarRef.value) {
                  const calendarApi = calendarRef.value.getApi();
                  if (calendarApi) {
                      calendarApi.refetchEvents();
                  }
              }
          });
      }
  } catch (error) {
      // Handle error silently
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

// Filtered list events based on search query
const filteredListEvents = computed(() => {
    if (!searchQuery.value.trim()) {
        return listEvents.value;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return listEvents.value.filter(event => {
        // Search in title
        if (event.title.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in seller name
        const sellerName = event.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.name ||
                          event.extendedProps.apiData?.sellers_data?.display_name ||
                          '';
        if (sellerName.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in company name
        const companyName = event.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.['denominazione-operatore-azienda'] ||
                           event.extendedProps.apiData?.buyers_data?.user_meta?.tfhb_buyers_data?.company_website ||
                           '';
        if (companyName.toLowerCase().includes(query)) {
            return true;
        }
        
        // Search in description
        const description = event.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.description || '';
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

// View toggle function
const toggleView = async (view) => {
    currentView.value = view;
    if (view === 'list') {
        // Convert calendar events to list format
        listEvents.value = [...calendarEvents.value];
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

// Pagination functions
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

// List view event click handler
const handleListEventClick = (event) => {
    showDetailsPanel.value = true;
    selectedEventData.value = event;
};

onBeforeMount(() => { 
    // Booking.fetchBookings('upcoming');
    // Meeting.fetchMeetings();
    // Host.fetchHosts();
    
    // Load seller's agenda data
    if (AddonsAuth.loggedInUser?.ID) {
        buyersAgenda(AddonsAuth.loggedInUser.ID);
        
        // Add a test event to see if calendar is working
        // setTimeout(() => {
        //     const testEvent = {
        //         id: 'test-1',
        //         title: 'Test Event',
        //         start: new Date().toISOString(),
        //         end: new Date(Date.now() + 3600000).toISOString(), // 1 hour later
        //         backgroundColor: '#4CAF50',
        //         borderColor: '#45a049'
        //     };
        //     calendarEvents.value = [testEvent];
        // }, 2000);
    }
});
</script>

<template>  
<!-- Booking Calendar View -->
<div class="tfhb-booking-calendar-container">
    <!-- Calendar Header Controls -->
    <div class="tfhb-calendar-header tfhb-flexbox tfhb-justify-between tfhb-align-center tfhb-mb-24">
        <div class="tfhb-calendar-navigation tfhb-flexbox tfhb-gap-16">
            <!-- View Toggle (Calendar/List) -->
            <div class="tfhb-view-toggle tfhb-flexbox tfhb-gap-8">
                <button 
                    class="tfhb-btn secondary-btn" 
                    :class="currentView === 'calendar' ? 'active' : ''"
                    @click="toggleView('calendar')"
                >
                    <Icon name="Calendar" size=16 />
                    {{ $tfhb_trans('Calendar') }}
                </button>
                <button 
                    class="tfhb-btn secondary-btn" 
                    :class="currentView === 'list' ? 'active' : ''"
                    @click="toggleView('list')"
                >
                    <Icon name="List" size=16 />
                    {{ $tfhb_trans('List') }}
                </button>
            </div>
            
            <!-- Calendar View Toggle (only show when in calendar view) -->
            
        </div>
        
        <div  v-if="currentView === 'calendar'" class="tfhb-calendar-date-display tfhb-flexbox tfhb-gap-16 tfhb-align-center">
          <div class="tfhb-calendar-view-toggle tfhb-flexbox tfhb-gap-8">
                <button 
                    class="tfhb-btn secondary-btn" 
                    :class="calendarView === 'timeGridDay' ? 'active' : ''"
                    @click="changeCalendarView('timeGridDay')"
                >
                    <Icon name="CalendarDays" size=16 />
                    {{ $tfhb_trans('Day') }}
                </button>
                <button 
                    class="tfhb-btn secondary-btn" 
                    :class="calendarView === 'timeGridWeek' ? 'active' : ''"
                    @click="changeCalendarView('timeGridWeek')"
                >
                    <Icon name="Calendar" size=16 />
                    {{ $tfhb_trans('Week') }}
                </button>
                <button 
                    class="tfhb-btn secondary-btn" 
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
        <!-- Calendar Component -->
        <div v-if="currentView === 'calendar'" class="tfhb-booking-calendar">   
            <FullCalendar 
                ref="calendarRef"
                class='demo-app-calendar' 
                :options='calendarOptions'
            >
                <template v-slot:eventContent='arg'>
                    <div class="tfhb-calendar-event-content">
                        <div class="tfhb-event-title">{{ truncateString(arg.event.title, 30) }}</div>
                        <div class="tfhb-event-time">{{ arg.event.extendedProps?.booking_time || 'No time' }}</div>
                        <div class="tfhb-event-status" :class="arg.event.extendedProps?.status || 'unknown'">
                            {{ arg.event.extendedProps?.status || 'unknown' }}
                        </div>
                    </div>
                </template>
            </FullCalendar>
        </div>

        <!-- List View Component -->
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
                        <div class="tfhb-table-cell tfhb-cell-title">
                            <div class="tfhb-cell-content">
                                <h4>{{ event.title }}</h4>
                                <div v-if="event.extendedProps?.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.description" class="tfhb-cell-description">
                                    {{ truncateString(event.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data.description, 80) }}
                                </div>
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
                                <span>{{ event.extendedProps?.booking_time || 'No time' }}</span>
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-contact">
                            <div class="tfhb-cell-content">
                                <Icon name="User" size=14 />
                                <span>{{ getSellerName(event.extendedProps?.apiData) }}</span>
                            </div>
                        </div>
                        <div class="tfhb-table-cell tfhb-cell-status">
                            <div class="tfhb-cell-content">
                                <span class="tfhb-status-badge" :class="event.extendedProps?.status || 'unknown'">
                                    {{ event.extendedProps?.status || 'unknown' }}
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
                    class="tfhb-btn secondary-btn" 
                    :disabled="currentPage === 1"
                    @click="prevPage"
                >
                    <Icon name="ChevronLeft" size=16 />
                    Previous
                </button>
                
                <div class="tfhb-page-numbers">
                    <button 
                        v-for="page in totalPages" 
                        :key="page"
                        class="tfhb-page-btn"
                        :class="{ 'active': page === currentPage }"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>
                </div>
                
                <button 
                    class="tfhb-btn secondary-btn" 
                    :disabled="currentPage === totalPages"
                    @click="nextPage"
                >
                    Next
                    <Icon name="ChevronRight" size=16 />
                </button>
            </div>
        </div>

        <!-- Details Panel (for all views) -->
        <div v-if="showDetailsPanel && selectedEventData" class="tfhb-details-panel">
            <div class="tfhb-details-header">
                <h3>Seller Details</h3>
                <div class="tfhb-details-actions">
                    <Icon name="MessageCircle" size=16 />
                    <Icon name="X" size=16 @click="showDetailsPanel = false" class="tfhb-close-btn" />
                </div>
            </div>

            <div class="tfhb-company-info" v-if="selectedEventData.extendedProps.apiData">
                <div class="tfhb-company-logo">
                  <img v-if="selectedEventData.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data.avatar" :src="selectedEventData.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data.avatar" alt="Buyers Avatar"
                  :style="{
                    'width': '80px',
                    'height': '80px', 
                    'border-radius': '50%'
                  }"
                  >
                  <img v-else :src="$tfhb_url+'/assets/images/avator.png'" alt="Buyers Avatar"
                  :style="{
                    'width': '80px',
                    'height': '80px', 
                    'border-radius': '50%'
                  }">
                </div>
                <h4 class="tfhb-company-name">{{ selectedEventData.title }}</h4>
            </div>

            <div class="tfhb-details-section" v-if="selectedEventData.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.description">
                <label class="tfhb-section-label">DESCRIPTION</label>
                <p class="tfhb-description-text">
                    {{ selectedEventData.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data.description || 'No description available' }}
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
                            {{ getSellerName(selectedEventData.extendedProps.apiData) }}
                        </span>
                        <span class="tfhb-staff-role">
                            {{ selectedEventData.extendedProps.apiData?.sellers_data?.user_meta?.tfhb_sellers_data?.incarico || 'Service Provider' }}
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
                <p class="tfhb-contact-info">{{ selectedEventData.extendedProps.apiData?.sellers_data?.user_email || 'No email available' }}</p>
            </div>

            <div class="tfhb-details-section" v-if="getAddress(selectedEventData.extendedProps.apiData)">
                <label class="tfhb-section-label">ADDRESS</label>
                <p class="tfhb-contact-info">{{ getAddress(selectedEventData.extendedProps.apiData) }}</p>
            </div>

            <!-- <div class="tfhb-details-section"  >
                <label class="tfhb-section-label">AREAS OF ACTIVITY</label>
                <div class="tfhb-activity-tags">
                    <span class="tfhb-tag"  >
                        {{ selectedEventData.extendedProps.apiData.sellers_data.user_meta.tfhb_sellers_data }}
                    </span>
                </div>
            </div> -->

            <div class="tfhb-details-section" v-if="getSpecializations(selectedEventData.extendedProps.apiData).length">
                <label class="tfhb-section-label">SPECIALIZATIONS</label>
                <div class="tfhb-activity-tags">
                    <span class="tfhb-tag" v-for="specialization in getSpecializations(selectedEventData.extendedProps.apiData)" :key="specialization">
                        {{ specialization }}
                    </span>
                </div>
            </div>

            <div class="tfhb-details-section" v-if="getBuyerInterests(selectedEventData.extendedProps.apiData).length">
                <label class="tfhb-section-label">BUYER INTERESTS</label>
                <div class="tfhb-activity-tags">
                    <span class="tfhb-tag" v-for="interest in getBuyerInterests(selectedEventData.extendedProps.apiData)" :key="interest">
                        {{ interest }}
                    </span>
                </div>
            </div>
        </div>
    </div>
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
.tfhb-btn.secondary-btn.active{
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
  min-width: 200px;
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
  min-width: 150px;
}

.tfhb-cell-status {
  flex: 1;
  min-width: 100px;
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

.tfhb-status-badge.unknown {
  background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
  color: #666;
  border: 1px solid #ccc;
}

.tfhb-list-item:hover {
  background: #f0f0f0;
  border-color: #d0d0d0;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tfhb-list-item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.tfhb-list-item-title {
  flex: 1;
}

.tfhb-list-item-title h4 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 700;
  color: #333;
  line-height: 1.3;
}

.tfhb-list-item-status {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tfhb-list-item-status.confirmed {
  background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.tfhb-list-item-status.pending {
  background: linear-gradient(135deg, #fff3e0, #ffcc80);
  color: #f57c00;
  border: 1px solid #ffb74d;
}

.tfhb-list-item-status.canceled {
  background: linear-gradient(135deg, #ffebee, #ffcdd2);
  color: #c62828;
  border: 1px solid #ef9a9a;
}

.tfhb-list-item-status.unknown {
  background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
  color: #666;
  border: 1px solid #d0d0d0;
}

.tfhb-list-item-actions {
  color: #666;
  transition: color 0.3s ease;
}

.tfhb-list-item:hover .tfhb-list-item-actions {
  color: #4CAF50;
}

.tfhb-list-item-details {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 12px;
}

.tfhb-list-item-date,
.tfhb-list-item-time,
.tfhb-list-item-contact {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #666;
}

.tfhb-list-item-date svg,
.tfhb-list-item-time svg,
.tfhb-list-item-contact svg {
  color: #4CAF50;
}

.tfhb-list-item-description {
  font-size: 13px;
  color: #666;
  line-height: 1.5;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

/* Pagination Styles */
.tfhb-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  padding-top: 24px;
  border-top: 1px solid #f0f0f0;
}

.tfhb-page-numbers {
  display: flex;
  gap: 8px;
}

.tfhb-page-btn {
  width: 40px;
  height: 40px;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tfhb-page-btn:hover {
  border-color: #4CAF50;
  background: #f0f0f0;
}

.tfhb-page-btn.active {
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  border-color: #4CAF50;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.tfhb-pagination .tfhb-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* View Toggle Styles */
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
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  border-color: #4CAF50;
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

/* Responsive Design for List View */
@media (max-width: 768px) {
  .tfhb-list-header {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
  
  .tfhb-list-item-details {
    flex-direction: column;
    gap: 8px;
  }
  
  .tfhb-pagination {
    flex-direction: column;
    gap: 12px;
  }
  
  .tfhb-page-numbers {
    order: 2;
  }
  
  .tfhb-pagination .tfhb-btn {
    order: 1;
  }
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
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  border-color: #4CAF50;
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
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
  min-width: 600px; /* Ensure minimum width for content */
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
  margin-left: 24px;
  width: 400px;
  height: fit-content;
  position: sticky;
  top: 24px;
  max-height: calc(100vh - 48px);
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #c1c1c1 #f1f1f1;
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

@media (max-width: 768px) {
  .tfhb-calendar-header {
    flex-direction: column;
    gap: 20px;
  }
  
  .tfhb-calendar-view-toggle {
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
  
  .tfhb-booking-calendar {
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
    min-width: 80px;
  }

  .tfhb-cell-actions {
    min-width: 60px;
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
}
.tfhb-btn.secondary-btn.active{
  color: #fff !important;
}
</style>
