<template>
    <form-field-template
        :name="name"
        :classname="classname"
        :label="label"
        :hint="hint"
        :required="required"
        :labelStyle="labelStyle"
        :showNewButton="showNewButton"
        :onClickEvent="getFieldName"
        :actionBtn="actionBtn"
        :isInlineForm="isInlineForm"
        :showPreview="showPreview"
        :tipRule="tipRule"
    >
        <div class="dynamic-select">

            <v-select
                :name="name"
                :options="listElements"
                :class="['faveo-dynamic-select']"
                v-model:modelValue="selectedValue"
                ref="faveoDynamicSelect"
                :label="optionLabel"
                :multiple="multiple"
                :placeholder="placeholder"
                :filterable="false"
                :disabled="disabled"
                :clearable="clearable"
                :searchable="searchable"
                :closeOnSelect="closeOnSelect"
                :taggable="taggable"
                :dir="dir"
                @update:modelValue="onValueChange"
                @search="onSearch"
                @open="connectObserver"
                @close="disconnectObserver"
                @search:blur="clearSearchQuery"
                :select-on-key-codes="[188, 13]"
            >
                <template #no-options="{search, searching}">
                    <template v-if="searching">No results found for <em>{{ search }}</em>. </template>
                    <template v-else>
<!--                        <span v-if="!isLoading && !hasNextPage">No options found.</span>-->
                        <loader v-if="!hasNextPage || isLoading" :duration="4000" :size="25"></loader>
                    </template>
                </template>

                <template #option="option">
                    <div class="d-center" :title="option[optionLabel]">
                        <!-- For images -->
                        <faveo-image-element v-if="option.profile_pic" id="faveo-dynamic-select-img" :source-url="option.profile_pic" />
                        <!-- For Icons -->
                        <i v-if="option.icon_class" :class="option.icon_class" aria-hidden="true"></i>
                        {{ subString(option[optionLabel]) }}
                    </div>
                </template>

                <template #selected-option="option">
                    <div class="selected d-center" :title="option[optionLabel]">
                        <!-- For images -->
                        <faveo-image-element v-if="option.profile_pic" id="faveo-dynamic-select-img" :source-url="option.profile_pic" />
                        <!-- For Icons -->
                        <i v-if="option.icon_class" :class="option.icon_class" aria-hidden="true"></i>
                        {{ subString(option[optionLabel]) }}
                    </div>
                </template>

                <template #list-footer v-if="hasNextPage">
                    <li ref="dynamicSelectLoader" class="loader-area">

                    </li>
                </template>
            </v-select>
        </div>
    </form-field-template>
</template>

<script type="text/javascript">

import vSelect from "vue-select";

import { getSubStringValue, boolean } from '../../../helpers/extraLogics';

import { errorHandler } from '../../../helpers/responseHandler'

import _ from 'lodash';

import axios from "axios";

import FormFieldTemplate from "./FormFieldTemplate.vue";
import FaveoImageElement from "../FaveoImageElement.vue";

