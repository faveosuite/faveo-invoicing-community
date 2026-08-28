<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_script_code') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <TextField
                                name="name"
                                :label="__('message.name')"
                                :required="true"
                                :value="form.name"
                                :onChange="onChange"
                                :error="errors.name"
                            />
                        </div>
                        <div class="col-md-4 mb-3">
                            <DynamicSelect
                                name="on_registration"
                                :label="__('message.show_script')"
                                :required="true"
                                :elements="showScriptOptions"
                                :value="showScriptOptions.find(o => o.id === form.on_registration) ?? null"
                                :onChange="(val) => form.on_registration = val?.id ?? 1"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>
                        <div class="col-md-4 mb-3">
                            <DynamicSelect
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
                                :label="__('message.chat_google_analytics_tag')"
                                :required="true"
                                :value="form.google_analytics_tag"
                                :onChange="onChange"
                                :error="errors.google_analytics_tag"
                            />
                        </div>
                    </div>
                    <TextField
                        name="script"
                        :label="__('message.content')"
                        :required="true"
                        type="textarea"
                        :value="form.script"
                        :onChange="onChange"
                        :rows="10"
                        inputClass="font-monospace"
                        :error="errors.script"
                    />
                </div>
                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { buildAnalyticsSchema } from '@/validations/admin/widgetValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const COMPONENT = 'analytics-edit'
const route   = useRoute()
const router  = useRouter()
const id      = route.params.id

const { errors, setErrors, setFieldError } = useForm()

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

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`/chat/show/${id}`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            name:                 d.name ?? '',
            on_registration:      d.on_registration ?? 1,
            google_analytics:     d.google_analytics ? 1 : 0,
            google_analytics_tag: d.google_analytics_tag ?? '',
            script:               d.script ?? '',
        })
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

async function submit() {
    if (!await validateForm(buildAnalyticsSchema(!!form.google_analytics), form, setErrors)) return

    saving.value = true
    try {
        const res = await http.put(`/chat/update/${id}`, {
            name:                 form.name,
            on_registration:      form.on_registration,
            google_analytics:     form.google_analytics,
            google_analytics_tag: form.google_analytics ? form.google_analytics_tag : '',
            script:               form.script,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/widgets/analytics'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}
</script>
