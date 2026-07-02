<template>
  <Modal
    :showModal="show"
    :onClose="closeModal"
    :closeLabel="__('message.close')"
    :closeOnBackdrop="true"
    classname="modal-md"
  >
    <template #title>
      <h5 class="modal-title fw-bold">{{ __('message.cloud_heading') }}</h5>
    </template>

    <template #fields>
      <Alert componentName="CloudTrialModal" />

      <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

      <template v-else>
        <!-- Domain -->
        <ClientField
          name="domain"
          :label="__('message.cloud_field_label')"
          :placeholder="__('message.cloud_domain')"
          :modelValue="domain"
          :error="errors.domain"
          @update:modelValue="domain = $event; setFieldError('domain', undefined)"
          @keyup.enter="submit"
        >
          <template #append>
            <span class="input-group-text bg-primary text-white border-primary fw-semibold">
              .{{ cloudSubdomain }}
            </span>
          </template>
        </ClientField>

        <!-- Product selector -->
        <DynamicSelect
          v-if="products.length"
          name="product_id"
          :label="__('message.select_product')"
          :elements="products"
          :value="selectedProduct"
          :onChange="(val) => { selectedProduct = val; setFieldError('selectedProduct', undefined) }"
          :clearable="false"
          optionLabel="name"
          :error="errors.selectedProduct"
        />

        <!-- Data center -->
        <DynamicSelect
          v-if="dataCenters.length"
          name="data_center_id"
          :label="__('message.choose_data_center')"
          :elements="dataCenters"
          :value="selectedDataCenter"
          :onChange="(val) => { selectedDataCenter = val; setFieldError('selectedDataCenter', undefined) }"
          :clearable="false"
          optionLabel="name"
          :error="errors.selectedDataCenter"
        />
      </template>
    </template>

    <template #controls>
      <button
        type="button"
        class="btn btn-primary"
        :disabled="submitting || loading"
        @click="submit"
      >
        <span v-if="submitting" class="spinner-border spinner-border-sm me-1" />
        <i v-else class="fas fa-check me-1" />
        {{ submitting ? __('message.please_wait') : __('message.submit') }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from 'vee-validate'
import { __ } from '@/plugins/i18n'
import { useAlertStore } from '@/core/stores/alert'
import { buildCloudTrialSchema } from '@/validations/client/cloudTrialValidations'
import http from '@/plugins/axios'
import Modal from '../common/Modal.vue'
import Alert from '@/components/Reusable/Alert.vue'
import ClientField from '../forms/ClientField.vue'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

const props = defineProps({
  show: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const loading = ref(false)
const submitting = ref(false)

const domain = ref('')
const cloudSubdomain = ref('')
const products = ref([])
const dataCenters = ref([])
const selectedProduct = ref(null)
const selectedDataCenter = ref(null)

async function fetchCloudData() {
  loading.value = true
  try {
    const { data } = await http.get('store/cloud-products')
    const d = data?.data ?? {}
    cloudSubdomain.value = d.cloud_subdomain ?? ''
    products.value = d.products ?? []
    dataCenters.value = d.data_centers ?? []
  } catch {
    // best-effort
  } finally {
    loading.value = false
  }
}

watch(() => props.show, (val) => {
  if (val) {
    domain.value = ''
    selectedProduct.value = null
    selectedDataCenter.value = null
    setErrors({})
    fetchCloudData()
  }
})

function closeModal() {
  alertStore.unsetAlert()
  emit('close')
}

async function submit() {
  try {
    buildCloudTrialSchema({ hasProducts: products.value.length > 0, hasDataCenters: dataCenters.value.length > 0 }).validateSync(
      { domain: domain.value, selectedProduct: selectedProduct.value, selectedDataCenter: selectedDataCenter.value },
      { abortEarly: false },
    )
  } catch (err) {
    const errMap = {}
    err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
    setErrors(errMap)
    return
  }

  submitting.value = true
  try {
    const res = await http.post('free-trial/start', {
      domain: domain.value,
      product_id: selectedProduct.value?.id ?? null,
      ...(selectedDataCenter.value ? { data_center_id: selectedDataCenter.value.id } : {}),
    })
    alertStore.setAlert({
      message: res?.data?.message ?? __('message.free_trial_started'),
      type: 'success',
      component_name: 'CloudTrialModal',
    })
  } catch (e) {
    alertStore.setAlert({
      message: e?.response?.data?.message ?? __('message.something_went_wrong'),
      type: 'error',
      component_name: 'CloudTrialModal',
    })
  } finally {
    submitting.value = false
  }
}
</script>

