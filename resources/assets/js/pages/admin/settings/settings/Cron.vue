<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.cron-setting') }}</h3>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <!-- Cron Command Section -->
                    <p class="text-muted mb-3">{{ __('message.copy-cron-command-description') }}</p>

                    <div class="card p-3 bg-light mb-4">
                        <div class="row align-items-center g-2">
                            <div class="col-sm-2">
                                <span class="fs-5 text-muted fw-bold">* &nbsp;* &nbsp;* &nbsp;* &nbsp;*</span>
                            </div>
                            <div class="col-sm-4">
                                <SelectField
                                    v-if="phpPath !== 'other'"
                                    name="php_path"
                                    label=""
                                    :elements="phpPathOptions"
                                    :value="phpPathOptions.find(o => o.id === phpPath) ?? null"
                                    :onChange="(val) => phpPath = val?.id ?? ''"
                                    :clearable="false"
                                    :searchable="false"
                                    :placeholder="__('message.specify_php_executable')"
                                />
                                <div v-else class="input-group">
                                    <input type="text" class="form-control" v-model="customPhpPath"
                                           :placeholder="__('message.specify_php_executable')" />
                                    <button class="btn btn-outline-secondary" type="button" @click="clearPhpPath">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <code class="text-break" style="color: inherit;">{{ cronCommand }}</code>
                            </div>
                            <div class="col-sm-1 text-center">
                                <span v-if="!copying" style="cursor:pointer"
                                      :title="__('message.verify_and_copy_command')"
                                      @click="copyCommand">
                                    <i class="far fa-clipboard fa-2x text-secondary"></i>
                                </span>
                                <span v-else>
                                    <i class="fas fa-circle-notch fa-spin fa-2x text-secondary"></i>
                                </span>
                            </div>
                        </div>
                        <small v-if="!execEnabled" class="text-warning mt-2 d-block">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ __('message.please_enable_php_exec_for_cronjob_check') }}
                        </small>
                    </div>

                    <!-- Job List -->
                    <div class="row">
                        <div v-for="job in jobs" :key="job.key" class="col-md-6">
                            <div class="info-box">

                                <span class="info-box-icon bg-info">
                                    <i :class="job.icon"></i>
                                </span>

                                <div class="info-box-content">
                                    <div class="row align-items-center">

                                        <div class="col-md-7">
                                            <div class="form-group mb-0 d-flex align-items-center gap-2">
                                                <Checkbox :name="job.status" :value="!!statuses[job.status]" :label="job.label" :onChange="(val) => statuses[job.status] = val" />
                                                <Tooltip :message="job.info" />
                                            </div>
                                        </div>

                                        <div v-if="statuses[job.status]" class="col-md-5">
                                            <div class="row">
                                                <div :class="conditionForms[job.key].condition === 'dailyAt' ? 'col-sm-6' : 'col-sm-12'">
                                                    <SelectField
                                                        :name="'schedule_' + job.key"
                                                        label=""
                                                        :elements="commandOptions"
                                                        :value="selectedOption(job.key)"
                                                        :onChange="(val) => onScheduleChange(job.key, val)"
                                                        :clearable="false"
                                                        :searchable="false"
                                                    />
                                                </div>
                                                <div v-if="conditionForms[job.key].condition === 'dailyAt'" class="col-sm-6">
                                                    <TextField
                                                        :name="'dailyat_' + job.key"
                                                        label=""
                                                        placeholder="H:i"
                                                        :value="conditionForms[job.key].at"
                                                        :onChange="(val) => conditionForms[job.key].at = val"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" type="button" :loading="savingScheduler" @click="saveScheduler" />
                </div>
            </template>
        </div>

        <!-- Cron Days Section -->
        <div v-if="!loading" class="card card-light mt-3">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.cron_days') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <SelectField
                            name="expiryday"
                            :label="__('message.expiry_mail_days')"
                            :elements="expiryElements.filter(o => !days.expiryday.includes(o.id))"
                            :value="expiryElements.filter(o => days.expiryday.includes(o.id))"
                            :onChange="(val) => days.expiryday = (val || []).map(o => o.id)"
                            :multiple="true"
                            :closeOnSelect="false"
                            :clearable="false"
                            :disabled="!statuses.expiry_cron"
                        />
                    </div>
                    <div class="col-md-4">
                        <SelectField
                            name="subexpiryday"
                            :label="__('message.auto_renewal_days')"
                            :elements="expiryElements.filter(o => !days.subexpiryday.includes(o.id))"
                            :value="expiryElements.filter(o => days.subexpiryday.includes(o.id))"
                            :onChange="(val) => days.subexpiryday = (val || []).map(o => o.id)"
                            :multiple="true"
                            :closeOnSelect="false"
                            :clearable="false"
                            :disabled="!statuses.subs_expirymail"
                        />
                    </div>
                    <div class="col-md-4">
                        <SelectField
                            name="postsubexpiry_days"
                            :label="__('message.post_expiry_days')"
                            :elements="expiryElements.filter(o => !days.postsubexpiry_days.includes(o.id))"
                            :value="expiryElements.filter(o => days.postsubexpiry_days.includes(o.id))"
                            :onChange="(val) => days.postsubexpiry_days = (val || []).map(o => o.id)"
                            :multiple="true"
                            :closeOnSelect="false"
                            :clearable="false"
                            :disabled="!statuses.postsubs_expirymail"
                        />
                    </div>
                    <div v-for="field in dayFields" :key="field.key" class="col-md-4">
                        <SelectField
                            :name="field.key"
                            :label="field.label"
                            :elements="field.elements"
                            :value="field.elements.find(o => o.id === days[field.key]) ?? null"
                            :onChange="(val) => days[field.key] = val?.id ?? ''"
                            :clearable="false"
                            :disabled="!statuses[field.jobStatus]"
                        />
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <action-button action="save" type="button" :loading="savingDays" @click="saveDays" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { __ } from '@/plugins/i18n'

