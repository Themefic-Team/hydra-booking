<script setup> 
import { ref, onBeforeMount, defineProps  } from 'vue'; 
 
import HbText from '@/components/form-fields/HbText.vue'
 
import HbButton from '@/components/form-fields/HbButton.vue';
</script>

<template > 
    <div class="thb-event-dashboard">
   
        <div class="tfhb-content-wrap">
          
            <!-- Date And Time --> 
            <div class="tfhb-admin-title" >
                <h2>{{ $tfhb_trans('Public Information') }}</h2> 
                <p>{{ $tfhb_trans('Configure your date and time') }}</p>
            </div>
            <div class="tfhb-admin-card-box ">   
                <div class="tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between">
                        <!-- Time Zone -->
                        <HbText  
                            v-model="Fullname"  
                            required= "true"  
                            :label="$tfhb_trans('Full Name')"  
                            selected = "1"
                            :placeholder="$tfhb_trans('Type your Admin Email')" 
                            width="50" 
                        />
                        <!-- Date And Time -->
                        
                </div> 
                <HbButton 
                    classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation tfhb-mt-16"  
                    :buttonText="$tfhb_trans('Update Public Information')"
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true" 
                />  
        
            </div>  
            <!-- Date And Time -->

            <!-- Date And Time --> 
            <div class="tfhb-admin-title" >
                <h2>{{ $tfhb_trans('Change password') }}</h2> 
                <!-- <p>{{ $tfhb_trans('Configure your date and time') }}</p> -->
            </div>
            <div class="tfhb-admin-card-box ">   
                <div class="tfhb-general-card tfhb-flexbox tfhb-gap-tb-24 tfhb-justify-between">
                    <!-- Time Zone -->
                   
                    <HbText  
                        v-model="old_password"  
                        required= "true"  
                        :label="$tfhb_trans('New Password')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Type your Admin Email')" 
                        width="50" 
                    />
                    <!-- Date And Time -->
                    <HbText  
                        v-model="old_password"  
                        required= "true"  
                        :label="$tfhb_trans('Confirm New Password')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Type your Admin Email')" 
                        width="50" 
                    />
                    <HbText  
                        v-model="old_password"  
                        required= "true"  
                        :label="$tfhb_trans('Old Password')"  
                        selected = "1"
                        :placeholder="$tfhb_trans('Type your Admin Email')" 
                        width="50" 
                    />
                    <!-- Date And Time -->
                        
                </div> 
                <HbButton 
                    classValue="tfhb-btn boxed-btn flex-btn tfhb-icon-hover-animation tfhb-mt-16"  
                    :buttonText="$tfhb_trans('Cnange Old Password')"
                    icon="ChevronRight" 
                    hover_icon="ArrowRight" 
                    :hover_animation="true" 
                />  
        
            </div>  
            <!-- Date And Time -->

           

        </div>
    </div>
 
    
</template>  
<style scoped lang="scss">
.thb-event-dashboard{
    padding: 16px;
    background-color: #fff;
    border-radius: 16px; 
}

</style>