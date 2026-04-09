<template>

	<modal v-if="showModal" :showModal="showModal" :onClose="onClose" :containerStyle="containerStyle">

		<template #title>

			<h4>{{lang('turn_off_authenticator')}}</h4>
		</template>

		<template #fields>

			<span v-if=!showPasswordRequiredFrom> {{lang('turn_off_authenticator_setup')}}</span>

			<div v-else>
				<text-field :label="lang('to_continue_first_verify')" :value="password" type="password" name="password"
							placehold="Enter Password..." :keyupListener="triggerEvent" :inputStyle="inputStyle" :onChange="onChange" classname="col-sm-12">

				</text-field>
			</div>

			<div class="row" v-if="loading">

				<custom-loader :duration="4000" :color="color"></custom-loader>
			</div>
		</template>

		<template #controls>

			<button class="btn pull-right float-start" :class="showPasswordRequiredFrom ?  from === 'client'? 'btn-custom':'btn-primary' : 'btn-danger'"
                    @click="submit()" :style="showPasswordRequiredFrom ? buttonStyle : ''">

				<i class="fas" :class="showPasswordRequiredFrom ? 'fa-check' : 'fa-power-off'"></i> {{ showPasswordRequiredFrom ? lang('validate') : lang('turn_off')}}
			</button>
		</template>
	</modal>
</template>

<script>

	import {errorHandler, successHandler} from '../../helpers/responseHandler'

	import axios from 'axios'

    import { lang } from '../../helpers/extraLogics'

    import TextField from "../../../js/components/Reusable/FormField/TextField.vue";

	export default {

		name : 'remove-authenticator-modal',

		description : 'Remove Authenticator Modal component',

		props:{

			showModal:{type:Boolean,default:false},

			onClose:{type: Function},

			alertName : { type : String, default : 'edit_profile'},

			id : { type : [String, Number], default : ''},

			color : { type : String, default : '#1d78ff'},

			removedFA : { type : Function, default : ()=>{}},

			from : { type : String, default : '' },

			buttonStyle : { type : Object, default : ()=> {}},

			inputStyle : { type : Object, default : ()=> {}},

		},

		data(){

			return {

				isDisabled:true,

				containerStyle:{ width:'600px' },

				loading:false,

				showPasswordRequiredFrom: false,

				password: ''
			}
		},

       methods:{

            lang,

			validatePass(){

				this.verifyLoader=true;

				const data = {}

				data['password'] = this.password;

				axios.post('/api/admin/verify/password', data).then(res=>{

					this.loading = false;

					this.onRemove();

				}).catch(err=>{

					this.loading = false;

					this.$store.dispatch('setValidationError', {'password' : 'Incorrect password.'})

				})
			},

			onRemove(){

				this.loading = true;

				this.isDisabled = true;

				axios.post('/api/admin/2fa/disable').then(res=>{

                    this.$emit('updateEditData', true)

					this.onClose();

					this.removedFA();

					successHandler(res, this.alertName)

					this.loading = false;

					this.isDisabled = false;

				}).catch(err=>{

					if (err.response.data.message === 'password_confirmation_required') {
						this.showPasswordRequiredFrom = true;
					} else {
						this.onClose();

						errorHandler(err, this.alertName);
					}


					this.isDisabled = false;

					this.loading = false;
				})
			},
			onChange(value, name) {

				this[name] = value;
			},

			submit() {

				if(this.showPasswordRequiredFrom) {
					this.validatePass()
				} else {
					this.onRemove()
				}

			},
      triggerEvent(event) {
        var key = event.which || event.keyCode;
        if (key === 13) { // 13 is enter
          this.validatePass();
        }
      },
		},

		components:{
			'text-field': TextField,
		}
	};
</script>
