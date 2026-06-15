<template>
  <div>
    <AppCard :title="__('message.profile_information')">
      <div v-if="!hasDataPopulated" class="row justify-content-center py-3"><loader /></div>

      <div v-if="hasDataPopulated">

        <!-- Avatar -->
        <div class="d-flex justify-content-center mb-5">
          <ProfileImageUpload
              :src="avatarPreview"
              :initials="initials"
              :alt="form.first_name"
              @change="onImageChange"
          />
        </div>

        <!-- Profile form -->
        <form @submit.prevent="submitProfile" class="needs-validation">

          <div class="row">
            <div class="col-md-6">
              <ClientField type="text" name="first_name"
                           :label="__('message.first_name')"
                           v-model="form.first_name"
                           :error="errors.first_name"
                           @update:modelValue="setFieldError('first_name', undefined)"
                           :required="true"/>
            </div>
            <div class="col-md-6">
              <ClientField type="text" name="last_name"
                           :label="__('message.last_name')"
                           v-model="form.last_name"
                           :error="errors.last_name"
                           @update:modelValue="setFieldError('last_name', undefined)"
                           :required="true"/>
            </div>
          </div>

          <ClientField type="text" name="user_name"
                       :label="__('message.user_name')"
                       v-model="form.user_name"
                       :error="errors.user_name"
                       @update:modelValue="setFieldError('user_name', undefined)"
                       :required="true"/>

          <!-- Email — changed via verified flow -->
          <ClientField type="email" name="email"
                       :label="__('message.email')"
                       :model-value="form.email"
                       :disabled="true">
            <template #append>
              <button type="button" class="input-group-text" tabindex="-1"
                      @click="showEmailModal = true"
                      v-tooltip="__('message.click_to_change_email')">
                <i class="fa fa-pencil-alt"></i>
              </button>
            </template>
          </ClientField>

          <!-- Mobile — changed via verified flow -->
          <ClientField type="text" name="mobile"
                       :label="__('message.mobile')"
                       :model-value="mobileDisplay"
                       :disabled="true">
            <template #append>
              <button type="button" class="input-group-text" tabindex="-1"
                      @click="showMobileModal = true"
                      v-tooltip="__('message.click_to_change_mobile_no')">
                <i class="fa fa-pencil-alt"></i>
              </button>
            </template>
          </ClientField>

          <ClientField type="text" name="company"
                       :label="__('message.company')"
                       v-model="form.company"
                       :error="errors.company"
                       @update:modelValue="setFieldError('company', undefined)"
                       :required="true"/>

          <ClientField type="text" name="address"
                       :label="__('message.address')"
                       v-model="form.address"
                       :error="errors.address"
                       @update:modelValue="setFieldError('address', undefined)"
                       :required="true"/>

          <ClientField type="text" name="town"
                       :label="__('message.town')"
                       v-model="form.town"/>

          <!-- Country — managed by admin, read-only on the client panel -->
          <SelectField name="country"
                       :label="__('message.country')"
                       :elements="countries"
                       optionLabel="name"
                       :value="countries.find(c => c.code === form.country) ?? null"
                       :onChange="(v) => form.country = v?.code ?? ''"
                       :disabled="true" />

          <SelectField name="state"
                       :label="__('message.state')"
                       :elements="states"
                       optionLabel="name"
                       :value="states.find(s => s.id === form.state) ?? null"
                       :onChange="(v) => form.state = v?.id ?? ''" />

          <DynamicSelect name="timezone_id"
                         :label="__('message.timezone')"
                         :apiEndpoint="`${baseUrl}/dependency/time-zones`"
                         dataKey="time_zones"
                         optionLabel="name"
                         :value="selectedTimezone"
                         :onChange="onTimezoneChange"
                         :placeholder="__('message.select')"
                         :error="errors.timezone_id" />

          <div class="form-group row">
            <div class="form-group col-lg-9"></div>
            <div class="form-group col-lg-3">
              <button type="submit" class="btn btn-primary btn-modern float-end" :disabled="savingProfile">
                <i v-if="savingProfile" class="fas fa-circle-notch fa-spin me-1"></i>
                {{ __('message.save') }}
              </button>
            </div>
          </div>

        </form>

        <EmailChangeModal v-model:show="showEmailModal"
                          :currentEmail="form.email"
                          @updated="onEmailUpdated" />

        <MobileChangeModal v-model:show="showMobileModal"
                           :currentEmail="form.email"
                           :currentCode="form.mobile_code"
                           :currentIso="form.mobile_country_iso"
                           @updated="onMobileUpdated" />

      </div>
    </AppCard>
  </div>
</template>

