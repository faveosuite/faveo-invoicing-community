<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_script_code') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <TextField
                                name="name"
                                :label="__('message.name') + ' *'"
                                :value="form.name"
                                :onChange="(val) => form.name = val"
                            />
                        </div>
                        <div class="col-md-4 mb-3">
                            <SelectField
                                name="on_registration"
                                :label="__('message.show_script') + ' *'"
                                :elements="showScriptOptions"
                                :value="showScriptOptions.find(o => o.id === form.on_registration) ?? null"
                                :onChange="(val) => form.on_registration = val?.id ?? 1"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4 mb-3">
                            <SelectField
                                name="google_analytics"
                                :label="__('message.google_analytics')"
                                :elements="yesNoOptions"
                                :value="yesNoOptions.find(o => o.id === form.google_analytics) ?? null"
                                :onChange="(val) => form.google_analytics = val?.id ?? 0"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div v-if="form.google_analytics" class="col-md-4 mb-3">
                            <TextField
                                name="google_analytics_tag"
                                :label="__('message.chat_google_analytics_tag') + ' *'"
                                :value="form.google_analytics_tag"
                                :onChange="(val) => form.google_analytics_tag = val"
                            />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('message.content') }} *</label>
                        <textarea
                            class="form-control font-monospace"
                            rows="10"
                            v-model="form.script"
                        ></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/settings/widgets/analytics" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'analytics-edit'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route   = useRoute()
const router  = useRouter()
const id      = route.params.id

const showScriptOptions = [
    { id: 1, name: __('message.on_registration') },
    { id: 0, name: __('message.on_every_page') },
]

const yesNoOptions = [
    { id: 1, name: __('message.yes') },
    { id: 0, name: __('message.no') },
]

const loading = ref(true)
const saving  = ref(false)
const form = reactive({
    name:                 '',
    on_registration:      1,
    google_analytics:     0,
    google_analytics_tag: '',
    script:               '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/chat/show/${id}`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            name:                 d.name ?? '',
            on_registration:      d.on_registration ?? 1,
            google_analytics:     d.google_analytics ? 1 : 0,
            google_analytics_tag: d.google_analytics_tag ?? '',
            script:               d.script ?? '',
        })
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/chat/update/${id}`, {
            name:                 form.name,
            on_registration:      form.on_registration,
            google_analytics:     form.google_analytics,
            google_analytics_tag: form.google_analytics ? form.google_analytics_tag : '',
            script:               form.script,
        })
        successHandler(res, COMPONENT)
        router.push('/settings/widgets/analytics')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
