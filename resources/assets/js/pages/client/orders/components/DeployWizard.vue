<template>
    <div>

        <!-- Step 0: Server Type -->
        <template v-if="step === 0">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                         :class="{ 'border border-primary': form.deploy_mode === 'extract_only' }"
                         @click="form.deploy_mode = 'extract_only'">
                        <div class="card-body p-relative zindex-1 p-3">
                            <div class="feature-box feature-box-style-6 text-center d-block">
                                <div class="feature-box-icon justify-content-center">
                                    <i class="fas fa-upload text-primary"></i>
                                </div>
                                <div class="feature-box-info">
                                    <h4 class="text-4 mt-3 mb-2 text-color-grey">Deploy on Existing Server</h4>
                                    <p class="mb-0 text-2">Copies Faveo files to a configured server via SSH/SFTP. Fastest path for servers already running a web stack.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer h-100"
                         :class="{ 'border border-primary': form.deploy_mode === 'fresh_install' }"
                         @click="form.deploy_mode = 'fresh_install'">
                        <div class="card-body p-relative zindex-1 p-3">
                            <div class="feature-box feature-box-style-6 text-center d-block">
                                <div class="feature-box-icon justify-content-center">
                                    <i class="fas fa-server text-primary"></i>
                                </div>
                                <div class="feature-box-info">
                                    <h4 class="text-4 mt-3 mb-2 text-color-grey">Deploy on Fresh Server</h4>
                                    <p class="mb-0 text-2">Runs the Faveo install script on a bare OS — installs PHP, Apache/Nginx, MariaDB, Redis, and Supervisor.</p>
                                    <span class="badge bg-warning text-dark mt-2">15–30 min</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <a v-if="manualGuideUrl" :href="manualGuideUrl" target="_blank" rel="noopener noreferrer" class="text-muted small">
                    <i class="fas fa-book me-1"></i>{{ __('message.manual_install_guide') }}
                </a>
                <span v-else></span>
                <button class="btn btn-primary btn-sm btn-modern" :disabled="!form.deploy_mode" @click="goToConfigure">
                    {{ __('message.continue') }} <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </template>

        <!-- Step 1: Configure — one card, segmented -->
        <template v-else-if="step === 1">
            <AppAlert :componentName="COMPONENT" />

            <div class="card">
                <div class="card-body">

                    <!-- Segment: SSH Connectivity -->
                    <h6 class="text-color-grey text-uppercase fw-semibold mb-3 section-label">
                        SSH Connectivity
                    </h6>
                    <div class="row">
                        <div class="col-md-8">
                            <ClientField type="text" name="host" label="Host Address" :required="true"
                                         v-model="form.host" placeholder="192.168.1.100 or example.com"
                                         :error="errors.host" @update:modelValue="setFieldError('host', undefined)" />
                        </div>
                        <div class="col-md-4">
                            <ClientField type="text" name="port" label="Port" :required="true"
                                         :model-value="String(form.port)" placeholder="22"
                                         :error="errors.port"
                                         @update:modelValue="form.port = Number($event); setFieldError('port', undefined)" />
                        </div>
                        <div class="col-md-6">
                            <ClientField type="text" name="username" label="Username" :required="true"
                                         v-model="form.username" placeholder="root or ubuntu"
                                         :error="errors.username" @update:modelValue="setFieldError('username', undefined)" />
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-dark">Auth Method</label>
                                <div class="d-flex align-items-center gap-4">
                                    <label class="d-flex align-items-center mb-0 clickable">
                                        <input type="radio" class="me-2" name="auth_method" value="password" v-model="form.auth_method" />
                                        Password
                                    </label>
                                    <label class="d-flex align-items-center mb-0 clickable">
                                        <input type="radio" class="me-2" name="auth_method" value="private_key" v-model="form.auth_method" />
                                        Private Key
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.auth_method === 'password'" class="col-md-6">
                            <ClientField type="password" name="password" label="SSH Password" :required="true"
                                         v-model="form.password" autocomplete="new-password"
                                         :error="errors.password" @update:modelValue="setFieldError('password', undefined)" />
                        </div>
                        <div v-else class="col-12">
                            <ClientField type="textarea" name="private_key" label="SSH Private Key" :required="true"
                                         v-model="form.private_key" :rows="4"
                                         placeholder="-----BEGIN OPENSSH PRIVATE KEY-----"
                                         :error="errors.private_key" @update:modelValue="setFieldError('private_key', undefined)" />
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-dark">
                                    Sudo Password
                                    <ToolTip message="Required only if the SSH user needs elevated privileges (sudo) to write to the deploy path or run commands on the server." size="small" />
                                </label>
                                <div class="input-group" :class="{ 'is-invalid': errors.sudo_password }">
                                    <input class="form-control form-control-lg text-4"
                                           :class="{ 'is-invalid': errors.sudo_password }"
                                           :type="showSudo ? 'text' : 'password'"
                                           v-model="form.sudo_password"
                                           autocomplete="new-password"
                                           @input="setFieldError('sudo_password', undefined)" />
                                    <button type="button" class="input-group-text" tabindex="-1"
                                            @mousedown.prevent @click="showSudo = !showSudo">
                                        <i class="fa" :class="showSudo ? 'fa-eye' : 'fa-eye-slash'"></i>
                                    </button>
                                </div>
                                <div v-if="errors.sudo_password" class="invalid-feedback d-block">{{ errors.sudo_password }}</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3" />

                    <!-- Segment: Deployment Path (extract_only) -->
                    <template v-if="form.deploy_mode === 'extract_only'">
                        <h6 class="text-color-grey text-uppercase fw-semibold mb-3 section-label">
                            Deployment Path
                        </h6>
                        <div class="row">
                            <div class="col-md-8">
                                <ClientField type="text" name="deploy_path" label="Deploy Path" :required="true"
                                             v-model="form.deploy_path" placeholder="/var/www/faveo"
                                             :error="errors.deploy_path" @update:modelValue="setFieldError('deploy_path', undefined)" />
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label text-dark">
                                        Domain
                                        <ToolTip message="Optional. Point your domain to this server's IP before deploying — it will be used to generate the web installer link once files are extracted." size="small" />
                                    </label>
                                    <input type="text" class="form-control form-control-lg text-4"
                                           v-model="form.install_domain" placeholder="helpdesk.example.com"
                                           @input="setFieldError('install_domain', undefined)" />
                                </div>
                            </div>
                        </div>
                        <hr class="my-3" />
                    </template>

                    <!-- Segment: Installation Details (fresh_install) -->
                    <template v-if="form.deploy_mode === 'fresh_install'">
                        <h6 class="text-color-grey text-uppercase fw-semibold mb-3 section-label">
                            Installation Details
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ClientField type="text" name="install_domain" label="Domain" :required="true"
                                             v-model="form.install_domain" placeholder="helpdesk.example.com"
                                             :error="errors.install_domain" @update:modelValue="setFieldError('install_domain', undefined)" />
                            </div>
                            <div class="col-md-6">
                                <ClientField type="email" name="install_email" label="Admin Email" :required="true"
                                             v-model="form.install_email"
                                             :error="errors.install_email" @update:modelValue="setFieldError('install_email', undefined)" />
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-dark">Web Server <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-4">
                                        <label class="d-flex align-items-center mb-0 clickable">
                                            <input type="radio" class="me-2" name="web_server" :value="1" v-model="form.web_server" />
                                            Apache
                                        </label>
                                        <label class="d-flex align-items-center mb-0 clickable">
                                            <input type="radio" class="me-2" name="web_server" :value="2" v-model="form.web_server" />
                                            Nginx
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-dark">SSL Certificate <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-4">
                                        <label v-for="opt in sslOptions" :key="opt.value"
                                               class="d-flex align-items-center mb-0 clickable">
                                            <input type="radio" class="me-2" name="ssl_type" :value="opt.value" v-model="form.ssl_type" />
                                            {{ opt.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <template v-if="form.ssl_type === 'C'">
                                <div class="col-md-6">
                                    <ClientField type="text" name="ssl_cert_path" label="Certificate Path" :required="true"
                                                 v-model="form.ssl_cert_path" placeholder="/etc/ssl/certs/cert.crt"
                                                 :error="errors.ssl_cert_path" @update:modelValue="setFieldError('ssl_cert_path', undefined)" />
                                </div>
                                <div class="col-md-6">
                                    <ClientField type="text" name="ssl_key_path" label="Key Path" :required="true"
                                                 v-model="form.ssl_key_path" placeholder="/etc/ssl/private/cert.key"
                                                 :error="errors.ssl_key_path" @update:modelValue="setFieldError('ssl_key_path', undefined)" />
                                </div>
                            </template>
                        </div>
                        <hr class="my-3" />
                    </template>

                    <!-- Segment: Version -->
                    <h6 class="text-color-grey text-uppercase fw-semibold mb-3 section-label">
                        Version to Deploy
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div v-if="loadingVersions" class="mb-3"><loader /></div>
                            <DynamicSelect
                                v-else
                                name="version_id"
                                label="Version"
                                :elements="versionOptions"
                                :value="versionOptions.find(v => v.id === form.version_id) ?? versionOptions[0]"
                                :onChange="(val) => form.version_id = val?.id ?? null"
                                :clearable="false"
                            />
                        </div>
                        <div class="col-md-6">
                            <ClientField type="text" name="web_user" label="Web User"
                                         v-model="form.web_user" placeholder="www-data (auto-detected if blank)" />
                        </div>
                    </div>

                    <p class="text-muted small mt-2 mb-3"><i class="fas fa-lock me-1"></i> Your credentials are never stored — used only for this request.</p>

                    <div class="form-group row">
                        <div class="col-6">
                            <button type="button" class="btn btn-light btn-modern" @click="step = 0">
                                <i class="fas fa-arrow-left me-1"></i> {{ __('message.back') }}
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-primary btn-modern float-end" :disabled="deploying" @click="startDeploy">
                                <i v-if="deploying" class="fas fa-circle-notch fa-spin me-1"></i>
                                {{ __('message.deploy') }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </template>

        <!-- Step 2: Progress -->
        <template v-else-if="step === 2">
            <AppCard>
                <h5 class="text-4 mb-3">Live Deployment</h5>
                <div v-for="(log, i) in progressLog" :key="i" class="d-flex align-items-start gap-2 mb-2">
                    <span v-if="log.status === 'ok'" class="text-success"><i class="fas fa-check-circle"></i></span>
                    <span v-else-if="log.status === 'error'" class="text-danger"><i class="fas fa-times-circle"></i></span>
                    <span v-else class="text-muted"><i class="fas fa-circle-notch fa-spin"></i></span>
                    <span :class="{ 'text-danger': log.status === 'error' }">{{ log.message }}</span>
                </div>
                <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>
            </AppCard>
        </template>

        <!-- Step 3: Success -->
        <template v-else-if="step === 3">
            <AppCard>
                <div class="text-center py-2">
                    <i class="fas fa-check-circle text-success icon-3rem"></i>
                    <h4 class="text-4 mt-3 mb-1">Setup Complete!</h4>
                    <p class="text-muted text-2">Your Faveo instance has been successfully deployed.</p>
                </div>
                <template v-if="result.setup_url">
                    <hr>
                    <p class="mb-1 fw-semibold">Web Installer</p>
                    <p class="text-muted small mb-2">Open this URL to complete the Faveo setup wizard.</p>
                    <a :href="result.setup_url" target="_blank" class="btn btn-primary btn-sm btn-modern">
                        Visit Web Installer <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </template>
                <template v-if="result.site_url">
                    <hr>
                    <p class="mb-1 fw-semibold">Detected Site URL</p>
                    <a :href="result.site_url" target="_blank" class="btn btn-outline-primary btn-sm btn-modern">{{ result.site_url }}</a>
                </template>
                <template v-if="result.credentials">
                    <hr>
                    <p class="mb-1 fw-semibold">Administrator Credentials</p>
                    <p class="text-muted small mb-2">Save these securely.</p>
                    <pre class="bg-light p-3 rounded small">{{ result.credentials }}</pre>
                </template>
                <hr>
                <button class="btn btn-outline-secondary btn-sm btn-modern" @click="resetWizard">
                    <i class="fas fa-redo me-1"></i> Start Over
                </button>
            </AppCard>
        </template>

    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import ToolTip from '@/components/Reusable/Tooltip.vue'
import { deploySchema } from '@/validations/client/deployWizard.js'

const props = defineProps({
    orderId:        { type: Number, required: true },
    serialKey:      { type: String, default: '' },
    orderNumber:    { type: String, default: '' },
    manualGuideUrl: { type: String, default: null },
})

const { errors, setErrors, setFieldError } = useForm()

const COMPONENT       = 'deploy-wizard'

const step            = ref(0)
const showSudo        = ref(false)
const deploying       = ref(false)
const loadingVersions = ref(false)
const versions        = ref([])
const progressLog     = ref([])
const errorMessage    = ref('')
const result          = ref({})

const sslOptions = [
    { label: "Let's Encrypt (free)", value: 'A' },
    { label: 'Self-Signed',          value: 'B' },
    { label: 'Paid Certificate',     value: 'C' },
]

const versionOptions = computed(() => [
    { id: null, name: 'Latest' },
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
    web_user:        '',
    version_id:      null,
})

async function goToConfigure() {
    step.value = 1
    if (versions.value.length === 0) {
        loadingVersions.value = true
        try {
            const res = await http.get(`/get-deploy-versions/${props.orderId}`)
            versions.value = res.data?.data ?? []
        } catch (e) {
            errorHandler(e, COMPONENT)
        } finally {
            loadingVersions.value = false
        }
    }
}

function addLog(message, status = 'pending') {
    progressLog.value.push({ message, status })
    return progressLog.value.length - 1
}

function updateLog(index, status) {
    if (progressLog.value[index]) progressLog.value[index].status = status
}

async function runStep(stepName, extra = {}) {
    const payload = {
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
        install_license: props.serialKey  || undefined,
        install_order:   props.orderNumber || undefined,
        web_server:      form.web_server      || undefined,
        ssl_type:        form.ssl_type        || undefined,
        ssl_cert_path:   form.ssl_cert_path   || undefined,
        ssl_key_path:    form.ssl_key_path    || undefined,
        ...extra,
    }
    const res = await http.post(`/deploy-product-step`, payload)
    return res.data?.data ?? {}
}

async function startDeploy() {
    if (! await validateForm(deploySchema, form, setErrors)) return

    deploying.value    = true
    errorMessage.value = ''
    progressLog.value  = []
    step.value         = 2

    try {
        const verifyIdx = addLog('Verifying SSH connection…')
        await runStep('verify')
        updateLog(verifyIdx, 'ok')

        if (form.deploy_mode === 'fresh_install') {
            const installIdx  = addLog('Running installation script (this may take 15–30 min)…')
            const installData = await runStep('install')
            updateLog(installIdx, 'ok')
            result.value = { ...installData }
        } else {
            const uploadIdx  = addLog('Uploading product files via SFTP…')
            const uploadData = await runStep('upload')
            updateLog(uploadIdx, 'ok')

            const extractIdx  = addLog('Extracting files on remote server…')
            const extractData = await runStep('extract', { remote_path: uploadData.remote_path })
            updateLog(extractIdx, 'ok')
            result.value = { ...extractData }
        }

        step.value = 3
    } catch (err) {
        const msg = err?.response?.data?.message ?? err?.message ?? 'Deployment failed.'
        errorMessage.value = msg
        const lastIdx = progressLog.value.length - 1
        if (lastIdx >= 0) updateLog(lastIdx, 'error')
    } finally {
        deploying.value = false
    }
}

function resetWizard() {
    step.value         = 0
    progressLog.value  = []
    errorMessage.value = ''
    result.value       = {}
    form.deploy_mode   = ''
}
</script>

<style scoped>
.section-label { font-size: 0.7rem; letter-spacing: .08em; }
.clickable { cursor: pointer; }
.icon-3rem { font-size: 3rem; }
</style>
