import '../css/app.css'
import '../css/common.scss';
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'
import { createApp } from 'vue'
import App from './Admin.vue'
import router from './routes/admin/adminRouter'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'
import { ServerTable, ClientTable } from 'v-tables-3'
import VueProgressBar from '@aacassandra/vue3-progressbar'

import GlobalLoader from './components/Reusable/GlobalLoader.vue'
import Loader from './components/Reusable/Loader.vue'
import DatatableActions from './components/Reusable/DatatableActions.vue'
import StatusSwitch from './components/Reusable/FormField/Switch.vue'
import ActionButton from './components/Reusable/ActionButton.vue'
import FormActions from './components/Reusable/FormActions.vue'
import mitt from 'mitt'
import { setupLoaderInterceptors } from './plugins/axios.js'
import DateTimePlugin from './plugins/dateTime.js'
import { useDateTimeStore } from './core/stores/dateTimeStore.js'
import { useAuthStore } from './core/stores/auth.js'

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
globalThis.emitter = emitter

// load theme first, then mount
import(`./themes/${theme}/index.js`).then(async themeModule => {
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

    app.component('global-loader', GlobalLoader)
    app.component('loader', Loader)
    app.component('spinner-loader', Loader)
    app.component('table-actions', DatatableActions)
    app.component('status-switch', StatusSwitch)
    app.component('action-button', ActionButton)
    app.component('form-actions', FormActions)

    app.use(pinia)
    app.use(DateTimePlugin)
    app.use(router)
    globalThis.__router = router
    app.use(i18n)
    app.use(VueProgressBar, progressBarOptions)
    app.use(ServerTable, {}, 'bootstrap4', {})
    app.use(ClientTable, {}, 'bootstrap4', {})
    app.use(FloatingVue)

    // Boot datetime settings — non-blocking, UTC fallback stays active until resolved
    useDateTimeStore().bootstrap()

    setupLoaderInterceptors(app.config.globalProperties.$Progress)

    // Hydrate auth state before mount — replaces data-authenticated DOM flag
    await useAuthStore().hydrate()
    app.mount('#app-root')
}).catch(err => {
    console.error('[Theme load failed]', err)
})
