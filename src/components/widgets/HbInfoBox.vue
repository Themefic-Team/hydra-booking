<script setup>
import { ref,reactive } from 'vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbPopup from '@/components/widgets/HbPopup.vue';  
import HbText from '@/components/form-fields/HbText.vue'; 

const props = defineProps({
  title: String,
  desc: String,
  icon: String,
  isblocked: Boolean
});

const licenseing = reactive({
  isOpen: false
});

const UnlockPopup = () => {
  licenseing.isOpen = false;
}

</script>

<template>
  <HbPopup :isOpen="licenseing.isOpen" max_width="450px" name="first-modal" @modal-close="UnlockPopup()">
      <template #header> 
        <h3>{{ $tfhb_trans('Let’s Get Started!') }}</h3>
      </template>

      <template #content>  
          <p>
            {{ $tfhb_trans('Please provide your email address. We’ll send your key directly to your inbox.') }}
          </p> 
          <HbText  
            required= "true"  
            selected = "1"
            placeholder="Enter your email address" 
          />  
          <div class="tfhb-submission-btn tfhb-gap-8">
            <HbButton 
              @click="licenseing.isOpen=true"
              classValue="tfhb-btn boxed-btn flex-btn" 
              :buttonText="$tfhb_trans('Send me the License key')"
            />  
          </div> 
      </template> 
  </HbPopup>
  <div class="tfhb-info-box tfhb-flexbox tfhb-gap-16 tfhb-p-24 tfhb-full-width">
    <div class="tfhb-info-box-icon" v-if="!isblocked">
        <Icon :name="icon ?? 'Info'" :size="20" />
    </div>
    <div class="tfhb-info-box-content" :class="isblocked ? 'tfhb-flexbox tfhb-unlock-box' : 'tfhb-full-width'">
        <h3 v-if="props.title">{{ props.title }}</h3>
        <slot name="content"> {{$tfhb_trans('default content')}}</slot>
        <HbButton 
          v-if="isblocked"
          @click="licenseing.isOpen=true"
          classValue="tfhb-btn boxed-btn flex-btn" 
          :buttonText="$tfhb_trans('Unlock this Feature')"
        />  
    </div>
  </div>
</template>