<template>

    <form-field-template :label="label" :labelStyle="labelStyle" :name="name" :classname="classname" :hint="hint" :required="required"
                         :showNewButton="showNewButton" :newBtnName="newBtnName" :onClickEvent="getActionEvent">

        <div v-if="type === 'textarea'">

            <textarea :id="id ? id : 'text-field-'+name" :name="name"
                      :class="['form-control', inputClass]"
                      :maxlength="length"
                      :type="type"
                      v-model="changedValue"
                      v-on:input="onChange(changedValue, name)"
                      :cols="columns"
                      :rows="rows"
                      :style="inputStyle"
                      :placeholder="placehold">

            </textarea>
        </div>
        <div v-else-if="type === 'password'">
            <div class="password-input">
                <input :id="id ? id : 'text-field-'+name" :name="name"
                       :class="['form-control', inputClass]"
                       :type="showPassword ? 'text' : 'password'"
                       :disabled="disabled"
                       :style="inputStyle"
                       v-model="changedValue"
                       v-on:input="onChange(changedValue, name)"
                       @keyup="keyupListener($event,name)"
                       @keydown="keydownListener($event,name)"
                       @keypress="keypressEvt($event,name)"
                       @paste="pasteEvt($event,name)"
                       :placeholder="placehold"
                       :maxlength="max ? max : undefined"
                />
                <i class="eye-icon fa" :class="!showPassword ? 'fa-eye-slash' : 'fa-eye'" @click="togglePasswordVisibility"></i>
            </div>
        </div>
        <div v-else>
            <input :id="id ? id : 'text-field-'+name" :name="name"
                   :class="['form-control', inputClass]"
                   :type="type"
                   :disabled="disabled"
                   :style="inputStyle"
                   v-model="changedValue"
                   v-on:input="onChange(changedValue, name)"
                   @keyup="keyupListener($event,name)"
                   @keydown="keydownListener($event,name)"
                   @keypress="keypressEvt($event,name)"
                   @paste="pasteEvt($event,name)"
                   :placeholder="placehold"
                   :maxlength="max ? max : undefined"
            />
        </div>

    </form-field-template>
</template>

<script>

import { boolean } from "../../../helpers/extraLogics";

import FormFieldTemplate from './FormFieldTemplate.vue'

export default {

    name: "text-field",

    props: {

        label: { type: String, default : '' },

        hint: { type: String, default: "" }, //for tooltip message

        value: { type: [String,null], required: true },

        name: { type: String, required: true },

        type: { type: String, default: "text" },

        onChange: { type: Function, Required: true },

        classname: { type: String, default: "" },

        required: { type: Boolean, default: false },

        length: {type: [Number,String], default: 2000},

        keyupListener: { type: Function , default : ()=>{} },

        keydownListener: { type: Function , default : ()=>{} },

        keypressEvt: { type: Function ,  default : ()=>{} },

        pasteEvt: { type: Function ,  default : ()=>{} },

        labelStyle:{type:Object},

        placehold : { type: String, default : 'Enter a value'},

        id : {type: [String,Number], default:''},

        disabled : { type : Boolean, default : false},

        columns : { type : [String, Number], default : ''},

        inputStyle : { type : Object, default : ()=>{}},

        max : { type : [Number, String] , default : ''},

        rows : { type : [Number, String] , default : ''},

        cols : { type : [Number, String] , default : ''},

        inputClass : { type : String, default : ''},

        showWordLimit: { type: Boolean, default: false },

        showNewButton: { type: Boolean, default: false },

        newBtnName : { type : String, default : '' },

        onNewButtonClick: { type : Function, default : ()=>{}},
    },

    data() {
        return {
            changedValue: this.value,
            showPassword: false // Initially password is not visible
        };
    },

    mounted() {
        this.changedValue = this.value;
    },

    watch: {
        value(newVal) {
            this.changedValue = newVal;
        }
    },

    methods : {
        getActionEvent(name){
            this.onNewButtonClick(name)
        },
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword; // Toggle visibility
        }
    },

    components: {
        "form-field-template": FormFieldTemplate,
    }
};
</script>

<style>
.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}
.eye-icon {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 2; /* Ensure the eye icon is above the input field */
}

.password-input {
    position: relative;
}

input[type="password"]::-ms-reveal {
    display: none;
}

</style>
