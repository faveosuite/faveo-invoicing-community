<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filters') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <TextField
                        name="filter_request_id"
                        :label="__('message.request_id')"
                        :value="form.requestId"
                        :onChange="(val) => form.requestId = val"
                        :placeholder="__('message.exact_request_id')"
                    />
                </div>
                <div class="col-md-4">
                    <TextField
                        name="filter_full_name"
                        :label="__('message.full_name')"
                        :value="form.fullName"
                        :onChange="(val) => form.fullName = val"
                        :placeholder="__('message.search_by_name')"
                    />
                </div>
                <div class="col-md-4">
                    <TextField
                        name="filter_email"
                        :label="__('message.email')"
                        :value="form.email"
                        :onChange="(val) => form.email = val"
                        :placeholder="__('message.search_by_email')"
                    />
                </div>
                <div class="col-md-4">
                    <TextField
                        name="filter_mobile_number"
                        :label="__('message.mobile_number')"
                        :value="form.mobileNumber"
                        :onChange="(val) => form.mobileNumber = val"
                        :placeholder="__('message.search_by_mobile_number')"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="filter_status"
                        :label="__('message.status')"
                        :elements="statusOptions"
                        :value="form.status"
                        :onChange="(val) => form.status = val"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="filter_source"
                        :label="__('message.source')"
                        :elements="sourceOptions"
                        :value="form.source"
                        :onChange="(val) => form.source = val"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="filter_action"
                        :label="__('message.action')"
                        :elements="actionOptions"
                        :value="form.action"
                        :onChange="(val) => form.action = val"
                    />
                </div>
                <div class="col-md-4">
                    <TextField
                        name="filter_failure_reason"
                        :label="__('message.failure_reason')"
                        :value="form.failureReason"
                        :onChange="(val) => form.failureReason = val"
                        :placeholder="__('message.search_by_failure_reason')"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="date_from"
                        :label="__('message.view_logs_from')"
                        :value="form.dateFrom"
                        :clearable="true"
                        :onChange="(val) => form.dateFrom = val"
                        :placeholder="__('message.select_date')"
                        :disabledDate="isFutureDate"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="date_to"
                        :label="__('message.view_logs_till')"
                        :value="form.dateTo"
                        :clearable="true"
                        :onChange="(val) => form.dateTo = val"
                        :placeholder="__('message.select_date')"
                        :disabledDate="isFutureDate"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <action-button action="apply" type="button" @click="apply" />
            <action-button action="reset" type="button" @click="reset" />
            <action-button action="cancel" type="button" @click="$emit('close')" />
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import { formatAction } from './msg91ActionLabel'

const props = defineProps({
    show:    { type: Boolean, default: false },
    baseUrl: { type: String, default: '' },
})

const emit = defineEmits(['apply', 'reset', 'close'])

// ── options ───────────────────────────────────────────────────────────────────
const statusOptions = ref([])
const sourceOptions = ref([])
const actionOptions = ref([])

async function loadOptions() {
    try {
        const res = await http.get(`${props.baseUrl}/getMsgFilters`)
        const data = res.data?.data ?? {}
        statusOptions.value = (data.statuses ?? []).map(s => ({ id: s, name: s }))
        sourceOptions.value = (data.sources  ?? []).map(s => ({ id: s, name: s }))
        actionOptions.value = (data.actions  ?? []).map(a => ({ id: a, name: formatAction(a) }))
    } catch (e) {
        errorHandler(e, 'msg91-filter')
    }
}

// ── form ──────────────────────────────────────────────────────────────────────
const empty = () => ({
    requestId:     '',
    fullName:      '',
    email:         '',
    mobileNumber:  '',
    status:        null,
    source:        null,
    action:        null,
    failureReason: '',
    dateFrom:      null,
    dateTo:        null,
})

const form = reactive(empty())

function apply() {
    const params = {}
    if (form.requestId.trim())     params.request_id     = form.requestId.trim()
    if (form.fullName.trim())      params.full_name      = form.fullName.trim()
    if (form.email.trim())         params.email          = form.email.trim()
    if (form.mobileNumber.trim())  params.mobile_number  = form.mobileNumber.trim()
    if (form.status?.id)           params.status         = form.status.id
    if (form.source?.id)           params.source         = form.source.id
    if (form.action?.id)           params.action         = form.action.id
    if (form.failureReason.trim()) params.failure_reason = form.failureReason.trim()
    if (form.dateFrom)             params.date_from      = form.dateFrom
    if (form.dateTo)               params.date_to        = form.dateTo
    emit('apply', params)
}

const isFutureDate = (date) => date > new Date()

function reset() {
    Object.assign(form, empty())
    emit('reset')
}

onMounted(() => loadOptions())
</script>
