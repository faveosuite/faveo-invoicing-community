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
                                <StaticSelect
                                    v-if="phpPath !== 'other'"
                                    name="php_path"
                                    label=""
                                    :elements="phpPathOptions"
                                    :value="phpPath"
                                    :onChange="(val) => phpPath = val"
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
                                <code class="text-break code-inherit">{{ cronCommand }}</code>
                            </div>
                            <div class="col-sm-1 text-center">
                                <span v-if="!copying" class="clickable"
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
                                                <Checkbox :name="job.key" :value="!!statuses[job.key]" :label="job.label" :onChange="(val) => statuses[job.key] = val" />
                                                <Tooltip :message="job.info" />
                                            </div>
                                        </div>

                                        <div v-if="statuses[job.key]" class="col-md-5">
                                            <div class="row">
                                                <div :class="conditionForms[job.key].condition === 'dailyAt' ? 'col-sm-6' : 'col-sm-12'">
                                                    <StaticSelect
                                                        :name="'schedule_' + job.key"
                                                        label=""
                                                        :elements="commandOptions"
                                                        :value="conditionForms[job.key].condition"
                                                        :onChange="(val) => onScheduleChange(job.key, val)"
                                                        :hideEmptySelect="true"
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
                        <DynamicSelect
                            name="expiryMail"
                            :label="__('message.expiry_mail_days')"
                            :tooltip="__('message.expiry_mail_days_tooltip')"
                            :elements="expiryElements.filter(o => !days.expiryMail.includes(o.id))"
                            :value="expiryElements.filter(o => days.expiryMail.includes(o.id))"
                            :onChange="(val) => days.expiryMail = (val || []).map(o => o.id)"
                            :multiple="true"
                            :closeOnSelect="false"
                            :clearable="false"
                            :disabled="!statuses.expiryMail"
                        />
                    </div>
                    <div class="col-md-4">
                        <DynamicSelect
                            name="subsExpirymail"
                            :label="__('message.auto_renewal_days')"
                            :tooltip="__('message.auto_renewal_days_tooltip')"
                            :elements="expiryElements.filter(o => !days.subsExpirymail.includes(o.id))"
                            :value="expiryElements.filter(o => days.subsExpirymail.includes(o.id))"
                            :onChange="(val) => days.subsExpirymail = (val || []).map(o => o.id)"
                            :multiple="true"
                            :closeOnSelect="false"
                            :clearable="false"
                            :disabled="!statuses.subsExpirymail"
                        />
                    </div>
                    <div class="col-md-4">
                        <DynamicSelect
                            name="postExpirymail"
                            :label="__('message.post_expiry_days')"
                            :tooltip="__('message.post_expiry_days_tooltip')"
                            :elements="expiryElements.filter(o => !days.postExpirymail.includes(o.id))"
                            :value="expiryElements.filter(o => days.postExpirymail.includes(o.id))"
                            :onChange="(val) => days.postExpirymail = (val || []).map(o => o.id)"
                            :multiple="true"
                            :closeOnSelect="false"
                            :clearable="false"
                            :disabled="!statuses.postExpirymail"
                        />
                    </div>
                    <div v-for="field in dayFields" :key="field.key" class="col-md-4">
                        <DynamicSelect
                            :name="field.key"
                            :label="field.label"
                            :tooltip="field.tooltip"
                            :elements="field.elements"
                            :value="field.elements.find(o => o.id === days[field.key]) ?? null"
                            :onChange="(val) => days[field.key] = val?.id ?? ''"
                            :clearable="false"
                            :disabled="!statuses[field.key]"
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
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { __ } from '@/plugins/i18n'

