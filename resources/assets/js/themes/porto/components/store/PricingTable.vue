<template>
    <div>
        <!-- Monthly / Yearly toggle (only when switcher is enabled) -->
        <div v-if="switcher" class="d-flex justify-content-center align-items-center mb-5">
            <div class="text-3 p-relative top-1">Monthly</div>
            <div class="px-2">
                <div class="form-check form-switch form-switch-md mb-0">
                    <input type="checkbox" class="form-check-input" v-model="isYearly" />
                </div>
            </div>
            <div class="text-3 p-relative top-1">Yearly</div>
        </div>

        <!-- Cards carousel with rounded navigation -->
        <div
            class="pricing-carousel owl-carousel owl-theme stage-margin rounded-nav"
            :data-plugin-options="carouselOptions"
        >
            <div v-for="product in products" :key="product.id" class="py-2">
                <PlanCard
                    :product="product"
                    :currencySymbol="currencySymbol"
                    :billingCycle="switcher ? billingCycle : null"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import PlanCard from './PlanCard.vue'
import { useCarousel } from '../../composables/useCarousel.js'

const props = defineProps({
    products:       { type: Array,   required: true },
    currencySymbol: { type: String,  default: '$' },
    switcher:       { type: Boolean, default: false },
    defaultCycle:   { type: String,  default: 'yearly' },
})

useCarousel('.pricing-carousel')

const isYearly     = ref(props.defaultCycle === 'yearly')
const billingCycle = computed(() => isYearly.value ? 'yearly' : 'monthly')


const carouselOptions = computed(() => {
    const n = props.products.length
    const maxItems = Math.min(n, 3)
    return JSON.stringify({
        responsive: {
            0:    { items: 1 },
            600:  { items: Math.min(n, 2) },
            992:  { items: maxItems },
        },
        margin: 20,
        loop:   false,
        nav:    true,
        dots:   false,
    })
})
</script>
