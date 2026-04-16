<template>

	<form-field-template :label="label" :name="name" :classname="classname" :hint="hint" :required="required"
		:labelStyle="labelStyle">

		<div class="dynamic-select">

			<v-select
			    :options="listElements"
			    :class="['faveo-dynamic-select']"
			    v-model:modelValue="selectedValue"
				:label="optionLabel"
				:multiple="multiple"
				:placeholder="placeholder"
				:disabled="disabled"
				:clearable="clearable"
				:searchable="searchable"
				:closeOnSelect="closeOnSelect"
				:taggable="taggable"
				@update:modelValue="onValueChange"
				@search="onSearch"
			>

			</v-select>
		</div>
	</form-field-template>
</template>

<script type="text/javascript">

import vSelect from "vue-select";

import { getSubStringValue, boolean } from '../../../helpers/extraLogics';

import _ from 'lodash';

import axios from "axios";

import FormFieldTemplate from "./FormFieldTemplate.vue";

export default {

	name: "static-select",

	props: {

		name: { type: [String, Number],Required: true},

		hint: {type: String,default: ""},

		required: {type: Boolean,default: false},

		classname: {type: String,default: ""},

		id: {type: [String, Number],default: "dynamic-select"},

		labelStyle: {type: Object,default: function () {return {};}},

		onChange: {type: Function,required: true},

		value: {type: [Object, String],default: null},

		elements: {type: Array,default () {return []}},

		disabled: {type: Boolean,default: false},

		clearable: {type: Boolean,default: true},

		searchable: {type: Boolean,default: true},

		multiple: {type: Boolean,default: false},

		placeholder: {type: String,default: "Search or Select"},

		clearSearchOnSelect: {type: Boolean,default: true},

		closeOnSelect: {type: Boolean,default: true},

		optionLabel: {type: String,default: "name"},

		label: {type: String,default: ''},

		taggable: {type: Boolean,default: false},

		strlength: {type: [Number, String],default: 40},
	},

	data: () => ({

		selectedValue: null,

		searchQuery : undefined,

		listElements: [],
	}),

	mounted() {
		//initialising input state with prop data
		this.selectedValue = this.value;
	},

	watch : {

		elements(newValue, oldValue) {
			if (newValue) {
				this.listElements = newValue;
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

		subString(value){

			return getSubStringValue(value,parseInt(this.strlength))
		},

		onSearch(searchQuery) {

			this.searchQuery = searchQuery;

			this.search();
		},

		onValueChange(value) {

			this.onChange(value, this.name);
		},

		search: _.debounce(function () {

			this.filterListElements();
		}, 350),

		filterListElements() {

			this.listElements = this.elements.filter((element) => element[this.optionLabel].toLowerCase().includes(this.searchQuery.toLowerCase()));
		},
	},

	components: {

		'v-select': vSelect,

		"form-field-template": FormFieldTemplate
	}
};
</script>
