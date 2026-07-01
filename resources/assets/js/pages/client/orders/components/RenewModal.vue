<template>
    <AppModal
        :showModal="show"
        :onClose="close"
        :showCloseBtn="false"
    >
        <template #title>
            <h5 class="modal-title fw-bold">{{ __('message.renew_your_order') }}</h5>
        </template>
        <template #fields>
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
            <template v-else>
                <!-- Current order info -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">{{ __('message.current_plan') }}</span>
                    <span class="fw-bold text-dark">{{ order?.current_plan || '—' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <span class="text-muted">{{ __('message.agents') }}</span>
                    <span class="fw-bold text-dark">{{ order?.agents || '—' }}</span>
                </div>

                <SelectField name="plan"
                             :label="__('message.plans')"
                             :elements="plans"
                             :value="selectedPlan"
                             :onChange="onPlanChange"
                             :required="true" />

                <!-- Price summary -->
                <div v-if="price" class="d-flex justify-content-between align-items-center border-top pt-3 mt-1">
                    <span class="text-muted">{{ __('message.price_to_be_paid') }}</span>
                    <span class="fw-bold text-dark fs-6">{{ price }}</span>
                </div>
            </template>
        </template>
        <template #controls>
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

// Load renewal plans whenever the modal opens.
watch(() => props.show, async (open) => {
    if (!open) return
    plans.value        = []
    selectedPlan.value = null
    price.value        = ''
    if (!props.order?.product_id) return

    loading.value = true
    try {
        const res = await http.get(`/renew-popup-details/${props.order.product_id}`)
        plans.value = res.data?.data?.plans ?? []
        if (plans.value.length) await onPlanChange(plans.value[0])
    } catch { /* silent */ }
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
    } catch { /* silent */ }
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
        errorHandler(e, 'client-page')
    } finally {
        submitting.value = false
    }
}
</script>