const COMPONENT = 'cron-settings'

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
    { key: 'expiryMail',                  label: __('message.expiry_mail'),                              icon: 'fas fa-envelope',      info: __('message.expiry_mail_tooltip') },
    { key: 'deleteLogs',                  label: __('message.delete_activity'),                          icon: 'fas fa-trash-alt',     info: __('message.cron_trigger_deletion_activity_logs') },
    { key: 'subsExpirymail',              label: __('message.subscription_renewal_reminder_autopayment'), icon: 'fas fa-bell',          info: __('message.auto_renewal_reminder_tooltip') },
    { key: 'postExpirymail',              label: __('message.subscription_expired'),                     icon: 'fas fa-paper-plane',   info: __('message.post_expiry_notification_tooltip') },
    { key: 'cloud',                       label: __('message.cloud_subscription_deletion'),              icon: 'fas fa-cloud',         info: __('message.cron_trigger_cloud_new') },
    { key: 'invoice',                     label: __('message.invoice_deletion'),                         icon: 'fas fa-file-invoice',  info: __('message.cron_trigger_deletion_old') },
    { key: 'msg91Reports',                label: __('message.msg91_reports_deletion'),                   icon: 'fas fa-comments',      info: __('message.cron_trigger_deletion_msg91_reports') },
    { key: 'reoon',                       label: __('message.reoon_logs_deletion'),                      icon: 'fas fa-shield-alt',    info: __('message.cron_trigger_deletion_reoon_logs') },
    { key: 'systemLogs',                  label: __('message.system_logs_deletion'),                     icon: 'fas fa-cog',           info: __('message.cron_trigger_deletion_system_logs') },
    { key: 'installationLogs',            label: __('message.installation_logs_cleanup'),                icon: 'fas fa-download',      info: __('message.cron_trigger_deletion_installation_logs') },
    { key: 'licenseReportsCleanup',       label: __('message.license_reports_cleanup'),                  icon: 'fas fa-key',           info: __('message.cron_trigger_deletion_license_reports') },
    { key: 'licenseCallbacksCleanup',     label: __('message.license_callbacks_cleanup'),                icon: 'fas fa-exchange-alt',  info: __('message.cron_trigger_deletion_license_callbacks') },
    { key: 'licenseCrackReportsCleanup',  label: __('message.license_crack_reports_cleanup'),            icon: 'fas fa-bug',           info: __('message.cron_trigger_deletion_license_crack') },
    { key: 'licenseSystemReportsCleanup', label: __('message.license_system_reports_cleanup'),           icon: 'fas fa-server',        info: __('message.cron_trigger_deletion_license_system') },
    { key: 'licenseVersionsCleanup',      label: __('message.license_versions_cleanup'),                 icon: 'fas fa-code-branch',   info: __('message.cron_trigger_deletion_license_versions') },
]

const statuses       = reactive({})
const conditionForms = reactive({})
// Each task's days, keyed by job: a list for reminders, one number for cleanups (0 = delete all).
const days = reactive({
    expiryMail: [], subsExpirymail: [], postExpirymail: [],
    cloud: 30, invoice: 7, msg91Reports: 30, reoon: 30,
    systemLogs: 30, installationLogs: 30, licenseReportsCleanup: 30,
    licenseCallbacksCleanup: 30, licenseCrackReportsCleanup: 30, licenseSystemReportsCleanup: 30,
    licenseVersionsCleanup: 30, deleteLogs: 30,
})

function makeOptions(values, deleteLabel = '') {
    return values.map(v => ({
        id: v,
        name: v === 0 && deleteLabel ? deleteLabel : `${v} ${__('message.days')}`,
    }))
}

const expiryElements  = makeOptions([30, 15, 7, 1])
const licenseElements = makeOptions([720, 365, 180, 90, 30, 15, 7, 1])

