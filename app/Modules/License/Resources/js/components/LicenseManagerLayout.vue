<template>

	<div class="wrapper">

		<nav-bar :user="getUserData"></nav-bar>

		<side-bar :user="getUserData"></side-bar>

		<div class="content-wrapper">

			<bread-crumbs></bread-crumbs>

			<div class="content">

				<div class="container-fluid">

					<div class="row">

						<transition name="fade" mode="out-in">

							<router-view :key="$route.fullPath" :user="getUserData"></router-view>
						</transition>
					</div>
				</div>
			</div>
		</div>

		<license-footer></license-footer>
	</div>
</template>

<script>

	import { mapGetters } from 'vuex';

	export default {

		name : 'license-manager-layout',

		components : {

			'nav-bar' : require('../Layouts/Components/Navbar').default,

			'side-bar' : require('../Layouts/Components/Sidebar').default,

			'bread-crumbs' : require('../Layouts/Components/Breadcrumbs').default,

			'license-footer' : require('../Layouts/Components/Footer').default,
		},

		computed : {

			...mapGetters(['getUserData'])
		}
	};
</script>

<style scoped>

	.fade-enter {
	  opacity: 0;
	}

	.fade-enter-active {
	  transition: opacity 0.2s ease;
	}

	.fade-leave {}

	.fade-leave-active {
	  transition: opacity 0.2s ease;
	  opacity: 0;
	}
</style>
