<template>
    <div class="app-wrapper">


        <nav-bar :user="getUserData"></nav-bar>
        <side-bar :user="getUserData"></side-bar>

        <div class="app-main">
            <bread-crumbs></bread-crumbs>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <router-view v-slot="{ Component }" :key="$route.fullPath">
                            <transition name="fade" mode="out-in">
                                <component :is="Component" />
                            </transition>
                        </router-view>
                    </div>
                </div>
            </div>
        </div>
        <license-footer :versioning="versioning"></license-footer>
    </div>
</template>

<script>

import { computed }  from 'vue';
import { useStore } from 'vuex';

import Navbar from "./Components/Navbar.vue";

import Sidebar from "./Components/Sidebar.vue";

import Breadcrumbs from "./Components/Breadcrumbs.vue";

import Footer from "./Components/Footer.vue";

export default {

    name : 'license-manager-layout',

    props:{
        versioning : { type : String , default : ''},
    },

    setup() {

        const store = useStore();

        return {
            // getter
            getUserData: computed(() => store.getters.getUserData)

        };
    },

    components : {

        'nav-bar' : Navbar,

        'side-bar' : Sidebar,

        'bread-crumbs' : Breadcrumbs,

        'license-footer' : Footer,
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
.content-wrapper{
  overflow: hidden;
}

</style>
<style>
.grecaptcha-badge{
    bottom: 25px !important;
    right: 0;
    display: inline;
    z-index: 1000;
}

.btn-primary{
    color: #fff !important;
    background-color: #3c8dbc !important;
    border-color: #3c8dbc !important;
    box-shadow: none !important;
}

.btn-primary:hover {
    color: #fff !important;
    background-color: #0069d9 !important;
    border-color: #0062cc !important;
}
</style>
