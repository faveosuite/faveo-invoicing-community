<template>
    <div>
        <AppAlert componentName="invoices-edit" />
        
        <div v-if="loading" class="text-center py-5">
            <span class="spinner-border text-secondary"></span>
        </div>

        <div v-else class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Edit Invoice: #{{ invoice?.number }}</h4>
                <div class="card-tools">
                    <router-link to="/invoices" class="btn btn-tool" title="Back to Invoices" v-tooltip>
                        <i class="fas fa-arrow-left"></i> Back
                    </router-link>
                </div>
            </div>

            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-4 mb-3">
                            <DatePicker
                                name="date"
                                label="Date"
                                :value="form.date"
                                :onChange="(val) => { form.date = val; errors.date = null; }"
                                placeholder="MM/DD/YYYY"
                            />
                            <span v-if="errors.date" class="text-danger small">{{ errors.date[0] }}</span>
                        </div>

                        <!-- Invoice Total -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Invoice Total</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.total"
                                @input="errors.total = null"
                            />
                            <span v-if="errors.total" class="text-danger small">{{ errors.total[0] }}</span>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" v-model="form.status" @change="errors.status = null">
                                <option value="">Choose</option>
                                <option value="success">Success</option>
                                <option value="pending">Pending</option>
                                <option value="Partially paid">Partially Paid</option>
                            </select>
                            <span v-if="errors.status" class="text-danger small">{{ errors.status[0] }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <i v-else class="fas fa-floppy-disk"></i>&nbsp; Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'invoices-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route = useRoute()
const router = useRouter()
const invoiceId = route.params.id

const loading = ref(true)
const saving = ref(false)
const errors = reactive({})
const invoice = ref(null)

const form = reactive({
    date: null,
    total: '',
    status: '',
})

async function fetchInvoice() {
    try {
        const res = await http.get(`${baseUrl}/invoice/${invoiceId}`)
        const data = res.data?.data ?? res.data
        invoice.value = data.invoice
        
        // Populate form
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
    Object.keys(errors).forEach(k => delete errors[k])
    
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/invoice/edit/${invoiceId}`, form)
        successHandler(res, COMPONENT)
        router.push('/invoices')
    } catch (e) {
        if (e.response?.status === 422) {
            Object.assign(errors, e.response.data.errors || {})
        }
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    fetchInvoice()
})
</script>
