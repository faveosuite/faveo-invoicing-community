<template>
    <div class="mb-3">
        <label v-if="label" class="form-label text-dark">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <v-select
            ref="vsRef"
            :inputId="name"
            :options="listElements"
            v-model="selectedValue"
            :label="optionLabel"
            :multiple="multiple"
            :placeholder="placeholder"
            :disabled="disabled"
            :clearable="clearable"
            :searchable="searchable"
            :filterable="false"
            :closeOnSelect="closeOnSelect"
            :taggable="taggable"
            :pushTags="pushTags"
            :noDrop="noDrop"
            :loading="isLoading"
            :dropdownShouldOpen="dropdownShouldOpen"
            :class="['faveo-dynamic-select', { 'is-invalid': fieldError }]"
            @update:modelValue="onValueChange"
            @search="onSearch"
            @search:blur="clearSearchQuery"
            @open="onOpen"
            @close="onClose"
        >
            <template #option="option">
                <slot name="option" v-bind="option">{{ option[optionLabel] }}</slot>
            </template>
            <template #list-footer>
                <li v-show="hasNextPage" ref="loaderRef" class="vs__dropdown-option">
                    Loading more options...
                </li>
            </template>
            <template #no-options="{ search }">
                <span v-if="search">No results for <em>{{ search }}</em></span>
                <span v-else-if="!isLoading">No options found</span>
            </template>
        </v-select>
        <div v-if="fieldError" class="invalid-feedback d-block">{{ fieldError }}</div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import http from '@/plugins/axios'
import { debounce } from 'lodash'

const props = defineProps({
    name:          { type: String, required: true },
    label:         { type: String, default: '' },
    apiEndpoint:   { type: String, default: null },
    elements:      { type: Array, default: () => [] },
    multiple:      { type: Boolean, default: false },
    value:         { type: [Object, Array, String, Number], default: null },
    onChange:      { type: Function, required: true },
    placeholder:   { type: String, default: 'Search or Select' },
    clearable:     { type: Boolean, default: true },
    searchable:    { type: Boolean, default: true },
    disabled:      { type: Boolean, default: false },
    closeOnSelect: { type: Boolean, default: true },
    taggable:      { type: Boolean, default: false },
    pushTags:      { type: Boolean, default: false },
    noDrop:        { type: Boolean, default: false },
    optionLabel:   { type: String, default: 'name' },
    apiParams:     { type: Object, default: () => ({}) },
    dataKey:       { type: String, default: null },
    required:      { type: Boolean, default: false },
    error:         { type: String, default: undefined },
})

const fieldError = computed(() => props.error ?? '')

const vsRef         = ref(null)
const loaderRef     = ref(null)
const listElements  = ref([...props.elements])
const selectedValue = ref(props.value)
const isLoading     = ref(false)
const nextPageUrl   = ref(null)
const searchQuery   = ref('')
let   observer      = null
let   page          = 1
let   initialLoaded = false

const hasNextPage = computed(() => Boolean(nextPageUrl.value && props.apiEndpoint))

onMounted(() => {
    observer = new IntersectionObserver(([{ isIntersecting, target }]) => {
        if (!isIntersecting || isLoading.value || !hasNextPage.value) return

        const ul        = target.offsetParent
        const scrollTop = ul ? ul.scrollTop : 0
        page += 1

        loadPage(false)
            .then(() => nextTick())
            .then(() => { if (ul) ul.scrollTop = scrollTop })
    })
})

onBeforeUnmount(() => {
    disconnectObserver()
    onSearch.cancel()
})

function dropdownShouldOpen({ noDrop, open }) {
    return !noDrop && open
}

async function onOpen() {
    if (props.apiEndpoint && !initialLoaded) {
        initialLoaded = true
        await loadPage(true)
    }
    await nextTick()
    connectObserver()
}

function onClose() {
    disconnectObserver()
}

function connectObserver() {
    if (hasNextPage.value && loaderRef.value && observer) {
        observer.disconnect()
        observer.observe(loaderRef.value)
    }
}

