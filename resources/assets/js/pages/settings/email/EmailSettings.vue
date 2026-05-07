<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Email Settings</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mail Driver</label>
                                <select class="form-select" v-model="form.driver">
                                    <option value="mail">PHP Mail</option>
                                    <option value="smtp">SMTP</option>
                                    <option value="mailgun">Mailgun</option>
                                    <option value="ses">Amazon SES</option>
                                    <option value="sparkpost">SparkPost</option>
                                    <option value="mandrill">Mandrill</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <TextField name="email" label="From Email" :value="form.email" :onChange="onChange" />
                        </div>
                        <div class="col-md-4">
                            <TextField name="from_name" label="From Name" :value="form.from_name" :onChange="onChange" />
                        </div>
                    </div>

                    <template v-if="form.driver === 'smtp'">
                        <div class="row">
                            <div class="col-md-4">
                                <TextField name="host" label="SMTP Host *" :value="form.host" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="port" label="SMTP Port *" :value="form.port" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Encryption</label>
                                    <select class="form-select" v-model="form.encryption">
                                        <option value="">None</option>
                                        <option value="ssl">SSL</option>
                                        <option value="tls">TLS</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <TextField name="username" label="Username" :value="form.username" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Password</label>
                                    <input type="password" class="form-control" v-model="form.password" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-if="['mailgun', 'mandrill', 'ses', 'sparkpost'].includes(form.driver)">
                        <div class="row">
                            <div class="col-md-4">
                                <TextField name="secret" label="Secret *" :value="form.secret" :onChange="onChange" />
                            </div>
                            <div v-if="form.driver === 'mailgun'" class="col-md-4">
                                <TextField name="domain" label="Domain *" :value="form.domain" :onChange="onChange" />
                            </div>
                            <div v-if="form.driver === 'ses'" class="col-md-4">
                                <TextField name="key" label="Key *" :value="form.key" :onChange="onChange" />
                            </div>
                            <div v-if="form.driver === 'ses'" class="col-md-4">
                                <TextField name="region" label="Region *" :value="form.region" :onChange="onChange" />
                            </div>
                        </div>
                    </template>
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

const COMPONENT = 'email-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    driver: 'mail', email: '', from_name: '', host: '', port: '',
    encryption: '', username: '', password: '', secret: '', domain: '', key: '', region: '',
})

function onChange(val, name) { form[name] = val }

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/email`)
        const d = res.data?.data ?? {}
        Object.assign(form, {
            driver: d.driver ?? 'mail', email: d.email ?? '', from_name: d.from_name ?? '',
            host: d.host ?? '', port: d.port ?? '', encryption: d.encryption ?? '',
            username: d.username ?? '', secret: d.secret ?? '', domain: d.domain ?? '',
            key: d.key ?? '', region: d.region ?? '',
        })
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/email`, form)
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
