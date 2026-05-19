<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.mobile_provider') }}</h4>
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
                                :label="__('message.mobileApikey')"
                                :value="form.apikey"
                                placeholder="Enter your API key"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>

                        <template v-if="form.provider === 'vonage'">
                            <div class="col-md-6">
                                <TextField
                                    name="apisecret"
                                    :label="__('message.mobileApisecret')"
                                    type="password"
                                    :value="form.apisecret"
                                    placeholder="Enter your API secret"
                                    :onChange="(val, key) => form[key] = val"
                                />
                            </div>
                            <div class="col-md-6">
                                <SelectField
                                    name="mode"
                                    :label="__('message.mobileMode')"
                                    :elements="modeOptions"
                                    :value="modeOptions.find(o => o.id === form.mode) ?? null"
                                    :onChange="(val) => form.mode = val?.id ?? ''"
                                    :clearable="false"
                                    :searchable="false"
                                />
                            </div>
                        </template>
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
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'

const COMPONENT = 'mobile-validation-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const providerOptions = [
    { id: 'vonage',   name: __('message.vonage')   },
    { id: 'abstract', name: __('message.abstract') },
]

const modeOptions = [
    { id: 'basic',          name: 'Basic'    },
    { id: 'standard',       name: 'Standard' },
    { id: 'advanced/async', name: 'Advanced' },
]

const form = reactive({
    provider:  'vonage',
    apikey:    '',
    apisecret: '',
    mode:      'basic',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/mobile-validation`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            provider:  d.provider   ?? 'vonage',
            apikey:    d.api_key    ?? '',
            apisecret: d.api_secret ?? '',
            mode:      d.mode       ?? 'basic',
        })
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
        }
        if (form.provider === 'vonage') {
            payload.apisecret = form.apisecret
            payload.mode      = form.mode
        }
        const res = await http.post(`${baseUrl}/mobile-settings-save`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
