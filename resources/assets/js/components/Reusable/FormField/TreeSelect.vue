<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <treeselect
            v-model="selectedValue"
            :options="listElements"
            :multiple="multiple"
            :placeholder="placeholder"
            :disabled="disabled"
            :normalizer="normalizer"
            :show-count="true"
            :flat="true"
            :search-nested="true"
            :disableBranchNodes="true"
            :max-height="200"
            noOptionsText=""
            noResultsText=""
            :class="{ 'is-invalid': error }"
            @update:modelValue="onValueChange"
            @search-change="onSearch"
            @open="connectObserver"
            @close="disconnectObserver"
        >
            <template #option-label="{ node }">
                <span>{{ node.label }}</span>
            </template>
            <template #after-list>
                <div v-if="hasNextPage || isLoading" ref="loaderRef" class="ts-loader">
                    <loader :duration="4000" :size="20" />
                </div>
            </template>
        </treeselect>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import Treeselect from 'vue3-treeselect'
import 'vue3-treeselect/dist/vue3-treeselect.css'
import http from '@/plugins/axios'
import { debounce } from 'lodash'

const props = defineProps({
    name:        { type: String, required: true },
    label:       { type: String, default: '' },
    value:       { type: [String, Number, Array, Object], default: null },
    onChange:    { type: Function, required: true },
    apiEndpoint: { type: String, default: null },
    elements:    { type: Array, default: () => [] },
    dataKey:     { type: String, default: null },
    multiple:    { type: Boolean, default: false },
    placeholder: { type: String, default: 'Search or Select' },
    disabled:    { type: Boolean, default: false },
    required:    { type: Boolean, default: false },
    error:       { type: String, default: undefined },
    apiParams:   { type: Object, default: () => ({}) },
})

const loaderRef     = ref(null)
const listElements  = ref([...props.elements])
const selectedValue = ref(toScalar(props.value))
const isLoading     = ref(false)
const nextPageUrl   = ref(null)
const searchQuery   = ref('')
const page          = ref(1)
let   observer      = null
let   initialLoaded = false

const hasNextPage = computed(() => Boolean(nextPageUrl.value && props.apiEndpoint))

// A single-select tree's v-model is a plain scalar id, not an object — but a
// caller hydrating from a saved record (e.g. an edit form) only has an
// `{ id, name }` pair to work with (the full options tree hasn't loaded yet
// to look the label up). Accept that shape here: pull out the id for the
// actual model value, and seed it into the options list so the closed-state
// label resolves immediately instead of falling back to "<value> (unknown)".
function toScalar(val) {
    if (val && typeof val === 'object' && !Array.isArray(val)) {
        if (val.id != null && !listElements.value.some(el => el.id === val.id)) {
            listElements.value = [...listElements.value, { id: val.id, name: val.name ?? val.label ?? '' }]
        }
        return val.id ?? null
    }
    return val
}

function normalizer(node) {
    return {
        id:       node.id,
        label:    node.name,
        children: node.children?.length ? node.children : undefined,
    }
}

function onValueChange(val) {
    props.onChange(val, props.name)
}

const onSearch = debounce((query) => {
    searchQuery.value = query
    page.value = 1
    if (props.apiEndpoint) loadPage(true)
}, 350)

function loadPage(isRefresh = false) {
    if (!props.apiEndpoint || isLoading.value) return Promise.resolve()
    isLoading.value = true

    return http.get(props.apiEndpoint, {
        params: {
            ...props.apiParams,
            'search-query': searchQuery.value,
            page: page.value,
            paginate: 1,
        },
    })
    .then(res => {
        const resData    = res.data?.data ?? {}
        const key        = props.dataKey ?? 'data'
        const items      = resData[key] ?? resData.data ?? []
        nextPageUrl.value = resData.next_page_url ?? null
        if (isRefresh) listElements.value = items
        else           listElements.value.push(...items)
    })
    .catch(() => { nextPageUrl.value = null })
    .finally(() => { isLoading.value = false })
}

async function connectObserver() {
    if (!props.apiEndpoint && !initialLoaded) {
        initialLoaded = true
        await loadPage(true)
    }
    if (props.apiEndpoint && !initialLoaded) {
        initialLoaded = true
        await loadPage(true)
    }
    if (hasNextPage.value && loaderRef.value && observer) {
        observer.disconnect()
        observer.observe(loaderRef.value)
    }
}

function disconnectObserver() {
    observer?.disconnect()
}

onMounted(() => {
    observer = new IntersectionObserver(([{ isIntersecting }]) => {
        if (!isIntersecting || isLoading.value || !hasNextPage.value) return
        page.value += 1
        loadPage(false)
    })
})

onBeforeUnmount(() => {
    observer?.disconnect()
    onSearch.cancel()
})

watch(() => props.value, val => { selectedValue.value = toScalar(val) ?? null })
watch(() => props.elements, val => { if (!props.apiEndpoint) listElements.value = [...val] })
watch(() => props.apiEndpoint, val => {
    if (val) { listElements.value = []; page.value = 1; nextPageUrl.value = null; initialLoaded = false; loadPage(true) }
})
</script>

<style>
.vue-treeselect__control { border: 1px solid rgba(60,60,60,0.26) !important; }
.vue-treeselect__icon-container { display: none !important; }
.vue-treeselect__option--highlight { background: #5897fb !important; color: #fff !important; }
.ts-loader { padding: 4px; display: flex; justify-content: center; }
</style>
