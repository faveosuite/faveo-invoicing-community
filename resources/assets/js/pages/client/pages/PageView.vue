<template>
    <div class="page-content py-3">
        <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

        <div v-else-if="page">
            <!-- Content is sanitized server-side by HTMLPurifier on save (see BaseModel) -->
            <div v-html="page.content"></div>
        </div>

        <div v-else class="text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <h2 class="text-muted">{{ __('message.page_not_found') }}</h2>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'
import { setPageTitle } from '@/core/composables/useBreadcrumb.js'

const el      = document.getElementById('app-client')
const appName = el?.dataset?.pageTitle || 'Client Panel'
const route   = useRoute()
const loading = ref(true)
const page    = ref(null)

async function loadPage(slug) {
    loading.value = true
    page.value = null
    try {
        const { data } = await http.get(`page-content/${slug}`)
        page.value = data?.data ?? null
        if (page.value?.name) {
            // Page-header banner title + browser tab title = the page name.
            setPageTitle(page.value.name)
            document.title = `${page.value.name} | ${appName}`
        }
    } catch (e) {
        errorHandler(e, 'client-page')
    } finally {
        loading.value = false
    }
}

// Re-fetch when navigating directly between pages (slug changes, same route).
watch(() => route.params.slug, (slug) => loadPage(slug), { immediate: true })

// Clear the override when leaving so other routes keep their own titles.
onUnmounted(() => setPageTitle(null))
</script>
