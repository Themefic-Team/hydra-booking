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
        // Validate user object
        if (!user) {
            throw new Error('User object is required');
        }
        
        // Extract user data with better fallbacks
        const userName = user.name || 
                        (user.first_name && user.last_name ? `${user.first_name} ${user.last_name}`.trim() : '') || 
                        user.display_name || 
                        'User Name';
        
        const userEmail = user.email || 
                          user.user_email || 
                          'No Email';
        
        const userRole = user.role || 'Participant';
        
        // Create QR code data with more comprehensive information
        const qr_data = `Name: ${userName} | Role: ${userRole} | Email: ${userEmail}`;
        
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
        
        // Determine background image based on user role
        if (user.role === 'buyers' || user.role === 'Buyers') {
            backgroundImageUrl = AddonsSettings?.buyers?.badge_pdf_image || '';
        } else if (user.role === 'sellers' || user.role === 'Sellers') {
            backgroundImageUrl = AddonsSettings?.Sellers?.badge_pdf_image || '';
        } else if (user.role === 'exhibitors' || user.role === 'Exhibitors') {
            backgroundImageUrl = AddonsSettings?.Exhibitors?.badge_pdf_image || '';
        }
        
        // Function to create PDF without background
        const createPDFWithoutBackground = () => {
            try {
                // Calculate bottom right quadrant positions
                const quadrantWidth = pageWidth / 2;
                const quadrantHeight = pageHeight / 2;
                const startX = quadrantWidth;
                const startY = quadrantHeight;
                
                // Add QR code (centered in bottom right quadrant)
                const qrSize = 35;
                const qrX = startX + (quadrantWidth - qrSize) / 2;
                const qrY = startY + 70;
                pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                
                // Helper function to fit text within available width
                const fitTextToWidth = (text, maxWidth, initialFontSize, minFontSize = 8) => {
                    pdf.setFontSize(initialFontSize);
                    let currentWidth = pdf.getTextWidth(text);
                    let fontSize = initialFontSize;
                    
                    while (currentWidth > maxWidth && fontSize > minFontSize) {
                        fontSize -= 0.5;
                        pdf.setFontSize(fontSize);
                        currentWidth = pdf.getTextWidth(text);
                    }
                    
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
                
                // Add role (centered in bottom right quadrant, below QR code)
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                const jobTitleResult = fitTextToWidth(userRole, quadrantWidth - 10, 10);
                pdf.setFontSize(jobTitleResult.fontSize);
                let currentY = startY + 110;
                jobTitleResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < jobTitleResult.lines.length - 1) currentY += 5;
                });
                
                // Add user name
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

                // Role-specific data
                if (user.role === 'Buyers' || user.role === 'buyers') {
                    // Company name
                    currentY += 7;
                    const companyName = (user.data?.travel_agent_name || '').trim();
                    if (companyName) {
                        pdf.setFont('helvetica', 'normal');
                        const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                        pdf.setFontSize(companyResult.fontSize);
                        companyResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < companyResult.lines.length - 1) currentY += 5;
                        });
                    }

                    // Nation
                    currentY += 7;
                    let nation = '';
                    if (Array.isArray(user.data?.nation)) {
                        nation = user.data.nation.join(', ');
                    } else if (typeof user.data?.nation === 'object' && user.data?.nation !== null) {
                        nation = Object.values(user.data.nation).join(', ');
                    } else if (typeof user.data?.nation === 'string') {
                        nation = user.data.nation;
                    }
                    if (nation) {
                        const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                        pdf.setFontSize(nationResult.fontSize);
                        nationResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < nationResult.lines.length - 1) currentY += 5;
                        });
                    }
                }

                if (user.role === 'Sellers' || user.role === 'sellers') {
                    // Company name
                    currentY += 7;
                    const companyName = (user.data?.denominazione_operatore_azienda || '').trim();
                    if (companyName) {
                        pdf.setFont('helvetica', 'normal');
                        const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                        pdf.setFontSize(companyResult.fontSize);
                        companyResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < companyResult.lines.length - 1) currentY += 5;
                        });
                    }

                    // Region
                    currentY += 7;
                    const nation = user.data?.regione || '';
                    if (nation) {
                        const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                        pdf.setFontSize(nationResult.fontSize);
                        nationResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < nationResult.lines.length - 1) currentY += 5;
                        });
                    }
                }

                if (user.role === 'Exhibitors' || user.role === 'exhibitors') {
                    // Company name
                    currentY += 7;
                    const companyName = (user.data?.company_name || '').trim();
                    if (companyName) {
                        pdf.setFont('helvetica', 'normal');
                        const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                        pdf.setFontSize(companyResult.fontSize);
                        companyResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < companyResult.lines.length - 1) currentY += 5;
                        });
                    }
                }
                
                // Save the PDF
                const fileName = `badge_${userName.replace(/\s+/g, '_')}_${Date.now()}.pdf`;
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
        
        // Function to load image and create PDF
        const createPDFWithBackground = () => {
            try {
                // Add background image if available
                if (backgroundImageUrl) {
                    pdf.addImage(backgroundImageUrl, 'PNG', 0, 0, pageWidth, pageHeight);
                }
                
                // Calculate bottom right quadrant positions
                const quadrantWidth = pageWidth / 2;
                const quadrantHeight = pageHeight / 2;
                const startX = quadrantWidth;
                const startY = quadrantHeight;
                
                // Add QR code (centered in bottom right quadrant)
                const qrSize = 35;
                const qrX = startX + (quadrantWidth - qrSize) / 2;
                const qrY = startY + 70;
                pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                
                // Helper function to fit text within available width
                const fitTextToWidth = (text, maxWidth, initialFontSize, minFontSize = 8) => {
                    pdf.setFontSize(initialFontSize);
                    let currentWidth = pdf.getTextWidth(text);
                    let fontSize = initialFontSize;
                    
                    while (currentWidth > maxWidth && fontSize > minFontSize) {
                        fontSize -= 0.5;
                        pdf.setFontSize(fontSize);
                        currentWidth = pdf.getTextWidth(text);
                    }
                    
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
                
                // Add role (centered in bottom right quadrant, below QR code)
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                const jobTitleResult = fitTextToWidth(userRole, quadrantWidth - 10, 10);
                pdf.setFontSize(jobTitleResult.fontSize);
                let currentY = startY + 110;
                jobTitleResult.lines.forEach((line, index) => {
                    const lineWidth = pdf.getTextWidth(line);
                    pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                    if (index < jobTitleResult.lines.length - 1) currentY += 5;
                });
                
                // Add user name
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

                // Role-specific data
                if (user.role === 'Buyers' || user.role === 'buyers') {
                    // Company name
                    currentY += 7;
                    const companyName = (user.data?.travel_agent_name || '').trim();
                    if (companyName) {
                        pdf.setFont('helvetica', 'normal');
                        const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                        pdf.setFontSize(companyResult.fontSize);
                        companyResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < companyResult.lines.length - 1) currentY += 5;
                        });
                    }

                    // Nation
                    currentY += 7;
                    let nation = '';
                    if (Array.isArray(user.data?.nation)) {
                        nation = user.data.nation.join(', ');
                    } else if (typeof user.data?.nation === 'object' && user.data?.nation !== null) {
                        nation = Object.values(user.data.nation).join(', ');
                    } else if (typeof user.data?.nation === 'string') {
                        nation = user.data.nation;
                    }
                    if (nation) {
                        const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                        pdf.setFontSize(nationResult.fontSize);
                        nationResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < nationResult.lines.length - 1) currentY += 5;
                        });
                    }
                }

                if (user.role === 'Sellers' || user.role === 'sellers') {
                    // Company name
                    currentY += 7;
                    const companyName = (user.data?.denominazione_operatore_azienda || '').trim();
                    if (companyName) {
                        pdf.setFont('helvetica', 'normal');
                        const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                        pdf.setFontSize(companyResult.fontSize);
                        companyResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < companyResult.lines.length - 1) currentY += 5;
                        });
                    }

                    // Region
                    currentY += 7;
                    const nation = user.data?.regione || '';
                    if (nation) {
                        const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                        pdf.setFontSize(nationResult.fontSize);
                        nationResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < nationResult.lines.length - 1) currentY += 5;
                        });
                    }
                }

                if (user.role === 'Exhibitors' || user.role === 'exhibitors') {
                    // Company name
                    currentY += 7;
                    const companyName = (user.data?.company_name || '').trim();
                    if (companyName) {
                        pdf.setFont('helvetica', 'normal');
                        const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                        pdf.setFontSize(companyResult.fontSize);
                        companyResult.lines.forEach((line, index) => {
                            const lineWidth = pdf.getTextWidth(line);
                            pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                            if (index < companyResult.lines.length - 1) currentY += 5;
                        });
                    }
                }
                
                // Save the PDF
                const fileName = `badge_${userName.replace(/\s+/g, '_')}_${Date.now()}.pdf`;
                pdf.save(fileName);
                
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
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            img.onload = () => {
                try {
                    createPDFWithBackground();
                } catch (error) {
                    console.error('Error creating PDF with background:', error);
                    createPDFWithoutBackground();
                }
            };
            
            img.onerror = () => {
                console.warn('Background image failed to load, creating PDF without background');
                createPDFWithoutBackground();
            };
            
            img.src = backgroundImageUrl;
        } else {
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

const DownloadBadgeStaffPDFWithQRCode = async (user) => {
    try { 
        console.log('Starting staff badge generation for user:', user);
        
        // Validate user object
        if (!user || !user.data) {
            throw new Error('User object or user data is required');
        }
        
        // Get staff array from user data
        const staffArray = user.data.staff || [];
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
        
        // Determine user role and get appropriate company name and badge image
        let companyName = '';
        let backgroundImageUrl = '';
        const userRole = (user.role || '').toLowerCase();
        
        if (userRole === 'buyers' || userRole === 'buyer') {
            companyName = (user.data?.travel_agent_name || '').trim();
            backgroundImageUrl = AddonsSettings?.buyers?.badge_pdf_image || '';
        } else if (userRole === 'sellers' || userRole === 'seller') {
            companyName = (user.data?.denominazione_operatore_azienda || '').trim();
            backgroundImageUrl = AddonsSettings?.Sellers?.badge_pdf_image || '';
        } else if (userRole === 'exhibitors' || userRole === 'exhibitor') {
            companyName = (user.data?.company_name || '').trim();
            backgroundImageUrl = AddonsSettings?.Exhibitors?.badge_pdf_image || '';
        } else {
            // Fallback for unknown roles
            companyName = (user.data?.company_name || user.data?.denominazione_operatore_azienda || user.data?.travel_agent_name || '').trim();
        }
        
        // Generate PDF for each present staff member
        for (let i = 0; i < presentStaff.length; i++) {
            const member = presentStaff[i];
            const staffName = member.name || `Staff ${i + 1}`;
            const staffPosition = member.position || 'Staff';
            
            try {
                // Create QR code data for staff member
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
                
                // Add role/position label (centered in bottom right quadrant, below QR code)
                pdf.setTextColor(0, 0, 0);
                pdf.setFont('helvetica', 'normal');
                const roleLabel = 'STAFF';
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
                
                // Add position (centered, below company)
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
                const fileName = `staff_badge_${staffName.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '')}_${Date.now()}_${i}.pdf`;
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
                        // Extract user data with better fallbacks
                        const userName = user.name || 
                                        (user.first_name && user.last_name ? `${user.first_name} ${user.last_name}`.trim() : '') || 
                                        user.display_name || 
                                        'User Name';
                        
                        const userEmail = user.email || 
                                          user.user_email || 
                                          'No Email';
                        
                        const userRole = user.role || 'Participant';
                        
                        // Create QR code data
                        const qr_data = `Name: ${userName} | Role: ${userRole} | Email: ${userEmail}`;
                        
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
                        
                        if (user.role === 'buyers' || user.role === 'Buyers') {
                            backgroundImageUrl = AddonsSettings?.buyers?.badge_pdf_image || '';
                        } else if (user.role === 'sellers' || user.role === 'Sellers') {
                            backgroundImageUrl = AddonsSettings?.Sellers?.badge_pdf_image || '';
                        } else if (user.role === 'exhibitors' || user.role === 'Exhibitors') {
                            backgroundImageUrl = AddonsSettings?.Exhibitors?.badge_pdf_image || '';
                        }

                        // Helper function to fit text within available width
                        const fitTextToWidth = (text, maxWidth, initialFontSize, minFontSize = 8) => {
                            pdf.setFontSize(initialFontSize);
                            let currentWidth = pdf.getTextWidth(text);
                            let fontSize = initialFontSize;
                            
                            while (currentWidth > maxWidth && fontSize > minFontSize) {
                                fontSize -= 0.5;
                                pdf.setFontSize(fontSize);
                                currentWidth = pdf.getTextWidth(text);
                            }
                            
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

                        // Function to add content to PDF (without background)
                        const addPDFContent = () => {
                            // Calculate bottom right quadrant positions
                            const quadrantWidth = pageWidth / 2;
                            const quadrantHeight = pageHeight / 2;
                            const startX = quadrantWidth;
                            const startY = quadrantHeight;
                            
                            // Add QR code
                            const qrSize = 35;
                            const qrX = startX + (quadrantWidth - qrSize) / 2;
                            const qrY = startY + 70;
                            pdf.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                            
                            // Add role
                            pdf.setTextColor(0, 0, 0);
                            pdf.setFont('helvetica', 'normal');
                            const jobTitleResult = fitTextToWidth(userRole, quadrantWidth - 10, 10);
                            pdf.setFontSize(jobTitleResult.fontSize);
                            let currentY = startY + 110;
                            jobTitleResult.lines.forEach((line, index) => {
                                const lineWidth = pdf.getTextWidth(line);
                                pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                                if (index < jobTitleResult.lines.length - 1) currentY += 5;
                            });
                            
                            // Add user name
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

                            // Role-specific data
                            if (user.role === 'Buyers' || user.role === 'buyers') {
                                currentY += 7;
                                const companyName = (user.data?.travel_agent_name || '').trim();
                                if (companyName) {
                                    pdf.setFont('helvetica', 'normal');
                                    const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                                    pdf.setFontSize(companyResult.fontSize);
                                    companyResult.lines.forEach((line, index) => {
                                        const lineWidth = pdf.getTextWidth(line);
                                        pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                                        if (index < companyResult.lines.length - 1) currentY += 5;
                                    });
                                }

                                currentY += 7;
                                let nation = '';
                                if (Array.isArray(user.data?.nation)) {
                                    nation = user.data.nation.join(', ');
                                } else if (typeof user.data?.nation === 'object' && user.data?.nation !== null) {
                                    nation = Object.values(user.data.nation).join(', ');
                                } else if (typeof user.data?.nation === 'string') {
                                    nation = user.data.nation;
                                }
                                if (nation) {
                                    const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                                    pdf.setFontSize(nationResult.fontSize);
                                    nationResult.lines.forEach((line, index) => {
                                        const lineWidth = pdf.getTextWidth(line);
                                        pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                                        if (index < nationResult.lines.length - 1) currentY += 5;
                                    });
                                }
                            }

                            if (user.role === 'Sellers' || user.role === 'sellers') {
                                currentY += 7;
                                const companyName = (user.data?.denominazione_operatore_azienda || '').trim();
                                if (companyName) {
                                    pdf.setFont('helvetica', 'normal');
                                    const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                                    pdf.setFontSize(companyResult.fontSize);
                                    companyResult.lines.forEach((line, index) => {
                                        const lineWidth = pdf.getTextWidth(line);
                                        pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                                        if (index < companyResult.lines.length - 1) currentY += 5;
                                    });
                                }

                                currentY += 7;
                                const nation = user.data?.regione || '';
                                if (nation) {
                                    const nationResult = fitTextToWidth(nation, quadrantWidth - 10, 9);
                                    pdf.setFontSize(nationResult.fontSize);
                                    nationResult.lines.forEach((line, index) => {
                                        const lineWidth = pdf.getTextWidth(line);
                                        pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                                        if (index < nationResult.lines.length - 1) currentY += 5;
                                    });
                                }
                            }

                            if (user.role === 'Exhibitors' || user.role === 'exhibitors') {
                                currentY += 7;
                                const companyName = (user.data?.company_name || '').trim();
                                if (companyName) {
                                    pdf.setFont('helvetica', 'normal');
                                    const companyResult = fitTextToWidth(companyName, quadrantWidth - 10, 9);
                                    pdf.setFontSize(companyResult.fontSize);
                                    companyResult.lines.forEach((line, index) => {
                                        const lineWidth = pdf.getTextWidth(line);
                                        pdf.text(line, startX + (quadrantWidth - lineWidth) / 2, currentY);
                                        if (index < companyResult.lines.length - 1) currentY += 5;
                                    });
                                }
                            }
                        };

                        // Try to load background image first if URL exists
                        let pdfDoc;
                        if (backgroundImageUrl) {
                            const img = new Image();
                            img.crossOrigin = 'anonymous';
                            
                            const pdfPromise = new Promise((resolve, reject) => {
                                img.onload = () => {
                                    try {
                                        // Add background image
                                        pdf.addImage(backgroundImageUrl, 'PNG', 0, 0, pageWidth, pageHeight);
                                        addPDFContent();
                                        resolve(pdf);
                                    } catch (error) {
                                        reject(error);
                                    }
                                };
                                
                                img.onerror = () => {
                                    try {
                                        // Create PDF without background
                                        addPDFContent();
                                        resolve(pdf);
                                    } catch (error) {
                                        reject(error);
                                    }
                                };
                                
                                img.src = backgroundImageUrl;
                            });

                            pdfDoc = await pdfPromise;
                        } else {
                            // No background image, create PDF directly
                            addPDFContent();
                            pdfDoc = pdf;
                        }

                        const pdfBlob = pdfDoc.output('blob');
                        
                        // Add PDF to zip with sanitized filename
                        const sanitizedName = userName.replace(/[^a-zA-Z0-9]/g, '_') || 'user';
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
                                    <span @click.stop="DownloadBadgeStaffPDFWithQRCode(user)" class="tfhb-edit-btn tfhb-flexbox tfhb-justify-center tfhb-align-center tfhb-gap-4">
                                        {{ $tfhb_trans('Staff Badge') }}
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
                            <h3>{{ $tfhb_trans('Registration Data') }}</h3>
                            <div class="tfhb-booking-info-inner tfhb-flexbox tfhb-gap-12" style="flex-direction: column;">
                                <template v-for="(value, key) in AddonsUsers.user_details_popup.user_data.data" :key="key">
                                    <div v-if="key != 'more_details_fields'"
                                    class="tfhb-single-booking-info tfhb-flexbox tfhb-gap-8" style="width: 100% !important;">
                                    <Icon name="FileText" size="20" />  
                                    
                                    <!-- Staff Display -->
                                    <div v-if="key == 'staff'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div v-if="Array.isArray(value) && value.length > 0" class="tfhb-staff-preview-list">
                                            <div v-for="(item, idx) in value" :key="idx" class="tfhb-staff-preview-item">
                                                <img v-if="item.image" :src="item.image" alt="" class="tfhb-staff-preview-image"> 
                                                <div class="tfhb-staff-preview-info">
                                                    <h5> <strong>Name: </strong> {{ item.name || 'N/A' }} </h5>
                                                    <p> <strong>Position: </strong>{{ item.position || 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No staff members added') }}</span>
                                    </div> 
                                    
                                    <!-- Documents Display -->
                                    <div v-else-if="key == 'documents'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div v-if="Array.isArray(value) && value.length > 0" class="tfhb-documents-preview-list">
                                            <div v-for="(item, idx) in value" :key="idx" class="tfhb-document-preview-item">  
                                                <img v-if="item.icon" :src="item.icon" alt="" class="tfhb-document-preview-icon">
                                                <div class="tfhb-document-preview-info">
                                                    <h5> <strong>Title: </strong> {{ item.title || 'N/A' }} </h5>
                                                    <p v-if="item.subtitle"> <strong>Subtitle: </strong>{{ item.subtitle }}</p> 
                                                    <p v-if="item.url"> <a :href="item.url" target="_blank" class="tfhb-view-document-link"> 📄 View Document</a></p> 
                                                </div>
                                            </div>
                                        </div>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No documents added') }}</span>
                                    </div> 
                                    
                                    <!-- Links Display -->
                                    <div v-else-if="key == 'links'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong> 
                                        <span v-if="Array.isArray(value) && value.length > 0" class="tfhb-array-list">
                                            <span v-for="(item, idx) in value" :key="idx" class="tfhb-array-item">
                                                <a :href="item.url" target="_blank"> 🔗 {{ item.title || item.url }}</a>
                                            </span>
                                        </span>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No links added') }}</span>
                                    </div> 
                                    
                                    <!-- Gallery Display -->
                                    <div v-else-if="key == 'gallery'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong> 
                                        <div v-if="Array.isArray(value) && value.length > 0" class="tfhb-gallery-preview-grid">
                                            <div v-for="(item, idx) in value" :key="idx" class="tfhb-gallery-preview-item">
                                                <img v-if="item.url" :src="item.url" :alt="item.title || 'Gallery Image'" class="tfhb-gallery-preview-image">
                                                <p v-if="item.title" class="tfhb-gallery-preview-title">{{ item.title }}</p>
                                            </div>
                                        </div>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No gallery images added') }}</span>
                                    </div>
                                    
                                    <!-- Video Display -->
                                    <div v-else-if="key == 'video'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>  
                                        <div v-if="value && (value.title || value.url)" class="tfhb-video-preview">  
                                            <p v-if="value.title"><strong>Title: </strong> {{ value.title }}</p>
                                            <p v-if="value.description"><strong>Description: </strong> {{ value.description }}</p>
                                            <p v-if="value.url"><strong>URL: </strong>  <a :href="value.url" target="_blank" class="tfhb-view-video-link">📺 View Video</a></p>
                                        </div>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No video added') }}</span>
                                    </div>
                                    
                                    <!-- Social Share Display -->
                                    <div v-else-if="key == 'social_share'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>   
                                        <div v-if="value && (value.instagram || value.facebook || value.youtube || value.linkedin)" class="tfhb-social-preview-list"> 
                                            <a v-if="value.instagram" :href="value.instagram" target="_blank" class="tfhb-social-preview-item">📷 Instagram</a>
                                            <a v-if="value.facebook" :href="value.facebook" target="_blank" class="tfhb-social-preview-item">📘 Facebook</a>
                                            <a v-if="value.youtube" :href="value.youtube" target="_blank" class="tfhb-social-preview-item">📺 YouTube</a>
                                            <a v-if="value.linkedin" :href="value.linkedin" target="_blank" class="tfhb-social-preview-item">💼 LinkedIn</a>
                                        </div>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No social media links added') }}</span>
                                    </div>
                                    
                                    <!-- Cover Image & Avatar Display -->
                                    <div v-else-if="key == 'cover_image' || key == 'avatar' || key == 'companey_logo'" class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>
                                        <div v-if="value" class="tfhb-image-preview-container">
                                            <img :src="value" :alt="key" class="tfhb-image-preview">
                                        </div>
                                        <span v-else class="tfhb-empty-value">{{ $tfhb_trans('No image uploaded') }}</span>
                                    </div>
                                    
                                    <!-- Default Display for other fields -->
                                    <div v-else class="tfhb-booking-details tfhb-full-width">
                                        <strong>{{ key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}:</strong>
                                        <span v-if="Array.isArray(value)" class="tfhb-array-list">
                                            <span v-for="(item, idx) in value" :key="idx" class="tfhb-array-item"> {{ item }}</span>
                                        </span>
                                        <span v-else-if="typeof value === 'object' && value !== null" class="tfhb-object-value">
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
                                            <div v-if="typeof value === 'string' && !key.includes('url') && !key.includes('image') && !getFieldType(key) === 'select'" class="tfhb-field-input">
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

/* Preview Styles for Complex Data Structures */

/* Staff Preview */
.tfhb-staff-preview-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 0.5rem;
    width: 100%;
}

.tfhb-staff-preview-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fb;
    border: 1px solid #e3e6ed;
    border-radius: 8px;
}

.tfhb-staff-preview-image {
    height: 60px;
    width: 60px;
    object-fit: cover;
    border-radius: 50%;
    flex-shrink: 0;
}

.tfhb-staff-preview-info {
    flex: 1;
}

.tfhb-staff-preview-info h5 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #2d3748;
}

.tfhb-staff-preview-info p {
    margin: 0;
    font-size: 0.875rem;
    color: #4a5568;
}

/* Documents Preview */
.tfhb-documents-preview-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 0.5rem;
    width: 100%;
}

.tfhb-document-preview-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fb;
    border: 1px solid #e3e6ed;
    border-left: 4px solid #0073aa;
    border-radius: 8px;
}

.tfhb-document-preview-icon {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}

.tfhb-document-preview-info {
    flex: 1;
}

.tfhb-document-preview-info h5 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #2d3748;
}

.tfhb-document-preview-info p {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
    color: #4a5568;
}

.tfhb-view-document-link {
    color: #0073aa !important;
    text-decoration: none;
    font-weight: 500;
}

.tfhb-view-document-link:hover {
    text-decoration: underline;
}

/* Gallery Preview */
.tfhb-gallery-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 0.5rem;
    width: 100%;
}

.tfhb-gallery-preview-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.tfhb-gallery-preview-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e3e6ed;
}

.tfhb-gallery-preview-title {
    margin: 0;
    font-size: 0.75rem;
    color: #4a5568;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Video Preview */
.tfhb-video-preview {
    margin-top: 0.5rem;
    padding: 1rem;
    background: #f8f9fb;
    border: 1px solid #e3e6ed;
    border-radius: 8px;
}

.tfhb-video-preview p {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: #4a5568;
}

.tfhb-video-preview p:last-child {
    margin-bottom: 0;
}

.tfhb-view-video-link {
    color: #0073aa !important;
    text-decoration: none;
    font-weight: 500;
}

.tfhb-view-video-link:hover {
    text-decoration: underline;
}

/* Social Media Preview */
.tfhb-social-preview-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.tfhb-social-preview-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #0073aa;
    color: #fff !important;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background-color 0.2s;
}

.tfhb-social-preview-item:hover {
    background: #005a87;
}

/* Image Preview */
.tfhb-image-preview-container {
    margin-top: 0.5rem;
}

.tfhb-image-preview {
    max-width: 200px;
    max-height: 200px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e3e6ed;
}

/* Full Width Helper */
.tfhb-full-width {
    width: 100% !important;
}

/* Responsive Adjustments for Previews */
@media (max-width: 768px) {
    .tfhb-staff-preview-item,
    .tfhb-document-preview-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .tfhb-gallery-preview-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .tfhb-gallery-preview-image {
        height: 100px;
    }
    
    .tfhb-social-preview-list {
        flex-direction: column;
    }
    
    .tfhb-social-preview-item {
        width: 100%;
        justify-content: center;
    }
}
</style>
 