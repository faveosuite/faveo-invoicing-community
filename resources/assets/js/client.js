import '../css/client.css'
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'
import { createApp } from 'vue'
import Client from './Client.vue'
import clientRouter from './routes/client/clientRouter'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'
import VueProgressBarModule from '@aacassandra/vue3-progressbar'
const VueProgressBar = VueProgressBarModule.default ?? VueProgressBarModule
import { ServerTable } from 'v-tables-3'
import mitt from 'mitt'

import { components as themeComponents } from './themes/porto/index.js'

import GlobalLoader  from './components/Reusable/GlobalLoader.vue'
import Loader from './components/Reusable/Loader.vue'
import { setupLoaderInterceptors } from './plugins/axios.js'
import DateTimePlugin from './plugins/dateTime.js'
import { useDateTimeStore } from './core/stores/dateTimeStore.js'
import { useAuthStore } from './core/stores/auth.js'
import { initSentry, Sentry } from './plugins/sentry.js'

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

globalThis.emitter = mitt()

const app = createApp(Client)
const el = document.getElementById('app-client')
initSentry(app, el)

app.config.errorHandler = (err, instance, info) => {
    console.error('[Vue client error]', info, err)
    Sentry.captureException(err)
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
globalThis.__router = clientRouter
app.use(i18n)
app.use(VueProgressBar, progressBarOptions)
app.use(FloatingVue)

// Load system date/time settings non-blocking; auth store provides user timezone
useDateTimeStore().bootstrap()

setupLoaderInterceptors(app.config.globalProperties.$Progress)

await clientRouter.isReady()
app.mount('#app-client')

// Hydrate auth state in the background — replaces the data-authenticated DOM
// flag with fresh profile data once it resolves. Not blocking mount on this
// lets the router resolve its initial route (and update document.title)
// immediately instead of waiting on a network round-trip. Safe to defer:
// useAuthStore()'s state already seeds isAuthenticated/user synchronously
// from data-* attributes (auth.js), which is all router.beforeEach's
// guestOnly/requiresAuth guard needs — hydrate() only refreshes that data.
useAuthStore().hydrate()
