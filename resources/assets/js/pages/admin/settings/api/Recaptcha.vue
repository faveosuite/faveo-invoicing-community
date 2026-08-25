<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.recaptcha_configuration') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <!-- General Configuration -->
                    <div class="card card-light mb-3">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('message.general') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <DynamicSelect
                                        name="captcha_version"
                                        :label="__('message.recaptcha_version')"
                                        :elements="versionOptions"
                                        :value="selectedVersion"
                                        :onChange="onVersionChange"
                                        :searchable="false"
                                        :clearable="false"
                                        :error="errors.captcha_version"
                                    />
                                </div>
                                <div class="col-md-6 mb-3" v-show="isV3">
                                    <DynamicSelect
                                        name="failover_action"
                                        :label="__('message.failover_action')"
                                        :elements="failoverOptions"
                                        :value="selectedFailover"
                                        :onChange="onFailoverChange"
                                        :searchable="false"
                                        :clearable="false"
                                        :error="errors.failover_action"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- v3 Settings -->
                    <div class="card card-light mb-3" v-show="isV3">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('message.recaptcha_v3_settings') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v3_site_key"
                                        :label="__('message.v3_site_key')"
                                        :required="true"
                                        :value="form.v3_site_key"
                                        :placeholder="__('message.enter_v3_site_key')"
                                        :onChange="onChange"
                                        :error="errors.v3_site_key"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v3_secret_key"
                                        :label="__('message.v3_secret_key')"
                                        :required="true"
                                        type="password"
                                        :value="form.v3_secret_key"
                                        :placeholder="__('message.enter_v3_secret_key')"
                                        :onChange="onChange"
                                        :error="errors.v3_secret_key"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="score_threshold"
                                        :label="__('message.v3_score_threshold')"
                                        :required="true"
                                        type="number"
                                        :value="form.score_threshold"
                                        :onChange="onScoreChange"
                                        :error="errors.score_threshold"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- v2 Settings -->
                    <div class="card card-light mb-3" v-show="isV2">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('message.recaptcha_v2_settings') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v2_site_key"
                                        :label="__('message.v2_site_key')"
                                        :required="true"
                                        :value="form.v2_site_key"
                                        :placeholder="__('message.enter_v2_site_key')"
                                        :onChange="onChange"
                                        :error="errors.v2_site_key"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v2_secret_key"
                                        :label="__('message.v2_secret_key')"
                                        :required="true"
                                        type="password"
                                        :value="form.v2_secret_key"
                                        :placeholder="__('message.enter_v2_secret_key')"
                                        :onChange="onChange"
                                        :error="errors.v2_secret_key"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('message.recaptcha_v2') }} {{ __('message.preview') }}</label>
                                    <div class="border rounded p-3 bg-light recaptcha-preview">
                                        <div id="v2_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <div class="card card-light mb-0">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('message.appearance') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3" v-show="isV2Checkbox">
                                    <RadioButton
                                        name="theme"
                                        :label="__('message.theme')"
                                        :options="[{ name: __('message.theme_light'), value: 'light' }, { name: __('message.theme_dark'), value: 'dark' }]"
                                        :value="form.theme"
                                        :onChange="onChange"
                                    />
                                    <div v-if="errors.theme" class="text-danger small mt-1">{{ errors.theme }}</div>
                                </div>
                                <div class="col-md-6 mb-3" v-show="isV2Checkbox">
                                    <RadioButton
                                        name="size"
                                        :label="__('message.size')"
                                        :options="[{ name: __('message.size_normal'), value: 'normal' }, { name: __('message.size_compact'), value: 'compact' }]"
                                        :value="form.size"
                                        :onChange="onChange"
                                    />
                                    <div v-if="errors.size" class="text-danger small mt-1">{{ errors.size }}</div>
                                </div>
                                <div class="col-md-6 mb-3" v-show="showBadge">
                                    <DynamicSelect
                                        name="badge_position"
                                        :label="__('message.badge_position')"
                                        :elements="badgeOptions"
                                        :value="selectedBadge"
                                        :onChange="onBadgeChange"
                                        :searchable="false"
                                        :clearable="false"
                                        :error="errors.badge_position"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>

        <RecaptchaProvider
            v-if="!loading && !isV3 && (showV2CheckboxPreview || showV2InvisiblePreview)"
            :key="previewTrigger"
            :auto-config="false"
            :enabled="true"
            :mode="previewMode"
            :v2-site-key="form.v2_site_key"
            :theme="form.theme"
            :size="form.size"
            :badge="form.badge_position"
        >
            <Teleport v-if="showV2CheckboxPreview" to="#v2_response">
                <RecaptchaCheckbox
                    ref="checkboxRef"
                    :theme="form.theme"
                    :size="form.size"
                    @verify="onCheckboxVerify"
                    @expired="onCheckboxExpired"
                    @error="onCheckboxError"
                />
            </Teleport>
            <Teleport v-else-if="showV2InvisiblePreview" to="#v2_response">
                <RecaptchaV2Invisible ref="invisiblePreviewRef" :badge="form.badge_position" @error="onInvisibleError" />
            </Teleport>
        </RecaptchaProvider>

        <RecaptchaProvider
            v-if="!loading && isV3 && form.v3_site_key.trim() !== ''"
            ref="v3ProviderRef"
            :key="previewTrigger"
            :auto-config="false"
            :enabled="true"
            mode="v3"
            :v3-site-key="form.v3_site_key"
            :badge="form.badge_position"
        >
            <RecaptchaV3 ref="v3Ref" action="settings_save" />
        </RecaptchaProvider>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import { RecaptchaProvider, RecaptchaCheckbox, RecaptchaV2Invisible, RecaptchaV3 } from '@recaptcha'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import { buildRecaptchaSchema } from '@/validations/admin/recaptchaValidations'

