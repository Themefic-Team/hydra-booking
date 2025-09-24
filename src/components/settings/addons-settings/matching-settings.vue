
<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { __ } from '@wordpress/i18n'
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
const matchingRulePopup = ref(false);
const dynamic_matching = ref(true);

// Reactive data
const settings = ref({
  matching_rules: [
    {
      buyer_field: '',
      seller_field: '',
      priority: 1,
      match_type: 'exact',
      field_mappings: []
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
  match_type: 'exact',
  field_mappings: [] // Array of value-to-value mappings
})

// Field mapping popup state
const fieldMappingPopup = ref(false)
const currentFieldMapping = reactive({
  key: 0,
  buyer_value: '',
  seller_value: '',
  enabled: true
})

import HbDateTime from '@/components/form-fields/HbDateTime.vue';

// Load fields from registration form builders
const loadFormFields = () => {
  // Load buyer fields from buyers settings
  buyerFields.value = AddonsSettings.getBuyerFields()

  // Load seller fields from sellers settings  
  sellerFields.value = AddonsSettings.getSellerFields()
}

// Load settings from API
const loadSettings = async () => {
  try {
    skeleton.value = true
    const response = await AddonsSettings.FetchMatchingSettings()
    
    if (response && response.status) {
      settings.value = {
        matching_rules: (response.settings && response.settings.matching_rules) ? response.settings.matching_rules : [],
      }
      
      // Load buyer and seller fields from API response if available
      if (response.buyer_fields) {
        buyerFields.value = response.buyer_fields
      }
      if (response.seller_fields) {
        sellerFields.value = response.seller_fields
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
    // Update the matching settings in the store
    AddonsSettings.matching_settings.matching_rules = settings.value.matching_rules;
    AddonsSettings.matching_settings.matching_start_date = AddonsSettings.matching_settings.matching_start_date;
    
    await AddonsSettings.UpdateMatchingSettings();
    
    // if (typeof toast !== 'undefined') {
    //   toast.success('Matching rules saved successfully');
    // }
  } catch (error) {
    console.error('Error saving settings:', error);
    if (typeof toast !== 'undefined') {
      toast.error(error.message || 'Failed to save settings');
    }
  }
}

// Add new matching rule
const addMatchingRule = () => {
  const newRule = {
    buyer_field: '',
    seller_field: '',
    priority: 1,
    match_type: 'exact',
    field_mappings: []
  }
  
  settings.value.matching_rules.push(newRule)
  const lastIndex = settings.value.matching_rules.length - 1
  matching_rule_data.key = lastIndex
  matching_rule_data.buyer_field = ''
  matching_rule_data.seller_field = ''
  matching_rule_data.priority = 1
  matching_rule_data.match_type = 'exact'
  matching_rule_data.field_mappings = []
  
  matchingRulePopup.value = true
}

// Edit matching rule
const editMatchingRule = (index) => {
  const rule = settings.value.matching_rules[index]
  
  matching_rule_data.key = index
  matching_rule_data.buyer_field = rule.buyer_field
  matching_rule_data.seller_field = rule.seller_field
  matching_rule_data.priority = rule.priority
  matching_rule_data.match_type = rule.match_type || 'exact'
  matching_rule_data.field_mappings = rule.field_mappings || []
  
  matchingRulePopup.value = true
}

// Save matching rule from popup
const saveMatchingRule = () => {
  // Validate required fields
  if (!matching_rule_data.buyer_field || !matching_rule_data.seller_field) {
    toast.error('Please select both buyer and seller fields')
    return
  }
  
  const ruleData = {
    buyer_field: matching_rule_data.buyer_field,
    seller_field: matching_rule_data.seller_field,
    priority: matching_rule_data.priority,
    match_type: matching_rule_data.match_type,
    field_mappings: matching_rule_data.field_mappings
  }
  
  // Update the rule
  settings.value.matching_rules[matching_rule_data.key] = ruleData
  
  matchingRulePopup.value = false
}

// Remove matching rule
const removeMatchingRule = (index) => {
  settings.value.matching_rules.splice(index, 1)
}

// Check if field needs mapping (checkbox, radio, select)
const needsFieldMapping = (fieldName, isBuyer = true) => {
  const fields = isBuyer ? buyerFields.value : sellerFields.value
  const field = fields.find(f => f.name === fieldName)
  return field && ['checkbox', 'radio', 'select'].includes(field.type)
}

// Get field options for mapping
const getFieldOptions = (fieldName, isBuyer = true) => {
  const fields = isBuyer ? buyerFields.value : sellerFields.value
  const field = fields.find(f => f.name === fieldName)
  return field ? field.options || [] : []
}

// Get field label for display
const getFieldLabel = (fieldName, isBuyer = true) => {
  const fields = isBuyer ? buyerFields.value : sellerFields.value
  const field = fields.find(f => f.name === fieldName)
  return field ? field.label : fieldName
}

// Add field mapping
const addFieldMapping = () => {
  matching_rule_data.field_mappings.push({
    buyer_value: '',
    seller_value: '',
    enabled: true
  })
  
  const lastIndex = matching_rule_data.field_mappings.length - 1
  currentFieldMapping.key = lastIndex
  currentFieldMapping.buyer_value = ''
  currentFieldMapping.seller_value = ''
  currentFieldMapping.enabled = true
  
  fieldMappingPopup.value = true
}

// Edit field mapping
const editFieldMapping = (index) => {
  const mapping = matching_rule_data.field_mappings[index]
  currentFieldMapping.key = index
  currentFieldMapping.buyer_value = mapping.buyer_value
  currentFieldMapping.seller_value = mapping.seller_value
  currentFieldMapping.enabled = mapping.enabled
  
  fieldMappingPopup.value = true
}

// Save field mapping
const saveFieldMapping = () => {
  if (!currentFieldMapping.buyer_value || !currentFieldMapping.seller_value) {
    toast.error('Please select both buyer and seller values')
    return
  }
  
  matching_rule_data.field_mappings[currentFieldMapping.key] = {
    buyer_value: currentFieldMapping.buyer_value,
    seller_value: currentFieldMapping.seller_value,
    enabled: currentFieldMapping.enabled
  }
  
  fieldMappingPopup.value = false
}

// Remove field mapping
const removeFieldMapping = (index) => {
  matching_rule_data.field_mappings.splice(index, 1)
}

// Convert matching rules to question format for HbQuestion component
const matchingRulesAsQuestions = computed(() => {
  return settings.value.matching_rules.map((rule, index) => ({
    label: `Rule ${index + 1}: ${rule.buyer_field} ↔ ${rule.seller_field}`,
    type: 'exact', // Default to exact match for now
    name: `rule_${index}`,
    placeholder: `Priority: ${rule.priority}`,
    options: [],
    required: 1,
    enable: 1,
    // Store original rule data
    buyer_field: rule.buyer_field,
    seller_field: rule.seller_field,
    priority: rule.priority
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

<template>
  <div class="tfhb-matching-settings">

    <div class="tfhb-admin-title">
      <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
        {{ $tfhb_trans('Maching Option') }}
      </h2> 
      <p>{{ $tfhb_trans('Configure how buyers and sellers should be matched based on their registration form data') }}</p>
    </div>

    <!-- <div class="tfhb-admin-card-box tfhb-gap-24 tfhb-m-0">
 
 
    </div> -->

    <!-- <div class="tfhb-admin-title tfhb-mt-24">
      <h2 class="tfhb-flexbox tfhb-gap-8 tfhb-justify-normal">
        {{ $tfhb_trans('Dynamic Matching Rules') }}
      </h2>
      <p>{{ $tfhb_trans('Configure how buyers and sellers should be matched based on their registration form data') }}</p>
    </div> -->

    <div v-if="dynamic_matching" class="tfhb-admin-card-box tfhb-gap-24 tfhb-m-0"> 
      <!-- Matching Rules --> 
      <div v-if="settings.matching_rules.length > 0" class="tfhb-matching-rules-wrap tfhb-mb-16">
        <HbQuestion 
          :question_value="matchingRulesAsQuestions"
          :skip_remove="-1"
          @question-edit="editMatchingRule"
          @question-remove="removeMatchingRule"
        />
      </div>
      
      <button class="tfhb-btn tfhb-flexbox tfhb-gap-8" @click="addMatchingRule">
        <Icon name="PlusCircle" :width="20"/>
        {{ $tfhb_trans('Add Matching Rule') }}
      </button>
 
      <HbPopup :isOpen="matchingRulePopup" @modal-close="matchingRulePopup = false" max_width="500px" name="matching-rule-modal">
        <template #header> 
          <h3>{{ $tfhb_trans('Configure Matching Rule') }}</h3>
        </template>

        <template #content>  
          <div class="tfhb-matching-rule-form"> 
            <HbDropdown 
              v-model="matching_rule_data.buyer_field"
              required="true" 
              :label="$tfhb_trans('Buyer Field')"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Buyer Field')"
              :option="buyerFields.map(field => ({name: field.label, value: field.name}))"
            />
 
            <HbDropdown 
              v-model="matching_rule_data.seller_field"
              required="true" 
              :label="$tfhb_trans('Seller Field')"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Seller Field')"
              :option="sellerFields.map(field => ({name: field.label, value: field.name}))"
            />

            <HbDropdown 
              v-model="matching_rule_data.match_type"
              required="true" 
              :label="$tfhb_trans('Match Type')"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Match Type')"
              :option="[
                {name: 'Exact Match', value: 'exact'},
                {name: 'Any Match', value: 'any'},
                {name: 'Contains', value: 'contains'},
                {name: 'Starts With', value: 'starts_with'},
                {name: 'Ends With', value: 'ends_with'}
              ]"
            />
 
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
                <!-- <small>{{ $tfhb_trans('Higher number = higher priority') }}</small> -->
              </div>
            </div>

            <!-- Field Mapping Section -->
            <div v-if="needsFieldMapping(matching_rule_data.buyer_field, true) && needsFieldMapping(matching_rule_data.seller_field, false)" class="tfhb-field-mapping-section">
              <div class="tfhb-section-header">
                <h4>{{ $tfhb_trans('Field Value Mapping') }}</h4>
                <p class="tfhb-text-sm tfhb-text-muted">{{ $tfhb_trans('Map specific values between buyer and seller fields') }}</p>
              </div>
              
              <div v-if="matching_rule_data.field_mappings.length > 0" class="tfhb-mapping-list">
                <div v-for="(mapping, index) in matching_rule_data.field_mappings" :key="index" class="tfhb-mapping-item">
                  <div class="tfhb-mapping-content">
                    <span class="tfhb-mapping-buyer">{{ mapping.buyer_value }}</span>
                    <Icon name="ArrowRight" :width="16" class="tfhb-mapping-arrow"/>
                    <span class="tfhb-mapping-seller">{{ mapping.seller_value }}</span>
                    <span v-if="!mapping.enabled" class="tfhb-mapping-disabled">{{ $tfhb_trans('(Disabled)') }}</span>
                  </div>
                  <div class="tfhb-mapping-actions">
                    <button @click="editFieldMapping(index)" class="tfhb-btn-small tfhb-btn-edit">
                      <Icon name="Edit" :width="14"/>
                    </button>
                    <button @click="removeFieldMapping(index)" class="tfhb-btn-small tfhb-btn-remove">
                      <Icon name="Trash2" :width="14"/>
                    </button>
                  </div>
                </div>
              </div>
              
              <button @click="addFieldMapping" class="tfhb-btn tfhb-flexbox tfhb-gap-8 tfhb-mt-8">
                <Icon name="PlusCircle" :width="16"/>
                {{ $tfhb_trans('Add Value Mapping') }}
              </button>
            </div>
            <div class="tfhb-popup-actions tfhb-flexbox tfhb-gap-16">
              <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="matchingRulePopup = false"
                :buttonText="$tfhb_trans('Cancel')"
                :hover_animation="false"
              />
              <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="saveMatchingRule"
                :buttonText="$tfhb_trans('Save Rule')"
                icon="Check"
                :hover_animation="true"
              />
            </div>

          </div>
 
          
        </template> 
      </HbPopup>

      <!-- Field Mapping Popup -->
      <HbPopup :isOpen="fieldMappingPopup" @modal-close="fieldMappingPopup = false" max_width="500px" name="field-mapping-modal">
        <template #header> 
          <h3>{{ $tfhb_trans('Configure Value Mapping') }}</h3>
          <p class="tfhb-mapping-field-info">
            <strong>{{ $tfhb_trans('Buyer Field') }}:</strong> {{ getFieldLabel(matching_rule_data.buyer_field, true) }} 
            <span class="tfhb-mapping-separator">↔</span> 
            <strong>{{ $tfhb_trans('Seller Field') }}:</strong> {{ getFieldLabel(matching_rule_data.seller_field, false) }}
          </p>
        </template>

        <template #content>  
          <div class="tfhb-field-mapping-form"> 
            <HbDropdown 
              v-model="currentFieldMapping.buyer_value"
              required="true" 
              :label="`${$tfhb_trans('Buyer Value')} (${getFieldLabel(matching_rule_data.buyer_field, true)})`"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Buyer Value')"
              :option="getFieldOptions(matching_rule_data.buyer_field, true).map(option => ({name: option, value: option}))"
            />
 
            <HbDropdown 
              v-model="currentFieldMapping.seller_value"
              required="true" 
              :label="`${$tfhb_trans('Seller Value')} (${getFieldLabel(matching_rule_data.seller_field, false)})`"
              width="100"
              :selected="1"
              :placeholder="$tfhb_trans('Select Seller Value')"
              :option="getFieldOptions(matching_rule_data.seller_field, false).map(option => ({name: option, value: option}))"
            />

            <div class="tfhb-single-form-field" style="width: 100%">
              <div class="tfhb-single-form-field-wrap">
                <label class="tfhb-flexbox tfhb-gap-8 tfhb-align-center">
                  <input 
                    v-model="currentFieldMapping.enabled"
                    type="checkbox" 
                    class="tfhb-checkbox"
                  />
                  {{ $tfhb_trans('Enable this mapping') }}
                </label>
              </div>
            </div>
            
            <div class="tfhb-popup-actions tfhb-flexbox tfhb-gap-16">
              <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="fieldMappingPopup = false"
                :buttonText="$tfhb_trans('Cancel')"
                :hover_animation="false"
              />
              <HbButton 
                classValue="tfhb-btn boxed-btn flex-btn" 
                @click="saveFieldMapping"
                :buttonText="$tfhb_trans('Save Mapping')"
                icon="Check"
                :hover_animation="true"
              />
            </div>
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


.tfhb-text-sm {
  font-size: 0.875rem;
}

.tfhb-text-muted {
  color: #6c757d;
}

.tfhb-text-center {
  text-align: center;
}

.tfhb-p-16 {
  padding: 1rem;
}

.tfhb-mb-16 {
  margin-bottom: 1rem;
}

.tfhb-mt-24 {
  margin-top: 1.5rem;
}

.tfhb-field-mapping-section {
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  padding: 1rem;
  margin-top: 1rem;
  background: #f8f9fa;
}

.tfhb-section-header {
  margin-bottom: 1rem;
}

.tfhb-section-header h4 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  color: #333;
}

.tfhb-mapping-list {
  margin-bottom: 1rem;
}

.tfhb-mapping-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.tfhb-mapping-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.tfhb-mapping-buyer {
  background: #e3f2fd;
  color: #1976d2;
  padding: 0.25rem 0.5rem;
  border-radius: 3px;
  font-size: 0.875rem;
  font-weight: 500;
}

.tfhb-mapping-seller {
  background: #f3e5f5;
  color: #7b1fa2;
  padding: 0.25rem 0.5rem;
  border-radius: 3px;
  font-size: 0.875rem;
  font-weight: 500;
}

.tfhb-mapping-arrow {
  color: #666;
}

.tfhb-mapping-disabled {
  color: #999;
  font-style: italic;
  font-size: 0.875rem;
}

.tfhb-mapping-actions {
  display: flex;
  gap: 0.5rem;
}

.tfhb-btn-small {
  padding: 0.25rem 0.5rem;
  border: 1px solid #ddd;
  border-radius: 3px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
}

.tfhb-btn-small:hover {
  background: #f5f5f5;
}

.tfhb-btn-edit {
  color: #1976d2;
  border-color: #1976d2;
}

.tfhb-btn-edit:hover {
  background: #e3f2fd;
}

.tfhb-btn-remove {
  color: #d32f2f;
  border-color: #d32f2f;
}

.tfhb-btn-remove:hover {
  background: #ffebee;
}

.tfhb-field-mapping-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tfhb-checkbox {
  margin-right: 0.5rem;
}

.tfhb-mt-8 {
  margin-top: 0.5rem;
}

.tfhb-mapping-field-info {
  margin: 0.5rem 0 0 0;
  font-size: 0.9rem;
  color: #666;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.tfhb-mapping-separator {
  color: #999;
  font-weight: bold;
  margin: 0 0.25rem;
}
</style> 