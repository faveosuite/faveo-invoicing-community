<template>
  <Modal
    :showModal="show"
    :onClose="closeModal"
    :closeLabel="__('message.close')"
    :closeOnBackdrop="true"
  >
    <template #title>
      <h5 class="modal-title fw-bold">{{ __('message.book_a_demo') }}</h5>
    </template>

    <template #fields>
      <Alert componentName="BookDemoModal" />

      <div class="row">
        <div class="col-md-6">
          <ClientField
            name="demoname"
            :label="__('message.name')"
            :modelValue="form.name"
            :error="errors.name"
            :required="true"
            @update:modelValue="form.name = $event; setFieldError('name', undefined)"
          />
        </div>
        <div class="col-md-6">
          <ClientField
            name="demoemail"
            type="email"
            :label="__('message.email_address')"
            :modelValue="form.email"
            :error="errors.email"
            :required="true"
            @update:modelValue="form.email = $event; setFieldError('email', undefined)"
          />
        </div>
      </div>

      <PhoneField
        name="Mobile"
        :label="__('message.mobile')"
        :required="true"
        :value="form.mobile"
        :error="errors.mobile"
        :onChange="onMobileInput"
        @countryChange="onCountryChange"
      />

      <ClientField
        name="demomessage"
        type="textarea"
        :label="__('message.contact_message')"
        :modelValue="form.message"
        :error="errors.message"
        :required="true"
        :rows="4"
        @update:modelValue="form.message = $event; setFieldError('message', undefined)"
      />

      <RecaptchaField ref="captchaRef" action="demo" class="mt-2" />
    </template>

    <template #controls>
      <button
        type="button"
        class="btn btn-primary"
        :disabled="submitting"
        @click="submit"
      >
        <span v-if="submitting" class="spinner-border spinner-border-sm me-1" />
        <i v-else class="fa fa-book me-1"></i>
        {{ __('message.book_a_demo') }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { useForm } from 'vee-validate'
import { __ } from '@/plugins/i18n'
import { useAlertStore } from '@/core/stores/alert'
import { demoSchema } from '@/validations/client/demoValidations'
import http from '@/plugins/axios'
import Modal from '../common/Modal.vue'
import Alert from '@/components/Reusable/Alert.vue'
import ClientField from '../forms/ClientField.vue'
import PhoneField from '@/components/Reusable/FormField/PhoneField.vue'
import { RecaptchaField } from '@recaptcha'

const props = defineProps({
  show: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const captchaRef = ref(null)
const submitting = ref(false)
const honeypot = ref(null)

const form = reactive({
  name: '',
  email: '',
  mobile: '',
  country_code: '',
  message: '',
})

watch(() => props.show, async (val) => {
  if (val) {
    form.name = ''
    form.email = ''
    form.mobile = ''
    form.country_code = ''
    form.message = ''
    setErrors({})
    captchaRef.value?.reset()
    try {
      const { data } = await http.get('honeypot')
      honeypot.value = data?.data ?? null
    } catch {
      honeypot.value = null
    }
  }
})

function onMobileInput(value) {
  form.mobile = String(value).replace(/[^\d]/g, '')
  setFieldError('mobile', undefined)
}

function onCountryChange({ dialCode }) {
  form.country_code = '+' + dialCode
}

function closeModal() {
  alertStore.unsetAlert()
  emit('close')
}

async function submit() {
  try {
    demoSchema.validateSync(
      { name: form.name, email: form.email, mobile: form.mobile, message: form.message },
      { abortEarly: false },
    )
  } catch (err) {
    const map = {}
    err.inner?.forEach(e => { if (e.path && !map[e.path]) map[e.path] = e.message })
    setErrors(map)
    return
  }

  submitting.value = true
  try {
    const captchaPayload = await captchaRef.value?.getPayload()
    if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
      return
    }
    const payload = {
      demoname:     form.name,
      demoemail:    form.email,
      Mobile:       form.mobile,
      country_code: form.country_code,
      demomessage:  form.message,
      ...captchaPayload,
    }
    if (honeypot.value) {
      payload.demo = {
        [honeypot.value.pot]:  '',
        [honeypot.value.time]: honeypot.value.token,
      }
    }
    const res = await http.post('demo-request', payload)
    form.name = ''
    form.email = ''
    form.mobile = ''
    form.country_code = ''
    form.message = ''
    setErrors({})
    captchaRef.value?.reset()
    alertStore.setAlert({
      message: res?.data?.message ?? __('message.message_sent_successfully_400'),
      type: 'success',
      component_name: 'BookDemoModal',
    })
  } catch (e) {
    if (e?.response?.data?.data?.show_v2_recaptcha) {
      captchaRef.value?.triggerFallback()
      return
    }
    alertStore.setAlert({
      message: e?.response?.data?.message ?? __('message.something_went_wrong'),
      type: 'error',
      component_name: 'BookDemoModal',
    })
  } finally {
    submitting.value = false
  }
}
</script>
