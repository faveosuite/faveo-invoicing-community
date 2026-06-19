<template>
  <div class="datatable">

    <!-- Search + optional bulk-actions slot -->
    <div v-if="isFilterable || $slots['table-tools'] || $slots['bulk-actions']" class="d-flex align-items-center gap-2 float-end me-0 mb-3">
      <slot name="bulk-actions" />
      <input
          v-if="isFilterable"
          type="text"
          class="form-control globe-search"
          v-model="searchStr"
          @keyup.enter="onSearch"
          placeholder="Type and press enter to search..."
      />
      <slot name="table-tools" />
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
import SimplePagination from '@/components/Reusable/SimplePagination.vue'

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

function defaultResponseAdapter(response) {
  // requestFunction's catch returns undefined on HTTP errors — handle that
  // safely so pagination doesn't end up with NaN (→ "Invalid array length").
  const res         = response?.data?.data
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
  sortIcon: {
    base: 'fas',
    is:   'fa-up-down',
    up:   'fa-chevron-down',
    down: 'fa-chevron-up',
  },
  requestAdapter(data) {
    return {
      'sort-field': data.orderBy,
      'sort-order': data.ascending ? 'asc' : 'desc',
      'search-query': (data.query ?? '').trim(),
      page: data.page,
      limit: data.limit,
    }
  },
  requestFunction(data) {
    return http.get(props.url, { params: data }).catch(e => {
      console.error('[DataTable] request error:', e)
    })
  },
  ...props.option,
  pagination: { chunk: 0, edge: false },
  responseAdapter: (response) => {
    const callerAdapter = props.option.responseAdapter
    const result = callerAdapter ? callerAdapter(response) : defaultResponseAdapter(response)
    if (callerAdapter) {
      const res = response?.data?.data
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
.datatable                        { padding-top: 10px !important; padding-bottom: 10px !important; }
table                             { border-collapse: collapse; }
.VueTables .table-responsive      { width: 100% !important; }
.VueTables__table                 { font-size: 14px !important; }
.VueTables__table th,
.VueTables__table td              { padding: 0.75rem; vertical-align: middle; }

.dt-select{width:40px!important;min-width:40px;max-width:40px;}
.dt-action{min-width:200px;max-width:200px;}
.dt-email{min-width:220px;max-width:220px;}
.dt-name{min-width:180px;max-width:180px;}
.dt-mobile{min-width:140px;max-width:140px;}
.dt-country{min-width:120px;max-width:120px;}
.dt-status{min-width:130px;max-width:130px;}
.dt-date{min-width:160px;max-width:160px;}
.dt-amount{min-width:130px;max-width:130px;}
.dt-number{min-width:130px;max-width:130px;}
.dt-code{min-width:120px;max-width:120px;}
.dt-text{min-width:250px;max-width:250px;}

.datatable .VueTables__search     { display: none !important; }
.datatable .VuePagination         { display: none !important; }

.VueTables__limit                        { float: left !important; margin-left: 0; }
.datatable .VueTables__limit-field       { display: flex; align-items: center; margin-bottom: 8px; }
.datatable .VueTables__limit-field label { display: none !important; }
.VueTables__limit-field .form-control    { cursor: pointer !important; appearance: auto !important; width: auto !important; min-width: 70px; }

.VueTables__search-field input,
.globe-search                     { width: 300px !important; }

.datatable .btn                   { margin-right: 4px !important; }

.VueTables__row a                 { text-decoration: none !important; }
.VueTables__sort-icon             { float: right !important; cursor: pointer !important; font-size: 0.85em !important; font-weight: 900 !important; transform: translateY(25%) !important; }
.VueTables__sortable              { cursor: pointer !important; }

.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-light {
  color: #333 !important;
  background-color: #dcdcdc !important;
  border-color: #c6c7c8 !important;
}
.table_btn { background: #dcdcdc !important; }
.user-table-actions .btn { margin-right: 4px; }
.dt-success { color: #017701 !important; }
.dt-danger { color: red !important; }
</style>