const COMPONENT = 'recaptcha-settings'

const loading = ref(true)
const saving  = ref(false)

const { errors, setErrors, setFieldError } = useForm()
const previewTrigger = ref(0)

// Preview widget refs + result flags
const invisiblePreviewRef = ref(null)
const checkboxRef         = ref(null)
const v3Ref               = ref(null)
const v3ProviderRef       = ref(null)
const v2CheckboxToken     = ref('')

function debounce(fn, ms) {
    let t
    const d = (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms) }
    d.cancel = () => clearTimeout(t)
    return d
}

const bumpPreview = debounce(() => { previewTrigger.value++ }, 800)

const form = reactive({
    captcha_version:  'v2_checkbox',
    failover_action:  'none',
    v3_site_key:      '',
    v3_secret_key:    '',
    score_threshold:  0.5,
    v2_site_key:      '',
    v2_secret_key:    '',
    theme:            'light',
    size:             'normal',
    badge_position:   'bottomright',
})

const versionOptions = [
    { id: 'v3_invisible', name: __('message.recaptcha_v3') },
    { id: 'v2_invisible', name: __('message.recaptcha_v2_invisible') },
    { id: 'v2_checkbox',  name: __('message.recaptcha_v2_checkbox') },
]
const failoverOptions = [
    { id: 'none',        name: __('message.none') },
    { id: 'v2_checkbox', name: __('message.fallback_v2_checkbox') },
]
const badgeOptions = [
    { id: 'bottomright', name: __('message.badge_bottomright') },
    { id: 'bottomleft',  name: __('message.badge_bottomleft') },
]

const isV3           = computed(() => form.captcha_version === 'v3_invisible')
const isV2Checkbox   = computed(() => form.captcha_version === 'v2_checkbox' || (isV3.value && form.failover_action === 'v2_checkbox'))
const isV2Invisible  = computed(() => form.captcha_version === 'v2_invisible')
const isV2           = computed(() => isV2Checkbox.value || isV2Invisible.value)
const showBadge      = computed(() => isV3.value || isV2Invisible.value)

const showV2CheckboxPreview  = computed(() => form.captcha_version === 'v2_checkbox' && form.v2_site_key.trim() !== '')
const showV2InvisiblePreview = computed(() => isV2Invisible.value && form.v2_site_key.trim() !== '')

// The preview only renders interactive v2 widgets (v3 has no widget — just the
// native floating badge), so the provider always loads an explicit-mode script.
const previewMode = computed(() => isV2Invisible.value ? 'v2-invisible' : 'v2')


const selectedVersion  = computed(() => versionOptions.find(o => o.id === form.captcha_version) ?? null)
const selectedFailover = computed(() => failoverOptions.find(o => o.id === form.failover_action) ?? null)
const selectedBadge    = computed(() => badgeOptions.find(o => o.id === form.badge_position) ?? null)

