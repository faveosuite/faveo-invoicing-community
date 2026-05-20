<template>
    <div>
        <AppAlert componentName="invoices-edit" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_invoice') }}: #{{ invoice?.number }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <!-- Date -->
                            <div class="col-md-4">
                                <DatePicker
                                    name="date"
                                    :label="__('message.date')"
                                    :required="true"
                                    :value="form.date"
                                    :onChange="onChange"
                                    placeholder="MM/DD/YYYY"
                                />
                            </div>

                            <!-- Invoice Total -->
                            <div class="col-md-4">
                                <TextField
                                    name="total"
                                    :label="__('message.invoice-total')"
                                    :value="form.total"
                                    :onChange="onChange"
                                />
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <SelectField
                                    name="status"
                                    :label="__('message.status')"
                                    :elements="statusOptions"
                                    :value="statusOptions.find(o => o.id === form.status) ?? null"
                                    :onChange="(val) => { clearFieldError('status'); form.status = val?.id ?? '' }"
                                    :placeholder="__('message.choose')"
                                    :clearable="false"
                                    :searchable="false"
                                />
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <action-button action="update" type="button" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'
import { useAlertStore } from '@/core/stores/alert'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const COMPONENT = 'invoices-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route = useRoute()
const router = useRouter()
const invoiceId = route.params.id

const { validate, clearFieldError, clearAllErrors } = useFormValidation()
const validationErrors = computed(() => useAlertStore().validation_errors)

const statusOptions = [
    { id: 'success',        name: __('message.success') },
    { id: 'pending',        name: __('message.pending') },
    { id: 'Partially paid', name: __('message.partially_paid') },
]

const loading = ref(true)
const saving = ref(false)
const invoice = ref(null)

const form = reactive({
    date: null,
    total: '',
    status: '',
})

function onChange(val, name) {
    clearFieldError(name)
    form[name] = val
}

async function fetchInvoice() {
    try {
        const res = await http.get(`${baseUrl}/invoice/${invoiceId}`)
        const data = res.data?.data ?? res.data
        invoice.value = data.invoice

        if (data.invoice.date) {
            const dt = new Date(data.invoice.date)
            const mm = String(dt.getMonth() + 1).padStart(2, '0')
            const dd = String(dt.getDate()).padStart(2, '0')
            const yyyy = dt.getFullYear()
            form.date = `${mm}/${dd}/${yyyy}`
        }
        form.total = data.invoice.grand_total ?? ''
        form.status = data.invoice.status ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

async function submit() {
    const isValid = validate({
        date: [form.date, { isRequired: __('validation.invoice.date.required') }],
    })
    if (!isValid) return

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/invoice/edit/${invoiceId}`, form)
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/invoices'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    clearAllErrors()
    fetchInvoice()
})
</script>
