<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.recaptcha_configuration') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

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
                                    <SelectField
                                        name="captcha_version"
                                        :label="__('message.recaptcha_version')"
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
                                        :label="__('message.failover_action')"
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
                            <h4 class="card-title">{{ __('message.recaptcha_v3_settings') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v3_site_key"
                                        :label="__('message.v3_site_key')"
                                        :value="form.v3_site_key"
                                        :placeholder="__('message.enter_v3_site_key')"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v3_secret_key"
                                        :label="__('message.v3_secret_key')"
                                        type="password"
                                        :value="form.v3_secret_key"
                                        :placeholder="__('message.enter_v3_secret_key')"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="score_threshold"
                                        :label="__('message.v3_score_threshold')"
                                        type="number"
                                        :value="form.score_threshold"
                                        :onChange="(val, key) => form[key] = parseFloat(val)"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('message.recaptcha_v3') }} {{ __('message.preview') }}</label>
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
                            <h4 class="card-title">{{ __('message.recaptcha_v2_settings') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v2_site_key"
                                        :label="__('message.v2_site_key')"
                                        :value="form.v2_site_key"
                                        :placeholder="__('message.enter_v2_site_key')"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <TextField
                                        name="v2_secret_key"
                                        :label="__('message.v2_secret_key')"
                                        type="password"
                                        :value="form.v2_secret_key"
                                        :placeholder="__('message.enter_v2_secret_key')"
                                        :onChange="(val, key) => form[key] = val"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('message.recaptcha_v2') }} {{ __('message.preview') }}</label>
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
                            <h4 class="card-title">{{ __('message.appearance') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3" v-show="isV2Checkbox">
                                    <label class="form-label fw-bold">{{ __('message.theme') }}</label>
                                    <div class="d-flex gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="theme_light" value="light" v-model="form.theme" />
                                            <label class="form-check-label" for="theme_light">{{ __('message.theme_light') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="theme_dark" value="dark" v-model="form.theme" />
                                            <label class="form-check-label" for="theme_dark">{{ __('message.theme_dark') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" v-show="isV2Checkbox">
                                    <label class="form-label fw-bold">{{ __('message.size') }}</label>
                                    <div class="d-flex gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="size_normal" value="normal" v-model="form.size" />
                                            <label class="form-check-label" for="size_normal">{{ __('message.size_normal') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="size_compact" value="compact" v-model="form.size" />
                                            <label class="form-check-label" for="size_compact">{{ __('message.size_compact') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" v-show="showBadge">
                                    <SelectField
                                        name="badge_position"
                                        :label="__('message.badge_position')"
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
                    <action-button action="save" :loading="saving" @click="save" />
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
                            <button class="btn btn-sm btn-outline-secondary" @click="execute">{{ __('message.verify_v3_key') }}</button>
                            <span v-if="response" class="text-success small">
                                <i class="fas fa-check-circle me-1"></i> {{ __('message.v3_key_valid') }}
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
    { id: 'inline',      name: __('message.badge_inline') },
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
