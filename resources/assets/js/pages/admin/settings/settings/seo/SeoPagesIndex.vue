<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.pages') }}</h4>
            </div>

            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'seo-pages-index'
const apiUrl = '/seo/default-pages'

const dtRef = ref(null)

const labels = {
    login: __('message.seo_login_and_register'),
    forgot_password: __('message.forgot-password'),
    reset_password: __('message.reset_password'),
    cart: __('message.shopping_cart'),
}

function pageLabel(pageKey) {
    return labels[pageKey] ?? pageKey
}

const columns = ['page', 'meta_title', 'meta_description', 'action']

const tableOptions = reactive({
    headings: {
        page: __('message.default_pages'),
        meta_title: __('message.meta_title'),
        meta_description: __('message.meta_description'),
        action: __('message.action'),
    },
    columnsClasses: {
        page: 'dt-name',
        meta_title: 'dt-text',
        meta_description: 'dt-text',
        action: 'dt-action',
    },
    templates: {
        page: (f, row) => pageLabel(row.page_key),
        meta_title: (f, row) => row.meta_title || '—',
        meta_description: (f, row) => row.meta_description || '—',
        action: (f, row) => h(RouterLink, {
            to: `/settings/seo/${row.page_key}/edit`,
            class: 'btn btn-light table_btn',
            title: __('message.edit'),
        }, () => h('i', { class: 'fas fa-edit' })),
    },
    filterable: true,
    requestAdapter: makeRequestAdapter(),
})
</script>
