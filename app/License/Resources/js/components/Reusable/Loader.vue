
<template>

    <spinner :width="size" :height="size" :color="color" :active="true" :opacity="opacity" :is-full-page="isFullPage"/>
</template>

<script>

    import Loading from 'vue-loading-overlay';

    // import 'vue-loading-overlay/dist/vue-loading.css';

    export default{

        props:{

            size : { type : [Number, String], default : 60},

            color : { type : String , default : '#1d78ff'},

            opacity : { type : Number, default : 0},

            isFullPage : { type : Boolean, default : false }
        },

    	components: {

            'spinner':Loading,
        }
    };
</script>

<style scoped>

    .fulfilling-bouncing-circle-spinner{
        margin: auto !important;
        left :0;
    }
</style>
