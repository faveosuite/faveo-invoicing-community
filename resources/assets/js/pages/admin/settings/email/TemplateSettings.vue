<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.template_settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div v-for="type in types" :key="type.id" class="col-md-6">
                            <SelectField
                                :name="String(type.id)"
                                :label="toLabel(type.name)"
                                :elements="templates"
                                :value="selected(type.id)"
                                :onChange="(val) => onSelect(type.id, val)"
                                :searchable="true"
                                :clearable="true"
                                :placeholder="__('message.select_a_template')"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'

const COMPONENT = 'template-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading   = ref(true)
const saving    = ref(false)
const types     = ref([])
const templates = ref([])
const mappings  = reactive({})  // typeId → templateId (number | null)

const LABEL_OVERRIDES = {
    welcome_mail:                   'Welcome Mail',
    forgot_password_mail:           'Forgot Password',
    subscription_going_to_end_mail: 'Subscription Going To End',
    subscription_over_mail:         'Subscription Over',
    invoice_mail:                   'Invoice',
    order_mail:                     'Order Mail',
    auto_subscription_going_to_end: 'Auto Renewal Reminder',
    payment_successfull:            'Auto Payment Successfull',
    payment_failed:                 'Auto Payment Failed',
    cloud_deleted:                  'URGENT: Order has been deleted',
    cloud_created:                  'New instance created',
    contact_us:                     'Contact Us',
    demo_request:                   'Request a Demo',
    registration_mail:              'Register Mail',
    sales_manager_email:            'New Sales Manager',
    account_manager_email:          'New Account Manager',
}

function toLabel(name) {
    return LABEL_OVERRIDES[name]
        ?? name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

// SelectField expects the full object as value, not just the id
function selected(typeId) {
    const id = mappings[typeId]
    return id ? (templates.value.find(t => t.id === id) ?? null) : null
}

function onSelect(typeId, val) {
    mappings[typeId] = val?.id ?? null
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/template`)
        const data = res.data?.data ?? {}
        types.value     = data.types     ?? []
        templates.value = data.templates ?? []
        types.value.forEach(t => { mappings[t.id] = t.selected_template_id ?? null })
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/template`, { mappings })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
