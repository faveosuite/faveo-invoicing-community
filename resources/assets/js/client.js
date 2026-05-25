import { createApp } from 'vue'
import Client from './Client.vue'
import clientRouter from './routes/clientRouter'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'
import VueProgressBar from '@aacassandra/vue3-progressbar'

import CustomLoader  from './components/Reusable/CustomLoader.vue'
import GlobalLoader  from './components/Reusable/GlobalLoader.vue'
import InlineLoader  from './components/Reusable/InlineLoader.vue'
import MiniLoader    from './components/Reusable/MiniLoader.vue'
import SpinnerLoader from './components/Reusable/SpinnerLoader.vue'
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

const el    = document.getElementById('app-client')
const theme = el?.dataset?.theme || 'porto'

const emitter = mitt()
window.emitter = emitter

import(`./themes/${theme}/index.js`).then(themeModule => {
    const app = createApp(Client)

    app.config.errorHandler = (err, instance, info) => {
        console.error('[Vue client error]', info, err)
    }

    const components = themeModule.components || themeModule.default?.components
    if (components) {
        Object.entries(components).forEach(([name, component]) => {
            app.component(name, component)
        })
    }

    app.component('custom-loader',  CustomLoader)
    app.component('global-loader',  GlobalLoader)
    app.component('inline-loader',  InlineLoader)
    app.component('mini-loader',    MiniLoader)
    app.component('spinner-loader', SpinnerLoader)

    app.use(pinia)
    app.use(clientRouter)
    app.use(i18n)
    app.use(VueProgressBar, progressBarOptions)

    setupLoaderInterceptors(app.config.globalProperties.$Progress)

    app.directive('tooltip', {
        mounted(el) {
            if (window.bootstrap?.Tooltip) new window.bootstrap.Tooltip(el)
        },
        unmounted(el) {
            window.bootstrap?.Tooltip?.getInstance(el)?.dispose()
        },
    })

    app.mount('#app-client')
}).catch(err => {
    console.error('[Client theme load failed]', err)
})
