<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.email_settings') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <SelectField
                                name="driver"
                                :label="__('message.mail_driver')"
                                :elements="driverOptions"
                                :value="form.driver"
                                :onChange="(val) => form.driver = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField name="email" :label="__('message.from_email')" :value="form.email" :onChange="onChange" />
                        </div>
                        <div class="col-md-4">
                            <TextField name="from_name" :label="__('message.from_name')" :value="form.from_name" :onChange="onChange" />
                        </div>
                    </div>

                    <template v-if="form.driver?.id === 'smtp'">
                        <div class="row">
                            <div class="col-md-4">
                                <TextField name="host" :label="__('message.smtp_host') + ' *'" :value="form.host" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <TextField name="port" :label="__('message.smtp_port') + ' *'" :value="form.port" :onChange="onChange" />
                            </div>
                            <div class="col-md-4">
                                <SelectField
                                    name="encryption"
                                    :label="__('message.encryption')"
                                    :elements="encryptionOptions"
                                    :value="form.encryption"
                                    :onChange="(val) => form.encryption = val"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('message.password') }}</label>
                                    <input type="password" class="form-control" v-model="form.password" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-if="['mailgun', 'mandrill', 'ses', 'sparkpost'].includes(form.driver?.id)">
                        <div class="row">
                            <div class="col-md-4">
                                <TextField name="secret" :label="__('message.secret') + ' *'" :value="form.secret" :onChange="onChange" />
                            </div>
                            <div v-if="form.driver?.id === 'mailgun'" class="col-md-4">
                                <TextField name="domain" :label="__('message.admin_domain') + ' *'" :value="form.domain" :onChange="onChange" />
                            </div>
                            <div v-if="form.driver?.id === 'ses'" class="col-md-4">
                                <TextField name="key" :label="__('message.db_key') + ' *'" :value="form.key" :onChange="onChange" />
                            </div>
                            <div v-if="form.driver?.id === 'ses'" class="col-md-4">
                                <TextField name="region" :label="__('message.region') + ' *'" :value="form.region" :onChange="onChange" />
                            </div>
                        </div>
                    </template>
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
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'email-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const driverOptions = [
    { id: 'smtp',      name: 'SMTP'       },
    { id: 'mail',      name: 'PHP Mail'   },
    { id: 'mailgun',   name: 'Mailgun'    },
    { id: 'mandrill',  name: 'Mandrill'   },
    { id: 'ses',       name: 'Amazon SES' },
    { id: 'sparkpost', name: 'SparkPost'  },
]

const encryptionOptions = [
    { id: 'ssl',      name: 'SSL'      },
    { id: 'tls',      name: 'TLS'      },
    { id: 'starttls', name: 'STARTTLS' },
]

const form = reactive({
    driver: null, email: '', from_name: '', host: '', port: '',
    encryption: null, password: '', secret: '', domain: '', key: '', region: '',
})

function onChange(val, name) { form[name] = val }

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/email`)
        const d = res.data?.data ?? {}
        Object.assign(form, {
            driver:     driverOptions.find(o => o.id === d.driver) ?? null,
            email:      d.email      ?? '',
            from_name:  d.from_name  ?? '',
            host:       d.host       ?? '',
            port:       d.port       ?? '',
            encryption: encryptionOptions.find(o => o.id === d.encryption) ?? null,
            password:   '',
            secret:     d.secret     ?? '',
            domain:     d.domain     ?? '',
            key:        d.key        ?? '',
            region:     d.region     ?? '',
        })
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const payload = {
            ...form,
            driver:     form.driver?.id     ?? '',
            encryption: form.encryption?.id ?? '',
        }
        const res = await http.patch(`${baseUrl}/settings/email`, payload)
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
