<template>
  <div>
    <div class="d-flex justify-content-end mb-2">
      <button type="button" class="btn btn-outline-primary btn-sm" @click="showCreditModal = true">
        <i class="fas fa-credit-card me-1"></i>{{ __('message.credits') }} {{ creditBalance }}
      </button>
    </div>

    <AppCard :title="__('message.my_invoices')">
      <DataTable :url="apiUrl" :dataColumns="columns" :option="tableOptions">
        <template #number="{ row }">
          <div class="d-flex flex-column">
            <RouterLink :to="'/my-invoice/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
            <span v-if="row.is_renewed" class="badge bg-primary mt-1 w-auto">
              {{ __('message.renewed') }}
            </span>
          </div>
        </template>
        <template #date="{ row }">{{ formatDate(row.date) }}</template>
        <template #orders="{ row }">
          <template v-if="row.orders && row.orders.length">
                    <span v-for="(order, i) in row.orders" :key="order.id">
                        <RouterLink :to="'/my-order/' + order.id" class="fw-semibold">{{ order.number }}</RouterLink>
                        <span v-if="i < row.orders.length - 1">, </span>
                    </span>
          </template>
          <span v-else>—</span>
        </template>
        <template #status="{ row }">
                <span class="badge" :class="row.status === 'Paid' ? 'bg-success' : 'bg-warning text-dark'">
                    {{ row.status }}
                </span>
        </template>
        <template #action="{ row }">
          <div class="d-flex align-items-center gap-1 flex-nowrap">
            <action-button action="view" :to="'/my-invoice/' + row.id"
                           v-tooltip="__('message.view')"/>

            <action-button v-if="row.show_pay"
                           icon="fas fa-credit-card" class="table_btn"
                           v-tooltip="__('message.pay')"
                           @click="goToPay(row.id)"/>

            <action-button v-if="row.show_delete"
                           icon="fas fa-trash" class="table_btn"
                           v-tooltip="__('message.delete')"
                           @click="confirmDelete(row)"/>
          </div>
        </template>
      </DataTable>
    </AppCard>

    <AppModal
        :showModal="!!deleteTarget"
        :onClose="() => deleteTarget = null"
        :showCloseBtn="false"
    >
        <template #title>
            <h5>{{ __('message.delete_invoice') }}</h5>
        </template>
        <template #fields>
            <AppAlert componentName="my-invoices" />
            <p class="mb-0">{{ __('message.delete_invoice_confirm', { number: deleteTarget?.number }) }}</p>
        </template>
        <template #controls>
            <action-button action="delete" :loading="deleteLoading" @click="doDelete" />
        </template>
    </AppModal>

    <AppModal
        :showModal="showCreditModal"
        :onClose="() => showCreditModal = false"
        :showControls="false"
    >
        <template #title>
            <h5 class="mb-0">{{ __('message.credit_balance') }} {{ creditBalance }}</h5>
        </template>
        <template #fields>
            <h6>{{ __('message.credit_balance_history') }}</h6>
            <ul class="list-group mt-2">
                <li v-if="!creditActivity.length" class="list-group-item text-center">
                    {{ __('message.no_records_found') }}
                </li>
                <li v-for="(item, i) in creditActivity" :key="i" class="list-group-item">
                    <div class="text-muted small">{{ formatDateTime(item.created_at) }}</div>
                    <div>{{ item.text }}</div>
                </li>
            </ul>
        </template>
    </AppModal>

  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { __ } from '@/plugins/i18n'
import { useDateTime } from '@/core/composables/useDateTime'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const { formatDate, formatDateTime } = useDateTime()

const showCreditModal = ref(false)
const creditBalance    = ref('')
const creditActivity   = ref([])

onMounted(async () => {
  try {
    const res = await http.get('/get-credit-balance')
    const data = res.data?.data ?? res.data
    creditBalance.value  = data?.balance ?? ''
    creditActivity.value = data?.activity ?? []
  } catch (e) { errorHandler(e, 'my-invoices') }
})

const router = useRouter()
const apiUrl = `/get-my-invoices`

const columns = ['number', 'date', 'orders', 'grand_total', 'paid', 'balance', 'status', 'action']

const tableOptions = reactive({
  headings: {
    number: () => __('message.invoice_no'),
    date: () => __('message.date'),
    orders: () => __('message.order_no'),
    grand_total: () => __('message.total'),
    paid: () => __('message.paid'),
    balance: () => __('message.balance'),
    status: () => __('message.status'),
    action: () => __('message.actions'),
  },
  sortable: ['number', 'date', 'grand_total'],
  filterable: true,
})


function goToPay(invoiceId) {
  router.push({ path: '/checkout', query: { invoice: invoiceId } })
}

const deleteTarget   = ref(null)
const deleteLoading  = ref(false)
const COMPONENT      = 'my-invoices'

function confirmDelete(row) {
  deleteTarget.value = row
}

async function doDelete() {
  if (!deleteTarget.value) return
  deleteLoading.value = true
  try {
    const res = await http.delete(`/my-invoice/${deleteTarget.value.id}`)
    successHandler(res, COMPONENT)
    deleteTarget.value = null
    globalThis.emitter?.emit('refreshData')
  } catch (e) { errorHandler(e, COMPONENT) }
  finally { deleteLoading.value = false }
}
</script>
