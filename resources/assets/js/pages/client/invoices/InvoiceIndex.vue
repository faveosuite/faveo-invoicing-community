<template>
  <div>
    <AppAlert componentName="client-invoices-index"/>
    <AppCard :title="__('message.my_invoices')">
      <DataTable :url="apiUrl" :dataColumns="columns" :option="tableOptions">
        <template #number="{ row }">
          <div class="d-flex flex-column">
            <RouterLink :to="'/invoices/' + row.id" class="fw-semibold">{{ row.number || '—' }}</RouterLink>
            <span v-if="row.is_renewed" class="badge bg-primary mt-1 w-auto">
              {{ __('message.renewed') }}
            </span>
          </div>
        </template>
        <template #date="{ row }">{{ formatDate(row.date) }}</template>
        <template #orders="{ row }">
          <template v-if="row.orders && row.orders.length">
                    <span v-for="(order, i) in row.orders" :key="order.id">
                        <RouterLink :to="'/orders/' + order.id" class="fw-semibold">{{ order.number }}</RouterLink>
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
            <action-button action="view" :to="'/invoices/' + row.id"
                           v-tooltip :title="__('message.view')"/>

            <action-button v-if="row.show_pay"
                           icon="fas fa-credit-card" class="table_btn"
                           v-tooltip :title="__('message.pay')"
                           @click="goToPay(row.id)"/>
          </div>
        </template>
      </DataTable>
    </AppCard>
  </div>
</template>

<script setup>
import {reactive} from 'vue'
import {RouterLink} from 'vue-router'
import {__} from '@/plugins/i18n'

const el = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-my-invoices`

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

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString(undefined, {year: 'numeric', month: 'short', day: 'numeric'})
}

function goToPay(invoiceId) {
  window.location.href = `${baseUrl}/autopaynow/${invoiceId}`
}
</script>
