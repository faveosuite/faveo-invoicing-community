<template>
  <div role="main" class="main shop pb-4">
    <div class="container py-4">

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
      </div>

      <div v-else-if="error" class="text-center py-6">
        <i class="fas fa-triangle-exclamation fa-3x text-color-grey-lighten mb-3 d-block"></i>
        <p class="text-color-grey mb-0">{{ error }}</p>
      </div>

      <div v-else class="row justify-content-center">
        <div class="col-lg-8">

          <!-- Thanks banner -->
          <div class="card border-width-3 border-radius-0 border-color-success">
            <div class="card-body text-center">
              <p class="text-color-dark font-weight-bold text-4-5 mb-0">
                <i class="fas fa-check text-color-success me-1"></i> {{ __('message.thanks_order_received') }}
              </p>
            </div>
          </div>

          <!-- Summary row -->
          <div class="d-flex flex-column flex-md-row justify-content-between py-3 px-4 my-4">
            <div class="text-center">
              <span><strong class="text-color-dark">{{ __('message.invoice_number') }}</strong><br>{{ invoice.number }}</span>
            </div>
            <div class="text-center mt-4 mt-md-0">
              <span><strong class="text-color-dark">{{ __('message.status') }}</strong><br>{{ __('message.success') }}</span>
            </div>
            <div class="text-center mt-4 mt-md-0">
              <span><strong class="text-color-dark">{{ __('message.date') }}</strong><br>{{ invoice.date }}</span>
            </div>
            <div v-if="paymentMethod" class="text-center mt-4 mt-md-0">
              <span><strong class="text-color-dark">{{ __('message.payment-method') }}</strong><br>{{ paymentMethod }}</span>
            </div>
            <div class="text-center mt-4 mt-md-0">
              <span><strong class="text-color-dark">{{ __('message.total') }}</strong><br>{{ symbol }}{{ invoice.grand_total }}</span>
            </div>
          </div>

          <!-- Order table -->
          <div class="card border-width-3 border-radius-0 border-color-hover-dark mb-4">
            <div class="card-body">
              <h4 class="font-weight-bold text-uppercase text-4 mb-3">{{ __('message.your_order') }}</h4>

              <table class="shop_table cart-totals mb-0">
                <tbody>
                  <tr>
                    <td colspan="2" class="border-top-0">
                      <strong class="text-color-dark">{{ __('message.product') }}</strong>
                    </td>
                  </tr>

                  <tr v-for="o in orders" :key="o.number">
                    <td>
                      <strong class="d-block text-color-dark line-height-1 font-weight-semibold">
                        {{ o.product_name }} <span class="product-qty">x {{ o.qty }}</span>
                      </strong>
                      <ul class="wc-item-meta" style="list-style: none; padding: 0;">
                        <li style="display: inline-block;">
                          <strong class="wc-item-meta-label">{{ __('message.order_number') }}:</strong>
                          <p style="display: inline;">{{ o.number }}</p>
                        </li>
                      </ul>
                    </td>
                    <td class="text-end align-top">
                      <span class="amount font-weight-medium text-color-grey">{{ symbol }}{{ o.price }}</span><br>
                      <a v-if="o.downloadable" :href="o.download_url"
                         class="btn btn-light-scale-2 btn-sm text-dark"
                         :aria-label="__('message.click_to_download')" :title="__('message.click_to_download')">
                        <i class="fa fa-download"></i>
                      </a>
                    </td>
                  </tr>

                  <tr class="total">
                    <td><strong class="text-color-dark text-3-5">{{ __('message.total') }}</strong></td>
                    <td class="text-end">
                      <strong class="text-color-dark"><span class="amount text-color-dark text-5">{{ symbol }}{{ invoice.grand_total }}</span></strong>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Actions -->
          <div class="d-flex justify-content-center gap-3">
            <router-link to="/invoices" class="btn btn-dark btn-modern text-uppercase border-radius-0 px-4 py-2">
              {{ __('message.my_invoices') }}
            </router-link>
            <router-link to="/orders" class="btn btn-light-scale-2 text-uppercase border-radius-0 px-4 py-2 text-dark">
              {{ __('message.my_orders') }}
            </router-link>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import http, { parseErrorMessage } from '@/plugins/axios'
import { __ } from '@/plugins/i18n'

const el = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route = useRoute()
const invoiceId = route.query.invoice

const loading = ref(true)
const error = ref('')

const invoice = ref({})
const orders = ref([])
const paymentMethod = ref('')
const symbol = ref('')

onMounted(async () => {
  if (!invoiceId) { loading.value = false; error.value = __('message.err_msg'); return }
  try {
    const { data } = await http.get(`${baseUrl}/invoice/${invoiceId}/pay-success`)
    invoice.value = data.data.invoice
    orders.value = data.data.orders
    paymentMethod.value = data.data.payment_method
    symbol.value = data.data.invoice.currency_symbol
  } catch (e) {
    error.value = parseErrorMessage(e)
  } finally {
    loading.value = false
  }
})
</script>
