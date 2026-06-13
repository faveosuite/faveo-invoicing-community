import '../css/client.css'
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'
import { createApp } from 'vue'
import Client from './Client.vue'
import clientRouter from './routes/client/clientRouter'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'
import VueProgressBar from '@aacassandra/vue3-progressbar'
import { ServerTable } from 'v-tables-3'
import mitt from 'mitt'

import { components as themeComponents } from './themes/porto/index.js'

import GlobalLoader  from './components/Reusable/GlobalLoader.vue'
import Loader from './components/Reusable/Loader.vue'
import { setupLoaderInterceptors } from './plugins/axios.js'
import DateTimePlugin from './plugins/dateTime.js'
import { useDateTimeStore } from './core/stores/dateTimeStore.js'
import axios from './plugins/axios.js'

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

window.emitter = mitt()

const app = createApp(Client)

app.config.errorHandler = (err, instance, info) => {
    console.error('[Vue client error]', info, err)
}

Object.entries(themeComponents).forEach(([name, component]) => {
    app.component(name, component)
})

app.component('global-loader',  GlobalLoader)
app.component('loader', Loader)
app.component('spinner-loader', Loader)

app.use(pinia)
app.use(DateTimePlugin)
app.use(ServerTable, {}, 'bootstrap4', {})
app.use(clientRouter)
app.use(i18n)
app.use(VueProgressBar, progressBarOptions)
app.use(FloatingVue)

const clientEl = document.getElementById('app-client')
const clientBaseUrl = clientEl?.dataset?.baseUrl ?? ''
const clientUserTimezone = clientEl?.dataset?.userTimezone ?? ''

axios.get(`${clientBaseUrl}/settings/system-data`).then(res => {
    const s = res.data?.data?.settings ?? {}
    useDateTimeStore().init({
        timezone:   s.timezone?.name ?? 'UTC',
        dateFormat: s.date_format    ?? 'd/m/Y',
        timeFormat: s.time_format    ?? 'H:i',
    })
    if (clientUserTimezone) useDateTimeStore().setUserTimezone(clientUserTimezone)
}).catch(() => {
    useDateTimeStore().init({ timezone: 'UTC', dateFormat: 'd/m/Y', timeFormat: 'H:i' })
    if (clientUserTimezone) useDateTimeStore().setUserTimezone(clientUserTimezone)
})

setupLoaderInterceptors(app.config.globalProperties.$Progress)

// Wait for the initial navigation to resolve before mounting so that
// route.meta (e.g. standalone: true) is correct on the very first render,
// preventing a flash of the full layout on standalone pages like /open-payment.
clientRouter.isReady().then(() => app.mount('#app-client'))
