<template>
    <FormFieldTemplate
        :name="name" :classname="classname" :label="label" :hint="hint"
        :required="required" :labelStyle="labelStyle" :showNewButton="showNewButton"
        :onClickEvent="getFieldName" :actionBtn="actionBtn" :isInlineForm="isInlineForm"
        :showPreview="showPreview" :tipRule="tipRule"
    >
        <div class="dynamic-select">
            <v-select
                :name="name" :options="listElements" :class="['faveo-dynamic-select']"
                v-model:modelValue="selectedValue" ref="faveoDynamicSelect"
                :label="optionLabel" :multiple="multiple" :placeholder="placeholder"
                :filterable="false" :disabled="disabled" :clearable="clearable"
                :searchable="searchable" :closeOnSelect="closeOnSelect"
                :taggable="taggable" :dir="dir"
                @update:modelValue="onValueChange" @search="onSearch"
                @open="connectObserver" @close="disconnectObserver"
                @search:blur="clearSearchQuery"
                :select-on-key-codes="[188, 13]"
            >
                <template #no-options="{ search, searching }">
                    <template v-if="searching">No results found for <em>{{ search }}</em>.</template>
                    <template v-else>
                        <loader v-if="!hasNextPage || isLoading" :duration="4000" :size="25" />
                    </template>
                </template>

                <template #option="option">
                    <div class="d-center" :title="option[optionLabel]">
                        <FaveoImageElement v-if="option.profile_pic" id="faveo-dynamic-select-img" :source-url="option.profile_pic" />
                        <i v-if="option.icon_class" :class="option.icon_class" aria-hidden="true"></i>
                        {{ subString(option[optionLabel]) }}
                    </div>
                </template>

                <template #selected-option="option">
                    <div class="selected d-center" :title="option[optionLabel]">
                        <FaveoImageElement v-if="option.profile_pic" id="faveo-dynamic-select-img" :source-url="option.profile_pic" />
                        <i v-if="option.icon_class" :class="option.icon_class" aria-hidden="true"></i>
                        {{ subString(option[optionLabel]) }}
                    </div>
                </template>

                <template #list-footer v-if="hasNextPage">
                    <li ref="dynamicSelectLoader" class="loader-area"></li>
                </template>
            </v-select>
        </div>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeMount, nextTick } from 'vue'
import vSelect from 'vue-select'
import _ from 'lodash'
import axios from '@/plugins/axios'
import { getSubStringValue, boolean } from '@/helpers/extraLogics'
import { errorHandler } from '@/helpers/responseHandler'
import FormFieldTemplate from './FormFieldTemplate.vue'
import FaveoImageElement from '../FaveoImageElement.vue'

const props = defineProps({
    name:               { type: [String, Number], required: true },
    hint:               { type: String,           default: '' },
    required:           { type: Boolean,          default: false },
    classname:          { type: String,           default: '' },
    id:                 { type: [String, Number], default: 'dynamic-select' },
    labelStyle:         { type: Object,           default: () => ({}) },
    onChange:           { type: Function,         required: true },
    autoscroll:         { type: Boolean,          default: true },
    showNewButton:      { type: Boolean,          default: false },
    value:              { type: [Object, String], default: null },
    dir:                { type: String,           default: 'auto' },
    elements:           { type: Array,            default: () => [] },
    disabled:           { type: Boolean,          default: false },
    clearable:          { type: Boolean,          default: true },
    searchable:         { type: Boolean,          default: true },
    multiple:           { type: Boolean,          default: false },
    placeholder:        { type: String,           default: 'Search or Select' },
    clearSearchOnSelect: { type: Boolean,         default: true },
    closeOnSelect:      { type: Boolean,          default: true },
    optionLabel:        { type: String,           default: 'name' },
    apiEndpoint:        { type: String,           default: null },
    apiParameters:      { type: [Object, Map],    default: () => ({}) },
    showPreview:        { type: [String, Object],  default: '' },
    isInlineForm:       { type: Boolean,          default: false },
    label:              { type: String,           default: '' },
    actionBtn:          { type: Object,           default: () => null },
    taggable:           { type: Boolean,          default: false },
    strlength:          { type: [Number, String], default: 40 },
    tipRule:            { type: [Number, Boolean], default: false },
})