function disconnectObserver() {
    if (observer) observer.disconnect()
}

function loadPage(isRefresh = false) {
    if (!props.apiEndpoint || isLoading.value) return Promise.resolve(false)
    isLoading.value = true

    return http.get(props.apiEndpoint, {
        params: {
            ...props.apiParams,
            'search-query': searchQuery.value,
            page: page,
            paginate: 1,
        },
    })
    .then(res => {
        const resData     = res.data?.data ?? {}
        const key         = props.dataKey ?? 'data'
        const items       = resData[key] ?? resData.data ?? []
        nextPageUrl.value = resData.next_page_url ?? null
        if (isRefresh) listElements.value = items
        else           listElements.value.push(...items)
        return true
    })
    .catch(() => {
        nextPageUrl.value = null
        return false
    })
    .finally(() => { isLoading.value = false })
}

function onValueChange(val) {
    props.onChange(val, props.name)
}

function clearSearchQuery() {
    if (vsRef.value) vsRef.value.onEscape()
}

const onSearch = debounce((query) => {
    searchQuery.value = query
    page = 1
    if (props.apiEndpoint) loadPage(true)
    else listElements.value = props.elements.filter(e =>
        String(e[props.optionLabel] ?? '').toLowerCase().includes(query.toLowerCase())
    )
}, 350)

function reset() {
    disconnectObserver()
    listElements.value = [...props.elements]
    page               = 1
    nextPageUrl.value  = null
    searchQuery.value  = ''
    isLoading.value    = false
    initialLoaded      = false
}

watch(() => props.value,    (val) => { selectedValue.value = val })
watch(() => props.elements, (val) => { if (!props.apiEndpoint) listElements.value = [...val] })
watch(() => props.apiEndpoint, (val) => { if (val) { reset(); loadPage(true) } })
watch(hasNextPage, async (val) => {
    if (!val) {
        disconnectObserver()
        return
    }

    if (val && vsRef.value?.open) {
        await nextTick()
        connectObserver()
    }
})
watch(
    () => props.apiParams,
    (newVal, oldVal) => {
        if (JSON.stringify(newVal) !== JSON.stringify(oldVal) && props.apiEndpoint) {
            reset()
            loadPage(true)
        }
    },
    { deep: true }
)
</script>

<style>
.faveo-dynamic-select .vs__dropdown-toggle {
    width: 100%;
    line-height: 1.4;
    display: flex;
    padding: 0;
    border: 1px solid rgba(60, 60, 60, 0.26);
    overflow-y: auto;
    min-height: 42px;
    max-height: 85px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.faveo-dynamic-select .vs__dropdown-toggle::-webkit-scrollbar {
    display: none;
}

.faveo-dynamic-select.is-invalid .vs__dropdown-toggle {
    border: 1px solid #dc3545 !important;
}

.faveo-dynamic-select .vs__selected {
    margin: 10px;
}

.faveo-dynamic-select .vs__selected .selected {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 400px;
}

.faveo-dynamic-select .vs__dropdown-menu {
    max-height: 200px;
}

.faveo-dynamic-select .vs__dropdown-menu::-webkit-scrollbar-track {
    background-color: #f1f1f1;
    border-radius: 10px;
}

.faveo-dynamic-select .vs__dropdown-menu::-webkit-scrollbar {
    width: 6px;
    background-color: #f1f1f1;
}

.faveo-dynamic-select .vs__dropdown-menu::-webkit-scrollbar-thumb {
    background-color: #c1c1c1;
    border-radius: 10px;
}

.faveo-dynamic-select .vs__selected-options {
    padding: 0;
}

.faveo-dynamic-select .vs__actions {
    padding: 0 5px 0 3px;
}

.faveo-dynamic-select .vs__clear {
    position: relative;
}

.faveo-dynamic-select .vs__search,
.faveo-dynamic-select .vs__search:focus {
    margin: 5px;
}
</style>
