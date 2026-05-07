<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Country List</h4>
            </div>
            <div class="card-body">
                <DataTable
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const COMPONENT = 'country-list'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl = `${baseUrl}/get-country`

const columns = ['country', 'code', 'count']

const tableOptions = reactive({
    headings: {
        country: 'Country',
        code: 'Code',
        count: 'Users',
    },
    templates: {
        country: (f, row) => row.country || '—',
        code: (f, row) => row.code || '—',
        count: (f, row) => row.count ?? 0,
    },
    sortable: ['country', 'code', 'count'],
    filterable: true,
    requestAdapter(data) {
        let sortField = data.orderBy ?? 'country_name'
        if (sortField === 'country') sortField = 'country_name'
        if (sortField === 'code')    sortField = 'country_code_char2'
        if (sortField === 'count')   sortField = 'users_count'

        return {
            'sort-field': sortField,
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    orderBy: { column: 'country', ascending: true },
})
</script>
