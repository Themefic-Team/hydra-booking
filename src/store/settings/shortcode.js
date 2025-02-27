import { reactive } from 'vue';
import axios from 'axios'; 
import { toast } from "vue3-toastify"; 

const ShortcodeData = reactive({
    skeleton: true, 
    preview_skeleton: false,
    preview_skeleton_reset: false,
    hostsList: [], 
    categoryList: [], 
    output: '',
    // Other Information 
    async fetchShortcodeData() { 

        try {  
            const response = await axios.get(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/shortcode', {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) { 
                // response.data.settings not empty then set value
                this.hostsList =  response.data.hostsList? response.data.hostsList : this.hostsList;
                this.categoryList =  response.data.categoryList? response.data.categoryList : this.categoryList;
                this.skeleton = false;
                 
            }
        } catch (error) {

            console.log(error);

        }  
    }, 
    async generateShortPreview(shortcode) {  
        try {  
            const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/shortcode/preview', {
                shortcode: shortcode
            }, {
                headers: {
                    'X-WP-Nonce': tfhb_core_apps.rest_nonce,
                    'capability': 'tfhb_manage_options'
                } 
            } );
    
            if (response.data.status) {    
                this.preview_skeleton = false; 
                this.preview_skeleton_reset = false; 
                // responsed message bottom
                // append output in this class .preview-shortcode-content 
                this.output = response.data.output; 
                
            }
        } catch (error) {
            console.log(error);
            this.update_preloader = false;

        }  
    },
    // async  generateShortPreview(shortcode) { 

    //     try {  
    //         const response = await axios.post(tfhb_core_apps.rest_route + 'hydra-booking/v1/settings/shortcode/preview', {
    //             shortcode: shortcode
    //         },{
    //             headers: {
    //                 'X-WP-Nonce': tfhb_core_apps.rest_nonce,
    //                 'capability': 'tfhb_manage_options'
    //             } 
    //         } );
    
    //         if (response.data.status) {  
                
                 
    //         }
    //     } catch (error) {

    //         console.log(error);

    //     }  
    // }
    
})

// export default ShortcodeData;
export { ShortcodeData }