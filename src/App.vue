<script setup> 
import "vue3-loading-skeleton/dist/style.css"; 
import 'vue3-toastify/dist/index.css';
const front_end_dashboard =  tfhb_core_apps.front_end_dashboard || false;  

// import front end dashboard
import FrontEndDashboard from './view/FrontendDashboard/Frontend.vue';


// Update local Storage when page reload
let webHash = window.location.hash;
let hashParts = webHash.split('/');
let requiredPart = hashParts[1];
if("meetings"==requiredPart){
  localStorage.setItem('currentMenuItemIndex', 2);
}else if("bookings"==requiredPart){
  localStorage.setItem('currentMenuItemIndex', 3);
}else if("hosts"==requiredPart){
  localStorage.setItem('currentMenuItemIndex', 4);
}else if("settings"==requiredPart){
  localStorage.setItem('currentMenuItemIndex', 5);
}else{
  localStorage.setItem('currentMenuItemIndex', 1);
}

 
if(!front_end_dashboard){
  //Admin Menu Active
  const tfhbn_parentMenuItem = document.getElementById('toplevel_page_hydra-booking');
  const tfhb_menuItems = tfhbn_parentMenuItem.querySelectorAll('li');
  const tfhb_parentMenuLinks = document.querySelectorAll('a.toplevel_page_hydra-booking');
    // Set Active class when submenu Clickl
  tfhb_menuItems.forEach((item, index) => {
    item.addEventListener('click', () => {
      tfhb_menuItems.forEach(item => {
        item.classList.remove('current');
      });
      item.classList.add('current');
      currentIndex = index;
      localStorage.setItem('currentMenuItemIndex', currentIndex);
    });
  });

  // Set 1 to Localstorage when click parent Menu
  tfhb_parentMenuLinks.forEach(link => {
    link.addEventListener('click', (event) => {
      tfhb_menuItems.forEach((item, index) => {
        if(1==index){
          item.classList.add('current');
        }else{
        item.classList.remove('current');
        }
      });
      localStorage.setItem('currentMenuItemIndex', 1);
    });
  });

  // Set the initial current menu item
  let currentIndex = localStorage.getItem('currentMenuItemIndex');
  if (currentIndex !== null) {
    tfhb_menuItems[currentIndex].classList.add('current');
  }

}


// if tfhb-setup-wizard has class add class into body else remove
const tfhb_setup_wizard = document.querySelector('.tfhb-setup-wizard');
if(tfhb_setup_wizard){
  document.body.classList.add('tfhb-setup-wizard-body');
}else{
  document.body.classList.remove('tfhb-setup-wizard-body');
}
// .tfhb-pro:after on click add alert this is pro version
const tfhb_pro = document.querySelector('.tfhb-pro');
if(tfhb_pro){
  tfhb_pro.addEventListener('click', () => {
    alert('This is Pro Version');
  });
}
 
</script>

<template > 
    <FrontEndDashboard v-if="front_end_dashboard == true" />
    <router-view v-else /> 
</template>  
<style scoped>  
 
</style>