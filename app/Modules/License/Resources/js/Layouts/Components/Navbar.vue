<template>

	<nav class="app-header navbar navbar-expand bg-body">

        <div class="container-fluid">

		    <ul class="navbar-nav">

                <li class="nav-item">

                    <a class="nav-link" data-lte-toggle="sidebar" href="javascript:;" role="button"><i class="fas fa-bars"></i></a>
                </li>

                <li class="nav-item d-none d-sm-inline-block">

                    <router-link to="/dashboard" v-tooltip="lang('home')" class="nav-link">{{trans('home')}}</router-link>
                </li>

            </ul>

            <ul class="navbar-nav ms-auto" v-if="user">

                <li class="nav-item dropdown user-menu">

                    <a href="javascript:;" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">

                        <span class="d-none d-md-inline me-2" v-tooltip="user.client_fname + ' ' + user.client_lname" dir="auto">
                             {{ subString(user.client_fname + ' ' + user.client_lname) }}
                         </span>
                        <image-element :sourceUrl="user.client_profile_pic" id="navbar-profile" class="user-image img-circle shadow d-none d-md-inline" alt="User Image"/>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-sm dropdown-profile dropdown-menu-end rounded model-box text-white dropdown-menu-arrow mt-2">
                        <li>
                            <router-link class="dropdown-item dp-data" to="/profile/edit"><i class="fa fa-user pe-2"></i>{{ trans('profile') }}</router-link>
                        </li>
                        <li>
                            <a href="javascript:;" class="dropdown-item dp-data mb-4 mt-1" @click="signOut">
                                <i class="fas fa-sign-out-alt pe-2"></i>{{ trans('sign_out') }}
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

	    <custom-loader v-if="loading"></custom-loader>
  	</nav>
</template>

<script>

	import { errorHandler } from '../../helpers/responseHandler';
    import ImageElement from "../../components/Reusable/ImageElement.vue";
    import {getSubStringValue, lang} from "../../helpers/extraLogics";

	export default {

		name : 'nav-bar',

		props : {

			user : { type : [Object, String], default : ''}
		},

		data () {

			return {

				loading : false
			}
		},

		methods : {
            lang,

            subString(value,length = 30){

                return getSubStringValue(value,length)
            },

			signOut() {

				this.loading = true;

				axios.get('/auth/logout').then(res=>{

          			this.loading = false;

          			window.location = window.axios.defaults.baseURL + '/login';

				}).catch(err=>{

					errorHandler(err);

          			this.loading = false;

				})
			}
		},

        components: {

            'image-element': ImageElement
        }
	}
</script>

<style scoped>

.dropdown-menu-arrow:before {
    content: ""!important;
    position: absolute!important;
    top: -10px!important;
    left: 88%;
    transform: translate(-50%);
    border-width: 3px 7px 8px;
    border-style: solid;
    border-color: transparent transparent #3e4d5d
}

.model-box {
    margin-top: 8px !important;
    margin-right: 20px !important;
    padding-top: 9px !important;
    width: 170px !important;
    height: 82px !important;
    background-color: #4f5962;
}
.dp-data {
    background-color: #4f5962;
    color: #c2c7d0 !important;
}
.dp-data:hover {
    background-color: rgba(0,0,0,0.2);
    color: #c2c7d0;
}

.dropdown-profile{
    right: 0;
    left: auto !important;
}
</style>
