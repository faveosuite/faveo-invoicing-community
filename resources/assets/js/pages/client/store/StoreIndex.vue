<template>
    <div>
        <div v-if="loadingGroups" class="row justify-content-center py-3"><loader /></div>

        <template v-else>
            <!-- Tagline only — group name is shown in the page header -->
            <div v-if="currentGroup?.tagline" class="text-center mb-5">
                <p class="text-muted">{{ currentGroup.tagline }}</p>
            </div>

            <!-- Products -->
            <div v-if="loadingProducts" class="row justify-content-center py-3"><loader /></div>
            <div v-else-if="products.length === 0" class="text-center text-muted py-5">
                {{ __('message.no_records_found') }}
            </div>
            <PricingTable
                v-else
                :products="products"
                :currencySymbol="currencySymbol"
                :cloudSubdomain="cloudSubdomain"
                :dataCenters="dataCenters"
                :switcher="currentGroup?.status === true"
            />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { setPageTitle } from '@/core/composables/useBreadcrumb.js'

const route  = useRoute()
const router = useRouter()

const groups          = ref([])
const selectedGroupId = ref(null)
const currentGroup    = ref(null)
const products        = ref([])
const currencySymbol  = ref('$')
const cloudSubdomain  = ref('')
const dataCenters     = ref([])
const loadingGroups   = ref(true)
const loadingProducts = ref(false)

async function loadGroups() {
    try {
        const res = await http.get(`/store/groups`)
        groups.value = res.data?.data ?? []

        if (groups.value.length === 0) return

        const paramId = route.params.groupId ? parseInt(route.params.groupId) : null
        const target  = paramId && groups.value.find(g => g.id === paramId)
            ? paramId
            : groups.value[0].id

        await selectGroup(target)
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        loadingGroups.value = false
    }
}

async function selectGroup(groupId) {
    selectedGroupId.value = groupId
    loadingProducts.value = true

    try {
        const res  = await http.get(`/store/${groupId}/products`)
        const data = res.data?.data ?? {}

        currentGroup.value    = data.group ?? null
        products.value        = data.products ?? []
        currencySymbol.value  = data.currency_symbol ?? '$'
        cloudSubdomain.value  = data.cloud_subdomain ?? ''
        dataCenters.value     = data.data_centers ?? []

        setPageTitle(data.group?.name)

        if (route.params.groupId !== String(groupId)) {
            router.replace({ path: `/store/${groupId}` })
        }
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        loadingProducts.value = false
    }
}

watch(() => route.params.groupId, (id) => {
    if (id && parseInt(id) !== selectedGroupId.value) {
        selectGroup(parseInt(id))
    }
})

onMounted(loadGroups)
onBeforeUnmount(() => setPageTitle(null))
</script>
