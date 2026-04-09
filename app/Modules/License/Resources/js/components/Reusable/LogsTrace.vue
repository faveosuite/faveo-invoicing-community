<template>

    <div>

        <p id="logs_trace">{{subStr(data.trace,40)}}

            <a  id="logs_read_more" href="javascript:;" @click="showModal = true">

                <span class="label label-default">{{lang('read-more')}}</span>
            </a>
        </p>
        <transition name="modal">
            <logs-modal v-if="showModal" :onClose="onClose" :showModal="showModal" :data="data" title="trace"></logs-modal>
        </transition>
    </div>

</template>

<script>

import LogsModal from "./LogsModal.vue";
import {lang} from '../../helpers/extraLogics'

import {getSubStringValue} from "../../helpers/extraLogics";

export default {

    name:"logs-trace",

    description: "Logs trace component",

    props: {

        data : { type : Object, required : true }

    },

    data(){

        return {

            showModal:false,
        }
    },

    methods:{

        lang,

        onClose(){

            this.showModal = false;
        },

        subStr(value,count) {

            return getSubStringValue(value,count)
        }
    },

    components:{

        'logs-modal': LogsModal
    }
};

</script>
<style>
.trace {
    padding: 1rem;
    color: #ffffff;
    background-color: black;
    height: 50vh;
    overflow: auto !important;
    line-height: 1.5 !important;
    white-space: nowrap !important;
}
</style>
