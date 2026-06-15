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
import { useAuthStore } from './core/stores/auth.js'
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

// Hydrate auth BEFORE installing the router. app.use(router) immediately
// triggers Vue Router's initial navigation, which fires beforeEach. If auth
// is not yet hydrated at that point, isAuthenticated is false and guestOnly
// routes (like /login) are not redirected even for logged-in users.
const auth = useAuthStore()
await auth.hydrate()

app.use(clientRouter)
app.use(i18n)
app.use(VueProgressBar, progressBarOptions)
app.use(FloatingVue)

const clientEl = document.getElementById('app-client')
const clientBaseUrl = clientEl?.dataset?.baseUrl ?? ''

// Load system date/time settings non-blocking; auth store provides user timezone
axios.get(`${clientBaseUrl}/settings/system-data`).then(res => {
    const s = res.data?.data?.settings ?? {}
    useDateTimeStore().init({
        timezone:   s.timezone?.name ?? 'UTC',
        dateFormat: s.date_format    ?? 'd/m/Y',
        timeFormat: s.time_format    ?? 'H:i',
    })
    // Re-apply user timezone after system data loads so it isn't overwritten
    const userTz = useAuthStore().user?.timezone?.name
    if (userTz) useDateTimeStore().setUserTimezone(userTz)
}).catch(() => {
    useDateTimeStore().init({ timezone: 'UTC', dateFormat: 'd/m/Y', timeFormat: 'H:i' })
})

setupLoaderInterceptors(app.config.globalProperties.$Progress)

await clientRouter.isReady()
app.mount('#app-client')
