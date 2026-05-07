<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.cron-setting') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Scheduler Command</label>
                            <code class="d-block p-2 bg-light">* * * * * php {{ cronPath }} schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Verify PHP Path</label>
                            <div class="input-group">
                                <select class="form-select" v-model="phpPath">
                                    <option v-for="path in phpPaths" :key="path" :value="path">{{ path }}</option>
                                </select>
                                <button class="btn btn-secondary" @click="verifyPath" :disabled="verifying || !phpPath">
                                    <span v-if="verifying" class="spinner-border spinner-border-sm me-1"></span>
                                    Verify
                                </button>
                            </div>
                            <small class="text-muted">exec(): {{ execEnabled ? 'enabled' : 'disabled' }}</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Job</th>
                                    <th class="text-center">{{ __('message.status') }}</th>
                                    <th>Schedule</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="job in jobs" :key="job.key">
                                    <td class="fw-semibold">{{ job.label }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" v-model="statuses[job.status]" />
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" v-model="conditionForms[job.key].condition">
                                            <option v-for="option in commandOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            class="form-control form-control-sm"
                                            type="time"
                                            v-model="conditionForms[job.key].at"
                                            :disabled="conditionForms[job.key].condition !== 'dailyAt'"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="saveScheduler" :disabled="savingScheduler">
                        <span v-if="savingScheduler" class="spinner-border spinner-border-sm me-1"></span>
                        Save Scheduler
                    </button>
                </div>
            </template>
        </div>

        <div class="card card-light mt-3">
            <div class="card-header">
                <h4 class="card-title">Cron Days</h4>
            </div>
            <div v-if="!loading" class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Expiry Mail Days</label>
                        <select class="form-select" multiple v-model="days.expiryday">
                            <option v-for="option in expiryOptions" :key="option" :value="option">{{ option }} days</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Auto Renewal Days</label>
                        <select class="form-select" multiple v-model="days.subexpiryday">
                            <option v-for="option in expiryOptions" :key="option" :value="option">{{ option }} days</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Post Expiry Days</label>
                        <select class="form-select" multiple v-model="days.postsubexpiry_days">
                            <option v-for="option in expiryOptions" :key="option" :value="option">{{ option }} days</option>
                        </select>
                    </div>
                    <div v-for="field in dayFields" :key="field.key" class="col-md-3 mb-3">
                        <label class="form-label fw-bold">{{ field.label }}</label>
                        <select class="form-select" v-model="days[field.key]">
                            <option v-for="option in field.options" :key="option" :value="option">{{ optionLabel(option, field.deleteLabel) }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div v-if="!loading" class="card-footer">
                <button class="btn btn-primary" @click="saveDays" :disabled="savingDays">
                    <span v-if="savingDays" class="spinner-border spinner-border-sm me-1"></span>
                    Save Days
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'cron-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const savingScheduler = ref(false)
const savingDays = ref(false)
const verifying = ref(false)
const cronPath = ref('')
const phpPath = ref('')
const phpPaths = ref([])
const execEnabled = ref(false)

const commandOptions = [
    { value: 'everyMinute', label: 'Every Minute' },
    { value: 'everyFiveMinutes', label: 'Every Five Minutes' },
    { value: 'everyTenMinutes', label: 'Every Ten Minutes' },
    { value: 'everyThirtyMinutes', label: 'Every Thirty Minutes' },
    { value: 'hourly', label: 'Hourly' },
    { value: 'daily', label: 'Daily' },
    { value: 'dailyAt', label: 'Daily At' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
]

const jobs = [
    { key: 'expiryMail', status: 'expiry_cron', label: 'Expiry Mail' },
    { key: 'deleteLogs', status: 'activity', label: 'Activity Log Cleanup' },
    { key: 'subsExpirymail', status: 'subs_expirymail', label: 'Subscription Expiry Mail' },
    { key: 'postExpirymail', status: 'postsubs_expirymail', label: 'Post Subscription Expiry Mail' },
    { key: 'cloud', status: 'cloud_cron', label: 'Cloud Mail' },
    { key: 'invoice', status: 'invoice_cron', label: 'Invoice Deletion' },
    { key: 'msg91Reports', status: 'msg91_cron', label: 'MSG91 Report Cleanup' },
    { key: 'reoon', status: 'reoon_cron', label: 'Reoon Log Cleanup' },
    { key: 'systemLogs', status: 'systemlogs_cron', label: 'System Log Cleanup' },
    { key: 'installationLogs', status: 'installationlogs_cron', label: 'Installation Log Cleanup' },
    { key: 'licenseReportsCleanup', status: 'licensereports_cron', label: 'License Reports Cleanup' },
    { key: 'licenseCallbacksCleanup', status: 'licensecallbacks_cron', label: 'License Callbacks Cleanup' },
    { key: 'licenseCrackReportsCleanup', status: 'licensecrack_cron', label: 'License Crack Reports Cleanup' },
    { key: 'licenseSystemReportsCleanup', status: 'licensesystem_cron', label: 'License System Reports Cleanup' },
    { key: 'licenseVersionsCleanup', status: 'licenseversions_cron', label: 'License Versions Cleanup' },
]

const statuses = reactive({})
const conditionForms = reactive({})
const days = reactive({
    expiryday: [],
    subexpiryday: [],
    postsubexpiry_days: [],
    cloud_days: '30',
    invoice_days: '7',
    msg91_days: '30',
    reoon_days: '30',
    system_logs_days: '30',
    installation_logs_days: '30',
    license_reports_days: '30',
    license_callbacks_days: '30',
    license_crack_days: '30',
    license_system_days: '30',
    license_versions_days: '30',
    logdelday: '30',
})

const expiryOptions = ['30', '15', '7', '1']
const cleanupOptions = ['720', '365', '180', '150', '60', '30', '15', '5', '2', '0']
const licenseOptions = ['720', '365', '180', '90', '30', '15', '7', '1']
const dayFields = [
    { key: 'cloud_days', label: 'Cloud Mail Days', options: expiryOptions },
    { key: 'invoice_days', label: 'Invoice Deletion Days', options: ['7', '5', '2', '1'] },
    { key: 'msg91_days', label: 'MSG91 Report Days', options: cleanupOptions, deleteLabel: 'Delete All Reports' },
    { key: 'reoon_days', label: 'Reoon Log Days', options: ['30', '15', '10', '5', '1'] },
    { key: 'system_logs_days', label: 'System Log Days', options: cleanupOptions, deleteLabel: 'Delete All Logs' },
    { key: 'installation_logs_days', label: 'Installation Log Days', options: licenseOptions },
    { key: 'license_reports_days', label: 'License Reports Days', options: licenseOptions },
    { key: 'license_callbacks_days', label: 'License Callbacks Days', options: licenseOptions },
    { key: 'license_crack_days', label: 'License Crack Days', options: licenseOptions },
    { key: 'license_system_days', label: 'License System Days', options: licenseOptions },
    { key: 'license_versions_days', label: 'License Versions Days', options: licenseOptions },
    { key: 'logdelday', label: 'Activity Log Days', options: cleanupOptions, deleteLabel: 'Delete All Logs' },
]

jobs.forEach(job => {
    statuses[job.status] = false
    conditionForms[job.key] = { condition: 'daily', at: '' }
})

onMounted(load)

async function load() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/settings/cron-data`)
        const data = res.data?.data ?? {}
        cronPath.value = data.cron_path ?? ''
        phpPaths.value = data.php_paths ?? []
        phpPath.value = phpPaths.value[0] ?? ''
        execEnabled.value = Boolean(data.exec_enabled)
        Object.assign(statuses, data.statuses ?? {})
        Object.assign(days, data.days ?? {})
        Object.entries(data.conditions ?? {}).forEach(([job, value]) => {
            const parts = String(value || 'daily').split(',')
            if (conditionForms[job]) {
                conditionForms[job].condition = parts[0] || 'daily'
                conditionForms[job].at = parts[1] || ''
            }
        })
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

function optionLabel(option, deleteLabel = '') {
    return option === '0' && deleteLabel ? deleteLabel : `${option} days`
}

function conditionPayload() {
    return jobs.reduce((payload, job) => {
        const form = conditionForms[job.key]
        payload[job.key] = form.condition === 'dailyAt' ? `dailyAt,${form.at || '00:00'}` : form.condition
        return payload
    }, {})
}

async function verifyPath() {
    verifying.value = true
    try {
        const res = await http.post(`${baseUrl}/verify-php-path`, { path: phpPath.value })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        verifying.value = false
    }
}

async function saveScheduler() {
    savingScheduler.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/cron-data`, {
            statuses,
            conditions: conditionPayload(),
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingScheduler.value = false
    }
}

async function saveDays() {
    savingDays.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/cron-days`, days)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingDays.value = false
    }
}
</script>
