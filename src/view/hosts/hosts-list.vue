<script setup>
import { __ } from '@wordpress/i18n';
import { ref, reactive, onBeforeMount } from 'vue';
import { onBeforeRouteLeave  } from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'
import HbPopup from '@/components/widgets/HbPopup.vue';
import HbButton from '@/components/form-fields/HbButton.vue';
const emit = defineEmits(["delete-host", "update-host-status"]); 
const props = defineProps({
    host_list: {
        type: Object,
        required: true
    },
    host_skeleton: {
        type: Boolean,
        required: true
    }
});

const deletePopup = ref(false)
const deleteItem = reactive({
    id: 0,
    user_id: 0
});
const deleteItemData = (id, user_id) => {
    deleteItem.id = id;
    deleteItem.user_id = user_id;
    deletePopup.value = true;
}
const deleteItemConfirm = () => { 
    emit('delete-host', deleteItem.id, deleteItem.user_id)
    deletePopup.value = false;
}


const activeItemDropdown = ref(0);
// on click add class active
const activeSingleHostDropdown = (id) => {
    if(activeItemDropdown.value == id) {
        activeItemDropdown.value = 0;
        return;
    }
    activeItemDropdown.value = id; 

}
 
// if click outside the dropdown
 
function hideDropdownOutsideClick(e) {
    if (!document.querySelector('.tfhb-hosts-list-wrap').contains(e.target)) {
        activeItemDropdown.value = 0;
    }
}
onBeforeMount(() => {  
    window.addEventListener('click', hideDropdownOutsideClick); 
}); 
onBeforeRouteLeave((to, from, next) => {
    activeItemDropdown.value = 0; 
    window.removeEventListener('click', hideDropdownOutsideClick);
    next();
})

</script>
<template>
    <HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="400px" name="first-modal">
        <template #header> 
            
        </template>

        <template #content>  
            <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                <div class="tfhb-close-icon">
                    <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                </div>
                <div class="tfhb-close-content">
                    <h3>{{ $tfhb_trans('Are you absolutely sure?') }}  </h3>  
                    <p>{{ $tfhb_trans('Deleting the host from settings will remove all data and bookings linked to this meeting. Previously scheduled meetings will not be impacted.') }}</p>
                </div>
                <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                    <HbButton 
                        classValue="tfhb-btn secondary-btn tfhb-flexbox tfhb-gap-8" 
                        @click=" deletePopup = !deletePopup"
                        :buttonText="$tfhb_trans('Cancel')" 
                    />  
                    <HbButton  
                        classValue="tfhb-btn boxed-btn-danger tfhb-flexbox tfhb-gap-8" 
                        @click="deleteItemConfirm"
                        :buttonText="$tfhb_trans('Delete')"
                        icon="Trash2"   
                        :hover_animation="false" 
                        icon_position = 'left'
                    />
                   
                </div>
            </div> 
        </template> 
    </HbPopup>

    <div class="tfhb-hosts-list-content" >
        <div class="tfhb-hosts-list-wrap tfhb-flexbox" :class="{ 'tfhb-skeleton': host_skeleton }"> 
            <!-- Single Hosts -->
            <div   v-for="(host, key) in host_list"  class="tfhb-single-hosts"> 
                <div class="tfhb-single-hosts-wrap ">
                    <span class="tfhb-hosts-status" v-if="host.status == 'activate'">{{ $tfhb_trans('Active') }}</span> 
                    <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span>

                    <div class="tfhb-hosts-info tfhb-flexbox">
                        <div class="hosts-avatar" >
                            <img  v-if="host.avatar !='' " :src="host.avatar" alt="Hosts Avatar">
                            <img  v-else  :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                        </div>
                        <div class="hosts-details">
                            <h3>{{ host.first_name }} {{ host.last_name }}</h3> 
                        </div>
                    </div>
                    <hr>
                    <div class="tfhb-hosts-info tfhb-flexbox">  
                         <span class="tfhb-info-icon-text tfhb-flexbox" v-if="host.email !=''"> <Icon name="Mail" size=20 />{{ host.email }}</span>
                         <span class="tfhb-info-icon-text tfhb-flexbox" v-if="host.phone_number !=''"> <Icon name="Phone" size=20 />{{ host.phone_number }}</span>
                    </div>
                    <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div @click="activeSingleHostDropdown(host.id)" class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt="">
                        <transition  name="tfhb-dropdown-transition">
                            <div v-if="host.id == activeItemDropdown" class="tfhb-dropdown-wrap"> 
                                <!-- route link -->
                                <router-link :to="{ name: 'HostsProfile', params: { id: host.id } }" class="tfhb-dropdown-single"><Icon name="SquarePen" size=16 />{{ $tfhb_trans('Edit') }}</router-link>
                                <!-- <span class="tfhb-dropdown-single">Duplicate</span> -->
                                <span class="tfhb-dropdown-single" @click="emit('update-host-status',host.id, host.user_id, host.status)">{{host.status == 'activate' ? 'Deactivate' : 'Activate'}}</span>
                        
                                <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="deleteItemData(host.id, host.user_id)"><Icon name="Trash2" size=16 />{{ $tfhb_trans('Delete') }}</span>
                            </div>
                        </transition>
                    </div>
                </div> 
            </div>
            <!-- Single Hosts -->
            
        </div>

    </div>
   
</template>


 