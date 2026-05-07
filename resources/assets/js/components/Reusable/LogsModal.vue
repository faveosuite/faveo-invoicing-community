<template>

    <div>

        <modal v-if="showModal" :showModal="showModal" :onClose="onClose" :containerStyle="containerStyle">

            <template #title>

                <h4 class="modal-title">{{lang(title)}}</h4>
            </template>

            <template #fields>

                <div v-if="!loading" class="mod_width">

                    <p v-html="content" :class="[{'trace' : title === 'trace'}]"></p>
                </div>


            </template>

        </modal>
    </div>
</template>

<script>

import axios from '@/plugins/axios';

export default {

    name : 'logs-modal',

    description : 'Logs Modal component',

    props:{

        showModal:{type:Boolean,default:false},

        onClose:{type: Function},

        data : { type : Object , default:()=>{}},

        title : { type : String , default :''},

        hideCheckBox: {type:Boolean}

    },

    data:()=>({

        containerStyle:{
            width:'950px'
        },
        shouldHideCheckBox :true,
        /**
         * initial state of loader
         * @type {Boolean}
         */
        loading:true,

        /**
         * size of the loader
         * @type {Number}
         */
        size: 60,

        /**
         * for rtl support
         * @type {String}
         */
        lang_locale:'',

        content : '',

        mail_message : '' ,

        isDisabled : true,

        delete_after_date : '',

        delete_before_date : '',

        logs : []

    }),

    beforeMount() {

        this.checkTitle();
    },

    created(){

        this.lang_locale = localStorage.getItem('LANGUAGE');
    },

    methods:{

        checkTitle(){
            if(this.title !== 'delete_logs'){

                if(this.title === 'logs_content'){

                    this.getLogsContent(this.data.id)
                } else{

                    this.content = this.data.trace

                    this. containerStyle.width = "1000px"

                    this.loading = false;
                }
            } else{
                this.loading = false
            }
        },

        getLogsContent(id){

            axios.get((document.getElementById('app-root')?.dataset?.baseUrl || '') + '/api/get-log-mail-body/'+id).then(res=>{

                this.loading = false

                this.content = this.contentParser(res.data.data.mail_body.replaceAll('<br />\r\n', ""));

                this.mail_message = res.data.message;
            }).catch(err => {

                this.loading = false
            })
        },
    },

};
</script>

<style type="text/css">

.mod_width{
    max-height: 400px;
    overflow-x: hidden;
    overflow-y: auto;
}
.trace {
    background: black !important;
    color: aliceblue;
    padding: 10px;
    font-family: monospace;
    font-size: 13px;
    line-height: 1.5 !important;
}

p{
    word-break:break-word;
}
</style>
