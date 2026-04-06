<template>

    <div class="datatable">

        <div v-if="showTable" class="row float-end me-0 mb-3">

            <div v-if="option.filterable" class="col-sm-auto pe-0">

                <input type="text" class="form-control globe-search" v-model="search_str"
                       @keyup.enter="checkFile()" :style="inputStyle" :placeholder="trans('type_and_enter_to_search')">
            </div>

            <div v-if="showColumn" class="dropdown col-sm-auto pf-0">
                <button v-tooltip="lang('select_columns')" class="btn btn-light mf-2 h-100 btn-sm dropdown-toggle px-2" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-columns"></i> {{lang('columns')}}</button>

                <div class="dropdown-menu dropdown-column-menu p-3" aria-labelledby="dropdownMenuButton" style="">
                    <div v-for="column in selectedColumns" :key="column" class="form-check dropdown-item">
                        <input
                            class="form-check-input"
                            :disabled="selectedColumnsArray.length===2 && selectedColumnsArray.includes(column)"
                            :checked="selectedColumnsArray.includes(column)" v-model="selectedColumnsArray"
                            style="cursor: pointer"
                            type="checkbox"
                            id="columnCheckbox"
                            :value="column"
                        />
                        <label class="form-check-label" for="columnCheckbox">{{lang(column)}}</label>
                    </div>

                    <div class="dropdown-item text-end column-button">
                        <button @click="updateColumns" id="updateButton" class="btn btn-primary">{{lang('apply')}}</button>
                    </div>
                </div>
            </div>
        </div>

        <v-server-table v-if="showTable" ref="table" :onLimit="onLimitChange" :url="endPoint" :columns="columnArray" :options="optionsObject" @error="onError" @loaded="onLoaded" :key="counter">

            <template v-if="isLoading && !disableLoader" #afterTable>

                <custom-loader loaderType='clip-loader' :color="color"></custom-loader>
            </template>

            <template v-slot:actions="props">

                <table-actions :data="props.row"></table-actions>
            </template>

        </v-server-table>

        <div v-if="!showTable && error_message" class="callout callout-danger bg-danger">

            <p><i class="fa fa-exclamation-triangle"> </i> {{(error_message)}}</p>
        </div>

        <div v-if="loading && !disableLoader" class="row faveo-datatable-loader">

            <loader :duration="4000" :color="color" :size="60"/>
        </div>

        <div class="pagination-container">

            <div v-if="showTable && !loading && show_pagination">
                <template v-if="total == 1">
                    {{trans('one_record')}}
                </template>
                <template v-if="total > 1 && total <= 10">
                    {{ total }} {{trans('records')}}
                </template>
                <template v-if="total > 10">
                    {{trans('showing')}} {{ from }} to {{ to }} of {{ total }} {{trans('records')}}
                </template>
            </div>

            <div v-if="showTable && !loading && show_pagination && total > 10" class="float-end mr-0 pt-2">

                <simple-pagination :next_page="next_page" :prev_page="prev_page" :onPagination="onPagination">

                </simple-pagination>
            </div>
        </div>


    </div>
</template>

<script>

import { errorHandler } from '../../helpers/responseHandler';
import PageLoader from '../Reusable/Loader.vue';
import SimplePagination from "../Reusable/SimplePagination.vue";
import {lang} from "../../helpers/extraLogics";