onMounted(async () => {
    try {
        const res = await http.get(`/recaptcha-settings`)
        const d = res.data?.data ?? {}
        Object.assign(form, {
            captcha_version:  d.captcha_version  ?? 'v2_checkbox',
            failover_action:  d.failover_action  ?? 'none',
            v3_site_key:      d.v3_site_key      ?? '',
            v3_secret_key:    d.v3_secret_key    ?? '',
            score_threshold:  d.score_threshold  ?? 0.5,
            v2_site_key:      d.v2_site_key      ?? '',
            v2_secret_key:    d.v2_secret_key    ?? '',
            theme:            d.theme            ?? 'light',
            size:             d.size             ?? 'normal',
            badge_position:   d.badge_position   ?? 'bottomright',
        })
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
        bumpPreview.cancel()
    }
})

watch(
    () => [form.v3_site_key, form.v2_site_key, form.captcha_version, form.failover_action, form.theme, form.size],
    bumpPreview
)

// Validate v3 site key the moment the provider finishes loading the reCAPTCHA script.
// This fires both on page-load (saved keys) and whenever the site key changes (provider remounts).
// v2 invisible / v2 checkbox rely on the widget's native @error event instead.
watch(
    () => v3ProviderRef.value?.isReady,
    async (ready) => {
        if (!ready) return
        try {
            const token = await v3Ref.value?.execute('settings_save')
            if (token) setFieldError('v3_site_key', undefined)
            else setFieldError('v3_site_key', __('recaptcha.valid_recaptcha_site_key'))
        } catch {
            setFieldError('v3_site_key', __('recaptcha.valid_recaptcha_site_key'))
        }
    }
)

function onInvisibleError() { setFieldError('v2_site_key', __('recaptcha.valid_recaptcha_site_key')) }

function onCheckboxVerify(token) {
    setFieldError('v2_site_key', undefined)
    v2CheckboxToken.value = token
}
function onCheckboxExpired() { v2CheckboxToken.value = '' }
function onCheckboxError() { setFieldError('v2_site_key', __('recaptcha.valid_recaptcha_site_key')) }

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onVersionChange(val) {
    setFieldError('captcha_version', undefined)
    form.captcha_version = val?.id ?? 'v2_checkbox'
}

function onFailoverChange(val) {
    setFieldError('failover_action', undefined)
    form.failover_action = val?.id ?? 'none'
}

function onScoreChange(val, name) {
    setFieldError(name, undefined)
    form[name] = parseFloat(val)
}

function onBadgeChange(val) {
    setFieldError('badge_position', undefined)
    form.badge_position = val?.id ?? 'bottomright'
}

async function save() {
    if (!await validateForm(buildRecaptchaSchema(form), form, setErrors)) return

    saving.value = true
    try {
        const payload = {
            captcha_version:  form.captcha_version,
            failover_action:  form.failover_action,
            v3_site_key:      form.v3_site_key,
            v3_secret_key:    form.v3_secret_key,
            score_threshold:  form.score_threshold,
            v2_site_key:      form.v2_site_key,
            v2_secret_key:    form.v2_secret_key,
            theme:            form.theme,
            size:             form.size,
            badge_position:   form.badge_position,
            recaptcha_status: true,
        }

        // Generate tokens so UpdateSettingsRequest can verify keys via Google API
        if (isV3.value && form.v3_site_key) {
            try {
                payload.v3_g_recaptcha_response = await v3Ref.value?.execute('settings_save') ?? ''
            } catch {
                setFieldError('v3_site_key', __('recaptcha.valid_recaptcha_site_key'))
                return
            }
        }

        if (isV2Invisible.value && form.v2_site_key) {
            try {
                payload.v2_g_recaptcha_response = await invisiblePreviewRef.value?.execute() ?? ''
                invisiblePreviewRef.value?.reset()
            } catch {
                setFieldError('v2_site_key', __('recaptcha.valid_recaptcha_site_key'))
                return
            }
        }

        if (form.captcha_version === 'v2_checkbox') {
            payload.v2_g_recaptcha_response = v2CheckboxToken.value ?? ''
        }

        const res = await http.patch(`/recaptcha-settings`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data?.errors ?? {}
            setErrors(Object.fromEntries(
                Object.entries(errs).map(([k, msgs]) => [k, Array.isArray(msgs) ? msgs[0] : msgs])
            ))
        } else {
            errorHandler(e, COMPONENT)
        }
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.recaptcha-preview { min-height: 100px; }
</style>
