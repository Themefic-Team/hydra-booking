<script setup>

import { ref, reactive, onBeforeMount, } from 'vue'; 
import { useRouter, RouterView,} from 'vue-router' 
import Icon from '@/components/icon/LucideIcon.vue'

// import Form Field 
import HbSelect from '@/components/form-fields/HbSelect.vue' 
import HbText from '@/components/form-fields/HbText.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue';
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbRadio from '@/components/form-fields/HbRadio.vue';
const outlookCalPopup = ref(false);

 

const props = defineProps([
    'outlook_calendar', 
    'class', 
    'display', 
])
const emit = defineEmits([ "update-integrations", ]);  

const storedOptionData = (data) => {
    let options = [];
    // data suild be array single array
    data.forEach((item, index) => {  
        options.push({
            value: item.id,
            label: item.title,
        });
    }); 
    return options;
}
</script>
 
<template> 
      <!-- Zoom Integrations  -->
      <div :class="props.class" class="tfhb-integrations-single-block tfhb-admin-card-box tfhb-pro">
         <div :class="display =='list' ? 'tfhb-flexbox' : '' " class="tfhb-admin-cartbox-cotent">
            <span class="tfhb-integrations-single-block-icon">
                <img :src="$tfhb_url+'/assets/images/outlook-calendar.png'" alt="" >
            </span> 

            <div class="cartbox-text">
                <h3>{{ $tfhb_trans['Outlook Calendar'] }}</h3> 
                <p>{{ $tfhb_trans['New standard in online payment'] }}</p>

            </div>
        </div> 
        <div class="tfhb-integrations-single-block-btn tfhb-flexbox"> 
            <span class="tfhb-badge tfhb-badge-pro not-absolute tfhb-flexbox tfhb-gap-8"> <Icon name="Crown" size="20" /> {{ $tfhb_trans['Pro'] }}</span>
           
        </div>
 
    </div>  
    <!-- Single Integrations  -->

</template>
 

<style scoped>
</style> 