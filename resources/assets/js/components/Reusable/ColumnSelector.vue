<template>
    <div class="btn-group column-selector">
        <button
            type="button"
            class="btn btn-light border dropdown-toggle"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false"
            @click="ensureLoaded"
        >
            <i class="fas fa-columns"></i>&nbsp;{{ __('message.selected_columns') }}
        </button>

        <div class="dropdown-menu dropdown-menu-end column-selector-menu">
            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <ul class="column-selector-list" @dragover.prevent>
                    <li
                        v-for="(col, index) in toggleable"
                        :key="col.key"
                        class="column-selector-item"
                        :class="{ 'is-dragging': dragIndex === index }"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragenter.prevent="onDragEnter(index)"
                        @dragend="onDragEnd"
                    >
                        <label class="column-selector-label mb-0">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                v-model="col.is_visible"
                                :disabled="col.is_visible && visibleCount === 1"
                                v-tooltip="col.is_visible && visibleCount === 1
                                    ? __('message.atleast_one_column_needs_to_be_selected')
                                    : ''"
                            />
                            <span class="column-selector-text">{{ displayLabel(col) }}</span>
                        </label>
                        <span class="column-selector-drag" :title="__('message.move')">
                            <i class="fas fa-arrows-alt-v"></i>
                        </span>
                    </li>
                </ul>

                <div class="column-selector-footer">
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        :disabled="saving"
                        @click="apply"
                    >
                        <i class="fas fa-sync" :class="{ 'fa-spin': saving }"></i>
                        {{ __('message.apply') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const props = defineProps({
    // report_columns.type — e.g. 'users', 'orders', 'invoices'
    entityType: { type: String, required: true },
    // Locked columns pinned to the front (always visible, not draggable/toggleable).
    pinStart: { type: Array, default: () => ['checkbox'] },
    // Locked columns pinned to the end.
    pinEnd: { type: Array, default: () => ['action'] },
    // Optional display-label overrides keyed by column key.
    labels: { type: Object, default: () => ({}) },
    componentName: { type: String, default: 'column-selector' },
})

// Emitted with the ordered list of visible column keys (incl. pinned), on
// initial load and after every successful Apply.
const emit = defineEmits(['change'])

const columns = ref([])      // full ordered metadata from the API
const loading = ref(false)
const saving = ref(false)
const loaded = ref(false)
const dragIndex = ref(null)

const locked = computed(() => [...props.pinStart, ...props.pinEnd])

// Only non-locked columns are user-toggleable / draggable.
const toggleable = computed(() => columns.value.filter(c => !locked.value.includes(c.key)))

const visibleCount = computed(() => toggleable.value.filter(c => c.is_visible).length)

function displayLabel(col) {
    if (props.labels[col.key]) return props.labels[col.key]
    const label = col.label || col.key
    return label.charAt(0).toUpperCase() + label.slice(1)
}

// Ordered visible keys: pinned-start, then the toggleable columns in their
// current order, then pinned-end — but only for columns that actually exist.
function visibleKeys() {
    const present = new Set(columns.value.map(c => c.key))
    const start = props.pinStart.filter(k => present.has(k))
    const end = props.pinEnd.filter(k => present.has(k))
    const middle = toggleable.value.filter(c => c.is_visible).map(c => c.key)

    return [...start, ...middle, ...end]
}

async function ensureLoaded() {
    if (loaded.value || loading.value) return
    await load()
}

async function load() {
    loading.value = true
    try {
        const res = await http.get(`/get-columns`, {
            params: { entity_type: props.entityType },
        })
        columns.value = (res.data?.data?.columns ?? []).map(c => ({
            ...c,
            is_visible: !!c.is_visible,
        }))
        loaded.value = true
        emit('change', visibleKeys())
    } catch (e) {
        errorHandler(e, props.componentName)
    } finally {
        loading.value = false
    }
}

function onDragStart(index) {
    dragIndex.value = index
}

function onDragEnter(index) {
    if (dragIndex.value === null || dragIndex.value === index) return

    // Reorder within the toggleable slice, then splice back into `columns`
    // so the pinned columns keep their canonical positions.
    const list = [...toggleable.value]
    const [moved] = list.splice(dragIndex.value, 1)
    list.splice(index, 0, moved)
    dragIndex.value = index

    const lockedCols = columns.value.filter(c => locked.value.includes(c.key))
    columns.value = [...list, ...lockedCols]
}

function onDragEnd() {
    dragIndex.value = null
}

async function apply() {
    if (saving.value) return
    saving.value = true
    try {
        const res = await http.post(`/save-columns`, {
            entity_type: props.entityType,
            selected_columns: visibleKeys(),
        })
        successHandler(res, props.componentName)
        emit('change', visibleKeys())
    } catch (e) {
        errorHandler(e, props.componentName)
    } finally {
        saving.value = false
    }
}

// Load preferences immediately so the table renders saved columns on mount.
load()
</script>

<style scoped>
.column-selector-menu {
    min-width: 240px;
    padding: 8px;
}

.column-selector-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 280px;
    overflow-y: auto;
    overflow-x: hidden;
}

.column-selector-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 8px;
    border-radius: 4px;
    cursor: default;
}

.column-selector-item:hover {
    background-color: #f4f6f9;
}

.column-selector-item.is-dragging {
    opacity: 0.5;
    background-color: #e9ecef;
}

.column-selector-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    flex: 1;
}

.column-selector-text {
    user-select: none;
}

.column-selector-drag {
    cursor: move;
    visibility: hidden;
    color: #6c757d;
    padding-left: 8px;
}

.column-selector-item:hover .column-selector-drag {
    visibility: visible;
}

.column-selector-footer {
    position: sticky;
    bottom: 0;
    background-color: #fff;
    padding-top: 8px;
    margin-top: 4px;
    border-top: 1px solid #eee;
    text-align: right;
}
</style>
