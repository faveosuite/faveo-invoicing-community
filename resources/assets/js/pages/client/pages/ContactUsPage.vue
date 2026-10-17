<template>
  <div>
    <Alert componentName="contact-us-page" />

    <div v-if="loadingInfo" class="row justify-content-center py-3"><loader /></div>

    <div v-else class="row py-4">
      <!-- Contact form -->
      <div class="col-lg-6">
        <h2 class="font-weight-bold text-8 mt-2 mb-0">{{ __('message.contact_us') }}</h2>
        <p class="mb-4">{{ __('message.feel_free') }}</p>

        <form @submit.prevent="submit">
          <div class="row">
            <div class="form-group col-lg-6">
              <label for="contact-name" class="form-label text-dark">
                {{ __('message.name') }} <span class="text-color-danger">*</span>
              </label>
              <input
                id="contact-name"
                type="text"
                name="conName"
                autocomplete="name"
                spellcheck="false"
                :aria-invalid="errors.name ? 'true' : undefined"
                :aria-describedby="errors.name ? 'contact-name-error' : undefined"
                maxlength="100"
                class="form-control form-control-lg text-4"
                :class="{ 'is-invalid': errors.name }"
                v-model="form.name"
                @input="setFieldError('name', undefined)"
              />
              <div v-if="errors.name" id="contact-name-error" class="invalid-feedback d-block" role="alert">{{ errors.name }}</div>
            </div>
            <div class="form-group col-lg-6">
              <label for="contact-email" class="form-label text-dark">
                {{ __('message.email_address') }} <span class="text-color-danger">*</span>
              </label>
              <input
                id="contact-email"
                type="email"
                name="email"
                autocomplete="email"
                spellcheck="false"
                :aria-invalid="errors.email ? 'true' : undefined"
                :aria-describedby="errors.email ? 'contact-email-error' : undefined"
                maxlength="100"
                class="form-control form-control-lg text-4"
                :class="{ 'is-invalid': errors.email }"
                v-model="form.email"
                @input="setFieldError('email', undefined)"
              />
              <div v-if="errors.email" id="contact-email-error" class="invalid-feedback d-block" role="alert">{{ errors.email }}</div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col">
              <label class="form-label text-dark">
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
              <label for="contact-message" class="form-label text-dark">
                {{ __('message.contact_message') }} <span class="text-color-danger">*</span>
              </label>
              <textarea
                id="contact-message"
                name="conmessage"
                :aria-invalid="errors.message ? 'true' : undefined"
                :aria-describedby="errors.message ? 'contact-message-error' : undefined"
                maxlength="5000"
                rows="8"
                class="form-control form-control-lg text-4"
                :class="{ 'is-invalid': errors.message }"
                v-model="form.message"
                @input="setFieldError('message', undefined)"
              ></textarea>
              <div v-if="errors.message" id="contact-message-error" class="invalid-feedback d-block" role="alert">{{ errors.message }}</div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col">
              <RecaptchaField ref="captchaRef" action="contact" />
            </div>
          </div>

          <div class="row">
            <div class="form-group col">
              <button
                type="submit"
                class="btn btn-dark btn-modern"
                :disabled="submitting"
              >
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1" aria-hidden="true" />
                {{ __('message.contact_send_msg') }}
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Office info -->
      <div class="col-lg-6">
        <template v-if="info">
          <h3 class="office-heading mt-2 mb-1">{{ __('message.our_office') }}</h3>
          <ul class="list list-icons list-icons-style-3 mt-2">
            <li>
              <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
              <strong class="text-dark">{{ __('message.address') }}:</strong>
              {{ info.address }}<br>
              {{ [info.city, info.state, info.country, info.zip].filter(Boolean).join(', ') }}
            </li>
            <li>
              <i class="fas fa-phone" aria-hidden="true"></i>
              <strong class="text-dark">{{ __('message.phone') }}:</strong>
              +{{ info.phone_code }} {{ info.phone }}
            </li>
            <li>
              <i class="fas fa-envelope" aria-hidden="true"></i>
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
import { validateForm } from '@/helpers/formUtils.js'
import { __ } from '@/plugins/i18n'
import { useAlertStore } from '@/core/stores/alert'
import { contactUsSchema } from '@/validations/client/contactUsValidations'
import http from '@/plugins/axios'
import Alert from '@/components/Reusable/Alert.vue'
import PhoneField from '@/components/Reusable/FormField/PhoneField.vue'
import { RecaptchaField } from '@recaptcha'

const COMPONENT = 'contact-us-page'

const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const captchaRef  = ref(null)
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
  if (!await validateForm(contactUsSchema, { name: form.name, email: form.email, mobile: form.mobile, message: form.message }, setErrors)) return

  submitting.value = true
  alertStore.unsetAlert()
  try {
    const captchaPayload = await captchaRef.value?.getPayload()
    if (!captchaRef.value?.disabled && !captchaPayload?.['g-recaptcha-response']) {
      return
    }
    const payload = {
      conName:      form.name,
      email:        form.email,
      Mobile:       form.mobile,
      country_code: form.country_code,
      conmessage:   form.message,
      ...captchaPayload,
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
    captchaRef.value?.reset()
    alertStore.setAlert({
      message:        res?.data?.message ?? __('message.message_sent_successfully_400'),
      type:           'success',
      component_name: COMPONENT,
    })
  } catch (e) {
    if (e?.response?.data?.data?.show_v2_recaptcha) {
      captchaRef.value?.triggerFallback()
      return
    }
    captchaRef.value?.reset()
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

<style scoped>
/* Section title for the office block (Porto uppercases h3, so undo that). */
.office-heading {
  font-size: 1.8em;
  font-weight: 700;
  line-height: 1.3;
  text-transform: none;
  color: var(--primary, #0088cc);
}
</style>
