<template>
    <div>

        <!-- Step 0: Server Type -->
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                     :class="{ 'border border-primary': form.deploy_mode === 'extract_only' }"
                     role="button" tabindex="0"
                     :aria-pressed="form.deploy_mode === 'extract_only'"
                     @click="form.deploy_mode = 'extract_only'"
                     @keydown.enter.space.prevent="form.deploy_mode = 'extract_only'">
                    <div class="card-body p-relative zindex-1 p-3">
                        <div class="feature-box feature-box-style-6 text-center d-block">
                            <div class="feature-box-icon justify-content-center">
                                <i class="fas fa-upload text-primary" aria-hidden="true"></i>
                            </div>
                            <div class="feature-box-info">
                                <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.deploy_on_existing_server') }}</h4>
                                <p class="mb-0 text-2">{{ __('message.deploy_existing_server_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                     :class="{ 'border border-primary': form.deploy_mode === 'fresh_install' }"
                     role="button" tabindex="0"
                     :aria-pressed="form.deploy_mode === 'fresh_install'"
                     @click="form.deploy_mode = 'fresh_install'"
                     @keydown.enter.space.prevent="form.deploy_mode = 'fresh_install'">
                    <div class="card-body p-relative zindex-1 p-3">
                        <div class="feature-box feature-box-style-6 text-center d-block">
                            <div class="feature-box-icon justify-content-center">
                                <i class="fas fa-server text-primary" aria-hidden="true"></i>
                            </div>
                            <div class="feature-box-info">
                                <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.deploy_on_fresh_server') }}</h4>
                                <p class="mb-0 text-2">{{ __('message.deploy_fresh_server_desc') }}</p>
                                <span class="badge bg-warning text-dark mt-2">{{ __('message.deploy_time_estimate') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dw-actions">
            <a v-if="manualGuideUrl" :href="manualGuideUrl" target="_blank" rel="noopener noreferrer" class="dw-guide">
                {{ __('message.deploy_manual_guide_link') }}<i class="fas fa-external-link-alt ms-2" aria-hidden="true"></i>
            </a>
            <button type="button" class="btn btn-primary btn-modern dw-btn ms-auto" :disabled="!form.deploy_mode" @click="openWizard">
                {{ __('message.continue') }}
            </button>
        </div>

        <Modal :showModal="showModal" :onClose="closeModal" :showCloseBtn="false"
               :showControls="!(step === 3 && deploying)" classname="modal-lg">
            <template #title>
                <h5 class="modal-title fw-bold">
                    {{ form.deploy_mode === 'fresh_install' ? __('message.deploy_on_fresh_server') : __('message.deploy_on_existing_server') }}
                </h5>
                <p v-if="step <= 2" class="dw-subtitle mb-0">
                    {{ __('message.deploy_step_of', { current: step, total: 2, name: step === 1 ? __('message.connection') : __('message.installation_details') }) }}
                </p>
            </template>

            <template #fields>
                <AppAlert v-if="step <= 2" :componentName="COMPONENT" />

                <!-- Step 1: Connection -->
                <template v-if="step === 1">
                    <div class="row">
                        <div class="col-8 col-md-9">
                            <ClientField type="text" name="host" :label="__('message.deploy_host_address')" :required="true"
                                         v-model="form.host" placeholder="192.168.1.100 or example.com…" autocomplete="off" :spellcheck="false"
                                         :error="errors.host" @update:modelValue="setFieldError('host', undefined)" />
                        </div>
                        <div class="col-4 col-md-3">
                            <ClientField type="text" name="port" :label="__('message.port')" :required="true"
                                         :model-value="String(form.port)" placeholder="22" inputmode="numeric" autocomplete="off"
                                         :error="errors.port"
                                         @update:modelValue="form.port = Number($event); setFieldError('port', undefined)" />
                        </div>
                    </div>
                    <ClientField type="text" name="username" :label="__('message.username')" :required="true"
                                 v-model="form.username" placeholder="root or ubuntu…" autocomplete="off" :spellcheck="false"
                                 :error="errors.username" @update:modelValue="setFieldError('username', undefined)" />

                    <div class="mb-3">
                        <span :id="`${COMPONENT}-auth`" class="form-label text-dark d-block">{{ __('message.deploy_auth_method') }}</span>
                        <div class="btn-group dw-toggle" role="radiogroup" :aria-labelledby="`${COMPONENT}-auth`">
                            <template v-for="opt in authOptions" :key="opt.id">
                                <input :id="`${COMPONENT}-auth-${opt.id}`" type="radio" class="btn-check"
                                       name="auth_method" :value="opt.id" v-model="form.auth_method" />
                                <label class="btn btn-outline-primary" :for="`${COMPONENT}-auth-${opt.id}`">{{ opt.name }}</label>
                            </template>
                        </div>
                    </div>

                    <ClientField v-if="form.auth_method === 'password'" type="password" name="password"
                                 :label="__('message.deploy_ssh_password')" :required="true"
                                 v-model="form.password" autocomplete="new-password"
                                 :error="errors.password" @update:modelValue="setFieldError('password', undefined)" />
                    <ClientField v-else type="textarea" name="private_key" :label="__('message.deploy_ssh_private_key')" :required="true"
                                 v-model="form.private_key" :rows="4" :spellcheck="false"
                                 placeholder="-----BEGIN OPENSSH PRIVATE KEY-----"
                                 :error="errors.private_key" @update:modelValue="setFieldError('private_key', undefined)" />

                    <ClientField type="password" name="sudo_password"
                                 :label="`${__('message.deploy_sudo_password')} (${__('message.optional')})`"
                                 v-model="form.sudo_password" autocomplete="new-password"
                                 :error="errors.sudo_password" @update:modelValue="setFieldError('sudo_password', undefined)" />
                    <p class="form-text dw-hint">{{ __('message.deploy_sudo_hint') }}</p>

                    <p class="dw-note mb-0">
                        <i class="fas fa-lock text-primary me-2" aria-hidden="true"></i>{{ __('message.deploy_credentials_notice') }}
                    </p>
                </template>

                <!-- Step 2: Install details -->
                <template v-else-if="step === 2">
                    <template v-if="form.deploy_mode === 'extract_only'">
                        <ClientField type="text" name="deploy_path" :label="__('message.deploy_path_label')" :required="true"
                                     v-model="form.deploy_path" placeholder="/var/www/faveo…" autocomplete="off" :spellcheck="false"
                                     :error="errors.deploy_path" @update:modelValue="setFieldError('deploy_path', undefined)" />
                        <ClientField type="text" name="install_domain"
                                     :label="`${__('message.deploy_domain')} (${__('message.optional')})`"
                                     v-model="form.install_domain" placeholder="helpdesk.example.com…" autocomplete="off" :spellcheck="false" />
                    </template>

                    <template v-else>
                        <div class="row">
                            <div class="col-md-6">
                                <ClientField type="text" name="install_domain" :label="__('message.deploy_domain')" :required="true"
                                             v-model="form.install_domain" placeholder="helpdesk.example.com…" autocomplete="off" :spellcheck="false"
                                             :error="errors.install_domain" @update:modelValue="setFieldError('install_domain', undefined)" />
                            </div>
                            <div class="col-md-6">
                                <ClientField type="email" name="install_email" :label="__('message.deploy_admin_email')" :required="true"
                                             v-model="form.install_email" placeholder="admin@example.com…" autocomplete="off"
                                             :error="errors.install_email" @update:modelValue="setFieldError('install_email', undefined)" />
                            </div>
                            <div class="col-md-6">
                                <DynamicSelect name="web_server" :label="__('message.deploy_web_server')" :required="true"
                                               :elements="webServerOptions"
                                               :value="webServerOptions.find(o => o.id === form.web_server)"
                                               :onChange="(val) => form.web_server = val?.id ?? 1"
                                               :clearable="false" :searchable="false" />
                            </div>
                            <div class="col-md-6">
                                <DynamicSelect name="ssl_type" :label="__('message.deploy_ssl_certificate')" :required="true"
                                               :elements="sslOptions"
                                               :value="sslOptions.find(o => o.id === form.ssl_type)"
                                               :onChange="(val) => form.ssl_type = val?.id ?? 'A'"
                                               :clearable="false" :searchable="false" />
                            </div>
                            <template v-if="form.ssl_type === 'C'">
                                <div class="col-md-6">
                                    <ClientField type="text" name="ssl_cert_path" :label="__('message.deploy_certificate_path')" :required="true"
                                                 v-model="form.ssl_cert_path" placeholder="/etc/ssl/certs/cert.crt…" autocomplete="off" :spellcheck="false"
                                                 :error="errors.ssl_cert_path" @update:modelValue="setFieldError('ssl_cert_path', undefined)" />
                                </div>
                                <div class="col-md-6">
                                    <ClientField type="text" name="ssl_key_path" :label="__('message.deploy_key_path')" :required="true"
                                                 v-model="form.ssl_key_path" placeholder="/etc/ssl/private/cert.key…" autocomplete="off" :spellcheck="false"
                                                 :error="errors.ssl_key_path" @update:modelValue="setFieldError('ssl_key_path', undefined)" />
                                </div>
                                <div class="col-md-6">
                                    <ClientField type="text" name="ssl_ca_path" :label="__('message.deploy_ca_path')" :required="true"
                                                 v-model="form.ssl_ca_path" placeholder="/etc/ssl/certs/ca-bundle.crt…" autocomplete="off" :spellcheck="false"
                                                 :error="errors.ssl_ca_path" @update:modelValue="setFieldError('ssl_ca_path', undefined)" />
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Fresh install fetches its own release, so version and web user only apply to existing servers -->
                    <div v-if="form.deploy_mode === 'extract_only'" class="row">
                        <div class="col-md-6">
                            <DynamicSelect name="version_id" :label="__('message.version')" :required="true"
                                           :elements="versionOptions"
                                           :value="versionOptions.find(v => v.id === form.version_id) ?? versionOptions[0]"
                                           :onChange="(val) => form.version_id = val?.id ?? null"
                                           :disabled="loadingVersions" :clearable="false" />
                        </div>
                        <div class="col-md-6">
                            <ClientField type="text" name="web_user"
                                         :label="`${__('message.deploy_web_user')} (${__('message.optional')})`"
                                         v-model="form.web_user" :placeholder="__('message.deploy_web_user_placeholder')" autocomplete="off" :spellcheck="false" />
                        </div>
                    </div>
                </template>

                <!-- Step 3: Deploy (live progress) -->
                <template v-else-if="step === 3">
                    <div class="dw-head">
                        <div class="min-w-0">
                            <p class="dw-head-title mb-1" :class="{ 'text-danger': errorMessage }">{{ headTitle }}</p>
                            <p class="dw-head-sub mb-0 text-break">{{ form.install_domain || form.host }}</p>
                        </div>
                        <span class="dw-clock">{{ totalTime }}</span>
                    </div>
                    <!-- Same timeline as the installer's database setup (databaseMigration.blade.php, AdminLTE .timeline):
                         one item per step; once the install script runs, its own stages become items -->
                    <div class="dw-timeline" aria-live="polite">
                        <div v-for="row in rows" :key="row.key" :class="`is-${row.state}`">
                            <i class="dw-tl-icon" :class="ROW_ICONS[row.state]" aria-hidden="true"></i>
                            <div class="dw-tl-item">
                                <span v-if="row.time" class="dw-tl-time"><i class="far fa-clock me-1" aria-hidden="true"></i>{{ row.time }}</span>
                                <h3 class="dw-tl-header">{{ row.label }}<span class="visually-hidden"> — {{ ROW_STATE_TEXT[row.state] }}</span></h3>
                                <div v-if="row.state === 'error'" class="dw-tl-body" role="alert">
                                    <strong class="d-block">{{ failure.reason || errorMessage }}</strong>
                                    <span v-if="failure.rolledBack" class="d-block mt-1">{{ __('message.deploy_rolled_back') }}</span>
                                    <span v-if="errorDetails.exit_code != null" class="d-block dw-muted mt-1">{{ __('message.deploy_exit_code', { code: errorDetails.exit_code }) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="deploying" class="dw-muted small mt-2 mb-0">{{ __('message.deploy_running_notice') }}</p>

                    <!-- Live script output: always there for a fresh install, open by default -->
                    <div v-if="form.deploy_mode === 'fresh_install' || logLines.length" class="dw-log">
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-link p-0 dw-link" :aria-expanded="showLog" @click="showLog = !showLog">
                                <i :class="showLog ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="me-1" aria-hidden="true"></i>{{ showLog ? __('message.deploy_hide_log') : __('message.deploy_show_log') }}
                            </button>
                            <button type="button" class="btn btn-link p-0 dw-link ms-auto" :disabled="!logLines.length" @click="copyLog">
                                <i :class="copied ? 'fas fa-check' : 'far fa-copy'" class="me-1" aria-hidden="true"></i>{{ copied ? __('message.deploy_log_copied') : __('message.deploy_copy_log') }}
                            </button>
                        </div>
                        <div v-if="showLog" ref="terminal" class="dw-terminal p-3 rounded small mt-2" @scroll="onTerminalScroll">
                            <p v-if="!logLines.length" class="dw-ansi-dim mb-0">{{ __('message.deploy_log_waiting') }}</p>
                            <p v-if="logLines.length > MAX_LINES" class="dw-ansi-dim mb-2">{{ __('message.deploy_log_truncated', { count: MAX_LINES }) }}</p>
                            <div v-for="(line, i) in logLines.slice(-MAX_LINES)" :key="i" class="dw-line"><span
                                v-for="(seg, j) in line.segments" :key="j" :class="seg.color && `dw-ansi-${seg.color}`">{{ seg.text }}</span></div>
                        </div>
                    </div>
                </template>

                <!-- Step 4: Done -->
                <template v-else-if="step === 4">
                    <div class="text-center py-2">
                        <i class="fas fa-check-circle text-success fa-3x" aria-hidden="true"></i>
                        <h4 class="text-4 mt-3 mb-1">{{ __('message.deploy_setup_complete') }}</h4>
                        <p class="text-muted text-2">{{ __('message.deploy_success_desc') }}</p>
                    </div>
                    <template v-if="result.setup_url">
                        <hr>
                        <p class="mb-1 fw-semibold">{{ __('message.deploy_web_installer') }}</p>
                        <p class="text-muted small mb-2">{{ __('message.deploy_web_installer_desc') }}</p>
                        <a :href="result.setup_url" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-modern">
                            {{ __('message.deploy_visit_web_installer') }} <i class="fas fa-external-link-alt ms-1" aria-hidden="true"></i>
                        </a>
                    </template>
                    <template v-if="result.site_url">
                        <hr>
                        <p class="mb-1 fw-semibold">{{ __('message.deploy_detected_site_url') }}</p>
                        <a :href="result.site_url" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-modern">{{ result.site_url }}</a>
                    </template>
                    <template v-if="result.credentials">
                        <hr>
                        <p class="mb-1 fw-semibold">{{ __('message.deploy_admin_credentials') }}</p>
                        <p class="text-muted small mb-2">{{ __('message.deploy_save_securely') }}</p>
                        <pre class="dw-creds bg-light p-3 rounded small">{{ result.credentials }}</pre>
                    </template>
                </template>
            </template>

            <template #controls>
                <template v-if="step === 1">
                    <action-button action="cancel" :disabled="checking" @click="closeModal" />
                    <action-button action="confirm" icon="fas fa-arrow-right" :label="__('message.next')" :loading="checking" @click="nextStep" />
                </template>
                <template v-else-if="step === 2">
                    <action-button action="back" @click="step = 1" />
                    <action-button action="confirm" :label="__('message.deploy')" :loading="deploying" @click="startDeploy" />
                </template>
                <template v-else-if="step === 3">
                    <action-button action="back" @click="step = 2" />
                    <action-button v-if="errorMessage" action="confirm" icon="fas fa-redo" :label="__('message.retry')" @click="startDeploy" />
                </template>
                <template v-else-if="step === 4">
                    <action-button action="confirm" :label="__('message.done')" @click="resetWizard" />
                </template>
            </template>
        </Modal>

    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import Modal from '@/themes/porto/components/common/Modal.vue'
import AppAlert from '@/components/Reusable/Alert.vue'
import { deploySchema } from '@/validations/client/deployWizard.js'
import { parseLog, failureReason, stages } from '@/helpers/ansiLog.js'
import { __ } from '@/plugins/i18n'

const props = defineProps({
    orderId:        { type: Number, required: true },
    manualGuideUrl: { type: String, default: null },
})

const { errors, setErrors, setFieldError } = useForm()

const COMPONENT = 'deploy-wizard'

// 0 = closed, 1 = connection, 2 = install details, 3 = deploying, 4 = done
const step            = ref(0)
const showModal       = ref(false)
const deploying       = ref(false)
const checking        = ref(false)   // connection check on Next
const loadingVersions = ref(false)
const versions        = ref([])
const status          = ref({})   // server step key → 'running' | 'ok' | 'error'
const errorMessage    = ref('')
const errorDetails    = ref({})
const liveLog         = ref('')     // install script output (raw, with colour codes), streamed in by followInstall()
const terminal        = ref(null)
const showLog         = ref(false)
const copied          = ref(false)
const startedAt       = ref(0)
const endedAt         = ref(0)      // 0 while running
const now             = ref(Date.now())
const result          = ref({})

const MAX_LINES      = 2000         // rendered; Copy log still has everything
// Same icons and colours as the installer's timeline: check / spinner / cross
const ROW_ICONS      = { ok: 'fas fa-check', running: 'fas fa-spinner fa-spin', error: 'fas fa-times', pending: 'far fa-circle' }
const ROW_STATE_TEXT = { ok: __('message.done'), running: __('message.pending'), error: __('message.failed'), pending: __('message.pending') }

const logLines  = computed(() => parseLog(liveLog.value))
const stageList = computed(() => stages(logLines.value))
const stageSeenAt = ref([])  // when each script stage first showed up — client clock, so ±1 poll

watch(stageList, list => {
    while (stageSeenAt.value.length < list.length) stageSeenAt.value.push(Date.now())
})
const failure   = computed(() => status.value.install === 'error' ? failureReason(logLines.value) : { reason: '', rolledBack: false })

const clock = (from, to) => {
    const s = Math.max(0, Math.round(((to || now.value) - from) / 1000))
    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`
}
const totalTime = computed(() => clock(startedAt.value, endedAt.value))
const headTitle = computed(() => {
    if (errorMessage.value) return form.deploy_mode === 'fresh_install' ? __('message.deploy_install_failed') : __('message.deploy_failed_generic')
    return form.deploy_mode === 'fresh_install' ? __('message.deploy_installing_title') : __('message.deploy_deploying_title')
})

const rows = computed(() => {
    const base = plannedSteps.value.map(s => {
        const state = status.value[s.key] ?? 'pending'
        return { key: s.key, state, label: state === 'ok' && s.doneLabel ? s.doneLabel : s.label }
    })
    if (form.deploy_mode !== 'fresh_install' || !stageList.value.length) return base

    // Once the script talks, its own stages carry the progress; the last one is where it is now.
    const last    = stageList.value.length - 1
    const install = status.value.install
    return [
        ...base.map(r => r.key === 'install' ? { ...r, state: 'ok', label: __('message.deploy_installer_started') } : r),
        ...stageList.value.map((label, i) => ({
            key:   `stage-${i}`,
            label,
            state: i < last ? 'ok' : install,
            time:  clock(stageSeenAt.value[i], i < last ? stageSeenAt.value[i + 1] : endedAt.value),
        })),
    ]
})

const sslOptions = [
    { name: __('message.deploy_ssl_lets_encrypt'),     id: 'A' },
    { name: __('message.deploy_ssl_self_signed'),      id: 'B' },
    { name: __('message.deploy_ssl_paid_certificate'), id: 'C' },
]

const authOptions = [
    { name: __('message.password'),                  id: 'password' },
    { name: __('message.deploy_private_key_option'), id: 'private_key' },
]

const webServerOptions = [
    { name: 'Apache', id: 1 },
    { name: 'Nginx',  id: 2 },
]

const versionOptions = computed(() => [
    { id: null, name: __('message.latest') },
    ...versions.value.map(v => ({ id: v.id, name: `${v.version} — ${v.title}` })),
])

const form = reactive({
    deploy_mode:     '',
    host:            '',
    port:            22,
    username:        '',
    auth_method:     'password',
    password:        '',
    private_key:     '',
    sudo_password:   '',
    deploy_path:     '',
    install_domain:  '',
    install_email:   '',
    web_server:      1,
    ssl_type:        'A',
    ssl_cert_path:   '',
    ssl_key_path:    '',
    ssl_ca_path:     '',
    web_user:        '',
    version_id:      null,
})

// The server-side steps each mode runs, in order.
const plannedSteps = computed(() => form.deploy_mode === 'fresh_install'
    ? [
        { key: 'verify',  label: __('message.deploy_log_verifying_ssh'), doneLabel: __('message.deploy_connected') },
        { key: 'install', label: __('message.deploy_starting_installer') },
    ]
    : [
        { key: 'verify',  label: __('message.deploy_log_verifying_ssh'), doneLabel: __('message.deploy_connected') },
        { key: 'upload',  label: __('message.deploy_log_uploading_files') },
        { key: 'extract', label: __('message.deploy_log_extracting_files') },
    ])

// Step 1 only checks the connection fields; Deploy checks the whole form.
const connectSchema = deploySchema.pick(['host', 'port', 'username', 'password', 'private_key'])

async function openWizard() {
    step.value      = 1
    showModal.value = true
    if (versions.value.length === 0) {
        loadingVersions.value = true
        try {
            const res = await http.get(`/get-deploy-versions/${props.orderId}`)
            versions.value = res.data?.data ?? []
        } catch (e) {
            errorHandler(e, COMPONENT, { setErrors })
        } finally {
            loadingVersions.value = false
        }
    }
}

// Next = check the fields, then log in to the server once (no deploy path yet),
// so a wrong host/password shows up here instead of after the whole form.
async function nextStep() {
    if (!await validateForm(connectSchema, form, setErrors)) return
    checking.value = true
    try {
        await http.post(`/deploy-product-step`, {
            step:        'verify',
            order_id:    props.orderId,
            host:        form.host,
            port:        form.port,
            username:    form.username,
            auth_method: form.auth_method,
            password:    form.auth_method === 'password'    ? form.password    : undefined,
            private_key: form.auth_method === 'private_key' ? form.private_key : undefined,
            deploy_mode: form.deploy_mode,
        })
        step.value = 2
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        checking.value = false
    }
}

function stepPayload(stepName) {
    return {
        step:            stepName,
        order_id:        props.orderId,
        host:            form.host,
        port:            form.port,
        username:        form.username,
        auth_method:     form.auth_method,
        password:        form.auth_method === 'password'    ? form.password    : undefined,
        private_key:     form.auth_method === 'private_key' ? form.private_key : undefined,
        deploy_mode:     form.deploy_mode,
        deploy_path:     form.deploy_path    || undefined,
        sudo_password:   form.sudo_password  || undefined,
        version_id:      form.version_id     || undefined,
        web_user:        form.web_user        || undefined,
        install_domain:  form.install_domain  || undefined,
        install_email:   form.install_email   || undefined,
        web_server:      form.web_server      || undefined,
        ssl_type:        form.ssl_type        || undefined,
        ssl_cert_path:   form.ssl_cert_path   || undefined,
        ssl_key_path:    form.ssl_key_path    || undefined,
        ssl_ca_path:     form.ssl_ca_path     || undefined,
    }
}

async function runStep(stepName) {
    status.value[stepName] = 'running'
    const res = await http.post(`/deploy-product-step`, stepPayload(stepName))
    status.value[stepName] = 'ok'
    return res.data?.data ?? {}
}

const POLL_MS     = 3000
const MAX_WAIT_MS = 60 * 60 * 1000
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms))
const fail  = (message, details = {}) => Object.assign(new Error(message), { details })

// The install runs detached on the server; poll its log into the terminal until it ends.
async function followInstall() {
    status.value.install = 'running'
    const startedAt = Date.now()
    let offset   = 0
    let failures = 0

    while (Date.now() - startedAt < MAX_WAIT_MS) {
        await sleep(POLL_MS)
        if (unmounted) throw fail(__('message.deploy_failed_generic'))

        let data
        try {
            data = (await http.post(`/deploy-product-step`, { ...stepPayload('status'), offset })).data?.data ?? {}
            failures = 0
        } catch (e) {
            // A network blip or busy server shouldn't kill a 10-minute install view; real errors (auth, 403) should.
            const transient = !e.response || e.response.status >= 500 || e.response.status === 429
            if (!transient || ++failures >= 5) throw e
            continue
        }

        offset = data.offset
        if (data.output) liveLog.value += data.output

        if (data.done) {
            if (data.exit_code !== 0) throw fail(__('message.deploy_install_script_errors'), { exit_code: data.exit_code })
            status.value.install = 'ok'
            return data
        }
    }

    throw fail(__('message.deploy_install_timed_out'))
}

// Follow new output — unless the user scrolled up to read, then leave them there.
let stickToBottom = true
function onTerminalScroll() {
    const el = terminal.value
    stickToBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 24
}
function scrollToBottom() {
    nextTick(() => { if (terminal.value) terminal.value.scrollTop = terminal.value.scrollHeight })
}
watch(liveLog, () => { if (stickToBottom) scrollToBottom() })
watch(showLog, open => { if (open) { stickToBottom = true; scrollToBottom() } })

async function copyLog() {
    try {
        await navigator.clipboard.writeText(logLines.value.map(l => l.text).join('\n'))
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    } catch {
        // Clipboard needs HTTPS and permission; the log stays visible to select by hand.
        showLog.value = true
    }
}

let ticker = null
let unmounted = false
onBeforeUnmount(() => { unmounted = true; clearInterval(ticker) })

async function startDeploy() {
    if (! await validateForm(deploySchema, form, setErrors)) return

    deploying.value    = true
    errorMessage.value = ''
    errorDetails.value = {}
    liveLog.value      = ''
    showLog.value      = true
    stageSeenAt.value  = []
    status.value       = {}
    step.value         = 3

    startedAt.value = now.value = Date.now()
    endedAt.value   = 0
    clearInterval(ticker)
    ticker = setInterval(() => { now.value = Date.now() }, 1000)

    try {
        await runStep('verify')

        if (form.deploy_mode === 'fresh_install') {
            await runStep('install')
            result.value = { ...await followInstall() }
        } else {
            await runStep('upload')
            result.value = { ...await runStep('extract') }
        }

        step.value = 4
    } catch (err) {
        const running = Object.keys(status.value).find(k => status.value[k] === 'running')
        if (running) status.value[running] = 'error'
        errorMessage.value = err?.response?.data?.message ?? err?.message ?? __('message.deploy_failed_generic')
        errorDetails.value = err?.details ?? err?.response?.data?.data ?? {}
        // e.g. wget's error when the install script itself could not be downloaded
        if (errorDetails.value.output) liveLog.value += errorDetails.value.output
        if (liveLog.value) showLog.value = true
    } finally {
        deploying.value = false
        endedAt.value   = Date.now()
        clearInterval(ticker)
    }
}

function closeModal() {
    if (deploying.value || checking.value) return
    if (step.value === 4) return resetWizard()
    showModal.value = false
    step.value      = 0
}

function resetWizard() {
    showModal.value    = false
    step.value         = 0
    status.value       = {}
    errorMessage.value = ''
    errorDetails.value = {}
    liveLog.value      = ''
    showLog.value      = false
    stageSeenAt.value  = []
    result.value       = {}
    form.deploy_mode   = ''
}
</script>

<style scoped>
.dw-terminal {
    background: #1e1e1e;
    color: #d4d4d4;
    max-height: 360px;
    overflow: auto;
    font-family: SFMono-Regular, Menlo, Consolas, monospace;
    line-height: 1.45;
}
.dw-line { white-space: pre-wrap; word-break: break-all; min-height: 1.45em; }
.dw-ansi-red    { color: #f48771; }
.dw-ansi-green  { color: #89d185; }
.dw-ansi-yellow { color: #e5c07b; }
.dw-ansi-blue   { color: #6cb6ff; }
.dw-ansi-cyan   { color: #56d4dd; }
.dw-ansi-dim    { color: #8b8b8b; }

/* Progress: what is happening now, with the overall timer */
.dw-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid #e9ecef;
}
.dw-head-title { font-size: 1.05rem; font-weight: 600; color: #212529; }
.dw-head-sub   { font-size: .85rem; color: #6c757d; }
.dw-clock      { font-size: 1.05rem; font-weight: 600; color: #495057; font-variant-numeric: tabular-nums; }
.dw-muted      { color: #6c757d; }

/* AdminLTE's .timeline, as on the installer's database setup page — the client theme doesn't load AdminLTE */
.dw-timeline { position: relative; margin: 0; }
.dw-timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 15px;
    left: 31px;
    width: 4px;
    border-radius: .375rem;
    background-color: #dee2e6;
}
.dw-timeline > div { position: relative; margin-right: 10px; margin-bottom: 15px; }
.dw-timeline > div:last-child { margin-bottom: 0; }
.dw-tl-icon {
    position: absolute;
    top: 0;
    left: 18px;
    width: 30px;
    height: 30px;
    font-size: 16px;
    line-height: 30px;
    text-align: center;
    border-radius: 50%;
    color: #fff;
    background-color: #adb5bd;
}
.is-ok .dw-tl-icon      { background-color: #198754; }
.is-running .dw-tl-icon { background-color: #0dcaf0; color: #000; }
.is-error .dw-tl-icon   { background-color: #dc3545; }
.is-pending .dw-tl-icon { background-color: #e9ecef; color: #adb5bd; }
.dw-tl-item {
    position: relative;
    margin-left: 60px;
    margin-right: 15px;
    border-radius: .375rem;
    background-color: #fff;
    box-shadow: 0 0 1px rgba(33, 37, 41, .125), 0 1px 3px rgba(33, 37, 41, .2);
}
.dw-tl-time   { float: right; padding: 10px; font-size: 12px; color: #6c757d; font-variant-numeric: tabular-nums; }
.dw-tl-header {
    margin: 0;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
    font-weight: 400;
    color: #6c757d;
    overflow-wrap: anywhere;
    /* the client theme capitalises headings; the installer timeline does not */
    text-transform: none;
    letter-spacing: normal;
}
.is-running .dw-tl-header { color: #212529; font-weight: 600; }
.is-error .dw-tl-header   { color: #b02a37; font-weight: 600; }
.is-pending .dw-tl-header { color: #adb5bd; }
.dw-tl-body { padding: 10px; font-size: .875rem; color: #842029; border-top: 1px solid #dee2e6; }

.dw-log { margin-top: 1.25rem; padding-top: .9rem; border-top: 1px solid #e9ecef; }
.dw-creds { line-height: 1.5; white-space: pre-wrap; }
.dw-link { font-size: .875rem; text-decoration: none; }

/* Tab view: footer row under the cards */
.dw-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}
.dw-guide { font-weight: 500; }
.dw-btn { font-size: .85rem; padding: .7rem 2rem; }

/* Step line under the popup title */
.dw-subtitle { font-size: .85rem; color: #6c757d; margin-top: .125rem; }

/* Password / Private key toggle in the theme colour */
.dw-toggle .btn { min-width: 8rem; padding: .55rem 1.25rem; font-size: .9rem; }
.dw-toggle .btn-outline-primary { color: #212529; border-color: #dee2e6; background: #fff; }
.dw-toggle .btn-check:checked + .btn-outline-primary {
    color: #fff;
    background-color: var(--primary, #0088cc);
    border-color: var(--primary, #0088cc);
}

.dw-hint { margin-top: -.75rem; margin-bottom: 1rem; }
.dw-note { color: #6c757d; }
</style>
