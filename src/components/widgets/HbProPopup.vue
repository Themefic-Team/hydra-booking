<script setup>
import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import axios from 'axios' 
import Icon from '@/components/icon/LucideIcon.vue'

import HbButton from '@/components/form-fields/HbButton.vue';
import HbText from '../form-fields/HbText.vue';
import HbSelect from '../form-fields/HbSelect.vue';
import HbDateTime from '../form-fields/HbDateTime.vue';
import { toast } from "vue3-toastify";  
 

const props = defineProps({
  isOpen: {
        type: Boolean,
        required: true
    },
  availabilityDataSingle: {},
  timeZone: {}, 
  max_width: {
    type: String,
    default: '600px'
  },
  gap: {
    type: String,
    default: '24px'
  }, 
  enableAvailabilityClass: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: ''
  }
});
const emit = defineEmits([ "modal-close" ]); 
const showData = ref(false);

 

</script>
 
 

<template> 
    <div  v-show="props.isOpen"  class="tfhb-popup "  :class="props.class, {'tfhb-popup-open': props.isOpen, 'tfhb-popup-close': !props.isOpen, 'tfhb-availability-popup': enableAvailabilityClass}" > 
      <span class="tfhb-popup-overlay" @click.stop="emit('modal-close')"></span>  
      <div class="tfhb-popup-wrap tfhb-scrollbar" :style="{ 'max-width': max_width }">
          <div v-if="props.isOpen" >
            <div  class="tfhb-dashboard-heading tfhb-flexbox tfhb-m-0">
                <div class="tfhb-admin-title">  
                </div>
                <div class="thb-admin-btn"> 
                    <span class="tfhb-popup-close tfhb-cursor-pointer" @click.stop="emit('modal-close')"><Icon name="X" size=20 /> </span> 
                </div> 
            </div>
            <div class="tfhb-content-wrap tfhb-flexbox" :style="{ 'gap': gap }">  
                <div class="tfhb-closing-confirmation-pupup tfhb-flexbox tfhb-gap-24">
                    <div class="tfhb-close-icon">
                        <img :src="$tfhb_url+'/assets/images/delete-icon.svg'" alt="">
                    </div>
                    <div class="tfhb-close-content">
                        <h3>{{ $tfhb_trans('Unlock Pro Feature') }}  </h3>  
                        <p>{{ $tfhb_trans('This feature is available in the Pro version of Hydra Booking. Upgrade now to access advanced booking options and premium support.') }}</p>
                    </div>
                    <div class="tfhb-close-btn tfhb-flexbox tfhb-gap-16">  
                        <HbButton  
                            classValue="tfhb-btn boxed-btn tfhb-flexbox tfhb-gap-8" 
                            @click="deleteItemConfirm"
                            :buttonText="$tfhb_trans('Upgrade to Hydra Booking Pro')"
                            icon="LockKeyholeOpen"   
                            :hover_animation="false" 
                            icon_position = 'left'
                        /> 
                    </div>
                </div> 
            </div> 
          </div>
          
        </div> 
    </div>
   

</template>
  