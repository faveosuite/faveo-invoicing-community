import '../css/app.css'
import '../css/common.scss';
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'
import { ServerTable } from 'v-tables-3'
import VueProgressBar from '@aacassandra/vue3-progressbar'

import CustomLoader from './components/Reusable/CustomLoader.vue'
import GlobalLoader from './components/Reusable/GlobalLoader.vue'
import InlineLoader from './components/Reusable/InlineLoader.vue'
import MiniLoader from './components/Reusable/MiniLoader.vue'
import SpinnerLoader from './components/Reusable/SpinnerLoader.vue'
import DatatableActions from './components/Reusable/DatatableActions.vue'
import StatusSwitch from './components/Reusable/FormField/Switch.vue'
import ActionButton from './components/Reusable/ActionButton.vue'
import FormActions from './components/Reusable/FormActions.vue'
import mitt from 'mitt'
import { setupLoaderInterceptors } from './plugins/axios.js'

const progressBarOptions = {
    color: 'rgb(0, 154, 186)',
    failedColor: 'red',
    thickness: '3px',
    transition: { speed: '2s', opacity: '0.6s' },
    autoRevert: true,
    location: 'top',
    inverse: false,
    autoFinish: false,
}

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
    app.component('global-loader', GlobalLoader)
    app.component('inline-loader', InlineLoader)
    app.component('mini-loader', MiniLoader)
    app.component('spinner-loader', SpinnerLoader)
    app.component('table-actions', DatatableActions)
    app.component('status-switch', StatusSwitch)
    app.component('action-button', ActionButton)
    app.component('form-actions', FormActions)

    app.use(pinia)
    app.use(router)
    app.use(i18n)
    app.use(VueProgressBar, progressBarOptions)
    app.use(ServerTable, {}, 'bootstrap4', {})

    setupLoaderInterceptors(app.config.globalProperties.$Progress)

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
