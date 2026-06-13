<template>
    <div>
        <AppBreadcrumb :items="breadcrumbs" />
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.new-payment') }}</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <DatePicker
                            name="payment_date"
                            :label="__('message.date-of-payment')"
                            :required="true"
                            :value="form.payment_date"
                            :onChange="(val) => { form.payment_date = val; setFieldError('payment_date', undefined) }"
                            :error="errors.payment_date"
                        />
                    </div>
                    <div class="col-md-4">
                        <SelectField
                            name="currency"
                            :label="__('message.currency')"
                            :required="true"
                            :elements="currencyOptions"
                            :value="selectedCurrency"
                            :onChange="onCurrencyChange"
                            :clearable="false"
                            :searchable="true"
                            :error="errors.currency"
                        />
                    </div>
                    <div class="col-md-4">
                        <SelectField
                            name="payment_method"
                            :label="__('message.payment-method')"
                            :required="true"
                            :elements="paymentMethods"
                            :value="selectedMethod"
                            :onChange="(val) => { form.payment_method = val?.value ?? ''; setFieldError('payment_method', undefined) }"
                            :clearable="false"
                            :error="errors.payment_method"
                        />
                    </div>
                    <div class="col-md-4">
                        <TextField
                            name="amount"
                            type="number"
                            :label="__('message.amount')"
                            :required="true"
                            :value="form.amount"
                            :onChange="(val) => { form.amount = val; distributeAmount() }"
                            :error="errors.amount"
                        />
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <AppButton>
                    <button type="button" class="btn btn-primary" :disabled="submitting" @click="submit">
                        <i class="fas fa-circle-notch fa-spin me-1" v-if="submitting"></i>
                        <i class="fas fa-save me-1" v-else></i>
                        {{ submitting ? __('message.please_wait') : __('message.save') }}
                    </button>
                </AppButton>
            </div>
        </div>

        <!-- Unpaid invoices in the selected currency -->
        <div class="card card-light mt-3">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.invoices') }}</h4>
            </div>
            <div class="card-body px-0 pt-0">
                <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
                <table v-else class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>{{ __('message.date') }}</th>
                            <th>{{ __('message.invoice_number') }}</th>
                            <th>{{ __('message.total') }}</th>
                            <th>{{ __('message.invoice_due') }}</th>
                            <th>{{ __('message.payment') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredInvoices.length">
                            <td colspan="6" class="text-center text-muted py-3">{{ __('message.no_invoices') }}</td>
                        </tr>
                        <tr v-for="inv in filteredInvoices" :key="inv.id">
                            <td class="text-center">
                                <input type="checkbox" v-model="inv.checked" @change="onCheck(inv)" />
                            </td>
                            <td>{{ inv.date }}</td>
                            <td>{{ inv.number }}</td>
                            <td>{{ symbol }}{{ inv.grand_total }}</td>
                            <td>{{ symbol }}{{ inv.pending }}</td>
                            <td>
                                <input
                                    type="number"
                                    class="form-control form-control-sm"
                                    style="width:120px;"
                                    v-model="inv.payAmount"
                                    :disabled="!inv.checked"
                                    min="0"
                                    :max="inv.pending"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <strong>{{ __('message.amount_to_credit') }} {{ symbol }}{{ amountToCredit }}</strong>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert'
import { __ } from '@/plugins/i18n'

const COMPONENT = 'user-payment-create'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route   = useRoute()
const router  = useRouter()
const userId  = route.params.id
const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const loading    = ref(true)
const submitting = ref(false)
const invoices   = ref([])
const currencies = ref([])

const form = ref({
    payment_date:   '',
    payment_method: '',
    amount:         '',
    currency:       '',
})

const paymentMethods = [
    { name: 'Cash',           value: 'cash' },
    { name: 'Check',          value: 'check' },
    { name: 'Online Payment', value: 'online payment' },
    { name: 'Razorpay',       value: 'razorpay' },
    { name: 'Stripe',         value: 'stripe' },
    { name: 'Credit Balance', value: 'Credit Balance' },
]

const selectedMethod = computed(() =>
    paymentMethods.find(m => m.value === form.value.payment_method) ?? null
)

// Currency dropdown is populated with the enabled (supported) currencies.
const currencyOptions = computed(() =>
    currencies.value.map(c => ({ name: `${c.name} (${c.code})`, value: c.code, symbol: c.symbol }))
)
const selectedCurrency = computed(() =>
    currencyOptions.value.find(c => c.value === form.value.currency) ?? null
)
const symbol = computed(() =>
    currencies.value.find(c => c.code === form.value.currency)?.symbol ?? ''
)

// Only invoices in the chosen currency are shown / payable.
const filteredInvoices = computed(() =>
    invoices.value.filter(inv => inv.currency === form.value.currency)
)

const breadcrumbs = [
    { label: __('message.home'),       to: '/dashboard' },
    { label: __('message.all-users'),  to: '/users' },
    { label: __('message.view_user'),  to: `/users/${userId}` },
    { label: __('message.new-payment') },
]

const amountToCredit = computed(() => {
    const total = parseFloat(form.value.amount) || 0
    const allocated = filteredInvoices.value
        .filter(i => i.checked)
        .reduce((s, i) => s + (parseFloat(i.payAmount) || 0), 0)
    return Math.max(0, total - allocated).toFixed(2)
})

function distributeAmount() {
    let remaining = parseFloat(form.value.amount) || 0
    filteredInvoices.value.forEach(inv => {
        if (inv.checked) {
            const alloc = Math.min(remaining, parseFloat(inv.pending) || 0)
            inv.payAmount = alloc > 0 ? alloc : ''
            remaining -= alloc > 0 ? alloc : 0
        }
    })
}

function onCheck(inv) {
    if (!inv.checked) inv.payAmount = ''
    distributeAmount()
}

// Switching currency changes which invoices apply, so clear any prior selection.
function onCurrencyChange(val) {
    form.value.currency = val?.value ?? ''
    setFieldError('currency', undefined)
    invoices.value.forEach(inv => { inv.checked = false; inv.payAmount = '' })
}

function validate() {
    const errs = {}
    if (!form.value.currency)       errs.currency       = __('message.select_currency') || 'Please select a currency.'
    if (!form.value.payment_date)   errs.payment_date   = __('message.payment_date_error')
    if (!form.value.payment_method) errs.payment_method = __('message.payment_method')
    if (!form.value.amount)         errs.amount         = __('message.payment_amount')
    setErrors(errs)
    return !Object.keys(errs).length
}

async function submit() {
    if (!validate()) return
    submitting.value = true
    alertStore.unsetAlert()

    const checked = filteredInvoices.value.filter(i => i.checked)
    const invoiceChecked = checked.map(i => i.id)
    const invoiceAmount  = checked.map(i => parseFloat(i.payAmount) || 0)

    try {
        await http.post(`${baseUrl}/newMultiplePayment/receive/${userId}`, {
            totalAmt:       form.value.amount,
            payment_date:   form.value.payment_date,
            payment_method: form.value.payment_method,
            currency:       form.value.currency,
            invoiceChecked,
            invoiceAmount,
            amtToCredit:    amountToCredit.value,
        })
        alertStore.setAlert({ message: __('message.payment_updated_succcessfully'), type: 'success', component_name: COMPONENT })
        router.push(`/users/${userId}`)
    } catch (err) {
        const res = err.response?.data
        if (res?.errors) {
            setErrors(Object.fromEntries(Object.entries(res.errors).map(([k, v]) => [k, v[0]])))
        } else {
            alertStore.setAlert({ message: res?.message || __('message.something_wrong'), type: 'danger', component_name: COMPONENT })
        }
    } finally {
        submitting.value = false
    }
}

onMounted(async () => {
    try {
        const { data } = await http.get(`${baseUrl}/newPayment/receive`, {
            params: { clientid: userId },
        })
        currencies.value = data.data.currencies ?? []
        invoices.value = (data.data.invoices ?? []).map(inv => ({
            ...inv,
            checked:   false,
            payAmount: '',
        }))
        // Default to the currency of the first unpaid invoice, else the first enabled currency.
        form.value.currency = invoices.value[0]?.currency
            ?? currencies.value[0]?.code
            ?? ''
    } catch {
        alertStore.setAlert({ message: __('message.something_wrong'), type: 'danger', component_name: COMPONENT })
    } finally {
        loading.value = false
    }
})
</script>
