<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.mailchimp') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="mailchimp_auth_key"
                                :label="__('message.mailchimp_key')"
                                :value="form.mailchimp_auth_key"
                                placeholder="Enter your Mailchimp API key"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>

                        <template v-if="lists.length > 0">
                            <div class="col-md-6 mb-3">
                                <SelectField
                                    name="list_id"
                                    :label="__('message.list_id')"
                                    :elements="listOptions"
                                    :value="selectedList"
                                    :onChange="(val) => form.list_id = val?.id ?? null"
                                    :searchable="true"
                                    :clearable="true"
                                />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('message.subscribe_status') }}</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="sub_1" :value="1" v-model="form.subscribe_status" />
                                        <label class="form-check-label" for="sub_1">{{ __('message.subscribe') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="sub_0" :value="0" v-model="form.subscribe_status" />
                                        <label class="form-check-label" for="sub_0">{{ __('message.unsubscribe') }}</label>
                                    </div>
                                </div>
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
import { reactive, ref, computed, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'

const COMPONENT = 'mailchimp-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)
const lists   = ref([])

const form = reactive({
    mailchimp_auth_key: '',
    list_id:            null,
    subscribe_status:   0,
})

const listOptions = computed(() => lists.value.map(l => ({ id: l.id, name: l.name })))
const selectedList = computed(() => listOptions.value.find(o => o.id === form.list_id) ?? null)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/mailchimp`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            mailchimp_auth_key: d.api_key          ?? '',
            list_id:            d.list_id          ?? null,
            subscribe_status:   d.subscribe_status ?? 0,
        })
        lists.value = d.lists ?? []
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
            mailchimp_auth_key: form.mailchimp_auth_key,
            status:             1,
        }
        if (form.list_id)            payload.list_id          = form.list_id
        if (form.subscribe_status !== undefined) payload.subscribe_status = form.subscribe_status

        const res = await http.post(`${baseUrl}/updateMailchimpDetails`, payload)
        const d   = res.data?.data ?? {}
        if (d.allLists) lists.value = d.allLists.map(l => ({ id: l.id, name: l.name }))
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
