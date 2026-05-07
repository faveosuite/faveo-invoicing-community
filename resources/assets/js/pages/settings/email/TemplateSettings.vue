<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Template Settings</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <p class="text-muted mb-3">Select which email template to use for each notification type.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Forgot Password</label>
                            <select class="form-select" v-model="form.forgot_password">
                                <option value="">— None —</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Order Mail</label>
                            <select class="form-select" v-model="form.order_mail">
                                <option value="">— None —</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Welcome Mail</label>
                            <select class="form-select" v-model="form.welcome_mail">
                                <option value="">— None —</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Invoice Template</label>
                            <select class="form-select" v-model="form.invoice_template">
                                <option value="">— None —</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Save
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'template-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const templates = ref([])

const form = reactive({
    forgot_password: '', order_mail: '', welcome_mail: '', invoice_template: '',
})

onMounted(async () => {
    try {
        const [settingsRes, templatesRes] = await Promise.all([
            http.get(`${baseUrl}/settings/template`),
            http.get(`${baseUrl}/template/list`, { params: { limit: 200 } }),
        ])
        const d = settingsRes.data?.data ?? {}
        form.forgot_password  = d.forgot_password  ?? ''
        form.order_mail       = d.order_mail       ?? ''
        form.welcome_mail     = d.welcome_mail     ?? ''
        form.invoice_template = d.invoice_template ?? ''

        templates.value = templatesRes.data?.data?.data ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/template`, form)
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
