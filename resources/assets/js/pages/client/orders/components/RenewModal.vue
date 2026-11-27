<template>
    <AppModal
        :showModal="show"
        :onClose="close"
        :showCloseBtn="false"
    >
        <template #title>
            <h2 class="modal-title h5 fw-bold mb-0">{{ __('message.renew_your_order') }}</h2>
        </template>
        <template #fields>
            <!-- In-modal so errors aren't hidden behind the backdrop. -->
            <Alert componentName="renew-modal" />
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
            <template v-else>
                <!-- Current order info -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold text-dark">{{ __('message.current_plan') }}</span>
                    <span class="text-dark">{{ order?.current_plan || '—' }}</span>
                </div>
                <!-- Agents only for products sold by agents (from the license code: 0 = unlimited) -->
                <div v-if="order?.agents != null" class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold text-dark">{{ __('message.agents') }}</span>
                    <span class="text-dark">{{ order.agents || __('message.unlimited') }}</span>
                </div>
                <!-- What renews with the plan, so the price below makes sense -->
                <div v-if="renewing.length" class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <span class="fw-semibold text-dark text-nowrap">{{ __('message.renew_addons') }}</span>
                    <span class="text-dark text-end">{{ renewing.slice(0, 4).map(e => e.label).join(', ') }}<template v-if="renewing.length > 4"> {{ __('message.renew_addons_more', { count: renewing.length - 4 }) }}</template></span>
                </div>
                <div class="border-bottom mb-3"></div>

                <DynamicSelect name="plan"
                             :label="__('message.plans')"
                             :elements="plans"
                             :value="selectedPlan"
                             :onChange="onPlanChange"
                             :required="true" />

                <!-- Price summary -->
                <div v-if="price" class="d-flex justify-content-between align-items-center border-top pt-3 mt-1">
                    <span class="fw-bold text-dark">{{ __('message.price_to_be_paid') }}</span>
                    <span class="fw-bold text-dark fs-5">{{ price }}</span>
                </div>
            </template>
        </template>
        <template #controls>
            <action-button action="cancel" @click="close" />
            <action-button
                action="confirm"
                :label="__('message.renew')"
                :loading="submitting"
                :disabled="!selectedPlan"
                @click="submit"
            />
        </template>
    </AppModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { __ } from '@/plugins/i18n'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'
import Alert from '@/components/Reusable/Alert.vue'

const props = defineProps({
    show:  { type: Boolean, default: false },
    // Order/row info: { product_id, id, sub_id, client_id, current_plan, agents }
    order: { type: Object, default: null },
})
const emit = defineEmits(['update:show'])

const router  = useRouter()

const loading      = ref(false)
const submitting   = ref(false)
const plans        = ref([])
const selectedPlan = ref(null)
const price        = ref('')
// The order's add-ons that renew (quantity > 0 at the next renewal)
const renewing     = ref([])

// Load renewal plans whenever the modal opens.
watch(() => props.show, async (open) => {
    if (!open) return
    plans.value        = []
    selectedPlan.value = null
    price.value        = ''
    renewing.value     = []
    if (!props.order?.product_id) return

    loading.value = true
    try {
        const res = await http.get(`/renew-popup-details/${props.order.product_id}`, {
            params: { order: props.order.id },
        })
        plans.value = res.data?.data?.plans ?? []
        if (plans.value.length) await onPlanChange(plans.value[0])
        // Only an extra: the renewal still works without it
        renewing.value = await http.get(`/my-order/${props.order.id}/extras`)
            .then(r => (r.data?.data?.extras ?? []).filter(e => e.renew_quantity > 0), () => [])
    } catch (e) { errorHandler(e, 'renew-modal') }
    finally { loading.value = false }
})

function close() {
    emit('update:show', false)
}

async function onPlanChange(plan) {
    selectedPlan.value = plan
    await fetchCost()
}

async function fetchCost() {
    if (!selectedPlan.value) { price.value = ''; return }
    try {
        const res = await http.get(`/get-renew-cost`, {
            params: { plan: selectedPlan.value.id, order: props.order?.id },
        })
        price.value = res.data?.data?.formatted_price ?? ''
    } catch (e) { errorHandler(e, 'renew-modal') }
}

async function submit() {
    if (!selectedPlan.value || submitting.value) return
    submitting.value = true
    try {
        const res = await http.post(`/client/renew/${props.order?.sub_id}`, {
            plan: selectedPlan.value.id,
            user: props.order?.client_id,
        })
        const invoiceId = res.data?.data?.invoice_id
        if (invoiceId) router.push({ path: '/checkout', query: { invoice: invoiceId } })
        else close()
    } catch (e) {
        errorHandler(e, 'renew-modal')
    } finally {
        submitting.value = false
    }
}
</script>