const dayFields = [
    { key: 'cloud',                       label: __('message.cloud_mail_days'),        tooltip: __('message.cloud_mail_days_tooltip'),        elements: expiryElements },
    { key: 'invoice',                     label: __('message.invoice_deletion_days'),  tooltip: __('message.invoice_deletion_days_tooltip'),  elements: makeOptions([7, 5, 2, 1]) },
    { key: 'msg91Reports',                label: __('message.msg91_report_days'),      tooltip: __('message.msg91_report_days_tooltip'),      elements: makeOptions([720, 365, 180, 150, 60, 30, 15, 5, 2, 0], __('message.delete_all_reports')) },
    { key: 'reoon',                       label: __('message.reoon_log_days'),         tooltip: __('message.reoon_log_days_tooltip'),         elements: makeOptions([30, 15, 10, 5, 1]) },
    { key: 'systemLogs',                  label: __('message.system_log_days'),        tooltip: __('message.system_log_days_tooltip'),        elements: makeOptions([720, 365, 180, 150, 60, 30, 15, 5, 2, 0], __('message.delete_all_logs')) },
    { key: 'installationLogs',            label: __('message.installation_log_days'),  tooltip: __('message.installation_log_days_tooltip'),  elements: licenseElements },
    { key: 'licenseReportsCleanup',       label: __('message.license_reports_days'),   tooltip: __('message.license_reports_days_tooltip'),   elements: licenseElements },
    { key: 'licenseCallbacksCleanup',     label: __('message.license_callbacks_days'), tooltip: __('message.license_callbacks_days_tooltip'), elements: licenseElements },
    { key: 'licenseCrackReportsCleanup',  label: __('message.license_crack_days'),     tooltip: __('message.license_crack_days_tooltip'),     elements: licenseElements },
    { key: 'licenseSystemReportsCleanup', label: __('message.license_system_days'),    tooltip: __('message.license_system_days_tooltip'),    elements: licenseElements },
    { key: 'licenseVersionsCleanup',      label: __('message.license_versions_days'),  tooltip: __('message.license_versions_days_tooltip'),  elements: licenseElements },
    { key: 'deleteLogs',                  label: __('message.activity_log_days'),      tooltip: __('message.activity_log_days_tooltip'),      elements: makeOptions([720, 365, 180, 150, 60, 30, 15, 5, 2, 0], __('message.delete_all_logs')) },
]

jobs.forEach(job => {
    statuses[job.key]       = false
    conditionForms[job.key] = { condition: 'daily', at: '' }
})

function onScheduleChange(jobKey, val) {
    conditionForms[jobKey].condition = val || 'daily'
}

onMounted(load)

async function load() {
    loading.value = true
    try {
        const res  = await http.get(`/settings/cron-data`)
        const data = res.data?.data ?? {}

        cronPath.value    = data.cron_path ?? ''
        phpPaths.value    = data.php_paths ?? []
        phpPath.value     = phpPaths.value[0] ?? ''
        execEnabled.value = Boolean(data.exec_enabled)

        Object.entries(data.tasks ?? {}).forEach(([job, task]) => {
            if (!conditionForms[job]) return
            statuses[job]                 = Boolean(task.active)
            conditionForms[job].condition = task.condition || 'daily'
            conditionForms[job].at        = task.at || ''
            if (job in days && task.days !== null && task.days !== undefined) days[job] = task.days
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
        const res = await http.post(`/verify-php-path`, { path })
        await navigator.clipboard.writeText(`* * * * * ${cronCommand.value}`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        copying.value = false
    }
}

function tasksPayload() {
    return jobs.reduce((payload, job) => {
        const form = conditionForms[job.key]
        payload[job.key] = {
            active: Boolean(statuses[job.key]),
            value:  form.condition === 'dailyAt' ? `dailyAt,${form.at || '00:00'}` : form.condition,
        }
        return payload
    }, {})
}

async function saveScheduler() {
    savingScheduler.value = true
    try {
        const res = await http.patch(`/settings/cron-data`, { tasks: tasksPayload() })
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
        const res = await http.patch(`/settings/cron-days`, { days })
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
.code-inherit { color: inherit; }
.clickable { cursor: pointer; }
</style>