const COMPONENT = 'cron-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading         = ref(true)
const savingScheduler = ref(false)
const savingDays      = ref(false)
const copying         = ref(false)
const cronPath        = ref('')
const phpPath         = ref('')
const phpPaths        = ref([])
const customPhpPath   = ref('')
const execEnabled     = ref(false)

const phpPathOptions = computed(() => [
    ...phpPaths.value.map(p => ({ id: p, name: p })),
    { id: 'other', name: __('message.other') },
])

const cronCommand = computed(() => {
    const path = phpPath.value === 'other' ? customPhpPath.value : phpPath.value
    return path
        ? `${path} -q ${cronPath.value} schedule:run 2>&1`
        : `${cronPath.value} schedule:run 2>&1`
})

const commandOptions = [
    { id: 'everyMinute',        name: __('message.every_minute') },
    { id: 'everyFiveMinutes',   name: __('message.every_five_minutes') },
    { id: 'everyTenMinutes',    name: __('message.every_ten_minutes') },
    { id: 'everyThirtyMinutes', name: __('message.every_thirty_minutes') },
    { id: 'hourly',             name: __('message.hourly') },
    { id: 'daily',              name: __('message.daily') },
    { id: 'dailyAt',            name: __('message.daily_at') },
    { id: 'weekly',             name: __('message.weekly') },
    { id: 'monthly',            name: __('message.monthly') },
    { id: 'yearly',             name: __('message.yearly') },
]

