import { lang } from './helpers/extraLogics.js'

import mitt from 'mitt'

const emitter = mitt()

export default {

    data() {

        return {

            lang : lang,

            emitter : emitter,

            getApiKey : this.$store.getters.getApiKey
        }
    },

    methods : {

        basePath : () => (document.getElementById('app-root')?.dataset?.baseUrl ?? ''),

        trans: (string) => lang(string)
    }
}
