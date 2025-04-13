import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 
import Papa from 'papaparse';
const importExport = reactive({
    skeleton: false, 
    progress: 0,
    importing: false,
    progressInterval: null,
    booking: {
        column: {},  
        import_column: {},  
        import_file: null,
        import_data: {},
        rearrange_column: {},
        import_status: false,
        import_progress: 0, // shuld be 0 to 100 dynamically
    }, 
    meeting: {
        steps: 'start',
        column: {},  
        import_column: {},  
        import_file: null,
        is_overwrite: false,
        import_data: {},
        rearrange_column: {},
        import_status: false,
        import_progress: 0, // shuld be 0 to 100 dynamically
    }, 
    async GetImportExportData() { 
      
        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                this.booking.column = response.data.booking_column;
                this.meeting.column = response.data.meeting_column;
                
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {
            console.log(error);
        }  
    },

    // export Booking Data
    async exportBooking() { 
        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/export-meeting-csv', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce
                } 
            } );
    
            if (response.data.status) {  
 
                // export csv file data
                const url = window.URL.createObjectURL(new Blob([response.data.data]));
                const link = document.createElement('a');
                const file_name = response.data.file_name;
    
                link.href = url;
    
                link.setAttribute('download', file_name);
    
                // Append to the DOM
                document.body.appendChild(link);
                link.click();
    
                // Clean up
                link.remove();
                window.URL.revokeObjectURL(url);
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });   
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {
            console.log(error);
        }  
    },
    // Other Information 
    async readBookingImportData(event) {   
        const file = event.target.files[0];
        Papa.parse(file, { 
            header: false,
            json: true,
            complete: function(results) { 
                importExport.booking.import_data = results.data;
                // get first row as column
                if (results.data.length > 0) {
                    importExport.booking.import_column = results.data[0];
                } 
                

                for (let i = 0; i < importExport.booking.import_column.length; i++) { 

                   
                    let element = importExport.booking.import_column[i];
                    importExport.booking.rearrange_column[element] = importExport.booking.import_column[i];
                    
                } 
                 
            }
        });    
    },
    // Other Information 
    async readMeetingImportData(event) {   
        const file = event;
        // const file = event.target.files[0];
        Papa.parse(file, { 
            header: false,
            json: true,
            complete: function(results) { 
                importExport.meeting.import_data = results.data;
                // get first row as column
                if (results.data.length > 0) {
                    importExport.meeting.import_column = results.data[0];
                } 
                

                for (let i = 0; i < importExport.meeting.import_column.length; i++) { 

                   
                    let element = importExport.meeting.import_column[i];
                    importExport.meeting.rearrange_column[element] = importExport.meeting.import_column[i];
                    
                } 
                 
            }
        });    
    },
    // Run The booking import
    async importMeeting() { 
        this.importing = true;
        this.progress = 0;
        // Simulate progress
        this.progressInterval = setInterval(() => {
            if (this.progress < 90) {
            this.progress += 1;
            }
        }, 100);

        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/import-meeting', {
                data: this.meeting.import_data,
                column: this.meeting.rearrange_column
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                this.progress = 100;
                this.meeting.steps = 'completed';
                this.importing = false;
                // scrool to top
                window.scrollTo(0, 0);
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });   
            }else{

                this.importing = false;
                this.progress = 100;
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {

            this.importing = false;
            this.progress = 100;
            console.log(error);
        }  
    },
     // Run The booking import
     async importBooking() { 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/import-booking', {
                data: this.booking.import_data,
                column: this.booking.rearrange_column
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });   
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {
            console.log(error);
        }  
    },

    // export Meetings Data
    async exportMeetings(exportData) { 
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/export-meetings', exportData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
 
               
                const fileContent = response.data.data; 
                let blob; // Declare blob outside the if-else

                if ('CSV' === exportData.type) {
                    blob = new Blob([fileContent], { type: "text/csv" }); // Set correct CSV MIME type
                } else {
                    blob = new Blob([fileContent], { type: "text/calendar" }); // iCal MIME type
                }
                // export csv file data
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                const file_name = response.data.file_name;

                link.href = url;

                link.setAttribute('download', file_name);

                // Append to the DOM
                document.body.appendChild(link);
                link.click();

                // Clean up
                link.remove();
                window.URL.revokeObjectURL(url);
                toast.success(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });   
                exportAsPreloader.value = false;
                ExportAsCSV.value = false;
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
            }
        } catch (error) {
            console.log(error);
        }  
    }
   

})

export { importExport }