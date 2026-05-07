<template>
    <div class="datatable">

        <!-- Search + optional bulk-actions slot -->
        <div v-if="isFilterable" class="d-flex align-items-center gap-2 float-end me-0 mb-3">
            <slot name="bulk-actions" />
            <input
                type="text"
                class="form-control globe-search"
                v-model="searchStr"
                @keyup.enter="onSearch"
                placeholder="Type and press enter to search..."
            />
        </div>

        <v-server-table
            :url="url"
            :columns="dataColumns"
            :options="computedOptions"
            ref="tableRef"
            @loaded="onLoaded"
        >
            <template v-for="(_, slotName) in $slots" :key="slotName" v-slot:[slotName]="slotData">
                <slot :name="slotName" v-bind="slotData ?? {}" />
            </template>
        </v-server-table>

        <div class="pagination-container">
            <div v-if="!isLoading">
                <template v-if="total === 1">1 record</template>
                <template v-else-if="total !== null && total <= perPage">{{ total }} records</template>
                <template v-else-if="total !== null && total > perPage">Showing {{ from }} to {{ to }} of {{ total }} records</template>
                <template v-else-if="from && to && nextPage">Showing {{ from }} to {{ to }} records of many</template>
                <template v-else-if="from && to">Showing {{ from }} to {{ to }} of {{ to }} records</template>
            </div>
            <div v-if="!isLoading && (nextPage || prevPage)" class="float-end mr-0 pt-2">
                <SimplePagination
                    :nextPage="nextPage"
                    :prevPage="prevPage"
                    @paginate="onPaginate"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import http from '@/plugins/axios'
import SimplePagination from './SimplePagination.vue'

const props = defineProps({
    url: { type: String, required: true },
    dataColumns: { type: Array, required: true },
    option: { type: Object, default: () => ({}) },
})

const tableRef  = ref(null)
const isLoading = ref(true)
const searchStr = ref('')
const nextPage  = ref(null)
const prevPage  = ref(null)
const total     = ref(null)
const from      = ref(null)
const to        = ref(null)
const perPage   = ref(10)

const isFilterable = computed(() => props.option.filterable ?? false)

function onSearch() {
    tableRef.value?.setFilter(searchStr.value)
}

function onLoaded() {
    isLoading.value = false
}

function onPaginate(direction) {
    const targetUrl = direction === 'next' ? nextPage.value : prevPage.value
    if (!targetUrl) return
    const page = parseInt(new URL(targetUrl).searchParams.get('page'))
    if (page) tableRef.value?.setPage(page)
}

function defaultResponseAdapter({ data }) {
    const res         = data?.data
    const pp          = parseInt(res?.per_page) || 10
    const currentPage = res?.current_page ?? 1
    const toVal       = res?.to ?? 0
    perPage.value     = pp
    total.value       = res?.total           ?? null
    from.value        = res?.from            ?? null
    to.value          = toVal
    nextPage.value    = res?.next_page_url   ?? null
    prevPage.value    = res?.prev_page_url   ?? null
    isLoading.value   = false
    return {
        data: res?.data ?? [],
        count: res?.total ?? (res?.next_page_url ? currentPage * pp + 1 : toVal),
    }
}

const tableData = computed(() => tableRef.value?.data ?? [])

onMounted(() => {
    if (window.emitter) window.emitter.on('refreshData', () => tableRef.value?.refresh())
})
onBeforeUnmount(() => {
    if (window.emitter) window.emitter.off('refreshData')
})
defineExpose({ nextPage, prevPage, paginate: onPaginate, total, from, to, perPage, isLoading, tableData, refresh: () => tableRef.value?.refresh() })

const computedOptions = computed(() => ({
    perPage: 10,
    perPageValues: [10, 25, 50, 100],
    skin: 'table table-hover table-striped table-bordered',
    sortable: [],
    filterable: false,
    requestAdapter(data) {
        return {
            'sort-field': data.orderBy,
            'sort-order': data.ascending ? 'asc' : 'desc',
            'search-query': (data.query ?? '').trim(),
            page: data.page,
            limit: data.limit,
        }
    },
    // bypass v-tables-3's window.axios path — axios 1.x removed CancelToken
    requestFunction(data) {
        return http.get(props.url, { params: data }).catch(e => {
            console.error('[DataTable] request error:', e)
        })
    },
    // caller overrides
    ...props.option,
    // always override — must not be changed by caller
    pagination: { chunk: 0, edge: false },
    responseAdapter: (response) => {
        const callerAdapter = props.option.responseAdapter
        const result = callerAdapter ? callerAdapter(response) : defaultResponseAdapter(response)
        if (callerAdapter) {
            // Caller's adapter doesn't manage pagination state — extract it here
            const res = response.data?.data
            if (res) {
                const pp          = parseInt(res.per_page) || 10
                perPage.value     = pp
                total.value       = res.total           ?? null
                from.value        = res.from            ?? null
                to.value          = res.to              ?? null
                nextPage.value    = res.next_page_url   ?? null
                prevPage.value    = res.prev_page_url   ?? null
                isLoading.value   = false
            }
        }
        return result
    },
}))
</script>

<style>
/* ── layout ── */
.datatable                        { padding-top: 10px !important; padding-bottom: 10px !important; }
table                             { border-collapse: collapse; }
.VueTables .table-responsive      { width: 100% !important; }
.VueTables__table                 { font-size: 14px !important; }
.VueTables__table th,
.VueTables__table td              { padding: 0.75rem; vertical-align: middle; }

/* ── hide built-in search, v-tables pagination ── */
.datatable .VueTables__search     { display: none !important; }
.datatable .VuePagination         { display: none !important; }

/* ── per-page selector ── */
.VueTables__limit                 { float: left !important; margin-left: -10px; }
.datatable .VueTables__limit-field label { display: none !important; }
.VueTables__limit-field .form-control    { cursor: pointer !important; appearance: auto !important; }

/* ── custom search input ── */
.VueTables__search-field input,
.globe-search                     { width: 300px !important; }

/* ── button spacing (matches Faveo global .btn rule) ── */
.datatable .btn                   { margin-right: 4px !important; }

/* ── table links & sort ── */
.VueTables__row a                 { text-decoration: none !important; }
.VueTables__sort-icon             { padding-left: 10px !important; cursor: pointer !important; }

/* ── pagination footer ── */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* ── Custom Button & Action Classes ── */
.table_btn { background: #dcdcdc !important; }
.actions-row .btn { margin-right: 4px; }
.dt-success { color: #017701 !important; }
.dt-danger { color: red !important; }
</style>
