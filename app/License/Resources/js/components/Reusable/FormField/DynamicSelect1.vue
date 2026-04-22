<template>
   <form-field-template v-if="showField"
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
			:class="['faveo-dynamic-select', classname]"
			ref="faveoDynamicSelect"
			:options="listElements"
			v-model="selectedValue"
			:label="optionLabel"
			:multiple="multiple"
			:placeholder="placeholder"
			:autoscroll="autoscroll"
			:disabled="disabled"
			:clearable="clearable"
			:searchable="searchable"
			:transition="transition"
			:closeOnSelect="closeOnSelect"
			:taggable="taggable"
			:pushTags="pushTags"
			:filterable="false"
			:noDrop="noDrop"
			:inputId="id"
			:dir="dir"
			@input="onValueChange"
			@search="onSearch"
			@open="connectObserver"
			@close="disconnectObserver"
			@search:blur="clearSearchQuery"
			>
				<template slot="no-options" slot-scope="{search, searching}">
					<template v-if="searching">No results found for <em>{{ search }}</em>. </template>
					<template v-else>
						<span v-if="!isLoading && !hasNextPage">No options found.</span>
						<loader v-if="isLoading && !hasNextPage" :duration="4000" :size="25"></loader>
					</template>
				</template>

				<template slot="option" slot-scope="option">
					<div class="d-center" :title="option[optionLabel]">
						<!-- For images -->
						<faveo-image-element v-if="option.profile_pic" id="faveo-dynamic-select-img" :source-url="option.profile_pic" />
						<!-- For Icons -->
						<i v-if="option.icon_class" :class="option.icon_class" aria-hidden="true"></i>
						{{ subString(option[optionLabel]) }}
					</div>
				</template>

				<template slot="selected-option" slot-scope="option">
					<div class="selected d-center" :title="option[optionLabel]">
						<!-- For images -->
						<faveo-image-element v-if="option.profile_pic" id="faveo-dynamic-select-img" :source-url="option.profile_pic" />
						<!-- For Icons -->
						<i v-if="option.icon_class" :class="option.icon_class" aria-hidden="true"></i>
						{{ subString(option[optionLabel]) }}
					</div>
				</template>

				<template slot="list-footer" v-if="hasNextPage">
					<li ref="dynamicSelectLoader" class="loader-area">
						<loader :duration="4000" :size="25"></loader>
					</li>
				</template>
			</v-select>
		</div>
   </form-field-template>
</template>

<script>

import vSelect from "vue-select";

import axios from "axios";

import FormFieldTemplate from "./FormFieldTemplate.vue";

import { errorHandler } from "../../../helpers/responseHandler";

import { getSubStringValue, boolean } from "../../../helpers/extraLogics";

import _ from 'lodash';

