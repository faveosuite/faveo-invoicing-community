<template>

	<form-field-template :label="label" :name="name" :classname="classname" :hint="hint" :required="required"
		:labelStyle="labelStyle">

		<select
			class="form-control"
			v-model="selectedValue"
			v-bind:name="name"
			v-on:change="onChange(selectedValue, name)"
			:id="id"
			:style="inputStyle"
			:disabled="disabled"
		>

			<option value="" v-if="!hideEmptySelect">Select</option>
			<option v-for="element in elements" v-bind:value="element.id" :key="element.id">
					{{subString(element.name)}}
			</option>

		</select>
	</form-field-template>
</template>

<script type="text/javascript">

import { getSubStringValue } from '../../../helpers/extraLogics';

import FormFieldTemplate from "./FormFieldTemplate.vue";

export default {

	name: "static-select",

	props: {
		/**
		 * label that has to be displayed above the field
		 * @type {String}
		 */
		label: { type: String, required: true },

		/**
		 * list of the elements that has to be displayed
		 * it must be an array of objects with fields as 'id' and 'name'
		 * for eg. [{id:'', name:''},{id:'', name:''}]
		 * @type {Array}
		 */
		elements: { type: Array, required: true },

		/**
		 * the name of the state in parent class, for looking into the vuex store for errors
		 * @type {String | Number}
		 */
		name: { type: [String, Number], required: true }, //the name of the state in parent class

		/**
		 * selected value of the field.
		 * list of already selected element ids that has to be displayed
		 * @type {Number|Boolean}
		 */
		value: { type: [String, Number], required: true },

		/**
		 * name of the class (for css, not really required)
		 * @type {String}
		 */
		classname: { type: String, default: "" }, //class name (optional)

		/**
		 * The function which will be called as soon as value of the field changes
		 * It should have two arguments `value` and `name`
		 *     `value` will be the updated value of the field
		 *     `name` will be thw name of the state in the parent class
		 *
		 * An example function :
		 *         onChange(value, name){
		 *             this[name]= selectedValue
		 *         }
		 *
		 * @type {Function}
		 */
		onChange: { type: Function, Required: true },

		/**
		 * Hint regarding what the field is about (it will be shown as tooltip message)
		 * @type {String}
		 */
		hint: { type: String, default: "" },

		/**
		 * Whether the given field is required or not.
		 * If passed yes, an asterik will be displayed after the label
		 * @type {Boolean}
		 */
		required: { type: Boolean, default: false },

		/**
		 *if you want to hide the empty select,
		 *if passed true then the empty select would be hidden , orelse default is set to false
		 */
		hideEmptySelect: {type: Boolean, default: false},

		/**
		 * Id of the text field
		 * @type {String|Number}
		 */
		id : {type: [String, Number], default:'static-select'},

		inputStyle : { type : Object, default : ()=>{}},

		/**
		 * For showing Field label.
		 * If passed display as none, the label will not be displayed
		 * @type {Object}
		 */
		labelStyle:{type:Object, default: function () { return { }}},

		strlength : {type : [String,Number], default : 100},

		disabled: {type: Boolean, required: false, default: false},
	},

	data: () => ({
		/**
		 * Value that has been selected
		 * @type {String}
		 */
		// this.value === "" ? null : this.value
		selectedValue: ""
	}),

	watch: {
		value(newvalue) {
			this.selectedValue = newvalue;
		}
	},

	mounted() {
		//initialising input state with prop data
		this.selectedValue = this.value;
	},

	methods : {

		subString(value){

			return getSubStringValue(value,parseInt(this.strlength))
		},
	},

	components: {
		"form-field-template": FormFieldTemplate
	}
};
</script>