<script setup>
import {reactive, ref, computed, onMounted} from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import {__} from '@/plugins/i18n'
import {successHandler, errorHandler} from '@/helpers/responseHandler.js'
import {profileSchema} from '@/validations/client/profile.js'
import ProfileImageUpload from '@/themes/porto/components/common/ProfileImageUpload.vue'
import EmailChangeModal from './components/EmailChangeModal.vue'
import MobileChangeModal from './components/MobileChangeModal.vue'

const el = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const avatarPreview = ref(el?.dataset?.userAvatar ?? '')
const selectedImage = ref(null)

const COMPONENT = 'client-page'

const hasDataPopulated = ref(false)
const savingProfile = ref(false)
const { errors, setErrors, setFieldError } = useForm()

const form = reactive({
  first_name: '', last_name: '', user_name: '', email: '',
  company: '', mobile: '', mobile_code: '', mobile_country_iso: '',
  address: '', town: '', country: '', state: '', timezone_id: '',
  // kept (not shown) so the saved value is preserved on submit
  zipcode: '',
})

const initials = computed(() => {
  const f = form.first_name?.[0] ?? ''
  const l = form.last_name?.[0] ?? ''
  return (f + l).toUpperCase() || '?'
})

const countries = ref([])
const states = ref([])
// Current timezone object for the DynamicSelect's initial display; its options
// are loaded on demand from /dependency/time-zones (the DependencyController).
const selectedTimezone = ref(null)

onMounted(async () => {
  try {
    const [profileRes, countriesRes] = await Promise.all([
      http.get(`${baseUrl}/get-my-profile`),
      http.get(`${baseUrl}/dependency/countries`, {params: {limit: 'all'}}),
    ])
    countries.value = countriesRes.data?.data?.countries ?? []
    const d = profileRes.data?.data ?? {}
    const user = d.user ?? {}
    Object.assign(form, {
      first_name: user.first_name ?? '', last_name: user.last_name ?? '',
      user_name: user.user_name ?? '', email: user.email ?? '',
      company: user.company ?? '', mobile: user.mobile ?? '',
      mobile_code: user.mobile_code ?? '', mobile_country_iso: user.mobile_country_iso ?? '',
      address: user.address ?? '', town: user.town ?? '',
      country: user.country ?? '', state: user.state ?? '',
      timezone_id: user.timezone_id ?? '', zipcode: user.zipcode ?? user.zip ?? '',
    })
    if (user.timezone) {
      selectedTimezone.value = { id: user.timezone.id, name: user.timezone.timezone_name ?? user.timezone.name }
    }
    if (form.country) await loadStates(form.country)
  } catch (e) {
    errorHandler(e, COMPONENT)
  } finally {
    hasDataPopulated.value = true
  }
})

function onTimezoneChange(v) {
  selectedTimezone.value = v
  form.timezone_id = v?.id ?? ''
  setFieldError('timezone_id', undefined)
}

async function loadStates(code) {
  if (!code) {
    states.value = [];
    return
  }
  try {
    const res = await http.get(`${baseUrl}/dependency/states`, {params: {country: code, limit: 'all'}})
    states.value = (res.data?.data?.states ?? []).map(st => ({
      id: st.iso2,
      name: st.name,
    }))
  } catch {
    states.value = []
  }
}

function onImageChange({file, previewUrl}) {
  selectedImage.value = file
  avatarPreview.value = previewUrl
}

// Email & mobile are changed through verified flows (OTP), not the main save.
const showEmailModal  = ref(false)
const showMobileModal = ref(false)

const mobileDisplay = computed(() =>
  form.mobile ? `${form.mobile_code ? '+' + form.mobile_code + ' ' : ''}${form.mobile}` : ''
)

function onEmailUpdated(email) {
  form.email = email
}

function onMobileUpdated({ mobile, mobile_code, mobile_country_iso }) {
  form.mobile = mobile
  form.mobile_code = mobile_code
  form.mobile_country_iso = mobile_country_iso
}

async function submitProfile() {
  if (!await validateForm(profileSchema, form, setErrors)) return
  savingProfile.value = true
  try {
    const data = new FormData()
    Object.entries(form).forEach(([k, v]) => {
      if (v != null) data.append(k, v)
    })
    if (selectedImage.value) {
      data.append('profile_pic', selectedImage.value, 'profile_pic.jpg')
    }
    data.append('_method', 'PATCH')
    const res = await http.post(`${baseUrl}/my-profile`, data, {headers: {'Content-Type': 'multipart/form-data'}})
    successHandler(res, COMPONENT)
  } catch (e) {
    errorHandler(e, COMPONENT)
  } finally {
    savingProfile.value = false
  }
}
</script>
