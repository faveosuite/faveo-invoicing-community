<template>
  <div>
    <Alert componentName="contact-us-page" />

    <div class="row py-4">
      <!-- Contact form -->
      <div class="col-lg-6">
        <h2 class="font-weight-bold text-8 mt-2 mb-0">{{ __('message.contact_us') }}</h2>
        <p class="mb-4">{{ __('message.feel_free') }}</p>

        <form @submit.prevent="submit">
          <div class="row">
            <div class="form-group col-lg-6">
              <label class="form-label mb-1 text-2">
                {{ __('message.name') }} <span class="text-color-danger">*</span>
              </label>
              <input
                type="text"
                name="conName"
                maxlength="100"
                class="form-control text-3 h-auto py-2"
                :class="{ 'is-invalid': errors.name }"
                v-model="form.name"
                @input="setFieldError('name', undefined)"
              />
              <div v-if="errors.name" class="invalid-feedback d-block">{{ errors.name }}</div>
            </div>
            <div class="form-group col-lg-6">
              <label class="form-label mb-1 text-2">
                {{ __('message.email_address') }} <span class="text-color-danger">*</span>
              </label>
              <input
                type="email"
                name="email"
                maxlength="100"
                class="form-control text-3 h-auto py-2"
                :class="{ 'is-invalid': errors.email }"
                v-model="form.email"
                @input="setFieldError('email', undefined)"
              />
              <div v-if="errors.email" class="invalid-feedback d-block">{{ errors.email }}</div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col">
              <label class="form-label mb-1 text-2">
                {{ __('message.mobile') }} <span class="text-color-danger">*</span>
              </label>
              <PhoneField
                name="Mobile"
                :value="form.mobile"
                :error="errors.mobile"
                :onChange="onMobileInput"
                @countryChange="onCountryChange"
              />
            </div>
          </div>

          <div class="row">
            <div class="form-group col">
              <label class="form-label mb-1 text-2">
                {{ __('message.contact_message') }} <span class="text-color-danger">*</span>
              </label>
              <textarea
                name="conmessage"
                maxlength="5000"
                rows="8"
                class="form-control text-3 h-auto py-2"
                :class="{ 'is-invalid': errors.message }"
                v-model="form.message"
                @input="setFieldError('message', undefined)"
              ></textarea>
              <div v-if="errors.message" class="invalid-feedback d-block">{{ errors.message }}</div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col">
              <button
                type="submit"
                class="btn btn-dark btn-modern"
                :disabled="submitting"
              >
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1" />
                {{ __('message.contact_send_msg') }}
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Office info -->
      <div class="col-lg-6">
        <inline-loader v-if="loadingInfo" />

        <template v-else-if="info">
          <h4 class="mt-2 mb-1">{{ __('message.our_office') }}</h4>
          <ul class="list list-icons list-icons-style-2 mt-2">
            <li>
              <i class="fas fa-map-marker-alt top-6"></i>
              <strong class="text-dark">{{ __('message.address') }}:</strong>
              {{ info.address }}<br>
              {{ [info.city, info.state, info.country, info.zip].filter(Boolean).join(', ') }}
            </li>
            <li>
              <i class="fas fa-phone top-6"></i>
              <strong class="text-dark">{{ __('message.phone') }}:</strong>
              +{{ info.phone_code }} {{ info.phone }}
            </li>
            <li>
              <i class="fas fa-envelope top-6"></i>
              <strong class="text-dark">{{ __('message.email') }}: </strong>
              <a :href="`mailto:${info.company_email}`">{{ info.company_email }}</a>
            </li>
          </ul>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import { __ } from '@/plugins/i18n'
import { useAlertStore } from '@/core/stores/alert'
import { contactUsSchema } from '@/validations/client/contactUsValidations'
import http from '@/plugins/axios'
import Alert from '@/themes/porto/components/common/Alert.vue'
import PhoneField from '@/themes/porto/components/forms/PhoneField.vue'

const COMPONENT = 'contact-us-page'

const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const submitting  = ref(false)
const loadingInfo = ref(true)
const info        = ref(null)
const honeypot    = ref(null)

const form = reactive({
  name:         '',
  email:        '',
  mobile:       '',
  country_code: '',
  message:      '',
})

onMounted(async () => {
  try {
    const [infoRes, hpRes] = await Promise.all([
      http.get('contact-us-info'),
      http.get('honeypot').catch(() => ({ data: null })),
    ])
    info.value     = infoRes.data?.data ?? null
    honeypot.value = hpRes.data?.data ?? null
  } catch {
    // best-effort
  } finally {
    loadingInfo.value = false
  }
})

function onMobileInput(value) {
  form.mobile = String(value).replace(/[^\d]/g, '')
  setFieldError('mobile', undefined)
}

function onCountryChange({ dialCode }) {
  form.country_code = '+' + dialCode
}

async function submit() {
  try {
    contactUsSchema.validateSync(
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
  alertStore.unsetAlert()
  try {
    const payload = {
      conName:      form.name,
      email:        form.email,
      Mobile:       form.mobile,
      country_code: form.country_code,
      conmessage:   form.message,
    }
    if (honeypot.value) {
      payload.contact = {
        [honeypot.value.pot]:  '',
        [honeypot.value.time]: honeypot.value.token,
      }
    }
    const res = await http.post('contact-us', payload)
    form.name         = ''
    form.email        = ''
    form.mobile       = ''
    form.country_code = ''
    form.message      = ''
    setErrors({})
    alertStore.setAlert({
      message:        res?.data?.message ?? __('message.message_sent_successfully_400'),
      type:           'success',
      component_name: COMPONENT,
    })
  } catch (e) {
    alertStore.setAlert({
      message:        e?.response?.data?.message ?? __('message.something_went_wrong'),
      type:           'danger',
      component_name: COMPONENT,
    })
  } finally {
    submitting.value = false
  }
}
</script>
