<template>

    <div>

        <div class="center-of-page">

            <spinner :active="true" :width="60" :height="60" :color="color" :opacity="opacity" :is-full-page="isFullPage">

            </spinner>
        </div>

        <div class="full-page"></div>
    </div>
</template>

<script>

    import Loading from 'vue-loading-overlay';

    import 'vue-loading-overlay/dist/css/index.css';

    export default {

        components:{ 'spinner':Loading},

        props:{

            color : { type : String, default : '#1d78ff'},

            opacity : { type : Number, default : 0},

            isFullPage : { type : Boolean, default : false }
        }
    };
</script>

<style type="text/css">
    .center-of-page{
        width: 100px;
        height: 100px;
        text-align: center;
        position: fixed;
        top: 25%;
        left: 25%;
        transform: translate(-50%, -50%);
        transform: -webkit-translate(-50%, -50%);
        transform: -moz-translate(-50%, -50%);
        transform: -ms-translate(-50%, -50%);
        bottom: 0;
        right: 0;
        z-index:100000;
        margin: auto;
    }
</style>
