<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.msg91_heading') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="msg91_auth_key"
                                :label="__('message.msg91_key')"
                                :value="form.msg91_auth_key"
                                placeholder="Enter your MSG91 Auth Key"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="msg91_sender"
                                :label="__('message.msg91_sender')"
                                :value="form.msg91_sender"
                                placeholder="Enter Sender ID"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="msg91_template_id"
                                :label="__('message.msg91_template_id')"
                                :value="form.msg91_template_id"
                                placeholder="Enter Template ID"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <SelectField
                                name="third_party_id"
                                :label="__('message.msg91_third_party_app_key')"
                                :elements="thirdPartyOptions"
                                :value="selectedApp"
                                :onChange="(val) => form.third_party_id = val?.id ?? null"
                                :searchable="true"
                                :clearable="true"
                                :placeholder="__('message.select_third_party_app')"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ __('message.save') }}
                    </button>
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
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'

const COMPONENT = 'msg91-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const thirdPartyApps = ref([])

const form = reactive({
    msg91_auth_key:    '',
    msg91_sender:      '',
    msg91_template_id: '',
    third_party_id:    null,
})

const thirdPartyOptions = computed(() =>
    thirdPartyApps.value.map(a => ({ id: a.id, name: a.app_name }))
)

const selectedApp = computed(() =>
    thirdPartyOptions.value.find(o => o.id === form.third_party_id) ?? null
)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/msg91`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            msg91_auth_key:    d.msg91_auth_key    ?? '',
            msg91_sender:      d.msg91_sender       ?? '',
            msg91_template_id: d.msg91_template_id  ?? '',
            third_party_id:    d.third_party_id     ?? null,
        })
        thirdPartyApps.value = d.third_party_apps ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/updatemobileDetails`, {
            msg91_auth_key:    form.msg91_auth_key,
            msg91_sender:      form.msg91_sender,
            msg91_template_id: form.msg91_template_id,
            thirdPartyId:      form.third_party_id,
            status:            1,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
