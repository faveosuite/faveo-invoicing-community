<template>

	<aside class="app-sidebar bg-body-secondary shadow sidebar-dark-black" data-bs-theme="dark">

    	<a href="javascript:;" class="sidebar-brand text-center">

            <image-element class="brand-link" id="admin-pic" :classes="['img-responsive', 'img-click', 'custom-img', 'brand-image']" :sourceUrl="getAdminLogo"></image-element>
    	</a>

    	<div class="sidebar-wrapper sidebar-scroll" :key="counter">

                <nav class="mt-2">

                    <div v-if="loading" class="license-navigation">

                        <loader :size="40"></loader>
                    </div>

                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview"
                        role="menu" data-accordion="false">

                        <navigation v-for="(navigation, index) in navigations" :menuItem="navigation" :key="index">

                        </navigation>
                    </ul>
                </nav>
     	</div>
    </aside>
</template>

<script>

	import axios from 'axios';

	import { getSubStringValue } from '../../helpers/extraLogics'

    import Loader from "../../components/Reusable/Loader.vue";

    import Navigation from "./Navigation.vue";
    import ImageElement from "../../components/Reusable/ImageElement.vue";
    import {useStore} from "vuex";
    import {computed} from "vue";

	export default {

		name : 'side-bar',

        setup() {

            const store = useStore();

            return {

                getAdminLogo : computed(() => store.getters.getAdminData)
            }
        },

		props : {

			user : { type : [Object, String], default : ''}
		},

		data () {

			return {

				navigations : [],

				loading : true,

				active : false,

				counter : 0
			}
		},

		beforeMount () {

			this.getRoutes();
		},

		watch : {

			$route(to, from){

        		this.counter += 1;
		   	}
		},

		methods : {

			subString(value,length = 15){

				return getSubStringValue(value,length)
			},

			getRoutes() {

          		axios.get('/json/routes.json').then((response) => {

            		setTimeout(()=>{

            			this.loading = false;

            			this.navigations = response.data.navigations;

            		},1000);

          		}).catch((error) => {

            		this.loading = false;
          		})
        	}
		},

		components : {
            "image-element": ImageElement,

			'loader': Loader,

			'navigation': Navigation,
		}
	}
</script>

<style scoped>

	.license-navigation { margin-top : 200px !important;}

    .user-panel img{
        height: 2.1rem;
        width: 2.1rem;
        object-fit: cover;
    }

    .sidebar-scroll{
        max-height: 100vh!important;
        overflow-y: auto;
    }

    .brand-image{
        max-width: 60px !important;
    }

    .sidebar-brand{
        height: 3.5rem;
    }

    .brand-link {
        float: left;
        width: auto;
        max-height: 33px;
        line-height: .8;
    }
</style>
