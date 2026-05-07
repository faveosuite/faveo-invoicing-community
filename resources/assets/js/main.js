import '../css/app.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'
import { ServerTable } from 'v-tables-3'

import store from './store/index.js'
import globalMixins from './globalMixins.js'
import CustomLoader from './components/Reusable/CustomLoader.vue'
import DatatableActions from './components/Reusable/DatatableActions.vue'
import mitt from 'mitt'

const el = document.getElementById('app-root')
const theme = el?.dataset?.theme || 'adminlte'

// Global event bus used by license module components (table refresh after delete, etc.)
const emitter = mitt()
window.emitter = emitter

// load theme first, then mount
import(`./themes/${theme}/index.js`).then(themeModule => {
    const app = createApp(App)

    // surface Vue runtime errors in the console
    app.config.errorHandler = (err, instance, info) => {
        console.error('[Vue error]', info, err)
    }

    // register all theme components globally
    const components = themeModule.components || themeModule.default?.components
    if (components) {
        Object.entries(components).forEach(([name, component]) => {
            app.component(name, component)
        })
    }

    app.component('custom-loader', CustomLoader)
    app.component('table-actions', DatatableActions)

    app.use(pinia)
    app.use(router)
    app.use(i18n)
    app.use(store)
    app.mixin(globalMixins)
    app.use(ServerTable, {}, 'bootstrap4', {})

    // ── Global Bootstrap 5 tooltip directive ───────────────────────────────
    app.directive('tooltip', {
        mounted(el) {
            if (window.bootstrap?.Tooltip) {
                new window.bootstrap.Tooltip(el)
            }
        },
        updated(el) {
            const instance = window.bootstrap?.Tooltip?.getInstance(el)
            if (instance) {
                const title = el.getAttribute('title')
                if (title) instance.setContent({ '.tooltip-inner': title })
            }
        },
        unmounted(el) {
            const instance = window.bootstrap?.Tooltip?.getInstance(el)
            if (instance) instance.dispose()
        },
    })

    app.mount('#app-root')
}).catch(err => {
    console.error('[Theme load failed]', err)
})
