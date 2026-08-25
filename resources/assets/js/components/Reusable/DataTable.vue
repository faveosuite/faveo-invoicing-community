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
          :placeholder="__('message.search_placeholder')"
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

    <div class="pagination-container d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div v-if="!isLoading">
        <template v-if="total === 1">1 record</template>
        <template v-else-if="total !== null && total <= perPage">{{ total }} records</template>
        <template v-else-if="total !== null">Showing {{ from }} to {{ to }} of {{ total }} records</template>
      </div>
      <Pagination
          v-if="!isLoading"
          :currentPage="currentPage"
          :totalPages="lastPage"
          @change="onPageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import http from '@/plugins/axios'
import Pagination from '@/components/Reusable/Pagination.vue'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const props = defineProps({
  url: { type: String, required: true },
  dataColumns: { type: Array, required: true },
  option: { type: Object, default: () => ({}) },
})

const tableRef     = ref(null)
const isLoading    = ref(true)
const searchStr    = ref('')
const currentPage  = ref(1)
const lastPage     = ref(1)
const total        = ref(null)
const from         = ref(null)
const to           = ref(null)
const perPage      = ref(10)

const isFilterable = computed(() => props.option.filterable ?? false)

function onSearch() {
  tableRef.value?.setFilter(searchStr.value)
}

function onLoaded() {
  isLoading.value = false
}

function onPageChange(page) {
  tableRef.value?.setPage(page)
}

function defaultResponseAdapter(response) {
  // requestFunction's catch returns undefined on HTTP errors — handle that
  // safely so pagination doesn't end up with NaN (→ "Invalid array length").
  const res          = response?.data?.data
  const pp           = parseInt(res?.per_page) || 10
  perPage.value      = pp
  total.value        = res?.total        ?? null
  from.value         = res?.from         ?? null
  to.value           = res?.to           ?? 0
  currentPage.value  = res?.current_page ?? 1
  lastPage.value     = res?.last_page    ?? 1
  isLoading.value    = false
  return {
    data: res?.data ?? [],
    count: res?.total ?? 0,
  }
}

const tableData = computed(() => tableRef.value?.data ?? [])

onMounted(() => {
  if (globalThis.emitter) globalThis.emitter.on('refreshData', () => tableRef.value?.refresh())
})
onBeforeUnmount(() => {
  if (globalThis.emitter) globalThis.emitter.off('refreshData')
})
defineExpose({ currentPage, lastPage, paginate: onPageChange, total, from, to, perPage, isLoading, tableData, refresh: () => tableRef.value?.refresh() })

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
  requestAdapter: makeRequestAdapter('created_at'),
  requestFunction(data) {
    return http.get(props.url, { params: data }).catch(e => {
      console.error('[DataTable] request error:', e)
    })
  },
  ...props.option,
  pagination: { chunk: 10, edge: false },
  responseAdapter: (response) => {
    const callerAdapter = props.option.responseAdapter
    const result = callerAdapter ? callerAdapter(response) : defaultResponseAdapter(response)
    if (callerAdapter) {
      const res = response?.data?.data
      if (res) {
        const pp          = parseInt(res.per_page) || 10
        perPage.value     = pp
        total.value       = res.total        ?? null
        from.value        = res.from         ?? null
        to.value          = res.to           ?? null
        currentPage.value = res.current_page ?? 1
        lastPage.value    = res.last_page    ?? 1
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

.table_btn {
  background: #dcdcdc !important;
  color: #333 !important;
  border-color: #c6c7c8 !important;
}
.user-table-actions .btn { margin-right: 4px; }
.dt-success { color: #017701 !important; }
.dt-danger { color: red !important; }
</style>
