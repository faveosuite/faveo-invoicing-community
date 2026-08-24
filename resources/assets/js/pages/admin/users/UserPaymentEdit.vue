<template>
    <div>
        <AppBreadcrumb :items="breadcrumbs" />
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.apply_payment_to_invoices') }}</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <TextField
                            name="payment_amount"
                            :label="__('message.amount')"
                            :value="`${symbol}${money(payment.amount)}`"
                            :disabled="true"
                            :onChange="() => {}"
                        />
                    </div>
                    <div class="col-md-3">
                        <TextField
                            name="payment_method"
                            :label="__('message.payment-method')"
                            :value="payment.payment_method ?? ''"
                            :disabled="true"
                            :onChange="() => {}"
                        />
                    </div>
                    <div class="col-md-3">
                        <DatePicker
                            name="payment_date"
                            :label="__('message.date-of-payment')"
                            :required="true"
                            :value="form.payment_date"
                            :onChange="(val) => { form.payment_date = val; setFieldError('payment_date', undefined) }"
                            :error="errors.payment_date"
                        />
                    </div>
                    <div class="col-md-3">
                        <TextField
                            name="unapplied"
                            :label="__('message.unapplied_balance')"
                            :value="`${symbol}${money(unapplied)}`"
                            :disabled="true"
                            :onChange="() => {}"
                        />
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <AppButton>
                    <button type="button" class="btn btn-primary" :disabled="submitting || !canSubmit" @click="submit">
                        <i class="fas fa-circle-notch fa-spin me-1" v-if="submitting"></i>
                        <i class="fas fa-save me-1" v-else></i>
                        {{ submitting ? __('message.please_wait') : __('message.save') }}
                    </button>
                </AppButton>
            </div>
        </div>

        <!-- Pending invoices -->
        <div class="card card-light mt-3">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.invoices') }}</h4>
            </div>
            <div class="card-body px-0 pt-0">
                <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
                <table v-else class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="th-checkbox"></th>
                            <th>{{ __('message.date') }}</th>
                            <th>{{ __('message.invoice_number') }}</th>
                            <th>{{ __('message.total') }}</th>
                            <th>{{ __('message.invoice_due') }}</th>
                            <th>{{ __('message.amount_applied') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!invoices.length">
                            <td colspan="6" class="text-center text-muted py-3">{{ __('message.no_invoices') }}</td>
                        </tr>
                        <tr v-for="inv in invoices" :key="inv.id">
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
                                    class="form-control form-control-sm input-amount"
                                    v-model="inv.payAmount"
                                    :disabled="!inv.checked"
                                    min="0"
                                    :max="inv.pending"
                                    @input="clampRow(inv)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <strong>{{ __('message.amount_applied') }}: {{ symbol }}{{ totalApplied }}</strong>
                <span class="ms-3 text-muted">{{ __('message.unapplied_balance') }}: {{ symbol }}{{ remaining }}</span>
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

const COMPONENT = 'user-payment-edit'

const route   = useRoute()
const router  = useRouter()
const userId    = route.params.id
const paymentId = route.params.paymentId
const alertStore = useAlertStore()
const { errors, setErrors, setFieldError } = useForm()

const loading    = ref(true)
const submitting = ref(false)
const invoices   = ref([])
const symbol     = ref('')
const unapplied = ref(0)
const payment   = ref({})

// Only the allocation date is editable — the payment's own amount and method
// are facts about money that already arrived, not fields to re-type here.
const form = ref({
    payment_date: '',
})

const breadcrumbs = [
    { label: __('message.home'),       to: '/dashboard' },
    { label: __('message.all-users'),  to: '/users' },
    { label: __('message.view_user'),  to: `/users/${userId}` },
    { label: __('message.apply_payment_to_invoices') },
]

// Display helper only. The underlying values stay raw numbers so the
// allocation maths keeps working — a formatted "1,234.50" parses back as 1.
function money(value) {
    return (parseFloat(value) || 0).toFixed(2)
}

const totalApplied = computed(() => {
    const sum = invoices.value
        .filter(i => i.checked)
        .reduce((s, i) => s + (parseFloat(i.payAmount) || 0), 0)
    return sum.toFixed(2)
})

const remaining = computed(() =>
    Math.max(0, (parseFloat(unapplied.value) || 0) - parseFloat(totalApplied.value)).toFixed(2)
)

const canSubmit = computed(() =>
    parseFloat(totalApplied.value) > 0 &&
    parseFloat(totalApplied.value) <= (parseFloat(unapplied.value) || 0)
)

// Spread what is left on this payment across the ticked invoices, capped by each invoice's due.
function distribute() {
    let remaining = parseFloat(unapplied.value) || 0
    invoices.value.forEach(inv => {
        if (inv.checked) {
            const alloc = Math.min(remaining, parseFloat(inv.pending) || 0)
            inv.payAmount = alloc > 0 ? alloc : ''
            remaining -= alloc > 0 ? alloc : 0
        }
    })
}

function onCheck(inv) {
    if (!inv.checked) inv.payAmount = ''
    distribute()
}

function clampRow(inv) {
    const max = parseFloat(inv.pending) || 0
    if (parseFloat(inv.payAmount) > max) inv.payAmount = max
}

function validate() {
    const errs = {}
    if (!form.value.payment_date) errs.payment_date = __('message.payment_date_error')
    setErrors(errs)
    return !Object.keys(errs).length
}

async function submit() {
    if (!validate()) return
    if (!canSubmit.value) {
        alertStore.setAlert({ message: __('message.insufficient_unapplied_payment'), type: 'danger', component_name: COMPONENT })
        return
    }
    submitting.value = true
    alertStore.unsetAlert()

    const checked = invoices.value.filter(i => i.checked)
    const invoiceChecked = checked.map(i => i.id)
    const invoiceAmount  = checked.map(i => parseFloat(i.payAmount) || 0)

    try {
        // Targets THIS payment, so the money allocated is the money on screen.
        await http.post(`/payments/${paymentId}/apply`, {
            payment_date: form.value.payment_date,
            invoiceChecked,
            invoiceAmount,
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
        const { data } = await http.get(`/payments/${paymentId}/edit`)
        symbol.value    = data.data.symbol ?? ''
        unapplied.value = data.data.unapplied ?? 0
        payment.value   = data.data.payment ?? {}
        invoices.value = (data.data.invoices ?? []).map(inv => ({
            ...inv,
            checked:   false,
            payAmount: '',
        }))
    } catch (err) {
        alertStore.setAlert({ message: err.response?.data?.message || __('message.something_wrong'), type: 'danger', component_name: COMPONENT })
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.th-checkbox { width: 40px; }
.input-amount { width: 120px; }
</style>
