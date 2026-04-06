<template>

	<div v-if="shallDisplay()" class="alert-container" id="alert">

			<div v-bind:class="['alert', classname]">

				<button type="button" v-on:click="dismiss" class="btn close float-end" data-bs-dismiss="alert" aria-label="Close" id="alert_close">×</button>

				<div id="alert-message">

					<i v-if="classname=='alert-success'" class="fa fa-check-circle alert-icon"></i>

					<i v-if="classname=='alert-danger'" class="fa fa-warning alert-icon"></i>&nbsp;

					<span v-html="message"></span>
			</div>
		</div>
	</div>
</template>

<script>

	import { mapGetters } from "vuex";

	export default {

		name: "alert",

		props: {

			componentName: { type: String, default: "" }
		},

		computed: {

			...mapGetters(["getAlertType", "getAlertMessage", "getAlertComponentName", "getAlertDuration"]),

			type: {

				get() {

					return this.getAlertType;
				}
			},

			message: {

				get() {

					return this.getAlertMessage;
				}
			},

			classname: {

				get() {

					return "alert-" + this.getAlertType;
				}
			}
		},

	methods: {

		dismiss() {

			this.$store.dispatch("unsetAlert");
		},

		shallDisplay() {

			return (this.type !== "" && this.getAlertComponentName === this.componentName) || this.getAlertComponentName === 'root-alert-container';
		},

		getData(value) {

			return  `<span>`+value+`</span>`
		}
	},

	watch: {

		message() {

			if (this.message !== "") {

				let self = this;

				const duration = this.getAlertDuration ? this.getAlertDuration : this.type === "success" ? 7000 : 7000;

				setTimeout(function() {

					self.dismiss();

				}, duration);

				let x = {};

				setTimeout(()=>{

					let x = document.getElementsByClassName("alert-container")[0];

					if(x !== undefined){

						x.scrollIntoView({behavior: "smooth", block: 'start' });
					}
				}, 50)
			}
		}
	}
};
</script>

<style type="text/css">
.alertHide {
	display: none;
}
#alert {
	/*margin: 10px 20px 20px 20px;*/
}
#ban {
	float: left !important;
	margin-left: 4px;
}

.alert-container {
	padding-top: 60px;
	margin-top: -60px;
}

#alert_close{
	font-size: 1.5rem !important;
	margin-top: -20px !important;
    border: none;
    padding: 5px;
}

#alert-message{ display: flex; }

.alert-icon { margin-top: 4px; }

</style>