const jobs = [
    { key: 'expiryMail',                  status: 'expiry_cron',            label: __('message.expiry_mail'),                              icon: 'fas fa-envelope',      info: __('message.expiry_mail_tooltip') },
    { key: 'deleteLogs',                  status: 'activity',                label: __('message.delete_activity'),                          icon: 'fas fa-trash-alt',     info: __('message.cron_trigger_deletion_activity_logs') },
    { key: 'subsExpirymail',              status: 'subs_expirymail',         label: __('message.subscription_renewal_reminder_autopayment'), icon: 'fas fa-bell',          info: __('message.auto_renewal_reminder_tooltip') },
    { key: 'postExpirymail',              status: 'postsubs_expirymail',     label: __('message.subscription_expired'),                     icon: 'fas fa-paper-plane',   info: __('message.auto_renewal_reminder_tooltip') },
    { key: 'cloud',                       status: 'cloud_cron',              label: __('message.cloud_subscription_deletion'),              icon: 'fas fa-cloud',         info: __('message.cron_trigger_cloud_new') },
    { key: 'invoice',                     status: 'invoice_cron',            label: __('message.invoice_deletion'),                         icon: 'fas fa-file-invoice',  info: __('message.cron_trigger_deletion_old') },
    { key: 'msg91Reports',                status: 'msg91_cron',              label: __('message.msg91_reports_deletion'),                   icon: 'fas fa-comments',      info: __('message.cron_trigger_deletion_msg91_reports') },
    { key: 'reoon',                       status: 'reoon_cron',              label: __('message.reoon_logs_deletion'),                      icon: 'fas fa-shield-alt',    info: __('message.cron_trigger_deletion_reoon_logs') },
    { key: 'systemLogs',                  status: 'systemlogs_cron',         label: __('message.system_logs_deletion'),                     icon: 'fas fa-cog',           info: __('message.cron_trigger_deletion_system_logs') },
    { key: 'installationLogs',            status: 'installationlogs_cron',   label: __('message.installation_logs_cleanup'),                icon: 'fas fa-download',      info: __('message.cron_trigger_deletion_installation_logs') },
    { key: 'licenseReportsCleanup',       status: 'licensereports_cron',     label: __('message.license_reports_cleanup'),                  icon: 'fas fa-key',           info: __('message.cron_trigger_deletion_license_reports') },
    { key: 'licenseCallbacksCleanup',     status: 'licensecallbacks_cron',   label: __('message.license_callbacks_cleanup'),                icon: 'fas fa-exchange-alt',  info: __('message.cron_trigger_deletion_license_callbacks') },
    { key: 'licenseCrackReportsCleanup',  status: 'licensecrack_cron',       label: __('message.license_crack_reports_cleanup'),            icon: 'fas fa-bug',           info: __('message.cron_trigger_deletion_license_crack') },
    { key: 'licenseSystemReportsCleanup', status: 'licensesystem_cron',      label: __('message.license_system_reports_cleanup'),           icon: 'fas fa-server',        info: __('message.cron_trigger_deletion_license_system') },
    { key: 'licenseVersionsCleanup',      status: 'licenseversions_cron',    label: __('message.license_versions_cleanup'),                 icon: 'fas fa-code-branch',   info: __('message.cron_trigger_deletion_license_versions') },
]

const statuses       = reactive({})
const conditionForms = reactive({})
const days = reactive({
    expiryday: [], subexpiryday: [], postsubexpiry_days: [],
    cloud_days: '30', invoice_days: '7', msg91_days: '30', reoon_days: '30',
    system_logs_days: '30', installation_logs_days: '30', license_reports_days: '30',
    license_callbacks_days: '30', license_crack_days: '30', license_system_days: '30',
    license_versions_days: '30', logdelday: '30',
})

function makeOptions(values, deleteLabel = '') {
    return values.map(v => ({
        id: v,
        name: v === '0' && deleteLabel ? deleteLabel : `${v} days`,
    }))
}

const expiryElements  = makeOptions(['30', '15', '7', '1'])
const licenseElements = makeOptions(['720', '365', '180', '90', '30', '15', '7', '1'])

