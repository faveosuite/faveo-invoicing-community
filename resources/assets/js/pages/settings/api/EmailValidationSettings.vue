<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.email_validation_provider') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <SelectField
                                name="provider"
                                :label="__('message.validation-provider')"
                                :elements="providerOptions"
                                :value="providerOptions.find(o => o.id === form.provider) ?? null"
                                :onChange="(val) => form.provider = val?.id ?? ''"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField
                                name="apikey"
                                :label="__('message.emailApikey')"
                                :value="form.apikey"
                                placeholder="Enter your API key"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6">
                            <SelectField
                                name="mode"
                                :label="__('message.emailMode')"
                                :elements="modeOptions"
                                :value="modeOptions.find(o => o.id === form.mode) ?? null"
                                :onChange="(val) => form.mode = val?.id ?? ''"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                    </div>

                    <div v-if="form.mode === 'power'" class="mt-2">
                        <label class="form-label fw-bold">{{ __('message.allowed_estatus') }}</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <div
                                v-for="opt in statusOptions"
                                :key="opt.bit"
                                class="form-check"
                            >
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :id="`bit_${opt.bit}`"
                                    :value="opt.bit"
                                    v-model="selectedBits"
                                />
                                <label class="form-check-label" :for="`bit_${opt.bit}`">
                                    {{ opt.name }}
                                </label>
                            </div>
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
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'

const COMPONENT = 'email-validation-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading      = ref(true)
const saving       = ref(false)
const statusOptions = ref([])
const selectedBits  = ref([])

const providerOptions = [
    { id: 'reoon', name: __('message.reoon') },
]

const modeOptions = [
    { id: 'quick', name: 'Quick' },
    { id: 'power', name: 'Power' },
]

const form = reactive({
    provider: 'reoon',
    apikey:   '',
    mode:     'quick',
})

const acceptedOutput = computed(() =>
    selectedBits.value.reduce((acc, bit) => acc | bit, 0)
)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/email-validation`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            provider: d.provider ?? 'reoon',
            apikey:   d.api_key  ?? '',
            mode:     d.mode     ?? 'quick',
        })
        statusOptions.value = d.status_options ?? []
        selectedBits.value  = d.selected_bits  ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const payload = {
            provider: form.provider,
            apikey:   form.apikey,
            mode:     form.mode,
        }
        if (form.mode === 'power') payload.accepted_output = acceptedOutput.value

        const res = await http.post(`${baseUrl}/email-settings-save`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
