<script setup>
import { ref, reactive } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbPopup from '@/components/widgets/HbPopup.vue';
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

</script>
<template>
    <HbPopup :isOpen="deletePopup" @modal-close="deletePopup = !deletePopup" max_width="400px" name="first-modal">
        <template #header> 
            <!-- {{ google_calendar }} -->
            <h2>{{ $tfhb_trans['Confirmation'] }}</h2>
            
        </template>

        <template #content>  
            <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                <div class="tfhb-close-icon">
                    <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                </div>
                <div class="tfhb-close-content">
                    <h3>{{ $tfhb_trans['Are you absolutely sure??'] }}  </h3>  
                    <p>{{ $tfhb_trans['Data and bookings associated with this meeting will be deleted. It will not affect previously scheduled meetings.'] }}</p>
                </div>
                <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16"> 
                    <button class="tfhb-btn secondary-btn flex-btn" @click=" deletePopup = !deletePopup">{{ $tfhb_trans['Cancel'] }}</button>
                    <button class="tfhb-btn boxed-btn flex-btn" @click="deleteItemConfirm">{{ $tfhb_trans['Delete'] }}</button>
                </div>
            </div> 
        </template> 
    </HbPopup>

    <div class="tfhb-hosts-list-content" >
        <div class="tfhb-hosts-list-wrap tfhb-flexbox" :class="{ 'tfhb-skeleton': host_skeleton }"> 
            <!-- Single Hosts -->
            <div   v-for="(host, key) in host_list"  class="tfhb-single-hosts"> 
                <div class="tfhb-single-hosts-wrap ">
                    <span class="tfhb-hosts-status" v-if="host.status == 'activate'">{{ $tfhb_trans['Activate'] }}</span> 
                    <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans['Deactivate'] }}</span>

                    <div class="tfhb-hosts-info tfhb-flexbox">
                        <div class="hosts-avatar" v-if="host.avatar !='' " >
                            <img :src="host.avatar" alt="Hosts Avatar">
                        </div>
                        <div class="hosts-details">
                            <h3>{{ host.first_name }} {{ host.last_name }}</h3> 
                        </div>
                    </div>
                    <hr>
                    <div class="tfhb-hosts-info tfhb-flexbox">  
                         <span class="tfhb-info-icon-text tfhb-flexbox" v-if="host.email !=''"> <Icon name="Mail" size="20" />{{ host.email }}</span>
                         <span class="tfhb-info-icon-text tfhb-flexbox" v-if="host.phone_number !=''"> <Icon name="Phone" size="20" />{{ host.phone_number }}</span>
                    </div>
                    <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size="20" /></button> -->
                    <div class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt="">
                        <div class="tfhb-dropdown-wrap"> 
                            <!-- route link -->
                            <router-link :to="{ name: 'HostsProfile', params: { id: host.user_id } }" class="tfhb-dropdown-single">{{ $tfhb_trans['View & Edit'] }}</router-link>
                            <!-- <span class="tfhb-dropdown-single">Duplicate</span> -->
                            <span class="tfhb-dropdown-single" @click="emit('update-host-status',host.id, host.user_id, host.status)">{{host.status == 'activate' ? 'Deactivate' : 'Activate'}}</span>
                       
                            <!-- <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="emit('delete-host', host.id, host.user_id)">{{ $tfhb_trans['Delete'] }}</span> -->
                            <span class="tfhb-dropdown-single tfhb-dropdown-error" @click="deleteItemData(host.id, host.user_id)">{{ $tfhb_trans['Delete'] }}</span>
                         </div>
                    </div>
                </div> 
            </div>
            <!-- Single Hosts -->
            
        </div>

    </div>
   
</template>


 