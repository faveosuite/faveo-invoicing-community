<template>
    <div>
        <inline-loader v-if="loading" />
        <div v-else class="row">

            <div class="col-md-4 mb-4">
                <RouterLink to="/my-invoices" class="text-decoration-none">
                    <div class="card h-100 bg-color-grey">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <i class="fas fa-user fa-3x text-muted"></i>
                                <span class="display-6 fw-bold text-dark">{{ data.pending_invoices_count ?? 0 }}</span>
                            </div>
                            <div class="mt-3">
                                <strong class="text-uppercase text-dark">{{ __('message.pending_invoices') }}</strong>
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>

            <div class="col-md-4 mb-4">
                <RouterLink to="/my-orders" class="text-decoration-none">
                    <div class="card h-100 bg-color-grey">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <i class="fas fa-check-circle fa-3x text-muted"></i>
                                <span class="display-6 fw-bold text-dark">{{ data.total_orders_count ?? 0 }}</span>
                            </div>
                            <div class="mt-3">
                                <strong class="text-uppercase text-dark">{{ __('message.orders') }}</strong>
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>

            <div class="col-md-4 mb-4">
                <RouterLink to="/my-orders" class="text-decoration-none">
                    <div class="card h-100 bg-color-grey">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <i class="fas fa-sync-alt fa-3x text-muted"></i>
                                <span class="display-6 fw-bold text-dark">{{ data.order_renewals_count ?? 0 }}</span>
                            </div>
                            <div class="mt-3">
                                <strong class="text-uppercase text-dark">{{ __('message.order_renewals') }}</strong>
                            </div>
                        </div>
                    </div>
                </RouterLink>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'

const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const data    = ref({})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/client-dashboard-details`)
        data.value = res.data?.data ?? res.data
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        loading.value = false
    }
})
</script>
