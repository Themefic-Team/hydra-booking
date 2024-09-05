import './assets/main.scss' 
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/router.js'   
import PrimeVue from "primevue/config"; 
import 'primevue/resources/themes/aura-light-green/theme.css'
const tfhb_trans = tfhb_core_apps.trans || {};  
const tfhb_url =  tfhb_core_apps.tfhb_url || ''; 
const tfhb_hydra_admin_url =  tfhb_core_apps.tfhb_hydra_admin_url || '';  
const user =  tfhb_core_apps.user || '';      
// Pro Plugins  checked  first tfhb_core_apps_pro is not defined
const tfhb_core_apps_pro_data = typeof tfhb_core_apps_pro !== 'undefined' ? tfhb_core_apps_pro : '';
const tfhb_is_pro = tfhb_core_apps_pro_data.tfhb_is_pro ? true : false;


const tfhbApps = createApp(App).use(router); 
tfhbApps.use(PrimeVue);
 
// tfhbApps.component("toast", toast);
tfhbApps.config.globalProperties.$tfhb_trans = tfhb_trans;
tfhbApps.config.globalProperties.$tfhb_url = tfhb_url;  
tfhbApps.config.globalProperties.$tfhb_hydra_admin_url = tfhb_hydra_admin_url;  
tfhbApps.config.globalProperties.$user = user; 
tfhbApps.config.globalProperties.$tfhb_is_pro = tfhb_is_pro; 

tfhbApps.mount('#tfhb-admin-app')

