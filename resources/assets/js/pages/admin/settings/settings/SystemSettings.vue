<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.system-settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="timezone_id"
                                :label="__('message.timezone')"
                                :required="true"
                                :elements="timezoneOptions"
                                :value="form.timezone_id"
                                :onChange="onChange"
                                :searchable="true"
                                :placeholder="__('message.choose')"
                                :error="errors.timezone_id"
                            />
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">{{ __('message.date_format') }}<span class="text-danger ms-1">*</span></label>
                                <small v-if="datePreview" class="text-muted fst-italic">(e.g {{ datePreview }})</small>
                            </div>
                            <SelectField
                                name="date_format"
                                label=""
                                :required="false"
                                :elements="dateFormatOptions"
                                :value="form.date_format"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                                :error="errors.date_format"
                            />
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">{{ __('message.time_format') }}<span class="text-danger ms-1">*</span></label>
                                <small v-if="timePreview" class="text-muted fst-italic">(e.g {{ timePreview }})</small>
                            </div>
                            <SelectField
                                name="time_format"
                                label=""
                                :required="false"
                                :elements="timeFormatOptions"
                                :value="form.time_format"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                                :error="errors.time_format"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import { DateTime } from 'luxon'
import { phpToLuxon } from '@/helpers/luxonHelpers'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'

const COMPONENT = 'system-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { errors, setFieldError } = useForm()

const loading = ref(true)
const saving  = ref(false)

const timezoneOptions   = ref([])
const dateFormatOptions = ref([])
const timeFormatOptions = ref([])

const form = reactive({
    timezone_id:  null,
    date_format:  null,
    time_format:  null,
})

const datePreview = computed(() => {
    if (!form.date_format?.id) return ''
    try { return DateTime.now().toFormat(phpToLuxon(form.date_format.id)) } catch { return '' }
})

const timePreview = computed(() => {
    if (!form.time_format?.id) return ''
    try { return DateTime.now().toFormat(phpToLuxon(form.time_format.id)) } catch { return '' }
})


onMounted(async () => {
    try {
        const res  = await http.get(`${baseUrl}/settings/system-data`)
        const data = res.data?.data ?? {}
        const s    = data.settings ?? {}

        timezoneOptions.value   = (data.timezones    ?? []).map(t => ({ id: t.id,    name: t.location || t.name }))
        dateFormatOptions.value = (data.date_formats ?? []).map(f => ({ id: f.value, name: f.label }))
        timeFormatOptions.value = (data.time_formats ?? []).map(f => ({ id: f.value, name: f.label }))

        form.timezone_id = timezoneOptions.value.find(t => t.id === s.timezone_id)   ?? null
        form.date_format = dateFormatOptions.value.find(f => f.id === s.date_format) ?? null
        form.time_format = timeFormatOptions.value.find(f => f.id === s.time_format) ?? null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

async function save() {
    if (!form.timezone_id || !form.date_format || !form.time_format) {
        if (!form.timezone_id) setFieldError('timezone_id', __('message.field_required'))
        if (!form.date_format) setFieldError('date_format', __('message.field_required'))
        if (!form.time_format) setFieldError('time_format', __('message.field_required'))
        return
    }

    saving.value = true
    try {
        const fd = new FormData()
        fd.append('timezone_id',  form.timezone_id?.id ?? '')
        fd.append('date_format',  form.date_format?.id ?? '')
        fd.append('time_format',  form.time_format?.id ?? '')
        fd.append('_method', 'PATCH')

        const res = await http.post(`${baseUrl}/settings/datetime-data`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