const faveoDynamicSelect = ref(null)
const dynamicSelectLoader = ref(null)

const selectedValue = ref(null)
const searchQuery = ref(undefined)
const listElements = ref([])
const page = ref(0)
const nextPageUrl = ref('')
const isLoading = ref(false)
let observer = null

const hasNextPage = computed(() => Boolean(nextPageUrl.value !== null && props.apiEndpoint))

onBeforeMount(() => {
    listElements.value = Boolean(props.elements) ? props.elements : []
})

onMounted(() => {
    if (props.apiEndpoint) flushAndRestart()
    selectedValue.value = props.value
    flushAndRestart('update')
})

watch(() => props.elements, (newValue) => {
    if (newValue) listElements.value = newValue
})

watch(() => props.apiEndpoint, (newValue) => {
    if (boolean(newValue)) flushAndRestart()
})

watch(() => props.apiParameters, (newValue) => {
    if (boolean(newValue)) flushAndRestart()
})

watch(() => props.value, (newValues) => {
    selectedValue.value = newValues
})

function getFieldName(name) {
    // onButtonClick not defined in original — preserve as noop
}

function subString(value) {
    return getSubStringValue(value, parseInt(props.strlength))
}

const search = _.debounce(function () {
    props.apiEndpoint ? getListFromServer(true) : filterListElements()
}, 350)

function onSearch(q) {
    searchQuery.value = q
    page.value = 1
    search()
}

function flushAndRestart(from = '') {
    disconnectObserver()
    resetProperties(from)
    observer = new IntersectionObserver(infiniteScroll)
}

function onValueChange(value) {
    props.onChange(value, props.name)
}

function filterListElements() {
    listElements.value = props.elements.filter(el =>
        el[props.optionLabel].toLowerCase().includes(searchQuery.value.toLowerCase())
    )
}

function getListFromServer(isRefresh, target) {
    if (!boolean(props.apiEndpoint)) return
    isLoading.value = true
    axios.get(props.apiEndpoint, { params: getApiParams() })
        .then(response => postApiResponseOperations(response.data.data, isRefresh, target))
        .catch(error => {
            nextPageUrl.value = null
            errorHandler(error)
        })
        .finally(() => { isLoading.value = false })
}

async function postApiResponseOperations(responseData, isRefresh, target) {
    try {
        nextPageUrl.value = responseData.next_page_url
        if (isRefresh) {
            listElements.value = responseData.data
        } else {
            let scrollTop
            let ul
            if (target) {
                ul = target.offsetParent
                if (ul) scrollTop = ul.scrollTop
            }
            listElements.value.push(...responseData.data)
            await nextTick()
            if (target && ul) ul.scrollTop = scrollTop
        }
    } catch (error) {
        console.error(error)
    }
}

function getApiParams() {
    const apiParams = boolean(props.apiParameters) ? props.apiParameters : {}
    const params = JSON.parse(JSON.stringify(apiParams))
    params.search_query = searchQuery.value
    params.page = page.value || undefined
    params.paginate = 1
    return params
}

function clearSearchQuery() {
    faveoDynamicSelect.value?.onEscape()
}

function infiniteScroll([{ isIntersecting, target }]) {
    if (isIntersecting) {
        page.value += 1
        getListFromServer(false, target)
    }
}

async function connectObserver() {
    if (hasNextPage.value) {
        await nextTick()
        observer?.observe(dynamicSelectLoader.value)
    }
}

function disconnectObserver() {
    observer?.disconnect()
}

function resetProperties(from = '') {
    listElements.value = props.elements.length ? listElements.value : []
    page.value = 0
    observer = null
    nextPageUrl.value = ''
    searchQuery.value = undefined
    isLoading.value = false
}
</script>
