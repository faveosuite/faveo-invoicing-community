<template>
  <div>
        <div v-if="loading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-2x"></i>
        </div>
        <div v-else>
          <div class="row">
            <div class="col-lg-4 col-6">
              <div class="small-box text-bg-info">
                <div class="inner">
                  <h4>{{ __('message.total_sales') }}</h4>
                  <template v-for="(amount, currency) in data.totalSales" :key="currency">
                    <span>{{ currency }}: &nbsp; {{ formatCurrency(amount, currency) }}</span><br/>
                  </template>
                </div>
                <div class="small-box-icon"><i class="ion ion-bag"></i></div>
                <router-link to="/invoices?status=success" class="small-box-footer">{{ __('message.more_info') }} <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box text-bg-success">
                <div class="inner">
                  <h4>{{ __('message.yearly_sales') }}</h4>
                  <template v-for="(amount, currency) in data.yearlySales" :key="currency">
                    <span>{{ currency }}: &nbsp; {{ formatCurrency(amount, currency) }}</span><br/>
                  </template>
                </div>
                <div class="small-box-icon"><i class="ion ion-stats-bars"></i></div>
                <router-link :to="`/invoices?status=success&from=${startingDateOfYear}`" class="small-box-footer">{{ __('message.more_info') }} <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box text-bg-warning">
                <div class="inner">
                  <h4>{{ __('message.monthly_sales') }}</h4>
                  <template v-for="(amount, currency) in data.monthlySales" :key="currency">
                    <span>{{ currency }}: &nbsp; {{ formatCurrency(amount, currency) }}</span><br/>
                  </template>
                </div>
                <div class="small-box-icon"><i class="ion ion-pie-graph"></i></div>
                <router-link :to="`/invoices?status=success&from=${startMonthDate}&till=${endMonthDate}`" class="small-box-footer">{{ __('message.more_info') }} <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box text-bg-danger">
                <div class="inner">
                  <h4>{{ __('message.pending_payments') }}</h4>
                  <template v-for="(amount, currency) in data.pendingPayments" :key="currency">
                    <span>{{ currency }}: &nbsp; {{ formatCurrency(amount, currency) }}</span><br/>
                  </template>
                </div>
                <div class="small-box-icon"><i class="ion ion-ios-pricetag-outline"></i></div>
                <router-link to="/invoices?status=pending" class="small-box-footer">{{ __('message.more_info') }} <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box text-bg-warning">
                <div class="inner">
                  <h4>{{ __('message.products_installed_rate') }}&nbsp;{{ formatRate(data.productInstalledRate?.rate) }}%</h4>
                  <span>{{ __('message.total_subscription') }} &nbsp; {{ data.productInstalledRate?.total_subscription || 0 }}</span><br/>
                  <span>{{ __('message.not_installed') }} &nbsp; {{ data.productInstalledRate?.inactive_subscription || 0 }}</span>
                </div>
                <div class="small-box-icon"><i class="ion ion-ios-download-outline"></i></div>
                <router-link to="/orders?ins_not_ins=not_installed" class="small-box-footer">{{ __('message.more_info') }} <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>

            <div class="col-lg-4 col-6">
              <div class="small-box text-bg-info">
                <div class="inner">
                  <h4>{{ __('message.paid_orders_rate') }}&nbsp;{{ formatRate(data.paidOrderRate?.rate) }}%</h4>
                  <span>{{ __('message.total_orders_rate') }} &nbsp; {{ data.paidOrderRate?.all_orders || 0 }}</span><br/>
                  <span>{{ __('message.paid_orders') }} &nbsp; {{ data.paidOrderRate?.paid_orders || 0 }}</span>
                </div>
                <div class="small-box-icon"><i class="ion ion-ios-cart-outline"></i></div>
                <router-link to="/orders?p_un=unpaid" class="small-box-footer">{{ __('message.more_info') }} <i class="fas fa-arrow-circle-right"></i></router-link>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.recently_register_users') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body direct-chat-messages p-0">
                  <ul class="users-list clearfix">
                    <li v-for="user in data.clientWithMobileAndEmailActivation" :key="user.id">
                      <router-link :to="`/clients/${user.id}`" class="text-decoration-none">
                        <img loading="lazy" :src="user.profile_pic" class="img-size-50 rounded-circle" alt="User Image">
                      </router-link>
                      <router-link :to="`/clients/${user.id}`" class="text-decoration-none users-list-name">{{ user.first_name }} {{ user.last_name }}</router-link>
                      <span class="users-list-date">{{ formatDateForUser(user.created_at) }}</span>
                    </li>
                  </ul>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/clients" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all') }}</router-link>
                </div>
              </div>
            </div>

            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.recent_invoice') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped table-valign-middle">
                      <thead class="table-light">
                      <tr>
                        <th>{{ __('message.invoice_no') }}</th>
                        <th>{{ __('message.total') }}</th>
                        <th>{{ __('message.user') }}</th>
                        <th>{{ __('message.date') }}</th>
                        <th>{{ __('message.paid') }}</th>
                        <th>{{ __('message.balance') }}</th>
                        <th>{{ __('message.status') }}</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="invoice in data.recentInvoices" :key="invoice.id">
                        <td><router-link :to="`/invoices/show?invoiceid=${invoice.id}`" class="text-decoration-none">{{ invoice.number }}</router-link></td>
                        <td>{{ invoice.grand_total }}</td>
                        <td>
                          <router-link v-if="invoice.user" :to="`/clients/${invoice.user.id}`" class="text-decoration-none">
                            {{ invoice.user.first_name }} {{ invoice.user.last_name }}
                          </router-link>
                        </td>
                        <td>{{ formatDate(invoice.date) }}</td>
                        <td>{{ invoice.paid_amount }}</td>
                        <td><div class="sparkbar" data-color="#00a65a" data-height="20">{{ invoice.balance }}</div></td>
                        <td><span v-html="invoice.status"></span></td>
                      </tr>
                      </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/invoices" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all') }}</router-link>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.paid_orders_expired') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped table-valign-middle">
                      <thead class="table-light">
                      <tr>
                        <th>{{ __('message.user') }}</th>
                        <th>{{ __('message.order_no') }}</th>
                        <th>{{ __('message.expiry') }}</th>
                        <th>{{ __('message.days_passed') }}</th>
                        <th>{{ __('message.product') }}</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="sub in data.expiredOrders" :key="sub.id">
                        <td>
                          <router-link v-if="sub.user" :to="`/clients/${sub.user.id}`" class="text-decoration-none">
                            {{ sub.user.first_name }} {{ sub.user.last_name }}
                          </router-link>
                        </td>
                        <td>
                          <router-link v-if="sub.order" :to="`/orders/${sub.order.id}`" class="text-decoration-none">
                            {{ sub.order.number }}
                          </router-link>
                        </td>
                        <td class="text-danger">{{ formatDate(sub.update_ends_at) }}</td>
                        <td>{{ sub.days_expired }} {{ __('message.days') }}</td>
                        <td>{{ sub.product?.name }}</td>
                      </tr>
                      </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/orders" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all') }}</router-link>
                </div>
              </div>
            </div>

            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.paid_next_orders_expired') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped table-valign-middle">
                      <thead class="table-light">
                      <tr>
                        <th>{{ __('message.user') }}</th>
                        <th>{{ __('message.order_no') }}</th>
                        <th>{{ __('message.expiry') }}</th>
                        <th>{{ __('message.days_left') }}</th>
                        <th>{{ __('message.product') }}</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="sub in data.expiringOrders" :key="sub.id">
                        <td>
                          <router-link v-if="sub.user" :to="`/clients/${sub.user.id}`" class="text-decoration-none">
                            {{ sub.user.first_name }} {{ sub.user.last_name }}
                          </router-link>
                        </td>
                        <td>
                          <router-link v-if="sub.order" :to="`/orders/${sub.order.id}`" class="text-decoration-none">
                            {{ sub.order.number }}
                          </router-link>
                        </td>
                        <td>{{ formatDate(sub.update_ends_at) }}</td>
                        <td>{{ sub.days_to_expire }} {{ __('message.days') }}</td>
                        <td>{{ sub.product?.name }}</td>
                      </tr>
                      </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/orders" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all') }}</router-link>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.clients_outdated_version') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped table-valign-middle">
                      <thead class="table-light">
                      <tr>
                        <th>{{ __('message.user') }}</th>
                        <th>{{ __('message.version') }}</th>
                        <th>{{ __('message.product') }}</th>
                        <th>{{ __('message.expiry') }}</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="sub in data.clientWithOutdatedProducts" :key="sub.id">
                        <td>
                          <span v-if="sub.user">{{ sub.user.first_name }} {{ sub.user.last_name }}</span>
                        </td>
                        <td>
                          <span class="fw-semibold text-body-secondary">{{ sub.version }}</span>
                        </td>
                        <td>{{ sub.product?.name }}</td>
                        <td>
                          <span :class="{ 'text-danger': isExpired(sub.update_ends_at) }">{{ formatDate(sub.update_ends_at) }}</span>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/orders" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all') }}</router-link>
                </div>
              </div>
            </div>

            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.recent_paid_orders') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped table-valign-middle">
                      <thead class="table-light">
                      <tr>
                        <th>{{ __('message.order_no') }}</th>
                        <th>{{ __('message.product') }}</th>
                        <th>{{ __('message.date') }}</th>
                        <th>{{ __('message.user') }}</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="order in data.recentPaidOrders" :key="order.id">
                        <td><router-link :to="`/orders/${order.id}`" class="text-decoration-none">{{ order.number }}</router-link></td>
                        <td>{{ order.product_relation?.name }}</td>
                        <td>{{ formatDate(order.created_at) }}</td>
                        <td>
                          <router-link v-if="order.user" :to="`/clients/${order.user.id}`" class="text-decoration-none">
                            {{ order.user.first_name }} {{ order.user.last_name }}
                          </router-link>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/orders" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all_orders') }}</router-link>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.product_sold') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped align-middle" role="table">
                    <thead class="table-light">
                      <tr>
                        <th scope="col">{{ __('message.product') }}</th>
                        <th scope="col">{{ __('message.sales') }}</th>
                        <th scope="col">{{ __('message.last_purchase') }}</th>
                        <th scope="col">{{ __('message.more') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="product in data.productSoldInLast30Days" :key="product.id">
                        <td>
                          <img loading="lazy" :src="product.image" alt="Product Image" class="rounded-circle img-size-32 me-2">
                          {{ product.name }}
                        </td>
                        <td>
                          {{ product.order_count }} {{ __('message.sold') || 'Sold' }}
                        </td>
                        <td>
                          <span>{{ formatDate(product.latest_order_created_at) }}</span>
                        </td>
                        <td>
                          <router-link :to="`/products/${product.id}`" class="text-decoration-none text-secondary">
                            <i class="bi bi-search"></i>
                          </router-link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/orders" class="btn btn-sm btn-secondary float-end">{{ __('message.view_all_orders') }}</router-link>
                </div>
              </div>
            </div>

            <div class="col-lg-6 col-md-12">
              <div class="card mb-4">
                <div class="card-header border-bottom bg-light">
                  <h3 class="card-title mb-0 fw-semibold">{{ __('message.total_sold_products') }}</h3>
                  <div class="card-tools">
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-tool btn-sm">
                      <i class="bi bi-list"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body table-responsive direct-chat-messages p-0">
                  <table class="table table-striped align-middle" role="table">
                    <thead class="table-light">
                      <tr>
                        <th scope="col">{{ __('message.product') }}</th>
                        <th scope="col">{{ __('message.sales') }}</th>
                        <th scope="col">{{ __('message.last_purchase') }}</th>
                        <th scope="col">{{ __('message.more') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="product in data.totalProductsSold" :key="product.id">
                        <td>
                          <img loading="lazy" :src="product.image" alt="Product Image" class="rounded-circle img-size-32 me-2">
                          {{ product.name }}
                        </td>
                        <td>
                          {{ product.order_count }} {{ __('message.sold') || 'Sold' }}
                        </td>
                        <td>
                          <span>{{ formatDate(product.latest_order_created_at) }}</span>
                        </td>
                        <td>
                          <router-link :to="`/products/${product.id}`" class="text-decoration-none text-secondary">
                            <i class="bi bi-search"></i>
                          </router-link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="card-footer clearfix">
                  <router-link to="/products?value=totalSoldProduct" class="btn btn-sm btn-secondary float-end">{{ __('message.view_sold_products') }}</router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '../../core/services/http'
import { __ } from '../../plugins/i18n'

const loading = ref(true)
const data = ref({
  totalSales: {},
  yearlySales: {},
  monthlySales: {},
  pendingPayments: {},
  productInstalledRate: {},
  paidOrderRate: {},
  clientWithMobileAndEmailActivation: [],
  recentInvoices: [],
  expiredOrders: [],
  expiringOrders: [],
  clientWithOutdatedProducts: [],
  recentPaidOrders: [],
  productSoldInLast30Days: [],
  totalProductsSold: []
})

const startingDateOfYear = ref(new Date().getFullYear() + '-01-01')
const d = new Date();
const startMonthDate = ref(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`)
const endMonthDate = ref(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate()}`)

onMounted(async () => {
  try {
    const response = await http.get('/dashboard')
    data.value = response.data
  } catch (error) {
    console.error('Failed to fetch dashboard data', error)
  } finally {
    loading.value = false
  }
})

const formatCurrency = (amount, currency) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(amount)
}

const formatRate = (rate) => {
  return Number(rate || 0).toFixed(2)
}

const formatDateForUser = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  
  if (date.toDateString() === today.toDateString()) {
    return __('message.today') || 'Today'
  } else if (date.toDateString() === yesterday.toDateString()) {
    return __('message.yesterday') || 'Yesterday'
  }
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const isExpired = (dateStr) => {
  if (!dateStr) return false
  return new Date(dateStr) < new Date()
}
</script>