const dayFields = [
    { key: 'cloud_days',             label: __('message.cloud_mail_days'),             elements: expiryElements,                                                                                                    jobStatus: 'cloud_cron' },
    { key: 'invoice_days',           label: __('message.invoice_deletion_days'),       elements: makeOptions(['7', '5', '2', '1']),                                                                                 jobStatus: 'invoice_cron' },
    { key: 'msg91_days',             label: __('message.msg91_report_days'),           elements: makeOptions(['720', '365', '180', '150', '60', '30', '15', '5', '2', '0'], __('message.delete_all_reports')),     jobStatus: 'msg91_cron' },
    { key: 'reoon_days',             label: __('message.reoon_log_days'),              elements: makeOptions(['30', '15', '10', '5', '1']),                                                                         jobStatus: 'reoon_cron' },
    { key: 'system_logs_days',       label: __('message.system_log_days'),             elements: makeOptions(['720', '365', '180', '150', '60', '30', '15', '5', '2', '0'], __('message.delete_all_logs')),        jobStatus: 'systemlogs_cron' },
    { key: 'installation_logs_days', label: __('message.installation_log_days'),       elements: licenseElements,                                                                                                   jobStatus: 'installationlogs_cron' },
    { key: 'license_reports_days',   label: __('message.license_reports_days'),        elements: licenseElements,                                                                                                   jobStatus: 'licensereports_cron' },
    { key: 'license_callbacks_days', label: __('message.license_callbacks_days'),      elements: licenseElements,                                                                                                   jobStatus: 'licensecallbacks_cron' },
    { key: 'license_crack_days',     label: __('message.license_crack_days'),          elements: licenseElements,                                                                                                   jobStatus: 'licensecrack_cron' },
    { key: 'license_system_days',    label: __('message.license_system_days'),         elements: licenseElements,                                                                                                   jobStatus: 'licensesystem_cron' },
    { key: 'license_versions_days',  label: __('message.license_versions_days'),       elements: licenseElements,                                                                                                   jobStatus: 'licenseversions_cron' },
    { key: 'logdelday',              label: __('message.activity_log_days'),           elements: makeOptions(['720', '365', '180', '150', '60', '30', '15', '5', '2', '0'], __('message.delete_all_logs')),        jobStatus: 'activity' },
]

jobs.forEach(job => {
    statuses[job.status]    = false
    conditionForms[job.key] = { condition: 'daily', at: '' }
})

function selectedOption(jobKey) {
    return commandOptions.find(o => o.id === conditionForms[jobKey].condition) ?? null
}

function onScheduleChange(jobKey, val) {
    conditionForms[jobKey].condition = val?.id ?? 'daily'
}

onMounted(load)

async function load() {
    loading.value = true
    try {
        const res  = await http.get(`${baseUrl}/settings/cron-data`)
        const data = res.data?.data ?? {}

        cronPath.value    = data.cron_path ?? ''
        phpPaths.value    = data.php_paths ?? []
        phpPath.value     = phpPaths.value[0] ?? ''
        execEnabled.value = Boolean(data.exec_enabled)

        Object.assign(statuses, data.statuses ?? {})
        Object.assign(days,     data.days     ?? {})

        Object.entries(data.conditions ?? {}).forEach(([job, value]) => {
            const parts = String(value || 'daily').split(',')
            if (conditionForms[job]) {
                conditionForms[job].condition = parts[0] || 'daily'
                conditionForms[job].at        = parts[1] || ''
            }
        })
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

function clearPhpPath() {
    phpPath.value       = ''
    customPhpPath.value = ''
}

async function copyCommand() {
    const path = phpPath.value === 'other' ? customPhpPath.value : phpPath.value
    copying.value = true
    try {
        const res = await http.post(`${baseUrl}/verify-php-path`, { path })
        await navigator.clipboard.writeText(`* * * * * ${cronCommand.value}`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        copying.value = false
    }
}

function conditionPayload() {
    return jobs.reduce((payload, job) => {
        const form = conditionForms[job.key]
        payload[job.key] = form.condition === 'dailyAt'
            ? `dailyAt,${form.at || '00:00'}`
            : form.condition
        return payload
    }, {})
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

<style scoped>
.mt-13 { margin-top: 13px; }
.info-box-icon { color: #ffffff !important; }
</style>