export default {

    name: "DatatableDynamicSelect",

    props: {

        name: { type: [String, Number],Required: true},

        hint: {type: String,default: ""},

        required: {type: Boolean,default: false},

        classname: {type: String,default: ""},

        id: {type: [String, Number],default: "dynamic-select"},

        labelStyle: {type: Object,default: function () {return {};}},

        onChange: {type: Function,required: true},

        autoscroll: {type: Boolean, default: true},

        showNewButton: { type: Boolean, default: false },

        value: {type: [Object, String],default: null},

        dir: {type: String, default: "auto"},

        elements: {type: Array,default () {return []}},

        disabled: {type: Boolean,default: false},

        clearable: {type: Boolean,default: true},

        searchable: {type: Boolean,default: true},

        multiple: {type: Boolean,default: false},

        placeholder: {type: String,default: "Search or Select"},

        clearSearchOnSelect: {type: Boolean,default: true},

        closeOnSelect: {type: Boolean,default: true},

        optionLabel: {type: String,default: "name"},

        apiEndpoint: {type: String, default: null},

        apiParameters: {type: [Object, Map], default: () => {}},

        showPreview : { type : [String, Object], default : '' },

        isInlineForm: { type: Boolean, default: false },

        label: {type: String,default: ''},

        actionBtn: { type: Object, default: () => null },

        taggable: {type: Boolean,default: false},

        strlength: {type: [Number, String],default: 40},

        tipRule : { type : [Number, Boolean], default : false }
    },

    data: () => ({

        selectedValue: null,

        searchQuery : undefined,

        listElements: [],

        page: 0,

        observer: null,

        nextPageUrl: '',

        isLoading: false,
    }),

    mounted() {

        if (this.apiEndpoint) {

            this.flushAndRestart();
        }
        //initialising input state with prop data
        this.selectedValue = this.value;
    },

    computed: {

        hasNextPage() {

            return Boolean( this.nextPageUrl !== null && this.apiEndpoint);
        }
    },

    created() {
        this.updateProperties()
    },

    watch : {

        elements(newValue, oldValue) {
            if (newValue) {
                this.listElements = newValue;
            }
        },

        apiEndpoint(newValue, oldValue) {
            if (boolean(newValue)) {
                this.flushAndRestart();
            }
        },

        apiParameters(newValue, oldValue) {
            if (boolean(newValue)) {
                this.flushAndRestart();
            }
        },

        value(newValues, oldValues) {

            this.selectedValue = newValues;
        }
    },

    beforeMount() {

        this.listElements = Boolean(this.elements) ? this.elements : [];
    },

    methods : {

        updateProperties() {

            this.flushAndRestart('update');
        },

        getFieldName(name){

            this.onButtonClick(name)
        },

        subString(value){

            return getSubStringValue(value,parseInt(this.strlength))
        },

        onSearch(searchQuery) {

            this.searchQuery = searchQuery;

            this.page = 1;

            this.search();
        },

        flushAndRestart(from = '') {

            this.disconnectObserver();

            this.resetProperties(from);

            this.observer = new IntersectionObserver(this.infiniteScroll);
        },

        onValueChange(value) {

            this.onChange(value, this.name);
        },

        search: _.debounce(function () {

            this.apiEndpoint ? this.getListFromServer(true) : this.filterListElements();

        }, 350),

        filterListElements() {

            this.listElements = this.elements.filter((element) => element[this.optionLabel].toLowerCase().includes(this.searchQuery.toLowerCase()));
        },

        getListFromServer(isRefresh, target) {

            if (!boolean(this.apiEndpoint)) return;

            this.isLoading = true;

            axios.get(this.apiEndpoint, {
                params: this.getApiParams()
            })
                .then(response => {
                    this.postApiResponseOperations(response.data.data, isRefresh, target);
                })
                .catch(error => {
                    // if request fails, there won't be any next page, so nextPageUrl will be marked as null
                    this.nextPageUrl = null
                    errorHandler(error);
                })
                .finally(() => {
                    this.isLoading = false;
                })
        },

        async postApiResponseOperations(responseData, isRefresh, target) {
            try {
                this.nextPageUrl = responseData.next_page_url;
                if (isRefresh) {
                    this.listElements = responseData.data;
                } else {
                    if(target) {
                        var ul = target.offsetParent;
                        if(ul){ var scrollTop = target.offsetParent.scrollTop;	}
                    }
                    this.listElements.push(...responseData.data);
                    await this.$nextTick();
                    if(target && ul) { ul.scrollTop = scrollTop;	}
                }
            } catch (error) {
                console.error(error);
            }
        },

        getApiParams() {

            let apiParams = boolean(this.apiParameters) ? this.apiParameters : {};

            let params = JSON.parse(JSON.stringify(apiParams));
            params['search_query'] = this.searchQuery;
            params['page'] = this.page || undefined;
            params['paginate'] = 1;
            return params;
        },

        clearSearchQuery() {

            this.$refs.faveoDynamicSelect.onEscape()
        },

        infiniteScroll([{ isIntersecting, target }]) {

            if (isIntersecting) {

                this.page += 1;

                this.getListFromServer(false, target);
            }
        },

        async connectObserver() {

            if (this.hasNextPage) {

                await this.$nextTick();

                this.observer.observe(this.$refs.dynamicSelectLoader);
            }
        },

        disconnectObserver() {

            if (this.observer) {

                this.observer.disconnect();
            }
        },

        resetProperties(from = '') {
            this.listElements = this.elements.length ? this.listElements : [];
            this.page = 0;
            this.observer = null;
            this.nextPageUrl = '';
            this.searchQuery = undefined;
            this.isLoading = false;
            // if(from != 'update') { this.selectedValue = null; }
        }

    },

    components: {

        'v-select': vSelect,

        "form-field-template": FormFieldTemplate,

        "faveo-image-element" : FaveoImageElement
    }
};
</script>
