<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filters') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <SelectField
                        name="filter_module"
                        :label="__('message.module')"
                        :elements="moduleOptions"
                        :multiple="true"
                        :closeOnSelect="false"
                        :searchable="true"
                        :value="form.modules"
                        :onChange="(val) => form.modules = val"
                    />
                </div>
                <div class="col-md-4">
                    <SelectField
                        name="filter_event"
                        :label="__('message.event')"
                        :elements="eventOptions"
                        :multiple="true"
                        :closeOnSelect="false"
                        :value="form.events"
                        :onChange="(val) => form.events = val"
                    />
                </div>
                <div class="col-md-4">
                    <SelectField
                        name="filter_performed_by"
                        :label="__('message.performed_by')"
                        :elements="userOptions"
                        :multiple="true"
                        :closeOnSelect="false"
                        :searchable="true"
                        :value="form.performedBy"
                        :onChange="(val) => form.performedBy = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="log_from"
                        :label="__('message.view_logs_from')"
                        :value="form.logFrom"
                        :clearable="true"
                        :onChange="(val) => form.logFrom = val"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="log_till"
                        :label="__('message.view_logs_till')"
                        :value="form.logTill"
                        :clearable="true"
                        :onChange="(val) => form.logTill = val"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="button" @click="apply">
                <i class="fas fa-check"></i>&nbsp;{{ __('message.apply') }}
            </button>
            <button class="btn btn-primary" type="button" @click="reset">
                <i class="fas fa-rotate-left"></i>&nbsp;{{ __('message.reset') }}
            </button>
            <button class="btn btn-secondary" type="button" @click="$emit('close')">
                <i class="fas fa-xmark"></i>&nbsp;{{ __('message.cancel') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'

const props = defineProps({
    show:    { type: Boolean, default: false },
    baseUrl: { type: String, default: '' },
})

const emit = defineEmits(['apply', 'reset', 'close'])

// ── options ───────────────────────────────────────────────────────────────────
const moduleOptions = ref([])
const userOptions   = ref([])

const eventOptions = [
    { id: 'created', name: 'Created' },
    { id: 'updated', name: 'Updated' },
    { id: 'deleted', name: 'Deleted' },
    { id: 'login',   name: 'Login'   },
]

async function loadOptions() {
    try {
        const res = await http.get(`${props.baseUrl}/get-activity-filters`)
        const data = res.data?.data ?? {}
        moduleOptions.value = (data.modules ?? []).map(m => ({ id: m, name: m }))
        userOptions.value   = data.users ?? []
    } catch (e) {
        errorHandler(e, 'activity-filter')
    }
}

// ── form ──────────────────────────────────────────────────────────────────────
const empty = () => ({
    modules:     [],
    events:      [],
    performedBy: [],
    logFrom:     null,
    logTill:     null,
})

const form = reactive(empty())

function apply() {
    const params = {}
    if (form.modules.length)      params.module       = form.modules.map(m => m.id ?? m)
    if (form.events.length)       params.event        = form.events.map(e => e.id ?? e)
    if (form.performedBy.length)  params.performed_by = form.performedBy.map(p => p.id ?? p)
    if (form.logFrom)             params.log_from     = form.logFrom
    if (form.logTill)             params.log_till     = form.logTill
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}

onMounted(() => loadOptions())
</script>
