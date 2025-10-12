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
import { AddonsSettings } from '@/store/settings/addons-settings.js';
import { Notification } from '@/store/notification';
import { toast } from "vue3-toastify";
import QRCode from 'qrcode';
import jsPDF from 'jspdf';
// Import JSZip properly for this environment
import JSZip from 'jszip';

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
    
    if (AddonsUsers.bulk_action === 'badge') {
        await handleBulkBadgeExport();
    } else {
        await AddonsUsers.bulkUpdateStatus(AddonsUsers.bulk_action);
    }
    
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

// Get bulk action button text
const getBulkActionButtonText = () => {
    if (AddonsUsers.bulk_action === 'activate') {
        return `Make Active ${selectedCount.value} Users`;
    } else if (AddonsUsers.bulk_action === 'deactivate') {
        return `Make Deactive ${selectedCount.value} Users`;
    } else if (AddonsUsers.bulk_action === 'badge') {
        return `Export Badges (${selectedCount.value} Users)`;
    }
    return '';
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

const DownloadBadgePDFWithQRCode = async (user) => {
    try { 
        
        // Create QR code data
        // const qr_data = user.name + ' ' + user.email;
        // console.log(user);

         // Create QR code data with more comprehensive information
         const qr_data = `Name: ${user.name} | Role: ${user.role} | Email: ${user.email}`;
        
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
        let backgroundImageUrl = '';
        if(user.role == 'Buyers'){
            // Background image URL
            backgroundImageUrl = AddonsSettings.buyers.badge_pdf_image;
        }else if(user.role == 'Sellers'){
            // Background image URL
            backgroundImageUrl = AddonsSettings.Sellers.badge_pdf_image;
        }else if(user.role == 'Exhibitors'){
            // Background image URL
            backgroundImageUrl = AddonsSettings.Exhibitors.badge_pdf_image;
        } 
        // Function to load image and create PDF
        const createPDFWithBackground = () => {
            // Add background image
            pdf.addImage(backgroundImageUrl, 'PNG', 0, 0, pageWidth, pageHeight);
            
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
            
            // Add job title (centered in bottom right quadrant, below QR code)
            pdf.setFontSize(11); // Reduced font size
            pdf.setTextColor(0, 0, 0);
            pdf.setFont('helvetica', 'normal');
            const jobTitle = user.role; // You can make this dynamic if needed
            const jobTitleWidth = pdf.getTextWidth(jobTitle);
            const jobTitleUpper = jobTitle ? jobTitle.toUpperCase() : '';
            pdf.text(jobTitleUpper, startX + (quadrantWidth - jobTitleWidth) / 2, startY + 110); // Moved up 10mm from 120 to 110

            
            // Add user name (centered in bottom right quadrant, below job title)
            pdf.setFontSize(16); // Reduced font size
            pdf.setTextColor(0, 0, 0);
            pdf.setFont('helvetica', 'bold');
            const nameText = user.name || 'User Name';
            const nameWidth = pdf.getTextWidth(nameText);
            pdf.text(nameText, startX + (quadrantWidth - nameWidth) / 2, startY + 118); // Moved up 10mm from 128 to 118

            
            if(user.role == 'Buyers'){ 
                const companyName = (user.data?.travel_agent_name || '').trim();
                pdf.setFontSize(10);
                const companyNameWidth = pdf.getTextWidth(companyName);
                pdf.text(companyName, startX + (quadrantWidth - companyNameWidth) / 2, startY + 125); // Moved up 10mm from 135 to 125

                
                // Convert nation object/array to comma-separated string
                let nation = '';
                if (Array.isArray(user.data?.nation)) {
                    nation = user.data.nation.join(', ');
                } else if (typeof user.data?.nation === 'object' && user.data?.nation !== null) {
                    nation = Object.values(user.data.nation).join(', ');
                } else if (typeof user.data?.nation === 'string') {
                    nation = user.data.nation;
                }
                pdf.setFontSize(10);
                const nationWidth = pdf.getTextWidth(nation);
                pdf.text(nation, startX + (quadrantWidth - nationWidth) / 2, startY + 132); // Moved up 10mm from 142 to 132

            } 

            if(user.role == 'Sellers'){  
                const companyName = (user.data?.eventuale_altra_denominazione || '').trim(); 
                pdf.setFontSize(10);
                const companyNameWidth = pdf.getTextWidth(companyName);
                pdf.text(companyName, startX + (quadrantWidth - companyNameWidth) / 2, startY + 125); // Moved up 10mm from 135 to 125

                
                // Convert nation object/array to comma-separated string
                let nation = user.data.regione;
                pdf.setFontSize(10);
                const nationWidth = pdf.getTextWidth(nation);
                pdf.text(nation, startX + (quadrantWidth - nationWidth) / 2, startY + 132); // Moved up 10mm from 142 to 132
            }

            if(user.role == 'Exhibitors'){  
                const companyName = (user.data?.company_name || '').trim(); 
                pdf.setFontSize(10);
                const companyNameWidth = pdf.getTextWidth(companyName);
                pdf.text(companyName, startX + (quadrantWidth - companyNameWidth) / 2, startY + 125); // Moved up 10mm from 135 to 125

                    
            }
                

           
            // Save the PDF
            const fileName = `badge_${user.name || 'user'}_${Date.now()}.pdf`;
            pdf.save(fileName);
            
            toast.success('Badge PDF generated successfully!', {
                position: 'bottom-right',
                autoClose: 3000,
            });
        };
        
        // Try to load the background image first
        const img = new Image();
        img.crossOrigin = 'anonymous'; // Handle CORS if needed
        
        img.onload = () => {
            try {
                createPDFWithBackground();
            } catch (error) {
                console.error('Error creating PDF:', error);
                toast.error('Failed to create PDF. Please try again.', {
                    position: 'bottom-right',
                    autoClose: 3000,
                });
            }
        };
        
        img.onerror = () => {
            console.warn('Background image failed to load, creating PDF without background');
            // Create PDF without background image
            try {
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
                
                // Add job title (centered in bottom right quadrant, below QR code)
                pdf.setFontSize(11); // Reduced font size
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                const jobTitle = user.role; // You can make this dynamic if needed
                const jobTitleWidth = pdf.getTextWidth(jobTitle);
                const jobTitleUpper = jobTitle ? jobTitle.toUpperCase() : '';
                pdf.text(jobTitleUpper, startX + (quadrantWidth - jobTitleWidth) / 2, startY + 110); // Moved up 10mm from 120 to 110
                // Add user name (centered in bottom right quadrant, below job title)
                pdf.setFontSize(16); // Reduced font size
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'bold');
                const nameText = user.name || 'User Name';
                const nameWidth = pdf.getTextWidth(nameText);
                pdf.text(nameText, startX + (quadrantWidth - nameWidth) / 2, startY + 118); // Moved up 10mm from 128 to 118


                         
                if(user.role == 'Buyers'){ 
                  
                    const companyName = (user.data?.travel_agent_name || '').trim(); 
                    pdf.setFontSize(10);
                    const companyNameWidth = pdf.getTextWidth(companyName);
                    pdf.text(companyName, startX + (quadrantWidth - companyNameWidth) / 2, startY + 125); // Moved up 10mm from 135 to 125

                    
                    // Convert nation object/array to comma-separated string
                    let nation = '';
                    if (Array.isArray(user.data?.nation)) {
                        nation = user.data.nation.join(', ');
                    } else if (typeof user.data?.nation === 'object' && user.data?.nation !== null) {
                        nation = Object.values(user.data.nation).join(', ');
                    } else if (typeof user.data?.nation === 'string') {
                        nation = user.data.nation;
                    }
                    pdf.setFontSize(10);
                    const nationWidth = pdf.getTextWidth(nation);
                    pdf.text(nation, startX + (quadrantWidth - nationWidth) / 2, startY + 132); // Moved up 10mm from 142 to 132
          
                }
 
                if(user.role == 'Sellers'){  
                    const companyName = (user.data?.eventuale_altra_denominazione || '').trim(); 
                    pdf.setFontSize(10);
                    const companyNameWidth = pdf.getTextWidth(companyName);
                    pdf.text(companyName, startX + (quadrantWidth - companyNameWidth) / 2, startY + 125); // Moved up 10mm from 135 to 125

                    
                    // Convert nation object/array to comma-separated string
                    const nation = user.data.regione;
                    pdf.setFontSize(10);
                    const nationWidth = pdf.getTextWidth(nation);
                    pdf.text(nation, startX + (quadrantWidth - nationWidth) / 2, startY + 132); // Moved up 10mm from 142 to 132
                }
              

                if(user.role == 'Exhibitors'){  
                    const companyName = (user.data?.company_name || '').trim(); 
                    pdf.setFontSize(10);
                    const companyNameWidth = pdf.getTextWidth(companyName);
                    pdf.text(companyName, startX + (quadrantWidth - companyNameWidth) / 2, startY + 125); // Moved up 10mm from 135 to 125

                     
                }
                 
                // Save the PDF
                const fileName = `badge_${user.name || 'user'}_${Date.now()}.pdf`;
                pdf.save(fileName);
                
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
        
        // Start loading the image
        img.src = backgroundImageUrl;
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        toast.error('Failed to generate PDF. Please try again.', {
            position: 'bottom-right',
            autoClose: 3000,
        });
    }
}

// Bulk badge export function
const handleBulkBadgeExport = async () => {
    if (AddonsUsers.selected_users.length === 0) {
        toast.warning('Please select users first', {
            position: 'bottom-right',
            autoClose: 1500,
        });
        return;
    }

    try {
        // Check if JSZip is available (try multiple sources)
        let JSZipInstance = JSZip;
        if (typeof JSZipInstance === 'undefined') {
            // Try to get JSZip from global scope
            JSZipInstance = window.JSZip;
        }
        if (typeof JSZipInstance === 'undefined') {
            // Try to get JSZip from require if available
            try {
                JSZipInstance = require('jszip');
            } catch (e) {
                // Ignore require error
            }
        }
        
        if (typeof JSZipInstance === 'undefined') {
            toast.error('JSZip library not available. Please contact administrator.', {
                position: 'bottom-right',
                autoClose: 3000,
            });
            return;
        }

        toast.info('Generating bulk badges...', {
            position: 'bottom-right',
            autoClose: 2000,
        });

        const zip = new JSZipInstance();
        const selectedUsers = AddonsUsers.users[AddonsUsers.current_tab].filter(user => 
            AddonsUsers.selected_users.includes(user.id)
        );

        // Validate that selected users have required data
        const validUsers = selectedUsers.filter(user => user.name && user.role);
        if (validUsers.length === 0) {
            toast.error('Selected users must have valid names and roles', {
                position: 'bottom-right',
                autoClose: 3000,
            });
            return;
        }

        if (validUsers.length !== selectedUsers.length) {
            toast.warning(`Some users (${selectedUsers.length - validUsers.length}) were skipped due to missing data`, {
                position: 'bottom-right',
                autoClose: 3000,
            });
        }

        // Check for reasonable limit to prevent performance issues
        const MAX_USERS = 100;
        if (validUsers.length > MAX_USERS) {
            toast.warning(`Large number of users selected (${validUsers.length}). This may take some time.`, {
                position: 'bottom-right',
                autoClose: 4000,
            });
        }

        // Show initial progress toast
        toast.info(`Starting bulk badge generation for ${validUsers.length} users...`, {
            position: 'bottom-right',
            autoClose: 3000,
        });

        // Generate PDFs for each valid user with timeout protection
        const TIMEOUT_PER_USER = 10000; // 10 seconds per user
        for (let i = 0; i < validUsers.length; i++) {
            const user = validUsers[i];
            
            try {
                // Add timeout protection for each user
                const userPromise = new Promise(async (resolve, reject) => {
                    const timeoutId = setTimeout(() => {
                        reject(new Error(`Timeout generating badge for ${user.name}`));
                    }, TIMEOUT_PER_USER);
                    
                    try {
                        // Create QR code data
                        const qr_data = `Name: ${user.name} | Role: ${user.role} | Email: ${user.email}`;
                        
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
                        let backgroundImageUrl = '';
                        
                        if(user.role == 'Buyers'){
                            backgroundImageUrl = AddonsSettings.buyers.badge_pdf_image;
                        } else if(user.role == 'Sellers'){
                            backgroundImageUrl = AddonsSettings.Sellers.badge_pdf_image;
                        } else if(user.role == 'Exhibitors'){
                            backgroundImageUrl = AddonsSettings.Exhibitors.badge_pdf_image;
                        }

                        // Function to create PDF with background
                        const createPDFWithBackground = () => {
                            // Add background image
                            pdf.addImage(backgroundImageUrl, 'PNG', 0, 0, pageWidth, pageHeight);
                            
                            // Calculate bottom right quadrant positions
                            const quadrantWidth = pageWidth / 2;
                            const quadrantHeight = pageHeight / 2;
                            const startX = quadrantWidth;
                            const startY = quadrantHeight;
                            
                            // Add QR code
                            const qrSize = 35;
                            const qrX = startX + (quadrantWidth - qrSize) / 2;
                            const qrY = startY + 70; // Moved up 10mm from 80 to 70
                            pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                            
                            // Add job title
                            pdf.setFontSize(11);
                            pdf.setTextColor(0, 0, 0);
                            pdf.setFont('helvetica', 'normal');
                            const jobTitle = user.role;
                            const jobTitleWidth = pdf.getTextWidth(jobTitle);
                            pdf.text(jobTitle, startX + (quadrantWidth - jobTitleWidth) / 2, startY + 110); // Moved up 10mm from 120 to 110
                            
                            // Add user name
                            pdf.setFontSize(16);
                            pdf.setTextColor(0, 0, 0);
                            pdf.setFont('helvetica', 'bold');
                            const nameText = user.name || 'User Name';
                            const nameWidth = pdf.getTextWidth(nameText);
                            pdf.text(nameText, startX + (quadrantWidth - nameWidth) / 2, startY + 118); // Moved up 10mm from 128 to 118
                            
                            return pdf;
                        };

                        // Try to load background image first
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        
                        const pdfPromise = new Promise((resolve, reject) => {
                            img.onload = () => {
                                try {
                                    const pdfDoc = createPDFWithBackground();
                                    resolve(pdfDoc);
                                } catch (error) {
                                    reject(error);
                                }
                            };
                            
                            img.onerror = () => {
                                try {
                                    // Create PDF without background
                                    const quadrantWidth = pageWidth / 2;
                                    const quadrantHeight = pageHeight / 2;
                                    const startX = quadrantWidth;
                                    const startY = quadrantHeight;
                                    
                                    const qrSize = 35;
                                    const qrX = startX + (quadrantWidth - qrSize) / 2;
                                    const qrY = startY + 70; // Moved up 10mm from 80 to 70
                                    pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                                    
                                    pdf.setFontSize(11);
                                    pdf.setTextColor(0, 0, 0);
                                    pdf.setFont('helvetica', 'normal');
                                    const jobTitle = user.role;
                                    const jobTitleWidth = pdf.getTextWidth(jobTitle);
                                    pdf.text(jobTitle, startX + (quadrantWidth - jobTitleWidth) / 2, startY + 110); // Moved up 10mm from 120 to 110
                                    
                                    pdf.setFontSize(16);
                                    pdf.setTextColor(0, 0, 0);
                                    pdf.setFont('helvetica', 'bold');
                                    const nameText = user.name || 'User Name';
                                    const nameWidth = pdf.getTextWidth(nameText);
                                    pdf.text(nameText, startX + (quadrantWidth - nameWidth) / 2, startY + 118); // Moved up 10mm from 128 to 118
                                    
                                    resolve(pdf);
                                } catch (error) {
                                    reject(error);
                                }
                            };
                            
                            img.src = backgroundImageUrl;
                        });

                        const pdfDoc = await pdfPromise;
                        const pdfBlob = pdfDoc.output('blob');
                        
                        // Add PDF to zip with sanitized filename
                        const sanitizedName = user.name.replace(/[^a-zA-Z0-9]/g, '_') || 'user';
                        const fileName = `badge_${sanitizedName}_${user.id}.pdf`;
                        zip.file(fileName, pdfBlob);
                        
                        clearTimeout(timeoutId);
                        resolve();
                    } catch (error) {
                        clearTimeout(timeoutId);
                        reject(error);
                    }
                });
                
                await userPromise;
                
                // Show progress only at key milestones (25%, 50%, 75%, 100%)
                const progress = Math.round(((i + 1) / validUsers.length) * 100);
                if (progress === 25 || progress === 50 || progress === 75 || progress === 100) {
                    toast.info(`Progress: ${progress}% (${i + 1}/${validUsers.length} badges generated)`, {
                        position: 'bottom-right',
                        autoClose: 2000,
                    });
                }
                
            } catch (error) {
                console.error(`Error generating badge for user ${user.name}:`, error);
                // Continue with other users even if one fails
            }
        }

        // Progress complete

        // Generate and download ZIP file
        const zipBlob = await zip.generateAsync({ type: 'blob' });
        const zipUrl = URL.createObjectURL(zipBlob);
        
        // Create download link
        const link = document.createElement('a');
        link.href = zipUrl;
        link.download = `bulk_badges_${AddonsUsers.current_tab}_${Date.now()}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        URL.revokeObjectURL(zipUrl);
        
        // Clear selections
        AddonsUsers.selected_users = [];
        
        toast.success(`✅ Bulk export complete! ${validUsers.length} badges generated and downloaded as ZIP file.`, {
            position: 'bottom-right',
            autoClose: 4000,
        });
        
    } catch (error) {
        console.error('Error generating bulk badges:', error);
        toast.error('Failed to generate bulk badges. Please try again.', {
            position: 'bottom-right',
            autoClose: 3000,
        });
    }
};

// Lifecycle
onBeforeMount(() => {
    AddonsUsers.init();
    AddonsSettings.FetchAddonsSettings();
});

onBeforeRouteLeave(() => {
    // Cleanup if needed
});
</script>

<template>
    <div class="tfhb-admin-dashboard tfhb-admin-meetings ">  
        <Header v-if="$front_end_dashboard == false" :title="$tfhb_trans('Addons Users Management')" :notifications="Notification.Data" :total_unread="Notification.total_unread" @MarkAsRead="Notification.MarkAsRead()" /> 
     
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
                            {'name': 'Deactive', 'value': 'deactivate'},
                            {'name': 'Export Badge', 'value': 'badge'}
                        ]"
                        @tfhb-onchange="[]"
                    />
                    <HbButton 
                        v-if="AddonsUsers.bulk_action && selectedCount > 0"
                        classValue="tfhb-btn boxed-btn"
                        @click="handleBulkAction"
                        :buttonText="getBulkActionButtonText()"
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
                            <th>{{ $tfhb_trans('Company Name') }}</th>
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
                                    <!-- {{ user.data }} -->
                                    <strong v-if="AddonsUsers.current_tab == 'sellers'">{{ user.data.denominazione_operatore_azienda  || 'N/A' }}</strong> 
                                    <strong v-if="AddonsUsers.current_tab == 'buyers'">{{ user.data.travel_agent_name  || 'N/A' }}</strong> 
                                    <strong v-if="AddonsUsers.current_tab == 'exhibitors'">{{ user.data.company_name  || 'N/A' }}</strong> 
                                    <!-- <strong>{{ user.data.company_name || 'N/A' }}</strong>  -->
                                    <!-- <strong v-if="user.data.eventuale_altra_denominazione"> ( {{ user.data.eventuale_altra_denominazione|| 'N/A' }} )</strong> -->
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
                                    <span @click.stop="DownloadBadgePDFWithQRCode(user)" class="tfhb-edit-btn tfhb-flexbox tfhb-justify-center tfhb-align-center tfhb-gap-4">
                                        {{ $tfhb_trans('Badge') }}
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
                            <h3> {{ $tfhb_trans('Basic Information') }} </h3>
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
                            <!-- {{ AddonsUsers.user_details_popup.user_data.data }} -->
                            <h3>{{ $tfhb_trans('Registration Data') }}</h3>
                            <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-12" style="flex-direction: column;">
                                <template v-for="(value, key) in AddonsUsers.user_details_popup.user_data.data" :key="key">
                                    <div v-if="key != 'more_details_fields'"
                                    class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: 100% !important;">
                                    <Icon name="FileText" size="20" />  
                                    <div v-if="key == 'staff'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div v-if="Array.isArray(value)" class="tfhb-array-list">
                                           
                                            <div v-for="item in value" :key="item" class="tfhb-flexbox tfhb-gap-8"  >
                                                <img v-if="item.image" :src="item.image" alt="" style="height: 60px; width: 60px; object-fit: cover;"> 
                                                <div>
                                                    <h5> <strong>Name: </strong> {{ item.name }} </h5>
                                                    <p> <strong>Position: </strong>{{ item.position }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                    <div v-else-if="key == 'documents'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div v-if="Array.isArray(value)" class="tfhb-array-list">
                                           
                                            <div v-for="item in value" :key="item" class="tfhb-flexbox tfhb-gap-8"  >  
                                                <div>
                                                    <h5> <strong>Title: </strong> {{ item.title }} </h5>
                                                    <p> <strong>Subtitle: </strong>{{ item.subtitle }}</p> 
                                                    <p> <a :href="item.url" target="_blank"> View Document</a></p> 
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                    <div v-else-if="key == 'links'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong> 
                                        <span v-if="Array.isArray(value)" class="tfhb-array-list">
                                            <span v-for="item in value" :key="item" class="tfhb-array-item">
                                                <a :href="item.url" target="_blank"> {{ item.title }}</a>
                                            </span>
                                        </span>
                                    </div> 
                                    <div v-else-if="key == 'gallery'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong> 
                                        <span v-if="Array.isArray(value)" class="tfhb-array-list">
                                            <span v-for="item in value" :key="item" class="tfhb-array-item">
                                                <a :href="item.url" target="_blank"> {{ item.title || 'View Image' }}</a>
                                            </span>
                                        </span>
                                    </div>
                                    <div v-else-if="key == 'video'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div  >  
                                            <span><strong>Title : </strong> {{ value.title }}</span>
                                            <br>
                                            <span><strong>Description : </strong> {{ value.description }}</span>
                                            <br>
                                            <span><strong>Url : </strong>  <a :href="value.url" target="_blank">  View Video</a></span>
                                        </div>
                                    </div>
                                    <div v-else-if="key == 'video'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div  >  
                                            <span><strong>Title : </strong> {{ value.title }}</span>
                                            <br>
                                            <span><strong>Description : </strong> {{ value.description }}</span>
                                            <br>
                                            <span><strong>Url : </strong>  <a :href="value.url" target="_blank">  View Video</a></span>
                                        </div>
                                    </div>
                                    <div v-else-if="key == 'social_share'" class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>   
                                        <span  class="tfhb-array-list"> 
                                            <span class="tfhb-array-item">   <a :href="value.instagram" target="_blank"> Instagram</a> </span>
                                            <span class="tfhb-array-item">   <a :href="value.facebook" target="_blank"> Facebook</a> </span>
                                            <span class="tfhb-array-item">   <a :href="value.youtube" target="_blank"> Youtube</a> </span>
                                            <span class="tfhb-array-item">   <a :href="value.linkedin" target="_blank"> Linkedin</a> </span>
                                        </span>
                                    </div>
                                    <div v-else class="tfhb-booking-details">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>
                                        <span v-if="Array.isArray(value)" class="tfhb-array-list">
                                            <span v-for="item in value" :key="item" class="tfhb-array-item"> {{ item }}</span>
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
                                </template>
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

.tfhb-array-item  a{
    color: #fff !important; 
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
 