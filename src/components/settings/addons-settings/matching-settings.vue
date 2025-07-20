<template>
  <div class="tfhb-matching-settings">
    <div class="tfhb-admin-title">
      <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
        {{ $tfhb_trans('Dynamic Matching Rules') }}
      </h2>
      <p>{{ $tfhb_trans('Configure how buyers and sellers should be matched based on their registration form data') }}</p>
    </div>

    <div class="tfhb-admin-card-box tfhb-gap-24 tfhb-m-0">
      <!-- Display existing matching rules using HbQuestion component -->
      <div v-if="settings.matching_rules.length > 0" class="tfhb-matching-rules-wrap tfhb-mb-16">
        <HbQuestion 
          :question_value="matchingRulesAsQuestions"
          :skip_remove="-1"
          @question-edit="editMatchingRule"
          @question-remove="removeMatchingRule"
        />
      </div>

      <!-- Add new rule button -->
      <button class="tfhb-btn tfhb-flexbox tfhb-gap-8" @click="addMatchingRule">
        <Icon name="PlusCircle" :width="20"/>
        {{ $tfhb_trans('Add Matching Rule') }}
      </button>

      <!-- Popup for adding/editing matching rules -->
      <HbPopup :isOpen="matchingRulePopup" @modal-close="matchingRulePopup = false" max_width="500px" name="matching-rule-modal">
        <template #header> 
          <h3>{{ $tfhb_trans('Configure Matching Rule') }}</h3>
        </template>

        <template #content>  
          <div class="tfhb-matching-rule-form">
            <!-- Buyer Field Selection -->
            <HbDropdown 
              v-model="matching_rule_data.buyer_field"
              required="true" 
              :label="$tfhb_trans('Buyer Field')"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Buyer Field')"
              :option="buyerFields.map(field => ({name: field.label, value: field.name}))"
            />

            <!-- Seller Field Selection -->
            <HbDropdown 
              v-model="matching_rule_data.seller_field"
              required="true" 
              :label="$tfhb_trans('Seller Field')"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Seller Field')"
              :option="sellerFields.map(field => ({name: field.label, value: field.name}))"
            />

            <!-- Priority Setting -->
            <div class="tfhb-single-form-field" style="width: 100%">
              <div class="tfhb-single-form-field-wrap">
                <label>{{ $tfhb_trans('Priority') }} <span>*</span></label>
                <input 
                  v-model="matching_rule_data.priority"
                  type="number" 
                  min="1" 
                  max="10"
                  placeholder="1"
                  class="tfhb-form-input"
                />
                <small>{{ $tfhb_trans('Higher number = higher priority') }}</small>
              </div>
            </div>

            <!-- Match Type Selection -->
            <HbDropdown 
              v-model="matching_rule_data.match_type"
              required="true" 
              :label="$tfhb_trans('Match Type')"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Match Type')"
              :option="[
                {name: 'Exact Match', value: 'exact'},
                {name: 'Contains', value: 'contains'},
                {name: 'Similar', value: 'similar'},
                {name: 'Category Match', value: 'category'}
              ]"
            />
          </div>

          <!-- Action buttons -->
          <div class="tfhb-popup-actions tfhb-flexbox tfhb-gap-16">
            <HbButton 
              classValue="tfhb-btn tfhb-btn-secondary" 
              @click="matchingRulePopup = false"
              :buttonText="$tfhb_trans('Cancel')"
              :hover_animation="false"
            />
            <HbButton 
              classValue="tfhb-btn tfhb-btn-primary" 
              @click="saveMatchingRule"
              :buttonText="$tfhb_trans('Save Rule')"
              icon="Check"
              :hover_animation="true"
            />
          </div>
        </template> 
      </HbPopup>
    </div>

    <!-- Save all rules button -->
    <HbButton 
      classValue="tfhb-btn boxed-btn flex-btn tfhb-mt-16" 
      @click="saveSettings" 
      :buttonText="$tfhb_trans('Save Matching Rules')"
      icon="ChevronRight" 
      hover_icon="ArrowRight" 
      :hover_animation="true"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { __ } from '@wordpress/i18n'
import axios from 'axios'
import { AddonsSettings } from '@/store/settings/addons-settings'
import { toast } from "vue3-toastify"

// Import core design components
import HbQuestion from '@/components/widgets/HbQuestion.vue'
import HbQuestionForm from '@/components/widgets/HbQuestionForm.vue'
import HbPopup from '@/components/widgets/HbPopup.vue'
import HbSwitch from '@/components/form-fields/HbSwitch.vue'
import HbButton from '@/components/form-fields/HbButton.vue'
import HbDropdown from '@/components/form-fields/HbDropdown.vue'
import Icon from '@/components/icon/LucideIcon.vue'

// Popup state
const matchingRulePopup = ref(false)

// Reactive data
const settings = ref({
  matching_rules: [
    {
      buyer_field: '',
      seller_field: '',
      priority: 1,
      match_type: 'exact'
    }
  ]
})

const errors = ref({})
const skeleton = ref(true)

// Buyer and Seller Fields (dynamically loaded from registration form builders)
const buyerFields = ref([])
const sellerFields = ref([])

// Matching rule data for popup
const matching_rule_data = reactive({
  key: 0,
  buyer_field: '',
  seller_field: '',
  priority: 1,
  match_type: 'exact'
})

