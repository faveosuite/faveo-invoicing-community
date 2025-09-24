import '../css/app.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import pinia from './plugins/pinia.js'
import i18n from './plugins/i18n.js'

const el = document.getElementById('app-root')
const theme = el?.dataset?.theme || 'adminlte'

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

    app.use(pinia)
    app.use(router)
    app.use(i18n)
    app.mount('#app-root')
}).catch(err => {
    console.error('[Theme load failed]', err)
})
