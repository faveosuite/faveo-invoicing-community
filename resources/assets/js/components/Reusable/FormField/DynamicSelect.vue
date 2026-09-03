<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
            <ToolTip v-if="tooltip" :message="tooltip" size="small" />
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
            :create-option="createOption || undefined"
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
                <slot name="option" v-bind="option">
                    <span :title="option[optionLabel]">{{ subString(option[optionLabel]) }}</span>
                </slot>
            </template>
            <template #selected-option="option">
                <span :title="option[optionLabel]">{{ subString(option[optionLabel]) }}</span>
            </template>
            <template #list-footer>
                <ul style="list-style:none;margin:0;padding:0"><li v-show="hasNextPage" ref="loaderRef" class="vs__load-trigger" /></ul>
            </template>
            <template #spinner>
                <loader v-if="isLoading" class="loader-area" :duration="4000" :size="20" />
            </template>
            <template #no-options="{ search }">
                <!-- A slot whose branches are ALL false renders only Vue
                     comment placeholders, which Vue treats as "no content"
                     and silently swaps in vue-select's own hardcoded
                     "Sorry, no matching options." text instead — hence the
                     trailing v-else, so the loading state renders a real
                     (empty) node instead of falling through to that. -->
                <span v-if="search">No results for <em>{{ search }}</em></span>
                <span v-else-if="!isLoading">No options found</span>
                <span v-else></span>
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
import ToolTip from '@/components/Reusable/Tooltip.vue'
import { getSubStringValue } from '@/helpers/extraLogics'

const props = defineProps({
    name:          { type: String, required: true },
    label:         { type: String, default: '' },
    tooltip:       { type: String, default: '' },
    createOption:  { type: Function, default: null },
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
    // Same fix favMer's DynamicSelect uses: truncate the label text itself
    // (title attribute carries the full value on hover). This is a
    // defensive ceiling on the DOM text, not the real visual truncation —
    // the CSS ellipsis on .vs__selected handles clipping precisely to
    // whatever width the field actually has, so keep this generous.
    strlength:     { type: [Number, String], default: 60 },
})

const fieldError = computed(() => props.error ?? '')

function subString(value) {
    return getSubStringValue(value, parseInt(props.strlength))
}

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
/* vue-select's own default renders placeholder text in the same solid
   color as a real selected value (--vs-search-input-color/-placeholder-color
   both default to "inherit", so the placeholder inherits ambient dark text).
   favMer fades its placeholder via opacity, not color — matching that here. */
.faveo-dynamic-select input.vs__search::placeholder {
    opacity: 0.3;
}

.faveo-dynamic-select .vs__dropdown-toggle {
    width: 100%;
    line-height: 1.4;
    display: flex;
    padding: 0;
    border: 1px solid rgba(60, 60, 60, 0.26);
    overflow-y: auto;
    min-height: 35px;
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
    margin: 3px;
    /* The #selected-option slot truncates the label by character count
       (see subString() in the script) only as a defensive ceiling — the
       real, pixel-precise truncation is this ellipsis, which fills
       whatever width the chip actually gets and clips exactly at the
       edge. That's why the char cap (DynamicSelect's `strlength` prop)
       should stay generous: too tight and it cuts the text short of the
       clear icon before the ellipsis ever gets a chance to run.
       min-width:0 + flex-basis:0 (via `flex: ... 0%`) matter too: a flex
       item's default min-width:auto keeps it at its content width even
       with overflow:hidden set, and flex-wrap lines break using each
       item's *unshrunk* hypothetical size (content-width when
       flex-basis:auto) — so without both, the search input next to it still
       gets forced onto its own line, growing the field.
       flex-grow is deliberately lopsided (20 vs. the search input's
       default 1): without it the two split the row ~50/50, leaving the
       text stopping halfway across the field with empty space before the
       clear icon instead of running up to it. Pushing the ratio higher
       than ~20 buys almost nothing more — the remaining sliver is the
       search input's own padding/margin (its click target), not flex
       share left to claim. */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
    flex: 20 1 0%;
}

.faveo-dynamic-select .vs__selected span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

.faveo-dynamic-select .vs__dropdown-menu {
    max-height: 200px;
}

/* Same reasoning as .vs__selected above: subString()'s char-count cap is a
   defensive ceiling, not the real fit — this option row has no flex fight
   to win, but a generous strlength (needed so the *selected* chip isn't
   cut short) means an option's untruncated text can still be wider than
   this narrow menu, forcing back the horizontal scrollbar. Ellipsis clips
   it to the row's actual pixel width regardless of the char count. */
.faveo-dynamic-select .vs__dropdown-option {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    /* See .vs__selected above: flex items default to min-width:auto, so
       without this the chip's content width pushes this container — and
       the field — wider/taller than the toggle's border. */
    min-width: 0;
}

.faveo-dynamic-select .vs__actions {
    padding: 0 5px 0 3px;
}

.faveo-dynamic-select .vs__clear {
    position: relative;
}

.faveo-dynamic-select.vs--loading .vs__open-indicator {
    opacity: 1;
}

.faveo-dynamic-select .vs__actions .loader-area {
    order: -1;
    margin-right: 5px;
}

.faveo-dynamic-select .vs__load-trigger {
    height: 1px;
    padding: 0;
    overflow: hidden;
}

.faveo-dynamic-select .vs__search,
.faveo-dynamic-select .vs__search:focus {
    margin: 5px;
}
</style>