// Load fields from registration form builders
const loadFormFields = () => {
  // Load buyer fields from buyers settings
  const buyersSettings = AddonsSettings.buyers
  if (buyersSettings && buyersSettings.registration_froms_fields) {
    buyerFields.value = buyersSettings.registration_froms_fields
      .filter(field => field.enable !== 0) // Include all fields except explicitly disabled ones
      .map(field => ({
        name: field.name,
        label: field.label,
        type: field.type,
        options: field.options || []
      }))
  }

  // Load seller fields from sellers settings  
  const sellersSettings = AddonsSettings.Sellers
  if (sellersSettings && sellersSettings.registration_froms_fields) {
    sellerFields.value = sellersSettings.registration_froms_fields
      .filter(field => field.enable !== 0) // Include all fields except explicitly disabled ones
      .map(field => ({
        name: field.name,
        label: field.label,
        type: field.type,
        options: field.options || []
      }))
  }
}

// Load settings from API
const loadSettings = async () => {
  try {
    skeleton.value = true
    const response = await axios.get('/wp-json/tfhb/v1/matching-settings')
    
    if (response.data.status) {
      settings.value = {
        matching_rules: response.data.settings.matching_rules ?? [],
      }
      
      // Load buyer and seller fields from API response
      if (response.data.buyer_fields) {
        buyerFields.value = response.data.buyer_fields
      }
      if (response.data.seller_fields) {
        sellerFields.value = response.data.seller_fields
      }
    }
  } catch (error) {
    console.error('Error loading matching settings:', error)
    errors.value.load = 'Failed to load settings'
  } finally {
    skeleton.value = false
  }
}

// Save settings
const saveSettings = async () => {
  try {
    const response = await axios.post('/wp-json/tfhb/v1/matching-settings', settings.value)
    
    if (response.data.status) {
      if (typeof toast !== 'undefined') {
        toast.success(response.data.message || 'Matching rules saved successfully')
      }
    } else {
      throw new Error(response.data.message || 'Failed to save settings')
    }
  } catch (error) {
    console.error('Error saving settings:', error)
    if (typeof toast !== 'undefined') {
      toast.error(error.message || 'Failed to save settings')
    }
  }
}

// Add new matching rule
const addMatchingRule = () => {
  settings.value.matching_rules.push({
    buyer_field: '',
    seller_field: '',
    priority: 1,
    match_type: 'exact'
  })
  
  const lastIndex = settings.value.matching_rules.length - 1
  matching_rule_data.key = lastIndex
  matching_rule_data.buyer_field = ''
  matching_rule_data.seller_field = ''
  matching_rule_data.priority = 1
  matching_rule_data.match_type = 'exact'
  
  matchingRulePopup.value = true
}

// Edit matching rule
const editMatchingRule = (index) => {
  const rule = settings.value.matching_rules[index]
  matching_rule_data.key = index
  matching_rule_data.buyer_field = rule.buyer_field
  matching_rule_data.seller_field = rule.seller_field
  matching_rule_data.priority = rule.priority
  matching_rule_data.match_type = rule.match_type
  
  matchingRulePopup.value = true
}

// Save matching rule from popup
const saveMatchingRule = () => {
  // Validate required fields
  if (!matching_rule_data.buyer_field || !matching_rule_data.seller_field) {
    toast.error('Please select both buyer and seller fields')
    return
  }
  
  // Update the rule
  settings.value.matching_rules[matching_rule_data.key] = {
    buyer_field: matching_rule_data.buyer_field,
    seller_field: matching_rule_data.seller_field,
    priority: matching_rule_data.priority,
    match_type: matching_rule_data.match_type
  }
  
  matchingRulePopup.value = false
}

// Remove matching rule
const removeMatchingRule = (index) => {
  settings.value.matching_rules.splice(index, 1)
}

// Convert matching rules to question format for HbQuestion component
const matchingRulesAsQuestions = computed(() => {
  return settings.value.matching_rules.map((rule, index) => ({
    label: `Rule ${index + 1}: ${rule.buyer_field} ↔ ${rule.seller_field}`,
    type: rule.match_type,
    name: `rule_${index}`,
    placeholder: `Priority: ${rule.priority}`,
    options: [],
    required: 1,
    enable: 1,
    // Store original rule data
    buyer_field: rule.buyer_field,
    seller_field: rule.seller_field,
    priority: rule.priority,
    match_type: rule.match_type
  }))
})

// Lifecycle
onMounted(() => {
  loadSettings()
  loadFormFields() // Load fields when component mounts
})

// Watch for changes in registration form fields and reload
watch(() => AddonsSettings.buyers.registration_froms_fields, () => {
  loadFormFields()
}, { deep: true })

watch(() => AddonsSettings.Sellers.registration_froms_fields, () => {
  loadFormFields()
}, { deep: true })
</script>

<style scoped>
.tfhb-matching-settings {
  max-width: 100%;
}

.tfhb-matching-rule-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tfhb-popup-actions {
  margin-top: 1.5rem;
  justify-content: flex-end;
}

.tfhb-form-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 3px;
  font-size: 0.9rem;
}

.tfhb-form-input:focus {
  outline: none;
  border-color: #0073aa;
}

.tfhb-matching-rules-wrap {
  margin-bottom: 1rem;
}
</style> 