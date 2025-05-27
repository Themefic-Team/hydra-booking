import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 
import Papa from 'papaparse';
const importExport = reactive({
    skeleton: false, 
    progress: 0,
    importing: false,
    host_preloader: false,
    progressInterval: null,
    meeting_list: {},
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
    host: {
        steps: 'start',
        column: {},  
        import_column: {},  
        import_file: null,
        is_overwrite: true,
        is_create_new_user: true,
        import_data: {},
        rearrange_column: {},
        import_status: false,
        import_progress: 0, // shuld be 0 to 100 dynamically
    }, 
    booking: {
        steps: 'start',
        column: {},  
        import_column: {},  
        import_file: null,
        is_overwrite: true,
        is_default_meeting: false,
        default_meeting_id: 0,
        import_data: {},
        rearrange_column: {},
        import_status: false,
        import_progress: 0, // shuld be 0 to 100 dynamically
    }, 
    allData: {
        steps: 'start',
        column: [],  
        import_column: {},  
        select_import: {},   
        is_overwrite_host: true,
        is_create_new_user: true,
        is_overwrite_meeting: true,
        is_overwrite_booking: true,
        is_default_meeting: true,
        default_meeting_id: 0,
        import_file: null, 
        import_data: {}, 
        import_status: false,
        import_progress: 0, // shuld be 0 to 100 dynamically
    }, 
    async GetImportExportData() { 
      
        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce, 
                } 
            } );
    
            if (response.data.status) {  
                this.booking.column = response.data.booking_column;
                this.meeting.column = response.data.meeting_column;
                this.host.column = response.data.host_column;
                this.meeting_list = response.data.meeting_list;
                
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
    async readAllImportData(file) {   
        if (file && file.type === "application/json") {
            const reader = new FileReader();
        
            reader.onload = (e) => {
              try {
                this.allData.import_data = JSON.parse(e.target.result);
                // foreach data 
                Object.entries(this.allData.import_data).forEach(([key, value]) => {
                    if(key == 'settings' || key == 'tfhb_hosts' || key == 'tfhb_meetings' || key == 'tfhb_bookings' ){
                        var name ='';
                        if(key == 'settings'){
                            name = 'Settings';
                        }
                        if(key == 'tfhb_hosts'){
                            name = 'Hosts'
                        }
                        if(key == 'tfhb_meetings'){
                            name = 'Meetings'
                        }
                         if(key == 'tfhb_bookings'){
                            name = 'Bookings'
                        }
                        this.allData.column.push(name)
                        // add data into object 
                        this.allData.import_column[name] = key;

                    }
                });
                 
                
              } catch (err) {
                console.error("Error parsing JSON:", err);
              }
            };
        
            reader.onerror = (e) => {
              console.error("Error reading file:", e);
            };
        
            reader.readAsText(file);
          } else {
            console.warn("Invalid file type. Please upload a .json file.");
          }
    },

     // Import All Data
     async importAllData() { 
        // if this.allData.select_import is empty return mesage
        if(Object.keys(this.allData.select_import).length === 0) {
            toast.error('Please select at least one item to import', {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
        }
        this.importing = true;
        this.progress = 0;
        // Simulate progress
        this.progressInterval = setInterval(() => {
            if (this.progress < 90) {
            this.progress += 1;
            }
        }, 100);

        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/import-all-data', {
                import_data: this.allData.import_data,  
                select_import: this.allData.select_import,   
                is_overwrite_host: this.allData.is_overwrite_host,  
                is_create_new_user: this.allData.is_create_new_user,  
                is_overwrite_meeting: this.allData.is_overwrite_meeting,  
                is_overwrite_booking: this.allData.is_overwrite_booking,  
                is_default_meeting: this.allData.is_default_meeting,  
                default_meeting_id: this.allData.default_meeting_id,  
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
        const file = event;
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
    // Other Information 
    async readHostImportData(event) {   
        const file = event;
        // const file = event.target.files[0];
        Papa.parse(file, { 
            header: false,
            json: true,
            complete: function(results) { 
                importExport.host.import_data = results.data;
                // get first row as column
                if (results.data.length > 0) {
                    importExport.host.import_column = results.data[0];
                } 
                

                for (let i = 0; i < importExport.host.import_column.length; i++) { 

                   
                    let element = importExport.host.import_column[i];
                    importExport.host.rearrange_column[element] = importExport.host.import_column[i];
                    
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
                column: this.meeting.rearrange_column,
                is_overwrite: this.meeting.is_overwrite,
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
    async importHost() { 
        this.importing = true;
        this.progress = 0;
        // Simulate progress
        this.progressInterval = setInterval(() => {
            if (this.progress < 90) {
            this.progress += 1;
            }
        }, 100);

        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/import-host', {
                data: this.host.import_data,
                column: this.host.rearrange_column,
                is_overwrite: this.host.is_overwrite,
                is_create_new_user: this.host.is_create_new_user,
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                this.progress = 100;
                this.host.steps = 'completed';
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
        this.importing = true;
        this.progress = 0;
        // Simulate progress
        this.progressInterval = setInterval(() => {
            if (this.progress < 90) {
            this.progress += 1;
            }
        }, 100);

        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/import-booking', {
                data: this.booking.import_data,
                columns: this.booking.rearrange_column,
                is_overwrite: this.booking.is_overwrite,
                is_default_meeting: this.booking.is_default_meeting,
                default_meeting_id: this.booking.default_meeting_id,
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                this.progress = 100;
                this.booking.steps = 'completed';
                this.importing = false;
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
    },

     // export Meetings Data
     async exportHosts() { 
        this.host_preloader = true;
        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/export-hosts',{
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
 
               
                const fileContent = response.data.data; 
                let blob; // Declare blob outside the if-else

                blob = new Blob([fileContent], { type: "text/csv" }); // Set correct CSV MIME type
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
                this.host_preloader = false;
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                this.host_preloader = false;
            }
        } catch (error) {
            console.log(error);
        }  
    },
    
     // Export All Data
     async exportAllData(exportData) { 

        // if exportData empty return the message
        if (exportData.select_export == '') {
            
            toast.error('Please select the data to export', {
                position: 'bottom-right', // Set the desired position
                "autoClose": 1500,
            });
            return false;
        }
        // this.host_preloader = true;
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/import-export/export-all-data', exportData, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {  
                // make a json file based on this json return data
                const fileContent = response.data.data; 
                let blob; // Declare blob outside the if-else

                blob = new Blob([fileContent], { type: "text/json" }); // Set correct json
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
                })

  
            }else{
                toast.error(response.data.message, {
                    position: 'bottom-right', // Set the desired position
                    "autoClose": 1500,
                });
                this.host_preloader = false;
            }
        } catch (error) {
            console.log(error);
        }  
    }
 

})

export { importExport }