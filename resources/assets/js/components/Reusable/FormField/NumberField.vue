<template>

    <form-field-template :label="label" :labelStyle="labelStyle" :name="name"  :classname="classname" :hint="hint"
        :required="required">

        <span class="inline" >

            <input class="form-control" :style="formStyle"
                :type="type"
                id="number"
                v-model="changedValue"
                v-on:input="onChange(changedValue, name)"
                @keypress="checkValue"
                @paste="onPaste"
                min="0"
                :placeholder="placeholder"
            />
        </span>
    </form-field-template>
</template>

<script>

    import FormFieldTemplate from './FormFieldTemplate.vue';

    export default {

        name:'number-field',

        props:{

            label: { type: String, default: '' },

            hint: { type:String, default: '' }, //for tooltip message

            value: { required: true },

            name: { type: String, required: true },

            type: {type: String, default: 'text'},

            onChange:{type: Function, Required: true},

            classname : {type: String, default:''},

            required: { type: Boolean, default: false},

            labelStyle:{type:Object},

            formStyle:{type:Object},

            max : { type : [String, Number], default :''},

            placeholder : { type : String, default : 'Enter a value'},

            pattern: { type: String, default: null }

        },

        data(){

            return {

                  changedValue: this.value
            }
        },

        mounted(){

            this.changedValue = this.value;
        },


        watch: {

            value(newVal) {

                this.changedValue = newVal;
            }
        },

        methods:{

            checkValue(evt) {

                evt = (evt) ? evt : window.event;

                var charCode = (evt.which) ? evt.which : evt.keyCode;

                if ((charCode > 31 && (charCode < 48 || charCode > 57))) {

                    evt.preventDefault();;

                } else {

                    return true;
                }
            },

            onPaste(evt) {

                evt = (evt) ? evt : window.event;

                if (evt.clipboardData.getData('Text').match(/[^\d]/)) {

                    evt.preventDefault();
                }
            },
        },

        components:{

            'form-field-template' : FormFieldTemplate
        }
    };
</script>

<style scoped>

    .inline {
        display:inline;
    }
    .form-control {
        display:inline !important;
    }
</style>
