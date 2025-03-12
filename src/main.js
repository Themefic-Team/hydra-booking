import './assets/main.scss' 
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/router.js'   
import PrimeVue from "primevue/config";  
import 'primevue/resources/themes/aura-light-green/theme.css' 
const tfhb_url =  tfhb_core_apps.tfhb_url || ''; 
const tfhb_hydra_admin_url =  tfhb_core_apps.tfhb_hydra_admin_url || '';  
const front_end_dashboard =  tfhb_core_apps.front_end_dashboard || false;   
const tfhb_license_type =  tfhb_core_apps.tfhb_license_type || false;   
const tfhb_is_valid =  tfhb_core_apps.tfhb_is_valid || false;   
const user =  tfhb_core_apps.user || '';    
// console.log(tfhb_core_apps); 
// Pro Plugins  checked  first tfhb_core_apps_pro is not defined
const tfhb_core_apps_pro_data = typeof tfhb_core_apps_pro !== 'undefined' ? tfhb_core_apps_pro : '';
const tfhb_is_pro = tfhb_core_apps_pro_data.tfhb_is_pro ? true : false;
const tfhb_license_status = tfhb_license_type == 'pro' ? true : false; 
const tfhb_trans = tfhb_core_apps.trans || {};  

const tfhbApps = createApp(App).use(router); 
tfhbApps.use(PrimeVue); 
// tfhbApps.component("toast", toast); 
tfhbApps.config.globalProperties.$tfhb_url = tfhb_url;  
tfhbApps.config.globalProperties.$tfhb_hydra_admin_url = tfhb_hydra_admin_url;   
tfhbApps.config.globalProperties.$tfhb_license_type = tfhb_license_type;   
tfhbApps.config.globalProperties.$tfhb_is_valid = tfhb_is_valid;   
tfhbApps.config.globalProperties.$front_end_dashboard = front_end_dashboard;   
tfhbApps.config.globalProperties.$user = user; 
tfhbApps.config.globalProperties.$tfhb_is_pro = tfhb_is_pro; 
tfhbApps.config.globalProperties.$tfhb_license_status = tfhb_license_status;  
tfhbApps.config.globalProperties.$tfhb_trans = function (text) { 
    // return tfhb_trans[text] || text + ' Not Found';
    return tfhb_trans[text] || text;
};
tfhbApps.mount('#tfhb-admin-app')

