import { lang } from './helpers/extraLogics.js'
import mitt from 'mitt'

const emitter = mitt()

export default {
    data() {
        return {
            lang,
            emitter,
        }
    },
    methods: {
        basePath: () => (document.getElementById('app-root')?.dataset?.baseUrl ?? ''),
        trans: (string) => lang(string),
    },
}
