<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">reCAPTCHA Configuration</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">

                    <!-- General Configuration -->
                    <div class="card card-light mb-3">
                        <div class="card-header">
                            <h4 class="card-title">General</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <SelectField
                                        name="captcha_version"
                                        label="reCAPTCHA Version"
                                        :elements="versionOptions"
                                        :value="selectedVersion"
                                        :onChange="(val) => form.captcha_version = val?.id ?? 'v2_checkbox'"
                                        :searchable="false"
                                        :clearable="false"
                                    />
                                </div>
                                <div class="col-md-6 mb-3" v-show="isV3">
                                    <SelectField
                                        name="failover_action"
                                        label="Failover Action"
                                        :elements="failoverOptions"
                                        :value="selectedFailover"
                                        :onChange="(val) => form.failover_action = val?.id ?? 'none'"
                                        :searchable="false"
                                        :clearable="false"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- v3 Settings -->
                    <div class="card card-light mb-3" v-show="isV3">
                        <div class="card-header">
                            <h4 class="card-title">reCAPTCHA v3 Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v3_site_key"
                                        label="v3 Site Key"
                                        :value="form.v3_site_key"
                                        placeholder="Enter your reCAPTCHA v3 site key"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v3_secret_key"
                                        label="v3 Secret Key"
                                        type="password"
                                        :value="form.v3_secret_key"
                                        placeholder="Enter your reCAPTCHA v3 secret key"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="score_threshold"
                                        label="v3 Score Threshold (0.0 – 1.0)"
                                        type="number"
                                        :value="form.score_threshold"
                                        :onChange="(val, key) => form[key] = parseFloat(val)"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">v3 Preview</label>
                                    <div class="border rounded p-3 bg-light" style="min-height: 60px;">
                                        <div id="v3_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- v2 Settings -->
                    <div class="card card-light mb-3" v-show="isV2">
                        <div class="card-header">
                            <h4 class="card-title">reCAPTCHA v2 Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v2_site_key"
                                        label="v2 Site Key"
                                        :value="form.v2_site_key"
                                        placeholder="Enter your reCAPTCHA v2 site key"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v2_secret_key"
                                        label="v2 Secret Key"
                                        type="password"
                                        :value="form.v2_secret_key"
                                        placeholder="Enter your reCAPTCHA v2 secret key"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">v2 Preview</label>
                                    <div class="border rounded p-3 bg-light" style="min-height: 100px;">
                                        <div id="v2_response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <div class="card card-light mb-0">
                        <div class="card-header">
                            <h4 class="card-title">Appearance</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3" v-show="isV2Checkbox">
                                    <label class="form-label fw-bold">Theme</label>
                                    <div class="d-flex gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="theme_light" value="light" v-model="form.theme" />
                                            <label class="form-check-label" for="theme_light">Light</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="theme_dark" value="dark" v-model="form.theme" />
                                            <label class="form-check-label" for="theme_dark">Dark</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" v-show="isV2Checkbox">
                                    <label class="form-label fw-bold">Size</label>
                                    <div class="d-flex gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="size_normal" value="normal" v-model="form.size" />
                                            <label class="form-check-label" for="size_normal">Normal</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="size_compact" value="compact" v-model="form.size" />
                                            <label class="form-check-label" for="size_compact">Compact</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" v-show="showBadge">
                                    <SelectField
                                        name="badge_position"
                                        label="Badge Position"
                                        :elements="badgeOptions"
                                        :value="selectedBadge"
                                        :onChange="(val) => form.badge_position = val?.id ?? 'bottomright'"
                                        :searchable="false"
                                        :clearable="false"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ __('message.save') }}
                    </button>
                </div>
            </template>
        </div>

        <RecaptchaPreviewProvider
            v-if="!loading"
            :key="previewTrigger"
            :v2SiteKey="form.v2_site_key"
            :v3SiteKey="form.v3_site_key"
        >
            <Teleport v-if="showV3Preview" to="#v3_response">
                <ChallengeV3 action="settings_preview" :autoExecute="false">
                    <template #default="{ response, execute }">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" @click="execute">Verify v3 key</button>
                            <span v-if="response" class="text-success small">
                                <i class="fas fa-check-circle me-1"></i> v3 key valid
                            </span>
                        </div>
                    </template>
                </ChallengeV3>
            </Teleport>
            <Teleport v-if="showV2CheckboxPreview" to="#v2_response">
                <Checkbox :theme="form.theme" :size="form.size" />
            </Teleport>
            <Teleport v-else-if="showV2InvisiblePreview" to="#v2_response">
                <ChallengeV2 badge="inline">
                    <span class="text-muted small d-block mb-2">Click to test invisible reCAPTCHA</span>
                </ChallengeV2>
            </Teleport>
        </RecaptchaPreviewProvider>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'
import { Checkbox, ChallengeV2, ChallengeV3 } from 'vue-recaptcha/head'
import RecaptchaPreviewProvider from './RecaptchaPreviewProvider.vue'

const COMPONENT = 'recaptcha-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)
const previewTrigger = ref(0)

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
    { id: 'v3_invisible', name: 'reCAPTCHA v3' },
    { id: 'v2_invisible', name: 'reCAPTCHA v2 Invisible' },
    { id: 'v2_checkbox',  name: 'reCAPTCHA v2 Checkbox' },
]
const failoverOptions = [
    { id: 'none',        name: 'None' },
    { id: 'v2_checkbox', name: 'Fallback to reCAPTCHA v2 Checkbox' },
]
const badgeOptions = [
    { id: 'bottomright', name: 'Bottom Right' },
    { id: 'bottomleft',  name: 'Bottom Left' },
    { id: 'inline',      name: 'Inline' },
]

const isV3           = computed(() => form.captcha_version === 'v3_invisible')
const isV2Checkbox   = computed(() => form.captcha_version === 'v2_checkbox' || (isV3.value && form.failover_action === 'v2_checkbox'))
const isV2Invisible  = computed(() => form.captcha_version === 'v2_invisible')
const isV2           = computed(() => isV2Checkbox.value || isV2Invisible.value)
const showBadge      = computed(() => isV3.value || isV2Invisible.value)

const showV3Preview          = computed(() => isV3.value && form.v3_site_key.trim() !== '')
const showV2CheckboxPreview  = computed(() => isV2Checkbox.value && form.v2_site_key.trim() !== '')
const showV2InvisiblePreview = computed(() => isV2Invisible.value && form.v2_site_key.trim() !== '')

const selectedVersion  = computed(() => versionOptions.find(o => o.id === form.captcha_version) ?? null)
const selectedFailover = computed(() => failoverOptions.find(o => o.id === form.failover_action) ?? null)
const selectedBadge    = computed(() => badgeOptions.find(o => o.id === form.badge_position) ?? null)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/recaptcha`)
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

async function save() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/recaptcha`, {
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
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
