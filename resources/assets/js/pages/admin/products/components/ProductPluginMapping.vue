<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">Plugins</h4>
            <div class="card-tools">
                <button class="btn btn-tool" v-tooltip="__('message.filter')" @click="showFilter = !showFilter">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <PluginFilter
                :show="showFilter"
                @apply="onFilterApply"
                @reset="onFilterReset"
                @close="showFilter = false"
            />
            <div v-if="loading" class="text-center py-4"><loader /></div>
            <template v-else>
                <div class="d-flex align-items-center gap-2 float-end mb-3">
                    <input
                        v-model="searchStr"
                        type="text"
                        class="form-control globe-search"
                        placeholder="Type and press enter to search..."
                        @keyup.enter="onSearch"
                    />
                </div>
                <v-client-table
                    ref="tableRef"
                    :data="tableData"
                    :columns="columns"
                    :options="tableOptions"
                />
            </template>
        </div>
        <div class="card-footer">
            <action-button action="update" :loading="saving" @click="save" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, h } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import PluginFilter from './PluginFilter.vue'

const props = defineProps({
    productId: { type: [String, Number], required: true },
    baseUrl:   { type: String, required: true },
})

const COMPONENT = 'products-edit'

const loading      = ref(true)
const saving       = ref(false)
const showFilter   = ref(false)
const activeFilter = ref({})
const searchStr    = ref('')
const tableRef     = ref(null)
const allPlugins   = ref([])
const bundled      = ref([])
const compatible   = ref([])

const bundledSet    = computed(() => new Set(bundled.value))
const compatibleSet = computed(() => new Set(compatible.value))

const tableData = computed(() => {
    const rows = allPlugins.value.map(p => ({
        id:            p.id,
        name:          p.name,
        is_bundled:    bundledSet.value.has(p.id),
        is_compatible: compatibleSet.value.has(p.id),
    }))

    const status = activeFilter.value.status
    if (!status) return rows
    if (status === 'bundled')    return rows.filter(p => p.is_bundled)
    if (status === 'compatible') return rows.filter(p => p.is_compatible)
    if (status === 'not_mapped') return rows.filter(p => !p.is_bundled && !p.is_compatible)
    return rows
})

const columns = ['name', 'is_compatible', 'is_bundled']

const tableOptions = reactive({
    skin: 'table table-hover table-striped table-bordered',
    headings: {
        name:          'Plugin',
        is_compatible: () => h('span', {}, [
            'Compatible',
            h(Tooltip, { message: __('message.plugin_compatible_tooltip') }),
        ]),
        is_bundled: () => h('span', {}, [
            'Bundled',
            h(Tooltip, { message: __('message.plugin_bundled_tooltip') }),
        ]),
    },
    columnsClasses: {
        is_bundled:    'text-center',
        is_compatible: 'text-center',
    },
    templates: {
        is_bundled:    (f, row) => h('input', { type: 'checkbox', class: 'form-check-input', checked: row.is_bundled,    onChange: () => toggle('bundled', row.id) }),
        is_compatible: (f, row) => h('input', { type: 'checkbox', class: 'form-check-input', checked: row.is_compatible, disabled: row.is_bundled, onChange: () => toggle('compatible', row.id) }),
    },
    sortable:      ['name'],
    filterable:    ['name'],
    pagination:    { show: false },
    perPage:       1,
    perPageValues: [],
    stickyHeader:  true,
    orderBy:       { column: 'name', ascending: true },
})

function onSearch() {
    tableRef.value?.setFilter(searchStr.value)
}

function onFilterApply(params) {
    activeFilter.value      = params
    showFilter.value        = false
    tableOptions.perPage    = tableData.value.length || 1
}

function onFilterReset() {
    activeFilter.value      = {}
    tableOptions.perPage    = allPlugins.value.length || 1
}

function toggle(type, id) {
    const list = type === 'bundled' ? bundled : compatible
    const idx  = list.value.indexOf(id)
    if (idx === -1) {
        list.value.push(id)
        if (type === 'bundled' && !compatibleSet.value.has(id)) {
            compatible.value.push(id)
        }
    } else {
        list.value.splice(idx, 1)
    }
}

onMounted(async () => {
    try {
        const res     = await http.get(`${props.baseUrl}/product/${props.productId}/plugins`)
        const plugins = res.data?.data?.plugins ?? []
        allPlugins.value     = plugins
        bundled.value        = plugins.filter(p => p.is_bundled).map(p => p.id)
        compatible.value     = plugins.filter(p => p.is_compatible).map(p => p.id)
        tableOptions.perPage = plugins.length || 1
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.post(`${props.baseUrl}/product/${props.productId}/plugins`, {
            bundled:    bundled.value,
            compatible: compatible.value,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
:deep(.table-responsive) {
    max-height: 420px;
    overflow-y: auto;
}

:deep(.VueTables__table thead th) {
    position: sticky;
    top: 0;
    z-index: 1;
    background-color: var(--bs-body-bg) !important;
    border-top: none;
}

:deep(.VueTables__search) {
    display: none;
}
</style>
