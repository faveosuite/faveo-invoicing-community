<template>

	<div class="actions-row">

		<router-link v-if="data.edit_url" class="btn btn-light btn-act" :to="data.edit_url" v-tooltip="trans('edit')">

			<i class="fas fa-edit"></i>
		</router-link> &nbsp;

        <span v-tooltip="disabled ? trans('default_field_is_not_restore') : trans('restore')">

			<button v-if="data.restore_url" class="btn btn-light btn-act me-2" @click="showRestoreModalMethod"
                    :disabled="disabled">

				<i class="fas fa-sync-alt"></i>
			</button>
		</span>

		<span v-tooltip="disabled ? trans('default_field_is_not_deletable') : data.tooltip ? trans(data.tooltip) : trans('delte')">

			<button v-if="data.delete_url" class="btn btn-light btn-act" @click="showModalMethod"
				:disabled="disabled">

				<i class="fas fa-trash"></i>
			</button>
		</span>

        <router-link v-if="data.view_url" class="btn btn-light btn-act ms-2" :to="data.view_url" v-tooltip="trans('view')">

            <i class="fas fa-eye"></i>
        </router-link>

		<transition name="modal">

		 	<delete-modal v-if="showModal" :onClose="onClose" :showModal="showModal" :deleteUrl="data.delete_url"
		 		:alertComponentName="alert" :keyVal="data.keyVal" :idVal="data.idVal" :modalMessage="data.modalMessage"
                :btnTitle="data.btnTitle" :softDelete="data.softDelete" :modalTitle="data.modalTitle">

		 	</delete-modal>
		</transition>

        <transition name="modal">

            <delete-modal v-if="showRestoreModal" :onClose="onClose" :showModal="showRestoreModal" :deleteUrl="data.restore_url"
                          :alertComponentName="alert" :keyVal="data.keyVal" :idVal="data.idVal" :modalMessage="data.restoreModalMessage"
                          :btnTitle="data.restoreBtnTitle" :modalTitle="data.restoreModalTitle">

            </delete-modal>
        </transition>
	</div>
</template>

<script type="text/javascript">

	import axios from '@/plugins/axios';

	import {boolean} from '../../helpers/extraLogics'

    import DeleteModal from './DeleteModal.vue'

	export default {

		name:"data-table-actions",

		props: {

			data : { type : Object, required : true },
		},

		data(){

			return{

				showModal : false,

                showRestoreModal : false,

				alert : ''
			}
		},

		computed : {

			disabled() {

				return boolean(this.data.is_default)
			}
		},

		created() {

			this.updateAlert()
		},

		methods:{

			updateAlert() {

				this.alert = this.data.alertComponentName ? this.data.alertComponentName : 'dataTableModal';
			},

            showRestoreModalMethod() {

                this.showRestoreModal = this.data.is_default ? false : true;
            },

			showModalMethod(){

				this.showModal = this.data.is_default ? false : true;
			},

			onClose(){

		    	this.showModal = false;

                this.showRestoreModal = false;

		    	this.$store.dispatch('unsetValidationError');
		  	},
		},

		components:{

			'delete-modal': DeleteModal
		}
	};
</script>

<style scoped>

	.actions-row a { padding-right: 10px;padding-left: 10px; }

	.btn-act { background: gainsboro !important; }
</style>