export default {

    name:'datatable',

    description:'Datatable that handles formatting queries in a way that it makes it easy to integrate with external APIs',

    props:{

        /**
         * Columns in the datatable.
         * Columns should atleast have title and field as
         * @return {Array}  columns in the datatable.(array of objects)
         */
        dataColumns: {type: Array, required: true},

        allColumns: {type: Array, required: false},

        option:{type:Object},

        url:{type:String},

        tickets : {type:Function,default : ()=>[]},

        scroll_to : { type: String, default : ''},

        componentTitle : { type : String, default : ''},

        color : { type : String, default : '#1d78ff'},

        /**
         * Alert component name to dispatch alert box
         */
        alertComponentName: { type: String, default: 'datatable' },

        inputStyle: {type:Object, default :  ()=>{} },

        show_pagination : { type : Boolean, default : false },

        disableLoader : {type: Boolean, default: false},

        showColumn: {type: Boolean, default: false}
    },

    data(){

        return{

            columnArray : this.dataColumns,

            selectedColumns: this.allColumns,

            selectedColumnsArray: this.dataColumns,

            optionsObj : this.option,

            endPoint : this.url,

            showTable : true,

            error_message : '',

            loading : false,

            markedRows : [],

            allMarked : false,

            styleObj : { display : 'none'},

            isLoading: false,

            counter : 0,

            search_str : '',

            next_page : '',

            prev_page : '',

            total: '',

            to: '',

            from: ''
        }
    },

    watch: {

        url(newValue,oldValue){

            this.endPoint = newValue

            this.counter++;
        },

        option(newValue,oldValue){

            this.optionsObj = newValue
        },

        dataColumns(newValue,oldValue){

            this.columnArray = newValue

            this.selectedColumnsArray = newValue

            if(this.show_pagination){

                this.endPoint = this.url;
            }
        },

        markedRows(newValue,oldValue){

            this.tickets(this.markedRows)

            return newValue
        }
    },

    created() {
        window.emitter.on('refreshData', this.onUpdate)
    },

    computed : {

        optionsObject() {

            const self = this;

            self.optionsObj.texts = {
                noResults: lang('no_matching_records'),
                loading: this.loading = true
            };

            if(self.optionsObj.headings && self.optionsObj.headings.hasOwnProperty('id')){

                self.optionsObj.headings.id = function(){

                    return self.h('input',{

                        type : 'checkbox',

                        modelValue : self.allMarked,

                        onChange(event) {

                            self.allMarked = event.target.checked;

                            self.toggleAll()
                        }
                    })
                }
            }

            this.optionsObj.debounce = 700;

            if(this.show_pagination) {

                this.optionsObj['pagination'] = { show : false };
            }

            return this.optionsObj
        }
    },

    methods :{

        lang,

        extractHref(orderUrl) {

            const parser = new DOMParser();

            // Parse the HTML string

            const parsedHtml = parser.parseFromString(orderUrl, 'text/html');

            // Get the root element of the parsed HTML

            const htmlElement = parsedHtml.documentElement;

            return htmlElement.querySelector('#href_link') ?? ''
        },

        checkFile() {

            this.endPoint = this.updateQueryParam(this.endPoint, "page", 1) // so when we search on any page it should default to page 1 on search

            this.$refs.table.setLimit(10);

            this.$refs.table.setFilter(this.search_str);

            this.loading = true;
        },

        unmarkAll() {

            this.allMarked = false;
        },

        unselectAll() {

            this.allMarked = false;

            this.markedRows = [];
        },

        toggleAll() {

            this.markedRows = this.allMarked?this.$refs.table.data.map(row=>row.id):[];
        },

        onUpdate() {

            this.counter++;
        },

        onError(data){

            if(data.response.data.message === 'Too Many Attempts.') {

                this.loading = true;

                errorHandler(data, this.alertComponentName)

                setTimeout(() => {

                    this.loading = false;

                    this.onPagination('prev');
                }, 60000)

                this.url=null;
            }

            if(this.alertComponentName && data && data.response && data.response.status) {

                errorHandler(data, this.alertComponentName)

            } else {

                if(data && data.response) {

                    this.error_message = data.response.data.message;

                    this.onUpdate();
                }
            }

            if(data && data.response && data.response.data.message === 'Invalid API end-point'){

                this.$refs.table.refresh();

                this.showTable = true;

                this.loading = true;
            } else {

                this.showTable = true

                this.loading = false
            }
        },

        onLoaded(resp){

            if(this.show_pagination){

                this.next_page = resp.data.data.next_page_url;

                this.prev_page = resp.data.data.prev_page_url;

                this.total = resp.data.data.total;

                this.to = resp.data.data.to;

                this.from = resp.data.data.from;
            }

            this.loading = false

            this.styleObj.display = 'block'
        },

        onPagination(direction) {

            const targetUrl = direction === 'next' ? this.next_page : this.prev_page;

            if (targetUrl) {

                const url = new URL(targetUrl);

                const pageValue = url.searchParams.get("page");

                this.endPoint = this.updateQueryParam(this.endPoint, "page", pageValue);
            }
        },

        updateQueryParam(url, param, value) {

            url = url.replace(/([?&])page=\d+/, '');

            const separator = url.includes('?') ? '&' : '?';

            return `${url}${separator}${param}=${value}`;
        },

        onLimitChange() {

            this.endPoint = this.updateQueryParam(this.endPoint, "page", 1)
        },

        updateColumns() {

            this.$emit('columns', this.selectedColumnsArray)
        }
    },

    components : {

        'simple-pagination': SimplePagination,

        'loader': PageLoader
    }
};

</script>

<style type="text/css">

.VueTables__row a {
    text-decoration: none !important;
}

table{
    border-collapse: collapse;
}
.datatable{
    padding-top:10px !important;
}
.VueTables__search-field input, .globe-search{
    width : 300px !important;
}

.VueTables__search{
    float : right;
}

.datatable .VueTables__search{
    display: none;
}

.VueTables__limit{
    float : left !important;
    margin-left: -7px;
}
.VuePagination__pagination{
    margin-top: -5px !important;
    margin-right: -15px !important;
    float: right !important;
}
.VuePagination{
    margin-top: 10px !important;
}
.VuePagination__count {
    display: contents !important;
    margin-top: -10px !important;
}
.VuePagination .text-center{
    text-align: left !important;
    width: inherit;
}
/*.undefined{*/
/*	margin-left: 10px !important;*/
/*}*/
.VueTables__columns-dropdown button {
    background: none !important;
    border: 1px solid #d4d3d3 !important;
    margin-right: 5px !important;
}
.VueTables__columns-dropdown ul li a input{
    width: 13px; height: 13px; padding: 0; margin:0; vertical-align: bottom; position: relative; top: -3px;
    overflow: hidden;
}
.overlay-loader {
    position: absolute;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: white;
    opacity: 0.8;
    filter: blur(5px);
}

.clip-loader {
    position: absolute;
    left: 50%;
    right: 50%;
    bottom: 70%;
    top: 30%;
}
.faveo-datatable-loader {
    margin-top: 30px;
    margin-bottom: 30px;
}
.VueTables__table {
    font-size: 14px !important;
}

/*.VueTables__table th{
    font-weight: 500 !important;
}*/
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.VueTables__sort-icon {
    padding-left: 10px !important;
    cursor: pointer !important;
}

.VueTables__limit-field .form-control{
    cursor: pointer!important;
    appearance: auto!important;
}

.VueTables__limit-field label{
    display: none !important;
}
.dropdown-column-menu{
    left: -150px;
}
.dropdown-item.active, .dropdown-item:active {
    color: black;
    text-decoration: none;
    background: none
}

</style>
