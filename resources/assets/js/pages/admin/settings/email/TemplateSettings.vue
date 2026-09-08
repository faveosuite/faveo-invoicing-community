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
                            <DynamicSelect
                                :name="String(type.id)"
                                :label="toLabel(type.name)"
                                :elements="templates"
                                :value="selected(type.id)"
                                :onChange="(val) => onSelect(type.id, val)"
                                :searchable="true"
                                :clearable="true"
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
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

const COMPONENT = 'template-settings'

const loading   = ref(true)
const saving    = ref(false)
const types     = ref([])
const templates = ref([])
const mappings  = reactive({})  // typeId → templateId (number | null)

const LABEL_OVERRIDES = {
    welcome_mail:                   __('message.welcome-mail'),
    forgot_password_mail:           __('message.forgot-password'), // NOSONAR
    subscription_going_to_end_mail: __('message.subscription-going-to-end'),
    subscription_over_mail:         __('message.subscription-over'),
    invoice_mail:                   __('message.invoice'),
    order_mail:                     __('message.order-mail'),
    auto_subscription_going_to_end: __('message.auto_renewal_reminder'),
    payment_successfull:            __('message.auto_payment_success'),
    payment_failed:                 __('message.auto_payment_failed'),
    cloud_deleted:                  __('message.urgent_order_deleted'),
    cloud_created:                  __('message.new_instance_created'),
    contact_us:                     __('message.contact_us'),
    demo_request:                   __('message.request_demo'),
    registration_mail:              __('message.register_mail'),
    sales_manager_email:            __('message.new_sales_manager'),
    account_manager_email:          __('message.new_account_manager'),
}

function toLabel(name) {
    return LABEL_OVERRIDES[name]
        ?? name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

// DynamicSelect expects the full object as value, not just the id
function selected(typeId) {
    const id = mappings[typeId]
    return id ? (templates.value.find(t => t.id === id) ?? null) : null
}

function onSelect(typeId, val) {
    mappings[typeId] = val?.id ?? null
}

onMounted(async () => {
    try {
        const res = await http.get(`/settings/template`)
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
        const res = await http.patch(`/settings/template`, { mappings })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
