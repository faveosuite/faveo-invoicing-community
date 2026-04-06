<template>

	<div id="datatable">

		<v-server-table v-if="showTable" ref="table" :url="endPoint" :columns="columnArray" :options="optionsObj" @error="onError" 
			:name="scroll_to" @loaded="onLoaded" @pagination="onPage" :style="styleObj" :key="counter">

			<div slot="h__id"> <input type="checkbox" v-model='allMarked' @change="toggleAll()"></div>

			<template slot="id" slot-scope="props">

			      <input type="checkbox" @change="unmarkAll()" :value="props.row.id" v-model="markedRows">
			</template>

      <template v-if="isLoading" slot="afterTable">

        <custom-loader></custom-loader>

      </template>

		</v-server-table>

		<div v-if="!showTable && error_message" class="callout callout-danger bg-danger">

			<p><i class="fa fa-exclamation-triangle"> </i> {{lang(error_message)}}</p>
		</div>

		<div v-if="loading" class="row faveo-datatable-loader">

			<loader :animation-duration="4000"/>
		</div>
	</div>
</template>

<script>

	import Vue from 'vue';

	import { errorHandler } from 'helpers/responseHandler';

	import {ServerTable, ClientTable, Event} from 'vue-tables-2';

	if (Vue && typeof Vue.use === 'function') { Vue.use(ServerTable); }
	
	export default {

		name:'datatable',

		props:{
		
			dataColumns: {type: Array, required: true},

			option:{type:Object},

			url:{type:String},

			tickets : {type:Function,default : ()=>[]},

			scroll_to : { type: String, default : ''},
			
			componentTitle : { type : String, default : ''},

			alertComponentName: { type: String, default: '' }
		},

		data(){

			return{

				columnArray : this.dataColumns,

				optionsObj : this.option,

				endPoint : this.url,

				showTable : true,

				error_message : '',
				
				loading : true,

				markedRows : [],

				allMarked : false,

        styleObj : { display : 'none'},

        isLoading: false,

        counter : 0
			}
		},

		watch: {

			url(newValue,oldValue){
				
				this.endPoint = newValue
			},

			option(newValue,oldValue){
			
				this.optionsObj = newValue
			},
			
			dataColumns(newValue,oldValue){
			
				this.columnArray = newValue
			},
			
			markedRows(newValue,oldValue){
			
				this.tickets(this.markedRows)
			
				return newValue
			}
    },

    mounted() {
     
      Event.$on('vue-tables.'+this.scroll_to+'.loading', () => {
     
        this.isLoading = true;
      });
     
      Event.$on('vue-tables.'+this.scroll_to+'.loaded', () => {
     
        this.isLoading = false;
      });
     
      Event.$on('vue-tables.'+this.scroll_to+'.error', () => {
     
        this.isLoading = false;
      });

      Event.$on('vue-tables.'+this.scroll_to+'.filter', () => {
        
        this.$refs.table.setLimit(10)
      });
    },

		created(){
			
			window.eventHub.$on(this.componentTitle+'refreshData',this.onUpdate)
			
			window.eventHub.$on(this.componentTitle+'uncheckCheckbox',this.unselectAll)
		},

		methods :{

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
				
				if(this.alertComponentName) {
				
					errorHandler(data, this.alertComponentName)
				
				} else {
				
					this.error_message = data.response.data.message;

					this.onUpdate();
				}

				if(data.response.data.message == 'Invalid API end-point'){

					this.$refs.table.refresh();

					this.showTable = true;

					this.loading = true;
				} else {

					this.showTable = false
			
					this.loading = false	
				}
			},

			onLoaded(){
				
				this.loading = false
			
				this.styleObj.display = 'block'
			},

			onPage(data){
				
				if(data !== 1){
					
					var elmnt = document.getElementById(this.scroll_to);
	  			
	  			elmnt.scrollIntoView({ behavior : "smooth"});
	  		}
  		}
		}
	};

</script>

<style type="text/css">

	table{ border-collapse: collapse;}

	#datatable{ padding-top:10px !important;padding-bottom: 45px !important;}

	.VueTables__search-field input{ width : 300px !important;}

	.VueTables__search{ float : right;} 

	.VueTables__limit{ float : left !important; }
	
	.VuePagination__pagination{ margin-top: -5px !important;margin-right: -15px !important;float: right !important; }
	
	.VuePagination{ margin-top: 10px !important;}
	
	.VuePagination__count { display: contents !important;margin-top: -10px !important; }
	
	.VuePagination .text-center{ text-align: left !important;width: inherit;}
	
	.VueTables__columns-dropdown button{background:none !important;border:1px solid #d4d3d3 !important; margin-right:5px !important;}

	.VueTables__columns-dropdown ul li a input{
		width: 13px; height: 13px; padding: 0; margin:0; vertical-align: bottom; position: relative; top: -3px; 
		overflow: hidden;
	}
	
	.faveo-datatable-loader { margin-top: 30px;margin-bottom: 30px;}

	.VueTables__table { font-size: 14px !important;}
</style>