export default {

	name: "dynamic-select",

	props: {

		name: {
			type: [String, Number],
			Required: true
		},

		hint: {
			type: String,
			default: ""
		},

		required: {
			type: Boolean,
			default: false
		},

		classname: {
			type: String,
			default: ""
		},

		id: {
			type: [String, Number],
			default: "dynamic-select"
		},

		labelStyle: {
			type: Object,
			default: function () {
				return {};
			}
		},

		onChange: {
			type: Function,
			required: true
		},

		autoscroll: {
			type: Boolean,
			default: true
		},

		value: {
			type: [Object, String],
			default: null
		},

		elements: {
			type: Array,
			default () {
				return [];
			}
		},

		disabled: {
			type: Boolean,
			default: false
		},

		clearable: {
			type: Boolean,
			default: true
		},

		searchable: {
			type: Boolean,
			default: true
		},

		multiple: {
			type: Boolean,
			default: false
		},

		apiEndpoint: {
			type: String,
			default: null
		},

		apiParameters: {
			type: [Object],
			default: () => {}
		},

		placeholder: {
			type: String,
			default: "Search or Select"
		},

		transition: {
			type: String,
			default: "fade"
		},

		clearSearchOnSelect: {
			type: Boolean,
			default: true
		},

		closeOnSelect: {
			type: Boolean,
			default: true
		},

		optionLabel: {
			type: String,
			default: "name"
		},

		label: {
			type: String,
			default: ''
		},

		taggable: {
			type: Boolean,
			default: false
		},

		pushTags: {
			type: Boolean,
			default: false
		},

		strlength: {
			type: [Number, String],
			default: 40
		},

		noDrop: {
			type: Boolean,
			default: false
		},

		inputId: {
			type: String
		},

		dir: {
			type: String,
			default: "auto"
		},

		showNewButton: { type: Boolean, default: false },

    	onClickEvent : { type : Function },

    	onButtonClick: { type : Function},

		isInlineForm: { type: Boolean, default: false },

		rules: { type: String, default: '' },

		actionBtn: { type: Object, default: () => null },

		showPreview : { type : [String, Object], default : '' },

		//FOR TOOLTIP POSITION
		tipRule : { type : [Number, Boolean], default : false }
	},

	data: () => {

		return {

			listElements: [],

			page: 0,

			observer: null,

			nextPageUrl: '',

			searchQuery: undefined,

			isLoading: false,

			selectedValue: null,

            showField:false
		};
	},

	beforeMount() {

		this.listElements = Boolean(this.elements) ? this.elements : [];
	},

	mounted() {

		if (this.apiEndpoint) {

			this.flushAndRestart();
		}

		this.selectedValue = this.value;

        // Intentionally added 1 sec timeout for avoiding initial errors on page load
        setTimeout(()=>{

            this.showField = true
        },1);
	},

	created() {

		window.eventHub.$on('updateDynamicSelect',this.updateProperties)
	},

	computed: {

		hasNextPage() {

			return Boolean( this.nextPageUrl !== null && this.apiEndpoint);
		}
	},

	methods: {

		updateProperties() {

			this.flushAndRestart('update');
		},

		getFieldName(name){

			this.onButtonClick(name)
		},

		subString(value) {

			return getSubStringValue(value, parseInt(this.strlength))
		},


		flushAndRestart(from = '') {

			this.disconnectObserver();

			this.resetProperties(from);

			this.observer = new IntersectionObserver(this.infiniteScroll);
		},

		onSearch(searchQuery) {

			this.searchQuery = searchQuery;

			this.page = 1;

			this.search();
		},

		onValueChange(value) {

			this.onChange(value, this.name);
		},

		search: _.debounce(function () {

			if (this.apiEndpoint) {

				this.getListFromServer(true);

			} else {

				this.filterListElements();
			}
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
			params['search-query'] = this.searchQuery;
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
			if(from != 'update') { this.selectedValue = null; }
		}

	},

	watch: {

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


		// TODO This `value` watcher needs refactoring
		value(newValues, oldValues) {
			this.selectedValue = newValues;

			// if taggable is there, handle value seperatey
			// loop over all values and see if a value is string
			// if it is a string make an object out of it with name as value and id as value
			if (this.taggable) {
				if (newValues === null) return;
				let modifiedValues = newValues.map(value => {
					let valueObj;
					if (typeof value === 'string') {
						valueObj = {}
						valueObj.id = value;
						valueObj[this.optionLabel] = value;
					}
					return valueObj || value;
				});
				// call onChange only if value if 2 objects are different to avoid infinite loop
				if (JSON.stringify(modifiedValues) !== JSON.stringify(newValues)) {
					this.onChange(modifiedValues, this.name)
				}
			}
		}
	},

	components: {
		'v-select': vSelect,
		'form-field-template': FormFieldTemplate,
		// 'faveo-image-element': require('components/Reusable/FaveoImageElement').default,
	}
};
</script>

<style scoped>
#faveo-dynamic-select-img {
	border: 1px solid transparent;
	width: 18px;
	border-radius: 10px;
	margin-top: -1px;
}
.loader-area {
	padding-top: 3px;
	height: 37px;
}
</style>
