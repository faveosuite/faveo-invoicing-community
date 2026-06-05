<template>
  <div>
    <AppCard :title="__('message.profile_information')">
      <inline-loader v-if="!hasDataPopulated"/>

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

          <!-- Email — read-only -->
          <ClientField type="email" name="email"
                       :label="__('message.email')"
                       v-model="form.email"
                       :disabled="true"/>

          <!-- Mobile -->
          <PhoneField name="mobile" :label="__('message.mobile')" required
                      :value="form.mobile" :error="errors.mobile"
                      :initialCountry="(form.mobile_country_iso || 'auto').toLowerCase()"
                      :onChange="onMobileInput"
                      @countryChange="onMobileCountryChange"/>

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

      </div>
    </AppCard>
  </div>
</template>

<script setup>
import {reactive, ref, computed, onMounted} from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import {__} from '@/plugins/i18n'
import {successHandler, errorHandler} from '@/helpers/responseHandler.js'
import {profileSchema} from '@/validations/client/profile.js'
import ProfileImageUpload from '@/themes/porto/components/common/ProfileImageUpload.vue'

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
      http.get(`${baseUrl}/my-profile`),
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

// PhoneField writes the digits back; its own country selector drives the dial code.
function onMobileInput(value) {
  form.mobile = String(value).replace(/[^\d]/g, '')
  setFieldError('mobile', undefined)
}

function onMobileCountryChange({iso, dialCode}) {
  form.mobile_country_iso = iso
  form.mobile_code = dialCode
}

async function submitProfile() {
  try {
    profileSchema.validateSync(form, {abortEarly: false})
  } catch (err) {
    const map = {}
    err.inner?.forEach(e => {
      if (e.path && !map[e.path]) map[e.path] = e.message
    })
    setErrors(map)
    return
  }
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
